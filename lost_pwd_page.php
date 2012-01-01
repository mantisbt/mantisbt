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
	 * @author Marcello Scata' <marcelloscata at users.sourceforge.net> ITALY
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	# lost password feature disabled or reset password via email disabled -> stop here!
	if ( LDAP == config_get_global( 'login_method' ) ||
		OFF == config_get( 'lost_password_feature' ) ||
		OFF == config_get( 'send_reset_password' )  ||
	 	OFF == config_get( 'enable_email_notification' ) ) {
		trigger_error( ERROR_LOST_PASSWORD_NOT_ENABLED, ERROR );
	}

	# don't index lost password page
	html_robots_noindex();

	html_page_top1();
	html_page_top2a();

	echo "<br />";
?>
<br />
<div align="center">
<form name="lost_password_form" method="post" action="lost_pwd.php">
<?php echo form_security_field( 'lost_pwd' ) ?>
<table class="width50" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<?php echo lang_get( 'lost_password_title' ) ?>
	</td>
</tr>
<?php
		$t_allow_passwd = helper_call_custom_function( 'auth_can_change_password', array() );
  if ( $t_allow_passwd ) {
?>
<tr class="row-1">
	<td class="category" width="25%">
		<?php echo lang_get( 'username' ) ?>
	</td>
	<td width="75%">
		<input type="text" name="username" size="32" maxlength="<?php echo DB_FIELD_SIZE_USERNAME;?>" />
	</td>
</tr>
<tr class="row-2">
	<td class="category" width="25%">
		<?php echo lang_get( 'email' ) ?>
	</td>
	<td width="75%">
		<?php print_email_input( 'email', '' ) ?>
	</td>
</tr>
<tr>
	<td colspan="2">
		<br />
		<?php echo lang_get( 'lost_password_info' ) ?>
		<br /><br />
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" class="button" value="<?php echo lang_get( 'submit_button' ) ?>" />
	</td>
</tr>
<?php
  } else {
?>
<tr>
	<td colspan="2">
		<br />
		<?php echo lang_get( 'no_password_request' ) ?>
		<br /><br />
	</td>
</tr>
<?php
  }
?>

</table>
</form>
</div>

<?php
	echo '<br /><div align="center">';
	print_login_link();
	echo '&#160;';
	print_signup_link();
	echo '</div>';

	if ( ON == config_get( 'use_javascript' ) ) {
?>
<!-- Autofocus JS -->
<?php if ( ON == config_get( 'use_javascript' ) ) { ?>
<script type="text/javascript" language="JavaScript">
<!--
	window.document.lost_password_form.username.focus();
// -->
</script>
<?php 
	}
}

html_page_bottom1a( __FILE__ );
