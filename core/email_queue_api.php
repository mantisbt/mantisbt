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
 * @package CoreAPI
 * @subpackage EmailQueueAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

 /**
 * EmailData Structure Definition
 * @package MantisBT
 * @subpackage classes
 */
class EmailData {
	// properties set during creation
	var $email = '';
	var $subject = '';
	var $body = '';
	var $metadata = array(
		'headers' => array(),
	);

	// auto-populated properties
	var $email_id = 0;
	var $submitted = '';
};

/**
 * Return a copy of the bug structure with all the instvars prepared for db insertion
 * @param EmailData $p_email_data
 * @return EmailData
 */
function email_queue_prepare_db( $p_email_data ) {
	$p_email_data->email_id = db_prepare_int( $p_email_data->email_id );

	return $p_email_data;
}

/**
 * Add to email queue
 * @param EmailData $p_email_data
 * @return int
 */
function email_queue_add( $p_email_data ) {
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

	$t_email_table = db_get_table( 'mantis_email_table' );

	$c_email = $t_email_data->email;
	$c_subject = $t_email_data->subject;
	$c_body = $t_email_data->body;
	$c_metadata = serialize( $t_email_data->metadata );

	$query = "INSERT INTO $t_email_table
				    ( email,
				      subject,
					  body,
					  submitted,
					  metadata)
				  VALUES
				    ( " . db_param() . ",
				      " . db_param() . ",
				      " . db_param() . ",
					  " . db_param() . ",
					  " . db_param() . "
					)";
	db_query_bound( $query, Array( $c_email, $c_subject, $c_body, db_now(), $c_metadata ) );
	$t_id = db_insert_id( $t_email_table, 'email_id' );

	log_event( LOG_EMAIL, "message #$t_id queued" );

	return $t_id;
}

/**
 * Convert email db row to EmailData object
 * @param array $p_row
 * @return bool|EmailData
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
 * @param int $p_email_id
 * @return bool|EmailData
 */
function email_queue_get( $p_email_id ) {
	$c_email_id = db_prepare_int( $p_email_id );
	$t_email_table = db_get_table( 'mantis_email_table' );

	$query = 'SELECT * FROM ' . $t_email_table . ' WHERE email_id=' . db_param();
	$result = db_query_bound( $query, Array( $c_email_id ) );

	$t_row = db_fetch_array( $result );

	return email_queue_row_to_object( $t_row );
}

/**
 * Delete entry from email queue
 * @param int $p_email_id
 * @return null
 */
function email_queue_delete( $p_email_id ) {
	$c_email_id = db_prepare_int( $p_email_id );
	$t_email_table = db_get_table( 'mantis_email_table' );

	$query = 'DELETE FROM ' . $t_email_table . ' WHERE email_id=' . db_param();
	db_query_bound( $query, Array( $c_email_id ) );

	log_event( LOG_EMAIL, "message #$p_email_id deleted from queue" );
}

/**
 * Get array of email queue id's
 * @param string $p_sort_order 'ASC' or 'DESC' (defaults to DESC)
 * @return array
 */
function email_queue_get_ids( $p_sort_order = 'DESC' ) {
	$t_email_table = db_get_table( 'mantis_email_table' );

	$query = "SELECT email_id FROM $t_email_table ORDER BY email_id $p_sort_order";
	$result = db_query_bound( $query );

	$t_ids = array();
	while(( $t_row = db_fetch_array( $result ) ) !== false ) {
		$t_ids[] = $t_row['email_id'];
	}

	return $t_ids;
}
