<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: history_api.php,v 1.15 2003-07-26 13:50:20 vboctor Exp $
	# --------------------------------------------------------

	###########################################################################
	# History API
	###########################################################################

	# --------------------
	# log the changes (old / new value are supplied to reduce db access)
	# events should be logged *after* the modification
	function history_log_event_direct( $p_bug_id, $p_field_name, $p_old_value, $p_new_value, $p_user_id = null ) {
		# Only log events that change the value
		if ( $p_new_value != $p_old_value ) {
			if ( null === $p_user_id ) {
				$p_user_id	= auth_get_current_user_id();
			};

			$c_field_name	= db_prepare_string( $p_field_name );
			$c_old_value	= db_prepare_string( $p_old_value );
			$c_new_value	= db_prepare_string( $p_new_value );
			$c_bug_id		= db_prepare_int( $p_bug_id );
			$c_user_id		= db_prepare_int( $p_user_id );
			
			$query = "INSERT INTO " . config_get( 'mantis_bug_history_table' ) . "
					( user_id, bug_id, date_modified, field_name, old_value, new_value )
					VALUES
					( '$c_user_id', '$c_bug_id', NOW(), '$c_field_name', '$c_old_value', '$c_new_value' )";
			$result = db_query( $query );
		}
	}
	# --------------------
	# log the changes
	# events should be logged *after* the modification
	function history_log_event( $p_bug_id, $p_field_name, $p_old_value ) {
		history_log_event_direct( $p_bug_id, $p_field_name, $p_old_value, bug_get_field( $p_bug_id, $p_field_name ) );
	}
	# --------------------
	# log the changes
	# events should be logged *after* the modification
	# These are special case logs (new bug, deleted bugnote, etc.)
	function history_log_event_special( $p_bug_id, $p_type, $p_optional='',  $p_optional2='' ) {
		$c_bug_id		= db_prepare_int( $p_bug_id );
		$c_type			= db_prepare_int( $p_type );
		$c_optional		= db_prepare_string( $p_optional );
		$c_optional2	= db_prepare_string( $p_optional2 );
		$t_user_id		= auth_get_current_user_id();

		$query = "INSERT INTO " . config_get( 'mantis_bug_history_table' ) . "
				( user_id, bug_id, date_modified, type, old_value, new_value )
				VALUES
				( '$t_user_id', '$c_bug_id', NOW(), '$c_type', '$c_optional', '$c_optional2' )";
		$result = db_query( $query );
	}
	# --------------------
	# return all bug history for a given bug id ordered by date
	function history_get_events( $p_bug_id ) {
		$t_mantis_bug_history_table = config_get( 'mantis_bug_history_table' );
		$t_mantis_user_table = config_get( 'mantis_user_table' );

		$c_bug_id = db_prepare_int( $p_bug_id );

		$query = "SELECT b.*, u.username
				FROM $t_bug_history_table b
				LEFT JOIN $t_mantis_user_table u
				ON b.user_id=u.id
				WHERE bug_id='$c_bug_id'
				ORDER BY date_modified DESC";
		$result = db_query( $query );
	}
	# --------------------
	# Retrieves the history events for the specified bug id and returns it in an array
	# The array is indexed from 0 to N-1.  The second dimension is: 'date', 'username',
	# 'note', 'change'.
	function history_get_events_array( $p_bug_id ) {
		$t_mantis_bug_history_table = config_get( 'mantis_bug_history_table' );
		$t_mantis_user_table = config_get( 'mantis_user_table' );
		$t_history_order = config_get( 'history_order' );
		$c_bug_id = db_prepare_int( $p_bug_id );

		# grab history and display by date_modified then field_name
		$query = "SELECT b.*, UNIX_TIMESTAMP(b.date_modified) as date_modified, u.username
				FROM $t_mantis_bug_history_table b
				LEFT JOIN $t_mantis_user_table u
				ON b.user_id=u.id
				WHERE bug_id='$c_bug_id'
				ORDER BY date_modified $t_history_order, field_name ASC";
		$result = db_query( $query );
		$history_count = db_num_rows( $result );
		$history = array();

		for ( $i=0; $i < $history_count; $i++ ) {
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, 'v' );

			$v_date_modified = date( config_get( 'normal_date_format' ), $v_date_modified );

			switch ( $v_field_name ) {
			case 'category':
				$t_field_localized = lang_get( 'category' );
				break;
			case 'status':
				$v_old_value = get_enum_element( 'status', $v_old_value );
				$v_new_value = get_enum_element( 'status', $v_new_value );
				$t_field_localized = lang_get( 'status' );
				break;
			case 'severity':
				$v_old_value = get_enum_element( 'severity', $v_old_value );
				$v_new_value = get_enum_element( 'severity', $v_new_value );
				$t_field_localized = lang_get( 'severity' );
				break;
			case 'reproducibility':
				$v_old_value = get_enum_element( 'reproducibility', $v_old_value );
				$v_new_value = get_enum_element( 'reproducibility', $v_new_value );
				$t_field_localized = lang_get( 'reproducibility' );
				break;
			case 'resolution':
				$v_old_value = get_enum_element( 'resolution', $v_old_value );
				$v_new_value = get_enum_element( 'resolution', $v_new_value );
				$t_field_localized = lang_get( 'resolution' );
				break;
			case 'priority':
				$v_old_value = get_enum_element( 'priority', $v_old_value );
				$v_new_value = get_enum_element( 'priority', $v_new_value );
				$t_field_localized = lang_get( 'priority' );
				break;
			case 'eta':
				$v_old_value = get_enum_element( 'eta', $v_old_value );
				$v_new_value = get_enum_element( 'eta', $v_new_value );
				$t_field_localized = lang_get( 'eta' );
				break;
			case 'view_state':
				$v_old_value = get_enum_element( 'view_state', $v_old_value );
				$v_new_value = get_enum_element( 'view_state', $v_new_value );
				$t_field_localized = lang_get( 'view_status' );
				break;
			case 'projection':
				$v_old_value = get_enum_element( 'projection', $v_old_value );
				$v_new_value = get_enum_element( 'projection', $v_new_value );
				$t_field_localized = lang_get( 'projection' );
				break;
			case 'project_id':
				if ( project_exists( $v_old_value ) ) {
					$v_old_value = project_get_field( $v_old_value, 'name' );
				} else {
					$v_old_value = '@'.$v_old_value.'@';
				}

				// Note that the new value maybe an intermediately project and not the 
				// current one.
				if ( project_exists( $v_new_value ) ) {
					$v_new_value = project_get_field( $v_new_value, 'name' );
				} else {
					$v_new_value = '@'.$v_new_value.'@';
				}
				$t_field_localized = lang_get( 'email_project' );
				break;
			case 'handler_id':
				$t_field_localized = lang_get( 'assigned_to' );
			case 'reporter_id':
				if ( 'reporter_id' == $v_field_name ) {
					$t_field_localized = lang_get( 'reporter' );
				}
				if ( 0 == $v_old_value ) {
					$v_old_value = '';
				} else {
					$v_old_value = user_get_name( $v_old_value );
				}

				if ( 0 == $v_new_value ) {
					$v_new_value = '';
				} else {
					$v_new_value = user_get_name( $v_new_value );
				}
				break;
			default:
				$t_field_localized = $v_field_name;
				break;
			}

			if ( NORMAL_TYPE != $v_type ) {
				switch ( $v_type ) {
				case NEW_BUG:
					$t_note = lang_get( 'new_bug' );
					break;
				case BUGNOTE_ADDED:
					$t_note = lang_get( 'bugnote_added' ) . ": " . $v_old_value;
					break;
				case BUGNOTE_UPDATED:
					$t_note = lang_get( 'bugnote_edited' ) . ": " . $v_old_value;
					break;
				case BUGNOTE_DELETED:
					$t_note = lang_get( 'bugnote_deleted' ) . ": " . $v_old_value;
					break;
				case SUMMARY_UPDATED:
					$t_note = lang_get( 'summary_updated' );
					break;
				case DESCRIPTION_UPDATED:
					$t_note = lang_get( 'description_updated' );
					break;
				case ADDITIONAL_INFO_UPDATED:	
					$t_note = lang_get( 'additional_information_updated' );
					break;
				case STEP_TO_REPRODUCE_UPDATED:	
					$t_note = lang_get( 'steps_to_reproduce_updated' );
					break;
				case FILE_ADDED:
					$t_note = lang_get( 'file_added' ) . ": " . $v_old_value;
					break;
				case FILE_DELETED:
					$t_note = lang_get( 'file_deleted' ) . ": " . $v_old_value;
					break;
				case BUGNOTE_STATE_CHANGED:
					$v_old_value = get_enum_element( 'view_state', $v_old_value );
					$t_note = lang_get( 'bugnote_view_state' ) . ": " . $v_old_value . ": " . $v_new_value;
					break;
				case BUG_MONITOR:
					$v_old_value = user_get_field( $v_old_value, 'username' );
					$t_note = lang_get( 'bug_monitor' ) . ": " . $v_old_value;
					break;
				case BUG_UNMONITOR:
					$v_old_value = user_get_field( $v_old_value, 'username' );
					$t_note = lang_get( 'bug_end_monitor' ) . ": " . $v_old_value;
					break;
				}
			}

			$history[$i]['date'] = $v_date_modified;
			$history[$i]['userid'] = $v_user_id;

			# $v_username will be empty, if user no longer exists.
			if ( '' == $v_username ) {
				$history[$i]['username'] = user_get_name( $v_user_id );
			} else {
				$history[$i]['username'] = $v_username;
			}

			# output special cases
			if ( NORMAL_TYPE != $v_type ) {
				$history[$i]['note'] = $t_note;
				$history[$i]['change'] = '';
			} else {   # output normal changes
				$history[$i]['note'] = $t_field_localized;
				$history[$i]['change'] = $v_old_value . ' => ' . $v_new_value;
			} # end if DEFAULT
		} # end for loop

		return ( $history );
	}
	# --------------------
	# delete all history associated with a bug
	function history_delete( $p_bug_id ) {
		$c_bug_id = db_prepare_int( $p_bug_id );

		$t_bug_history_table = config_get( 'mantis_bug_history_table' );

		$query = "DELETE
				  FROM $t_bug_history_table
				  WHERE bug_id='$c_bug_id'";
		db_query($query);

		# db_query() errors on failure so:
		return true;
	}
?>
