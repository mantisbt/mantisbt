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
	check_access( ADMINISTRATOR );
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

<? print_manage_menu( $g_manage_create_user_page ) ?>

<p>
<div align="center">
<table class="width50" cellspacing="1">
<form method="post" action="<? echo $g_manage_create_new_user ?>">
<tr>
	<td class="form-title" colspan="2">
		<? echo $s_create_new_account_title ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="25%">
		<? echo $s_username ?>
	</td>
	<td width="75%">
		<input type="text" name="f_username" size="32" maxlength="32">
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<? echo $s_email ?>
	</td>
	<td>
		<input type="text" name="f_email" size="32" maxlength="64">
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<? echo $s_password ?>
	</td>
	<td>
		<input type="password" name="f_password" size="32" maxlength="32">
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<? echo $s_verify_password ?>
	</td>
	<td>
		<input type="password" name="f_password_verify" size="32" maxlength="32">
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<? echo $s_access_level ?>
	</td>
	<td>
		<select name="f_access_level">
			<? print_enum_string_option_list( $s_access_levels_enum_string, REPORTER ) ?>
		</select>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<? echo $s_enabled ?>
	</td>
	<td>
		<input type="checkbox" name="f_enabled" CHECKED>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<? echo $s_protected ?>
	</td>
	<td colspan="2">
		<input type="checkbox" name="f_protected">
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<? echo $s_create_user_button ?>">
	</td>
</tr>
</form>
</table>
</div>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>