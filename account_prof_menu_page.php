<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: account_prof_menu_page.php,v 1.37 2005-03-21 12:44:20 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	# This page allows users to add a new profile which is POSTed to
	# account_prof_add.php

	# Users can also manage their profiles
?>
<?php
	if ( isset( $g_global_profiles ) ) {
		$g_global_profiles = true;
	} else {
		$g_global_profiles = false;
	}
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'current_user_api.php' );
?>
<?php
	auth_ensure_user_authenticated();

	current_user_ensure_unprotected();
?>
<?php
	if ( $g_global_profiles ) {
		access_ensure_global_level( config_get( 'manage_global_profile_threshold' ) );
	} else {
		access_ensure_global_level( config_get( 'add_profile_threshold' ) );
	}
?>
<?php html_page_top1( lang_get( 'manage_profiles_link' ) ) ?>
<?php html_page_top2() ?>

<?php
	if ( $g_global_profiles ) {
		print_manage_menu( 'manage_prof_menu_page.php' );
	}
?>

<?php
	if ( $g_global_profiles ) {
		$t_user_id = ALL_USERS;
	} else {
		$t_user_id = auth_get_current_user_id();
	}
?>

<?php # Add Profile Form BEGIN ?>
<br />
<div align="center">
<form method="post" action="account_prof_add.php">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title">
		<input type="hidden" name="user_id" value="<?php echo $t_user_id ?>" />
		<?php echo lang_get( 'add_profile_title' ) ?>
	</td>
	<td class="right">
	<?php
		if ( !$g_global_profiles ) {
			print_account_menu( 'account_prof_menu_page.php' );
		}
	?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="25%">
		<span class="required">*</span><?php echo lang_get( 'platform' ) ?>
	</td>
	<td width="75%">
		<input type="text" name="platform" size="32" maxlength="32" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<span class="required">*</span><?php echo lang_get( 'operating_system' ) ?>
	</td>
	<td>
		<input type="text" name="os" size="32" maxlength="32" />
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<span class="required">*</span><?php echo lang_get( 'version' ) ?>
	</td>
	<td>
		<input type="text" name="os_build" size="16" maxlength="16" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'additional_description' ) ?>
	</td>
	<td>
		<textarea name="description" cols="60" rows="8" wrap="virtual"></textarea>
	</td>
</tr>
<tr>
	<td class="left">
		<span class="required"> * <?php echo lang_get( 'required' ) ?></span>
	</td>
	<td class="center">
		<input type="submit" class="button" value="<?php echo lang_get( 'add_profile_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>
<?php # Add Profile Form END ?>

<?php # Edit or Delete Profile Form BEGIN ?>
<br />
<div align="center">
<form method="post" action="account_prof_edit_page.php">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<?php echo lang_get( 'edit_or_delete_profiles_title' ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="center" colspan="2">
		<input type="radio" name="action" value="edit" checked="checked" /> <?php echo lang_get( 'edit_profile' ) ?>
<?php
	if ( !$g_global_profiles ) {
?>
		<input type="radio" name="action" value="default" /> <?php echo lang_get( 'make_default' ) ?>
<?php
	}
?>
		<input type="radio" name="action" value="delete" /> <?php echo lang_get( 'delete_profile' ) ?>
	</td>
</tr>
<tr class="row-2">
	<td class="category" width="25%">
		<?php echo lang_get( 'select_profile' ) ?>
	</td>
	<td width="75%">
		<select name="profile_id">
			<?php print_profile_option_list( $t_user_id ) ?>
		</select>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" class="button" value="<?php echo lang_get( 'submit_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>
<?php # Edit or Delete Profile Form END ?>

<?php html_page_bottom1( __FILE__ ) ?>
