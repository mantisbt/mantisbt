<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
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
<? echo $s_login_error_msg ?>
</div>

<p>
<div align="center">
<form method=post action="<? echo $g_login ?>">
<table width="50%" bgcolor="<? echo $g_primary_border_color ?>" <? echo $g_primary_table_tags ?>>
<tr>
	<td bgcolor="<? echo $g_white_color ?>">
	<table width="100%">
	<tr>
		<td colspan=2 bgcolor="<? echo $g_table_title_color ?>">
			<b><? echo $s_login_title ?></b>
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_dark ?>">
		<td width="25%">
			<? echo $s_username ?>:
		</td>
		<td width="75%">
			<input type="text" name="f_username" size="32" maxlength="32">
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_dark ?>">
		<td>
			<? echo $s_password ?>:
		</td>
		<td>
			<input type="password" name="f_password" size="16" maxlength="32">
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_dark ?>">
		<td>
			<? echo $s_save_login ?>:
		</td>
		<td>
			<input type="checkbox" name="f_perm_login">
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_light ?>">
		<td>
			<? echo $s_choose_project ?>:
		</td>
		<td>
			<select name="f_project_id">
			<? print_project_option_list() ?>
			</select>
		</td>
	</tr>
	<tr>
		<td align="center" colspan="2">
			<input type="submit" value="<? echo $s_login_button ?>">
		</td>
	</tr>
	</table>
	</td>
</tr>
</table>
</form>

<? if ( $g_allow_signup != "0" ) { ?>
<p>
<a href="<? echo $g_signup_page ?>"><? echo $s_signup_link ?></a>
<? } ?>
</div>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>