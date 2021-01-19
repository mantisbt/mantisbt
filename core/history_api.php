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

		if( is_null( $p_old_value ) ) {
			$c_old_value = '';
		} else {
			$c_old_value = mb_strimwidth( $p_old_value, 0, DB_FIELD_SIZE_HISTORY_VALUE, '...' );
		}
		if( is_null( $p_new_value ) ) {
			$c_new_value = '';
		} else {
			$c_new_value = mb_strimwidth( $p_new_value, 0, DB_FIELD_SIZE_HISTORY_VALUE, '...' );
		}

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
		$c_old_value = '';
	} else {
		$c_old_value = mb_strimwidth( $p_old_value, 0, DB_FIELD_SIZE_HISTORY_VALUE, '...' );
	}

	if( is_null( $p_new_value ) ) {
		$c_new_value = '';
	} else {
		$c_new_value = mb_strimwidth( $p_new_value, 0, DB_FIELD_SIZE_HISTORY_VALUE, '...' );
	}

	db_param_push();
	$t_query = 'INSERT INTO {bug_history}
					( user_id, bug_id, date_modified, type, old_value, new_value, field_name )
				VALUES
					( ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ',' . db_param() . ', ' . db_param() . ')';
	db_query( $t_query, array( $t_user_id, $p_bug_id, db_now(), $p_type, $c_old_value, $c_new_value, '' ) );
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
		/**
		 * @var int $v_userid
		 * @var string $v_username
		 * @var string $v_field
		 * @var string $v_old_value
		 * @var string $v_new_value
		 * @var int $v_type
		 * @var int $v_date
		 */
		extract( $t_item, EXTR_PREFIX_ALL, 'v' );
		$t_history[$k] = history_localize_item( $p_bug_id, $v_field, $v_type, $v_old_value, $v_new_value );
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
 * Creates and executes a query for the history rows, returning a database result object.
 * Query options is an array with parameters to build the query.
 *
 * Supported options are:
 * - "bug_id" => integer | array    Limit search to these bug ids.
 * - "start_time" => integer        Timestamp for start time to filter by (inclusive)
 * - "end_time" => integer          Timestamp for end time to filter by (exclusive)
 * - "user_id" => integer | array   Limit search to actions by these user ids.
 * - "filter" => filter array       A filter array to limit history to bugs matched by this filter.
 * - "order" => Sort order          'ASC' or 'DESC' for result order.
 *
 * Any option can be omitted.
 *
 * @param array $p_query_options	Array of query options
 * @return IteratorAggregate|boolean database result to pass into history_get_event_from_row().
 */
function history_query_result( array $p_query_options ) {
	# check query order by
	if( isset( $p_query_options['order'] ) ) {
		$t_history_order = $p_query_options['order'];
	} else {
		$t_history_order = config_get( 'history_order' );
	}

	$t_query = new DbQuery();
	$t_where = array();

	# With bug filter
	if( isset( $p_query_options['filter'] ) ) {
		$t_subquery = new BugFilterQuery( $p_query_options['filter'], BugFilterQuery::QUERY_TYPE_IDS );
		$t_where[] = '{bug_history}.bug_id IN ' . $t_query->param( $t_subquery );
	}

	# Start time
	if( isset( $p_query_options['start_time'] ) ) {
		$t_where[] = '{bug_history}.date_modified >= ' . $t_query->param( (int)$p_query_options['start_time'] );
	}

	# End time
	if( isset( $p_query_options['end_time'] ) ) {
		$t_where[] = '{bug_history}.date_modified < ' . $t_query->param( (int)$p_query_options['end_time'] );
	}

	# Bug ids
	if( isset( $p_query_options['bug_id'] ) ) {
		$c_ids = array();
		if( is_array( $p_query_options['bug_id'] ) ) {
			foreach( $p_query_options['bug_id'] as $t_id ) {
				$c_ids[] = (int)$t_id;
			}
		} else {
			$c_ids[] = (int)$p_query_options['bug_id'];
		}
		$t_where[] = $t_query->sql_in( '{bug_history}.bug_id', $c_ids );
	}

	# User ids
	if( isset( $p_query_options['user_id'] ) ) {
		$c_ids = array();
		if( is_array( $p_query_options['user_id'] ) ) {
			foreach( $p_query_options['user_id'] as $t_id ) {
				$c_ids[] = (int)$t_id;
			}
		} else {
			$c_ids[] = (int)$p_query_options['user_id'];
		}
		$t_where[] = $t_query->sql_in( '{bug_history}.user_id', $c_ids );
	}

	$t_query->append_sql( 'SELECT * FROM {bug_history}' );
	if ( count( $t_where ) > 0 ) {
		$t_query->append_sql( ' WHERE ' . implode( ' AND ', $t_where ) );
	}

	# Order history lines by date. Use the storing sequence as 2nd order field for lines with the same date.
	$t_query->append_sql( ' ORDER BY {bug_history}.date_modified ' . $t_history_order . ', {bug_history}.id ' . $t_history_order );

	return $t_query->execute();
}

/**
 * Creates and executes a query for the history rows related to bugs matched by the provided filter
 * @param  array $p_filter           Filter array
 * @param  integer $p_start_time     The start time to filter by, or null for all.
 * @param  integer $p_end_time       The end time to filter by, or null for all.
 * @param  string  $p_history_order  The sort order.
 * @return IteratorAggregate|boolean database result to pass into history_get_event_from_row().
 * @deprecated		Use history_query_result() instead
 */
function history_get_range_result_filter( $p_filter, $p_start_time = null, $p_end_time = null, $p_history_order = null ) {
	error_parameters( __FUNCTION__ . '()', 'history_query_result()' );
	trigger_error( ERROR_DEPRECATED_SUPERSEDED, DEPRECATED );

	$t_query_options = array();
	if ( $p_history_order !== null ) {
		$t_query_options['order'] = $p_history_order;
	}
	if ( $p_start_time !== null ) {
		$t_query_options['start_time'] = $p_start_time;
	}
	if ( $p_end_time !== null ) {
		$t_query_options['end_time'] = $p_end_time;
	}
	if ( $p_filter !== null ) {
		$t_query_options['filter'] = $p_filter;
	}
	return history_query_result( $t_query_options );
}

/**
 * Creates and executes a query for the history rows matching the specified criteria.
 * @param  integer $p_bug_id         The bug id or null for matching any bug.
 * @param  integer $p_start_time     The start time to filter by, or null for all.
 * @param  integer $p_end_time       The end time to filter by, or null for all.
 * @param  string  $p_history_order  The sort order.
 * @return IteratorAggregate|boolean database result to pass into history_get_event_from_row().
 * @deprecated		Use history_query_result() instead
 */
function history_get_range_result( $p_bug_id = null, $p_start_time = null, $p_end_time = null, $p_history_order = null ) {
	error_parameters( __FUNCTION__ . '()', 'history_query_result()' );
	trigger_error( ERROR_DEPRECATED_SUPERSEDED, DEPRECATED );

	$t_query_options = array();
	if ( $p_history_order !== null ) {
		$t_query_options['order'] = $p_history_order;
	}
	if ( $p_start_time !== null ) {
		$t_query_options['start_time'] = $p_start_time;
	}
	if ( $p_end_time !== null ) {
		$t_query_options['end_time'] = $p_end_time;
	}
	if ( $p_bug_id !== null ) {
		$t_query_options['bug_id'] = $p_bug_id;
	}
	return history_query_result( $t_query_options );
}

/**
 * Gets the next accessible history event for current user and specified db result.
 * @param  object  $p_result      The database result.
 * @param  integer $p_user_id     The user id or null for logged in user.
 * @param  boolean $p_check_access_to_issue true: check that user has access to bugs,
 *                                          false otherwise.
 * @return array|false containing the history event or false if no more matches.
 */
function history_get_event_from_row( $p_result, $p_user_id = null, $p_check_access_to_issue = true ) {
	static $s_bug_visible = array();
	$t_user_id = ( null === $p_user_id ) ? auth_get_current_user_id() : $p_user_id;

	while ( $t_row = db_fetch_array( $p_result ) ) {
		/**
		 * @var int $v_user_id
		 * @var int $v_bug_id
		 * @var string $v_field_name
		 * @var string $v_old_value
		 * @var string $v_new_value
		 * @var int $v_type
		 * @var int $v_date_modified
		 */
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

		$t_project_id = bug_get_field( $v_bug_id, 'project_id' );

		if( $v_type == NORMAL_TYPE ) {
			if( !in_array( $v_field_name, columns_get_standard() ) ) {
				# check that the item should be visible to the user
				$t_field_id = custom_field_get_id_from_name( $v_field_name );
				if( false !== $t_field_id && !custom_field_has_read_access( $t_field_id, $v_bug_id, $t_user_id ) ) {
					continue;
				}
			}

			if( ( $v_field_name == 'target_version' ) &&
				!access_has_bug_level( config_get( 'roadmap_view_threshold', null, $t_user_id, $t_project_id ), $v_bug_id, $t_user_id ) ) {
				continue;
			}

			if( ( $v_field_name == 'due_date' ) &&
				!access_has_bug_level( config_get( 'due_date_view_threshold', null, $t_user_id, $t_project_id ), $v_bug_id, $t_user_id ) ) {
				continue;
			}

			if( ( $v_field_name == 'handler_id' ) &&
				!access_has_bug_level( config_get( 'view_handler_threshold', null, $t_user_id, $t_project_id ), $v_bug_id, $t_user_id ) ) {
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

				if( !access_has_bug_level( config_get( 'private_bugnote_threshold', null, $t_user_id, $t_project_id ), $v_bug_id, $t_user_id ) && ( bugnote_get_field( $v_old_value, 'view_state' ) == VS_PRIVATE ) ) {
					continue;
				}
			}

			if( $v_type == BUGNOTE_STATE_CHANGED ) {
				if( !bugnote_exists( $v_new_value ) ) {
					continue;
				}

				if( !access_has_bug_level( config_get( 'private_bugnote_threshold', null, $t_user_id, $t_project_id ), $v_bug_id, $t_user_id ) && ( bugnote_get_field( $v_new_value, 'view_state' ) == VS_PRIVATE ) ) {
					continue;
				}
			}
		}

		# tags
		if( $v_type == TAG_ATTACHED || $v_type == TAG_DETACHED || $v_type == TAG_RENAMED ) {
			if( !access_has_bug_level( config_get( 'tag_view_threshold', null, $t_user_id, $t_project_id ), $v_bug_id, $t_user_id ) ) {
				continue;
			}
		}

		# attachments
		if( $v_type == FILE_ADDED || $v_type == FILE_DELETED ) {
			if( !access_has_bug_level( config_get( 'view_attachments_threshold', null, $t_user_id, $t_project_id ), $v_bug_id, $t_user_id ) ) {
				continue;
			}

			# Files were originally just associated with the issue, then association with specific bugnotes
			# was added, so handled legacy and new way of handling attachments.
			if( !empty( $v_new_value ) && (int)$v_new_value != 0 ) {
				if( !bugnote_exists( $v_new_value ) ) {
					continue;
				}

				if( !access_has_bug_level( config_get( 'private_bugnote_threshold', null, $t_user_id, $t_project_id ), $v_bug_id, $t_user_id ) && ( bugnote_get_field( $v_new_value, 'view_state' ) == VS_PRIVATE ) ) {
					continue;
				}
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

		if( $v_type == BUG_REVISION_DROPPED || $v_type == BUGNOTE_REVISION_DROPPED ) {
			if( !access_can_view_bug_revisions( $v_bug_id ) ) {
				continue;
			}
		}

		$t_event = array();
		$t_event['bug_id'] = $v_bug_id;
		$t_event['date'] = $v_date_modified;
		$t_event['userid'] = $v_user_id;
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

	$t_query_options = array(
		'bug_id' => $p_bug_id,
		'start_time' => $p_start_time,
		'end_time' => $p_end_time
		);
	$t_result = history_query_result( $t_query_options );

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
 * Localize the specified field name for native or custom fields.
 *
 * @param string $p_field_name The field name.
 * @return string The localized field name.
 */
function history_localize_field_name( $p_field_name ) {
	switch( $p_field_name ) {
		case 'category':
			$t_field_localized = lang_get( 'category' );
			break;
		case 'status':
			$t_field_localized = lang_get( 'status' );
			break;
		case 'severity':
			$t_field_localized = lang_get( 'severity' );
			break;
		case 'reproducibility':
			$t_field_localized = lang_get( 'reproducibility' );
			break;
		case 'resolution':
			$t_field_localized = lang_get( 'resolution' );
			break;
		case 'priority':
			$t_field_localized = lang_get( 'priority' );
			break;
		case 'eta':
			$t_field_localized = lang_get( 'eta' );
			break;
		case 'view_state':
			$t_field_localized = lang_get( 'view_status' );
			break;
		case 'projection':
			$t_field_localized = lang_get( 'projection' );
			break;
		case 'sticky':
			$t_field_localized = lang_get( 'sticky_issue' );
			break;
		case 'project_id':
			$t_field_localized = lang_get( 'email_project' );
			break;
		case 'handler_id':
			$t_field_localized = lang_get( 'assigned_to' );
			break;
		case 'reporter_id':
			$t_field_localized = lang_get( 'reporter' );
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
			$t_field_localized = lang_get( 'date_submitted' );
			break;
		case 'last_updated':
			$t_field_localized = lang_get( 'last_update' );
			break;
		case 'os':
			$t_field_localized = lang_get( 'os' );
			break;
		case 'os_build':
			$t_field_localized = lang_get( 'os_build' );
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
			$t_field_localized = lang_get( 'due_date' );
			break;
		default:
			# assume it's a custom field name
			$t_field_localized = lang_get_defaulted( $p_field_name );
			break;
	}

	return $t_field_localized;
}

/**
 * Get name of the change type.
 *
 * @param integer $p_type The type code.
 * @return string The type name.
 */
function history_get_type_name( $p_type ) {
	$t_type = (int)$p_type;

	switch( $t_type ) {
		case NORMAL_TYPE:
			$t_type_name = 'field-updated';
			break;
		case NEW_BUG:
			$t_type_name = 'issue-new';
			break;
		case BUGNOTE_ADDED:
			$t_type_name = 'note-added';
			break;
		case BUGNOTE_UPDATED:
			$t_type_name = 'note-updated';
			break;
		case BUGNOTE_DELETED:
			$t_type_name = 'note-deleted';
			break;
		case DESCRIPTION_UPDATED:
			$t_type_name = 'issue-description-updated';
			break;
		case ADDITIONAL_INFO_UPDATED:
			$t_type_name = 'issue-additional-info-updated';
			break;
		case STEP_TO_REPRODUCE_UPDATED:
			$t_type_name = 'issue-steps-to-reproduce-updated';
			break;
		case FILE_ADDED:
			$t_type_name = 'file-added';
			break;
		case FILE_DELETED:
			$t_type_name = 'file-deleted';
			break;
		case BUGNOTE_STATE_CHANGED:
			$t_type_name = 'note-view-state-updated';
			break;
		case BUG_MONITOR:
			$t_type_name = 'monitor-added';
			break;
		case BUG_UNMONITOR:
			$t_type_name = 'monitor-deleted';
			break;
		case BUG_DELETED:
			$t_type_name = 'issue-deleted';
			break;
		case BUG_ADD_SPONSORSHIP:
			$t_type_name = 'sponsorship-added';
			break;
		case BUG_UPDATE_SPONSORSHIP:
			$t_type_name = 'sponsorship-updated';
			break;
		case BUG_DELETE_SPONSORSHIP:
			$t_type_name = 'sponsorship-deleted';
			break;
		case BUG_PAID_SPONSORSHIP:
			$t_type_name = 'sponsorship-paid';
			break;
		case BUG_ADD_RELATIONSHIP:
			$t_type_name = 'relationship-added';
			break;
		case BUG_REPLACE_RELATIONSHIP:
			$t_type_name = 'relationship-updated';
			break;
		case BUG_DEL_RELATIONSHIP:
			$t_type_name = 'relationship-deleted';
			break;
		case BUG_CLONED_TO:
			$t_type_name = 'issue-cloned-to';
			break;
		case BUG_CREATED_FROM:
			$t_type_name = 'issue-cloned-from';
			break;
		case TAG_ATTACHED:
			$t_type_name = 'tag-added';
			break;
		case TAG_DETACHED:
			$t_type_name = 'tag-deleted';
			break;
		case TAG_RENAMED:
			$t_type_name = 'tag-updated';
			break;
		case BUG_REVISION_DROPPED:
			$t_type_name = 'revision-deleted';
			break;
		case BUGNOTE_REVISION_DROPPED:
			$t_type_name = 'note-revision-deleted';
			break;
		default:
			$t_type_name = '';
			break;
	}

	return $t_type_name;
}

/**
 * Localizes one raw history item.
 *
 * @param int     $p_bug_id     Parent bug id
 * @param string  $p_field_name The field name of the field being localized.
 * @param integer $p_type       The type of the history entry.
 * @param string  $p_old_value  The old value of the field.
 * @param string  $p_new_value  The new value of the field.
 * @param boolean $p_linkify    Whether to return a string containing hyperlinks.
 *
 * @return array with two elements indexed as 'note' and 'change'
 */
function history_localize_item( $p_bug_id, $p_field_name, $p_type, $p_old_value, $p_new_value, $p_linkify = true ) {
	$t_note = '';
	$t_change = '';
	$t_raw = true;

	if( PLUGIN_HISTORY == $p_type ) {
		$t_note = lang_get_defaulted( 'plugin_' . $p_field_name, $p_field_name );
		$t_change = ( isset( $p_new_value ) ? $p_old_value . ' => ' . $p_new_value : $p_old_value );

		return array( 'note' => $t_note, 'change' => $t_change, 'raw' => true );
	}

	$t_field_localized = history_localize_field_name( $p_field_name );
	switch( $p_field_name ) {
		case 'status':
		case 'severity':
		case 'reproducibility':
		case 'resolution':
		case 'priority':
		case 'eta':
		case 'view_state':
		case 'projection':
			$p_old_value = get_enum_element( $p_field_name, $p_old_value );
			$p_new_value = get_enum_element( $p_field_name, $p_new_value );
			break;
		case 'sticky':
			$p_old_value = gpc_string_to_bool( $p_old_value ) ? lang_get( 'yes' ) : lang_get( 'no' );
			$p_new_value = gpc_string_to_bool( $p_new_value ) ? lang_get( 'yes' ) : lang_get( 'no' );
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
			break;
		case 'handler_id':
		case 'reporter_id':
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
		case 'date_submitted':
		case 'last_updated':
			$p_old_value = date( config_get( 'normal_date_format' ), $p_old_value );
			$p_new_value = date( config_get( 'normal_date_format' ), $p_new_value );
			break;
		case 'due_date':
			if( $p_old_value !== '' ) {
				$p_old_value = date( config_get( 'normal_date_format' ), (int)$p_old_value );
			}
			if( $p_new_value !== '' ) {
				$p_new_value = date( config_get( 'normal_date_format' ), (int)$p_new_value );
			}
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
				if( $p_linkify
					&& bug_revision_exists( $t_new_value )
					&& access_can_view_bugnote_revisions( $t_old_value )
				) {
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
			case STEP_TO_REPRODUCE_UPDATED:
			case ADDITIONAL_INFO_UPDATED:
				switch( $p_type ) {
					case DESCRIPTION_UPDATED:
						$t_note = lang_get( 'description_updated' );
						break;
					case STEP_TO_REPRODUCE_UPDATED:
						$t_note = lang_get( 'steps_to_reproduce_updated' );
						break;
					case ADDITIONAL_INFO_UPDATED:
						$t_note = lang_get( 'additional_information_updated' );
						break;
				}
				$t_old_value = (int)$p_old_value;
				if( $p_linkify
					&& bug_revision_exists( $t_old_value )
					&& access_can_view_bug_revisions( $p_bug_id )
				) {
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
				$t_note = lang_get( 'bug_revision_dropped_history' ) . ': '
					. bug_revision_get_type_name( $p_new_value ) . ': '
					. $p_old_value;
				break;
			case BUGNOTE_REVISION_DROPPED:
				$t_note = lang_get( 'bugnote_revision_dropped_history' ) . ': '
					. $p_new_value . ': '
					. $p_old_value;
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

/**
 * Link the file added/deleted history events that match the specified bug_id and filename
 * with the specified bugnote id.
 *
 * @param integer $p_bug_id The bug id.
 * @param string $p_filename The filename dot extension (display name).
 * @param integer $p_bugnote_id The bugnote id.
 * @return void
 */
function history_link_file_to_bugnote( $p_bug_id, $p_filename, $p_bugnote_id ) {
	db_param_push();
	$t_query = 'UPDATE {bug_history} SET new_value = ' . db_param() .
		' WHERE bug_id=' . db_param() . ' AND old_value=' . db_param() .
		' AND (type=' . db_param() . ' OR type=' . db_param() . ')';

	db_query( $t_query, array( (int)$p_bugnote_id, (int)$p_bug_id, $p_filename, FILE_ADDED, FILE_DELETED ) );
}

