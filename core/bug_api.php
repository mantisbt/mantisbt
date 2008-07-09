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
	# $Id: bug_api.php,v 1.111.2.1 2007-10-13 22:35:13 giallu Exp $
	# --------------------------------------------------------

	$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;

	require_once( $t_core_dir . 'history_api.php' );
	require_once( $t_core_dir . 'email_api.php' );
	require_once( $t_core_dir . 'bugnote_api.php' );
	require_once( $t_core_dir . 'file_api.php' );
	require_once( $t_core_dir . 'string_api.php' );
	require_once( $t_core_dir . 'sponsorship_api.php' );
	require_once( $t_core_dir . 'twitter_api.php' );
	require_once( $t_core_dir . 'tag_api.php' );

	# MASC RELATIONSHIP
	require_once( $t_core_dir.'relationship_api.php' );
	# MASC RELATIONSHIP

	### Bug API ###

	#===================================
	# Bug Data Structure Definition
	#===================================
	class BugData {
		var $project_id = null;
		var $reporter_id = 0;
		var $handler_id = 0;
		var $duplicate_id = 0;
		var $priority = NORMAL;
		var $severity = MINOR;
		var $reproducibility = 10;
		var $status = NEW_;
		var $resolution = OPEN;
		var $projection = 10;
		var $category = '';
		var $date_submitted = '';
		var $last_updated = '';
		var $eta = 10;
		var $os = '';
		var $os_build = '';
		var $platform = '';
		var $version = '';
		var $fixed_in_version = '';
		var $target_version = '';
		var $build = '';
		var $view_state = VS_PUBLIC;
		var $summary = '';
		var $sponsorship_total = 0;
		var $sticky = 0;

		# omitted:
		# var $bug_text_id
		var $profile_id;

		# extended info
		var $description = '';
		var $steps_to_reproduce = '';
		var $additional_information = '';
		
		#internal helper objects
		var $_stats = null;
	}

	#===================================
	# Caching
	#===================================

	#########################################
	# SECURITY NOTE: cache globals are initialized here to prevent them
	#   being spoofed if register_globals is turned on

	$g_cache_bug = array();
	$g_cache_bug_text = array();

	# --------------------
	# Cache an object as a bug.
	function bug_cache_database_result( $p_bug_datebase_result, $p_stats = null ) {
		global $g_cache_bug;
		
		if ( isset( $g_cache_bug[ $p_bug_datebase_result['id'] ] ) ) {
			return $g_cache_bug[ $p_bug_datebase_result['id'] ];
		}	
		
		if( !is_int( $p_bug_datebase_result['date_submitted'] ) )
			$p_bug_datebase_result['date_submitted']	= db_unixtimestamp( $p_bug_datebase_result['date_submitted']['date_submitted'] );
		if( !is_int( $p_bug_datebase_result['last_updated'] ) )
			$p_bug_datebase_result['last_updated']	= db_unixtimestamp( $p_bug_datebase_result['last_updated'] );
		$g_cache_bug[ $p_bug_datebase_result['id'] ] = $p_bug_datebase_result;
		if( !is_null( $p_stats ) ) {
			$g_cache_bug[ $p_bug_datebase_result['id'] ]['_stats'] = $p_stats;
		}
	}

	# --------------------
	# Cache a bug row if necessary and return the cached copy
	#  If the second parameter is true (default), trigger an error
	#  if the bug can't be found.  If the second parameter is
	#  false, return false if the bug can't be found.
	function bug_cache_row( $p_bug_id, $p_trigger_errors=true ) {
		global $g_cache_bug;

		if ( isset( $g_cache_bug[$p_bug_id] ) ) {
			return $g_cache_bug[$p_bug_id];
		}

		$c_bug_id		= db_prepare_int( $p_bug_id );
		$t_bug_table	= config_get( 'mantis_bug_table' );

		$query = "SELECT *
				  FROM $t_bug_table
				  WHERE id='$c_bug_id'";
		$result = db_query( $query );

		if ( 0 == db_num_rows( $result ) ) {
			$g_cache_bug[$c_bug_id] = false;

			if ( $p_trigger_errors ) {
				error_parameters( $p_bug_id );
				trigger_error( ERROR_BUG_NOT_FOUND, ERROR );
			} else {
				return false;
			}
		}

		$row = db_fetch_array( $result );
		$row['date_submitted']	= db_unixtimestamp( $row['date_submitted'] );
		$row['last_updated']	= db_unixtimestamp( $row['last_updated'] );
		$g_cache_bug[$c_bug_id] = $row;

		return $row;
	}

	# --------------------
	# Inject a bug into the bug cache
	function bug_add_to_cache( $p_bug_row ) {
		global $g_cache_bug;

		if ( !is_array( $p_bug_row ) )
			return false;

		$c_bug_id = db_prepare_int( $p_bug_row['id'] );
		$g_cache_bug[ $c_bug_id ] = $p_bug_row;

		return true;
	}

	# --------------------
	# Clear the bug cache (or just the given id if specified)
	function bug_clear_cache( $p_bug_id = null ) {
		global $g_cache_bug;

		if ( null === $p_bug_id ) {
			$g_cache_bug = array();
		} else {
			$c_bug_id = db_prepare_int( $p_bug_id );
			unset( $g_cache_bug[$c_bug_id] );
		}

		return true;
	}

	# --------------------
	# Cache a bug text row if necessary and return the cached copy
	#  If the second parameter is true (default), trigger an error
	#  if the bug text can't be found.  If the second parameter is
	#  false, return false if the bug text can't be found.
	function bug_text_cache_row( $p_bug_id, $p_trigger_errors=true ) {
		global $g_cache_bug_text;

		$c_bug_id			= db_prepare_int( $p_bug_id );
		$t_bug_table		= config_get( 'mantis_bug_table' );
		$t_bug_text_table	= config_get( 'mantis_bug_text_table' );

		if ( isset ( $g_cache_bug_text[$c_bug_id] ) ) {
			return $g_cache_bug_text[$c_bug_id];
		}

		$query = "SELECT bt.*
				  FROM $t_bug_text_table bt, $t_bug_table b
				  WHERE b.id='$c_bug_id' AND
				  		b.bug_text_id = bt.id";
		$result = db_query( $query );

		if ( 0 == db_num_rows( $result ) ) {
			$g_cache_bug_text[$c_bug_id] = false;

			if ( $p_trigger_errors ) {
				error_parameters( $p_bug_id );
				trigger_error( ERROR_BUG_NOT_FOUND, ERROR );
			} else {
				return false;
			}
		}

		$row = db_fetch_array( $result );

		$g_cache_bug_text[$c_bug_id] = $row;

		return $row;
	}

	# --------------------
	# Clear the bug text cache (or just the given id if specified)
	function bug_text_clear_cache( $p_bug_id = null ) {
		global $g_cache_bug_text;

		if ( null === $p_bug_id ) {
			$g_cache_bug_text = array();
		} else {
			$c_bug_id = db_prepare_int( $p_bug_id );
			unset( $g_cache_bug_text[$c_bug_id] );
		}

		return true;
	}

	#===================================
	# Boolean queries and ensures
	#===================================

	# --------------------
	# check to see if bug exists by id
	# return true if it does, false otherwise
	function bug_exists( $p_bug_id ) {
		if ( false == bug_cache_row( $p_bug_id, false ) ) {
			return false;
		} else {
			return true;
		}
	}

	# --------------------
	# check to see if bug exists by id
	# if it doesn't exist then error
	#  otherwise let execution continue undisturbed
	function bug_ensure_exists( $p_bug_id ) {
		if ( !bug_exists( $p_bug_id ) ) {
			error_parameters( $p_bug_id );
			trigger_error( ERROR_BUG_NOT_FOUND, ERROR );
		}
	}

	# --------------------
	# check if the given user is the reporter of the bug
	# return true if the user is the reporter, false otherwise
	function bug_is_user_reporter( $p_bug_id, $p_user_id ) {
		if ( bug_get_field( $p_bug_id, 'reporter_id' ) == $p_user_id ) {
			return true;
		} else {
			return false;
		}
	}

	# --------------------
	# check if the given user is the handler of the bug
	# return true if the user is the handler, false otherwise
	function bug_is_user_handler( $p_bug_id, $p_user_id ) {
		if ( bug_get_field( $p_bug_id, 'handler_id' ) == $p_user_id ) {
			return true;
		} else {
			return false;
		}
	}

	# --------------------
	# Check if the bug is readonly and shouldn't be modified
	# For a bug to be readonly the status has to be >= bug_readonly_status_threshold and
	# current user access level < update_readonly_bug_threshold.
	function bug_is_readonly( $p_bug_id ) {
		$t_status = bug_get_field( $p_bug_id, 'status' );
		if ( $t_status < config_get( 'bug_readonly_status_threshold' ) ) {
			return false;
		}

		if ( access_has_bug_level( config_get( 'update_readonly_bug_threshold' ), $p_bug_id ) ) {
			return false;
		}

		return true;
	}

	# --------------------
	# Check if the bug is resolved
	function bug_is_resolved( $p_bug_id ) {
		$t_status = bug_get_field( $p_bug_id, 'status' );
		return ( $t_status >= config_get( 'bug_resolved_status_threshold' ) );
	}

	# --------------------
	# Validate workflow state to see if bug can be moved to requested state
	function bug_check_workflow( $p_bug_status, $p_wanted_status ) {
		$t_status_enum_workflow = config_get( 'status_enum_workflow' );

		if ( count( $t_status_enum_workflow ) < 1) {
			# workflow not defined, use default enum
			return true;
		} else if ( $p_bug_status == $p_wanted_status ) {
			# no change in state, allow the transition
			return true;
		} else {
			# workflow defined - find allowed states
			$t_allowed_states = $t_status_enum_workflow[$p_bug_status];
			$t_arr = explode_enum_string( $t_allowed_states );

			$t_enum_count = count( $t_arr );

			for ( $i = 0; $i < $t_enum_count; $i++ ) {
				# check if wanted status is allowed
				$t_elem  = explode_enum_arr( $t_arr[$i] );
				if ( $p_wanted_status == $t_elem[0] ) {
					return true;
				}
			} # end for
		}

		return false;
	}

	#===================================
	# Creation / Deletion / Updating
	#===================================

	# --------------------
	# Create a new bug and return the bug id
	#
	function bug_create( $p_bug_data ) {

		$c_summary				= db_prepare_string( $p_bug_data->summary );
		$c_description			= db_prepare_string( $p_bug_data->description );
		$c_project_id			= db_prepare_int( $p_bug_data->project_id );
		$c_reporter_id			= db_prepare_int( $p_bug_data->reporter_id );
		$c_handler_id			= db_prepare_int( $p_bug_data->handler_id );
		$c_priority				= db_prepare_int( $p_bug_data->priority );
		$c_severity				= db_prepare_int( $p_bug_data->severity );
		$c_reproducibility		= db_prepare_int( $p_bug_data->reproducibility );
		$c_category				= db_prepare_string( $p_bug_data->category );
		$c_os					= db_prepare_string( $p_bug_data->os );
		$c_os_build				= db_prepare_string( $p_bug_data->os_build );
		$c_platform				= db_prepare_string( $p_bug_data->platform );
		$c_version				= db_prepare_string( $p_bug_data->version );
		$c_build				= db_prepare_string( $p_bug_data->build );
		$c_profile_id			= db_prepare_int( $p_bug_data->profile_id );
		$c_view_state			= db_prepare_int( $p_bug_data->view_state );
		$c_steps_to_reproduce	= db_prepare_string( $p_bug_data->steps_to_reproduce );
		$c_additional_info		= db_prepare_string( $p_bug_data->additional_information );
		$c_sponsorship_total 	= 0;
		$c_sticky 				= 0;

		# Summary cannot be blank
		if ( is_blank( $c_summary ) ) {
			error_parameters( lang_get( 'summary' ) );
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		}

		# Description cannot be blank
		if ( is_blank( $c_description ) ) {
			error_parameters( lang_get( 'description' ) );
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		}

		# If category is not specified and not defaulted + project has categories defined, then error.
		if ( is_blank( $c_category ) && ( category_get_count( $c_project_id ) > 0 ) ) {
			error_parameters( lang_get( 'category' ) );
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		}

		# Only set target_version if user has access to do so
		if ( access_has_project_level( config_get( 'roadmap_update_threshold' ) ) ) {
			$c_target_version	= db_prepare_string( $p_bug_data->target_version );
		} else { 
			$c_target_version	= '';
		}

		$t_bug_table				= config_get( 'mantis_bug_table' );
		$t_bug_text_table			= config_get( 'mantis_bug_text_table' );
		$t_project_category_table	= config_get( 'mantis_project_category_table' );

		# Insert text information
		$query = "INSERT INTO $t_bug_text_table
				    ( description, steps_to_reproduce, additional_information )
				  VALUES
				    ( '$c_description', '$c_steps_to_reproduce',
				      '$c_additional_info' )";
		db_query( $query );

		# Get the id of the text information we just inserted
		# NOTE: this is guarranteed to be the correct one.
		# The value LAST_INSERT_ID is stored on a per connection basis.

		$t_text_id = db_insert_id($t_bug_text_table);

		# check to see if we want to assign this right off
		$t_status = config_get( 'bug_submit_status' );

		# if not assigned, check if it should auto-assigned.
		if ( 0 == $c_handler_id ) {
			# if a default user is associated with the category and we know at this point
			# that that the bug was not assigned to somebody, then assign it automatically.
			$query = "SELECT user_id
					  FROM $t_project_category_table
					  WHERE project_id='$c_project_id' AND category='$c_category'";
			$result = db_query( $query );

			if ( db_num_rows( $result ) > 0 ) {
				$c_handler_id = $p_handler_id = db_result( $result );
			}
		}

		# Check if bug was pre-assigned or auto-assigned.
		if ( ( $c_handler_id != 0 ) && ( ON == config_get( 'auto_set_status_to_assigned' ) ) ) {
			$t_status = config_get( 'bug_assigned_status' );
		}

		# Insert the rest of the data
		$t_resolution = OPEN;

		$query = "INSERT INTO $t_bug_table
				    ( project_id,
				      reporter_id, handler_id,
				      duplicate_id, priority,
				      severity, reproducibility,
				      status, resolution,
				      projection, category,
				      date_submitted, last_updated,
				      eta, bug_text_id,
				      os, os_build,
				      platform, version,
				      build,
				      profile_id, summary, view_state, sponsorship_total, sticky, fixed_in_version,
				      target_version 
				    )
				  VALUES
				    ( '$c_project_id',
				      '$c_reporter_id', '$c_handler_id',
				      '0', '$c_priority',
				      '$c_severity', '$c_reproducibility',
				      '$t_status', '$t_resolution',
				      10, '$c_category',
				      " . db_now() . "," . db_now() . ",
				      10, '$t_text_id',
				      '$c_os', '$c_os_build',
				      '$c_platform', '$c_version',
				      '$c_build',
				      '$c_profile_id', '$c_summary', '$c_view_state', '$c_sponsorship_total', '$c_sticky', '',
				      '$c_target_version'
				    )";
		db_query( $query );

		$t_bug_id = db_insert_id($t_bug_table);

		# log new bug
		history_log_event_special( $t_bug_id, NEW_BUG );

		# log changes, if any (compare happens in history_log_event_direct)
		history_log_event_direct( $t_bug_id, 'status', config_get( 'bug_submit_status' ), $t_status );
		history_log_event_direct( $t_bug_id, 'handler_id', 0, $c_handler_id );

		return $t_bug_id;
	}

	# --------------------
	# Copy a bug from one project to another. Also make copies of issue notes, attachments, history,
	# email notifications etc.
	# @@@ Not managed FTP file upload
	# MASC RELATIONSHIP
	function bug_copy( $p_bug_id, $p_target_project_id = null, $p_copy_custom_fields = false, $p_copy_relationships = false,
		$p_copy_history = false, $p_copy_attachments = false, $p_copy_bugnotes = false, $p_copy_monitoring_users = false ) {
		global $g_db;

		$t_mantis_custom_field_string_table	= config_get( 'mantis_custom_field_string_table' );
		$t_mantis_bug_file_table			= config_get( 'mantis_bug_file_table' );
		$t_mantis_bugnote_table				= config_get( 'mantis_bugnote_table' );
		$t_mantis_bugnote_text_table		= config_get( 'mantis_bugnote_text_table' );
		$t_mantis_bug_monitor_table			= config_get( 'mantis_bug_monitor_table' );
		$t_mantis_bug_history_table			= config_get( 'mantis_bug_history_table' );
		$t_mantis_db = $g_db;

		$t_bug_id = db_prepare_int( $p_bug_id );
		$t_target_project_id = db_prepare_int( $p_target_project_id );


		$t_bug_data = new BugData;
		$t_bug_data = bug_get( $t_bug_id, true );

		# retrieve the project id associated with the bug
		if ( ( $p_target_project_id == null ) || is_blank( $p_target_project_id ) ) {
			$t_target_project_id = $t_bug_data->project_id;
		}

		$t_bug_data->project_id = $t_target_project_id;

		$t_new_bug_id = bug_create( $t_bug_data );

		# MASC ATTENTION: IF THE SOURCE BUG HAS TO HANDLER THE bug_create FUNCTION CAN TRY TO AUTO-ASSIGN THE BUG
		# WE FORCE HERE TO DUPLICATE THE SAME HANDLER OF THE SOURCE BUG
		# @@@ VB: Shouldn't we check if the handler in the source project is also a handler in the destination project?
		bug_set_field( $t_new_bug_id, 'handler_id', $t_bug_data->handler_id );

		bug_set_field( $t_new_bug_id, 'duplicate_id', $t_bug_data->duplicate_id );
		bug_set_field( $t_new_bug_id, 'status', $t_bug_data->status );
		bug_set_field( $t_new_bug_id, 'resolution', $t_bug_data->resolution );
		bug_set_field( $t_new_bug_id, 'projection', $t_bug_data->projection );
		bug_set_field( $t_new_bug_id, 'date_submitted', $t_mantis_db->DBTimeStamp( $t_bug_data->date_submitted ), false );
		bug_set_field( $t_new_bug_id, 'last_updated', $t_mantis_db->DBTimeStamp( $t_bug_data->last_updated ), false );
		bug_set_field( $t_new_bug_id, 'eta', $t_bug_data->eta );
		bug_set_field( $t_new_bug_id, 'fixed_in_version', $t_bug_data->fixed_in_version );
		bug_set_field( $t_new_bug_id, 'target_version', $t_bug_data->target_version );
		bug_set_field( $t_new_bug_id, 'sponsorship_total', 0 );
		bug_set_field( $t_new_bug_id, 'sticky', 0 );

		# COPY CUSTOM FIELDS
		if ( $p_copy_custom_fields ) {
			$query = "SELECT field_id, bug_id, value
					   FROM $t_mantis_custom_field_string_table
					   WHERE bug_id = '$t_bug_id';";
			$result = db_query( $query );
			$t_count = db_num_rows( $result );

			for ( $i = 0 ; $i < $t_count ; $i++ ) {
				$t_bug_custom = db_fetch_array( $result );

				$c_field_id = db_prepare_int( $t_bug_custom['field_id'] );
				$c_new_bug_id = db_prepare_int( $t_new_bug_id );
				$c_value = db_prepare_string( $t_bug_custom['value'] );

				$query = "INSERT INTO $t_mantis_custom_field_string_table
						   ( field_id, bug_id, value )
						   VALUES ('$c_field_id', '$c_new_bug_id', '$c_value')";
				db_query( $query );
			}
		}

		# COPY RELATIONSHIPS
		if ( $p_copy_relationships ) {
			if ( ON == config_get( 'enable_relationship' ) ) {
				relationship_copy_all( $t_bug_id,$t_new_bug_id );
			}
		}

		# Copy bugnotes
		if ( $p_copy_bugnotes ) {
			$query = "SELECT *
					  FROM $t_mantis_bugnote_table
					  WHERE bug_id = '$t_bug_id';";
			$result = db_query( $query );
			$t_count = db_num_rows( $result );

			for ( $i = 0; $i < $t_count; $i++ ) {
				$t_bug_note = db_fetch_array( $result );
				$t_bugnote_text_id = $t_bug_note['bugnote_text_id'];

				$query2 = "SELECT *
						   FROM $t_mantis_bugnote_text_table
						   WHERE id = '$t_bugnote_text_id';";
				$result2 = db_query( $query2 );
				$t_count2 = db_num_rows( $result2 );

				$t_bugnote_text_insert_id = -1;
				if ( $t_count2 > 0 ) {
					$t_bugnote_text = db_fetch_array( $result2 );
					$t_bugnote_text['note'] = db_prepare_string( $t_bugnote_text['note'] );

					$query2 = "INSERT INTO $t_mantis_bugnote_text_table
							   ( note )
							   VALUES ( '" . $t_bugnote_text['note'] . "' );";
					db_query( $query2 );
					$t_bugnote_text_insert_id = db_insert_id( $t_mantis_bugnote_text_table );
				}

				$query2 = "INSERT INTO $t_mantis_bugnote_table
						   ( bug_id, reporter_id, bugnote_text_id, view_state, date_submitted, last_modified )
						   VALUES ( '$t_new_bug_id',
						   			'" . $t_bug_note['reporter_id'] . "',
						   			'$t_bugnote_text_insert_id',
						   			'" . $t_bug_note['view_state'] . "',
						   			'" . $t_bug_note['date_submitted'] . "',
						   			'" . $t_bug_note['last_modified'] . "' );";
				db_query( $query2 );
			}
		}

		# Copy attachments
		if ( $p_copy_attachments ) {
			$query = "SELECT *
					  FROM $t_mantis_bug_file_table
					  WHERE bug_id = '$t_bug_id';";
			$result = db_query( $query );
			$t_count = db_num_rows( $result );

			$t_bug_file = array();
			for ( $i = 0; $i < $t_count; $i++ ) {
				$t_bug_file = db_fetch_array( $result );

				# prepare the new diskfile name and then copy the file
				$t_file_path = dirname( $t_bug_file['folder'] );
				$t_new_diskfile_name = $t_file_path . file_generate_unique_name( 'bug-' . $p_file_name, $t_file_path );
				$t_new_file_name = file_get_display_name( $t_bug_file['filename'] );
				if ( ( config_get( 'file_upload_method' ) == DISK ) ) {
					copy( $t_bug_file['diskfile'], $t_new_diskfile_name );
					chmod( $t_new_diskfile_name, config_get( 'attachments_file_permissions' ) );
				}

				$query = "INSERT INTO $t_mantis_bug_file_table
						( bug_id, title, description, diskfile, filename, folder, filesize, file_type, date_added, content )
						VALUES ( '$t_new_bug_id',
								 '" . db_prepare_string( $t_bug_file['title'] ) . "',
								 '" . db_prepare_string( $t_bug_file['description'] ) . "',
								 '" . db_prepare_string( $t_new_diskfile_name ) . "',
								 '" . db_prepare_string( $t_new_file_name ) . "',
								 '" . db_prepare_string( $t_bug_file['folder'] ) . "',
								 '" . db_prepare_int( $t_bug_file['filesize'] ) . "',
								 '" . db_prepare_string( $t_bug_file['file_type'] ) . "',
								 '" . db_prepare_string( $t_bug_file['date_added'] ) . "',
								 '" . db_prepare_string( $t_bug_file['content'] ) . "');";
				db_query( $query );
			}
		}

		# Copy users monitoring bug
		if ( $p_copy_monitoring_users ) {
			$query = "SELECT *
					  FROM $t_mantis_bug_monitor_table
					  WHERE bug_id = '$t_bug_id';";
			$result = db_query( $query );
			$t_count = db_num_rows( $result );

			for ( $i = 0; $i < $t_count; $i++ ) {
				$t_bug_monitor = db_fetch_array( $result );
				$query = "INSERT INTO $t_mantis_bug_monitor_table
						 ( user_id, bug_id )
						 VALUES ( '" . $t_bug_monitor['user_id'] . "', '$t_new_bug_id' );";
				db_query( $query );
			}
		}

		# COPY HISTORY
		history_delete( $t_new_bug_id );	# should history only be deleted inside the if statement below?
		if ( $p_copy_history ) {
			$query = "SELECT *
					  FROM $t_mantis_bug_history_table
					  WHERE bug_id = '$t_bug_id';";
			$result = db_query( $query );
			$t_count = db_num_rows( $result );

			for ( $i = 0; $i < $t_count; $i++ ) {
				$t_bug_history = db_fetch_array( $result );
				$query = "INSERT INTO $t_mantis_bug_history_table
						  ( user_id, bug_id, date_modified, field_name, old_value, new_value, type )
						  VALUES ( '" . db_prepare_int( $t_bug_history['user_id'] ) . "',
						  		   '$t_new_bug_id',
						  		   '" . db_prepare_string( $t_bug_history['date_modified'] ) . "',
						  		   '" . db_prepare_string( $t_bug_history['field_name'] ) . "',
						  		   '" . db_prepare_string( $t_bug_history['old_value'] ) . "',
						  		   '" . db_prepare_string( $t_bug_history['new_value'] ) . "',
						  		   '" . db_prepare_int( $t_bug_history['type'] ) . "' );";
				db_query( $query );
			}
		}

		return $t_new_bug_id;
	}

	# --------------------
	# allows bug deletion :
	# delete the bug, bugtext, bugnote, and bugtexts selected
	# used in bug_delete.php & mass treatments
	function bug_delete( $p_bug_id ) {
		$c_bug_id			= db_prepare_int( $p_bug_id );
		$t_bug_table		= config_get( 'mantis_bug_table' );
		$t_bug_text_table	= config_get( 'mantis_bug_text_table' );

		# call pre-deletion custom function
		helper_call_custom_function( 'issue_delete_validate', array( $p_bug_id ) );

		# log deletion of bug
		history_log_event_special( $p_bug_id, BUG_DELETED, bug_format_id( $p_bug_id ) );

		email_bug_deleted( $p_bug_id );

		# call post-deletion custom function.  We call this here to allow the custom function to access the details of the bug before 
		# they are deleted from the database given it's id.  The other option would be to move this to the end of the function and
		# provide it with bug data rather than an id, but this will break backward compatibility.
		helper_call_custom_function( 'issue_delete_notify', array( $p_bug_id ) );

		# Unmonitor bug for all users
		bug_unmonitor( $p_bug_id, null );

		# Delete custom fields
		custom_field_delete_all_values( $p_bug_id );

		# Delete bugnotes
		bugnote_delete_all( $p_bug_id );

		# Delete all sponsorships
		sponsorship_delete( sponsorship_get_all_ids( $p_bug_id ) );

		# MASC RELATIONSHIP
		# we delete relationships even if the feature is currently off.
		relationship_delete_all( $p_bug_id );
		# MASC RELATIONSHIP

		# Delete files
		file_delete_attachments( $p_bug_id );

		# Detach tags
		tag_bug_detach_all( $p_bug_id, false );
		
		# Delete the bug history
		history_delete( $p_bug_id );

		# Delete the bugnote text
		$t_bug_text_id = bug_get_field( $p_bug_id, 'bug_text_id' );

		$query = "DELETE FROM $t_bug_text_table
				  WHERE id='$t_bug_text_id'";
		db_query( $query );

		# Delete the bug entry
		$query = "DELETE FROM $t_bug_table
				  WHERE id='$c_bug_id'";
		db_query( $query );

		bug_clear_cache( $p_bug_id );
		bug_text_clear_cache( $p_bug_id );

		# db_query() errors on failure so:
		return true;
	}

	# --------------------
	# Delete all bugs associated with a project
	function bug_delete_all( $p_project_id ) {
		$c_project_id = db_prepare_int( $p_project_id );

		$t_bug_table = config_get( 'mantis_bug_table' );

		$query = "SELECT id
				  FROM $t_bug_table
				  WHERE project_id='$c_project_id'";
		$result = db_query( $query );

		$bug_count = db_num_rows( $result );

		for ( $i=0 ; $i < $bug_count ; $i++ ) {
			$row = db_fetch_array( $result );

			bug_delete( $row['id'] );
		}

		# @@@ should we check the return value of each bug_delete() and
		#  return false if any of them return false? Presumable bug_delete()
		#  will eventually trigger an error on failure so it won't matter...

		return true;
	}

	# --------------------
	# Update a bug from the given data structure
	#  If the third parameter is true, also update the longer strings table
	function bug_update( $p_bug_id, $p_bug_data, $p_update_extended = false, $p_bypass_mail = false ) {
		$c_bug_id		= db_prepare_int( $p_bug_id );
		$c_bug_data		= bug_prepare_db( $p_bug_data );

		# Summary cannot be blank
		if ( is_blank( $c_bug_data->summary ) ) {
			error_parameters( lang_get( 'summary' ) );
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		}

		if ( $p_update_extended ) {
			# Description field cannot be empty
			if ( is_blank( $c_bug_data->description ) ) {
				error_parameters( lang_get( 'description' ) );
				trigger_error( ERROR_EMPTY_FIELD, ERROR );
			}
		}

		if( !is_blank( $p_bug_data->duplicate_id ) && ( $p_bug_data->duplicate_id != 0 ) && ( $p_bug_id == $p_bug_data->duplicate_id ) ) {
			trigger_error( ERROR_BUG_DUPLICATE_SELF, ERROR );  # never returns
	    }

		$t_old_data = bug_get( $p_bug_id, true );

		$t_bug_table = config_get( 'mantis_bug_table' );

		# Update all fields
		# Ignore date_submitted and last_updated since they are pulled out
		#  as unix timestamps which could confuse the history log and they
		#  shouldn't get updated like this anyway.  If you really need to change
		#  them use bug_set_field()
		$query = "UPDATE $t_bug_table
				SET project_id='$c_bug_data->project_id',
					reporter_id='$c_bug_data->reporter_id',
					handler_id='$c_bug_data->handler_id',
					duplicate_id='$c_bug_data->duplicate_id',
					priority='$c_bug_data->priority',
					severity='$c_bug_data->severity',
					reproducibility='$c_bug_data->reproducibility',
					status='$c_bug_data->status',
					resolution='$c_bug_data->resolution',
					projection='$c_bug_data->projection',
					category='$c_bug_data->category',
					eta='$c_bug_data->eta',
					os='$c_bug_data->os',
					os_build='$c_bug_data->os_build',
					platform='$c_bug_data->platform',
					version='$c_bug_data->version',
					build='$c_bug_data->build',
					fixed_in_version='$c_bug_data->fixed_in_version',";

		$t_roadmap_updated = false;
		if ( access_has_project_level( config_get( 'roadmap_update_threshold' ) ) ) {
			$query .= "
					target_version='$c_bug_data->target_version',";
			$t_roadmap_updated = true;
		}

		$query .= "
					view_state='$c_bug_data->view_state',
					summary='$c_bug_data->summary',
					sponsorship_total='$c_bug_data->sponsorship_total',
					sticky='$c_bug_data->sticky'
				WHERE id='$c_bug_id'";
		db_query( $query );

		bug_clear_cache( $p_bug_id );

		# log changes
		history_log_event_direct( $p_bug_id, 'project_id', $t_old_data->project_id, $p_bug_data->project_id );
		history_log_event_direct( $p_bug_id, 'reporter_id', $t_old_data->reporter_id, $p_bug_data->reporter_id );
		history_log_event_direct( $p_bug_id, 'handler_id', $t_old_data->handler_id, $p_bug_data->handler_id );
		history_log_event_direct( $p_bug_id, 'duplicate_id', $t_old_data->duplicate_id, $p_bug_data->duplicate_id );
		history_log_event_direct( $p_bug_id, 'priority', $t_old_data->priority, $p_bug_data->priority );
		history_log_event_direct( $p_bug_id, 'severity', $t_old_data->severity, $p_bug_data->severity );
		history_log_event_direct( $p_bug_id, 'reproducibility', $t_old_data->reproducibility, $p_bug_data->reproducibility );
		history_log_event_direct( $p_bug_id, 'status', $t_old_data->status, $p_bug_data->status );
		history_log_event_direct( $p_bug_id, 'resolution', $t_old_data->resolution, $p_bug_data->resolution );
		history_log_event_direct( $p_bug_id, 'projection', $t_old_data->projection, $p_bug_data->projection );
		history_log_event_direct( $p_bug_id, 'category', $t_old_data->category, $p_bug_data->category );
		history_log_event_direct( $p_bug_id, 'eta',	$t_old_data->eta, $p_bug_data->eta );
		history_log_event_direct( $p_bug_id, 'os', $t_old_data->os, $p_bug_data->os );
		history_log_event_direct( $p_bug_id, 'os_build', $t_old_data->os_build, $p_bug_data->os_build );
		history_log_event_direct( $p_bug_id, 'platform', $t_old_data->platform, $p_bug_data->platform );
		history_log_event_direct( $p_bug_id, 'version', $t_old_data->version, $p_bug_data->version );
		history_log_event_direct( $p_bug_id, 'build', $t_old_data->build, $p_bug_data->build );
		history_log_event_direct( $p_bug_id, 'fixed_in_version', $t_old_data->fixed_in_version, $p_bug_data->fixed_in_version );
		if ( $t_roadmap_updated ) {
			history_log_event_direct( $p_bug_id, 'target_version', $t_old_data->target_version, $p_bug_data->target_version );
		}
		history_log_event_direct( $p_bug_id, 'view_state', $t_old_data->view_state, $p_bug_data->view_state );
		history_log_event_direct( $p_bug_id, 'summary', $t_old_data->summary, $p_bug_data->summary );
		history_log_event_direct( $p_bug_id, 'sponsorship_total', $t_old_data->sponsorship_total, $p_bug_data->sponsorship_total );
		history_log_event_direct( $p_bug_id, 'sticky', $t_old_data->sticky, $p_bug_data->sticky );

		# Update extended info if requested
		if ( $p_update_extended ) {
			$t_bug_text_table = config_get( 'mantis_bug_text_table' );

			$t_bug_text_id = bug_get_field( $p_bug_id, 'bug_text_id' );

			$query = "UPDATE $t_bug_text_table
						SET description='$c_bug_data->description',
							steps_to_reproduce='$c_bug_data->steps_to_reproduce',
							additional_information='$c_bug_data->additional_information'
						WHERE id='$t_bug_text_id'";
			db_query( $query );

			bug_text_clear_cache( $p_bug_id );

			if ( $t_old_data->description != $p_bug_data->description ) {
				history_log_event_special( $p_bug_id, DESCRIPTION_UPDATED );
			}
			if ( $t_old_data->steps_to_reproduce != $p_bug_data->steps_to_reproduce ) {
				history_log_event_special( $p_bug_id, STEP_TO_REPRODUCE_UPDATED );
			}
			if ( $t_old_data->additional_information != $p_bug_data->additional_information ) {
				history_log_event_special( $p_bug_id, ADDITIONAL_INFO_UPDATED );
			}
		}

		# Update the last update date
		bug_update_date( $p_bug_id );

		if ( false == $p_bypass_mail ) {		# allow bypass if user is sending mail separately
			$t_action_prefix = 'email_notification_title_for_action_bug_';
			$t_status_prefix = 'email_notification_title_for_status_bug_';

			# status changed
			if ( $t_old_data->status != $p_bug_data->status ) {
				$t_status = get_enum_to_string( config_get( 'status_enum_string' ), $p_bug_data->status );
				$t_status = str_replace( ' ', '_', $t_status );
				email_generic( $p_bug_id, $t_status, $t_status_prefix . $t_status );
				return true;
			}

			# bug assigned
			if ( $t_old_data->handler_id != $p_bug_data->handler_id ) {
				email_generic( $p_bug_id, 'owner', $t_action_prefix . 'assigned' );
				return true;
			}

			# @@@ handle priority change if it requires special handling

			# generic update notification
			email_generic( $p_bug_id, 'updated', $t_action_prefix . 'updated' );
		}

		return true;
	}

	#===================================
	# Data Access
	#===================================

	# --------------------
	# Returns the extended record of the specified bug, this includes
	# the bug text fields
	# @@@ include reporter name and handler name, the problem is that
	#      handler can be 0, in this case no corresponding name will be
	#      found.  Use equivalent of (+) in Oracle.
	function bug_get_extended_row( $p_bug_id ) {
		$t_base = bug_cache_row( $p_bug_id );
		$t_text = bug_text_cache_row( $p_bug_id );

		# merge $t_text first so that the 'id' key has the bug id not the bug text id
		return array_merge( $t_text, $t_base );
	}

	# --------------------
	# Returns the record of the specified bug
	function bug_get_row( $p_bug_id ) {
		return bug_cache_row( $p_bug_id );
	}

	# --------------------
	# Returns an object representing the specified bug
	function bug_get( $p_bug_id, $p_get_extended = false ) {
		if ( $p_get_extended ) {
			$row = bug_get_extended_row( $p_bug_id );
		} else {
			$row = bug_get_row( $p_bug_id );
		}

		$t_bug_data = new BugData;
		$t_row_keys = array_keys( $row );
		$t_vars = get_object_vars( $t_bug_data );

		# Check each variable in the class
		foreach ( $t_vars as $var => $val ) {
			# If we got a field from the DB with the same name
			if ( in_array( $var, $t_row_keys, true ) ) {
				# Store that value in the object
				$t_bug_data->$var = $row[$var];
			}
		}

		return $t_bug_data;
	}

	# --------------------
	# return the specified field of the given bug
	#  if the field does not exist, display a warning and return ''
	function bug_get_field( $p_bug_id, $p_field_name ) {
		$row = bug_get_row( $p_bug_id );

		if ( isset( $row[$p_field_name] ) ) {
			return $row[$p_field_name];
		} else {
			error_parameters( $p_field_name );
			trigger_error( ERROR_DB_FIELD_NOT_FOUND, WARNING );
			return '';
		}
	}

	# --------------------
	# return the specified text field of the given bug
	#  if the field does not exist, display a warning and return ''
	function bug_get_text_field( $p_bug_id, $p_field_name ) {
		$row = bug_text_cache_row( $p_bug_id );

		if ( isset( $row[$p_field_name] ) ) {
			return $row[$p_field_name];
		} else {
			error_parameters( $p_field_name );
			trigger_error( ERROR_DB_FIELD_NOT_FOUND, WARNING );
			return '';
		}
	}

	# --------------------
	# return the bug summary
	#  this is a wrapper for the custom function
	function bug_format_summary( $p_bug_id, $p_context ) {
		return 	helper_call_custom_function( 'format_issue_summary', array( $p_bug_id , $p_context ) );
	}


	# --------------------
	# Returns the number of bugnotes for the given bug_id
	function bug_get_bugnote_count( $p_bug_id ) {
		$c_bug_id = db_prepare_int( $p_bug_id );

		$t_project_id = bug_get_field( $p_bug_id, 'project_id' );

		if ( !access_has_project_level( config_get( 'private_bugnote_threshold' ), $t_project_id ) ) {
			$t_restriction = 'AND view_state=' . VS_PUBLIC;
		} else {
			$t_restriction = '';
		}

		$t_bugnote_table = config_get( 'mantis_bugnote_table' );
		$query = "SELECT COUNT(*)
				  FROM $t_bugnote_table
				  WHERE bug_id ='$c_bug_id' $t_restriction";
		$result = db_query( $query );

		return db_result( $result );
	}

	# --------------------
	# return the timestamp for the most recent time at which a bugnote
	#  associated wiht the bug was modified
	function bug_get_newest_bugnote_timestamp( $p_bug_id ) {
		$c_bug_id			= db_prepare_int( $p_bug_id );
		$t_bugnote_table	= config_get( 'mantis_bugnote_table' );

		$query = "SELECT last_modified
				  FROM $t_bugnote_table
				  WHERE bug_id='$c_bug_id'
				  ORDER BY last_modified DESC";
		$result = db_query( $query, 1 );
		$row = db_result( $result );

		if ( false === $row ) {
			return false;
		} else {
			return db_unixtimestamp( $row );
		}
	}

	# --------------------
	# return the timestamp for the most recent time at which a bugnote
	#  associated with the bug was modified and the total bugnote
	#  count in one db query
	function bug_get_bugnote_stats( $p_bug_id ) {
		global $g_cache_bug;
		$c_bug_id			= db_prepare_int( $p_bug_id );

		if( !is_null( $g_cache_bug[ $c_bug_id ]['_stats'] ) ) {
			if( $g_cache_bug[ $c_bug_id ]['_stats'] === false ) { 
				return false;			
			} else {
				$t_stats['last_modified'] = db_unixtimestamp( $g_cache_bug[ $c_bug_id ]['_stats']['last_modified'] );
				$t_stats['count'] = $g_cache_bug[ $c_bug_id ]['_stats']['count'];
			}
			return $t_stats;
		}

		$t_bugnote_table	= config_get( 'mantis_bugnote_table' );

		$query = "SELECT last_modified
				  FROM $t_bugnote_table
				  WHERE bug_id='$c_bug_id'
				  ORDER BY last_modified DESC";
		$result = db_query( $query );
		$row = db_fetch_array( $result );

		if ( false === $row )
			return false;

		$t_stats['last_modified'] = db_unixtimestamp( $row['last_modified'] );
		$t_stats['count'] = db_num_rows( $result );

		return $t_stats;
	}

	# --------------------
	# Get array of attachments associated with the specified bug id.  The array will be
	# sorted in terms of date added (ASC).  The array will include the following fields:
	# id, title, diskfile, filename, filesize, file_type, date_added.
	function bug_get_attachments( $p_bug_id ) {
		if ( !file_can_view_bug_attachments( $p_bug_id ) ) {
	        return;
		}

		$c_bug_id = db_prepare_int( $p_bug_id );

		$t_bug_file_table = config_get( 'mantis_bug_file_table' );

		$query = "SELECT id, title, diskfile, filename, filesize, file_type, date_added
		                FROM $t_bug_file_table
		                WHERE bug_id='$c_bug_id'
		                ORDER BY date_added";
		$db_result = db_query( $query );
		$num_notes = db_num_rows( $db_result );

		$t_result = array();

		for ( $i = 0; $i < $num_notes; $i++ ) {
			$t_result[] = db_fetch_array( $db_result );
		}

		return $t_result;
	}

	#===================================
	# Data Modification
	#===================================

	# --------------------
	# set the value of a bug field
	function bug_set_field( $p_bug_id, $p_field_name, $p_status, $p_prepare = true ) {
		$c_bug_id			= db_prepare_int( $p_bug_id );
		$c_field_name		= db_prepare_string( $p_field_name );
		if( $p_prepare ) {
			$c_status		= '\'' . db_prepare_string( $p_status ) . '\''; #generic, unknown type
		} else {
			$c_status		=  $p_status; #generic, unknown type
		}

		$h_status = bug_get_field( $p_bug_id, $p_field_name );

		# return if status is already set
		if ( $c_status == $h_status ) {
			return true;
		}

		$t_bug_table = config_get( 'mantis_bug_table' );

		# Update fields
		$query = "UPDATE $t_bug_table
				  SET $c_field_name=$c_status
				  WHERE id='$c_bug_id'";
		db_query( $query );

		# updated the last_updated date
		bug_update_date( $p_bug_id );

		# log changes
		history_log_event_direct( $p_bug_id, $p_field_name, $h_status, $p_status );

		bug_clear_cache( $p_bug_id );

		return true;
	}

	# --------------------
	# assign the bug to the given user
	function bug_assign( $p_bug_id, $p_user_id, $p_bugnote_text='', $p_bugnote_private = false ) {
		$c_bug_id	= db_prepare_int( $p_bug_id );
		$c_user_id	= db_prepare_int( $p_user_id );

		if ( ( $c_user_id != NO_USER ) && !access_has_bug_level( config_get( 'handle_bug_threshold' ), $p_bug_id, $p_user_id ) ) {
		    trigger_error( ERROR_USER_DOES_NOT_HAVE_REQ_ACCESS );
		}

		# extract current information into history variables
		$h_status		= bug_get_field( $p_bug_id, 'status' );
		$h_handler_id	= bug_get_field( $p_bug_id, 'handler_id' );

		if ( ( ON == config_get( 'auto_set_status_to_assigned' ) ) &&
			 ( NO_USER != $p_user_id ) ) {
			$t_ass_val = config_get( 'bug_assigned_status' );
		} else {
			$t_ass_val = $h_status;
		}

		$t_bug_table = config_get( 'mantis_bug_table' );

		if ( ( $t_ass_val != $h_status ) || ( $p_user_id != $h_handler_id ) ) {

			# get user id
			$query = "UPDATE $t_bug_table
					  SET handler_id='$c_user_id', status='$t_ass_val'
					  WHERE id='$c_bug_id'";
			db_query( $query );

			# log changes
			history_log_event_direct( $c_bug_id, 'status', $h_status, $t_ass_val );
			history_log_event_direct( $c_bug_id, 'handler_id', $h_handler_id, $p_user_id );

			# Add bugnote if supplied ignore false return
			bugnote_add( $p_bug_id, $p_bugnote_text, 0, $p_bugnote_private, 0, '', NULL, FALSE );

			# updated the last_updated date
			bug_update_date( $p_bug_id );

			bug_clear_cache( $p_bug_id );

			# send assigned to email
			email_assign( $p_bug_id );
		}

		return true;
	}

	# --------------------
	# close the given bug
	function bug_close( $p_bug_id, $p_bugnote_text = '', $p_bugnote_private = false, $p_time_tracking = '0:00' ) {
		$p_bugnote_text = trim( $p_bugnote_text );

		# Add bugnote if supplied ignore a false return
		# Moved bugnote_add before bug_set_field calls in case time_tracking_no_note is off.
		# Error condition stopped execution but status had already been changed
		bugnote_add( $p_bug_id, $p_bugnote_text, $p_time_tracking, $p_bugnote_private, 0, '', NULL, FALSE );

		bug_set_field( $p_bug_id, 'status', CLOSED );

		email_close( $p_bug_id );

		# MASC RELATIONSHIP
		if ( ON == config_get( 'enable_relationship' ) ) {
			email_relationship_child_closed( $p_bug_id );
		}
		# MASC RELATIONSHIP

		return true;
	}

	# --------------------
	# resolve the given bug
	function bug_resolve( $p_bug_id, $p_resolution, $p_fixed_in_version = '', $p_bugnote_text = '', $p_duplicate_id = null, $p_handler_id = null, $p_bugnote_private = false, $p_time_tracking = '0:00' ) {
		$p_bugnote_text = trim( $p_bugnote_text );

		# Add bugnote if supplied
		# Moved bugnote_add before bug_set_field calls in case time_tracking_no_note is off.
		# Error condition stopped execution but status had already been changed
		bugnote_add( $p_bug_id, $p_bugnote_text, $p_time_tracking, $p_bugnote_private, 0, '', NULL, FALSE );

		$t_duplicate = !is_blank( $p_duplicate_id ) && ( $p_duplicate_id != 0 );
		if ( $t_duplicate ) {
			if ( $p_bug_id == $p_duplicate_id ) {
			    trigger_error( ERROR_BUG_DUPLICATE_SELF, ERROR );  # never returns
			}

			# the related bug exists...
			bug_ensure_exists( $p_duplicate_id );

			if ( ON == config_get( 'enable_relationship' ) ) {
				# check if there is other relationship between the bugs...
				$t_id_relationship = relationship_same_type_exists( $p_bug_id, $p_duplicate_id, BUG_DUPLICATE );

				if ( $t_id_relationship == -1 ) {
					# the relationship type is already set. Nothing to do
				}
				else if ( $t_id_relationship > 0 ) {
					# Update the relationship
					relationship_update( $t_id_relationship, $p_bug_id, $p_duplicate_id, BUG_DUPLICATE );

					# Add log line to the history (both bugs)
					history_log_event_special( $p_bug_id, BUG_REPLACE_RELATIONSHIP, BUG_DUPLICATE, $p_duplicate_id );
					history_log_event_special( $p_duplicate_id, BUG_REPLACE_RELATIONSHIP, BUG_HAS_DUPLICATE, $p_bug_id );
				}
				else {
					# Add the new relationship
					relationship_add( $p_bug_id, $p_duplicate_id, BUG_DUPLICATE );

					# Add log line to the history (both bugs)
					history_log_event_special( $p_bug_id, BUG_ADD_RELATIONSHIP, BUG_DUPLICATE, $p_duplicate_id );
					history_log_event_special( $p_duplicate_id, BUG_ADD_RELATIONSHIP, BUG_HAS_DUPLICATE, $p_bug_id );
				}
			}

			bug_set_field( $p_bug_id, 'duplicate_id', (int)$p_duplicate_id );
		}
		
		$c_resolution = db_prepare_int( $p_resolution );

		bug_set_field( $p_bug_id, 'status', config_get( 'bug_resolved_status_threshold' ) );
		bug_set_field( $p_bug_id, 'fixed_in_version', $p_fixed_in_version );
		bug_set_field( $p_bug_id, 'resolution', $c_resolution );

		# only set handler if specified explicitly or if bug was not assigned to a handler
		if ( null == $p_handler_id ) {
			if ( bug_get_field( $p_bug_id, 'handler_id' ) == 0 ) {
				$p_handler_id = auth_get_current_user_id();
				bug_set_field( $p_bug_id, 'handler_id', $p_handler_id );
			}
		} else {
			bug_set_field( $p_bug_id, 'handler_id', $p_handler_id );
		}

		email_resolved( $p_bug_id );

		if ( $c_resolution == FIXED ) {
			twitter_issue_resolved( $p_bug_id );
		}

		# MASC RELATIONSHIP
		if ( ON == config_get( 'enable_relationship' ) ) {
			email_relationship_child_resolved( $p_bug_id );
		}
		# MASC RELATIONSHIP

		return true;
	}

	# --------------------
	# reopen the given bug
	function bug_reopen( $p_bug_id, $p_bugnote_text='', $p_time_tracking = '0:00', $p_bugnote_private = false ) {
		$p_bugnote_text = trim( $p_bugnote_text );

		# Add bugnote if supplied
		# Moved bugnote_add before bug_set_field calls in case time_tracking_no_note is off.
		# Error condition stopped execution but status had already been changed
		bugnote_add( $p_bug_id, $p_bugnote_text, $p_time_tracking, $p_bugnote_private, 0, '', NULL, FALSE );

		bug_set_field( $p_bug_id, 'status', config_get( 'bug_reopen_status' ) );
		bug_set_field( $p_bug_id, 'resolution', config_get( 'bug_reopen_resolution' ) );

		email_reopen( $p_bug_id );

		return true;
	}

	# --------------------
	# updates the last_updated field
	function bug_update_date( $p_bug_id ) {
		$c_bug_id = db_prepare_int( $p_bug_id );

		$t_bug_table = config_get( 'mantis_bug_table' );

		$query = "UPDATE $t_bug_table
				  SET last_updated= " . db_now() . "
				  WHERE id='$c_bug_id'";
		db_query( $query );

		bug_clear_cache( $p_bug_id );

		return true;
	}

	# --------------------
	# enable monitoring of this bug for the user
	function bug_monitor( $p_bug_id, $p_user_id ) {
		$c_bug_id	= db_prepare_int( $p_bug_id );
		$c_user_id	= db_prepare_int( $p_user_id );

		# Make sure we aren't already monitoring this bug
		if ( user_is_monitoring_bug( $p_user_id, $p_bug_id ) ) {
			return true;
		}

		$t_bug_monitor_table = config_get( 'mantis_bug_monitor_table' );

		# Insert monitoring record
		$query ="INSERT ".
				"INTO $t_bug_monitor_table ".
				"( user_id, bug_id ) ".
				"VALUES ".
				"( '$c_user_id', '$c_bug_id' )";
		db_query( $query );

		# log new monitoring action
		history_log_event_special( $p_bug_id, BUG_MONITOR, $c_user_id );

		return true;
	}

	# --------------------
	# disable monitoring of this bug for the user
	# if $p_user_id = null, then bug is unmonitored for all users.
	function bug_unmonitor( $p_bug_id, $p_user_id ) {
		$c_bug_id	= db_prepare_int( $p_bug_id );
		$c_user_id	= db_prepare_int( $p_user_id );

		$t_bug_monitor_table = config_get( 'mantis_bug_monitor_table' );

		# Delete monitoring record
		$query ="DELETE ".
				"FROM $t_bug_monitor_table ".
				"WHERE bug_id = '$c_bug_id'";

		if ( $p_user_id !== null ) {
			$query .= " AND user_id = '$c_user_id'";
		}

		db_query( $query );

		# log new un-monitor action
		history_log_event_special( $p_bug_id, BUG_UNMONITOR, $p_user_id );

		return true;
	}

	#===================================
	# Other
	#===================================

	# --------------------
	# Pads the bug id with the appropriate number of zeros.
	function bug_format_id( $p_bug_id ) {
		$t_padding = config_get( 'display_bug_padding' );
		return( str_pad( $p_bug_id, $t_padding, '0', STR_PAD_LEFT ) );
	}

	# --------------------
	# Return a copy of the bug structure with all the instvars prepared for db insertion
	function bug_prepare_db( $p_bug_data ) {
		$t_bug_data = new BugData;
		$t_bug_data->project_id			= db_prepare_int( $p_bug_data->project_id );
		$t_bug_data->reporter_id		= db_prepare_int( $p_bug_data->reporter_id );
		$t_bug_data->handler_id			= db_prepare_int( $p_bug_data->handler_id );
		$t_bug_data->duplicate_id		= db_prepare_int( $p_bug_data->duplicate_id );
		$t_bug_data->priority			= db_prepare_int( $p_bug_data->priority );
		$t_bug_data->severity			= db_prepare_int( $p_bug_data->severity );
		$t_bug_data->reproducibility	= db_prepare_int( $p_bug_data->reproducibility );
		$t_bug_data->status				= db_prepare_int( $p_bug_data->status );
		$t_bug_data->resolution			= db_prepare_int( $p_bug_data->resolution );
		$t_bug_data->projection			= db_prepare_int( $p_bug_data->projection );
		$t_bug_data->category			= db_prepare_string( $p_bug_data->category );
		$t_bug_data->date_submitted		= db_prepare_string( $p_bug_data->date_submitted );
		$t_bug_data->last_updated		= db_prepare_string( $p_bug_data->last_updated );
		$t_bug_data->eta				= db_prepare_int( $p_bug_data->eta );
		$t_bug_data->os					= db_prepare_string( $p_bug_data->os );
		$t_bug_data->os_build			= db_prepare_string( $p_bug_data->os_build );
		$t_bug_data->platform			= db_prepare_string( $p_bug_data->platform );
		$t_bug_data->version			= db_prepare_string( $p_bug_data->version );
		$t_bug_data->build				= db_prepare_string( $p_bug_data->build );
		$t_bug_data->fixed_in_version	= db_prepare_string( $p_bug_data->fixed_in_version );
		$t_bug_data->target_version		= db_prepare_string( $p_bug_data->target_version );
		$t_bug_data->view_state			= db_prepare_int( $p_bug_data->view_state );
		$t_bug_data->summary			= db_prepare_string( $p_bug_data->summary );
		$t_bug_data->sponsorship_total	= db_prepare_int( $p_bug_data->sponsorship_total );
		$t_bug_data->sticky				= db_prepare_int( $p_bug_data->sticky );

		$t_bug_data->description		= db_prepare_string( $p_bug_data->description );
		$t_bug_data->steps_to_reproduce	= db_prepare_string( $p_bug_data->steps_to_reproduce );
		$t_bug_data->additional_information	= db_prepare_string( $p_bug_data->additional_information );

		return $t_bug_data;
	}

	# --------------------
	# Return a copy of the bug structure with all the instvars prepared for editing
	#  in an HTML form
	function bug_prepare_edit( $p_bug_data ) {
		$p_bug_data->category			= string_attribute( $p_bug_data->category );
		$p_bug_data->date_submitted		= string_attribute( $p_bug_data->date_submitted );
		$p_bug_data->last_updated		= string_attribute( $p_bug_data->last_updated );
		$p_bug_data->os					= string_attribute( $p_bug_data->os );
		$p_bug_data->os_build			= string_attribute( $p_bug_data->os_build );
		$p_bug_data->platform			= string_attribute( $p_bug_data->platform );
		$p_bug_data->version			= string_attribute( $p_bug_data->version );
		$p_bug_data->build				= string_attribute( $p_bug_data->build );
		$p_bug_data->target_version     = string_attribute( $p_bug_data->target_version );
		$p_bug_data->fixed_in_version	= string_attribute( $p_bug_data->fixed_in_version );
		$p_bug_data->summary			= string_attribute( $p_bug_data->summary );
		$p_bug_data->sponsorship_total	= string_attribute( $p_bug_data->sponsorship_total );
		$p_bug_data->sticky				= string_attribute( $p_bug_data->sticky );

		$p_bug_data->description		= string_textarea( $p_bug_data->description );
		$p_bug_data->steps_to_reproduce	= string_textarea( $p_bug_data->steps_to_reproduce );
		$p_bug_data->additional_information	= string_textarea( $p_bug_data->additional_information );

		return $p_bug_data;
	}

	# --------------------
	# Return a copy of the bug structure with all the instvars prepared for editing
	#  in an HTML form
	function bug_prepare_display( $p_bug_data ) {
		$p_bug_data->category			= string_display_line( $p_bug_data->category );
		$p_bug_data->date_submitted		= string_display_line( $p_bug_data->date_submitted );
		$p_bug_data->last_updated		= string_display_line( $p_bug_data->last_updated );
		$p_bug_data->os					= string_display_line( $p_bug_data->os );
		$p_bug_data->os_build			= string_display_line( $p_bug_data->os_build );
		$p_bug_data->platform			= string_display_line( $p_bug_data->platform );
		$p_bug_data->version			= string_display_line( $p_bug_data->version );
		$p_bug_data->build				= string_display_line( $p_bug_data->build );
		$p_bug_data->target_version		= string_display_line( $p_bug_data->target_version );
		$p_bug_data->fixed_in_version	= string_display_line( $p_bug_data->fixed_in_version );
		$p_bug_data->summary			= string_display_line_links( $p_bug_data->summary );
		$p_bug_data->sponsorship_total	= string_display_line( $p_bug_data->sponsorship_total );
		$p_bug_data->sticky				= string_display_line( $p_bug_data->sticky );

		$p_bug_data->description		= string_display_links( $p_bug_data->description );
		$p_bug_data->steps_to_reproduce	= string_display_links( $p_bug_data->steps_to_reproduce );
		$p_bug_data->additional_information	= string_display_links( $p_bug_data->additional_information );

		return $p_bug_data;
	}
?>
