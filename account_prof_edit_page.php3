<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	# This page allows the user to edit his/her profile
	# Changes get POSTed to account_prof_update.php3
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	$f_user_id = get_current_user_field( "id" );

	# If deleteing profile redirect to delete script
	if ( "delete" == $f_action) {
		print_header_redirect( "$g_account_profile_delete?f_id=$f_id" );
		exit;
	}
	# If Defaulting profile redirect to make default script
	else if ( "make default" == $f_action ) {
		print_header_redirect( "$g_account_profile_make_default?f_id=$f_id&f_user_id=$f_user_id" );
		exit;
	}

	# Retrieve new item data and prefix with v_
	$query = "SELECT *
		FROM $g_mantis_user_profile_table
		WHERE id='$f_id' AND user_id='$f_user_id'";
    $result = db_query( $query );
	$row = db_fetch_array( $result );
	if ( $row ) {
    	extract( $row, EXTR_PREFIX_ALL, "v" );
    }

	# Prepare for edit display
   	$v_platform 	= string_edit_text( $v_platform );
   	$v_os 			= string_edit_text( $v_os );
   	$v_os_build 	= string_edit_text( $v_os_build );
   	$v_description  = string_edit_textarea( $v_description );
?>
<? print_page_top1() ?>
<? print_page_top2() ?>

<? # Edit Profile Form BEGIN ?>
<p>
<div align="center">
<table class="width75" cellspacing="1">
<form method="post" action="<? echo $g_account_profile_update ?>">
<input type="hidden" name="f_id" value="<? echo $v_id ?>">
<tr>
	<td class="form-title">
		<? echo $s_edit_profile_title ?>
	</td>
	<td class="right">
		<? print_account_menu() ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="25%">
		<? echo $s_platform ?>
	</td>
	<td width="75%">
		<input type="text" name="f_platform" size="32" maxlength="32" value="<? echo $v_platform ?>">
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<? echo $s_operating_system ?>
	</td>
	<td>
		<input type="text" name="f_os" size="32" maxlength="32" value="<? echo $v_os ?>">
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<? echo $s_version ?>
	</td>
	<td>
		<input type="text" name="f_os_build" size="16" maxlength="16" value="<? echo $v_os_build ?>">
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<? echo $s_additional_description ?>
	</td>
	<td>
		<textarea name="f_description" cols="60" rows="8" wrap="virtual"><? echo $v_description ?></textarea>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<? echo $s_update_profile_button ?>">
	</td>
</tr>
</form>
</table>
</div>
<? # Edit Profile Form END ?>

<? print_page_bot1( __FILE__ ) ?>