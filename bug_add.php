<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# This page stores the reported bug and then redirects to view_all_bug_page.php3
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	# these pages are invalid for the 'All Project' selection
	if ( '0000000' == $g_project_cookie_val ) {
		print_header_redirect( 'login_select_proj_page.php' );
	}

	check_access( REPORTER );

	if ( ( ( DISK == $g_file_upload_method ) || ( FTP == $g_file_upload_method ) ) && isset( $f_file ) && is_uploaded_file( $f_file ) ) {
		$query = "SELECT file_path
				FROM $g_mantis_project_table
				WHERE id='$g_project_cookie_val'";
		$result = db_query( $query );
		$t_file_path = db_result( $result );

		if ( !file_exists( $t_file_path ) ) {
			print_mantis_error( ERROR_NO_DIRECTORY );
		}
	}

	# We check to see if the variable exists to avoid warnings

	check_varset( $f_steps_to_reproduce, '' );
	check_varset( $f_build, '' );
	check_varset( $f_platform, '' );
	check_varset( $f_os, '' );
	check_varset( $f_os_build, '' );
	check_varset( $f_product_version, '' );
	check_varset( $f_profile_id, '' );

	# validating input
	$check_failed = false;
	if ( ( empty( $f_category ) ) ||
		 ( empty( $f_severity ) ) ||
		 ( empty( $f_reproducibility ) ) ||
		 ( empty( $f_summary ) ) ||
		 ( empty( $f_description ) ) ) {
		$check_failed = true;
	}

	# required fields ok, proceeding
	$result = 0;
	if ( !$check_failed ) {
		# Get user id
		$u_id = current_user_get_field( 'id' );

		# Make strings safe for database
		$c_summary 				= string_prepare_text( $f_summary );
		$c_description 			= string_prepare_textarea( $f_description );
		$c_additional_info 		= string_prepare_textarea( $f_additional_info );
		$c_steps_to_reproduce 	= string_prepare_textarea( $f_steps_to_reproduce );

		$c_build 				= string_prepare_text( $f_build );
		$c_platform 			= string_prepare_text( $f_platform );
		$c_os 					= string_prepare_text( $f_os );
		$c_os_build 			= string_prepare_text( $f_os_build );

		# if a profile was selected then let's use that information
		if ( !empty( $f_profile_id ) ) {
			# Get profile data and prefix with v_
			$c_profile_id = (integer)$f_profile_id;
			$query = "SELECT *
				FROM $g_mantis_user_profile_table
				WHERE id='$c_profile_id'";
		    $result = db_query( $query );
		    $profile_count = db_num_rows( $result );
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, 'v' );

			$c_platform	= string_prepare_text( $v_platform );
			$c_os		= string_prepare_text( $v_os );
			$c_os_build	= string_prepare_text( $v_os_build );
		}

		# Insert text information
		$query = "INSERT
				INTO $g_mantis_bug_text_table
				( id, description, steps_to_reproduce, additional_information )
				VALUES
				( null, '$c_description', '$c_steps_to_reproduce',
				'$c_additional_info' )";
		$result = db_query( $query );

		# Get the id of the text information we just inserted
		# NOTE: this is guarranteed to be the correct one.
		# The value LAST_INSERT_ID is stored on a per connection basis.

		$t_id = db_insert_id();

		check_varset( $f_priority, NORMAL );

		$c_assign_id 		= (integer)$f_assign_id;
		$c_severity 		= (integer)$f_severity;
		$c_reproducibility 	= (integer)$f_reproducibility;
		$c_view_state 		= (integer)$f_view_state;
		$c_profile_id		= (integer)$f_profile_id;
		$c_priority 		= (integer)$f_priority;
		$c_category 		= addslashes($f_category);
		$c_product_version 	= addslashes($f_product_version);

		# check to see if we want to assign this right off
		$t_status = NEW_;

		# if not assigned, check if it should auto-assigned.
		if ( $c_assign_id == 0 ) {
			# if a default user is associated with the category and we know at this point
			# that that the bug was not assigned to somebody, then assign it automatically.
			$query = "SELECT user_id
					FROM $g_mantis_project_category_table
					WHERE project_id='$g_project_cookie_val' AND category='$c_category'";

			$result = db_query( $query );

			if ( db_num_rows( $result ) == 1 ) {
				$c_assign_id = db_result( $result );
				$f_assign_id = sprintf( '%07d', $c_assign_id );
			}
		}

		# Check if bug was pre-assigned or auto-assigned.
		if ( ( $c_assign_id != 0 ) && ( ON == $g_auto_set_status_to_assigned ) ) {
			$t_status = ASSIGNED;
		}

		# Insert the rest of the data
		$t_open = OPEN;

		$query = "INSERT
				INTO $g_mantis_bug_table
				( id, project_id,
				reporter_id, handler_id,
				duplicate_id, priority,
				severity, reproducibility,
				status, resolution,
				projection, category,
				date_submitted, last_updated,
				eta, bug_text_id,
				os, os_build,
				platform, version,
				build, votes,
				profile_id, summary, view_state )
				VALUES
				( null, '$g_project_cookie_val',
				'$u_id', '$c_assign_id',
				'0000000', '$c_priority',
				'$c_severity', '$c_reproducibility',
				'$t_status', '$t_open',
				10, '$f_category',
				NOW(), NOW(),
				10, '$t_id',
				'$c_os', '$c_os_build',
				'$c_platform', '$c_product_version',
				'$c_build',	1,
				'$c_profile_id', '$c_summary', '$c_view_state' )";
		$result = db_query( $query );

		$t_bug_id = db_insert_id();

		# log new bug
		history_log_event_special( $t_bug_id, NEW_BUG );

		# File Uploaded
		check_varset( $f_file, 'none' );
		$f_file = trim( $f_file );
		$disallowed = 0;
		check_varset( $f_file_name, '' );
		if ( !file_type_check( $f_file_name ) ) {
			$disallowed = 1;
		} else if ( is_uploaded_file( $f_file ) ) {
			$t_bug_id = str_pad( $t_bug_id, '0', 7, STR_PAD_LEFT );

			# grab the file path
			$t_file_path = project_get_field( helper_get_current_project(), 'file_path' );

			# prepare variables for insertion
			$f_file_name = $t_bug_id.'-'.$f_file_name;
			$t_file_size = filesize( $f_file );

			switch ( $g_file_upload_method ) {
				case FTP:
				case DISK:	if ( !file_exists( $t_file_path.$f_file_name ) ) {
								if ( FTP == $g_file_upload_method ) {
									$conn_id = file_ftp_connect();
									file_ftp_put ( $conn_id, $f_file_name, $f_file );
									file_ftp_disconnect ( $conn_id );
								}

								umask( 0333 );  # make read only
								copy($f_file, $t_file_path.$f_file_name);

								$query = "INSERT INTO $g_mantis_bug_file_table
										(id, bug_id, title, description, diskfile, filename, folder, filesize, file_type, date_added, content)
										VALUES
										(null, $t_bug_id, '', '', '$t_file_path$f_file_name', '$f_file_name', '$t_file_path', $t_file_size, '$f_file_type', NOW(), '')";
							} else {
								print_mantis_error( ERROR_DUPLICATE_FILE );
							}
							break;
				case DATABASE:
							$t_content = addslashes( fread ( fopen( $f_file, 'rb' ), $t_file_size ) );
							$query = "INSERT INTO $g_mantis_bug_file_table
									(id, bug_id, title, description, diskfile, filename, folder, filesize, file_type, date_added, content)
									VALUES
									(null, $t_bug_id, '', '', '$t_file_path$f_file_name', '$f_file_name', '$t_file_path', $t_file_size, '$f_file_type', NOW(), '$t_content')";
							break;
			}
			$result = db_query( $query );

			# log new bug
			history_log_event_special( $t_bug_id, FILE_ADDED, file_get_display_name( $f_file_name ) );
		}

		# Notify users of new bug report
		email_new_bug( $t_bug_id );
	} # end if !check_failed

	# Determine which report page to redirect back to.
	$t_redirect_url = get_report_redirect_url();
