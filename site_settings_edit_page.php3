<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	### WARNING: Only use this page if you know exactly what you are doing and
	### are aware of the security risks involved
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( ADMINISTRATOR );

	$fd = fopen( "config_inc.php", "r" );
	$buffer = fread( $fd, filesize( "config_inc.php" ) );
	fclose( $fd );
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<? include( $g_meta_include_file ) ?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>

<? print_menu( $g_menu_include_file ) ?>

<p>
<div align="center">
	<? print_bracket_link( $g_site_settings_page, "Back" ) ?>
</div>

<p>
<div align="center">
<table width="100%" bgcolor="<? echo $g_primary_border_color ?>" <? echo $g_primary_table_tags ?>>
<tr>
	<td bgcolor="<? echo $g_white_color ?>">
	<table width="100%">
	<form method="post" action="<? echo $g_site_settings_update ?>">
	<tr>
		<td bgcolor="<? echo $g_table_title_color ?>">
			<b><? echo $s_edit_site_settings_title ?></b>
		</td>
	</tr>
	<tr align="center">
		<td bgcolor="<? echo $g_table_title_color ?>">
			<textarea name="f_text" rows="30" cols="90" wrap="virtual"><? echo $buffer ?></textarea>
		</td>
	</tr>
	<tr align="center">
		<td bgcolor="<? echo $g_table_title_color ?>">
			<input type="submit" value="<? echo $s_save_settings_button ?>">
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