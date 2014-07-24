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

access_ensure_global_level( config_get_global( 'admin_site_threshold' ) );

layout_page_header();

layout_admin_page_begin();

print_admin_menu_bar( 'email_queue.php' );
?>

<div class="col-md-12 col-xs-12">
    <div class="space-10"></div>

<?php
$f_to = gpc_get( 'send', null );

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

$t_ids = email_queue_get_ids();

if( count( $t_ids ) > 0 ) {
    echo '<div class="widget-box widget-color-blue2">';
    echo '<div class="widget-header widget-header-small">';
    echo '<h4 class="widget-title lighter">';
    echo '<i class="ace-icon fa fa-envelope"></i>';
    echo 'Email Queue';
    echo '</h4>';
    echo '</div>';
    echo '<div class="widget-body">';
    echo '<div class="widget-main no-padding">';

    echo '<div class="table-responsive">';
    echo '<table class="table table-bordered table-striped table-condensed table-hover">';
	echo '<tr><th>' . lang_get( 'id' ) . '</th><th>' . lang_get( 'email' ) . '</th><th>' . lang_get( 'timestamp' ) . '</th><th>Send Or Delete</th></tr>';
	foreach( $t_ids as $t_id ) {
		$t_row = email_queue_get( $t_id );

		echo '<tr><td>'
			. $t_row->email_id . '</td><td>'
			. $t_row->email . '</td><td>'
			. date( config_get( 'complete_date_format' ), $t_row->submitted ) . '</td><td>'
			, html_button( 'email_queue.php', 'Send Or Delete', array( 'send' => $t_row->email_id ) )
			, '</td></tr>';
	}
	echo '</table>';
    echo '</div></div></div></div>';

    echo '<div class="btn-group inline">';
    echo '<div class="pull-left">';
	html_button( 'email_queue.php', 'Send All', array( 'send' => 'all') );
    echo '</div>';
    echo '<div class="pull-left">';
	html_button( 'email_queue.php', 'Send Or Delete All', array( 'send' => 'sendordelall') );
    echo '</div>';
    echo '</div>';
} else {
	echo '<p class="lead">Email Queue Empty</div>';
}
?>
</div>
<?php
layout_admin_page_end();
