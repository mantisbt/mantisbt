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
	$t_protected = get_current_user_field( 'protected' );

	# protected account check
	if ( ON == $t_protected ) {
		print_mantis_error( ERROR_PROTECTED_ACCOUNT );
	}

	# extracts the user information for the currently logged in user
	# and prefixes it with u_
    $query = "SELECT *
    		FROM $g_mantis_user_table
			WHERE cookie_string='$g_string_cookie_val'";
    $result = db_query( $query );
	$row = db_fetch_array( $result );
	extract( $row, EXTR_PREFIX_ALL, 'u' );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php # Edit Account Form BEGIN ?>
<p>
<div align="center">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title">
		<form method="post" action="account_update.php">
		<?php echo $s_edit_account_title ?>
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
	    <?php echo $s_username ?>:
	</td>
	<td width="75%">
	    <?php echo $u_username ?>
	</td>
</tr>
<?php
	if ( ON == $g_use_ldap_email ) {
    	$u_email = get_user_info( $u_username, 'email' );
?>
<tr class="row-2">
	<td class="category">
	    <?php echo $s_email ?>:
	</td>
	<td>
	    <?php echo $u_email ?>
	</td>
</tr>
<?php } else { ?>
<tr class="row-2">
	<td class="category">
	    <?php echo $s_email ?>:
	</td>
	<td>
	    <input type="text" size="32" maxlength="64" name="f_email" value="<?php echo $u_email ?>">
	</td>
</tr>
<?php } ?>
<tr class="row-1">
	<td class="category">
		<?php echo $s_access_level ?>:
	</td>
	<td>
		<?php echo get_enum_element( 'access_levels', $u_access_level ) ?>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_access_level_project ?>:
	</td>
	<td>
		<?php echo get_enum_element( 'access_levels', get_current_user_access_level() ) ?>
	</td>
</tr>
<tr class="row-1" valign="top">
	<td class="category">
		<?php echo $s_assigned_projects ?>:
	</td>
	<td>
		<?php print_project_user_list( get_current_user_field( 'id' ) ) ?>
	</td>
</tr>
<tr>
	<td class="center">
		<input type="submit" value="<?php echo $s_update_user_button ?>">
		</form>
	</td>
	<td class="center">
		<form method="post" action="account_delete_page.php">
		<input type="submit" value="<?php echo $s_delete_account_button ?>">
		</form>
	</td>
</tr>

<?php 	} else { # end LDAP section ?>

<tr class="row-1">
	<td class="category" width="25%">
		<?php echo $s_username ?>:
	</td>
	<td width="75%">
		<input type="text" size="16" maxlength="32" name="f_username" value="<?php echo $u_username ?>">
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_password ?>:
	</td>
	<td>
		<input type="password" size="32" maxlength="32" name="f_password">
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_confirm_password ?>:
	</td>
	<td>
		<input type="password" size="32" maxlength="32" name="f_password_confirm">
	</td>
</tr>
<tr class="row-2">
	<td class="category">
	    <?php echo $s_email ?>:
	</td>
	<td>
	    <input type="text" size="32" maxlength="64" name="f_email" value="<?php echo $u_email ?>">
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_access_level ?>:
	</td>
	<td>
		<?php echo get_enum_element( 'access_levels', $u_access_level ) ?>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_access_level_project ?>:
	</td>
	<td>
		<?php echo get_enum_element( 'access_levels', get_current_user_access_level() ) ?>
	</td>
</tr>
<tr class="row-1" valign="top">
	<td class="category">
		<?php echo $s_assigned_projects ?>:
	</td>
	<td>
		<?php print_project_user_list( get_current_user_field( 'id' ) ) ?>
	</td>
</tr>
<tr>
	<td class="left">
		<input type="submit" value="<?php echo $s_update_user_button ?>">
		</form>
	</td>
<?php
		# check if users can't delete their own accounts
		if ( ON == $g_allow_account_delete ) {
?>
	<td class="right">
		<form method="post" action="account_delete_page.php">
		<input type="submit" value="<?php echo $s_delete_account_button ?>">
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
