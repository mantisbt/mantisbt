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
	check_access( UPDATER );
	check_bug_exists( $f_id );
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

<? ### Resolve Form BEGIN ?>
<p>
<div align="center">
<table width="50%" bgcolor="<? echo $g_primary_border_color ?>" <? echo $g_primary_table_tags ?>>
<tr>
	<td bgcolor="<? echo $g_white_color ?>">
	<table cols="2" width="100%">
	<form method="post" action="<? echo $g_bug_resolve_page2 ?>">
	<input type="hidden" name="f_id" value="<? echo $f_id ?>">
	<tr>
		<td colspan="2" bgcolor="<? echo $g_table_title_color ?>">
			<b><? echo $s_resolve_bug_title ?></b>
		</td>
	</tr>
	<tr>
		<td bgcolor="<? echo $g_category_title_color ?>">
			<b><? echo $s_resolution ?></b>
		</td>
		<td bgcolor="<? echo $g_primary_color_dark ?>">
			<select name="f_resolution">
				<? print_enum_string_option_list( $s_resolution_enum_string, FIXED ) ?>
			</select>
		</td>
	</tr>
	<tr>
		<td bgcolor="<? echo $g_category_title_color ?>">
			<b><? echo $s_duplicate_id ?></b>
		</td>
		<td bgcolor="<? echo $g_primary_color_light ?>">
			<input type="text" name="f_duplicate_id" maxlength="7">
		</td>
	</tr>
	<tr>
		<td align="center" colspan="2" bgcolor="<? echo $g_primary_color_light ?>">
			<input type="submit" value="<? echo $s_resolve_bug_button ?>">
		</td>
	</tr>
	</form>
	</table>
	</td>
</tr>
</table>
</div>
<? ### Resolve Form END ?>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>