<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	### extracts the user information for the currently logged in user
	### and prefixes it with u_
    $query = "SELECT *
    		FROM $g_mantis_user_table
			WHERE cookie_string='$g_string_cookie_val'";
    $result = db_query( $query );
	$row = db_fetch_array( $result );
	if ( $row ) {
		extract( $row, EXTR_PREFIX_ALL, "u" );
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

<p>
<? print_menu( $g_menu_include_file ) ?>

<?
	if ( access_level_check_greater_or_equal( "reporter" ) ) {
?>
<p>
<div align=center>
	[ <a href="<? echo $g_account_profile_manage_page ?>"><? echo $s_manage_profiles_link ?></a> ]
	[ <a href="<? echo $g_account_prefs_page ?>"><? echo $s_change_preferences_link ?></a> ]
</div>
<?
	}
?>
<p>
<div align=center>
<table width=50% bgcolor=<? echo $g_primary_border_color." ".$g_primary_table_tags ?>>
<tr>
	<td bgcolor=<? echo $g_white_color ?>>
	<table cols=2 width=100%>
	<form method=post action="<? echo $g_account_update ?>">
	<input type=hidden name=f_id value="<? echo $u_id ?>">
	<input type=hidden name=f_protected value="<? echo $u_protected ?>">
	<tr>
		<td colspan=2 bgcolor=<? echo $g_table_title_color ?>>
			<b><? echo $s_edit_account_title ?></b>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dark ?>>
		<td width=40%>
			<? echo $s_username ?>:
		</td>
		<td width=60%>
			<input type=text size=16 maxlength=32 name=f_username value="<? echo $u_username ?>">
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_light ?>>
		<td>
			<? echo $s_email ?>:
		</td>
		<td>
			<input type=text size=32 maxlength=64 name=f_email value="<? echo $u_email ?>">
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dark ?>>
		<td>
			<? echo $s_password ?>:
		</td>
		<td>
			<input type=password size=32 maxlength=32 name=f_password>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_light ?>>
		<td>
			<? echo $s_confirm_password ?>:
		</td>
		<td>
			<input type=password size=32 maxlength=32 name=f_password_confirm>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dark ?>>
		<td>
			<? echo $s_access_level ?>:
		</td>
		<td>
			<? echo $u_access_level ?>
		</td>
	</tr>
	<tr align=center>
		<td align=left>
			<input type=submit value="<? echo $s_update_user_button ?>">
		</td>
			</form>
		<td align=right>
			<form method=post action="<? echo $g_account_delete_page ?>">
				<input type=hidden name=f_id value="<? echo $u_id ?>">
				<input type=hidden name=f_protected value="<? echo $u_protected ?>">
				<input type=submit value="<? echo $s_delete_account_button ?>">
		</td>
			</form>
	</tr>
	</table>
	</td>
</tr>
</table>
</div>

<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>