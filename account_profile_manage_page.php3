<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_mysql_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	if ( !access_level_check_greater_or_equal( "reporter" ) ) {
		### need to replace with access error page
		header( "Location: $g_logout_page" );
		exit;
	}

	### Get user information and prefix with u_
	$query = "SELECT id
		FROM $g_mantis_user_table
		WHERE cookie_string='$g_string_cookie_val'";
    $result = db_mysql_query($query);
    $u_id = mysql_result( $result, 0 );
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

<p>
<div align=center>
<table width=75% bgcolor=<? echo $g_primary_border_color." ".$g_primary_table_tags ?>>
<tr>
	<td bgcolor=<? echo $g_white_color ?>>
	<table cols=2 width=100%>
	<form method=post action="<? echo $g_account_profile_add ?>">
	<input type=hidden name=f_user_id value="<? echo $u_id ?>">
	<tr>
		<td colspan=2 bgcolor=<? echo $g_table_title_color ?>>
			<b>Add Profile</b>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dark ?>>
		<td width=25%>
			Platform
		</td>
		<td width=75%>
			<input type=text name=f_platform size=32 maxlength=32>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_light ?>>
		<td>
			Operating System
		</td>
		<td>
			<input type=text name=f_os size=32 maxlength=32>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dark ?>>
		<td>
			Version/Build
		</td>
		<td>
			<input type=text name=f_os_build size=16 maxlength=16>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_light ?>>
		<td>
			Additional Description
		</td>
		<td>
			<textarea name=f_description cols=60 rows=8></textarea>
		</td>
	</tr>
	<tr>
		<td align=center colspan=2>
			<input type=submit value=" Add Profile ">
		</td>
	</tr>
	</form>
	</table>
	</td>
</tr>
</table>
</div>

<p>
<div align=center>
<table width=75% bgcolor=<? echo $g_primary_border_color." ".$g_primary_table_tags ?>>
<tr>
	<td bgcolor=<? echo $g_white_color ?>>
	<table width=100%>
	<form method=post action="<? echo $g_account_profile_edit_page ?>">
	<input type=hidden name=f_user_id value="<? echo $u_id ?>">
	<tr>
		<td colspan=2 bgcolor=<? echo $g_table_title_color ?>>
			<b>Edit or Delete Profiles</b>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dark ?>>
		<td align=center colspan=2>
			<input type=radio name=f_action value="edit" CHECKED> Edit Profile
			<input type=radio name=f_action value="make default"> Make Default
			<input type=radio name=f_action value="delete"> Delete Profile
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_light ?> align=center>
		<td valign=top width=25%>
			Select Profile
		</td>
		<td width=75%>
			<select name=f_id>
				<? print_profiles( $u_id ) ?>
			</select>
		</td>
	</tr>
	<tr>
		<td align=center colspan=2>
			<input type=submit value=" Submit ">
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