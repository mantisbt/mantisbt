<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	# Login page POSTs results to login.php
	# Check to see if the user is already logged in via login_cookie_check()
?>
<? include( "core_API.php" ) ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	# Check to see if the user is logged in and then validate the cookie value
	if ( !empty( $g_string_cookie_val ) ) {
		login_cookie_check( $g_main_page );
	}
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>
<? print_top_page( $g_top_include_page ) ?>

<p>
<div align="center">
<?
	### Only echo error message if error variable is set
	if ( isset( $f_error ) ) {
		PRINT "$s_login_error_msg<p>";
	}

	### Display short greeting message
	echo $s_login_page_info;
?>
</div>

<? ### Login Form BEGIN ?>
<p>
<div align="center">
<form method="post" action="<? echo $g_login ?>">
<?	if (isset($f_project_id)) { ?>
<input type="hidden" name="f_project_id" value="<? echo $f_project_id ?>">
<?	} ?>
<table class="width50" cellspacing="0">
<tr>
	<td class="form-title" colspan="2">
		<? echo $s_login_title ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="25%">
		<? echo $s_username ?>:
	</td>
	<td width="75%">
		<input type="text" name="f_username" size="32" maxlength="32">
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<? echo $s_password ?>:
	</td>
	<td>
		<input type="password" name="f_password" size="16" maxlength="32">
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<? echo $s_save_login ?>:
	</td>
	<td>
		<input type="checkbox" name="f_perm_login">
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<? echo $s_login_button ?>">
	</td>
</tr>
</table>
</form>
</div>
<? ### Login Form END ?>

<? print_signup_link() ?>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>