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
	check_access( ADMINISTRATOR );

	# grab user data and prefix with u_
    $query = "SELECT *
    		FROM $g_mantis_user_table
			WHERE id='$f_id'";
    $result = db_query($query);
	$row = db_fetch_array($result);
	extract( $row, EXTR_PREFIX_ALL, "u" );
?>
<? print_page_top1() ?>
<? print_page_top2() ?>

<? print_manage_menu() ?>

<p>
<div align="center">
<table class="width50" cellspacing="1">
<form method="post" action="<? echo $g_manage_user_update ?>">
<input type="hidden" name="f_id" value="<? echo $u_id ?>">
<tr>
	<td class="form-title" colspan="3">
		<? echo $s_edit_user_title ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<? echo $s_username ?>:
	</td>
	<td colspan="2">
		<input type="text" size="16" maxlength="32" name="f_username" value="<? echo $u_username ?>">
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<? echo $s_email ?>:
	</td>
	<td colspan="2">
		<input type="text" size="32" maxlength="64" name="f_email" value="<? echo $u_email ?>">
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<? echo $s_access_level ?>:
	</td>
	<td colspan="2">
		<select name="f_access_level">
			<? print_enum_string_option_list( $s_access_levels_enum_string, $u_access_level ) ?>
		</select>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<? echo $s_enabled ?>
	</td>
	<td colspan="2">
		<input type="checkbox" name="f_enabled" <? if ( ON == $u_enabled ) echo "CHECKED" ?>>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<? echo $s_protected ?>
	</td>
	<td colspan="2">
		<input type="checkbox" name="f_protected" <? if ( ON == $u_protected ) echo "CHECKED" ?>>
	</td>
</tr>
<tr>
	<td class="center">
		<input type="submit" value="<? echo $s_update_user_button ?>">
	</td>
</form>
	<form method="post" action="<? echo $g_manage_user_reset ?>">
	<td class="center">
		<input type="hidden" name="f_id" value="<? echo $u_id ?>">
		<input type="hidden" name="f_email" value="<? echo $u_email ?>">
		<input type="hidden" name="f_protected" value="<? echo $u_protected ?>">
		<input type="submit" value="<? echo $s_reset_password_button ?>">
	</td>
	</form>
	<form method="post" action="<? echo $g_manage_user_delete_page ?>">
	<td class="center">
		<input type="hidden" name="f_id" value="<? echo $u_id ?>">
		<input type="hidden" name="f_protected" value="<? echo $u_protected ?>">
		<input type="submit" value="<? echo $s_delete_user_button ?>">
	</td>
	</form>
</tr>
</table>
</div>

<p>
<div align="center">
	<? echo $s_reset_password_msg ?>
</div>

<? print_page_bot1( __FILE__ ) ?>