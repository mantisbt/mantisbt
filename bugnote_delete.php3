<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	### Remove the bugnote and bugnote text and redirect back to
	### the viewing page
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	project_access_check( $f_id );
	check_access( REPORTER );

	# $f_bug_id is the bug id
	# @@@ WHat does this do?
	check_bugnote_exists( $f_id );

	### grab the bugnote text id
	$query = "SELECT bugnote_text_id
			FROM $g_mantis_bugnote_table
			WHERE id='$f_bugnote_id'";
	$result = db_query( $query );
	$t_bugnote_text_id = db_result( $result, 0, 0 );

	### Remove the bugnote
	$query = "DELETE
			FROM $g_mantis_bugnote_table
			WHERE id='$f_bugnote_id'";
	$result = db_query($query);

	### Remove the bugnote text
	$query = "DELETE
			FROM $g_mantis_bugnote_text_table
			WHERE id='$t_bugnote_text_id'";
	$result = db_query($query);

	# Determine which view page to redirect back to.
	$t_redirect_url = get_view_redirect_url( $f_id );
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<?
	if ( $result ) {
		print_meta_redirect( $t_redirect_url, $g_wait_time );
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
		PRINT "$s_bugnote_deleted_msg<p>";
	} else {							### FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $t_redirect_url, $s_proceed );
?>
</div>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>