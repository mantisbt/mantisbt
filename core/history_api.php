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
 * History API
 *
 * @package CoreAPI
 * @subpackage HistoryAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses bug_revision_api.php
 * @uses bugnote_api.php
 * @uses columns_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses custom_field_api.php
 * @uses database_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses project_api.php
 * @uses relationship_api.php
 * @uses sponsorship_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */

require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'bug_revision_api.php' );
require_api( 'bugnote_api.php' );
require_api( 'columns_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'custom_field_api.php' );
require_api( 'database_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'project_api.php' );
require_api( 'relationship_api.php' );
require_api( 'sponsorship_api.php' );
require_api( 'user_api.php' );
require_api( 'utility_api.php' );

/**
 * log the changes (old / new value are supplied to reduce db access)
 * events should be logged *after* the modification
 * @param integer $p_bug_id     The bug identifier of the bug being modified.
 * @param string  $p_field_name The field name of the field being modified.
 * @param string  $p_old_value  The old value of the field.
 * @param string  $p_new_value  The new value of the field.
 * @param integer $p_user_id    The user identifier of the user modifying the bug.
 * @param integer $p_type       The type of the modification.
 * @return void
 */
function history_log_event_direct( $p_bug_id, $p_field_name, $p_old_value, $p_new_value, $p_user_id = null, $p_type = 0 ) {
	# Only log events that change the value
	if( $p_new_value != $p_old_value ) {
		if( null === $p_user_id ) {
			$p_user_id = auth_get_current_user_id();
		}

		$c_field_name = $p_field_name;
		$c_old_value = ( is_null( $p_old_value ) ? '' : (string)$p_old_value );
		$c_new_value = ( is_null( $p_new_value ) ? '' : (string)$p_new_value );

		db_param_push();
		$t_query = 'INSERT INTO {bug_history}
						( user_id, bug_id, date_modified, field_name, old_value, new_value, type )
					VALUES
						( ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ' )';
		db_query( $t_query, array( $p_user_id, $p_bug_id, db_now(), $c_field_name, $c_old_value, $c_new_value, $p_type ) );
	}
}

/**
 * log the changes
 * events should be logged *after* the modification
 * @param integer $p_bug_id     The bug identifier of the bug being modified.
 * @param string  $p_field_name The field name of the field being modified.
 * @param string  $p_old_value  The old value of the field.
 * @return void
 */
function history_log_event( $p_bug_id, $p_field_name, $p_old_value ) {
	history_log_event_direct( $p_bug_id, $p_field_name, $p_old_value, bug_get_field( $p_bug_id, $p_field_name ) );
}

/**
 * log the changes
 * events should be logged *after* the modification
 * These are special case logs (new bug, deleted bugnote, etc.)
 * @param integer $p_bug_id    The bug identifier of the bug being modified.
 * @param integer $p_type      The type of the modification.
 * @param string  $p_old_value The optional value to store in the old_value field.
 * @param string  $p_new_value The optional value to store in the new_value field.
 * @return void
 */
function history_log_event_special( $p_bug_id, $p_type, $p_old_value = '', $p_new_value = '' ) {
	$t_user_id = auth_get_current_user_id();

	if( is_null( $p_old_value ) ) {
		$p_old_value = '';
	}
	if( is_null( $p_new_value ) ) {
		$p_new_value = '';
	}

	db_param_push();
	$t_query = 'INSERT INTO {bug_history}
					( user_id, bug_id, date_modified, type, old_value, new_value, field_name )
				VALUES
					( ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ',' . db_param() . ', ' . db_param() . ')';
	db_query( $t_query, array( $t_user_id, $p_bug_id, db_now(), $p_type, $p_old_value, $p_new_value, '' ) );
}

/**
 * Retrieves the history events for the specified bug id and returns it in an array
 * The array is indexed from 0 to N-1.  The second dimension is: 'date', 'username',
 * 'note', 'change'.
 * @param integer $p_bug_id  A valid bug identifier.
 * @param integer $p_user_id A valid user identifier.
 * @return array
 */
function history_get_events_array( $p_bug_id, $p_user_id = null ) {
	$t_normal_date_format = config_get( 'normal_date_format' );

	$t_raw_history = history_get_raw_events_array( $p_bug_id, $p_user_id );
	$t_history = array();

	foreach( $t_raw_history as $k => $t_item ) {
		extract( $t_item, EXTR_PREFIX_ALL, 'v' );
		$t_history[$k] = history_localize_item( $v_field, $v_type, $v_old_value, $v_new_value );
		$t_history[$k]['date'] = date( $t_normal_date_format, $v_date );
		$t_history[$k]['userid'] = $v_userid;
		$t_history[$k]['username'] = $v_username;
	}

	return( $t_history );
}

/**
 * Counts the number of changes done by the specified user within specified time window.
 * @param  integer $p_duration_in_seconds The time window in seconds.
 * @param  [type]  $p_user_id             The user id or null for logged in user.
 * @return integer The number of changes done by user in the specified time window.
 */
function history_count_user_recent_events( $p_duration_in_seconds, $p_user_id = null ) {
	$t_user_id = ( ( null === $p_user_id ) ? auth_get_current_user_id() : $p_user_id );

	$t_params = array( db_now() - $p_duration_in_seconds, $t_user_id );

	db_param_push();
	$t_query = 'SELECT count(*) as event_count FROM {bug_history} WHERE date_modified > ' . db_param() .
				' AND user_id = ' . db_param();
	$t_result = db_query( $t_query, $t_params );

	$t_row = db_fetch_array( $t_result );
	return $t_row['event_count'];
}

/**
 * Creates and executes a query for the history rows related to bugs matched by the provided filter
 * @param  array $p_filter           Filter array
 * @param  integer $p_start_time     The start time to filter by, or null for all.
 * @param  integer $p_end_time       The end time to filter by, or null for all.
 * @param  string  $p_history_order  The sort order.
 * @return database result to pass into history_get_event_from_row().
 */
function history_get_range_result_filter( $p_filter, $p_start_time = null, $p_end_time = null, $p_history_order = null ) {
	if ( $p_history_order === null ) {
		$t_history_order = config_get( 'history_order' );
	} else {
		$t_history_order = $p_history_order;
	}

	# Note: filter_get_bug_rows_query_clauses() calls db_param_push();
	$t_query_clauses = filter_get_bug_rows_query_clauses( $p_filter, null, null, null );

	# if the query can't be formed, there are no results
	if( empty( $t_query_clauses ) ) {
		# reset the db_param stack that was initialized by "filter_get_bug_rows_query_clauses()"
		db_param_pop();
		return db_empty_result();
	}

	$t_select_string = 'SELECT {bug}.id ';
	$t_from_string = ' FROM ' . implode( ', ', $t_query_clauses['from'] );
	$t_join_string = count( $t_query_clauses['join'] ) > 0 ? implode( ' ', $t_query_clauses['join'] ) : ' ';
	$t_where_string = ' WHERE '. implode( ' AND ', $t_query_clauses['project_where'] );
	if( count( $t_query_clauses['where'] ) > 0 ) {
		$t_where_string .= ' AND ( ';
		$t_where_string .= implode( $t_query_clauses['operator'], $t_query_clauses['where'] );
		$t_where_string .= ' ) ';
	}

	$t_query = 'SELECT * FROM {bug_history} WHERE {bug_history}.bug_id IN'
			. ' ( ' . $t_select_string . $t_from_string . $t_join_string . $t_where_string . ' )';

	$t_params = $t_query_clauses['where_values'];
	$t_where = array();
	if ( $p_start_time !== null ) {
		$t_where[] = 'date_modified >= ' . db_param();
		$t_params[] = $p_start_time;
	}

	if ( $p_end_time !== null ) {
		$t_where[] = 'date_modified < ' . db_param();
		$t_params[] = $p_end_time;
	}

	if ( count( $t_where ) > 0 ) {
		$t_query .= ' AND ' . implode( ' AND ', $t_where );
	}

	$t_query .= ' ORDER BY {bug_history}.date_modified ' . $t_history_order . ', {bug_history}.id ' . $t_history_order;
	$t_result = db_query( $t_query, $t_params );
	return $t_result;
}

/**
 * Creates and executes a query for the history rows matching the specified criteria.
 * @param  integer $p_bug_id         The bug id or null for matching any bug.
 * @param  integer $p_start_time     The start time to filter by, or null for all.
 * @param  integer $p_end_time       The end time to filter by, or null for all.
 * @param  string  $p_history_order  The sort order.
 * @return IteratorAggregate|boolean database result to pass into history_get_event_from_row().
 */
function history_get_range_result( $p_bug_id = null, $p_start_time = null, $p_end_time = null, $p_history_order = null ) {
	if ( $p_history_order === null ) {
		$t_history_order = config_get( 'history_order' );
	} else {
		$t_history_order = $p_history_order;
	}

	db_param_push();
	$t_query = 'SELECT * FROM {bug_history}';
	$t_params = array();
	$t_where = array();

	if ( $p_bug_id !== null ) {
		$t_where[] = 'bug_id=' . db_param();
		$t_params = array( $p_bug_id );
	}

	if ( $p_start_time !== null ) {
		$t_where[] = 'date_modified >= ' . db_param();
		$t_params[] = $p_start_time;
	}

	if ( $p_end_time !== null ) {
		$t_where[] = 'date_modified < ' . db_param();
		$t_params[] = $p_end_time;
	}

	if ( count( $t_where ) > 0 ) {
		$t_query .= ' WHERE ' . implode( ' AND ', $t_where );
	}

	$t_query .= ' ORDER BY date_modified ' . $t_history_order . ',id ' . $t_history_order;

	$t_result = db_query( $t_query, $t_params );

	return $t_result;
}

/**
 * Gets the next accessible history event for current user and specified db result.
 * @param  object  $p_result      The database result.
 * @param  integer $p_user_id     The user id or null for logged in user.
 * @param  boolean $p_check_access_to_issue true: check that user has access to bugs,
 *                                          false otherwise.
 * @return array containing the history event or false if no more matches.
 */
function history_get_event_from_row( $p_result, $p_user_id = null, $p_check_access_to_issue = true ) {
	static $s_bug_visible = array();
	$t_user_id = ( null === $p_user_id ) ? auth_get_current_user_id() : $p_user_id;

	while ( $t_row = db_fetch_array( $p_result ) ) {
		extract( $t_row, EXTR_PREFIX_ALL, 'v' );

		# Ignore entries related to non-existing bugs (see #20727)
		if( !bug_exists( $v_bug_id ) ) {
			continue;
		}

		if( $p_check_access_to_issue ) {
			if( !isset( $s_bug_visible[$v_bug_id] ) ) {
				$s_bug_visible[$v_bug_id] = access_has_bug_level( VIEWER, $v_bug_id );
			}

			if( !$s_bug_visible[$v_bug_id] ) {
				continue;
			}
		}

		if( $v_type == NORMAL_TYPE ) {
			if( !in_array( $v_field_name, columns_get_standard() ) ) {
				# check that the item should be visible to the user
				$t_field_id = custom_field_get_id_from_name( $v_field_name );
				if( false !== $t_field_id && !custom_field_has_read_access( $t_field_id, $v_bug_id, $t_user_id ) ) {
					continue;
				}
			}

			if( ( $v_field_name == 'target_version' ) && !access_has_bug_level( config_get( 'roadmap_view_threshold' ), $v_bug_id, $t_user_id ) ) {
				continue;
			}

			if( ( $v_field_name == 'due_date' ) && !access_has_bug_level( config_get( 'due_date_view_threshold' ), $v_bug_id, $t_user_id ) ) {
				continue;
			}

			if( ( $v_field_name == 'handler_id' ) && !access_has_bug_level( config_get( 'view_handler_threshold' ), $v_bug_id, $t_user_id ) ) {
				continue;
			}
		}

		# bugnotes
		if( $t_user_id != $v_user_id ) {
			# bypass if user originated note
			if( ( $v_type == BUGNOTE_ADDED ) || ( $v_type == BUGNOTE_UPDATED ) || ( $v_type == BUGNOTE_DELETED ) ) {
				if( !bugnote_exists( $v_old_value ) ) {
					continue;
				}

				if( !access_has_bug_level( config_get( 'private_bugnote_threshold' ), $v_bug_id, $t_user_id ) && ( bugnote_get_field( $v_old_value, 'view_state' ) == VS_PRIVATE ) ) {
					continue;
				}
			}

			if( $v_type == BUGNOTE_STATE_CHANGED ) {
				if( !bugnote_exists( $v_new_value ) ) {
					continue;
				}

				if( !access_has_bug_level( config_get( 'private_bugnote_threshold' ), $v_bug_id, $t_user_id ) && ( bugnote_get_field( $v_new_value, 'view_state' ) == VS_PRIVATE ) ) {
					continue;
				}
			}
		}

		# tags
		if( $v_type == TAG_ATTACHED || $v_type == TAG_DETACHED || $v_type == TAG_RENAMED ) {
			if( !access_has_bug_level( config_get( 'tag_view_threshold' ), $v_bug_id, $t_user_id ) ) {
				continue;
			}
		}

		# attachments
		if( $v_type == FILE_ADDED || $v_type == FILE_DELETED ) {
			if( !access_has_bug_level( config_get( 'view_attachments_threshold' ), $v_bug_id, $t_user_id ) ) {
				continue;
			}
		}

		# monitoring
		if( $v_type == BUG_MONITOR || $v_type == BUG_UNMONITOR ) {
			if( !access_has_bug_level( config_get( 'show_monitor_list_threshold' ), $v_bug_id, $t_user_id ) ) {
				continue;
			}
		}

		# relationships
		if( $v_type == BUG_ADD_RELATIONSHIP || $v_type == BUG_DEL_RELATIONSHIP || $v_type == BUG_REPLACE_RELATIONSHIP ) {
			$t_related_bug_id = $v_new_value;

			# If bug doesn't exist, then we don't know whether to expose it or not based on the fact whether it was
			# accessible to user or not.  This also simplifies client code that is accessing the history log.
			if( !bug_exists( $t_related_bug_id ) || !access_has_bug_level( config_get( 'view_bug_threshold' ), $t_related_bug_id, $t_user_id ) ) {
				continue;
			}
		}

		$t_event = array();
		$t_event['bug_id'] = $v_bug_id;
		$t_event['date'] = $v_date_modified;
		$t_event['userid'] = $v_user_id;

		# user_get_name handles deleted users, and username vs realname
		$t_event['username'] = user_get_name( $v_user_id );

		$t_event['field'] = $v_field_name;
		$t_event['type'] = $v_type;
		$t_event['old_value'] = $v_old_value;
		$t_event['new_value'] = $v_new_value;

		return $t_event;
	}

	return false;
}

/**
 * Retrieves the raw history events for the specified bug id and returns it in an array
 * The array is indexed from 0 to N-1.  The second dimension is: 'date', 'userid', 'username',
 * 'field','type','old_value','new_value'
 * @param integer $p_bug_id  A valid bug identifier or null to not filter by bug.  If no bug id is specified,
 *                           then returned array will have a field for bug_id, otherwise it won't.
 * @param integer $p_user_id A valid user identifier.
 * @param integer $p_start_time The start time to filter by, or null for all.
 * @param integer $p_end_time   The end time to filter by, or null for all.
 * @return array
 */
function history_get_raw_events_array( $p_bug_id, $p_user_id = null, $p_start_time = null, $p_end_time = null ) {
	$t_user_id = (( null === $p_user_id ) ? auth_get_current_user_id() : $p_user_id );

	# grab history and display by date_modified then field_name
	# @@@ by MASC I guess it's better by id then by field_name. When we have more history lines with the same
	# date, it's better to respect the storing order otherwise we should risk to mix different information
	# I give you an example. We create a child of a bug with different custom fields. In the history of the child
	# bug we will find the line related to the relationship mixed with the custom fields (the history is creted
	# for the new bug with the same timestamp...)

	$t_result = history_get_range_result( $p_bug_id, $p_start_time, $p_end_time );

	$t_raw_history = array();

	$j = 0;
	while( true ) {
		$t_event = history_get_event_from_row( $t_result, $t_user_id, /* check access */ true );
		if ( $t_event === false ) {
			break;
		}

		$t_raw_history[$j] = $t_event;
		$j++;
	}

	# end for loop

	return $t_raw_history;
}

/**
 * Localizes one raw history item specified by set the next parameters: $p_field_name, $p_type, $p_old_value, $p_new_value
 * Returns array with two elements indexed as 'note' and 'change'
 * @param string  $p_field_name The field name of the field being localized.
 * @param integer $p_type       The type of the history entry.
 * @param string  $p_old_value  The old value of the field.
 * @param string  $p_new_value  The new value of the field.
 * @param boolean $p_linkify    Whether to return a string containing hyperlinks.
 * @return array
 */
function history_localize_item( $p_field_name, $p_type, $p_old_value, $p_new_value, $p_linkify = true ) {
	$t_note = '';
	$t_change = '';
	$t_field_localized = $p_field_name;
	$t_raw = true;

	if( PLUGIN_HISTORY == $p_type ) {
		$t_note = lang_get_defaulted( 'plugin_' . $p_field_name, $p_field_name );
		$t_change = ( isset( $p_new_value ) ? $p_old_value . ' => ' . $p_new_value : $p_old_value );

		return array( 'note' => $t_note, 'change' => $t_change, 'raw' => true );
	}

	switch( $p_field_name ) {
		case 'category':
			$t_field_localized = lang_get( 'category' );
			break;
		case 'status':
			$p_old_value = get_enum_element( 'status', $p_old_value );
			$p_new_value = get_enum_element( 'status', $p_new_value );
			$t_field_localized = lang_get( 'status' );
			break;
		case 'severity':
			$p_old_value = get_enum_element( 'severity', $p_old_value );
			$p_new_value = get_enum_element( 'severity', $p_new_value );
			$t_field_localized = lang_get( 'severity' );
			break;
		case 'reproducibility':
			$p_old_value = get_enum_element( 'reproducibility', $p_old_value );
			$p_new_value = get_enum_element( 'reproducibility', $p_new_value );
			$t_field_localized = lang_get( 'reproducibility' );
			break;
		case 'resolution':
			$p_old_value = get_enum_element( 'resolution', $p_old_value );
			$p_new_value = get_enum_element( 'resolution', $p_new_value );
			$t_field_localized = lang_get( 'resolution' );
			break;
		case 'priority':
			$p_old_value = get_enum_element( 'priority', $p_old_value );
			$p_new_value = get_enum_element( 'priority', $p_new_value );
			$t_field_localized = lang_get( 'priority' );
			break;
		case 'eta':
			$p_old_value = get_enum_element( 'eta', $p_old_value );
			$p_new_value = get_enum_element( 'eta', $p_new_value );
			$t_field_localized = lang_get( 'eta' );
			break;
		case 'view_state':
			$p_old_value = get_enum_element( 'view_state', $p_old_value );
			$p_new_value = get_enum_element( 'view_state', $p_new_value );
			$t_field_localized = lang_get( 'view_status' );
			break;
		case 'projection':
			$p_old_value = get_enum_element( 'projection', $p_old_value );
			$p_new_value = get_enum_element( 'projection', $p_new_value );
			$t_field_localized = lang_get( 'projection' );
			break;
		case 'sticky':
			$p_old_value = gpc_string_to_bool( $p_old_value ) ? lang_get( 'yes' ) : lang_get( 'no' );
			$p_new_value = gpc_string_to_bool( $p_new_value ) ? lang_get( 'yes' ) : lang_get( 'no' );
			$t_field_localized = lang_get( 'sticky_issue' );
			break;
		case 'project_id':
			if( project_exists( $p_old_value ) ) {
				$p_old_value = project_get_field( $p_old_value, 'name' );
			} else {
				$p_old_value = '@' . $p_old_value . '@';
			}

			# Note that the new value maybe an intermediately project and not the
			# current one.
			if( project_exists( $p_new_value ) ) {
				$p_new_value = project_get_field( $p_new_value, 'name' );
			} else {
				$p_new_value = '@' . $p_new_value . '@';
			}
			$t_field_localized = lang_get( 'email_project' );
			break;
		case 'handler_id':
			$t_field_localized = lang_get( 'assigned_to' );
		case 'reporter_id':
			if( 'reporter_id' == $p_field_name ) {
				$t_field_localized = lang_get( 'reporter' );
			}
			if( 0 == $p_old_value ) {
				$p_old_value = '';
			} else {
				$p_old_value = user_get_name( $p_old_value );
			}

			if( 0 == $p_new_value ) {
				$p_new_value = '';
			} else {
				$p_new_value = user_get_name( $p_new_value );
			}
			break;
		case 'version':
			$t_field_localized = lang_get( 'product_version' );
			break;
		case 'fixed_in_version':
			$t_field_localized = lang_get( 'fixed_in_version' );
			break;
		case 'target_version':
			$t_field_localized = lang_get( 'target_version' );
			break;
		case 'date_submitted':
			$p_old_value = date( config_get( 'normal_date_format' ), $p_old_value );
			$p_new_value = date( config_get( 'normal_date_format' ), $p_new_value );
			$t_field_localized = lang_get( 'date_submitted' );
			break;
		case 'last_updated':
			$p_old_value = date( config_get( 'normal_date_format' ), $p_old_value );
			$p_new_value = date( config_get( 'normal_date_format' ), $p_new_value );
			$t_field_localized = lang_get( 'last_update' );
			break;
		case 'os':
			$t_field_localized = lang_get( 'os' );
			break;
		case 'os_build':
			$t_field_localized = lang_get( 'os_version' );
			break;
		case 'build':
			$t_field_localized = lang_get( 'build' );
			break;
		case 'platform':
			$t_field_localized = lang_get( 'platform' );
			break;
		case 'summary':
			$t_field_localized = lang_get( 'summary' );
			break;
		case 'duplicate_id':
			$t_field_localized = lang_get( 'duplicate_id' );
			break;
		case 'sponsorship_total':
			$t_field_localized = lang_get( 'sponsorship_total' );
			break;
		case 'due_date':
			if( $p_old_value !== '' ) {
				$p_old_value = date( config_get( 'normal_date_format' ), (int)$p_old_value );
			}
			if( $p_new_value !== '' ) {
				$p_new_value = date( config_get( 'normal_date_format' ), (int)$p_new_value );
			}
			$t_field_localized = lang_get( 'due_date' );
			break;
		default:

			# assume it's a custom field name
			$t_field_id = custom_field_get_id_from_name( $p_field_name );
			if( false !== $t_field_id ) {
				$t_cf_type = custom_field_type( $t_field_id );
				if( '' != $p_old_value ) {
					$p_old_value = string_custom_field_value_for_email( $p_old_value, $t_cf_type );
				}
				$p_new_value = string_custom_field_value_for_email( $p_new_value, $t_cf_type );
				$t_field_localized = lang_get_defaulted( $p_field_name );
			}
		}

		if( NORMAL_TYPE != $p_type ) {
			switch( $p_type ) {
				case NEW_BUG:
					$t_note = lang_get( 'new_bug' );
					break;
				case BUGNOTE_ADDED:
					$t_note = lang_get( 'bugnote_added' ) . ': ' . $p_old_value;
					break;
				case BUGNOTE_UPDATED:
					$t_note = lang_get( 'bugnote_edited' ) . ': ' . $p_old_value;
					$t_old_value = (int)$p_old_value;
					$t_new_value = (int)$p_new_value;
					if( $p_linkify && bug_revision_exists( $t_new_value ) ) {
						if( bugnote_exists( $t_old_value ) ) {
							$t_bug_revision_view_page_argument = 'bugnote_id=' . $t_old_value . '#r' . $t_new_value;
						} else {
							$t_bug_revision_view_page_argument = 'rev_id=' . $t_new_value;
						}
						$t_change = '<a href="bug_revision_view_page.php?' . $t_bug_revision_view_page_argument . '">' .
							lang_get( 'view_revisions' ) . '</a>';
						$t_raw = false;
					}
					break;
				case BUGNOTE_DELETED:
					$t_note = lang_get( 'bugnote_deleted' ) . ': ' . $p_old_value;
					break;
				case DESCRIPTION_UPDATED:
					$t_note = lang_get( 'description_updated' );
					$t_old_value = (int)$p_old_value;
					if( $p_linkify && bug_revision_exists( $t_old_value ) ) {
						$t_change = '<a href="bug_revision_view_page.php?rev_id=' . $t_old_value . '#r' . $t_old_value . '">' .
							lang_get( 'view_revisions' ) . '</a>';
						$t_raw = false;
					}
					break;
				case ADDITIONAL_INFO_UPDATED:
					$t_note = lang_get( 'additional_information_updated' );
					$t_old_value = (int)$p_old_value;
					if( $p_linkify && bug_revision_exists( $t_old_value ) ) {
						$t_change = '<a href="bug_revision_view_page.php?rev_id=' . $t_old_value . '#r' . $t_old_value . '">' .
							lang_get( 'view_revisions' ) . '</a>';
						$t_raw = false;
					}
					break;
				case STEP_TO_REPRODUCE_UPDATED:
					$t_note = lang_get( 'steps_to_reproduce_updated' );
					$t_old_value = (int)$p_old_value;
					if( $p_linkify && bug_revision_exists( $t_old_value ) ) {
						$t_change = '<a href="bug_revision_view_page.php?rev_id=' . $t_old_value . '#r' . $t_old_value . '">' .
							lang_get( 'view_revisions' ) . '</a>';
						$t_raw = false;
					}
					break;
				case FILE_ADDED:
					$t_note = lang_get( 'file_added' ) . ': ' . $p_old_value;
					break;
				case FILE_DELETED:
					$t_note = lang_get( 'file_deleted' ) . ': ' . $p_old_value;
					break;
				case BUGNOTE_STATE_CHANGED:
					$p_old_value = get_enum_element( 'view_state', $p_old_value );
					$t_note = lang_get( 'bugnote_view_state' ) . ': ' . $p_new_value . ': ' . $p_old_value;
					break;
				case BUG_MONITOR:
					$p_old_value = user_get_name( $p_old_value );
					$t_note = lang_get( 'bug_monitor' ) . ': ' . $p_old_value;
					break;
				case BUG_UNMONITOR:
					if( $p_old_value !== '' ) {
						$p_old_value = user_get_name( $p_old_value );
					}
					$t_note = lang_get( 'bug_end_monitor' ) . ': ' . $p_old_value;
					break;
				case BUG_DELETED:
					$t_note = lang_get( 'bug_deleted' ) . ': ' . $p_old_value;
					break;
				case BUG_ADD_SPONSORSHIP:
					$t_note = lang_get( 'sponsorship_added' );
					$t_change = user_get_name( $p_old_value ) . ': ' . sponsorship_format_amount( $p_new_value );
					break;
				case BUG_UPDATE_SPONSORSHIP:
					$t_note = lang_get( 'sponsorship_updated' );
					$t_change = user_get_name( $p_old_value ) . ': ' . sponsorship_format_amount( $p_new_value );
					break;
				case BUG_DELETE_SPONSORSHIP:
					$t_note = lang_get( 'sponsorship_deleted' );
					$t_change = user_get_name( $p_old_value ) . ': ' . sponsorship_format_amount( $p_new_value );
					break;
				case BUG_PAID_SPONSORSHIP:
					$t_note = lang_get( 'sponsorship_paid' );
					$t_change = user_get_name( $p_old_value ) . ': ' . get_enum_element( 'sponsorship', $p_new_value );
					break;
				case BUG_ADD_RELATIONSHIP:
					$t_note = lang_get( 'relationship_added' );
					$t_change = relationship_get_description_for_history( $p_old_value ) . ' ' . bug_format_id( $p_new_value );
					break;
				case BUG_REPLACE_RELATIONSHIP:
					$t_note = lang_get( 'relationship_replaced' );
					$t_change = relationship_get_description_for_history( $p_old_value ) . ' ' . bug_format_id( $p_new_value );
					break;
				case BUG_DEL_RELATIONSHIP:
					$t_note = lang_get( 'relationship_deleted' );

					# Fix for #7846: There are some cases where old value is empty, this may be due to an old bug.
					if( !is_blank( $p_old_value ) && $p_old_value > 0 ) {
						$t_change = relationship_get_description_for_history( $p_old_value ) . ' ' . bug_format_id( $p_new_value );
					} else {
						$t_change = bug_format_id( $p_new_value );
					}
					break;
				case BUG_CLONED_TO:
					$t_note = lang_get( 'bug_cloned_to' ) . ': ' . bug_format_id( $p_new_value );
					break;
				case BUG_CREATED_FROM:
					$t_note = lang_get( 'bug_created_from' ) . ': ' . bug_format_id( $p_new_value );
					break;
				case TAG_ATTACHED:
					$t_note = lang_get( 'tag_history_attached' ) . ': ' . $p_old_value;
					break;
				case TAG_DETACHED:
					$t_note = lang_get( 'tag_history_detached' ) . ': ' . $p_old_value;
					break;
				case TAG_RENAMED:
					$t_note = lang_get( 'tag_history_renamed' );
					$t_change = $p_old_value . ' => ' . $p_new_value;
					break;
				case BUG_REVISION_DROPPED:
					$t_note = lang_get( 'bug_revision_dropped_history' ) . ': ' . bug_revision_get_type_name( $p_new_value ) . ': ' . $p_old_value;
					break;
				case BUGNOTE_REVISION_DROPPED:
					$t_note = lang_get( 'bugnote_revision_dropped_history' ) . ': ' . $p_new_value . ': ' . $p_old_value;
					break;
			}
	}

	# output special cases
	if( NORMAL_TYPE == $p_type ) {
		$t_note = $t_field_localized;
		$t_change = $p_old_value . ' => ' . $p_new_value;
	}

	# end if DEFAULT
	return array( 'note' => $t_note, 'change' => $t_change, 'raw' => $t_raw );
}

/**
 * delete all history associated with a bug
 * @param integer $p_bug_id A valid bug identifier.
 * @return void
 */
function history_delete( $p_bug_id ) {
	db_param_push();
	$t_query = 'DELETE FROM {bug_history} WHERE bug_id=' . db_param();
	db_query( $t_query, array( $p_bug_id ) );
}
