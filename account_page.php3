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
	db_mysql_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	### extracts the user information for the currently logged in user
	### and prefixes it with u_
    $query = "SELECT *
    		FROM $g_mantis_user_table
			WHERE cookie_string='$g_string_cookie_val'";
    $result = db_mysql_query( $query );
	$row = mysql_fetch_array( $result );
	if ( $row ) {
		extract( $row, EXTR_PREFIX_ALL, "u" );
	}
?>

<p>
<? print_menu( $g_menu_include_file ) ?>

<p>
<div align=center>
	[ <a href="<? echo $g_account_manage_profiles ?>">Manage Profiles</a> ]
</div>

<p>
<div align=center>
<table bgcolor=<? echo $g_primary_border_color ?> width=50%>
<tr>
	<td bgcolor=<? echo $g_white_color ?>>
	<table width=100% cols=3>
	<form method=post action="<? echo $g_account_update ?>">
	<input type=hidden name=f_id value="<? echo $u_id ?>">
	<input type=hidden name=f_action value="update">
	<tr>
		<td>
			<b>Edit Account</b>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dark ?>>
		<td width=40%>
			Username:
		</td>
		<td width=60%>
			<input type=text size=16 name=f_username value="<? echo $u_username ?>">
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_light ?>>
		<td>
			Email:
		</td>
		<td>
			<input type=text size=32 name=f_email value="<? echo $u_email ?>">
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dark ?>>
		<td>
			Password:
		</td>
		<td>
			<input type=password size=32 name=f_password>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_light ?>>
		<td>
			Confirm Password:
		</td>
		<td>
			<input type=password size=32 name=f_password_confirm>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dark ?>>
		<td>
			Access Level:
		</td>
		<td>
			<? echo $u_access_level ?>
		</td>
	</tr>
	<tr align=center>
		<td align=left>
			<input type=submit value=" Update User ">
		</td>
			</form>
		<td align=right>
			<form method=post action="<? echo $g_account_delete_page ?>">
			<input type=hidden name=f_id value="<? echo $u_id ?>">
			<input type=submit value="Delete Account">
		</td>
			</form>
	</tr>
	</table>
	</td>
</tr>
</table>
</div>

<? print_footer() ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>