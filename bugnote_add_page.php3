<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<? include( $g_meta_include_file ) ?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
?>

<p>
<? print_menu( $g_menu_include_file ) ?>

<p>
<div align=center>
[ <a href="<? echo $g_view_bug_page ?>?f_id=<? echo $f_id ?>">Back</a> ]
</div>

<? include( $g_bugnote_include_file ) ?>

<p>
<div align=center>
<table width=100% bgcolor=<? echo $g_primary_border_color." ".$g_primary_table_tags ?>>
<tr>
	<td bgcolor=<? echo $g_white_color ?>>
	<table width=100%>
	<form method=post action="<? echo $g_bugnote_add ?>">
	<input type=hidden name=f_bug_id value="<? echo $f_id ?>">
	<tr>
		<td bgcolor=<? echo $g_table_title_color ?>>
			<b><? echo $s_add_bugnote_title ?></b>
		</td>
	</tr>
	<tr>
		<td bgcolor=<? echo $g_primary_color_dark ?> align=center>
			<textarea name=f_bugnote_text cols=80 rows=10></textarea>
		</td>
	</tr>
	<tr>
		<td bgcolor=<? echo $g_primary_color_light ?> align=center>
			<input type=submit value="<? echo $s_add_bugnote_button ?>">
		</td>
	</tr>
	</form>
	</table>
	</td>
</tr>
</table>
</div>

<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>