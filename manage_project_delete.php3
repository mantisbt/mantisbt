<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	if ( !access_level_check_greater_or_equal( "administrator" ) ) {
		### need to replace with access error page
		header( "Location: $g_logout_page" );
		exit;
	}

	### Delete the project entry
	$query = "DELETE
			FROM $g_mantis_project_table
    		WHERE id='$f_project_id'";
    $result = db_query( $query );

/*	### Delete the bugs
	$query = "DELETE
			FROM $g_mantis_project_table
    		WHERE id='$f_id'";
    $result = db_query( $query );

	### Delete the bug texts
	$query = "DELETE
			FROM $g_mantis_project_table
    		WHERE id='$f_id'";
    $result = db_query( $query );

	### Delete the bugnotes
	$query = "DELETE
			FROM $g_mantis_project_table
    		WHERE id='$f_id'";
    $result = db_query( $query );

	### Delete the bugnote texts
	$query = "DELETE
			FROM $g_mantis_project_table
    		WHERE id='$f_id'";
    $result = db_query( $query );*/
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<?
	if ( $result ) {
		print_meta_redirect( $g_manage_project_menu_page, $g_wait_time );
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
	if ( $result ) {
		PRINT "Project successfully removed...<p>";
	}
	else {
		PRINT "$s_sql_error_detected <a href=\"<? echo $g_administrator_email ?>\">administrator</a><p>";
	}
?>
<p>
<a href="<? echo $g_manage_project_menu_page ?>"><? echo $s_proceed ?></a>
</div>

<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>