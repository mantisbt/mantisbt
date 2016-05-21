<?php
# Mantis - a php based bugtracking system

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
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Bug Revision API
 *
 * @package CoreAPI
 * @subpackage BugRevisionAPI
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses constant_inc.php
 * @uses database_api.php
 */

require_api( 'constant_inc.php' );
require_api( 'database_api.php' );

/**
 * Add a new revision to a bug history.
 * @param integer $p_bug_id     A bug identifier.
 * @param integer $p_user_id    User ID.
 * @param integer $p_type       Revision Type.
 * @param string  $p_value      Value.
 * @param integer $p_bugnote_id A Bugnote ID.
 * @param integer $p_timestamp  Integer Timestamp.
 * @return int Revision ID
 */
function bug_revision_add( $p_bug_id, $p_user_id, $p_type, $p_value, $p_bugnote_id = 0, $p_timestamp = null ) {
	if( $p_type <= REV_ANY ) {
		return null;
	}

	$t_last = bug_revision_last( $p_bug_id, $p_type );

	# Don't save a revision twice if nothing has changed
	if( !is_null( $t_last ) &&
		$p_value == $t_last['value'] ) {

		return $t_last['id'];
	}

	if( $p_timestamp === null ) {
		$t_timestamp = db_now();
	} else {
		$t_timestamp = $p_timestamp;
	}

	db_param_push();
	$t_query = 'INSERT INTO {bug_revision} (
			bug_id, bugnote_id, user_id,
			timestamp, type, value
		) VALUES ( ' .
			db_param() . ', ' . db_param() . ', ' . db_param() . ', ' .
			db_param() . ', ' . db_param() . ', ' . db_param() . ' )';
	db_query( $t_query, array(
			$p_bug_id, $p_bugnote_id, $p_user_id,
			$t_timestamp, $p_type, $p_value ) );

	return db_insert_id( db_get_table( 'bug_revision' ) );
}

/**
 * Check if a bug revision exists
 * @param integer $p_revision_id A bug revision identifier.
 * @return boolean Whether or not the bug revision exists
 */
function bug_revision_exists( $p_revision_id ) {
	db_param_push();
	$t_query = 'SELECT id FROM {bug_revision} WHERE id=' . db_param();
	$t_result = db_query( $t_query, array( $p_revision_id ) );

	if( !db_result( $t_result ) ) {
		return false;
	}

	return true;
}

/**
 * Get a row of data for a given revision ID.
 * @param integer $p_revision_id A bug revision identifier.
 * @return array Revision data row
 */
function bug_revision_get( $p_revision_id ) {
	db_param_push();
	$t_query = 'SELECT * FROM {bug_revision} WHERE id=' . db_param();
	$t_result = db_query( $t_query, array( $p_revision_id ) );

	$t_row = db_fetch_array( $t_result );
	if( !$t_row ) {
		trigger_error( ERROR_BUG_REVISION_NOT_FOUND, ERROR );
	}

	return $t_row;
}

/**
 * Get the name of the type of a bug revision.
 * @param integer $p_revision_type_id A bug revision type ID (see constant_inc.php for possible values).
 * @return string Name of the type of the bug revision
 */
function bug_revision_get_type_name( $p_revision_type_id ) {
	$t_type_name = '';
	switch( $p_revision_type_id ) {
		case REV_DESCRIPTION:
			$t_type_name = lang_get( 'description' );
			break;
		case REV_STEPS_TO_REPRODUCE:
			$t_type_name = lang_get( 'steps_to_reproduce' );
			break;
		case REV_ADDITIONAL_INFO:
			$t_type_name = lang_get( 'additional_information' );
			break;
		case REV_BUGNOTE:
			$t_type_name = lang_get( 'bugnote' );
			break;
	}
	return $t_type_name;
}

/**
 * Remove one or more bug revisions from the bug history.
 * @param integer $p_revision_id A bug revision identifier, or array of revision identifiers.
 * @return void
 */
function bug_revision_drop( $p_revision_id ) {
	db_param_push();

	if( is_array( $p_revision_id ) ) {
		$t_revisions = array();
		$t_first = true;
		$t_query = 'DELETE FROM {bug_revision} WHERE id IN ( ';

		# TODO: Fetch bug revisions in one query (and cache them)
		foreach( $p_revision_id as $t_rev_id ) {
			$t_query .= ( $t_first ? db_param() : ', ' . db_param() );
			$t_revisions[$t_rev_id] = bug_revision_get( $t_rev_id );
		}

		$t_query .= ' )';
		db_query( $t_query, $p_revision_id );
		foreach( $p_revision_id as $t_rev_id ) {
			if( $t_revisions[$t_rev_id]['type'] == REV_BUGNOTE ) {
				history_log_event_special( $t_revisions[$t_rev_id]['bug_id'], BUGNOTE_REVISION_DROPPED, bugnote_format_id( $t_rev_id ), $t_revisions[$t_rev_id]['bugnote_id'] );
			} else {
				history_log_event_special( $t_revisions[$t_rev_id]['bug_id'], BUG_REVISION_DROPPED, bugnote_format_id( $t_rev_id ), $t_revisions[$t_rev_id]['type'] );
			}
		}
	} else {
		$t_revision = bug_revision_get( $p_revision_id );
		$t_query = 'DELETE FROM {bug_revision} WHERE id=' . db_param();
		db_query( $t_query, array( $p_revision_id ) );
		if( $t_revision['type'] == REV_BUGNOTE ) {
			history_log_event_special( $t_revision['bug_id'], BUGNOTE_REVISION_DROPPED, bugnote_format_id( $p_revision_id ), $t_revision['bugnote_id'] );
		} else {
			history_log_event_special( $t_revision['bug_id'], BUG_REVISION_DROPPED, bugnote_format_id( $p_revision_id ), $t_revision['type'] );
		}
	}
}

