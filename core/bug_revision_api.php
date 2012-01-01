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
 * This API allows bugs to keep a read-only history of changes to longtext boxes.
 *
 * @package CoreAPI
 * @subpackage BugRevisionAPI
 * @copyright Copyright (C) 2002 - 2012  Mantis Team   - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * Add a new revision to a bug history.
 * @param int $p_bug_id Bug ID
 * @param int $p_user_id User ID
 * @param int $p_type Revision Type
 * @param string $p_value Value
 * @param int $p_bugnote_id Bugnote ID
 * @param int $p_timestamp Timestamp(int)
 * @return int Revision ID
 */
function bug_revision_add( $p_bug_id, $p_user_id, $p_type, $p_value, $p_bugnote_id=0, $p_timestamp = null ) {
	if ( $p_type <= REV_ANY ) {
		return null;
	}

	$t_bug_rev_table = db_get_table( 'mantis_bug_revision_table' );

	$t_last = bug_revision_last( $p_bug_id, $p_type );

	# Don't save a revision twice if nothing has changed
	if ( !is_null( $t_last ) &&
		$p_value == $t_last['value'] ) {

		return $t_last['id'];
	}

	if ( $p_timestamp === null ) {
		$t_timestamp = db_now();
	} else {
		$t_timestamp = $p_timestamp;
	}

	$t_query = "INSERT INTO $t_bug_rev_table (
			bug_id,
			bugnote_id,
			user_id,
			timestamp,
			type,
			value
		) VALUES ( " .
			db_param() . ', ' .
			db_param() . ', ' .
			db_param() . ', ' .
			db_param() . ', ' .
			db_param() . ', ' .
			db_param() .
		' )';
	db_query_bound( $t_query, array(
			$p_bug_id,
			$p_bugnote_id,
			$p_user_id,
			$t_timestamp,
			$p_type,
			$p_value
		) );

	return db_insert_id( $t_bug_rev_table );
}

/**
 * Check if a bug revision exists
 * @param int $p_revision_id Revision ID
 * @return bool Whether or not the bug revision exists
 */
function bug_revision_exists( $p_revision_id ) {
	$t_bug_rev_table = db_get_table( 'mantis_bug_revision_table' );

	$t_query = "SELECT * FROM $t_bug_rev_table WHERE id=" . db_param();
	$t_result = db_query_bound( $t_query, array( $p_revision_id ) );

	if ( db_num_rows( $t_result ) < 1 ) {
		return false;
	}

	return true;
}

/**
 * Get a row of data for a given revision ID.
 * @param int $p_revision_id Revision ID
 * @return array Revision data row
 */
function bug_revision_get( $p_revision_id ) {
	$t_bug_rev_table = db_get_table( 'mantis_bug_revision_table' );

	$t_query = "SELECT * FROM $t_bug_rev_table WHERE id=" . db_param();
	$t_result = db_query_bound( $t_query, array( $p_revision_id ) );

	if ( db_num_rows( $t_result ) < 1 ) {
		trigger_error( ERROR_BUG_REVISION_NOT_FOUND, ERROR );
	}

	return db_fetch_array( $t_result );
}

/**
 * Get the name of the type of a bug revision.
 * @param int $p_revision_id Revision type ID (see constant_inc.php for possible values)
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
 * @param int $p_revision_id Revision ID, or array of revision IDs
 * @return null
 */
function bug_revision_drop( $p_revision_id ) {
	$t_bug_rev_table = db_get_table( 'mantis_bug_revision_table' );

	if ( is_array( $p_revision_id ) ) {
		$t_revisions = array();
		$t_first = true;
		$t_query = "DELETE FROM $t_bug_rev_table WHERE id IN ( ";

		# TODO: Fetch bug revisions in one query (and cache them)
		foreach( $p_revision_id as $t_rev_id ) {
			$t_query .= ( $t_first ? db_param() : ', ' . db_param() );
			$t_revisions[$t_rev_id] = bug_revision_get( $t_rev_id );
		}

		$t_query .= ' )';
		db_query_bound( $t_query, $p_revision_id );
		foreach( $p_revision_id as $t_rev_id ) {
			if ( $t_revisions[$t_rev_id]['type'] == REV_BUGNOTE ) {
				history_log_event_special( $t_revisions[$t_rev_id]['bug_id'], BUGNOTE_REVISION_DROPPED, bugnote_format_id( $t_rev_id ), $t_revisions[$t_rev_id]['bugnote_id'] );
			} else {
				history_log_event_special( $t_revisions[$t_rev_id]['bug_id'], BUG_REVISION_DROPPED, bugnote_format_id( $t_rev_id ), $t_revisions[$t_rev_id]['type'] );
			}
		}
	} else {
		$t_revision = bug_revision_get( $p_revision_id );
		$t_query = "DELETE FROM $t_bug_rev_table WHERE id=" . db_param();
		db_query_bound( $t_query, array( $p_revision_id ) );
		if ( $t_revision['type'] == REV_BUGNOTE ) {
			history_log_event_special( $t_revision['bug_id'], BUGNOTE_REVISION_DROPPED, bugnote_format_id( $p_revision_id ), $t_revision['bugnote_id'] );
		} else {
			history_log_event_special( $t_revision['bug_id'], BUG_REVISION_DROPPED, bugnote_format_id( $p_revision_id ), $t_revision['type'] );
		}
	}
}

/**
 * Retrieve a count of revisions to the bug's information.
 * @param int $p_bug_id Bug ID
 * @param int $p_type Revision Type (optional)
 * @param int $p_bugnote_id Bugnote ID (optional)
 * @return array|null Array of Revision rows
 */
