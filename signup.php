<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: signup.php,v 1.37 2004-08-15 22:21:53 thraxisp Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'email_api.php' );

	$f_username		= strip_tags( gpc_get_string( 'username' ) );
	$f_email		= strip_tags( gpc_get_string( 'email' ) );
	$f_captcha		= gpc_get_string( 'captcha', '' );
	$f_public_key	= gpc_get_int( 'public_key', '' );

	$f_username = trim( $f_username );
	$f_email = email_append_domain( trim( $f_email ) );
	$f_captcha = strtolower( trim( $f_captcha ) );

	# Check to see if signup is allowed
	if ( OFF == config_get( 'allow_signup' ) ) {
		print_header_redirect( 'login_page.php' );
		exit;
	}

	if( ON == config_get( 'signup_use_captcha' ) && get_gd_version() > 0 ) {
		# captcha image requires GD library and related option to ON
		$t_key = strtolower( substr( md5( config_get( 'password_confirm_hash_magic_string' ) . $f_public_key ), 1, 5) );

		if ( $t_key != $f_captcha ) {
			trigger_error( ERROR_SIGNUP_NOT_MATCHING_CAPTCHA, ERROR );
		}
	}

	# notify the selected group a new user has signed-up
	if( user_signup( $f_username, $f_email ) ) {
		email_notify_new_account( $f_username, $f_email );
	}

	html_page_top1();
	html_page_top2a();
?>

<br />
<div align="center">
<table class="width50" cellspacing="1">
<tr>
	<td class="center">
		<b><?php echo lang_get( 'signup_done_title' ) ?></b><br/>
		<?php echo "[$f_username - $f_email] " ?>
	</td>
</tr>
<tr>
	<td>
		<br/>
		<?php echo lang_get( 'password_emailed_msg' ) ?>
		<br /><br/>
		<?php echo lang_get( 'no_reponse_msg') ?>
		<br/><br/>
	</td>
</tr>
</table>
<br />
<?php print_bracket_link( 'login_page.php', lang_get( 'proceed' ) ); ?>
</div>

<?php html_page_bottom1a( __FILE__ ) ?>
