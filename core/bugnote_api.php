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
 * BugNote API
 * @package CoreAPI
 * @subpackage BugnoteAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * requires current_user_api
 */
require_once( 'current_user_api.php' );
/**
 * requires email_api
 */
require_once( 'email_api.php' );
/**
 * requires history_api
 */
require_once( 'history_api.php' );
/**
 * requires bug_api
 */
require_once( 'bug_api.php' );

/**
 * Bugnote Data Structure Definition
 * @package MantisBT
 * @subpackage classes
 */
class BugnoteData {
	var $id;
	var $bug_id;
	var $reporter_id;
	var $note;
	var $view_state;
	var $date_submitted;
	var $last_modified;
	var $note_type;
	var $note_attr;
	var $time_tracking;
}

/**
 * Check if a bugnote with the given ID exists
 * return true if the bugnote exists, false otherwise
 * @param int $p_bugnote_id bugnote id
 * @return bool
 * @access public
 */
function bugnote_exists( $p_bugnote_id ) {
	$c_bugnote_id = db_prepare_int( $p_bugnote_id );
	$t_bugnote_table = db_get_table( 'mantis_bugnote_table' );

	$query = "SELECT COUNT(*)
		          	FROM $t_bugnote_table
		          	WHERE id=" . db_param();
	$result = db_query_bound( $query, Array( $c_bugnote_id ) );

	if( 0 == db_result( $result ) ) {
		return false;
	} else {
		return true;
	}
}

/**
 * Check if a bugnote with the given ID exists
 * return true if the bugnote exists, raise an error if not
 * @param int $p_bugnote_id bugnote id
 * @access public
 */
function bugnote_ensure_exists( $p_bugnote_id ) {
	if( !bugnote_exists( $p_bugnote_id ) ) {
		trigger_error( ERROR_BUGNOTE_NOT_FOUND, ERROR );
	}
}

/**
 * Check if the given user is the reporter of the bugnote
 * return true if the user is the reporter, false otherwise
 * @param int $p_bugnote_id bugnote id
 * @param int $p_user_id user id
 * @return bool
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
 * @param int $p_bug_id bug id
 * @param string $p_bugnote_text bugnote text
 * @param string $p_time_tracking hh:mm string
 * @param bool $p_private whether bugnote is private
 * @param int $p_type bugnote type
 * @param string $p_attr
 * @param int $p_user_id user id
 * @param bool $p_send_email generate email?
 * @return false|int false or indicating bugnote id added
 * @access public
 */
