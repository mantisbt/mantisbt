<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	# This page allos users to add a new profile which is POSTed to
	# account_prof_add.php3

	# Users can also manage their profiles
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( REPORTER );
?>
<? print_page_top1() ?>
<? print_page_top2() ?>

<? # Add Profile Form BEGIN ?>
<p>
<div align="center">
<table class="width75" cellspacing="1">
<form method="post" action="<? echo $g_account_profile_add ?>">
<input type="hidden" name="f_user_id" value="<? echo get_current_user_field( "id " ) ?>">
<tr>
	<td class="form-title">
		<? echo $s_add_profile_title ?>
	</td>
	<td class="right">
		<? print_account_menu( $g_account_profile_menu_page ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="25%">
		<? echo $s_platform ?>
	</td>
	<td width="75%">
		<input type="text" name="f_platform" size="32" maxlength="32">
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<? echo $s_operating_system ?>
	</td>
	<td>
		<input type="text" name="f_os" size="32" maxlength="32">
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<? echo $s_version ?>
	</td>
	<td>
		<input type="text" name="f_os_build" size="16" maxlength="16">
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<? echo $s_additional_description ?>
	</td>
	<td>
		<textarea name="f_description" cols="60" rows="8" wrap="virtual"></textarea>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<? echo $s_add_profile_button ?>">
	</td>
</tr>
</form>
</table>
</div>
<? # Add Profile Form END ?>

<? # Edit or Delete Profile Form BEGIN ?>
<p>
<div align="center">
<table class="width75" cellspacing="0">
<form method="post" action="<? echo $g_account_profile_edit_page ?>">
<tr>
	<td class="form-title" colspan="2">
		<? echo $s_edit_or_delete_profiles_title ?>
	</td>
</tr>
<tr class="row-1">
	<td class="center" colspan="2">
		<input type="radio" name="f_action" value="edit" CHECKED> <? echo $s_edit_profile ?>
		<input type="radio" name="f_action" value="make default"> <? echo $s_make_default ?>
		<input type="radio" name="f_action" value="delete"> <? echo $s_delete_profile ?>
	</td>
</tr>
<tr class="row-2">
	<td class="category" width="25%">
		<? echo $s_select_profile ?>
	</td>
	<td width="75%">
		<select name="f_id">
			<? print_profile_option_list( get_current_user_field( "id " ) ) ?>
		</select>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<? echo $s_submit_button ?>">
	</td>
	</form>
</tr>
</table>
</div>
<? # Edit or Delete Profile Form END ?>

<? print_page_bot1( __FILE__ ) ?>