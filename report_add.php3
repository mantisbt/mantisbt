<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	### This page stores the reported bug and then redirects to view_all_bug_page.php3
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( REPORTER );

	### We check to see if the variable exists to avoid warnings

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

	### validating input
	$check_failed = false;
	if ( ( $f_category=="" ) ||
		 ( $f_severity=="" ) ||
		 ( $f_reproducibility=="" ) ||
		 ( $f_summary=="" ) ||
		 ( $f_description=="" ) ) {
		$check_failed = true;
	}

	### required fields ok, proceeding
	if ( !$check_failed ) {
		### Get user id
		$u_id = get_current_user_field( "id" );

		### Make strings safe for database
		$f_summary 				= string_prepare_text( $f_summary );
		$f_description 			= string_prepare_textarea( $f_description );
		$f_additional_info 		= string_prepare_textarea( $f_additional_info );
		$f_steps_to_reproduce 	= string_prepare_textarea( $f_steps_to_reproduce );

		$f_build 				= string_prepare_text( $f_build );
		$f_platform 			= string_prepare_text( $f_platform );
		$f_os 					= string_prepare_text( $f_os );
		$f_osbuild 				= string_prepare_text( $f_osbuild );

		### if a profile was selected then let's use that information
		if ( !empty( $f_profile_id ) ) {
			### Get profile data and prefix with v_
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

		### Insert text information
		$query = "INSERT
				INTO $g_mantis_bug_text_table
				( id, description, steps_to_reproduce, additional_information )
				VALUES
				( null, '$f_description', '$f_steps_to_reproduce',
				'$f_additional_info' )";
		$result = db_query( $query );

		### Get the id of the text information we just inserted
		### NOTE: this is guarranteed to be the correct one.
		### The value LAST_INSERT_ID is stored on a per connection basis.

		$t_id = db_insert_id();

		### check to see if we want to assign this right off
		$t_status = NEW_;
		if ( $f_assign_id != "0000000" ) {
			$t_status = ASSIGNED;
		}

		### Insert the rest of the data
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

		### File Uploaded
		if ( !isset( $f_file ) ) {
			$f_file = "none";
		}
		$f_file = trim( $f_file );
		if (( $f_file != "none" )&&( !empty( $f_file) )) {
			$t_bug_id = str_pd( $t_bug_id, "0", 7 );

			$query = "SELECT file_path
					FROM $g_mantis_project_table
					WHERE id='$g_project_cookie_val'";
			$result = db_query( $query );
			$t_file_path = db_result( $result );

			$f_file_name = $t_bug_id."-".$f_file_name;
			copy($f_file, $t_file_path.$f_file_name);
			$t_file_size = filesize( $f_file );
			$t_content = addslashes(fread(fopen($f_file, "r"), filesize($f_file)));
			$query = "INSERT INTO mantis_bug_file_table
					(id, bug_id, title, description, diskfile, filename, folder, filesize, date_added, content)
					VALUES
					(null, $t_bug_id, '', '', '$t_file_path$f_file_name', '$f_file_name', '$t_file_path', $t_file_size, NOW(), '')";
			$result = db_query( $query );
		}

		### Notify users of new bug report
		if ( $g_notify_developers_on_new == 1 ) {
			email_new_bug( $t_bug_id );
		}
	}
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<? include( $g_meta_include_file ) ?>
<?
	if (( !$check_failed )&&( $result )&&( !isset( $f_report_stay ) )) {
		print_meta_redirect( $g_view_all_bug_page, $g_wait_time );
	}
?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>
<? print_top_page( $g_top_include_page ) ?>

<p>
<? print_menu( $g_menu_include_file ) ?>

<p>
<div align=center>
<?
	### FORM ERROR
	### required fields not entered
	if ( $check_failed ) {
		PRINT "<b>$s_report_add_error_msg</b><p>";
		if ( $f_category=="" ) {
			PRINT "$s_must_enter_category<br>";
		}
		if ( $f_severity=="" ) {
			PRINT "$s_must_enter_severity<br>";
		}
		if ( $f_reproducibility=="" ) {
			PRINT "$s_must_enter_reproducibility<br>";
		}
		if ( $f_summary=="" ) {
			PRINT "$s_must_enter_summary<br>";
		}
		if ( $f_description=="" ) {
			PRINT "$s_must_enter_description<br>";
		}
?>
		<p>
		<form method=post action="<? echo $HTTP_REFERER ?>">
		<input type=hidden name=f_category value="<? echo $f_category ?>">
		<input type=hidden name=f_severity value="<? echo $f_severity ?>">
		<input type=hidden name=f_reproducibility value="<? echo $f_reproducibility ?>">

		<input type=hidden name=f_profile_id value="<? echo $f_profile_id ?>">
		<input type=hidden name=f_platform value="<? echo $f_platform ?>">
		<input type=hidden name=f_os value="<? echo $f_os ?>">
		<input type=hidden name=f_osbuild value="<? echo $f_osbuild ?>">
		<input type=hidden name=f_product_version value="<? echo $f_product_version ?>">
		<input type=hidden name=f_build value="<? echo $f_build ?>">
		<input type=hidden name=f_assign_id value="<? echo $f_assign_id ?>">

		<input type=hidden name=f_summary value="<? echo $f_summary ?>">
		<input type=hidden name=f_description value="<? echo $f_description ?>">
		<input type=hidden name=f_steps_to_reproduce value="<? echo $f_steps_to_reproduce ?>">
		<input type=hidden name=f_additional_info value="<? echo $f_additional_info ?>">
		<input type=submit value="<? echo $s_go_back ?>">
		</form>
<?
	} else if ( !$result ) {		### MYSQL ERROR
		print_sql_error( $query );
	} else {						### SUCCESS
		PRINT "$s_submission_thanks_msg<p>";

		if ( isset( $f_report_stay )) {
			PRINT "<form method=post action=\"$HTTP_REFERER\">";
			PRINT "<input type=hidden name=f_category value=\"$f_category\">";
			PRINT "<input type=hidden name=f_severity value=\"$f_severity\">";
			PRINT "<input type=hidden name=f_reproducibility value=\"$f_reproducibility\">";
			PRINT "<input type=hidden name=f_profile_id value=\"$f_profile_id\">";
			PRINT "<input type=hidden name=f_platform value=\"$f_platform\">";
			PRINT "<input type=hidden name=f_os value=\"$f_os\">";
			PRINT "<input type=hidden name=f_osbuild value=\"$f_osbuild\">";
			PRINT "<input type=hidden name=f_product_version value=\"$f_product_version\">";
			PRINT "<input type=hidden name=f_build value=\"$f_build\">";
			PRINT "<input type=hidden name=f_report_stay value=\"$f_report_stay\">";
			PRINT "<input type=submit value=\"$s_report_more_bugs\">";
			PRINT "</form>";
		} else {
			print_bracket_link( $g_view_all_bug_page, $s_view_bugs_link );
		}
	}
?>
</div>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>