<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: account_prof_edit_page.php,v 1.37 2005-02-25 00:23:48 jlatour Exp $
	# --------------------------------------------------------
?>
<?php
	# This page allows the user to edit his/her profile
	# Changes get POSTed to account_prof_update.php
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'current_user_api.php' );
	require_once( $t_core_path.'profile_api.php' );
?>
<?php
	auth_ensure_user_authenticated();

	current_user_ensure_unprotected();
?>
<?php
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

	if ( profile_is_global( $f_profile_id ) ) {
		access_ensure_global_level( config_get( 'manage_global_profile_threshold' ) );

		$row = profile_get_row( ALL_USERS, $f_profile_id );
	} else {
		$row = profile_get_row( auth_get_current_user_id(), $f_profile_id );
	}

   	extract( $row, EXTR_PREFIX_ALL, 'v' );
?>

<?php html_page_top1() ?>
<?php html_page_top2() ?>

<?php
	if ( profile_is_global( $f_profile_id ) ) {
		print_manage_menu();
	}
?>

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
		<?php
			if ( !profile_is_global( $f_profile_id ) ) {
				print_account_menu();
			}
		?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="25%">
		<span class="required">*</span><?php echo lang_get( 'platform' ) ?>
	</td>
	<td width="75%">
		<input type="text" name="platform" size="32" maxlength="32" value="<?php echo string_attribute( $v_platform ) ?>" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<span class="required">*</span><?php echo lang_get( 'operating_system' ) ?>
	</td>
	<td>
		<input type="text" name="os" size="32" maxlength="32" value="<?php echo string_attribute( $v_os ) ?>" />
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<span class="required">*</span><?php echo lang_get( 'version' ) ?>
	</td>
	<td>
		<input type="text" name="os_build" size="16" maxlength="16" value="<?php echo string_attribute( $v_os_build ) ?>" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'additional_description' ) ?>
	</td>
	<td>
		<textarea name="description" cols="60" rows="8" wrap="virtual"><?php echo string_textarea( $v_description ) ?></textarea>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" class="button" value="<?php echo lang_get( 'update_profile_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>
<?php # Edit Profile Form END ?>

<?php html_page_bottom1( __FILE__ ) ?>
