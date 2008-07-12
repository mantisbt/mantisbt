<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

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

	# --------------------------------------------------------
	# $Id: bugnote_api.php,v 1.46.2.1 2007-10-13 22:35:14 giallu Exp $
	# --------------------------------------------------------

	$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;

	require_once( $t_core_dir . 'current_user_api.php' );
	require_once( $t_core_dir . 'email_api.php' );
	require_once( $t_core_dir . 'history_api.php' );
	require_once( $t_core_dir . 'bug_api.php' );

	### Bugnote API ###

	#===================================
	# Bugnote Data Structure Definition
	#===================================
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

	#===================================
	# Boolean queries and ensures
	#===================================

	# --------------------
	# Check if a bugnote with the given ID exists
	#
	# return true if the bugnote exists, false otherwise
	function bugnote_exists( $p_bugnote_id ) {
		$c_bugnote_id   	= db_prepare_int( $p_bugnote_id );
		$t_bugnote_table	= config_get( 'mantis_bugnote_table' );

		$query 	= "SELECT COUNT(*)
		          	FROM $t_bugnote_table
		          	WHERE id='$c_bugnote_id'";
		$result	= db_query( $query );

		if ( 0 == db_result( $result ) ) {
			return false;
		} else {
			return true;
		}
	}

	# --------------------
	# Check if a bugnote with the given ID exists
	#
	# return true if the bugnote exists, raise an error if not
	function bugnote_ensure_exists( $p_bugnote_id ) {
		if ( !bugnote_exists( $p_bugnote_id ) ) {
			trigger_error( ERROR_BUGNOTE_NOT_FOUND, ERROR );
		}
	}

	# --------------------
	# Check if the given user is the reporter of the bugnote
	# return true if the user is the reporter, false otherwise
	function bugnote_is_user_reporter( $p_bugnote_id, $p_user_id ) {
		if ( bugnote_get_field( $p_bugnote_id, 'reporter_id' ) == $p_user_id ) {
			return true;
		} else {
			return false;
		}
	}

	#===================================
	# Creation / Deletion / Updating
	#===================================

	# --------------------
	# Add a bugnote to a bug
	#
	# return the ID of the new bugnote
	function bugnote_add ( $p_bug_id, $p_bugnote_text, $p_time_tracking = '0:00', $p_private = false, $p_type = 0, $p_attr = '', $p_user_id = null, $p_send_email = TRUE ) {
		$c_bug_id            	= db_prepare_int( $p_bug_id );
		$c_bugnote_text      	= db_prepare_string( $p_bugnote_text );
		$c_time_tracking	= db_prepare_time( $p_time_tracking );
		$c_private           	= db_prepare_bool( $p_private );
		$c_type            	= db_prepare_int( $p_type );
		$c_attr      	= db_prepare_string( $p_attr );

		$t_bugnote_text_table	= config_get( 'mantis_bugnote_text_table' );
		$t_bugnote_table     	= config_get( 'mantis_bugnote_table' );

		$t_time_tracking_enabled = config_get( 'time_tracking_enabled' );
		$t_time_tracking_without_note  = config_get( 'time_tracking_without_note' );
		if ( ON == $t_time_tracking_enabled && $c_time_tracking > 0 ) {
			if ( is_blank( $p_bugnote_text ) && OFF == $t_time_tracking_without_note ) {
				error_parameters( lang_get( 'bugnote' ) );
				trigger_error( ERROR_EMPTY_FIELD, ERROR );
			}
			$c_type = TIME_TRACKING;
		} else if ( is_blank( $p_bugnote_text ) ) {
			return false;
		}

		# insert bugnote text
		$query = "INSERT INTO $t_bugnote_text_table
		          		( note )
		          	 VALUES
		          		( '$c_bugnote_text' )";
		db_query( $query );

		# retrieve bugnote text id number
		$t_bugnote_text_id = db_insert_id( $t_bugnote_text_table );

		# get user information
		if ( $p_user_id === null ) {
			$c_user_id = auth_get_current_user_id();
		} else {
			$c_user_id = db_prepare_int( $p_user_id );
		}

		# Check for private bugnotes.
		# @@@ VB: Should we allow users to report private bugnotes, and possibly see only their own private ones
		if ( $p_private && access_has_bug_level( config_get( 'private_bugnote_threshold' ), $p_bug_id, $c_user_id ) ) {
			$t_view_state = VS_PRIVATE;
		} else {
			$t_view_state = VS_PUBLIC;
		}

		# insert bugnote info
		$query = "INSERT INTO $t_bugnote_table
					(bug_id, reporter_id, bugnote_text_id, view_state, date_submitted, last_modified, note_type, note_attr, time_tracking )
		          	 VALUES
					('$c_bug_id', '$c_user_id','$t_bugnote_text_id', '$t_view_state', " . db_now() . "," . db_now() . ", '$c_type', '$c_attr', '$c_time_tracking' )";
		db_query( $query );

		# get bugnote id
		$t_bugnote_id = db_insert_id( $t_bugnote_table );

		# update bug last updated
		bug_update_date( $p_bug_id );

		# log new bug
		history_log_event_special( $p_bug_id, BUGNOTE_ADDED, bugnote_format_id( $t_bugnote_id ) );

		# only send email if the text is not blank, otherwise, it is just recording of time without a comment.
		if ( $p_send_email && !is_blank( $p_bugnote_text ) ) {
			email_bugnote_add( $p_bug_id );
		}
		return $t_bugnote_id;
	}

	# --------------------
	# Delete a bugnote
	function bugnote_delete( $p_bugnote_id ) {
		$c_bugnote_id        	= db_prepare_int( $p_bugnote_id );
		$t_bug_id            	= bugnote_get_field( $p_bugnote_id, 'bug_id' );
		$t_bugnote_text_id   	= bugnote_get_field( $p_bugnote_id, 'bugnote_text_id' );
		$t_bugnote_text_table	= config_get( 'mantis_bugnote_text_table' );
		$t_bugnote_table     	= config_get( 'mantis_bugnote_table' );

		# Remove the bugnote
		$query = "DELETE FROM $t_bugnote_table
		          	WHERE id='$c_bugnote_id'";
		db_query( $query );

		# Remove the bugnote text
		$query = "DELETE FROM $t_bugnote_text_table
		          	WHERE id='$t_bugnote_text_id'";
		db_query( $query );

		# log deletion of bug
		history_log_event_special( $t_bug_id, BUGNOTE_DELETED, bugnote_format_id( $p_bugnote_id ) );

		return true;
	}

	# --------------------
	# delete all bugnotes associated with the given bug
	function bugnote_delete_all( $p_bug_id ) {
		$c_bug_id            	= db_prepare_int( $p_bug_id );
		$t_bugnote_table     	= config_get( 'mantis_bugnote_table' );
		$t_bugnote_text_table	= config_get( 'mantis_bugnote_text_table' );

		# Delete the bugnote text items
		$query = "SELECT bugnote_text_id
		          	FROM $t_bugnote_table
		          	WHERE bug_id='$c_bug_id'";
		$result = db_query( $query );
		$bugnote_count = db_num_rows( $result );
		for ( $i = 0 ; $i < $bugnote_count ; $i++ ) {
			$row = db_fetch_array( $result );
			$t_bugnote_text_id = $row['bugnote_text_id'];

			# Delete the corresponding bugnote texts
			$query = "DELETE FROM $t_bugnote_text_table
			          	WHERE id='$t_bugnote_text_id'";
			db_query( $query );
		}

		# Delete the corresponding bugnotes
		$query = "DELETE FROM $t_bugnote_table
		          	WHERE bug_id='$c_bug_id'";
		$result = db_query( $query );

		# db_query() errors on failure so:
		return true;
	}


	#===================================
	# Data Access
	#===================================

	# --------------------
	# Get the text associated with the bugnote
	function bugnote_get_text( $p_bugnote_id ) {
		$t_bugnote_text_id   	= bugnote_get_field( $p_bugnote_id, 'bugnote_text_id' );
		$t_bugnote_text_table	= config_get( 'mantis_bugnote_text_table' );

		# grab the bugnote text
		$query = "SELECT note
		          	FROM $t_bugnote_text_table
		          	WHERE id='$t_bugnote_text_id'";
		$result = db_query( $query );

		return db_result( $result );
	}

	# --------------------
	# Get a field for the given bugnote
	function bugnote_get_field( $p_bugnote_id, $p_field_name ) {
		$c_bugnote_id   	= db_prepare_int( $p_bugnote_id );
		$c_field_name   	= db_prepare_string( $p_field_name );
		$t_bugnote_table 	= config_get( 'mantis_bugnote_table' );

		$query = "SELECT $c_field_name
		          	FROM $t_bugnote_table
		          	WHERE id='$c_bugnote_id' ";
		$result = db_query( $query, 1 );

		return db_result( $result );
	}

	# --------------------
	# Get latest bugnote id
	function bugnote_get_latest_id( $p_bug_id ) {
		$c_bug_id   	= db_prepare_int( $p_bug_id );
		$t_bugnote_table 	= config_get( 'mantis_bugnote_table' );

		$query = "SELECT id
		          	FROM $t_bugnote_table
		          	WHERE bug_id='$c_bug_id'
		          	ORDER by last_modified DESC";
		$result = db_query( $query, 1 );

		return db_result( $result );
	}

	# --------------------
	# Build the bugnotes array for the given bug_id filtered by specified $p_user_access_level.
	# Bugnotes are sorted by date_submitted according to 'bugnote_order' configuration setting.
	#
	# Return BugnoteData class object with raw values from the tables except the field
	# last_modified - it is UNIX_TIMESTAMP.
	function bugnote_get_all_visible_bugnotes( $p_bug_id, $p_user_access_level, $p_user_bugnote_order, $p_user_bugnote_limit ) {
		$t_all_bugnotes	            	= bugnote_get_all_bugnotes( $p_bug_id, $p_user_bugnote_order, $p_user_bugnote_limit );
		$t_private_bugnote_threshold	= config_get( 'private_bugnote_threshold' );

		$t_private_bugnote_visible = access_compare_level( $p_user_access_level, config_get( 'private_bugnote_threshold' ) );
		$t_time_tracking_visible = access_compare_level( $p_user_access_level, config_get( 'time_tracking_view_threshold' ) );

		$t_bugnotes = array();
		foreach ( $t_all_bugnotes as $t_note_index => $t_bugnote ) {
			if ( $t_private_bugnote_visible || ( VS_PUBLIC == $t_bugnote->view_state ) ) {
				# If the access level specified is not enough to see time tracking information
				# then reset it to 0.
				if ( !$t_time_tracking_visible ) {
					$t_bugnote->time_tracking = 0;
				}

				$t_bugnotes[$t_note_index] = $t_bugnote;
			}
		}

		return $t_bugnotes;
	}

	# --------------------
	# Build the bugnotes array for the given bug_id. Bugnotes are sorted by date_submitted
	# according to 'bugnote_order' configuration setting.
	# Return BugnoteData class object with raw values from the tables except the field
	# last_modified - it is UNIX_TIMESTAMP.
	# The data is not filtered by VIEW_STATE !!
	function bugnote_get_all_bugnotes( $p_bug_id, $p_user_bugnote_order, $p_user_bugnote_limit ) {
		global $g_cache_bugnotes;

		if ( !isset( $g_cache_bugnotes ) )  {
			$g_cache_bugnotes = array();
		}

		# the cache should be aware of the sorting order
		if ( !isset( $g_cache_bugnotes[$p_bug_id][$p_user_bugnote_order] ) )  {
			$c_bug_id            	= db_prepare_int( $p_bug_id );
			$t_bugnote_table     	= config_get( 'mantis_bugnote_table' );
			$t_bugnote_text_table	= config_get( 'mantis_bugnote_text_table' );

			if ( 0 == $p_user_bugnote_limit ) {
				## Show all bugnotes
				$t_bugnote_limit = -1;
				$t_bugnote_offset = -1;
			} else {
				## Use offset only if order is ASC to get the last bugnotes
				if ( 'ASC' == $p_user_bugnote_order ) {
					$result = db_query( "SELECT COUNT(*) AS row_count FROM $t_bugnote_table WHERE bug_id = '$c_bug_id'" );
					$row    = db_fetch_array( $result );

					$t_bugnote_offset = $row['row_count'] - $p_user_bugnote_limit;
				} else {
					$t_bugnote_offset = -1;
				}

				$t_bugnote_limit = $p_user_bugnote_limit;
			}

			# sort by bugnote id which should be more accurate than submit date, since two bugnotes
			# may be submitted at the same time if submitted using a script (eg: MantisConnect).
			$query = "SELECT b.*, t.note
			          	FROM      $t_bugnote_table b
			          	LEFT JOIN $t_bugnote_text_table t ON b.bugnote_text_id = t.id
			          	WHERE b.bug_id = '$c_bug_id'
			          	ORDER BY b.id $p_user_bugnote_order";
			$t_bugnotes = array();

			# BUILD bugnotes array
			$result	= db_query( $query, $t_bugnote_limit, $t_bugnote_offset );
			$count 	= db_num_rows( $result );
			for ( $i=0; $i < $count; $i++ ) {
				$row = db_fetch_array( $result );

				$t_bugnote = new BugnoteData;

				$t_bugnote->id            = $row['id'];
				$t_bugnote->bug_id        = $row['bug_id'];
				$t_bugnote->note          = $row['note'];
				$t_bugnote->view_state    = $row['view_state'];
				$t_bugnote->reporter_id   = $row['reporter_id'];
				$t_bugnote->date_submitted = db_unixtimestamp( $row['date_submitted'] );
				$t_bugnote->last_modified = db_unixtimestamp( $row['last_modified'] );
				$t_bugnote->note_type     = $row['note_type'];
				$t_bugnote->note_attr     = $row['note_attr'];
				$t_bugnote->time_tracking = $row['time_tracking'];

				$t_bugnotes[] = $t_bugnote;
			}
			$g_cache_bugnotes[$p_bug_id][$p_user_bugnote_order] = $t_bugnotes;
		}

		return $g_cache_bugnotes[$p_bug_id][$p_user_bugnote_order];
	}

	#===================================
	# Data Modification
	#===================================

	# --------------------
	# Update the time_tracking field of the bugnote
	function bugnote_set_time_tracking( $p_bugnote_id, $p_time_tracking ) {
		$c_bugnote_id            = db_prepare_int( $p_bugnote_id );
		$c_bugnote_time_tracking = db_prepare_time( $p_time_tracking );
		$t_bugnote_table         = config_get( 'mantis_bugnote_table' );

		$query = "UPDATE $t_bugnote_table
				SET time_tracking = '$c_bugnote_time_tracking'
				WHERE id='$c_bugnote_id'";
		db_query( $query );

		# db_query() errors if there was a problem so:
		return true;
	}

	# --------------------
	# Update the last_modified field of the bugnote
	function bugnote_date_update( $p_bugnote_id ) {
		$c_bugnote_id		= db_prepare_int( $p_bugnote_id );
		$t_bugnote_table	= config_get( 'mantis_bugnote_table' );

		$query = "UPDATE $t_bugnote_table
		          	SET last_modified=" . db_now() . "
		          	WHERE id='$c_bugnote_id'";
		db_query( $query );

		# db_query() errors if there was a problem so:
		return true;
	}

	# --------------------
	# Set the bugnote text
	function bugnote_set_text( $p_bugnote_id, $p_bugnote_text ) {
		$c_bugnote_text	     	= db_prepare_string( $p_bugnote_text );
		$t_bug_id            	= bugnote_get_field( $p_bugnote_id, 'bug_id' );
		$t_bugnote_text_id   	= bugnote_get_field( $p_bugnote_id, 'bugnote_text_id' );
		$t_bugnote_text_table	= config_get( 'mantis_bugnote_text_table' );

		$query = "UPDATE $t_bugnote_text_table
		          	SET note='$c_bugnote_text'
		          	WHERE id='$t_bugnote_text_id'";
		db_query( $query );

		# updated the last_updated date
		bugnote_date_update( $p_bugnote_id );

		# log new bugnote
		history_log_event_special( $t_bug_id, BUGNOTE_UPDATED, bugnote_format_id( $p_bugnote_id ) );

		return true;
	}

	# --------------------
	# Set the view state of the bugnote
	function bugnote_set_view_state( $p_bugnote_id, $p_private ) {
		$c_bugnote_id	= db_prepare_int( $p_bugnote_id );
		$t_bug_id    	= bugnote_get_field( $p_bugnote_id, 'bug_id' );

		if ( $p_private ) {
			$t_view_state = VS_PRIVATE;
		} else {
			$t_view_state = VS_PUBLIC;
		}

		$t_bugnote_table = config_get( 'mantis_bugnote_table' );

		# update view_state
		$query = "UPDATE $t_bugnote_table
		          	SET view_state='$t_view_state'
		          	WHERE id='$c_bugnote_id'";
		db_query( $query );

		history_log_event_special( $t_bug_id, BUGNOTE_STATE_CHANGED, bugnote_format_id( $t_view_state ), $p_bugnote_id );

		return true;
	}


	#===================================
	# Other
	#===================================

	# --------------------
	# Pad the bugnote id with the appropriate number of zeros for printing
	function bugnote_format_id( $p_bugnote_id ) {
		$t_padding	= config_get( 'display_bugnote_padding' );

		return str_pad( $p_bugnote_id, $t_padding, '0', STR_PAD_LEFT );
	}


	#===================================
	# Bugnote Stats
	#===================================

	# --------------------
	# Returns an array of bugnote stats
	# $p_from - Starting date (yyyy-mm-dd) inclusive, if blank, then ignored.
	# $p_to - Ending date (yyyy-mm-dd) inclusive, if blank, then ignored.
	function bugnote_stats_get_events_array( $p_bug_id, $p_from, $p_to ) {
		$c_bug_id = db_prepare_int( $p_bug_id );
		$c_from = db_prepare_date( $p_from );
		$c_to = db_prepare_date( $p_to );

		$t_user_table = config_get( 'mantis_user_table' );
		$t_bugnote_table = config_get( 'mantis_bugnote_table' );

		if ( !is_blank( $c_from ) ) {
			$t_from_where = " AND bn.date_submitted >= '$c_from 00:00:00'";
		} else {
			$t_from_where = '';
		}

		if ( !is_blank( $c_to ) ) {
			$t_to_where = " AND bn.date_submitted <= '$c_to 23:59:59'";
		} else {
			$t_to_where = '';
		}

		$t_results = array();

		$query = "SELECT username, SUM(time_tracking) AS sum_time_tracking
				FROM $t_user_table u, $t_bugnote_table bn
				WHERE u.id = bn.reporter_id AND
				bn.bug_id = '$c_bug_id'
				$t_from_where $t_to_where
			GROUP BY u.id, u.username";

		$result = db_query( $query );

		while ( $row = db_fetch_array( $result ) ) {
			$t_results[] = $row;
		}

		return $t_results;
	}

	# --------------------
	# Returns an array of bugnote stats
	# $p_from - Starting date (yyyy-mm-dd) inclusive, if blank, then ignored.
	# $p_to - Ending date (yyyy-mm-dd) inclusive, if blank, then ignored.
	function bugnote_stats_get_project_array( $p_project_id, $p_from, $p_to, $p_cost ) {
		$c_project_id = db_prepare_int( $p_project_id );
		$c_to = db_prepare_date( $p_to );
		$c_from = db_prepare_date( $p_from );
		$c_cost = db_prepare_double( $p_cost );

		// MySQL
		$t_bug_table = config_get( 'mantis_bug_table' );
		$t_user_table = config_get( 'mantis_user_table' );
		$t_bugnote_table = config_get( 'mantis_bugnote_table' );

		if ( !is_blank( $c_from ) ) {
			$t_from_where = " AND bn.date_submitted >= '$c_from 00:00:00'";
		} else {
			$t_from_where = '';
		}

		if ( !is_blank( $c_to ) ) {
			$t_to_where = " AND bn.date_submitted <= '$c_to 23:59:59'";
		} else {
			$t_to_where = '';
		}

		if ( ALL_PROJECTS != $c_project_id ) {
			$t_project_where = " AND b.project_id = '$c_project_id' AND bn.bug_id = b.id ";
		} else {
			$t_project_where = '';
		}

		$t_results = array();

		$query = "SELECT username, summary, bn.bug_id, SUM(time_tracking) AS sum_time_tracking
			FROM $t_user_table u, $t_bugnote_table bn, $t_bug_table b
			WHERE u.id = bn.reporter_id AND bn.time_tracking != 0 AND bn.bug_id = b.id
			$t_project_where $t_from_where $t_to_where
			GROUP BY bn.bug_id, u.id, u.username, b.summary
			ORDER BY bn.bug_id";

		$result = db_query( $query );

		$t_cost_min = $c_cost / 60;

		while ( $row = db_fetch_array( $result ) ) {
			$t_total_cost = $t_cost_min * $row['sum_time_tracking'];
			$row['cost'] = $t_total_cost;
			$t_results[] = $row;
		}

		return $t_results;
	}
?>
