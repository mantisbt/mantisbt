<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	### This file sets the bug to the chosen resolved state then gives the
	### user the opportunity to enter a reason for the closure
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	project_access_check( $f_id );
	check_access( UPDATER );
	check_bug_exists( $f_id );

	$t_handler_id = get_current_user_field( "id " );

	### Update fields
	$t_clo_val = CLOSED;
	$query = "UPDATE $g_mantis_bug_table
			SET status='$t_clo_val'
			WHERE id='$f_id'";
	$result = db_query($query);

	email_close( $f_id );
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<?
	if ( $result ) {
		print_meta_redirect( $g_view_all_bug_page, $g_wait_time );
	}
?>
<? include( $g_meta_include_file ) ?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>
<? print_top_page( $g_top_include_page ) ?>
<? print_menu( $g_menu_include_file ) ?>

<p>
<div align="center">
<?
	if ( $result ) {					### SUCCESS
		PRINT "$s_bug_close_msg<p>";
	} else {							### FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $g_view_all_bug_page, $s_proceed );
?>
</div>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>