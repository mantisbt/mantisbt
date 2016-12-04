<?php
# MantisBT - A PHP based bugtracking system

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Show Contents of Email Queue in Mantis Database
 * This allows an administrator to clear 'stuck' mails
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team   - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once( dirname( dirname( __FILE__ ) ) . '/core.php' );

# Load schema version needed to render admin menu bar
require_once( 'schema.php' );

access_ensure_global_level( config_get_global( 'admin_site_threshold' ) );

layout_page_header();

layout_admin_page_begin();

print_admin_menu_bar( 'email_queue.php' );
?>

<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>

<?php
$f_to = gpc_get( 'send', null );
$f_mail_test = gpc_get_bool( 'mail_test' );

if( $f_to !== null ) {
	if( $f_to == 'all' ) {
		echo 'Sending emails...<br />';
		email_send_all();
		echo 'Done';
	} else if( $f_to == 'sendordelall' ) {
		echo 'Sending or deleting emails...<br />';
		email_send_all( true );
		echo 'Done';

	} else {
		$t_email_data = email_queue_get( (int)$f_to );

		# check if email was found.  This can fail if another request picks up the email first and sends it.
		echo 'Sending email...<br />';
		if( $t_email_data !== false ) {
			if( !email_send( $t_email_data ) ) {
				echo 'Email Not Sent - Deleting from queue<br />';
				email_queue_delete( $t_email_data->email_id );
			} else {
				echo 'Email Sent<br />';
			}
		} else {
			echo 'Email not found in queue<br />';
		}
	}
}

if( $f_mail_test ) {
	lang_push( 'english' );

	$t_email_data = new EmailData;
	$t_email_data->email = config_get_global( 'webmaster_email' );
	$t_email_data->subject = 'Testing PHP mail() function';
	$t_email_data->body = 'Your PHP mail settings appear to be correctly set.';
	$t_email_data->metadata['charset'] = 'utf-8';
	$t_result = email_send( $t_email_data );

	if( !$t_result ) {
		echo '<div class="alert alert-sm alert-danger">';
		echo '<i class="ace-icon fa fa-times fa-lg"></i> ';
		echo '<strong>Testing Mail</strong> - ';
		echo ' PROBLEMS SENDING MAIL TO: ' . config_get_global( 'webmaster_email' ) . '. Please check your php/mail server settings.';
		echo '</div>';
	} else {
		echo '<div class="alert alert-sm alert-success">';
		echo '<i class="ace-icon fa fa-check fa-lg"></i> ';
		echo '<strong>Testing Mail</strong> - ';
		echo ' mail() send successful.';
		echo '</div>';
	}
}

$t_ids = email_queue_get_ids();

if( count( $t_ids ) > 0 ) {
?>
<div class="widget-box widget-color-blue2">
<div class="widget-header widget-header-small">
	<h4 class="widget-title lighter">
	<i class="ace-icon fa fa-envelope"></i>
	Email Queue
	</h4>
</div>
<div class="widget-body">
<div class="widget-main no-padding">
	<div class="table-responsive">
	<table class="table table-bordered table-striped table-condensed table-hover">

		<thead>
			<tr>
				<th><?php echo lang_get( 'id' ); ?></th>
				<th><?php echo lang_get( 'email' ); ?></th>
				<th><?php echo lang_get( 'timestamp' ) ?></th>
				<th>Send Or Delete</th>
			</tr>
		</thead>
		<tbody>
<?php
	foreach( $t_ids as $t_id ) {
		$t_row = email_queue_get( $t_id );
?>
			<tr>
				<td><?php echo $t_row->email_id; ?></td>
				<td><?php echo $t_row->email; ?></td>
				<td><?php echo date( config_get( 'complete_date_format' ), $t_row->submitted );?></td>
				<td><?php html_button( 'email_queue.php', 'Send Or Delete', array( 'send' => $t_row->email_id ) ); ?></td>
			</tr>
<?php
	}
?>
		</tbody>
	</table>
	</div>
</div></div></div>
<?php
	echo '<div class="btn-group inline">';
	echo '<div class="pull-left">';
	html_button( 'email_queue.php', 'Send All', array( 'send' => 'all') );
	echo '</div>';
	echo '<div class="pull-left">';
	html_button( 'email_queue.php', 'Send Or Delete All', array( 'send' => 'sendordelall') );
	echo '</div>';
	echo '</div>';
} else {
	echo '<p class="lead">Email Queue Empty</p>';
}
?>
	<div class="widget-box widget-color-blue2">
	<div class="widget-body">
	<div class="widget-main">
		<form method="post" action="<?php echo $_SERVER['SCRIPT_NAME']?>">
			<fieldset>
				<h4>Testing Email</h4>
				<p>You can test the ability for MantisBT to send email notifications
					with this form. Just click "Send Mail". If the page takes a very
					long time to reappear or results in an error then you will need to
					investigate your php/mail server settings (see PHPMailer related
					settings in your config/config_inc.php, if they don't exist,
					copy from config_defaults_inc.php).</p>
				<p>Note that errors can also appear in the server error log.</p>
				<p> More help can be found at the
					<a href="http://www.php.net/manual/en/ref.mail.php">PHP website</a>
					if you are using the mail() PHPMailer sending mode.</p>
				<p>
					Email Address: <?php echo config_get_global( 'webmaster_email' );?>
				</p>
				<input type="submit" value="Send Mail" name="mail_test" class="btn btn-primary btn-white btn-round" />
			</fieldset>
		</form>
	</div>
	</div></div></div>

</div>
<?php
layout_admin_page_end();
