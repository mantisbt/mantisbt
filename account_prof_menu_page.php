<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# This page allos users to add a new profile which is POSTed to
	# account_prof_add.php

	# Users can also manage their profiles
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	check_access( REPORTER );

	# protected account check
	if ( current_user_is_protected() ) {
		trigger_error( ERROR_PROTECTED_ACCOUNT, ERROR );
	}
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php # Add Profile Form BEGIN ?>
<br />
<div align="center">
<form method="post" action="account_prof_add.php">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title">
		<input type="hidden" name="f_user_id" value="<?php echo auth_get_current_user_id() ?>" />
		<?php echo lang_get( 'add_profile_title' ) ?>
	</td>
	<td class="right">
		<?php print_account_menu( 'account_prof_menu_page.php' ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="25%">
		<span class="required">*</span><?php echo lang_get( 'platform' ) ?>
	</td>
	<td width="75%">
		<input type="text" name="f_platform" size="32" maxlength="32" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<span class="required">*</span><?php echo lang_get( 'operating_system' ) ?>
	</td>
	<td>
		<input type="text" name="f_os" size="32" maxlength="32" />
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<span class="required">*</span><?php echo lang_get( 'version' ) ?>
	</td>
	<td>
		<input type="text" name="f_os_build" size="16" maxlength="16" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<span class="required">*</span><?php echo lang_get( 'additional_description' ) ?>
	</td>
	<td>
		<textarea name="f_description" cols="60" rows="8" wrap="virtual"></textarea>
	</td>
</tr>
<tr>
	<td class="left">
		<span class="required"> * <?php echo lang_get( 'required' ) ?></span>
	</td>
	<td class="center">
		<input type="submit" value="<?php echo lang_get( 'add_profile_button' ) ?>" />
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
		<input type="radio" name="f_action" value="edit" checked="checked" /> <?php echo lang_get( 'edit_profile' ) ?>
		<input type="radio" name="f_action" value="default" /> <?php echo lang_get( 'make_default' ) ?>
		<input type="radio" name="f_action" value="delete" /> <?php echo lang_get( 'delete_profile' ) ?>
	</td>
</tr>
<tr class="row-2">
	<td class="category" width="25%">
		<?php echo lang_get( 'select_profile' ) ?>
	</td>
	<td width="75%">
		<select name="f_profile_id">
			<?php print_profile_option_list( auth_get_current_user_id() ) ?>
		</select>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<?php echo lang_get( 'submit_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>
<?php # Edit or Delete Profile Form END ?>

<?php print_page_bot1( __FILE__ ) ?>
