<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: signup_page.php,v 1.29 2004-08-14 15:26:20 thraxisp Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	# Check for invalid access to signup page
	if ( OFF == config_get( 'allow_signup' ) ) {
		print_header_redirect( 'login_page.php' );
	}

	html_page_top1();
	html_page_top2a();

	$t_key = mt_rand( 0,99999 );
?>

<br />
<div align="center">
<form name="signup_form" method="post" action="signup.php">
<table class="width50" cellspacing="1">
<tr>
	<td class="form-title" colspan="3">
		<?php echo lang_get( 'signup_title' ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="30%">
		<?php echo lang_get( 'username' ) ?>:
	</td>
	<td width="70%" colspan="2">
		<input type="text" name="username" size="32" maxlength="32" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'email' ) ?>:
	</td>
	<td colspan="2">
		<?php print_email_input( 'email', '' ) ?>
	</td>
</tr>
<?php
	if( ON == config_get( 'signup_use_captcha' ) && get_gd_version() > 0 ) {
		# captcha image requires GD library and related option to ON
?>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'signup_captcha_request' ) ?>:
	</td>
	<td>
		<?php print_captcha_input( 'captcha', '' ) ?>
	</td>
	<td>
		<img src="make_captcha_img.php?public_key=<?php echo $t_key ?>">
		<input type="hidden" name="public_key" value="<?php echo $t_key ?>">
	</td>
</tr>
<?php
	}
?>
<tr>
	<td colspan="3">
		<br/>
		<?php echo lang_get( 'signup_info' ) ?>
		<br/><br/>
	</td>
</tr>
<tr>
	<td class="center" colspan="3">
		<input type="submit" class="button" value="<?php echo lang_get( 'signup_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<?php
	PRINT '<br /><div align="center">';
	print_login_link();
	PRINT '&nbsp;';
	print_lost_password_link();
	PRINT '</div>';

	if ( ON == config_get( 'use_javascript' ) ) {
?>
<!-- Autofocus JS -->
<script type="text/javascript" language="JavaScript">
window.document.signup_form.username.focus();
</script>
<?php
	}

	html_page_bottom1a( __FILE__ );
?>
