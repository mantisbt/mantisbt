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

<p>
<? print_menu( $g_menu_include_file ) ?>

<p>
<div align=center>
<table width=50% bgcolor=<? echo $g_primary_border_color." ".$g_primary_table_tags ?>>
<tr>
	<td bgcolor=<? echo $g_white_color ?>>
	<table width=100% bgcolor=<? echo $g_white_color ?>>
	<tr>
		<td bgcolor=<? echo $g_table_title_color ?>>
			<b><? echo $s_edit_project_category_title ?></b>
		</td>
	</tr>
	<tr align=center bgcolor=<? echo $g_primary_color_dark ?>>
		<form method=post action="<? echo $g_manage_project_category_update ?>">
		<input type=hidden name=f_project_id value="<? echo $f_project_id ?>">
		<input type=hidden name=f_orig_category value="<? echo $f_category ?>">
		<td colspan=2>
			<input type=text name=f_category size=32 maxlength=32 value="<? echo $f_category ?>">
		</td>
	</tr>
	<tr align=center bgcolor=<? echo $g_white_color ?>>
		<td width=50%>
			<input type=submit value="<? echo $s_update_category_button ?>">
		</td>
		</form>
		<form method=post action="<? echo $g_manage_project_category_delete_page ?>">
		<input type=hidden name=f_project_id value="<? echo $f_project_id ?>">
		<input type=hidden name=f_category value="<? echo $f_category ?>">
		<td width=50%>
			<input type=submit value="<? echo $s_delete_category_button ?>">
		</td>
		</form>
	</tr>
	</table>
	</td>
</tr>
</form>
</table>
</div>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>