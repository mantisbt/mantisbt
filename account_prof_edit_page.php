<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: account_prof_edit_page.php,v 1.25 2003-01-23 23:02:49 jlatour Exp $
	# --------------------------------------------------------
?>
<?php
	# This page allows the user to edit his/her profile
	# Changes get POSTed to account_prof_update.php
?>
<?php
	require_once( 'core.php' );
	
	require_once( $g_core_path . 'current_user_api.php' );
	require_once( $g_core_path . 'profile_api.php' );
?>
<?php login_cookie_check() ?>
<?php
	# protected account check
	current_user_ensure_unprotected();

	$f_profile_id	= gpc_get_int( 'profile_id' );
	$f_action		= gpc_get_string( 'action' );

	# If deleteing profile redirect to delete script
	if ( 'delete' == $f_action) {
		print_header_redirect( 'account_prof_delete.php?profile_id=' . $f_profile_id );
	}
	# If Defaulting profile redirect to make default script
	else if ( 'default' == $f_action ) {
		print_header_redirect( 'account_prof_make_default.php?profile_id=' . $f_profile_id );
	}

	$row = profile_get_row( auth_get_current_user_id(), $f_profile_id );

   	extract( $row, EXTR_PREFIX_ALL, 'v' );
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
		<input type="hidden" name="profile_id" value="<?php echo $v_id ?>" />
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
		<input type="text" name="platform" size="32" maxlength="32" value="<?php echo string_edit_text( $v_platform ) ?>" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'operating_system' ) ?>
	</td>
	<td>
		<input type="text" name="os" size="32" maxlength="32" value="<?php echo string_edit_text( $v_os ) ?>" />
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'version' ) ?>
	</td>
	<td>
		<input type="text" name="os_build" size="16" maxlength="16" value="<?php echo string_edit_text( $v_os_build ) ?>" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'additional_description' ) ?>
	</td>
	<td>
		<textarea name="description" cols="60" rows="8" wrap="virtual"><?php echo string_edit_textarea( $v_description ) ?></textarea>
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
