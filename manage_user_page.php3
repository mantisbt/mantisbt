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
	check_access( MANAGER );

	### grab user data and prefix with u_
    $query = "SELECT *
    		FROM $g_mantis_user_table
			WHERE id='$f_id'";
    $result = db_query($query);
	$row = db_fetch_array($result);
	extract( $row, EXTR_PREFIX_ALL, "u" );
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

<? print_manage_menu() ?>

<p>
<div align="center">
<table width="50%" bgcolor="<? echo $g_primary_border_color ?>" <? echo $g_primary_table_tags ?>>
<tr>
	<td bgcolor="<? echo $g_white_color ?>">
	<table width="100%">
	<form method="post" action="<? echo $g_manage_user_update ?>">
	<input type="hidden" name="f_id" value="<? echo $u_id ?>">
	<tr>
		<td colspan="3" bgcolor="<? echo $g_table_title_color ?>">
			<b><? echo $s_edit_user_title ?></b>
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_dark ?>">
		<td>
			<? echo $s_username ?>:
		</td>
		<td colspan="2">
			<input type="text" size="16" maxlength="32" name="f_username" value="<? echo $u_username ?>">
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_light ?>">
		<td>
			<? echo $s_email ?>:
		</td>
		<td colspan="2">
			<input type="text" size="32" maxlength="64" name="f_email" value="<? echo $u_email ?>">
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_dark ?>">
		<td>
			<? echo $s_access_level ?>:
		</td>
		<td colspan="2">
			<select name="f_access_level">
				<? print_enum_string_option_list( $s_access_levels_enum_string, $u_access_level ) ?>
			</select>
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_light ?>">
		<td>
			<? echo $s_enabled ?>
		</td>
		<td colspan="2">
			<input type="checkbox" name="f_enabled" <? if ( $u_enabled==1 ) echo "CHECKED" ?>>
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_dark ?>">
		<td>
			<? echo $s_protected ?>
		</td>
		<td colspan="2">
			<input type="checkbox" name="f_protected" <? if ( $u_protected==1 ) echo "CHECKED" ?>>
		</td>
	</tr>
	<tr align="center">
		<td>
			<input type="submit" value="<? echo $s_update_user_button ?>">
		</td>
		</form>
		<form method="post" action="<? echo $g_manage_user_reset ?>">
		<td>
			<input type="hidden" name="f_id" value="<? echo $u_id ?>">
			<input type="hidden" name="f_email" value="<? echo $u_email ?>">
			<input type="hidden" name="f_protected" value="<? echo $u_protected ?>">
			<input type="submit" value="<? echo $s_reset_password_button ?>">
		</td>
		</form>
		<form method="post" action="<? echo $g_manage_user_delete_page ?>">
		<td>
			<input type="hidden" name="f_id" value="<? echo $u_id ?>">
			<input type="hidden" name="f_protected" value="<? echo $u_protected ?>">
			<input type="submit" value="<? echo $s_delete_user_button ?>">
		</td>
		</form>
	</tr>
	</table>
	</td>
</tr>
</table>
</div>

<p>
<div align="center">
	<? echo $s_reset_password_msg ?>
</div>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>