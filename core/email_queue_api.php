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
 * Email Queue API
 *
 * @package CoreAPI
 * @subpackage EmailQueueAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses constant_api.php
 * @uses database_api.php
 * @uses error_api.php
 * @uses lang_api.php
 * @uses utility_api.php
 */

require_api( 'constant_inc.php' );
require_api( 'database_api.php' );
require_api( 'error_api.php' );
require_api( 'lang_api.php' );
require_api( 'utility_api.php' );

/**
 * EmailData Structure Definition
 */
class EmailData {
	/**
	 * Email address
	 */
	public $email = '';

	/**
	 * Subject text
	 */
	public $subject = '';

	/**
	 * Body text
	 */
	public $body = '';

	/**
	 * Meta Data array
	 */
	public $metadata = array(
		'headers' => array(),
	);

	/**
	 * Email ID
	 */
	public $email_id = 0;

	/**
	 * Submitted
	 */
	public $submitted = '';
}

/**
 * Return a copy of the bug structure with all the instvars prepared for database insertion
 * @param EmailData $p_email_data Email Data structure to store.
 * @return EmailData
 */
function email_queue_prepare_db( EmailData $p_email_data ) {
	$p_email_data->email_id = (int)$p_email_data->email_id;

	return $p_email_data;
}

/**
 * Add to email queue
 * @param EmailData $p_email_data Email Data structure.
 * @return integer
 */
function email_queue_add( EmailData $p_email_data ) {
	$t_email_data = email_queue_prepare_db( $p_email_data );

	# email cannot be blank
	if( is_blank( $t_email_data->email ) ) {
		error_parameters( lang_get( 'email' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	# subject cannot be blank
	if( is_blank( $t_email_data->subject ) ) {
		error_parameters( lang_get( 'subject' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	# body cannot be blank
	if( is_blank( $t_email_data->body ) ) {
		error_parameters( lang_get( 'body' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	$c_email = $t_email_data->email;
	# MySQL 4-bytes UTF-8 chars workaround #21101
	$c_subject = db_mysql_fix_utf8( $t_email_data->subject );
	$c_body = db_mysql_fix_utf8( $t_email_data->body );
	$c_metadata = serialize( $t_email_data->metadata );

	db_param_push();
	$t_query = 'INSERT INTO {email}
				    ( email, subject, body, submitted, metadata)
				  VALUES
				    (' . db_param() . ',' . db_param() . ',' . db_param() . ',' . db_param() . ',' . db_param() . ')';
	db_query( $t_query, array( $c_email, $c_subject, $c_body, db_now(), $c_metadata ) );
	$t_id = db_insert_id( db_get_table( 'email' ), 'email_id' );

	log_event( LOG_EMAIL_VERBOSE, sprintf( 'message %d queued', $t_id ) );

	return $t_id;
}

/**
 * Convert email db row to EmailData object
 * @param array|false $p_row Database result row to convert.
 * @return boolean|EmailData
 */
function email_queue_row_to_object( $p_row ) {
	# typically this function takes as an input the result of db_fetch_array() which can be false.
	if( $p_row === false ) {
		return false;
	}

	$t_row = $p_row;
	$t_row['metadata'] = unserialize( $t_row['metadata'] );

	$t_email_data = new EmailData;

	$t_row_keys = array_keys( $t_row );
	$t_vars = get_object_vars( $t_email_data );

	# Check each variable in the class
	foreach( $t_vars as $t_var => $t_value ) {
		# If we got a field from the DB with the same name
		if( in_array( $t_var, $t_row_keys, true ) ) {
			# Store that value in the object
			$t_email_data->$t_var = $t_row[$t_var];
		}
	}

	return $t_email_data;
}

/**
 * Get Corresponding EmailData object
 * @param integer $p_email_id An email identifier.
 * @return boolean|EmailData
 */
function email_queue_get( $p_email_id ) {
	db_param_push();
	$t_query = 'SELECT * FROM {email} WHERE email_id=' . db_param();
	$t_result = db_query( $t_query, array( $p_email_id ) );

	$t_row = db_fetch_array( $t_result );

	return email_queue_row_to_object( $t_row );
}

/**
 * Delete entry from email queue
 * @param integer $p_email_id Email queue identifier.
 * @param string $p_reason The reason for deletion.
 * @return void
 */
function email_queue_delete( $p_email_id, $p_reason = '' ) {
	db_param_push();
	$t_query = 'DELETE FROM {email} WHERE email_id=' . db_param();
	db_query( $t_query, array( $p_email_id ) );

	if( is_blank( $p_reason ) ) {
		log_event( LOG_EMAIL_VERBOSE, sprintf( 'message %d deleted from queue', $p_email_id ) );
	} else {
		log_event( LOG_EMAIL_VERBOSE, sprintf( 'message %d deleted from queue. reason: %s', $p_email_id, $p_reason ) ); 
	}
}

/**
 * Get array of email queue id's
 * @return array
 */
function email_queue_get_ids() {
	$t_query = 'SELECT email_id FROM {email} ORDER BY email_id ASC';
	$t_result = db_query( $t_query );

	$t_ids = array();
	while( ( $t_row = db_fetch_array( $t_result ) ) !== false ) {
		$t_ids[] = $t_row['email_id'];
	}

	return $t_ids;
}
