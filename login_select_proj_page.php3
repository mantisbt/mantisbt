<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	### Allows the user to select a project that is visible to him
?>
<? include( "core_API.php" ) ?>
<? login_user_check_only() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
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

<? ### Project Select Form BEGIN ?>
<p>
<div align="center">
<form method="post" action="<? echo $g_set_project ?>">
<table width="50%" bgcolor="<? echo $g_primary_border_color ?>" <? echo $g_primary_table_tags ?>>
<tr>
	<td bgcolor="<? echo $g_white_color ?>">
	<table width="100%">
	<tr>
		<td colspan="2" bgcolor="<? echo $g_table_title_color ?>">
			<b><? echo $s_login_title ?></b>
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_light ?>">
		<td width="40%">
			<? echo $s_choose_project ?>:
		</td>
		<td width="60%">
			<select name="f_project_id">
			<? print_project_option_list() ?>
			</select>
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_dark ?>">
		<td width="40%">
			<? echo $s_make_default ?>:
		</td>
		<td width="60%">
			<input type="checkbox" name="f_make_default">
		</td>
	</tr>
	<tr align="center">
		<td colspan="2">
			<input type="submit" value="<? echo $s_select_project_button ?>">
		</td>
	</tr>
	</table>
	</td>
</tr>
</table>
</form>
</div>
<? ### Project Select Form END ?>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>