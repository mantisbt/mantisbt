<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: account_page.php,v 1.51 2005-07-17 21:56:21 thraxisp Exp $
	# --------------------------------------------------------

	# CALLERS
	#	This page is called from:
	#	- print_menu()
	#	- print_account_menu()
	#	- header redirects from account_*.php
	#   - included by verify.php to allow user to change their password

	# EXPECTED BEHAVIOUR
	#	- Display the user's current settings
	#	- Allow the user to edit their settings
	#	- Allow the user to save their changes
	#	- Allow the user to delete their account if account deletion is enabled

	# CALLS
	#	This page calls the following pages:
	#	- account_update.php  (to save changes)
	#	- account_delete.php  (to delete the user's account)

	# RESTRICTIONS & PERMISSIONS
	#	- User must be authenticated
	#	- The user's account must not be protected

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'current_user_api.php' );

	#============ Parameters ============
	# (none)

	#============ Permissions ============
	auth_ensure_user_authenticated();

	current_user_ensure_unprotected();
?>
<?php

	# extracts the user information for the currently logged in user
	# and prefixes it with u_
	$row = user_get_row( auth_get_current_user_id() );
	extract( $row, EXTR_PREFIX_ALL, 'u' );

	$t_ldap = ( LDAP == config_get( 'login_method' ) );

	# In case we're using LDAP to get the email address... this will pull out
	#  that version instead of the one in the DB
	$u_email = user_get_email( $u_id, $u_username );
	
	# note if we are being included by a script of a different name, if so,
	#  this is a mandatory password change request
	$t_force_pw_reset = is_page_name( 'verify.php' );

	html_page_top1( lang_get( 'account_link' ) );
	html_page_top2();
?>

<!-- # Edit Account Form BEGIN -->
<br />
<?php if ( $t_force_pw_reset ) { ?>
<center><div style="color:red; width:75%">
		<?php 
			echo lang_get( 'verify_warning' ); 
			if ( helper_call_custom_function( 'auth_can_change_password', array() ) ) {
				echo '<br />' . lang_get( 'verify_change_password' );
			}
		?>
</div></center>
<br />
<?php } ?>
<div align="center">
<form method="post" action="account_update.php">
<table class="width75" cellspacing="1">

	<!-- Headings -->
	<tr>
		<td class="form-title">
			<?php echo lang_get( 'edit_account_title' ) ?>
		</td>
		<td class="right">
			<?php print_account_menu( 'account_page.php' ) ?>
		</td>
	</tr>

<?php if ( !helper_call_custom_function( 'auth_can_change_password', array() ) ) { ?> <!-- With LDAP -->

	<!-- Username -->
	<tr class="row-1">
		<td class="category" width="25%">
			<?php echo lang_get( 'username' ) ?>
		</td>
		<td width="75%">
			<?php echo $u_username ?>
		</td>
	</tr>

	<!-- Password -->
	<tr class="row-2">
		<td class="category">
			<?php echo lang_get( 'password' ) ?>
		</td>
		<td>
			<?php echo lang_get( 'no_password_change' ) ?>
		</td>
	</tr>

<?php } else { ?> <!-- Without LDAP -->

	<!-- Username -->
	<tr class="row-1">
		<td class="category" width="25%">
			<?php echo lang_get( 'username' ) ?>
		</td>
		<td width="75%">
			<?php echo $u_username ?>
		</td>
	</tr>

	<!-- Password -->
	<tr class="row-2">
		<td class="category">
			<?php echo lang_get( 'password' ) ?>
			<?php if ( $t_force_pw_reset ) { ?>
			<span class="required">*</span>
			<?php } ?>
		</td>
		<td>
			<input type="password" size="32" maxlength="32" name="password" />
		</td>
	</tr>

	<!-- Password confirmation -->
	<tr class="row-1">
		<td class="category">
			<?php echo lang_get( 'confirm_password' ) ?>
			<?php if ( $t_force_pw_reset ) { ?>
			<span class="required">*</span>
			<?php } ?>
		</td>
		<td>
			<input type="password" size="32" maxlength="32" name="password_confirm" />
		</td>
	</tr>

<?php } ?> <!-- End LDAP conditional -->

<?php if ( $t_ldap && ON == config_get( 'use_ldap_email' ) ) { ?> <!-- With LDAP Email-->

	<!-- Email -->
	<tr class="row-1">
		<td class="category">
			<?php echo lang_get( 'email' ) ?>
		</td>
		<td>
			<?php echo $u_email ?>
		</td>
	</tr>

<?php } else { ?> <!-- Without LDAP Email -->

	<!-- Email -->
	<tr class="row-2">
		<td class="category">
			<?php echo lang_get( 'email' ) ?>
		</td>
		<td>
			<?php print_email_input( 'email', $u_email ) ?>
		</td>
	</tr>

<?php } ?> <!-- End LDAP Email conditional -->

	<!-- Realname -->
	<tr class="row-1" valign="top">
		<td class="category">
			<?php echo lang_get( 'realname' ) ?>
		</td>
		<td>
			<input type="text" size="32" maxlength="64" name="realname" value="<?php echo $u_realname ?>" />
		</td>
	</tr>

	<!-- Access level -->
	<tr class="row-2">
		<td class="category">
			<?php echo lang_get( 'access_level' ) ?>
		</td>
		<td>
			<?php echo get_enum_element( 'access_levels', $u_access_level ) ?>
		</td>
	</tr>

	<!-- Project access level -->
	<tr class="row-1">
		<td class="category">
			<?php echo lang_get( 'access_level_project' ) ?>
		</td>
		<td>
			<?php echo get_enum_element( 'access_levels', current_user_get_access_level() ) ?>
		</td>
	</tr>

	<!-- Assigned project list -->
	<tr class="row-2" valign="top">
		<td class="category">
			<?php echo lang_get( 'assigned_projects' ) ?>
		</td>
		<td>
			<?php print_project_user_list( auth_get_current_user_id(), false ) ?>
		</td>
	</tr>

	<!-- BUTTONS -->
	<tr>
		<td class="left">
			<?php if ( $t_force_pw_reset ) { ?>
			<span class="required"> * <?php echo lang_get( 'required' ) ?></span>
			<?php } ?>
		</td>
		<!-- Update Button -->
		<td>
			<input type="submit" class="button" value="<?php echo lang_get( 'update_user_button' ) ?>" />
		</td>
	</tr>
</table>
</form>
</div>

<br />
<?php # Delete Account Form BEGIN ?>
<?php
	# check if users can't delete their own accounts
	if ( ON == config_get( 'allow_account_delete' ) ) {
?>

<!-- Delete Button -->
<div class="border-center">
	<form method="post" action="account_delete.php">
	<input type="submit" class="button" value="<?php echo lang_get( 'delete_account_button' ) ?>" />
	</form>
</div>

<?php } ?>
<?php # Delete Account Form END ?>

<?php html_page_bottom1( __FILE__ ) ?>
