<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	### Reopen the bug, set status to feedback and give the user the opportunity
	### to input a bugnote
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	project_access_check( $f_id );
	check_access( $g_reopen_bug_threshold );
	check_bug_exists( $f_id );

	### Update fields
	$t_fee_val = FEEDBACK;
	$t_reop = REOPENED;
    $query = "UPDATE $g_mantis_bug_table
    		SET status='$t_fee_val',
				resolution='$t_reop'
    		WHERE id='$f_id'";
   	$result = db_query($query);

   	email_reopen( $f_id );
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
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
		PRINT "$s_bug_reopened_msg<p>";
	} else {							### FAILURE
		print_sql_error( $query );
	}
?>

<? include( $g_view_bug_inc ) ?>

<? include( $g_bugnote_include_file ) ?>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>