function bugnote_add( $p_bug_id, $p_bugnote_text, $p_time_tracking = '0:00', $p_private = false, $p_type = 0, $p_attr = '', $p_user_id = null, $p_send_email = TRUE, $p_log_history = TRUE) {
	$c_bug_id = db_prepare_int( $p_bug_id );
	$c_time_tracking = helper_duration_to_minutes( $p_time_tracking );
	$c_private = db_prepare_bool( $p_private );
	$c_type = db_prepare_int( $p_type );

	$t_bugnote_text_table = db_get_table( 'mantis_bugnote_text_table' );
	$t_bugnote_table = db_get_table( 'mantis_bugnote_table' );

	$t_time_tracking_enabled = config_get( 'time_tracking_enabled' );
	$t_time_tracking_without_note = config_get( 'time_tracking_without_note' );

	if( ON == $t_time_tracking_enabled && $c_time_tracking > 0 ) {
		if( is_blank( $p_bugnote_text ) && OFF == $t_time_tracking_without_note ) {
			error_parameters( lang_get( 'bugnote' ) );
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		}
		$c_type = TIME_TRACKING;
	} else if( is_blank( $p_bugnote_text ) ) {
		return false;
	}

	$t_bugnote_text = $p_bugnote_text;

	# Event integration
	$t_bugnote_text = event_signal( 'EVENT_BUGNOTE_DATA', $t_bugnote_text, $c_bug_id );

	# insert bugnote text
	$query = 'INSERT INTO ' . $t_bugnote_text_table . ' ( note ) VALUES ( ' . db_param() . ' )';
	db_query_bound( $query, Array( $t_bugnote_text ) );

	# retrieve bugnote text id number
	$t_bugnote_text_id = db_insert_id( $t_bugnote_text_table );

	# get user information
	if( $p_user_id === null ) {
		$c_user_id = auth_get_current_user_id();
	} else {
		$c_user_id = db_prepare_int( $p_user_id );
	}

	# Check for private bugnotes.
	# @@@ VB: Should we allow users to report private bugnotes, and possibly see only their own private ones
	if( $p_private && access_has_bug_level( config_get( 'private_bugnote_threshold' ), $p_bug_id, $c_user_id ) ) {
		$t_view_state = VS_PRIVATE;
	} else {
		$t_view_state = VS_PUBLIC;
	}

	# insert bugnote info
	$query = "INSERT INTO $t_bugnote_table
				(bug_id, reporter_id, bugnote_text_id, view_state, date_submitted, last_modified, note_type, note_attr, time_tracking )
			VALUES
				(" . db_param() . ', ' . db_param() . ',' . db_param() . ', ' . db_param() . ', ' . db_param() . ',' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ' )';
	db_query_bound( $query, Array( $c_bug_id, $c_user_id, $t_bugnote_text_id, $t_view_state, db_now(), db_now(), $c_type, $p_attr, $c_time_tracking ) );

	# get bugnote id
	$t_bugnote_id = db_insert_id( $t_bugnote_table );

	# update bug last updated
	bug_update_date( $p_bug_id );

	# log new bug
	if ( TRUE == $p_log_history)
    	history_log_event_special( $p_bug_id, BUGNOTE_ADDED, bugnote_format_id( $t_bugnote_id ) );

	# if it was FEEDBACK its NEW_ now
	if ( config_get( 'reassign_on_feedback' ) &&
		bug_get_field( $p_bug_id, 'status' ) == config_get( 'bug_feedback_status' ) &&
		bug_get_field( $p_bug_id, 'reporter_id' ) == $c_user_id ) {

		if ( bug_get_field( $p_bug_id, 'handler_id') == 0 ) {
			bug_set_field( $p_bug_id, 'status', config_get( 'bug_submit_status' ) );
		} else {
			bug_set_field( $p_bug_id, 'status', config_get( 'bug_assigned_status' ) );
		}
	}

	# Event integration
	event_signal( 'EVENT_BUGNOTE_ADD', array( $p_bug_id, $t_bugnote_id ) );

	# only send email if the text is not blank, otherwise, it is just recording of time without a comment.
	if( TRUE == $p_send_email && !is_blank( $t_bugnote_text ) ) {
		email_bugnote_add( $p_bug_id );
	}

	return $t_bugnote_id;
}

/**
 * Delete a bugnote
 * @param int $p_bugnote_id bug note id
 * @return bool
 * @access public
 */
function bugnote_delete( $p_bugnote_id ) {
	$c_bugnote_id = db_prepare_int( $p_bugnote_id );
	$t_bug_id = bugnote_get_field( $p_bugnote_id, 'bug_id' );
	$t_bugnote_text_id = bugnote_get_field( $p_bugnote_id, 'bugnote_text_id' );
	$t_bugnote_text_table = db_get_table( 'mantis_bugnote_text_table' );
	$t_bugnote_table = db_get_table( 'mantis_bugnote_table' );

	# Remove the bugnote
	$query = 'DELETE FROM ' . $t_bugnote_table . ' WHERE id=' . db_param();
	db_query_bound( $query, Array( $c_bugnote_id ) );

	# Remove the bugnote text
	$query = 'DELETE FROM ' . $t_bugnote_text_table . ' WHERE id=' . db_param();
	db_query_bound( $query, Array( $t_bugnote_text_id ) );

	# log deletion of bug
	history_log_event_special( $t_bug_id, BUGNOTE_DELETED, bugnote_format_id( $p_bugnote_id ) );

	return true;
}

/**
 * delete all bugnotes associated with the given bug
 * @param int $p_bug_id bug id
 * @return bool
 * @access public
 */
function bugnote_delete_all( $p_bug_id ) {
	$c_bug_id = db_prepare_int( $p_bug_id );
	$t_bugnote_table = db_get_table( 'mantis_bugnote_table' );
	$t_bugnote_text_table = db_get_table( 'mantis_bugnote_text_table' );

	# Delete the bugnote text items
	$query = "SELECT bugnote_text_id
		          	FROM $t_bugnote_table
		          	WHERE bug_id=" . db_param();
	$result = db_query_bound( $query, Array( $c_bug_id ) );
	$bugnote_count = db_num_rows( $result );
	for( $i = 0;$i < $bugnote_count;$i++ ) {
		$row = db_fetch_array( $result );
		$t_bugnote_text_id = $row['bugnote_text_id'];

		# Delete the corresponding bugnote texts
		$query = "DELETE FROM $t_bugnote_text_table
			          	WHERE id=" . db_param();
		db_query_bound( $query, Array( $t_bugnote_text_id ) );
	}

	# Delete the corresponding bugnotes
	$query = "DELETE FROM $t_bugnote_table
		          	WHERE bug_id=" . db_param();
	$result = db_query_bound( $query, Array( $c_bug_id ) );

	# db_query errors on failure so:
	return true;
}

/**
 * Get the text associated with the bugnote
 * @param int $p_bugnote_id bugnote id
 * @return string bugnote text
 * @access public
 */
function bugnote_get_text( $p_bugnote_id ) {
	$t_bugnote_text_id = bugnote_get_field( $p_bugnote_id, 'bugnote_text_id' );
	$t_bugnote_text_table = db_get_table( 'mantis_bugnote_text_table' );

	# grab the bugnote text
	$query = "SELECT note
		          	FROM $t_bugnote_text_table
		          	WHERE id=" . db_param();
	$result = db_query_bound( $query, Array( $t_bugnote_text_id ) );

	return db_result( $result );
}

/**
 * Get a field for the given bugnote
 * @param int $p_bugnote_id bugnote id
 * @param string $p_field_name field name
 * @return string field value
 * @access public
 */
function bugnote_get_field( $p_bugnote_id, $p_field_name ) {
	global $g_cache_bugnote;

	if( isset( $g_cache_bugnote[(int)$p_bugnote_id] ) ) {
		return $g_cache_bugnote[(int)$p_bugnote_id]->$p_field_name;
	}

	$c_bugnote_id = db_prepare_int( $p_bugnote_id );
	$c_field_name = db_prepare_string( $p_field_name );
	$t_bugnote_table = db_get_table( 'mantis_bugnote_table' );

	$query = "SELECT $c_field_name
		          	FROM $t_bugnote_table
		          	WHERE id=" . db_param();
	$result = db_query_bound( $query, Array( $c_bugnote_id ), 1 );

	return db_result( $result );
}

/**
 * Get latest bugnote id
 * @param int $p_bug_id bug id
 * @return int latest bugnote id
 * @access public
 */
function bugnote_get_latest_id( $p_bug_id ) {
	$c_bug_id = db_prepare_int( $p_bug_id );
	$t_bugnote_table = db_get_table( 'mantis_bugnote_table' );

	$query = "SELECT id
		          	FROM $t_bugnote_table
		          	WHERE bug_id=" . db_param() . "
		          	ORDER by last_modified DESC";
	$result = db_query_bound( $query, Array( $c_bug_id ), 1 );

	return (int)db_result( $result );
}

/**
 * Build the bugnotes array for the given bug_id filtered by specified $p_user_access_level.
 * Bugnotes are sorted by date_submitted according to 'bugnote_order' configuration setting.
 * Return BugnoteData class object with raw values from the tables except the field
 * last_modified - it is UNIX_TIMESTAMP.
 * @param int $p_bug_id bug id
 * @param int $p_user_bugnote_order sort order
 * @param int $p_user_bugnote_limit number of bugnotes to display to user
 * @param int $p_user_id user id
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
	$t_private_bugnote_threshold = config_get( 'private_bugnote_threshold' );

	$t_private_bugnote_visible = access_compare_level( $t_user_access_level, config_get( 'private_bugnote_threshold' ) );
	$t_time_tracking_visible = access_compare_level( $t_user_access_level, config_get( 'time_tracking_view_threshold' ) );

	$t_bugnotes = array();
	$t_bugnote_count = count( $t_all_bugnotes );
	$t_bugnote_limit = $p_user_bugnote_limit > 0 ? $p_user_bugnote_limit : $t_bugnote_count;
	$t_bugnotes_found = 0;

	# build a list of the latest bugnotes that the user can see
	for ( $i = 0; ( $i < $t_bugnote_count ) && ( $t_bugnotes_found < $t_bugnote_limit ); $i++ ) {
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
	if ( 'ASC' == $p_user_bugnote_order ) {
		$t_bugnotes = array_reverse( $t_bugnotes );
	}

	return $t_bugnotes;
}

/**
 * Build the bugnotes array for the given bug_id.
 * Return BugnoteData class object with raw values from the tables except the field
 * last_modified - it is UNIX_TIMESTAMP.
 * The data is not filtered by VIEW_STATE !!
 * @param int $p_bug_id bug id
 * @return array array of bugnotes
 * @access public
 */
function bugnote_get_all_bugnotes( $p_bug_id ) {
	global $g_cache_bugnotes, $g_cache_bugnote;

	if( !isset( $g_cache_bugnotes ) ) {
		$g_cache_bugnotes = array();
	}

	if( !isset( $g_cache_bugnote ) ) {
		$g_cache_bugnote = array();
	}

	# the cache should be aware of the sorting order
	if( !isset( $g_cache_bugnotes[(int)$p_bug_id] ) ) {
		$t_bugnote_table = db_get_table( 'mantis_bugnote_table' );
		$t_bugnote_text_table = db_get_table( 'mantis_bugnote_text_table' );

		# sort by bugnote id which should be more accurate than submit date, since two bugnotes
		# may be submitted at the same time if submitted using a script (eg: MantisConnect).
		$t_query = "SELECT b.*, t.note
			          	FROM      $t_bugnote_table b
			          	LEFT JOIN $t_bugnote_text_table t ON b.bugnote_text_id = t.id
						WHERE b.bug_id=" . db_param() . '
						ORDER BY b.id ASC';
		$t_bugnotes = array();

		# BUILD bugnotes array
		$t_result = db_query_bound( $t_query, array( $p_bug_id ) );

		while( $row = db_fetch_array( $t_result ) ) {
			$t_bugnote = new BugnoteData;

			$t_bugnote->id = $row['id'];
			$t_bugnote->bug_id = $row['bug_id'];
			$t_bugnote->note = $row['note'];
			$t_bugnote->view_state = $row['view_state'];
			$t_bugnote->reporter_id = $row['reporter_id'];
			$t_bugnote->date_submitted = $row['date_submitted'];
			$t_bugnote->last_modified = $row['last_modified'];
			$t_bugnote->note_type = $row['note_type'];
			$t_bugnote->note_attr = $row['note_attr'];
			$t_bugnote->time_tracking = $row['time_tracking'];

			$t_bugnotes[] = $t_bugnote;
			$g_cache_bugnote[(int)$t_bugnote->id] = $t_bugnote;
		}

		$g_cache_bugnotes[(int)$p_bug_id] = $t_bugnotes;
	}

	return $g_cache_bugnotes[(int)$p_bug_id];
}

/**
 * Update the time_tracking field of the bugnote
 * @param int $p_bugnote_id bugnote id
 * @param string $p_time_tracking timetracking string (hh:mm format)
 * @return bool
 * @access public
 */
function bugnote_set_time_tracking( $p_bugnote_id, $p_time_tracking ) {
	$c_bugnote_id = db_prepare_int( $p_bugnote_id );
	$c_bugnote_time_tracking = helper_duration_to_minutes( $p_time_tracking );
	$t_bugnote_table = db_get_table( 'mantis_bugnote_table' );

	$query = "UPDATE $t_bugnote_table
				SET time_tracking = " . db_param() . "
				WHERE id=" . db_param();
	db_query_bound( $query, Array( $c_bugnote_time_tracking, $c_bugnote_id ) );

	# db_query errors if there was a problem so:
	return true;
}

/**
 * Update the last_modified field of the bugnote
 * @param int $p_bugnote_id bugnote id
 * @return bool
 * @access public
 */
function bugnote_date_update( $p_bugnote_id ) {
	$c_bugnote_id = db_prepare_int( $p_bugnote_id );
	$t_bugnote_table = db_get_table( 'mantis_bugnote_table' );

	$query = "UPDATE $t_bugnote_table
					SET last_modified=" . db_param() . "
					WHERE id=" . db_param();
	db_query_bound( $query, Array( db_now(), $c_bugnote_id ) );

	# db_query errors if there was a problem so:
	return true;
}

/**
 * Set the bugnote text
 * @param int $p_bugnote_id bugnote id
 * @param string $p_bugnote_text bugnote text
 * @return bool
 * @access public
 */
function bugnote_set_text( $p_bugnote_id, $p_bugnote_text ) {
	$t_old_text = bugnote_get_text( $p_bugnote_id );

	if ( $t_old_text == $p_bugnote_text ) {
		return true;
	}

	$t_bug_id = bugnote_get_field( $p_bugnote_id, 'bug_id' );
	$t_bugnote_text_id = bugnote_get_field( $p_bugnote_id, 'bugnote_text_id' );
	$t_bugnote_text_table = db_get_table( 'mantis_bugnote_text_table' );

	# insert an 'original' revision if needed
	if ( bug_revision_count( $t_bug_id, REV_BUGNOTE, $p_bugnote_id ) < 1 ) {
		$t_user_id = bugnote_get_field( $p_bugnote_id, 'reporter_id' );
		$t_timestamp = bugnote_get_field( $p_bugnote_id, 'last_modified' );
		bug_revision_add( $t_bug_id, $t_user_id, REV_BUGNOTE, $t_old_text, $p_bugnote_id, $t_timestamp );
	}

	$query = "UPDATE $t_bugnote_text_table
			SET note=" . db_param() . " WHERE id=" . db_param();
	db_query_bound( $query, Array( $p_bugnote_text, $t_bugnote_text_id ) );

	# updated the last_updated date
	bugnote_date_update( $p_bugnote_id );

	# insert a new revision
	$t_user_id = auth_get_current_user_id();
	$t_revision_id = bug_revision_add( $t_bug_id, $t_user_id, REV_BUGNOTE, $p_bugnote_text, $p_bugnote_id );

	# log new bugnote
	history_log_event_special( $t_bug_id, BUGNOTE_UPDATED, bugnote_format_id( $p_bugnote_id ), $t_revision_id );

	return true;
}

/**
 * Set the view state of the bugnote
 * @param int $p_bugnote_id bugnote id
 * @param bool $p_private
 * @return bool
 * @access public
 */
function bugnote_set_view_state( $p_bugnote_id, $p_private ) {
	$c_bugnote_id = db_prepare_int( $p_bugnote_id );
	$t_bug_id = bugnote_get_field( $p_bugnote_id, 'bug_id' );

	if( $p_private ) {
		$t_view_state = VS_PRIVATE;
	} else {
		$t_view_state = VS_PUBLIC;
	}

	$t_bugnote_table = db_get_table( 'mantis_bugnote_table' );

	$query = "UPDATE $t_bugnote_table
		          	SET view_state=" . db_param() . "
		          	WHERE id=" . db_param();
	db_query_bound( $query, Array( $t_view_state, $c_bugnote_id ) );

	history_log_event_special( $t_bug_id, BUGNOTE_STATE_CHANGED, $t_view_state, bugnote_format_id( $p_bugnote_id ) );

	return true;
}

/**
 * Pad the bugnote id with the appropriate number of zeros for printing
 * @param int $p_bugnote_id bugnote id
 * @return string
 * @access public
 */
function bugnote_format_id( $p_bugnote_id ) {
	$t_padding = config_get( 'display_bugnote_padding' );

	return utf8_str_pad( $p_bugnote_id, $t_padding, '0', STR_PAD_LEFT );
}

/**
 * Returns an array of bugnote stats
 * @param int $p_bug_id bug id
 * @param string $p_from Starting date (yyyy-mm-dd) inclusive, if blank, then ignored.
 * @param string $p_to Ending date (yyyy-mm-dd) inclusive, if blank, then ignored.
 * @return array array of bugnote stats
 * @access public
 */
function bugnote_stats_get_events_array( $p_bug_id, $p_from, $p_to ) {
	$c_bug_id = db_prepare_int( $p_bug_id );
	$c_to = strtotime( $p_to ) + SECONDS_PER_DAY - 1;
	$c_from = strtotime( $p_from );

	$t_user_table = db_get_table( 'mantis_user_table' );
	$t_bugnote_table = db_get_table( 'mantis_bugnote_table' );

	if( !is_blank( $c_from ) ) {
		$t_from_where = " AND bn.date_submitted >= $c_from ";
	} else {
		$t_from_where = '';
	}

	if( !is_blank( $c_to ) ) {
		$t_to_where = " AND bn.date_submitted <= $c_to ";
	} else {
		$t_to_where = '';
	}

	$t_results = array();

	$query = "SELECT username, realname, SUM(time_tracking) AS sum_time_tracking
				FROM $t_user_table u, $t_bugnote_table bn
				WHERE u.id = bn.reporter_id AND bn.time_tracking != 0 AND
				bn.bug_id = '$c_bug_id'
				$t_from_where $t_to_where
				GROUP BY u.username, u.realname";

	$result = db_query( $query );

	while( $row = db_fetch_array( $result ) ) {
		$t_results[] = $row;
	}

	return $t_results;
}

/**
 * Returns an array of bugnote stats
 * @param int $p_project_id project id
 * @param string $p_from Starting date (yyyy-mm-dd) inclusive, if blank, then ignored.
 * @param string $p_to Ending date (yyyy-mm-dd) inclusive, if blank, then ignored.
 * @param int $p_cost cost
 * @return array array of bugnote stats
 * @access public
 */
function bugnote_stats_get_project_array( $p_project_id, $p_from, $p_to, $p_cost ) {
	$c_project_id = db_prepare_int( $p_project_id );

	$c_to = strtotime( $p_to ) + SECONDS_PER_DAY - 1;
	$c_from = strtotime( $p_from );

	if ( $c_to === false || $c_from === false ) {
		error_parameters( array( $p_form, $p_to ) );
		trigger_error( ERROR_GENERIC, ERROR );
	}

	$c_cost = db_prepare_double( $p_cost );

	$t_bug_table = db_get_table( 'mantis_bug_table' );
	$t_user_table = db_get_table( 'mantis_user_table' );
	$t_bugnote_table = db_get_table( 'mantis_bugnote_table' );

	if( !is_blank( $c_from ) ) {
		$t_from_where = " AND bn.date_submitted >= $c_from";
	} else {
		$t_from_where = '';
	}

	if( !is_blank( $c_to ) ) {
		$t_to_where = " AND bn.date_submitted <= $c_to";
	} else {
		$t_to_where = '';
	}

	if( ALL_PROJECTS != $c_project_id ) {
		$t_project_where = " AND b.project_id = '$c_project_id' AND bn.bug_id = b.id ";
	} else {
		$t_project_where = '';
	}

	$t_results = array();

	$query = "SELECT username, realname, summary, bn.bug_id, SUM(time_tracking) AS sum_time_tracking
			FROM $t_user_table u, $t_bugnote_table bn, $t_bug_table b
			WHERE u.id = bn.reporter_id AND bn.time_tracking != 0 AND bn.bug_id = b.id
			$t_project_where $t_from_where $t_to_where
			GROUP BY bn.bug_id, u.username, u.realname, b.summary
			ORDER BY bn.bug_id";

	$result = db_query( $query );

	$t_cost_min = $c_cost / 60;

	while( $row = db_fetch_array( $result ) ) {
		$t_total_cost = $t_cost_min * $row['sum_time_tracking'];
		$row['cost'] = $t_total_cost;
		$t_results[] = $row;
	}

	return $t_results;
}

/**
 * Clear a bugnote from the cache or all bug notes if no bugnote id specified.
 * @param int bugnote id to clear (optional)
 * @return null
 * @access public
 */
function bugnote_clear_cache( $p_bugnote_id = null ) {
	global $g_cache_bugnote, $g_cache_bugnotes;

	if( null === $p_bugnote_id ) {
		$g_cache_bugnote = array();
	} else {
		unset( $g_cache_bugnote[(int) $p_bugnote_id] );
	}
	$g_cache_bugnotes = array();

	return true;
}
