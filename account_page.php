<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Users may change their user information from this page.
	# The data is POSTed to account_update.php3
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	# get protected state
	$t_protected = current_user_get_field( 'protected' );

	# protected account check
	if ( ON == $t_protected ) {
		trigger_error( ERROR_PROTECTED_ACCOUNT, ERROR );
	}

	# extracts the user information for the currently logged in user
	# and prefixes it with u_
    $row = user_get_row( auth_get_current_user_id() );
	extract( $row, EXTR_PREFIX_ALL, 'u' );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php # Edit Account Form BEGIN ?>
<br />
<div align="center">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title">
		<form method="post" action="account_update.php">
		<?php echo lang_get( 'edit_account_title' ) ?>
	</td>
	<td class="right">
		<?php print_account_menu( 'account_page.php' ) ?>
	</td>
</tr>
<?php # using LDAP accounts
	if ( LDAP == $g_login_method ) {
?>
<tr class="row-2">
	<td colspan="2">
		The password settings are controlled by your LDAP entry,<br />
		hence cannot be edited here.
	</td>
</tr>
<tr class="row-2">
	<td class="category" width="25%">
	    <?php echo lang_get( 'username' ) ?>:
	</td>
	<td width="75%">
	    <?php echo $u_username ?>
	</td>
</tr>
<?php
	if ( ON == $g_use_ldap_email ) {
    	$u_email = user_get_email( $u_username );
?>
<tr class="row-2">
	<td class="category">
	    <?php echo lang_get( 'email' ) ?>:
	</td>
	<td>
	    <?php echo $u_email ?>
	</td>
</tr>
<?php } else { ?>
<tr class="row-2">
	<td class="category">
	    <?php echo lang_get( 'email' ) ?>:
	</td>
	<td>
	    <?php print_email_input( 'f_email', $u_email ) ?>
	</td>
</tr>
<?php } ?>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'access_level' ) ?>:
	</td>
	<td>
		<?php echo get_enum_element( 'access_levels', $u_access_level ) ?>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'access_level_project' ) ?>:
	</td>
	<td>
		<?php echo get_enum_element( 'access_levels', current_user_get_access_level() ) ?>
	</td>
</tr>
<tr class="row-1" valign="top">
	<td class="category">
		<?php echo lang_get( 'assigned_projects' ) ?>:
	</td>
	<td>
		<?php print_project_user_list( current_user_get_field( 'id' ) ) ?>
	</td>
</tr>
<tr>
	<td class="center">
		<input type="submit" value="<?php echo lang_get( 'update_user_button' ) ?>" />
		</form>
	</td>
	<td class="center">
		<form method="post" action="account_delete_page.php">
		<input type="submit" value="<?php echo lang_get( 'delete_account_button' ) ?>" />
		</form>
	</td>
</tr>

<?php 	} else { # end LDAP section ?>

<tr class="row-1">
	<td class="category" width="25%">
		<?php echo lang_get( 'username' ) ?>:
	</td>
	<td width="75%">
		<?php echo $u_username ?>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'password' ) ?>:
	</td>
	<td>
		<input type="password" size="32" maxlength="32" name="f_password" />
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'confirm_password' ) ?>:
	</td>
	<td>
		<input type="password" size="32" maxlength="32" name="f_password_confirm" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
	    <?php echo lang_get( 'email' ) ?>:
	</td>
	<td>
		<?php print_email_input( 'f_email', $u_email ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'access_level' ) ?>:
	</td>
	<td>
		<?php echo get_enum_element( 'access_levels', $u_access_level ) ?>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'access_level_project' ) ?>:
	</td>
	<td>
		<?php echo get_enum_element( 'access_levels', current_user_get_access_level() ) ?>
	</td>
</tr>
<tr class="row-1" valign="top">
	<td class="category">
		<?php echo lang_get( 'assigned_projects' ) ?>:
	</td>
	<td>
		<?php print_project_user_list( current_user_get_field( 'id' ) ) ?>
	</td>
</tr>
<tr>
	<td class="left">
		<input type="submit" value="<?php echo lang_get( 'update_user_button' ) ?>" />
		</form>
	</td>
<?php
		# check if users can't delete their own accounts
		if ( ON == $g_allow_account_delete ) {
?>
	<td class="right">
		<form method="post" action="account_delete_page.php">
		<input type="submit" value="<?php echo lang_get( 'delete_account_button' ) ?>" />
		</form>
	</td>
<?php 	} else { ?>
	<td>
		&nbsp;
	</td>
<?php 	} ?>
</tr>
<?php } # end LDAP else ?>
</table>
</div>
<?php # Edit Account Form END ?>

<?php print_page_bot1( __FILE__ ) ?>
