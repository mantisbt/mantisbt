<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: config_api.php,v 1.21 2005-03-18 03:43:35 thraxisp Exp $
	# --------------------------------------------------------

	# cache for config variables
	$g_cache_config = array();
	$g_cache_config_access = array();

	### Configuration API ###

	# ------------------
	# Retrieves the value of a config option
	#  This function will return one of (in order of preference):
	#    1. value from cache
	#    2. value from database
	#     looks for specified config_id + current user + current project.
	#     if not found, config_id + current user + all_project
	#     if not found, config_id + default user + current project
	#     if not found, config_id + default user + all_project.
	#    3.use GLOBAL[config_id]
	function config_get( $p_option, $p_default = null, $p_user = null, $p_project = null ) {
		global $g_cache_config, $g_cache_config_access;

		if ( isset( $g_cache_config[$p_option] ) ) {
			return $g_cache_config[$p_option];
		}

		# bypass table lookup for certain options
		$t_match_pattern = '/' . implode( '|', config_get_global( 'global_settings' ) ) . '/';
		$t_bypass_lookup = ( 0 < preg_match( $t_match_pattern, $p_option ) );
		# @@ debug @@ if ($t_bypass_lookup) { echo "bp=$p_option match=$t_match_pattern <br />"; }
		# @@ debug @@ if ( ! db_is_connected() ) { echo "no db"; }

		if ( ( ! $t_bypass_lookup ) && ( TRUE === db_is_connected() )
				&& ( db_table_exists( config_get_global( 'mantis_config_table' ) ) ) ) {
			$t_config_table = config_get_global( 'mantis_config_table' );
			# @@ debug @@ echo "lu table=" . ( db_table_exists( $t_config_table ) ? "yes" : "no" );
			# @@ debug @@ error_print_stack_trace();

			# prepare the user's list
			$t_users = array( ALL_USERS );
			if ( ( null == $p_user ) && ( auth_is_user_authenticated() ) ) {
				$t_users[] = auth_get_current_user_id();
			} else if ( ! in_array( $p_user, $t_users ) ) {
				$t_users[] = $p_user;
			}
			if ( 1 < count( $t_users ) ) {
				$t_user_clause = "user_id in (". implode( ', ', $t_users ) .")";
			} else {
				$t_user_clause = "user_id=$t_users[0]";
			}

			# prepare the projects list
			$t_projects = array( ALL_PROJECTS );
			if ( ( null == $p_project ) && ( auth_is_user_authenticated() ) ) {
				$t_selected_project = helper_get_current_project();
				if ( ALL_PROJECTS <> $t_selected_project ) {
					$t_projects[] = $t_selected_project;
				}
			} else if ( ! in_array( $p_project, $t_projects ) ) {
				$t_projects[] = $p_project;
			}
			if ( 1 < count( $t_projects ) ) {
				$t_project_clause = "project_id in (". implode( ', ', $t_projects ) .")";
			} else {
				$t_project_clause = "project_id=$t_projects[0]";
			}

			$c_option = db_prepare_string( $p_option );
			# @@@ (thraxisp) if performance is a problem, we could fetch all of the configs at
			#  once here. we need to reverse the sort, so that the last value overwrites the
			#  config table
			$query = "SELECT type, value, access FROM $t_config_table
				WHERE config_id = '$p_option' AND
					$t_project_clause AND
					$t_user_clause
				ORDER BY user_id DESC, project_id DESC";

			$result = db_query( $query, 1 );

			if ( 0 < db_num_rows( $result ) ) {
				$row = db_fetch_array( $result );
				$t_type = $row['type'];
				$t_raw_value = $row['value'];

				switch ( $t_type ) {
					case CONFIG_TYPE_INT:
						$t_value = (int) $t_raw_value;
						break;
					case CONFIG_TYPE_COMPLEX:
						$t_value = unserialize( $t_raw_value );
						break;
					case CONFIG_TYPE_STRING:
					default:
						$t_value = config_eval( $t_raw_value );
				}
				$g_cache_config[$p_option] = $t_value;
				$g_cache_config_access[$p_option] = $row['access'];
				return $t_value;
			}
		}
		return config_get_global( $p_option, $p_default );
	}

	# ----------------------
	# force config variable from a global to avoid recursion
	function config_get_global( $p_option, $p_default = null ) {
		global $g_cache_config, $g_cache_config_access;

		if ( isset( $GLOBALS['g_' . $p_option] ) ) {
			$t_value = config_eval( $GLOBALS['g_' . $p_option] );
			$g_cache_config[$p_option] = $t_value;
			$g_cache_config_access[$p_option] = ADMINISTRATOR;
			return $t_value;
		} else {
			# unless we were allowing for the option not to exist by passing
			#  a default, trigger a WARNING
			if ( null === $p_default ) {
				error_parameters( $p_option );
				trigger_error( ERROR_CONFIG_OPT_NOT_FOUND, WARNING );
			}
			return $p_default;
		}
	}

	# ------------------
	# Retrieves the access level needed to change a config value
	function config_get_access( $p_option ) {
		global $g_cache_config, $g_cache_config_access;

		if ( ! isset( $g_cache_config[$p_option] ) ) {
			$t_value = config_get( $p_option );
		}

		return $g_cache_config_access[$p_option];
	}

	# ------------------
	# Returns true if the specified config option exists (ie. a
	#  value or default can be found), false otherwise
	function config_is_set( $p_option, $p_user = null, $p_project = null ) {
		global $g_cache_config;
		if ( isset( $GLOBALS['g_' . $p_option] ) || isset( $g_cache_config[$p_option] ) ) {
			return true;
		} else {
			# bypass table lookup for certain options
			$t_match_pattern = '/' . implode( '|', config_get_global( 'global_settings' ) ) . '/';
			$t_bypass_lookup = ( 0 < preg_match( $t_match_pattern, $p_option ) );

			if ( ( ! $t_bypass_lookup ) && ( TRUE === db_is_connected() )
				&& ( db_table_exists( config_get_global( 'mantis_config_table' ) ) ) ) {

				$t_config_table = config_get_global( 'mantis_config_table' );

				# prepare the user's list
				$t_users = array( ALL_USERS );
				if ( ( null == $p_user ) && ( auth_is_user_authenticated() ) ) {
					$t_users[] = auth_get_current_user_id();
				} else if ( ! in_array( $p_user, $t_users ) ) {
					$t_users[] = $p_user;
				}
				if ( 1 < count( $t_users ) ) {
					$t_user_clause = "user_id in (". implode( ', ', $t_users ) .")";
				} else {
					$t_user_clause = "user_id=$t_users[0]";
				}

				# prepare the projects list
				$t_projects = array( ALL_PROJECTS );
				if ( ( null == $p_project ) && ( auth_is_user_authenticated() ) ) {
					$t_selected_project = helper_get_current_project( false );
					if ( ALL_PROJECTS <> $t_selected_project ) {
						$t_projects[] = $t_selected_project;
					}
				} else if ( ! in_array( $p_project, $t_projects ) ) {
					$t_projects[] = $p_project;
				}
				if ( 1 < count( $t_projects ) ) {
					$t_project_clause = "project_id in (". implode( ', ', $t_projects ) .")";
				} else {
					$t_project_clause = "project_id=$t_projects[0]";
				}

				$c_option = db_prepare_string( $p_option );
				# @@@ (thraxisp) if performance is a problem, we could fetch all of the configs at
				#  once here. we need to reverse the sort, so that the last value overwrites the
				#  config table
				$query = "SELECT COUNT(*) FROM $t_config_table
					WHERE config_id = '$p_option' AND
						$t_project_clause AND
						$t_user_clause";

				$result = db_query( $query );

				if ( 0 < db_result( $result ) ) {
					return true;
				}
			}

			return false;
		}
	}

	# ------------------
	# Sets the value of the given config option to the given value
	#  If the config option does not exist, an ERROR is triggered
	function config_set( $p_option, $p_value, $p_user = NO_USER, $p_project = ALL_PROJECTS, $p_access = ADMINISTRATOR ) {
		if ( is_array( $p_value ) || is_object( $p_value ) ) {
			$t_type = CONFIG_TYPE_COMPLEX;
			$c_value = db_prepare_string( serialize( $p_value ) );
		} else if ( is_int( $p_value ) ) {
			$t_type = CONFIG_TYPE_INT;
			$c_value = db_prepare_int( $p_value );
		} else {
			$t_type = CONFIG_TYPE_STRING;
			$c_value = db_prepare_string( $p_value );
		}
		$c_option = db_prepare_string( $p_option );
		$c_user = db_prepare_int( $p_user );
		$c_project = db_prepare_int( $p_project );
		$c_access = db_prepare_int( $p_access );

		$t_config_table = config_get_global( 'mantis_config_table' );
		$query = "SELECT * from $t_config_table
			WHERE config_id = '$c_option' AND
				project_id = $c_project AND
				user_id = $c_user";
		$result = db_query( $query );

		if ( 0 < db_num_rows( $result ) ) {
			$t_set_query = "UPDATE $t_config_table
				SET value='$c_value', type=$t_type, access=$c_access
				WHERE config_id = '$c_option' AND
					project_id = $c_project AND
					user_id = $c_user";
		} else {
			$t_set_query = "INSERT INTO $t_config_table
				SET value='$c_value', type=$t_type, access=$c_access,
					config_id = '$c_option',
					project_id = $c_project,
					user_id = $c_user";
		}

		$result = db_query( $t_set_query );

		return true;
	}
	# ------------------
	# Checks if an obsolete configuration variable is still in use.  If so, an error
	# will be generated and the script will exit.  This is called from admin_check.php.
	function config_obsolete( $p_var, $p_replace ) {
		# @@@ we could trigger a WARNING here, once we have errors that can
		#     have extra data plugged into them (we need to give the old and
		#     new config option names in the warning text)

		if ( config_is_set( $p_var ) ) {
			PRINT '<p><b>Warning:</b> The configuration option <tt>$g_' . $p_var . '</tt> is now obsolete';
			if ( is_array( $p_replace ) ) {
				PRINT ', please see the following options: <ul>';
				foreach ( $p_replace as $t_option ) {
					PRINT '<li>$g_' . $t_option . '</li>';
				}
				PRINT '</ul>';
			} else if ( !is_blank( $p_replace ) ) {
				PRINT ', please use <tt>$g_' . $p_replace . '</tt> instead.';
			}
			PRINT '</p>';
		}
	}

	# ------------------
	# check for recursion in defining config variables
	# If there is a %text% in the returned value, re-evaluate the "text" part and replace
	#  the string
	function config_eval( $p_value ) {
		$t_value = $p_value;
		if ( is_string( $t_value ) && !is_numeric( $t_value ) ) {
			if ( 0 < preg_match_all( '/%(.*)%/U', $t_value, $t_matches ) ) {
				for ($i=0; $i< count($t_matches[0]); $i++) {
					# $t_matches[0][$i] is the matched string including the delimiters
					# $t_matches[1][$i] is the target parameter string
					$t_repl = config_get( $t_matches[1][$i] );
					$t_value = str_replace( $t_matches[0][$i], $t_repl, $t_value );
				}
			}
		}
		return $t_value;
	}
?>
