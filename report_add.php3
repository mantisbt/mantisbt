<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_mysql_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	if ( !access_level_check_greater( "viewer" ) ) {
		# should be an access error page
		header( "Location: $g_logout_page" );
		exit;
	}
	# need access level check

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
		$query = "SELECT id
				FROM $g_mantis_user_table
				WHERE cookie_string='$g_string_cookie_val'";
		$result = db_mysql_query( $query );
		$u_id = mysql_result( $result, 0 );

		### Make strings safe for database
		$f_summary = string_safe( $f_summary );
		$f_description = string_safe( $f_description );
		$f_additional_info = string_safe( $f_additional_info );
		$f_steps_to_reproduce = string_safe( $f_steps_to_reproduce );
		$f_os = string_safe( $f_os );
		$f_osbuild = string_safe( $f_osbuild );
		$f_platform = string_safe( $f_platform );
		$f_version = string_safe( $f_version );
		$f_build = string_safe( $f_build );

		### Insert text information
		$query = "INSERT
				INTO $g_mantis_bug_text_table
				( id, description, steps_to_reproduce, additional_information )
				VALUES
				( null, '$f_description', '$f_steps_to_reproduce',
				'$f_additional_info' )";
		$result = mysql_query( $query );

		### MAJOR BUG!!! WHAT IF MULTIPLE REPORTS AT SAME TIMES?!?!?
		### FIX ME### FIX ME### FIX ME### FIX ME### FIX ME### FIX ME
		### Get the id of the text information we just inserted
		$query = "SELECT id
				FROM $g_mantis_bug_text_table
				ORDER BY id DESC
				LIMIT 1";
		$result = mysql_query( $query );
		$t_id = mysql_result( $result, 0 );

		### Insert the rest of the data
		$query = "INSERT
				INTO $g_mantis_bug_table
				( id, reporter_id, handler_id, duplicate_id, priority, severity,
				reproducibility, status, resolution, projection, category,
				date_submitted, last_updated, eta, bug_text_id, os, os_build,
				platform, version, build, votes, summary )
				VALUES
				( null, '$u_id', '0000000', '0000000', 'normal', '$f_severity',
				'$f_reproducibility', 'new', 'open', 'minor fix', '$f_category',
				NOW(), NOW(), NOW(), '$t_id', '$f_os', '$f_osbuild',
				'$f_platform', '$f_version', '$f_build',
				1, '$f_summary' )";
		$result = mysql_query( $query );
	}
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<?
	if ( $result ) {
		print_meta_redirect( $g_bug_view_all_page, $g_wait_time );
	}
?>
<? include( $g_meta_include_file ) ?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>

<p>
<? print_menu( $g_menu_include_file ) ?>

<p>
<div align=center>
<?
	### FORM ERROR
	### required fields not entered
	if ( $check_failed ) {
		PRINT "<b>ERROR: There was an error in your report</b><br>";
		if ( $f_category=="" ) {
			PRINT "You must select a category<br>";
		}
		if ( $f_severity=="" ) {
			PRINT "You must select a severity<br>";
		}
		if ( $f_reproducibility=="" ) {
			PRINT "You must select a reproducibility<br>";
		}
		if ( $f_summary=="" ) {
			PRINT "You must enter a summary<br>";
		}
		if ( $f_description=="" ) {
			PRINT "You must enter a description<br>";
		}
		PRINT "<p>";
		PRINT "Please hit back and renter the required fields.";
	}
	### MYSQL ERROR
	else if ( !$result ) {
		PRINT "ERROR DETECTED: Report this sql statement to <a href=\"<? echo $g_administrator_email ?>\">administrator</a><p>";
		PRINT $query;
	}
	### OK!!!
	else {
		PRINT "Thank you for your submission.";
		PRINT "<p>";
		PRINT "You are now being transported to the Bug Viewing Page in 3 seconds ... <p>";
	}
?>
<p>
<a href="<? echo $g_bug_view_all_page ?>">Click here to proceed</a>
</div>

<? print_footer() ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>