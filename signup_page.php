<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php require_once( 'core.php' ) ?>
<?php
	# Check for invalid access to signup page
	if ( OFF == $g_allow_signup ) {
		print_header_redirect( 'login_page.php' );
	}
?>
<?php html_page_top1() ?>
<?php html_page_top2a() ?>

<br />
<div align="center">
<?php echo lang_get( 'signup_info' ) ?>
</div>

<?php # Signup form BEGIN ?>
<br />
<div align="center">
<form method="post" action="signup.php">
<table class="width50" cellspacing="1">
<tr>
	<td class="form-title">
		<?php echo lang_get( 'signup_title' ) ?>
	</td>
	<td class="right">
		<?php print_bracket_link( 'login_page.php', lang_get( 'go_back' ) ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="30%">
		<?php echo lang_get( 'username' ) ?>:
	</td>
	<td width="70%">
		<input type="text" name="username" size="32" maxlength="32" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'email' ) ?>:
	</td>
	<td>
		<?php print_email_input( 'email', '' ) ?>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<?php echo lang_get( 'signup_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>
<?php # Signup form END ?>

<?php html_page_bottom1a( __FILE__ ) ?>
