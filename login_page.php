<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Login page POSTs results to login.php
	# Check to see if the user is already logged in via login_cookie_check()
?>
<?php include( "core_API.php" ) ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	# Check to see if the user is logged in and then validate the cookie value
	if ( !empty( $g_string_cookie_val ) ) {
		login_cookie_check( $g_main_page );
	}
?>
<?php print_page_top1() ?>
<?php print_page_top2a() ?>

<p>
<div align="center">
<?php
	# Only echo error message if error variable is set
	if ( isset( $f_error ) ) {
		PRINT "$s_login_error_msg<p>";
	}

	# Display short greeting message
	echo $s_login_page_info;
?>
</div>

<?php # Login Form BEGIN ?>
<p>
<div align="center">
<form method="post" action="<?php echo $g_login ?>">
<?php	if (isset($f_project_id)) { ?>
<input type="hidden" name="f_project_id" value="<?php echo $f_project_id ?>">
<?php } ?>
<table class="width50" cellspacing="0">
<tr>
	<td class="form-title" colspan="2">
		<?php echo $s_login_title ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="25%">
		<?php echo $s_username ?>:
	</td>
	<td width="75%">
		<input type="text" name="f_username" size="32" maxlength="32">
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_password ?>:
	</td>
	<td>
		<input type="password" name="f_password" size="16" maxlength="32">
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_save_login ?>:
	</td>
	<td>
		<input type="checkbox" name="f_perm_login">
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<?php echo $s_login_button ?>">
	</td>
</tr>
</table>
</form>
</div>
<?php # Login Form END ?>

<?php print_signup_link() ?>

<?php print_page_bot1( __FILE__ ) ?>