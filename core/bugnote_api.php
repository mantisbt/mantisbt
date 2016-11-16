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
 * Bugnote API
 *
 * @package CoreAPI
 * @subpackage BugnoteAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses antispam_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses bug_revision_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses email_api.php
 * @uses error_api.php
 * @uses event_api.php
 * @uses helper_api.php
 * @uses history_api.php
 * @uses lang_api.php
 * @uses mention_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */

require_api( 'access_api.php' );
require_api( 'antispam_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'bug_revision_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'database_api.php' );
require_api( 'email_api.php' );
require_api( 'error_api.php' );
require_api( 'event_api.php' );
require_api( 'helper_api.php' );
require_api( 'history_api.php' );
require_api( 'lang_api.php' );
require_api( 'mention_api.php' );
require_api( 'user_api.php' );
require_api( 'utility_api.php' );

# Cache of bugnotes arrays related to a bug, indexed by bug_id.
# Each item is an array of BugnoteData objects
$g_cache_bugnotes_by_bug_id = array();

# Cache of BugnoteData objects, indexed by bugnote id
$g_cache_bugnotes_by_id = array();

/**
 * Bugnote Data Structure Definition
 */
class BugnoteData {
	/**
	 * Bugnote ID
	 */
	public $id;

	/**
	 * Bug ID
	 */
	public $bug_id;

	/**
	 * Reporter ID
	 */
	public $reporter_id;

	/**
	 * Note text
	 */
	public $note;

	/**
	 * View State
	 */
	public $view_state;

	/**
	 * Date submitted
	 */
	public $date_submitted;

	/**
	 * Last Modified
	 */
	public $last_modified;

	/**
	 * Bugnote type
	 */
	public $note_type;

	/**
	 * ???
	 */
	public $note_attr;

	/**
	 * Time tracking information
	 */
	public $time_tracking;

	/**
	 * Bugnote Text id
	 */
	public $bugnote_text_id;
}

/**
 * Check if a bugnote with the given ID exists
 * return true if the bugnote exists, false otherwise
 * @param integer $p_bugnote_id A bugnote identifier.
 * @return boolean
 * @access public
 */
function bugnote_exists( $p_bugnote_id ) {
	$c_bugnote_id = (int)$p_bugnote_id;

	global $g_cache_bugnotes_by_id;
	if( isset( $g_cache_bugnotes_by_id[$c_bugnote_id] ) ) {
		return true;
	}

	# Check for invalid id values
	if( $c_bugnote_id <= 0 || $c_bugnote_id > DB_MAX_INT ) {
		return false;
	}

	db_param_push();
	$t_query = 'SELECT b.*, t.note
			          	FROM      {bugnote} b
			          	LEFT JOIN {bugnote_text} t ON b.bugnote_text_id = t.id
						WHERE b.id = ' . db_param();
	$t_result = db_query( $t_query, array( $c_bugnote_id ) );
	$t_row = db_fetch_array( $t_result );

	if( $t_row === false ) {
		return false;
	}

	$t_bugnote = bugnote_row_to_object( $t_row );
	bugnote_cache( $t_bugnote );
	return true;
}

/**
 * Caches the provided bugnote object.
 *
 * @param BugnoteData $p_bugnote The bugnote object.
 * @return void
 */
function bugnote_cache( BugnoteData $p_bugnote ) {
	global $g_cache_bugnotes_by_id;

	$g_cache_bugnotes_by_id[(int)$p_bugnote->id] = $p_bugnote;
}

/**
 * Check if a bugnote with the given ID exists
 * return true if the bugnote exists, raise an error if not
 * @param integer $p_bugnote_id A bugnote identifier.
 * @access public
 * @return void
 */
function bugnote_ensure_exists( $p_bugnote_id ) {
	if( !bugnote_exists( $p_bugnote_id ) ) {
		trigger_error( ERROR_BUGNOTE_NOT_FOUND, ERROR );
	}
}

/**
 * Check if the given user is the reporter of the bugnote
 * return true if the user is the reporter, false otherwise
 * @param integer $p_bugnote_id A bugnote identifier.
 * @param integer $p_user_id    An user identifier.
 * @return boolean
 * @access public
 */
