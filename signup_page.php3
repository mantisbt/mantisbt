<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	### Check for invalid access to signup page
	if ( $g_allow_signup == 0 ) {
		header( "Location: $g_login_page" );
		exit;
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
<? echo $s_signup_info ?>
</div>

<? ### Signup form BEGIN ?>
<p>
<div align="center">
<table width="50%" bgcolor="<? echo $g_primary_border_color ?>" <? echo $g_primary_table_tags ?>>
<tr>
	<td bgcolor="<? echo $g_white_color ?>">
	<table cols="2" width="100%">
	<form method="post" action="<? echo $g_signup ?>">
	<tr>
		<td colspan="2" bgcolor="<? echo $g_table_title_color ?>">
			<b><? echo $s_signup_title ?></b>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dark ?>>
		<td width="25%">
			<? echo $s_username ?>:
		</td>
		<td width="75%">
			<input type="text" name="f_username" size="32" maxlength="32">
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_light ?>">
		<td>
			<? echo $s_email ?>:
		</td>
		<td>
			<input type="text" name="f_email" size="32" maxlength="64">
		</td>
	</tr>
	<tr>
		<td align="center" colspan="2">
			<input type="submit" value="<? echo $s_signup_button ?>">
		</td>
	</tr>
	</form>
	</table>
	</td>
</tr>
</table>
</div>
<? ### Signup form END ?>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>