?>
<?php print_page_top1() ?>
<?php
	if ( ( !$check_failed )&&( $result )&&( !isset( $f_report_stay ) ) ) {
		print_meta_redirect( 'view_all_bug_page.php', $g_wait_time );
	}
?>
<?php print_page_top2() ?>

<p>
<div align="center">
<?php
	# FORM ERROR
	# required fields not entered
	if ( $check_failed ) {
		PRINT '<span class="bold">'.$MANTIS_ERROR[ERROR_REPORT].'</span><p>';
		if ( empty( $f_category ) ) {
			PRINT $s_must_enter_category.'<br />';
		}
		if ( empty( $f_severity ) ) {
			PRINT $s_must_enter_severity.'<br />';
		}
		if ( empty( $f_reproducibility ) ) {
			PRINT $s_must_enter_reproducibility.'<br />';
		}
		if ( empty( $f_summary ) ) {
			PRINT $s_must_enter_summary.'<br />';
		}
		if ( empty( $f_description ) ) {
			PRINT $s_must_enter_description.'<br />';
		}
?>
		<p>
		<form method="post" action="<?php echo $t_redirect_url ?>">
			<input type="hidden" name="f_category" 			value="<?php echo $f_category ?>">
			<input type="hidden" name="f_severity" 			value="<?php echo $f_severity ?>">
			<input type="hidden" name="f_reproducibility" 	value="<?php echo $f_reproducibility ?>">
			<input type="hidden" name="f_profile_id" 		value="<?php echo $f_profile_id ?>">
			<input type="hidden" name="f_platform" 			value="<?php echo $f_platform ?>">
			<input type="hidden" name="f_os" 				value="<?php echo $f_os ?>">
			<input type="hidden" name="f_os_build" 			value="<?php echo $f_os_build ?>">
			<input type="hidden" name="f_product_version" 	value="<?php echo $f_product_version ?>">
			<input type="hidden" name="f_build" 			value="<?php echo $f_build ?>">
			<input type="hidden" name="f_assign_id" 		value="<?php echo $f_assign_id ?>">
			<input type="hidden" name="f_summary" 			value="<?php echo $f_summary ?>">
			<input type="hidden" name="f_description" 		value="<?php echo $f_description ?>">
			<input type="hidden" name="f_steps_to_reproduce" value="<?php echo $f_steps_to_reproduce ?>">
			<input type="hidden" name="f_additional_info" 	value="<?php echo $f_additional_info ?>">
			<input type="submit" 							value="<?php echo $s_go_back ?>">
		</form>
<?php
	} else if ( !$result ) {		# MYSQL ERROR
		print_sql_error( $query );
	} else {						# SUCCESS
		PRINT "$s_operation_successful<p>";

		if ( isset( $f_report_stay )) {
?>
			<form method="post" action="<?php echo $t_redirect_url ?>">
				<input type="hidden" name="f_category" 			value="<?php echo $f_category ?>">
				<input type="hidden" name="f_severity" 			value="<?php echo $f_severity ?>">
				<input type="hidden" name="f_reproducibility" 	value="<?php echo $f_reproducibility ?>">
				<input type="hidden" name="f_profile_id" 		value="<?php echo $f_profile_id ?>">
				<input type="hidden" name="f_platform" 			value="<?php echo $f_platform ?>">
				<input type="hidden" name="f_os" 				value="<?php echo $f_os ?>">
				<input type="hidden" name="f_os_build" 			value="<?php echo $f_os_build ?>">
				<input type="hidden" name="f_product_version" 	value="<?php echo $f_product_version ?>">
				<input type="hidden" name="f_build" 			value="<?php echo $f_build ?>">
				<input type="hidden" name="f_report_stay" 		value="<?php echo $f_report_stay ?>">
				<input type="submit" 							value="<?php echo $s_report_more_bugs ?>">
			</form>
<?php
		} else {
			$t_view_bug_url = get_view_redirect_url( $t_bug_id, 1 );
			print_bracket_link( $t_view_bug_url, $s_view_submitted_bug_link.' '.$t_bug_id );
			print_bracket_link( 'view_all_bug_page.php', $s_view_bugs_link );
		}
	}
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
