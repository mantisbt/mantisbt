<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: history_api.php,v 1.28 2004-08-17 18:01:18 thraxisp Exp $
	# --------------------------------------------------------

	### History API ###

	# --------------------
	# log the changes (old / new value are supplied to reduce db access)
	# events should be logged *after* the modification
	function history_log_event_direct( $p_bug_id, $p_field_name, $p_old_value, $p_new_value, $p_user_id = null ) {
		# Only log events that change the value
		if ( $p_new_value != $p_old_value ) {
			if ( null === $p_user_id ) {
				$p_user_id	= auth_get_current_user_id();
			}

			$c_field_name	= db_prepare_string( $p_field_name );
			$c_old_value	= db_prepare_string( $p_old_value );
			$c_new_value	= db_prepare_string( $p_new_value );
			$c_bug_id		= db_prepare_int( $p_bug_id );
			$c_user_id		= db_prepare_int( $p_user_id );

			$t_mantis_bug_history_table = config_get( 'mantis_bug_history_table' );

			$query = "INSERT INTO $t_mantis_bug_history_table
						( user_id, bug_id, date_modified, field_name, old_value, new_value )
					VALUES
						( '$c_user_id', '$c_bug_id', " . db_now() . ", '$c_field_name', '$c_old_value', '$c_new_value' )";
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

		$t_mantis_bug_history_table = config_get( 'mantis_bug_history_table' );

		$query = "INSERT INTO $t_mantis_bug_history_table
					( user_id, bug_id, date_modified, type, old_value, new_value )
				VALUES
					( '$t_user_id', '$c_bug_id', " . db_now() . ", '$c_type', '$c_optional', '$c_optional2' )";
		$result = db_query( $query );
	}
	# --------------------
	# return all bug history for a given bug id ordered by date
	function history_get_events( $p_bug_id ) {
		$t_mantis_bug_history_table	= config_get( 'mantis_bug_history_table' );
		$t_mantis_user_table		= config_get( 'mantis_user_table' );

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
		$t_normal_date_format = config_get( 'normal_date_format' );

		$raw_history = history_get_raw_events_array( $p_bug_id );
		$raw_history_count = count( $raw_history );
		$history = array();

		for ( $i=0; $i < $raw_history_count; $i++ ) {
			$history[$i]			= history_localize_item( $raw_history[$i]['field'], $raw_history[$i]['type'], $raw_history[$i]['old_value'], $raw_history[$i]['new_value'] );
			$history[$i]['date']	= date( $t_normal_date_format, $raw_history[$i]['date'] );
			$history[$i]['userid']	= $raw_history[$i]['userid'];
			$history[$i]['username'] = $raw_history[$i]['username'];
		}

		return ( $history );
	}

	# --------------------
	# Retrieves the raw history events for the specified bug id and returns it in an array
	# The array is indexed from 0 to N-1.  The second dimension is: 'date', 'userid', 'username',
	# 'field','type','old_value','new_value'
	function history_get_raw_events_array( $p_bug_id ) {
		$t_mantis_bug_history_table	= config_get( 'mantis_bug_history_table' );
		$t_mantis_user_table		= config_get( 'mantis_user_table' );
		$t_history_order			= config_get( 'history_order' );
		$c_bug_id					= db_prepare_int( $p_bug_id );

		# grab history and display by date_modified then field_name
		# @@@ by MASC I guess it's better by id then by field_name. When we have more history lines with the same
		# date, it's better to respect the storing order otherwise we should risk to mix different information
		# I give you an example. We create a child of a bug with different custom fields. In the history of the child
		# bug we will find the line related to the relationship mixed with the custom fields (the history is creted
		# for the new bug with the same timestamp...)
		$query = "SELECT *
				FROM $t_mantis_bug_history_table
				WHERE bug_id='$c_bug_id'
				ORDER BY date_modified $t_history_order,id";
		$result = db_query( $query );
		$raw_history_count = db_num_rows( $result );
		$raw_history = array();

		for ( $i=0; $i < $raw_history_count; ++$i ) {
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, 'v' );

			$raw_history[$i]['date']	= db_unixtimestamp( $v_date_modified );
			$raw_history[$i]['userid']	= $v_user_id;

			# user_get_name handles deleted users, and username vs realname
			$raw_history[$i]['username'] = user_get_name( $v_user_id );

			$raw_history[$i]['field']		= $v_field_name;
			$raw_history[$i]['type']		= $v_type;
			$raw_history[$i]['old_value']	= $v_old_value;
			$raw_history[$i]['new_value']	= $v_new_value;
		} # end for loop

		return $raw_history;
	}

	# --------------------
	# Localizes one raw history item specified by set the next parameters: $p_field_name, $p_type, $p_old_value, $p_new_value
	# Returns array with two elements indexed as 'note' and 'change'
	#
	function history_localize_item( $p_field_name, $p_type, $p_old_value, $p_new_value ) {
		$t_note = '';
		$t_change = '';
		$t_field_localized = $p_field_name;

		switch ( $p_field_name ) {
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
			case 'project_id':
				if ( project_exists( $p_old_value ) ) {
					$p_old_value = project_get_field( $p_old_value, 'name' );
				} else {
					$p_old_value = '@'.$p_old_value.'@';
				}

				# Note that the new value maybe an intermediately project and not the
				# current one.
				if ( project_exists( $p_new_value ) ) {
					$p_new_value = project_get_field( $p_new_value, 'name' );
				} else {
					$p_new_value = '@'.$p_new_value.'@';
				}
				$t_field_localized = lang_get( 'email_project' );
				break;
			case 'handler_id':
				$t_field_localized = lang_get( 'assigned_to' );
			case 'reporter_id':
				if ( 'reporter_id' == $p_field_name ) {
					$t_field_localized = lang_get( 'reporter' );
				}
				if ( 0 == $p_old_value ) {
					$p_old_value = '';
				} else {
					$p_old_value = user_get_name( $p_old_value );
				}

				if ( 0 == $p_new_value ) {
					$p_new_value = '';
				} else {
					$p_new_value = user_get_name( $p_new_value );
				}
				break;
			case 'fixed_in_version':
				$t_field_localized = lang_get( 'fixed_in_version' );
				break;
			case 'date_submitted':
				$t_field_localized = lang_get( 'date_submitted' );
				break;
			case 'last_updated':
				$t_field_localized = lang_get( 'last_update' );
				break;
			case 'summary':
				$t_field_localized = lang_get( 'summary' );
				break;
			case 'duplicate_id':
				$t_field_localized = lang_get( 'duplicate_id' );
				break;
		}

		if ( NORMAL_TYPE != $p_type ) {
			switch ( $p_type ) {
				case NEW_BUG:
					$t_note = lang_get( 'new_bug' );
					break;
				case BUGNOTE_ADDED:
					$t_note = lang_get( 'bugnote_added' ) . ": " . $p_old_value;
					break;
				case BUGNOTE_UPDATED:
					$t_note = lang_get( 'bugnote_edited' ) . ": " . $p_old_value;
					break;
				case BUGNOTE_DELETED:
					$t_note = lang_get( 'bugnote_deleted' ) . ": " . $p_old_value;
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
					$t_note = lang_get( 'file_added' ) . ": " . $p_old_value;
					break;
				case FILE_DELETED:
					$t_note = lang_get( 'file_deleted' ) . ": " . $p_old_value;
					break;
				case BUGNOTE_STATE_CHANGED:
					$p_old_value = get_enum_element( 'view_state', $p_old_value );
					$t_note = lang_get( 'bugnote_view_state' ) . ": " . $p_old_value . ": " . $p_new_value;
					break;
				case BUG_MONITOR:
					$p_old_value = user_get_field( $p_old_value, 'username' );
					$t_note = lang_get( 'bug_monitor' ) . ": " . $p_old_value;
					break;
				case BUG_UNMONITOR:
					$p_old_value = user_get_field( $p_old_value, 'username' );
					$t_note = lang_get( 'bug_end_monitor' ) . ": " . $p_old_value;
					break;
				case BUG_DELETED:
					$t_note = lang_get( 'bug_deleted' ) . ": " . $p_old_value;
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
				case BUG_ADD_RELATIONSHIP:
					$t_note = lang_get( 'relationship_added' );
					$t_change = relationship_get_description_for_history( $p_old_value ) . ' ' . bug_format_id( $p_new_value );
					break;
				case BUG_DEL_RELATIONSHIP:
					$t_note = lang_get( 'relationship_deleted' );
					$t_change = relationship_get_description_for_history( $p_old_value ) . ' ' . bug_format_id( $p_new_value );
					break;
				case BUG_CLONED_TO:
					$t_note = lang_get( 'bug_cloned_to' );
					$t_change = bug_format_id( $p_new_value );
					break;
				case BUG_CREATED_FROM:
					$t_note = lang_get( 'bug_created_from' );
					$t_change = bug_format_id( $p_new_value );
					break;
				case CHECKIN:
					$t_note = lang_get( 'checkin' );
					break;
			}
		}

		# output special cases
		if ( NORMAL_TYPE == $p_type ) {
			$t_note		= $t_field_localized;
			$t_change	= $p_old_value . ' => ' . $p_new_value;
		} # end if DEFAULT
		return array( 'note' => $t_note, 'change' => $t_change );
	}

	# --------------------
	# delete all history associated with a bug
	function history_delete( $p_bug_id ) {
		$c_bug_id = db_prepare_int( $p_bug_id );

		$t_bug_history_table = config_get( 'mantis_bug_history_table' );

		$query = "DELETE FROM $t_bug_history_table
				  WHERE bug_id='$c_bug_id'";
		db_query($query);

		# db_query() errors on failure so:
		return true;
	}
?>
