<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Users may change their user information from this page.
	# The data is POSTed to account_update.php3
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	# extracts the user information for the currently logged in user
	# and prefixes it with u_
    $query = "SELECT *
    		FROM $g_mantis_user_table
			WHERE cookie_string='$g_string_cookie_val'";
    $result = db_query( $query );
	$row = db_fetch_array( $result );
	extract( $row, EXTR_PREFIX_ALL, "u" );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php # Edit Account Form BEGIN ?>
<p>
<div align="center">
<table class="width75" cellspacing="1">
<form method="post" action="<?php echo $g_account_update ?>">
<tr>
	<td class="form-title">
		<?php echo $s_edit_account_title ?>
	</td>
	<td class="right">
		<?php print_account_menu( $g_account_page ) ?>
	</td>
</tr>
<?php # using LDAP accounts
	if ( LDAP == $g_login_method ) {
?>
<tr class="row-2">
	<td colspan="2">
		The password settings are controlled by your LDAP entry,<BR>
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
    	$u_email = get_user_info( "$u_username","email" );
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
		<?php echo get_enum_element( $s_access_levels_enum_string, $u_access_level ) ?>
	</td>
</tr>
<tr>
	<td class="center">
		<input type="submit" value="<?php echo $s_update_user_button ?>">
	</td>
</form>
<form method="post" action="<?php echo $g_account_delete_page ?>">
	<td class="center">
		<input type="submit" value="<?php echo $s_delete_account_button ?>">
	</td>
</form>
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
		<?php echo get_enum_element( $s_access_levels_enum_string, $u_access_level ) ?>
	</td>
</tr>
<tr>
	<td class="left">
		<input type="submit" value="<?php echo $s_update_user_button ?>">
	</td>
</form>
<? 	if ( ON == $g_allow_account_delete ) { ?>
<form method="post" action="<?php echo $g_account_delete_page ?>">
	<td class="right">
		<input type="submit" value="<?php echo $s_delete_account_button ?>">
	</td>
</form>
<? } else { ?>
	<td>
		&nbsp;
	</td>
<? } ?>
</tr>
<?php } ?>
</table>
</div>
<?php # Edit Account Form END ?>

<?php print_page_bot1( __FILE__ ) ?>