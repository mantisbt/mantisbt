<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( "core_API.php" ) ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	# Check for invalid access to signup page
	if ( OFF == $g_allow_signup ) {
		print_header_redirect( $g_login_page );
		exit;
	}
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>


<p>
<div align="center">
<?php echo $s_signup_info ?>
</div>

<?php # Signup form BEGIN ?>
<p>
<div align="center">
<table class="width50" cellspacing="1">
<form method="post" action="<?php echo $g_signup ?>">
<tr>
	<td class="form-title">
		<?php echo $s_signup_title ?>
	</td>
	<td class="right">
		<?php print_bracket_link( $g_login_page, $s_go_back ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="30%">
		<?php echo $s_username ?>:
	</td>
	<td width="70%">
		<input type="text" name="f_username" size="32" maxlength="32">
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_email ?>:
	</td>
	<td>
		<input type="text" name="f_email" size="32" maxlength="64">
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<?php echo $s_signup_button ?>">
	</td>
</tr>
</form>
</table>
</div>
<?php # Signup form END ?>

<?php print_page_bot1( __FILE__ ) ?>