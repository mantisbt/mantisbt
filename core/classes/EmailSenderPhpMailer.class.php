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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as phpmailerException;

/** @global PHPMailer $g_phpMailer Reusable PHPMailer object */
$g_phpMailer = null;

require_once( __DIR__ . DIRECTORY_SEPARATOR . 'EmailSender.class.php' );

/**
 * An implementation that sends out emails using PhpMailer library.
 */
class EmailSenderPhpMailer extends EmailSender {
	/**
	 * Send an email
	 *
	 * @param EmailMessage $p_message The email to send
	 * @return bool true if the email was sent successfully, false otherwise
	 */
	public function send( EmailMessage $p_message ) : bool {
		global $g_phpMailer;

		if( is_null( $g_phpMailer ) ) {
			if( PHPMAILER_METHOD_SMTP == config_get( 'phpMailer_method' ) ) {
				register_shutdown_function( 'phpmailer_close' );
			}

			$g_phpMailer = new PHPMailer( true );

			// Set e-mail addresses validation pattern. The 'html5' setting is
			// consistent with the regex defined in email_regex_simple().
			PHPMailer::$validator  = 'html5';
		}

		$t_mail = $g_phpMailer;

		if( !empty( $p_message->hostname ) ) {
			$t_mail->Hostname = $p_message->hostname;
		}

		$t_mail->setLanguage( lang_get( 'phpmailer_language', $p_message->lang ) );

		# Select the method to send mail
		switch( config_get( 'phpMailer_method' ) ) {
			case PHPMAILER_METHOD_MAIL:
				$t_mail->isMail();
				break;

			case PHPMAILER_METHOD_SENDMAIL:
				$t_mail->isSendmail();
				break;

			case PHPMAILER_METHOD_SMTP:
				$t_mail->isSMTP();

				# SMTP collection is always kept alive
				$t_mail->SMTPKeepAlive = true;

				if( !is_blank( config_get( 'smtp_username' ) ) ) {
					# Use SMTP Authentication
					$t_mail->SMTPAuth = true;
					$t_mail->Username = config_get( 'smtp_username' );
					$t_mail->Password = config_get( 'smtp_password' );
				}

				if( is_blank( config_get( 'smtp_connection_mode' ) ) ) {
					$t_mail->SMTPAutoTLS = false;
				} else {
					$t_mail->SMTPSecure = config_get( 'smtp_connection_mode' );
				}

				$t_mail->Port = config_get( 'smtp_port' );

				break;
		}

		# S/MIME signature
		if( ON == config_get_global( 'email_smime_enable' ) ) {
			$t_mail->sign(
				config_get_global( 'email_smime_cert_file' ),
				config_get_global( 'email_smime_key_file' ),
				config_get_global( 'email_smime_key_password' ),
				config_get_global( 'email_smime_extracerts_file' )
			);
		}

		# apply DKIM settings
		if( config_get_global( 'email_dkim_enable' ) ) {
			$t_mail->DKIM_domain = config_get_global( 'email_dkim_domain' );
			$t_mail->DKIM_private = config_get_global( 'email_dkim_private_key_file_path' );
			$t_mail->DKIM_private_string = config_get_global( 'email_dkim_private_key_string' );
			$t_mail->DKIM_selector = config_get_global( 'email_dkim_selector' );
			$t_mail->DKIM_passphrase = config_get_global( 'email_dkim_passphrase' );
			$t_mail->DKIM_identity = config_get_global( 'email_dkim_identity' );
		}

		# set email format to plain text and word wrap to 80 characters
		$t_mail->isHTML( false );
		$t_mail->WordWrap = 80;

		$t_mail->CharSet = $p_message->hostname;
		$t_mail->Host = config_get( 'smtp_host' );
		$t_mail->From = config_get( 'from_email' );
		$t_mail->Sender = config_get( 'return_path_email' );
		$t_mail->FromName = config_get( 'from_name' );

		foreach( $p_message->cc as $cc ) {
			$t_mail->addCC( $cc );
		}

		foreach( $p_message->bcc as $bcc ) {
			$t_mail->addBCC( $bcc );
		}

		$t_mail->Encoding = 'quoted-printable';

		if( isset( $t_email_data->metadata['priority'] ) ) {
			$t_mail->Priority = $t_email_data->metadata['priority'];  # Urgent = 1, Not Urgent = 5, Disable = 0
		}

		$t_log_msg = 'Error: message could not be sent - ';

		try {
			foreach( $p_message->to as $t_recipient ) {
				$t_mail->addAddress( $t_recipient );
			}
		}
		catch ( phpmailerException $e ) {
			log_event( LOG_EMAIL, $t_log_msg . $t_mail->ErrorInfo );
			self::reset( $t_mail );
			return false;
		}

		$t_mail->Subject = $p_message->subject;
		$t_mail->Body = $p_message->text;

		foreach( $p_message->headers as $t_key => $t_value ) {
			switch( strtolower( $t_key ) ) {
				case 'message-id':
					# Note: hostname can never be blank here as we set metadata['hostname']
					# in email_store() where mail gets queued.
					if( !strchr( $t_value, '@' ) && !is_blank( $t_mail->Hostname ) ) {
						$t_value = $t_value . '@' . $t_mail->Hostname;
					}
					$t_mail->set( 'MessageID', '<' . $t_value . '>' );
					break;
				/** @noinspection PhpMissingBreakStatementInspection */
				case 'in-reply-to':
					if( !preg_match( '/<.+@.+>/m', $t_value ) ) {
						$t_value = '<' . $t_value . '@' . $t_mail->Hostname . '>';
					}
					# Fall-through
				default:
					$t_mail->addCustomHeader( $t_key . ': ' . $t_value );
					break;
			}
		}

		try {
			$t_success = $t_mail->send();
			if( !$t_success ) {
				# We should never get here, as an exception is thrown after failures
				log_event( LOG_EMAIL, $t_log_msg . $t_mail->ErrorInfo );
			}
		} catch ( phpmailerException $e ) {
			log_event( LOG_EMAIL, $t_log_msg . $t_mail->ErrorInfo );
			$t_success = false;
		}

		self::reset( $t_mail );

		return $t_success;
	}

	/**
	 * Clear the state of the global object.
	 *
	 * @param PHPMailer &$p_mail The object to clear.
	 * @return void
	 */
	private static function reset( PHPMailer &$p_mail ) : void {
		$p_mail->clearAllRecipients();
		$p_mail->clearAttachments();
		$p_mail->clearReplyTos();
		$p_mail->clearCustomHeaders();
	}
}

/**
 * closes opened kept alive SMTP connection (if it was opened)
 *
 * @return void
 */
function phpmailer_close() {
	global $g_phpMailer;

	if( !is_null( $g_phpMailer ) ) {
		$t_smtp = $g_phpMailer->getSMTPInstance();
		if( $t_smtp->connected() ) {
			$t_smtp->quit();
			$t_smtp->close();
		}

		$g_phpMailer = null;
	}
}
