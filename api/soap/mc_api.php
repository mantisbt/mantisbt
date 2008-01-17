<?php
	# MantisConnect - A webservice interface to Mantis Bug Tracker
	# Copyright (C) 2004-2007  Victor Boctor - vboctor@users.sourceforge.net
	# This program is distributed under dual licensing.  These include
	# GPL and a commercial licenses.  Victor Boctor reserves the right to
	# change the license of future releases.
	# See docs/ folder for more details

	# --------------------------------------------------------
	# $Id: mc_api.php,v 1.2 2007-08-22 04:09:59 vboctor Exp $
	# --------------------------------------------------------

	# set up error_handler() as the new default error handling function
	set_error_handler( 'mc_error_handler' );

	# override some Mantis configurations
	$g_show_detailed_errors	= OFF;
	$g_stop_on_errors = ON;
	$g_display_errors = array(
		E_WARNING => 'halt',
		E_NOTICE => 'halt',
		E_USER_ERROR => 'halt',
		E_USER_WARNING => 'halt',
		E_USER_NOTICE => 'halt'
	);

	/**
	 * Get the MantisConnect webservice version.
	 */
	function mc_version() {
		return MANTIS_VERSION;
	}

	# --------------------
	# Checks if Mantis installation is marked as offline by the administrator.
	# true: offline, false: online
	function mci_is_mantis_offline() {
		$t_offline_file = dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'mantis_offline.php';
		return file_exists( $t_offline_file );
	}

	# --------------------
	# return user_id if successful, otherwise false.
	function mci_check_login( $p_username, $p_password ) {
		if ( mci_is_mantis_offline() ) {
			return false;
		}

		# if no user name supplied, then attempt to login as anonymous user.
		if ( is_blank( $p_username ) ) {
			$t_anon_allowed = config_get( 'allow_anonymous_login' );
			if ( OFF == $t_anon_allowed ) {
				return false;
			}

			$p_username = config_get( 'anonymous_account' );

			# do not use password validation.
			$p_password = null;
		}

		if ( false === auth_attempt_script_login( $p_username, $p_password ) ) {
			return false;
		}

		return auth_get_current_user_id();
	}

	# --------------------
	function mci_has_readonly_access( $p_user_id, $p_project_id = ALL_PROJECTS ) {
		$t_access_level = user_get_access_level( $p_user_id, $p_project_id );
		return ( $t_access_level >= config_get( 'mc_readonly_access_level_threshold' ) );
	}

	# --------------------
	function mci_has_readwrite_access( $p_user_id, $p_project_id = ALL_PROJECTS ) {
		$t_access_level = user_get_access_level( $p_user_id, $p_project_id );
		return ( $t_access_level >= config_get( 'mc_readwrite_access_level_threshold' ) );
	}

	# --------------------
	function mci_has_administrator_access( $p_user_id, $p_project_id = ALL_PROJECTS ) {
		$t_access_level = user_get_access_level( $p_user_id, $p_project_id );
		return ( $t_access_level >= config_get( 'mc_admin_access_level_threshold' ) );
	}

	# --------------------
	function mci_get_project_id( $p_project ) {
		if ( (int)$p_project['id'] != 0 ) {
			$t_project_id =  (int)$p_project['id'];
		} else {
			$t_project_id = project_get_id_by_name( $p_project['name'] );
		}

		return $t_project_id;
	}

	# --------------------
	function mci_get_project_status_id( $p_status ) {
		return mci_get_enum_id_from_objectref( 'project_status', $p_status );
	}
	
	# --------------------
	function mci_get_project_view_state_id( $p_view_state ) {
		return mci_get_enum_id_from_objectref( 'project_view_state', $p_view_state );
	}
	
	# --------------------
	function mci_get_user_id( &$p_user ) {
		if ( !isset( $p_user ) ) {
			return 0;
		}

		$t_user_id = 0;

		if ( (int)$p_user['id'] != 0 ) {
			$t_user_id =  (int)$p_user['id'];
		} else {
			if ( isset( $p_user['name'] ) ) {
				$t_user_id = user_get_id_by_name( $p_user['name'] );
			}
		}

		return $t_user_id;
	}

	# --------------------	
	function mci_get_user_lang( $p_user_id ) {
		$t_lang = user_pref_get_pref( $p_user_id,  'language' );
		if ( $t_lang == 'auto' ) {
			$t_lang = config_get( 'fallback_language' );
		}
		return $t_lang;
	}

	# --------------------
	function mci_get_status_id( $p_status ) {
		return mci_get_enum_id_from_objectref( 'status', $p_status );
	}

	# --------------------
	function mci_get_severity_id( $p_severity ) {
		return mci_get_enum_id_from_objectref( 'severity', $p_severity );
	}

	# --------------------
	function mci_get_priority_id( $p_priority ) {
		return mci_get_enum_id_from_objectref( 'priority', $p_priority );
	}

	# --------------------
	function mci_get_reproducibility_id( $p_reproducibility ) {
		return mci_get_enum_id_from_objectref( 'reproducibility', $p_reproducibility );
	}

	# --------------------
	function mci_get_resolution_id( $p_resolution ) {
		return mci_get_enum_id_from_objectref( 'resolution', $p_resolution );
	}

	# --------------------
	function mci_get_projection_id( $p_projection ) {
		return mci_get_enum_id_from_objectref( 'projection', $p_projection );
	}

	# --------------------
	function mci_get_eta_id( $p_eta ) {
		return mci_get_enum_id_from_objectref( 'eta', $p_eta );
	}

	# --------------------
	function mci_get_view_state_id( $p_view_state ) {
		return mci_get_enum_id_from_objectref( 'view_state', $p_view_state );
	}

	# --------------------
	# Get null on empty value.
	#
	# @param Object $p_value  The value
	# @return Object  The value if not empty; null otherwise.
	#
	function mci_null_if_empty( &$p_value ) {
		if( !is_blank( $p_value ) ) {
			return $p_value;
		}

		return null;
	}

	/**
	 * Gets the url for Mantis.  This is based on the 'path' config variable in Mantis.  However,
	 * the default value for 'path' doesn't work properly when access from within MantisConnect.
	 * This internal function fixes this bug.
	 *
	 * @return Mantis URL terminated by a /.
	 */
	function mci_get_mantis_path() {
		$t_path = config_get( 'path' );
		$t_dir = basename( dirname( __FILE__ ) );

		# for some reason str_replace() doesn't work when DIRECTORY_SEPARATOR (/) is in the search
		# string.
		$t_path = str_replace( $t_dir . '/', '', $t_path );

		return $t_path;
	}	
	
	# --------------------
	# Given a enum string and num, return the appropriate localized string
	function mci_get_enum_element( $p_enum_name, $p_val, $p_lang ) {
		$config_var = config_get( $p_enum_name.'_enum_string' );
		$string_var = lang_get(  $p_enum_name.'_enum_string', $p_lang );

		# use the global enum string to search
		$t_arr			= explode_enum_string( $config_var );
		$t_arr_count	= count( $t_arr );
		for ( $i=0; $i < $t_arr_count ;$i++ ) {
			$elem_arr = explode_enum_arr( $t_arr[$i] );
			if ( $elem_arr[0] == $p_val ) {
				# now get the appropriate translation
				return get_enum_to_string( $string_var, $p_val );
			}
		}
		return '@' . $p_val . '@';
	}
	
	# --------------------
	# Gets the sub-projects that are accessible to the specified user / project.
	function mci_user_get_accessible_subprojects( $p_user_id, $p_parent_project_id, $p_lang = null ) {
		if ( $p_lang === null ) {
			$t_lang = mci_get_user_lang( $p_user_id );
		} else {
			$t_lang = $p_lang;
		}

		$t_result = array();
		foreach( user_get_accessible_subprojects( $p_user_id, $p_parent_project_id ) as $t_subproject_id ) {
			$t_subproject_row = project_cache_row( $t_subproject_id );
			$t_subproject = array();
			$t_subproject['id'] = $t_subproject_id;
			$t_subproject['name'] = $t_subproject_row['name'];
			$t_subproject['status'] = mci_enum_get_array_by_id( $t_subproject_row['status'], 'project_status', $t_lang );
			$t_subproject['enabled'] = $t_subproject_row['enabled'];
			$t_subproject['view_state'] = mci_enum_get_array_by_id( $t_subproject_row['view_state'], 'project_view_state', $t_lang );
			$t_subproject['access_min'] = mci_enum_get_array_by_id( $t_subproject_row['access_min'], 'access_levels', $t_lang );
			$t_subproject['file_path'] =
				array_key_exists( 'file_path', $t_subproject_row ) ? $t_subproject_row['file_path'] : "";
			$t_subproject['description'] =
				array_key_exists( 'description', $t_subproject_row ) ? $t_subproject_row['description'] : "";
			$t_subproject['subprojects'] = mci_user_get_accessible_subprojects( $p_user_id, $t_subproject_id, $t_lang );
			$t_result[] = $t_subproject;
		}

		return $t_result;
	}
	
	# --------------------
	# category_get_all_rows did't respect subprojects.
	function mci_category_get_all_rows( $p_project_id, $p_user_id ) {
		$t_project_category_table = config_get( 'mantis_project_category_table' );

		$c_project_id = db_prepare_int( $p_project_id );

		$t_project_where = helper_project_specific_where( $c_project_id, $p_user_id );

		$query = "SELECT category FROM $t_project_category_table
				WHERE $t_project_where
				ORDER BY category ASC";
		$result = db_query( $query );
		$count = db_num_rows( $result );
		$cat_arr = array();
		for ( $i = 0 ; $i < $count ; $i++ ) {
			$row = db_fetch_array( $result );
			$cat_arr[] = string_attribute( $row['category'] );
		}

		sort( $cat_arr );

		return $cat_arr;
	}
	
	/**
	 * Basically this is a copy of core/filter_api.php#filter_db_get_available_queries().
	 * The only difference is that the result of this function is not an array of filter
	 * names but an array of filter structures. 
	 */
	function mci_filter_db_get_available_queries( $p_project_id = null, $p_user_id = null ) {
		$t_filters_table = config_get( 'mantis_filters_table' );
		$t_overall_query_arr = array();

		if ( null === $p_project_id ) {
			$t_project_id = helper_get_current_project();
		} else {
			$t_project_id = db_prepare_int( $p_project_id );
		}

		if ( null === $p_user_id ) {
			$t_user_id = auth_get_current_user_id();
		} else {
			$t_user_id = db_prepare_int( $p_user_id );
		}

		# If the user doesn't have access rights to stored queries, just return
		if ( !access_has_project_level( config_get( 'stored_query_use_threshold' ) ) ) {
			return $t_overall_query_arr;
		}

		# Get the list of available queries. By sorting such that public queries are
		# first, we can override any query that has the same name as a private query
		# with that private one
		$query = "SELECT * FROM $t_filters_table
					WHERE (project_id='$t_project_id'
					OR project_id='0')
					AND name!=''
					ORDER BY is_public DESC, name ASC";
		$result = db_query( $query );
		$query_count = db_num_rows( $result );

		for ( $i = 0; $i < $query_count; $i++ ) {
			$row = db_fetch_array( $result );
			if ( ( $row['user_id'] == $t_user_id ) || db_prepare_bool( $row['is_public'] ) ) {
				$t_overall_query_arr[$row['name']] = $row;
			}
		}

		return array_values( $t_overall_query_arr );
	}
		
	#########################################
	# SECURITY NOTE: these globals are initialized here to prevent them
	#   being spoofed if register_globals is turned on
	#
	$g_error_parameters		= array();
	$g_error_handled		= false;
	$g_error_proceed_url	= null;

	# ---------------
	# Default error handler
	#
	# This handler will not receive E_ERROR, E_PARSE, E_CORE_*, or E_COMPILE_*
	#  errors.
	#
	# E_USER_* are triggered by us and will contain an error constant in $p_error
	# The others, being system errors, will come with a string in $p_error
	#
	function mc_error_handler( $p_type, $p_error, $p_file, $p_line, $p_context ) {
		global $g_error_parameters, $g_error_handled, $g_error_proceed_url;
		global $g_lang_overrides;
		global $g_error_send_page_header;
		global $l_oServer;

		# check if errors were disabled with @ somewhere in this call chain
		# also suppress php 5 strict warnings
		if ( 0 == error_reporting() || 2048 == $p_type ) {
			return;
		}

		$t_lang_pushed = false;

		# flush any language overrides to return to user's natural default
		if ( function_exists( 'db_is_connected' ) ) {
			if ( db_is_connected() ) {
				lang_push( lang_get_default() );
				$t_lang_pushed = true;
			}
		}

		$t_short_file	= basename( $p_file );
		$t_method_array = config_get( 'display_errors' );
		if ( isset( $t_method_array[$p_type] ) ) {
			$t_method = $t_method_array[$p_type];
		} else {
			$t_method		= 'none';
		}

		# build an appropriate error string
		switch ( $p_type ) {
			case E_WARNING:
				$t_error_type = 'SYSTEM WARNING';
				$t_error_description = $p_error;
				break;
			case E_NOTICE:
				$t_error_type = 'SYSTEM NOTICE';
				$t_error_description = $p_error;
				break;
			case E_USER_ERROR:
				$t_error_type = "APPLICATION ERROR #$p_error";
				$t_error_description = error_string( $p_error );
				break;
			case E_USER_WARNING:
				$t_error_type = "APPLICATION WARNING #$p_error";
				$t_error_description = error_string( $p_error );
				break;
			case E_USER_NOTICE:
				# used for debugging
				$t_error_type = 'DEBUG';
				$t_error_description = $p_error;
				break;
			default:
				#shouldn't happen, just display the error just in case
				$t_error_type = '';
				$t_error_description = $p_error;
		}

		$t_error_description = $t_error_description;
		$t_error_stack = error_get_stack_trace();

		$l_oServer->fault( 'Server', "Error Type: $t_error_type,\nError Description:\n$t_error_description,\nStack Trace:\n$t_error_stack" );
		$l_oServer->send_response();
		exit();
	}

	# ---------------
	# Get a stack trace if PHP provides the facility or xdebug is present
	function error_get_stack_trace() {
		$t_stack = '';

		if ( extension_loaded( 'xdebug' ) ) { #check for xdebug presence
			$t_stack = xdebug_get_function_stack();

			# reverse the array in a separate line of code so the
			#  array_reverse() call doesn't appear in the stack
			$t_stack = array_reverse( $t_stack );
			array_shift( $t_stack ); #remove the call to this function from the stack trace

			foreach ( $t_stack as $t_frame ) {
				$t_stack .= ( isset( $t_frame['file'] ) ? basename( $t_frame['file'] ) : 'UnknownFile' ) . ' L' . ( isset( $t_frame['line'] ) ? $t_frame['line'] : '?' ) . ' ' . ( isset( $t_frame['function'] ) ? $t_frame['function'] : 'UnknownFunction' );

				$t_args = array();
				if ( isset( $t_frame['params'] ) ) {
					$t_stack .= ' Params: ';
					foreach( $t_frame['params'] as $t_value ) {
						$t_args[] = error_build_parameter_string( $t_value );
					}
					
					$t_stack .= '(' . implode( $t_args, ', ' ) . ')';
				} else {
					$t_stack .= '()';
				}

				$t_stack .= "\n";
			}
		} else {
			$t_stack = debug_backtrace();

			array_shift( $t_stack ); #remove the call to this function from the stack trace
			array_shift( $t_stack ); #remove the call to the error handler from the stack trace

			foreach ( $t_stack as $t_frame ) {
				$t_stack .= ( isset( $t_frame['file'] ) ? basename( $t_frame['file'] ) : 'UnknownFile' ) . ' L' . ( isset( $t_frame['line'] ) ? $t_frame['line'] : '?' ) . ' ' . ( isset( $t_frame['function'] ) ? $t_frame['function'] : 'UnknownFunction' );

				$t_args = array();
				if ( isset( $t_frame['args'] ) ) {
					foreach( $t_frame['args'] as $t_value ) {
						$t_args[] = error_build_parameter_string( $t_value );
					}
					
					$t_stack .= '(' . implode( $t_args, ', ' ) . ')';
				} else {
					$t_stack .= '()';
				}

				$t_stack .= "\n";
			}
		}

		return $t_stack;
	}
	?>