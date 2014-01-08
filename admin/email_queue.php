<?php
# MantisBT - a php based bugtracking system

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
 * @package MantisBT
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */
/**
 * Mantis Core API's
 */
require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core.php' );

access_ensure_global_level( config_get_global( 'admin_site_threshold' ) );

$f_to = gpc_get( 'send', null );

if ( $f_to !== null ) {
	if ( $f_to == 'all' ) {
		echo "Sending emails...<br />";
		email_send_all();
		echo "Done";
	} else if ( $f_to == 'sendordelall' ) {
		echo "Sending or deleting emails...<br />";
		email_send_all(true);
		echo "Done";

	} else {
		$t_email_data = email_queue_get( (int) $f_to );

		// check if email was found.  This can fail if another request picks up the email first and sends it.
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

	echo '<table><tr><th>' . lang_get('id') . '</th><th>' . lang_get('email') . '</th><th>' . lang_get('timestamp') . '</th><th>Send Or Delete</th></tr>';
	foreach( $t_ids as $t_id ) {
		$row = email_queue_get( $t_id );

		echo '<tr><td>'
			. $row->email_id . '</td><td>'
			. $row->email . '</td><td>'
			. date( config_get( 'complete_date_format' ), $row->submitted ) . '</td><td>'
			, html_button( 'email_queue.php', 'Send Or Delete', array( 'send' => $row->email_id ) )
			, '</td></tr>';
	}
	echo '</table>';
} else {
}

html_button( 'email_queue.php', 'Send All', array( 'send' => 'all') );
html_button( 'email_queue.php', 'Send Or Delete All', array( 'send' => 'sendordelall') );
