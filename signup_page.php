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
	 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	# Check for invalid access to signup page
	if ( OFF == config_get_global( 'allow_signup' ) || LDAP == config_get_global( 'login_method' ) ) {
		print_header_redirect( 'login_page.php' );
	}

	# signup page shouldn't be indexed by search engines
	html_robots_noindex();

	html_page_top1();
	html_page_top2a();

	$t_key = mt_rand( 0,99999 );
?>

<br />
<div align="center">
<form name="signup_form" method="post" action="signup.php">
<?php echo form_security_field( 'signup' ); ?>
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
		<input type="text" name="username" size="32" maxlength="<?php echo DB_FIELD_SIZE_USERNAME;?>" />
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
	$t_allow_passwd = helper_call_custom_function( 'auth_can_change_password', array() );
	if( ON == config_get( 'signup_use_captcha' ) && get_gd_version() > 0 && ( true == $t_allow_passwd ) ) {
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
		<img src="make_captcha_img.php?public_key=<?php echo $t_key ?>" alt="visual captcha" />
		<input type="hidden" name="public_key" value="<?php echo $t_key ?>" />
	</td>
</tr>
<?php
	}
	if( false == $t_allow_passwd ) {
?>
<tr class="row-1">
	<td class="category">
	</td>
	<td colspan="2">
		<?php echo lang_get( 'no_password_request' ) ?>
	</td>
</tr>
<?php
	}
?>
<tr>
	<td colspan="3">
		<br />
		<?php echo lang_get( 'signup_info' ) ?>
		<br /><br />
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
	echo '<br /><div align="center">';
	print_login_link();
	echo '&#160;';
	print_lost_password_link();
	echo '</div>';

	if ( ON == config_get( 'use_javascript' ) ) {
?>
<!-- Autofocus JS -->
<?php if ( ON == config_get( 'use_javascript' ) ) { ?>
<script type="text/javascript" language="JavaScript">
<!--
	window.document.signup_form.username.focus();
// -->
</script>
<?php
	}
}

html_page_bottom1a( __FILE__ );
