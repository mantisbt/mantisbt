<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# This page allows the user to edit his/her profile
	# Changes get POSTed to account_prof_update.php3
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	# protected account check
	if ( current_user_is_protected() ) {
		trigger_error( ERROR_PROTECTED_ACCOUNT, ERROR );
	}

	$f_id		= gpc_get_int( 'f_id' );
	$f_action	= gpc_get_string( 'f_action' );

	# If deleteing profile redirect to delete script
	if ( 'delete' == $f_action) {
		print_header_redirect( 'account_prof_delete.php?f_id=' . $f_id );
	}
	# If Defaulting profile redirect to make default script
	else if ( 'make default' == $f_action ) {
		print_header_redirect( 'account_prof_make_default.php?f_id=' . $f_id );
	}

	$c_id = db_prepare_int( $f_id );

	$t_user_id = auth_get_current_user_id();

	$t_user_profile_table = config_get( 'mantis_user_profile_table' );

	# Retrieve new item data and prefix with v_
	$query = "SELECT *
		FROM $t_user_profile_table
		WHERE id='$c_id' AND user_id='$t_user_id'";
    $result = db_query( $query );
	$row = db_fetch_array( $result );
	if ( $row ) {
    	extract( $row, EXTR_PREFIX_ALL, 'v' );
    }

	# Prepare for edit display
   	$v_platform 	= string_edit_text( $v_platform );
   	$v_os 			= string_edit_text( $v_os );
   	$v_os_build 	= string_edit_text( $v_os_build );
   	$v_description  = string_edit_textarea( $v_description );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php # Edit Profile Form BEGIN ?>
<br />
<div align="center">
<form method="post" action="account_prof_update.php">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title">
		<input type="hidden" name="f_id" value="<?php echo $v_id ?>" />
		<?php echo lang_get( 'edit_profile_title' ) ?>
	</td>
	<td class="right">
		<?php print_account_menu() ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="25%">
		<?php echo lang_get( 'platform' ) ?>
	</td>
	<td width="75%">
		<input type="text" name="f_platform" size="32" maxlength="32" value="<?php echo $v_platform ?>" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'operating_system' ) ?>
	</td>
	<td>
		<input type="text" name="f_os" size="32" maxlength="32" value="<?php echo $v_os ?>" />
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'version' ) ?>
	</td>
	<td>
		<input type="text" name="f_os_build" size="16" maxlength="16" value="<?php echo $v_os_build ?>" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'additional_description' ) ?>
	</td>
	<td>
		<textarea name="f_description" cols="60" rows="8" wrap="virtual"><?php echo $v_description ?></textarea>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<?php echo lang_get( 'update_profile_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>
<?php # Edit Profile Form END ?>

<?php print_page_bot1( __FILE__ ) ?>