function bugnote_is_user_reporter( $p_bugnote_id, $p_user_id ) {
	if( bugnote_get_field( $p_bugnote_id, 'reporter_id' ) == $p_user_id ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Add a bugnote to a bug
 * return the ID of the new bugnote
 * @param integer $p_bug_id          A bug identifier.
 * @param string  $p_bugnote_text    The bugnote text to add.
 * @param string  $p_time_tracking   Time tracking value - hh:mm string.
 * @param boolean $p_private         Whether bugnote is private.
 * @param integer $p_type            The bugnote type.
 * @param string  $p_attr            Bugnote Attribute.
 * @param integer $p_user_id         A user identifier.
 * @param boolean $p_send_email      Whether to generate email.
 * @param integer $p_date_submitted  Date submitted (defaults to now()).
 * @param integer $p_last_modified   Last modification date (defaults to now()).
 * @param boolean $p_skip_bug_update Skip bug last modification update (useful when importing bugs/bugnotes).
 * @param boolean $p_log_history     Log changes to bugnote history (defaults to true).
 * @return boolean|integer false or indicating bugnote id added
 * @access public
 */
function bugnote_add( $p_bug_id, $p_bugnote_text, $p_time_tracking = '0:00', $p_private = false, $p_type = BUGNOTE, $p_attr = '', $p_user_id = null, $p_send_email = true, $p_date_submitted = 0, $p_last_modified = 0, $p_skip_bug_update = false, $p_log_history = true ) {
	$c_bug_id = (int)$p_bug_id;
	$c_time_tracking = helper_duration_to_minutes( $p_time_tracking );
	$c_type = (int)$p_type;
	$c_date_submitted = $p_date_submitted <= 0 ? db_now() : (int)$p_date_submitted;
	$c_last_modified = $p_last_modified <= 0 ? db_now() : (int)$p_last_modified;

	antispam_check();

	if( REMINDER !== $p_type ) {
		# Check if this is a time-tracking note
		$t_time_tracking_enabled = config_get( 'time_tracking_enabled' );
		if( ON == $t_time_tracking_enabled && $c_time_tracking > 0 ) {
			$t_time_tracking_without_note = config_get( 'time_tracking_without_note' );
			if( is_blank( $p_bugnote_text ) && OFF == $t_time_tracking_without_note ) {
				error_parameters( lang_get( 'bugnote' ) );
				trigger_error( ERROR_EMPTY_FIELD, ERROR );
			}
			$c_type = TIME_TRACKING;
		} else if( is_blank( $p_bugnote_text ) ) {
			# This is not time tracking (i.e. it's a normal bugnote)
			# @todo should we not trigger an error in this case ?
			return false;
		}
	}

	# Event integration
	$t_bugnote_text = event_signal( 'EVENT_BUGNOTE_DATA', $p_bugnote_text, $c_bug_id );

	# MySQL 4-bytes UTF-8 chars workaround #21101
	$t_bugnote_text = db_mysql_fix_utf8( $t_bugnote_text );

	# insert bugnote text
	db_param_push();
	$t_query = 'INSERT INTO {bugnote_text} ( note ) VALUES ( ' . db_param() . ' )';
	db_query( $t_query, array( $t_bugnote_text ) );

	# retrieve bugnote text id number
	$t_bugnote_text_id = db_insert_id( db_get_table( 'bugnote_text' ) );

	# get user information
	if( $p_user_id === null ) {
		$p_user_id = auth_get_current_user_id();
	}

	# Check for private bugnotes.
	if( $p_private && access_has_bug_level( config_get( 'set_view_status_threshold' ), $p_bug_id, $p_user_id ) ) {
		$t_view_state = VS_PRIVATE;
	} else {
		$t_view_state = VS_PUBLIC;
	}

	# insert bugnote info
	db_param_push();
	$t_query = 'INSERT INTO {bugnote}
			(bug_id, reporter_id, bugnote_text_id, view_state, date_submitted, last_modified, note_type, note_attr, time_tracking)
		VALUES ('
		. db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', '
		. db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', '
		. db_param() . ' )';
	$t_params = array(
		$c_bug_id, $p_user_id, $t_bugnote_text_id, $t_view_state,
		$c_date_submitted, $c_last_modified, $c_type, $p_attr,
		$c_time_tracking );
	db_query( $t_query, $t_params );

	# get bugnote id
	$t_bugnote_id = db_insert_id( db_get_table( 'bugnote' ) );

	# update bug last updated
	if( !$p_skip_bug_update ) {
		bug_update_date( $p_bug_id );
	}

	# log new bug
	if( true == $p_log_history ) {
		history_log_event_special( $p_bug_id, BUGNOTE_ADDED, bugnote_format_id( $t_bugnote_id ) );
	}

	# Event integration
	event_signal( 'EVENT_BUGNOTE_ADD', array( $p_bug_id, $t_bugnote_id ) );

	# only send email if the text is not blank, otherwise, it is just recording of time without a comment.
	if( true == $p_send_email && !is_blank( $t_bugnote_text ) ) {
		email_bugnote_add( $t_bugnote_id );
	}

	return $t_bugnote_id;
}

/**
 * Process mentions in bugnote, typically after its added.
 *
 * @param  int $p_bug_id          The bug id
 * @param  int $p_bugnote_id      The bugnote id
 * @param  string $p_bugnote_text The bugnote text
 * @return array User ids that received mentioned emails.
 * @access public
 */
function bugnote_process_mentions( $p_bug_id, $p_bugnote_id, $p_bugnote_text ) {
	# Process the mentions that have access to the issue note
	$t_mentioned_user_ids = mention_get_users( $p_bugnote_text );
	$t_filtered_mentioned_user_ids = access_has_bugnote_level_filter(
		config_get( 'view_bug_threshold' ),
		$p_bugnote_id,
		$t_mentioned_user_ids );

	$t_removed_mentions_user_ids = array_diff( $t_mentioned_user_ids, $t_filtered_mentioned_user_ids );

	return mention_process_user_mentions(
		$p_bug_id,
		$t_filtered_mentioned_user_ids,
		$p_bugnote_text,
		$t_removed_mentions_user_ids );
}

/**
 * Delete a bugnote
 * @param integer $p_bugnote_id A bug note identifier.
 * @return boolean
 * @access public
 */
function bugnote_delete( $p_bugnote_id ) {
	$t_bug_id = bugnote_get_field( $p_bugnote_id, 'bug_id' );
	$t_bugnote_text_id = bugnote_get_field( $p_bugnote_id, 'bugnote_text_id' );

	# Remove the bugnote
	db_param_push();
	$t_query = 'DELETE FROM {bugnote} WHERE id=' . db_param();
	db_query( $t_query, array( $p_bugnote_id ) );

	# Remove the bugnote text
	db_param_push();
	$t_query = 'DELETE FROM {bugnote_text} WHERE id=' . db_param();
	db_query( $t_query, array( $t_bugnote_text_id ) );

	# log deletion of bug
	history_log_event_special( $t_bug_id, BUGNOTE_DELETED, bugnote_format_id( $p_bugnote_id ) );

	# Event integration
	event_signal( 'EVENT_BUGNOTE_DELETED', array( $t_bug_id, $p_bugnote_id ) );

	return true;
}

/**
 * delete all bugnotes associated with the given bug
 * @param integer $p_bug_id A bug identifier.
 * @return void
 * @access public
 */
function bugnote_delete_all( $p_bug_id ) {
	# Delete the bugnote text items
	db_param_push();
	$t_query = 'SELECT bugnote_text_id FROM {bugnote} WHERE bug_id=' . db_param();
	$t_result = db_query( $t_query, array( (int)$p_bug_id ) );
	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_bugnote_text_id = $t_row['bugnote_text_id'];

		# Delete the corresponding bugnote texts
		db_param_push();
		$t_query = 'DELETE FROM {bugnote_text} WHERE id=' . db_param();
		db_query( $t_query, array( $t_bugnote_text_id ) );
	}

	# Delete the corresponding bugnotes
	db_param_push();
	$t_query = 'DELETE FROM {bugnote} WHERE bug_id=' . db_param();
	db_query( $t_query, array( (int)$p_bug_id ) );
}

/**
 * Get the text associated with the bugnote
 * @param integer $p_bugnote_id A bugnote identifier.
 * @return string bugnote text
 * @access public
 */
function bugnote_get_text( $p_bugnote_id ) {
	$t_bugnote_text_id = bugnote_get_field( $p_bugnote_id, 'bugnote_text_id' );

	# grab the bugnote text
	db_param_push();
	$t_query = 'SELECT note FROM {bugnote_text} WHERE id=' . db_param();
	$t_result = db_query( $t_query, array( $t_bugnote_text_id ) );

	return db_result( $t_result );
}

/**
 * Get a field for the given bugnote
 * @param integer $p_bugnote_id A bugnote identifier.
 * @param string  $p_field_name Field name to retrieve.
 * @return string field value
 * @access public
 */
function bugnote_get_field( $p_bugnote_id, $p_field_name ) {
	$t_bugnote = bugnote_get( $p_bugnote_id );
	return $t_bugnote->$p_field_name;
}

/**
 * Get latest bugnote id
 * @param integer $p_bug_id A bug identifier.
 * @return int latest bugnote id
 * @access public
 */
function bugnote_get_latest_id( $p_bug_id ) {
	db_param_push();
	$t_query = 'SELECT id FROM {bugnote} WHERE bug_id=' . db_param() . ' ORDER by last_modified DESC';
	$t_result = db_query( $t_query, array( (int)$p_bug_id ), 1 );

	return (int)db_result( $t_result );
}

/**
 * Build the bugnotes array for the given bug_id filtered by specified $p_user_access_level.
 * Bugnotes are sorted by date_submitted according to 'bugnote_order' configuration setting.
 * Return BugnoteData class object with raw values from the tables except the field
 * last_modified - it is UNIX_TIMESTAMP.
 * @param integer $p_bug_id             A bug identifier.
 * @param integer $p_user_bugnote_order Sort order.
 * @param integer $p_user_bugnote_limit Number of bugnotes to display to user.
 * @param integer $p_user_id            An user identifier.
 * @return array array of bugnotes
 * @access public
 */
function bugnote_get_all_visible_bugnotes( $p_bug_id, $p_user_bugnote_order, $p_user_bugnote_limit, $p_user_id = null ) {
	if( $p_user_id === null ) {
		$t_user_id = auth_get_current_user_id();
	} else {
		$t_user_id = $p_user_id;
	}

	$t_project_id = bug_get_field( $p_bug_id, 'project_id' );
	$t_user_access_level = user_get_access_level( $t_user_id, $t_project_id );

	$t_all_bugnotes = bugnote_get_all_bugnotes( $p_bug_id );

	$t_private_bugnote_visible = access_compare_level( $t_user_access_level, config_get( 'private_bugnote_threshold' ) );
	$t_time_tracking_visible = access_compare_level( $t_user_access_level, config_get( 'time_tracking_view_threshold' ) );

	$t_bugnotes = array();
	$t_bugnote_count = count( $t_all_bugnotes );
	$t_bugnote_limit = $p_user_bugnote_limit > 0 ? $p_user_bugnote_limit : $t_bugnote_count;
	$t_bugnotes_found = 0;

	# build a list of the latest bugnotes that the user can see
	for( $i = 0; ( $i < $t_bugnote_count ) && ( $t_bugnotes_found < $t_bugnote_limit ); $i++ ) {
		$t_bugnote = array_pop( $t_all_bugnotes );

		if( $t_private_bugnote_visible || $t_bugnote->reporter_id == $t_user_id || ( VS_PUBLIC == $t_bugnote->view_state ) ) {
			# If the access level specified is not enough to see time tracking information
			# then reset it to 0.
			if( !$t_time_tracking_visible ) {
				$t_bugnote->time_tracking = 0;
			}

			$t_bugnotes[$t_bugnotes_found++] = $t_bugnote;
		}
	}

	# reverse the list for users with ascending view preferences
	if( 'ASC' == $p_user_bugnote_order ) {
		$t_bugnotes = array_reverse( $t_bugnotes );
	}

	return $t_bugnotes;
}

/**
 * Build a string that captures all the notes visible to the logged in user along with their
 * metadata.  The string will contain information about each note including reporter, timestamp,
 * time tracking, view state.  This will result in multi-line string with "\n" as the line
 * separator.
 *
 * @param integer $p_bug_id             A bug identifier.
 * @param integer $p_user_bugnote_order Sort order.
 * @param integer $p_user_bugnote_limit Number of bugnotes to display to user.
 * @param integer $p_user_id            An user identifier.
 * @return string The string containing all visible notes.
 * @access public
 */
function bugnote_get_all_visible_as_string( $p_bug_id, $p_user_bugnote_order, $p_user_bugnote_limit, $p_user_id = null ) {
	$t_notes = bugnote_get_all_visible_bugnotes( $p_bug_id, $p_user_bugnote_order, $p_user_bugnote_limit, $p_user_id );
	$t_date_format = config_get( 'normal_date_format' );
	$t_show_time_tracking = access_has_bug_level( config_get( 'time_tracking_view_threshold' ), $p_bug_id );

	$t_output = '';

	foreach( $t_notes as $t_note ) {
		$t_note_string = '@' . user_get_name( $t_note->reporter_id );
		if ( $t_note->view_state != VS_PUBLIC ) {
			$t_note_string .= ' (' . lang_get( 'private' ) . ')';
		}

		$t_note_string .= ' ' . date( $t_date_format, $t_note->date_submitted );

		if ( $t_show_time_tracking && $t_note->note_type == TIME_TRACKING ) {
			$t_time_tracking_hhmm = db_minutes_to_hhmm( $t_note->time_tracking );
			$t_note_string .= ' ' . lang_get( 'time_tracking_time_spent' ) . ' ' . $t_time_tracking_hhmm;
		}

		$t_note_string .= "\n" . $t_note->note . "\n";

		if ( !empty( $t_output ) ) {
			$t_output .= "---\n";
		}

		$t_output .= $t_note_string;
	}

	return $t_output;
}

/**
 * Converts a bugnote database row to a bugnote object.
 *
 * @param array $p_row The bugnote row (including bugnote_text note)
 * @return BugnoteData The bugnote object.
 * @access private
 */
function bugnote_row_to_object( array $p_row ) {
	$t_bugnote = new BugnoteData;

	$t_bugnote->id = $p_row['id'];
	$t_bugnote->bug_id = (int)$p_row['bug_id'];
	$t_bugnote->bugnote_text_id = (int)$p_row['bugnote_text_id'];
	$t_bugnote->note = $p_row['note'];
	$t_bugnote->view_state = (int)$p_row['view_state'];
	$t_bugnote->reporter_id = (int)$p_row['reporter_id'];
	$t_bugnote->date_submitted = (int)$p_row['date_submitted'];
	$t_bugnote->last_modified = (int)$p_row['last_modified'];
	$t_bugnote->note_type = (int)$p_row['note_type'];
	$t_bugnote->note_attr = $p_row['note_attr'];
	$t_bugnote->time_tracking = (int)$p_row['time_tracking'];

	# Handle old bugnotes before setting type to time tracking
	if ( $t_bugnote->time_tracking != 0 ) {
		$t_bugnote->note_type = TIME_TRACKING;
	}

	return $t_bugnote;
}

/**
 * Build the bugnotes array for the given bug_id.
 * Return BugnoteData class object with raw values from the tables except the field
 * last_modified - it is UNIX_TIMESTAMP.
 * The data is not filtered by VIEW_STATE !!
 * @param integer $p_bug_id A bug identifier.
 * @return array array of bugnotes
 * @access public
 */
function bugnote_get_all_bugnotes( $p_bug_id ) {
	global $g_cache_bugnotes_by_bug_id, $g_cache_bugnotes_by_id;

	# the cache should be aware of the sorting order
	if( !isset( $g_cache_bugnotes_by_bug_id[(int)$p_bug_id] ) ) {
		# Now sorting by submit date and id (#11742). The date_submitted
		# column is currently not indexed, but that does not seem to affect
		# performance in a measurable way
		db_param_push();
		$t_query = 'SELECT b.*, t.note
			          	FROM      {bugnote} b
			          	LEFT JOIN {bugnote_text} t ON b.bugnote_text_id = t.id
						WHERE b.bug_id=' . db_param() . '
						ORDER BY b.date_submitted ASC, b.id ASC';
		$t_bugnotes = array();

		# BUILD bugnotes array
		$t_result = db_query( $t_query, array( $p_bug_id ) );

		while( $t_row = db_fetch_array( $t_result ) ) {
			$t_bugnote = bugnote_row_to_object( $t_row );
			$t_bugnotes[] = $t_bugnote;
			bugnote_cache( $t_bugnote );
		}

		$g_cache_bugnotes_by_bug_id[(int)$p_bug_id] = $t_bugnotes;
	}

	return $g_cache_bugnotes_by_bug_id[(int)$p_bug_id];
}

/**
 * Gets the bugnote object given its id.
 *
 * @param int $p_bugnote_id The bugnote id.
 * @return BugnoteData The bugnote object.
 */
function bugnote_get( $p_bugnote_id ) {
	# If bugnote exists but not in cache, it will be added to cache.
	# If bugnote doesn't exist, this will trigger an error.
	bugnote_ensure_exists( $p_bugnote_id );

	global $g_cache_bugnotes_by_id;

	# Return the object from the cache, fetched above.
	if( isset( $g_cache_bugnotes_by_id[(int)$p_bugnote_id] ) ) {
		return $g_cache_bugnotes_by_id[(int)$p_bugnote_id];
	}

	# if we reached here something is wrong, trigger an error.
	trigger_error( ERROR_BUGNOTE_NOT_FOUND, ERROR );
}

/**
 * Update the time_tracking field of the bugnote
 * @param integer $p_bugnote_id    A bugnote identifier.
 * @param string  $p_time_tracking Timetracking string (hh:mm format).
 * @return void
 * @access public
 */
function bugnote_set_time_tracking( $p_bugnote_id, $p_time_tracking ) {
	$c_bugnote_time_tracking = helper_duration_to_minutes( $p_time_tracking );

	db_param_push();
	$t_query = 'UPDATE {bugnote} SET time_tracking = ' . db_param() . ' WHERE id=' . db_param();
	db_query( $t_query, array( $c_bugnote_time_tracking, $p_bugnote_id ) );
}

/**
 * Update the last_modified field of the bugnote
 * @param integer $p_bugnote_id A bugnote identifier.
 * @return void
 * @access public
 */
function bugnote_date_update( $p_bugnote_id ) {
	db_param_push();
	$t_query = 'UPDATE {bugnote} SET last_modified=' . db_param() . ' WHERE id=' . db_param();
	db_query( $t_query, array( db_now(), $p_bugnote_id ) );
}

/**
 * Set the bugnote text
 * @param integer $p_bugnote_id   A bugnote identifier.
 * @param string  $p_bugnote_text The bugnote text to set.
 * @return boolean
 * @access public
 */
function bugnote_set_text( $p_bugnote_id, $p_bugnote_text ) {
	$t_old_text = bugnote_get_text( $p_bugnote_id );

	if( $t_old_text == $p_bugnote_text ) {
		return true;
	}
	# MySQL 4-bytes UTF-8 chars workaround #21101
	$p_bugnote_text = db_mysql_fix_utf8( $p_bugnote_text );


	$t_bug_id = bugnote_get_field( $p_bugnote_id, 'bug_id' );
	$t_bugnote_text_id = bugnote_get_field( $p_bugnote_id, 'bugnote_text_id' );

	# insert an 'original' revision if needed
	if( bug_revision_count( $t_bug_id, REV_BUGNOTE, $p_bugnote_id ) < 1 ) {
		$t_user_id = bugnote_get_field( $p_bugnote_id, 'reporter_id' );
		$t_timestamp = bugnote_get_field( $p_bugnote_id, 'last_modified' );
		bug_revision_add( $t_bug_id, $t_user_id, REV_BUGNOTE, $t_old_text, $p_bugnote_id, $t_timestamp );
	}

	db_param_push();
	$t_query = 'UPDATE {bugnote_text} SET note=' . db_param() . ' WHERE id=' . db_param();
	db_query( $t_query, array( $p_bugnote_text, $t_bugnote_text_id ) );

	# updated the last_updated date
	bugnote_date_update( $p_bugnote_id );
	bug_update_date( $t_bug_id );

	# insert a new revision
	$t_user_id = auth_get_current_user_id();
	$t_revision_id = bug_revision_add( $t_bug_id, $t_user_id, REV_BUGNOTE, $p_bugnote_text, $p_bugnote_id );

	# log new bugnote
	history_log_event_special( $t_bug_id, BUGNOTE_UPDATED, bugnote_format_id( $p_bugnote_id ), $t_revision_id );

	return true;
}

/**
 * Set the view state of the bugnote
 * @param integer $p_bugnote_id A bugnote identifier.
 * @param boolean $p_private    Whether bugnote should be set to private status.
 * @return boolean
 * @access public
 */
function bugnote_set_view_state( $p_bugnote_id, $p_private ) {
	$t_bug_id = bugnote_get_field( $p_bugnote_id, 'bug_id' );

	if( $p_private ) {
		$t_view_state = VS_PRIVATE;
	} else {
		$t_view_state = VS_PUBLIC;
	}

	db_param_push();
	$t_query = 'UPDATE {bugnote} SET view_state=' . db_param() . ' WHERE id=' . db_param();
	db_query( $t_query, array( $t_view_state, $p_bugnote_id ) );

	history_log_event_special( $t_bug_id, BUGNOTE_STATE_CHANGED, $t_view_state, bugnote_format_id( $p_bugnote_id ) );

	return true;
}

/**
 * Pad the bugnote id with the appropriate number of zeros for printing
 * @param integer $p_bugnote_id A bugnote identifier.
 * @return string
 * @access public
 */
function bugnote_format_id( $p_bugnote_id ) {
	$t_padding = config_get( 'display_bugnote_padding' );

	return utf8_str_pad( $p_bugnote_id, $t_padding, '0', STR_PAD_LEFT );
}

/**
 * Returns an array of bugnote stats
 * @param integer $p_bug_id A bug identifier.
 * @param string  $p_from   Starting date (yyyy-mm-dd) inclusive, if blank, then ignored.
 * @param string  $p_to     Ending date (yyyy-mm-dd) inclusive, if blank, then ignored.
 * @return array array of bugnote stats
 * @access public
 */
function bugnote_stats_get_events_array( $p_bug_id, $p_from, $p_to ) {
	$c_to = strtotime( $p_to ) + SECONDS_PER_DAY - 1;
	$c_from = strtotime( $p_from );

	if( !is_blank( $c_from ) ) {
		$t_from_where = ' AND bn.date_submitted >= ' . $c_from;
	} else {
		$t_from_where = '';
	}

	if( !is_blank( $c_to ) ) {
		$t_to_where = ' AND bn.date_submitted <= ' . $c_to;
	} else {
		$t_to_where = '';
	}

	$t_results = array();

	db_param_push();
	$t_query = 'SELECT username, realname, SUM(time_tracking) AS sum_time_tracking
				FROM {user} u, {bugnote} bn
				WHERE u.id = bn.reporter_id AND bn.time_tracking != 0 AND
				bn.bug_id = ' . db_param() . $t_from_where . $t_to_where .
				' GROUP BY u.username, u.realname';
	$t_result = db_query( $t_query, array( $p_bug_id ) );

	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_results[] = $t_row;
	}

	return $t_results;
}

/**
 * Clear a bugnote from the cache or all bug notes if no bugnote id specified.
 * @param integer $p_bugnote_id Identifier to clear (optional).
 * @return boolean
 * @access public
 */
function bugnote_clear_cache( $p_bugnote_id = null ) {
	global $g_cache_bugnotes_by_id, $g_cache_bugnotes_by_bug_id;

	if( null === $p_bugnote_id ) {
		$g_cache_bugnotes_by_id = array();
		$g_cache_bugnotes_by_bug_id = array();
	} else {
		if( isset( $g_cache_bugnotes_by_id[(int)$p_bugnote_id] ) ) {
			$t_note_obj = $g_cache_bugnotes_by_id[(int)$p_bugnote_id];
			# current note id will be unset in the following call
			bugnote_clear_bug_cache( $t_note_obj->bug_id );
		}
	}

	return true;
}

/**
 * Clear the bugnotes related to a bug, or all bugs if no bug id specified.
 * @param integer $p_bug_id Identifier to clear (optional).
 * @return boolean
 * @access public
 */
function bugnote_clear_bug_cache( $p_bug_id = null ) {
	global $g_cache_bugnotes_by_bug_id, $g_cache_bugnotes_by_id;

	if( null === $p_bug_id ) {
		$g_cache_bugnotes_by_bug_id = array();
		$g_cache_bugnotes_by_id = array();
	} else {
		if( isset( $g_cache_bugnotes_by_bug_id[(int)$p_bug_id] ) ) {
			foreach( $g_cache_bugnotes_by_bug_id[(int)$p_bug_id] as $t_note_obj ) {
				unset( $g_cache_bugnotes_by_id[(int)$t_note_obj->id] );
			}
			unset( $g_cache_bugnotes_by_bug_id[(int)$p_bug_id] );
		}
	}

	return true;
}