/**
 * Retrieve a count of revisions to the bug's information.
 * @param integer $p_bug_id     A bug identifier.
 * @param integer $p_type       Revision Type (optional).
 * @param integer $p_bugnote_id A bugnote identifier (optional).
 * @return array|null Array of Revision rows
 */
function bug_revision_count( $p_bug_id, $p_type = REV_ANY, $p_bugnote_id = 0 ) {
	db_param_push();
	$t_params = array( $p_bug_id );
	$t_query = 'SELECT COUNT(id) FROM {bug_revision} WHERE bug_id=' . db_param();

	if( REV_ANY < $p_type ) {
		$t_query .= ' AND type=' . db_param();
		$t_params[] = $p_type;
	}

	if( $p_bugnote_id > 0 ) {
		$t_query .= ' AND bugnote_id=' . db_param();
		$t_params[] = $p_bugnote_id;
	} else {
		$t_query .= ' AND bugnote_id=0';
	}

	$t_result = db_query( $t_query, $t_params );

	return db_result( $t_result );
}

/**
 * Delete all revision history for a bug.
 * @param integer $p_bug_id     A bug identifier.
 * @param integer $p_bugnote_id A bugnote identifier (optional).
 * @return void
 */
function bug_revision_delete( $p_bug_id, $p_bugnote_id = 0 ) {
	db_param_push();
	if( $p_bugnote_id < 1 ) {
		$t_query = 'DELETE FROM {bug_revision} WHERE bug_id=' . db_param();
		db_query( $t_query, array( $p_bug_id ) );
	} else {
		$t_query = 'DELETE FROM {bug_revision} WHERE bugnote_id=' . db_param();
		db_query( $t_query, array( $p_bugnote_id ) );
	}
}

/**
 * Retrieve the last change to the bug's information.
 * @param integer $p_bug_id     A bug identifier.
 * @param integer $p_type       Revision Type (optional).
 * @param integer $p_bugnote_id A bugnote identifier (optional).
 * @return null|array Revision row
 */
function bug_revision_last( $p_bug_id, $p_type = REV_ANY, $p_bugnote_id = 0 ) {
	db_param_push();
	$t_params = array( $p_bug_id );
	$t_query = 'SELECT * FROM {bug_revision} WHERE bug_id=' . db_param();

	if( REV_ANY < $p_type ) {
		$t_query .= ' AND type=' . db_param();
		$t_params[] = $p_type;
	}

	if( $p_bugnote_id > 0 ) {
		$t_query .= ' AND bugnote_id=' . db_param();
		$t_params[] = $p_bugnote_id;
	} else {
		$t_query .= ' AND bugnote_id=0';
	}

	$t_query .= ' ORDER BY timestamp DESC';
	$t_result = db_query( $t_query, $t_params, 1 );

	$t_row = db_fetch_array( $t_result );
	if( $t_row ) {
		return $t_row;
	} else {
		return null;
	}
}

/**
 * Retrieve a full list of changes to the bug's information.
 * @param integer $p_bug_id     A bug identifier.
 * @param integer $p_type       Revision Type.
 * @param integer $p_bugnote_id A bugnote identifier.
 * @return array/null Array of Revision rows
 */
function bug_revision_list( $p_bug_id, $p_type = REV_ANY, $p_bugnote_id = 0 ) {
	db_param_push();
	$t_params = array( $p_bug_id );
	$t_query = 'SELECT * FROM {bug_revision} WHERE bug_id=' . db_param();

	if( REV_ANY < $p_type ) {
		$t_query .= ' AND type=' . db_param();
		$t_params[] = $p_type;
	}

	if( $p_bugnote_id > 0 ) {
		$t_query .= ' AND bugnote_id=' . db_param();
		$t_params[] = $p_bugnote_id;
	} else {
		$t_query .= ' AND bugnote_id=0';
	}

	$t_query .= ' ORDER BY id DESC';
	$t_result = db_query( $t_query, $t_params );

	$t_revisions = array();
	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_revisions[$t_row['id']] = $t_row;
	}

	return $t_revisions;
}

/**
 * Retrieve a list of changes to a bug of the same type as the
 * given revision ID.
 * @param integer $p_rev_id A bug revision identifier.
 * @return array|null Array of Revision rows
 */
function bug_revision_like( $p_rev_id ) {
	db_param_push();
	$t_query = 'SELECT bug_id, bugnote_id, type FROM {bug_revision} WHERE id=' . db_param();
	$t_result = db_query( $t_query, array( $p_rev_id ) );

	$t_row = db_fetch_array( $t_result );

	if( !$t_row ) {
		trigger_error( ERROR_BUG_REVISION_NOT_FOUND, ERROR );
	}

	$t_bug_id = $t_row['bug_id'];
	$t_bugnote_id = $t_row['bugnote_id'];
	$t_type = $t_row['type'];

	db_param_push();
	$t_params = array( $t_bug_id );
	$t_query = 'SELECT * FROM {bug_revision} WHERE bug_id=' . db_param();

	if( REV_ANY < $t_type ) {
		$t_query .= ' AND type=' . db_param();
		$t_params[] = $t_type;
	}

	if( $t_bugnote_id > 0 ) {
		$t_query .= ' AND bugnote_id=' . db_param();
		$t_params[] = $t_bugnote_id;
	} else {
		$t_query .= ' AND bugnote_id=0';
	}

	$t_query .= ' ORDER BY id DESC';
	$t_result = db_query( $t_query, $t_params );

	$t_revisions = array();
	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_revisions[$t_row['id']] = $t_row;
	}

	return $t_revisions;
}