function bug_revision_count( $p_bug_id, $p_type=REV_ANY, $p_bugnote_id=0 ) {
	$t_bug_rev_table = db_get_table( 'mantis_bug_revision_table' );

	$t_params = array( $p_bug_id );
	$t_query = "SELECT COUNT(id) FROM $t_bug_rev_table
		WHERE bug_id=" . db_param();

	if ( REV_ANY < $p_type ) {
		$t_query .= ' AND type=' . db_param();
		$t_params[] = $p_type;
	}

	if ( $p_bugnote_id > 0 ) {
		$t_query .= ' AND bugnote_id=' . db_param();
		$t_params[] = $p_bugnote_id;
	} else {
		$t_query .= ' AND bugnote_id=0';
	}

	$t_result = db_query_bound( $t_query, $t_params );

	return db_result( $t_result );
}

/**
 * Delete all revision history for a bug.
 * @param int $p_bug_id Bug ID
 * @param int $p_bugnote_id Bugnote ID (optional)
 * @return null
 */
function bug_revision_delete( $p_bug_id, $p_bugnote_id=0 ) {
	$t_bug_rev_table = db_get_table( 'mantis_bug_revision_table' );

	if ( $p_bugnote_id < 1 ) {
		$t_query = "DELETE FROM $t_bug_rev_table WHERE bug_id=" . db_param();
		db_query_bound( $t_query, array( $p_bug_id ) );
	} else {
		$t_query = "DELETE FROM $t_bug_rev_table WHERE bugnote_id=" . db_param();
		db_query_bound( $t_query, array( $p_bugnote_id ) );
	}
}

/**
 * Retrieve the last change to the bug's information.
 * @param int $p_bug_id Bug ID
 * @param int $p_type Revision Type (optional)
 * @param int $p_bugnote_id Bugnote ID (optional)
 * @return null|array Revision row
 */
function bug_revision_last( $p_bug_id, $p_type=REV_ANY, $p_bugnote_id=0 ) {
	$t_bug_rev_table = db_get_table( 'mantis_bug_revision_table' );

	$t_params = array( $p_bug_id );
	$t_query = "SELECT * FROM $t_bug_rev_table
		WHERE bug_id=" . db_param();

	if ( REV_ANY < $p_type ) {
		$t_query .= ' AND type=' . db_param();
		$t_params[] = $p_type;
	}

	if ( $p_bugnote_id > 0 ) {
		$t_query .= ' AND bugnote_id=' . db_param();
		$t_params[] = $p_bugnote_id;
	} else {
		$t_query .= ' AND bugnote_id=0';
	}

	$t_query .= ' ORDER BY timestamp DESC';
	$t_result = db_query_bound( $t_query, $t_params, 1 );

	if ( db_num_rows( $t_result ) > 0 ) {
		return db_fetch_array( $t_result );
	} else {
		return null;
	}
}

/**
 * Retrieve a full list of changes to the bug's information.
 * @param int $p_bug_id Bug ID
 * @param int $p_type Revision Type
 * @param int $p_bugnote_id Bugnote ID
 * @return array/null Array of Revision rows
 */
function bug_revision_list( $p_bug_id, $p_type=REV_ANY, $p_bugnote_id=0 ) {
	$t_bug_rev_table = db_get_table( 'mantis_bug_revision_table' );

	$t_params = array( $p_bug_id );
	$t_query = "SELECT * FROM $t_bug_rev_table
		WHERE bug_id=" . db_param();

	if ( REV_ANY < $p_type ) {
		$t_query .= ' AND type=' . db_param();
		$t_params[] = $p_type;
	}

	if ( $p_bugnote_id > 0 ) {
		$t_query .= ' AND bugnote_id=' . db_param();
		$t_params[] = $p_bugnote_id;
	} else {
		$t_query .= ' AND bugnote_id=0';
	}

	$t_query .= ' ORDER BY timestamp ASC';
	$t_result = db_query_bound( $t_query, $t_params );

	$t_revisions = array();
	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_revisions[$t_row['id']] = $t_row;
	}

	return $t_revisions;
}

/**
 * Retrieve a list of changes to a bug of the same type as the
 * given revision ID.
 * @param int $p_rev_id Revision ID
 * @return array|null Array of Revision rows
 */
function bug_revision_like( $p_rev_id ) {
	$t_bug_rev_table = db_get_table( 'mantis_bug_revision_table' );

	$t_query = "SELECT bug_id, bugnote_id, type FROM $t_bug_rev_table WHERE id=" . db_param();
	$t_result = db_query_bound( $t_query, array( $p_rev_id ) );

	if ( db_num_rows( $t_result ) < 1 ) {
		trigger_error( ERROR_BUG_REVISION_NOT_FOUND, ERROR );
	}

	$t_row = db_fetch_array( $t_result );
	$t_bug_id = $t_row['bug_id'];
	$t_bugnote_id = $t_row['bugnote_id'];
	$t_type = $t_row['type'];

	$t_params = array( $t_bug_id );
	$t_query = "SELECT * FROM $t_bug_rev_table
		WHERE bug_id=" . db_param();

	if ( REV_ANY < $t_type ) {
		$t_query .= ' AND type=' . db_param();
		$t_params[] = $t_type;
	}

	if ( $t_bugnote_id > 0 ) {
		$t_query .= ' AND bugnote_id=' . db_param();
		$t_params[] = $t_bugnote_id;
	} else {
		$t_query .= ' AND bugnote_id=0';
	}

	$t_query .= ' ORDER BY timestamp ASC';
	$t_result = db_query_bound( $t_query, $t_params );

	$t_revisions = array();
	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_revisions[$t_row['id']] = $t_row;
	}

	return $t_revisions;
}

