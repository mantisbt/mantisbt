<?php
# MantisBT - A PHP based bugtracking system

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
 * Email Sending Test Utility
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once( dirname( dirname( __FILE__ ) ) . '/core.php' );

access_ensure_global_level( config_get_global( 'admin_site_threshold' ) );

$f_mail_test = gpc_get_bool( 'mail_test' );

html_page_top();

?>

<a id="email"></a>
<table width="100%" bgcolor="#222222" cellpadding="20" cellspacing="1">
<tr>
	<td bgcolor="#f4f4f4">
		<span class="title">Testing Email</span>
		<p>You can test the ability for MantisBT to send email notifications with this form.  Just click "Send Mail".  If the page takes a very long time to reappear or results in an error then you will need to investigate your php/mail server settings (see PHPMailer related settings in your config/config_inc.php, if they don't exist, copy from config_defaults_inc.php).  Note that errors can also appear in the server error log.  More help can be found at the <a href="http://www.php.net/manual/en/ref.mail.php">PHP website</a> if you are using the mail() PHPMailer sending mode.</p>
		<?php
		if( $f_mail_test ) {
			echo '<strong>Testing Mail</strong> - ';

			# @@@ thraxisp - workaround to ensure a language is set without authenticating
			#  will disappear when this is properly localized
			lang_push( 'english' );

			$t_email_data = new EmailData;
			$t_email_data->email = config_get_global( 'webmaster_email' );
			$t_email_data->subject = 'Testing PHP mail() function';
			$t_email_data->body = 'Your PHP mail settings appear to be correctly set.';
			$t_email_data->metadata['priority'] = config_get( 'mail_priority' );
			$t_email_data->metadata['charset'] = 'utf-8';
			$t_result = email_send( $t_email_data );

			if( !$t_result ) {
				echo ' PROBLEMS SENDING MAIL TO: ' . config_get_global( 'webmaster_email' ) . '. Please check your php/mail server settings.<br />';
			} else {
				echo ' mail() send successful.<br />';
			}
		}
?>
		<form method="post" action="<?php echo $_SERVER['SCRIPT_NAME']?>#email">
		Email Address: <?php echo config_get_global( 'webmaster_email' );?><br />
		<input type="submit" class="button" value="Send Mail" name="mail_test" />
		</form>
	</td>
</tr>
</table>

<?php

html_page_bottom();
