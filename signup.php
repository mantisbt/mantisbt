<?php
# MantisBT - a php based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

	/**
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	require_once( 'email_api.php' );

	form_security_validate( 'signup' );

	$f_username		= strip_tags( gpc_get_string( 'username' ) );
	$f_email		= strip_tags( gpc_get_string( 'email' ) );
	$f_captcha		= gpc_get_string( 'captcha', '' );

	$f_username = trim( $f_username );
	$f_email = email_append_domain( trim( $f_email ) );
	$f_captcha = utf8_strtolower( trim( $f_captcha ) );

	# force logout on the current user if already authenticated
	if( auth_is_user_authenticated() ) {
		auth_logout();
	}

	# Check to see if signup is allowed
	if ( OFF == config_get_global( 'allow_signup' ) ) {
		print_header_redirect( 'login_page.php' );
		exit;
	}

	if( ON == config_get( 'signup_use_captcha' ) && get_gd_version() > 0 	&&
				helper_call_custom_function( 'auth_can_change_password', array() ) ) {
		$t_form_key = session_get( CAPTCHA_KEY );

		# captcha image requires GD library and related option to ON
		$t_key = utf8_strtolower( utf8_substr( md5( config_get( 'password_confirm_hash_magic_string' ) . $t_form_key ), 1, 5) );

		if ( $t_key != $f_captcha ) {
			trigger_error( ERROR_SIGNUP_NOT_MATCHING_CAPTCHA, ERROR );
		}
	}

	email_ensure_not_disposable( $f_email );

	# notify the selected group a new user has signed-up
	if( user_signup( $f_username, $f_email ) ) {
		email_notify_new_account( $f_username, $f_email );
	}

	form_security_purge( 'signup' );

	html_page_top1();
	html_page_top2a();
?>

<br />
<div align="center">
<table class="width50" cellspacing="1">
<tr>
	<td class="center">
		<b><?php echo lang_get( 'signup_done_title' ) ?></b><br />
		<?php echo "[$f_username - $f_email] " ?>
	</td>
</tr>
<tr>
	<td>
		<br />
		<?php echo lang_get( 'password_emailed_msg' ) ?>
		<br /><br />
		<?php echo lang_get( 'no_reponse_msg') ?>
		<br /><br />
	</td>
</tr>
</table>
<br />
<?php print_bracket_link( 'login_page.php', lang_get( 'proceed' ) ); ?>
</div>

<?php
	html_page_bottom1a( __FILE__ );
