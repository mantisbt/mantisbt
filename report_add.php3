<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# This page stores the reported bug and then redirects to view_all_bug_page.php3
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?php
	# these pages are invalid for the "All Project" selection
	if ( "0000000" == $g_project_cookie_val ) {
		print_header_redirect( $g_login_select_proj_page );
	}

	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( REPORTER );

	# We check to see if the variable exists to avoid warnings

	if ( !isset( $f_steps_to_reproduce ) ) {
		$f_steps_to_reproduce = "";
	}

	if ( !isset( $f_build ) ) {
		$f_build = "";
	}

	if ( !isset( $f_platform ) ) {
		$f_platform = "";
	}

	if ( !isset( $f_os ) ) {
		$f_os = "";
	}

	if ( !isset( $f_osbuild ) ) {
		$f_osbuild = "";
	}

	if ( !isset( $f_product_version ) ) {
		$f_product_version = "";
	}

	if ( !isset( $f_profile_id ) ) {
		$f_profile_id = "";
	}

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
	if ( !$check_failed ) {
		# Get user id
		$u_id = get_current_user_field( "id" );

		# Make strings safe for database
		$f_summary 				= string_prepare_text( $f_summary );
		$f_description 			= string_prepare_textarea( $f_description );
		$f_additional_info 		= string_prepare_textarea( $f_additional_info );
		$f_steps_to_reproduce 	= string_prepare_textarea( $f_steps_to_reproduce );

		$f_build 				= string_prepare_text( $f_build );
		$f_platform 			= string_prepare_text( $f_platform );
		$f_os 					= string_prepare_text( $f_os );
		$f_osbuild 				= string_prepare_text( $f_osbuild );

		# if a profile was selected then let's use that information
		if ( !empty( $f_profile_id ) ) {
			# Get profile data and prefix with v_
			$query = "SELECT *
				FROM $g_mantis_user_profile_table
				WHERE id='$f_profile_id'";
		    $result = db_query( $query );
		    $profile_count = db_num_rows( $result );
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, "v" );

			$f_platform	= string_prepare_text( $v_platform );
			$f_os		= string_prepare_text( $v_os );
			$f_osbuild	= string_prepare_text( $v_os_build );
		}

		# Insert text information
		$query = "INSERT
				INTO $g_mantis_bug_text_table
				( id, description, steps_to_reproduce, additional_information )
				VALUES
				( null, '$f_description', '$f_steps_to_reproduce',
				'$f_additional_info' )";
		$result = db_query( $query );

		# Get the id of the text information we just inserted
		# NOTE: this is guarranteed to be the correct one.
		# The value LAST_INSERT_ID is stored on a per connection basis.

		$t_id = db_insert_id();

		# check to see if we want to assign this right off
		$t_status = NEW_;
		if ( $f_assign_id != "0000000" ) {
			$t_status = ASSIGNED;
		}

		# Insert the rest of the data
		$t_open = OPEN;
		$t_nor = NORMAL;
		$query = "INSERT
				INTO $g_mantis_bug_table
				( id, project_id, reporter_id, handler_id, duplicate_id, priority, severity,
				reproducibility, status, resolution, projection, category,
				date_submitted, last_updated, eta, bug_text_id, os, os_build,
				platform, version, build, votes, profile_id, summary )
				VALUES
				( null, '$g_project_cookie_val', '$u_id', '$f_assign_id', '0000000', '$t_nor', '$f_severity',
				'$f_reproducibility', '$t_status', '$t_open', 10, '$f_category',
				NOW(), NOW(), 10, '$t_id', '$f_os', '$f_osbuild',
				'$f_platform', '$f_product_version', '$f_build',
				1, '$f_profile_id', '$f_summary' )";
		$result = db_query( $query );

		$t_bug_id = db_insert_id();

		# File Uploaded
		if ( !isset( $f_file ) ) {
			$f_file = "none";
		}
		$f_file = trim( $f_file );
		if ( is_uploaded_file( $f_file ) ) {
			$t_bug_id = str_pd( $t_bug_id, "0", 7, STR_PAD_LEFT );

			$query = "SELECT file_path
					FROM $g_mantis_project_table
					WHERE id='$g_project_cookie_val'";
			$result = db_query( $query );
			$t_file_path = db_result( $result );

			$f_file_name = $t_bug_id."-".$f_file_name;
			copy($f_file, $t_file_path.$f_file_name);
			$t_file_size = filesize( $f_file );
			$t_content = addslashes(fread(fopen($f_file, "r"), filesize($f_file)));
			$query = "INSERT INTO $g_mantis_bug_file_table
					(id, bug_id, title, description, diskfile, filename, folder, filesize, date_added, content)
					VALUES
					(null, $t_bug_id, '', '', '$t_file_path$f_file_name', '$f_file_name', '$t_file_path', $t_file_size, NOW(), '')";
			$result = db_query( $query );
		}

		# Notify users of new bug report
		if ( ON == $g_notify_developers_on_new ) {
			email_new_bug( $t_bug_id );
		}
	}

	# Determine which report page to redirect back to.
	$t_redirect_url = get_report_redirect_url();
?>
<?php print_page_top1() ?>
<?php
	if ( ( !$check_failed )&&( $result )&&( !isset( $f_report_stay ) ) ) {
		print_meta_redirect( $g_view_all_bug_page, $g_wait_time );
	}
?>
<?php print_page_top2() ?>

<p>
<div align="center">
<?php
	# FORM ERROR
	# required fields not entered
	if ( $check_failed ) {
		PRINT "<span class=\"bold\">$s_report_add_error_msg</span><p>";
		if ( empty( $f_category ) ) {
			PRINT "$s_must_enter_category<br>";
		}
		if ( empty( $f_severity ) ) {
			PRINT "$s_must_enter_severity<br>";
		}
		if ( empty( $f_reproducibility ) ) {
			PRINT "$s_must_enter_reproducibility<br>";
		}
		if ( empty( $f_summary ) ) {
			PRINT "$s_must_enter_summary<br>";
		}
		if ( empty( $f_description ) ) {
			PRINT "$s_must_enter_description<br>";
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
			<input type="hidden" name="f_osbuild" 			value="<?php echo $f_osbuild ?>">
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
				<input type="hidden" name="f_osbuild" 			value="<?php echo $f_osbuild ?>">
				<input type="hidden" name="f_product_version" 	value="<?php echo $f_product_version ?>">
				<input type="hidden" name="f_build" 			value="<?php echo $f_build ?>">
				<input type="hidden" name="f_report_stay" 		value="<?php echo $f_report_stay ?>">
				<input type="submit" 							value="<?php echo $s_report_more_bugs ?>">
			</form>
<?php
		} else {
			print_bracket_link( $g_view_all_bug_page, $s_view_bugs_link );
		}
	}
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>