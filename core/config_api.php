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
 * Configuration API
 *
 * @package CoreAPI
 * @subpackage ConfigurationAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses authentication_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses error_api.php
 * @uses helper_api.php
 * @uses utility_api.php
 */

require_api( 'authentication_api.php' );
require_api( 'constant_inc.php' );
require_api( 'database_api.php' );
require_api( 'error_api.php' );
require_api( 'helper_api.php' );
require_api( 'utility_api.php' );

# cache for config variables
$g_cache_config = array();
$g_cache_config_eval = array();
$g_cache_config_access = array();
$g_cache_bypass_lookup = array();
$g_cache_filled = false;
$g_cache_can_set_in_database = '';

# cache environment to speed up lookups
$g_cache_db_table_exists = false;

$g_cache_config_user = null;
$g_cache_config_project = null;

/**
 *
 * Retrieves the value of a config option
 *  This function will return one of (in order of preference):
 *    1. value from cache
 *    2. value from database
 *     looks for specified config_id + current user + current project.
 *     if not found, config_id + current user + all_project
 *     if not found, config_id + default user + current project
 *     if not found, config_id + default user + all_project.
 *    3.use GLOBAL[config_id]
 *
 * @param string $p_option Config option
 * @param string $p_default default value
 * @param int $p_user user id
 * @param int $p_project project id
 * @return mixed
 */
function config_get( $p_option, $p_default = null, $p_user = null, $p_project = null ) {
	global $g_cache_config, $g_cache_config_access, $g_cache_db_table_exists, $g_cache_filled;
	global $g_cache_config_user, $g_cache_config_project, $g_project_override;

	# @@ debug @@ echo "lu o=$p_option ";
	# bypass table lookup for certain options
	$t_bypass_lookup = !config_can_set_in_database( $p_option );

	# @@ debug @@ if ($t_bypass_lookup) { echo "bp=$p_option match=$t_match_pattern <br />"; }

	if( !$t_bypass_lookup ) {
		if( $g_project_override !== null && $p_project === null ) {
			$p_project = $g_project_override;
		}
		# @@ debug @@ if ( ! db_is_connected() ) { echo "no db "; }
		# @@ debug @@ echo "lu table=" . ( db_table_exists( $t_config_table ) ? "yes " : "no " );
		if( !$g_cache_db_table_exists ) {
			$t_config_table = db_get_table( 'config' );
			$g_cache_db_table_exists = ( TRUE === db_is_connected() ) && db_table_exists( $t_config_table );
		}

		if( $g_cache_db_table_exists ) {

			# @@ debug @@ echo " lu db $p_option ";
			# @@ debug @@ error_print_stack_trace();
			# prepare the user's list
			$t_users = array();
			if( null === $p_user ) {
				if( !isset( $g_cache_config_user ) ) {
					$t_users[] = auth_is_user_authenticated() ? auth_get_current_user_id() : ALL_USERS;
					if( !in_array( ALL_USERS, $t_users ) ) {
						$t_users[] = ALL_USERS;
					}
					$g_cache_config_user = $t_users;
				} else {
					$t_users = $g_cache_config_user;
				}
			} else {
				$t_users[] = $p_user;
				if( !in_array( ALL_USERS, $t_users ) ) {
					$t_users[] = ALL_USERS;
				}
			}

			# prepare the projects list
			$t_projects = array();
			if(( null === $p_project ) ) {
				if( !isset( $g_cache_config_project ) ) {
					$t_projects[] = auth_is_user_authenticated() ? helper_get_current_project() : ALL_PROJECTS;
					if( !in_array( ALL_PROJECTS, $t_projects ) ) {
						$t_projects[] = ALL_PROJECTS;
					}
					$g_cache_config_project = $t_projects;
				} else {
					$t_projects = $g_cache_config_project;
				}
			} else {
				$t_projects[] = $p_project;
				if( !in_array( ALL_PROJECTS, $t_projects ) ) {
					$t_projects[] = ALL_PROJECTS;
				}
			}

			# @@ debug @@ echo 'pr= '; var_dump($t_projects);
			# @@ debug @@ echo 'u= '; var_dump($t_users);

			if( !$g_cache_filled ) {
				$t_config_table = db_get_table( 'config' );
				$query = "SELECT config_id, user_id, project_id, type, value, access_reqd FROM $t_config_table";
				$t_result = db_query_bound( $query );
				while( false <> ( $row = db_fetch_array( $t_result ) ) ) {
					$t_config = $row['config_id'];
					$t_user = $row['user_id'];
					$t_project = $row['project_id'];
					$g_cache_config[$t_config][$t_user][$t_project] = $row['type'] . ';' . $row['value'];
					$g_cache_config_access[$t_config][$t_user][$t_project] = $row['access_reqd'];
				}
				$g_cache_filled = true;
			}

			if( isset( $g_cache_config[$p_option] ) ) {
				$t_found = false;
				reset( $t_users );
				while(( list(, $t_user ) = each( $t_users ) ) && !$t_found ) {
					reset( $t_projects );
					while(( list(, $t_project ) = each( $t_projects ) ) && !$t_found ) {
						if( isset( $g_cache_config[$p_option][$t_user][$t_project] ) ) {
							$t_value = $g_cache_config[$p_option][$t_user][$t_project];
							$t_found = true;

							# @@ debug @@ echo "clu found u=$t_user, p=$t_project, v=$t_value ";
						}
					}
				}

				if( $t_found ) {
					list( $t_type, $t_raw_value ) = explode( ';', $t_value, 2 );

					switch( $t_type ) {
						case CONFIG_TYPE_FLOAT:
							$t_value = (float) $t_raw_value;
							break;
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
					return $t_value;
				}
			}
		}
	}
	return config_get_global( $p_option, $p_default );
}

/**
 * force config variable from a global to avoid recursion
 *
 * @param string $p_option config option
 * @param string $p_default default value
 * @return string
 */
function config_get_global( $p_option, $p_default = null ) {
	global $g_cache_config_eval;
	if( isset( $GLOBALS['g_' . $p_option] ) ) {
		if( !isset( $g_cache_config_eval['g_' . $p_option] ) ) {
			$t_value = config_eval( $GLOBALS['g_' . $p_option], true );
			$g_cache_config_eval['g_' . $p_option] = $t_value;
		} else {
			$t_value = $g_cache_config_eval['g_' . $p_option];
		}
		return $t_value;
	} else {
		# unless we were allowing for the option not to exist by passing
		#  a default, trigger a WARNING
		if( null === $p_default ) {
			error_parameters( $p_option );
			trigger_error( ERROR_CONFIG_OPT_NOT_FOUND, WARNING );
		}
		return $p_default;
	}
}

/**
 * Retrieves the access level needed to change a config value
 *
 * @param string $p_option config option
 * @param int $p_user user id
 * @param int $p_project project id
 * @return int
 */
function config_get_access( $p_option, $p_user = null, $p_project = null ) {
	global $g_cache_config, $g_cache_config_access, $g_cache_filled;

	if( !$g_cache_filled ) {
		config_get( $p_option, -1, $p_user, $p_project );
	}

	# prepare the user's list
	$t_users = array();
	if(( null === $p_user ) && ( auth_is_user_authenticated() ) ) {
		$t_users[] = auth_get_current_user_id();
	}
	else if( !in_array( $p_user, $t_users ) ) {
		$t_users[] = $p_user;
	}
	$t_users[] = ALL_USERS;

	# prepare the projects list
	$t_projects = array();
	if(( null === $p_project ) && ( auth_is_user_authenticated() ) ) {
		$t_selected_project = helper_get_current_project();
		$t_projects[] = $t_selected_project;
		if( ALL_PROJECTS <> $t_selected_project ) {
			$t_projects[] = ALL_PROJECTS;
		}
	}
	else if( !in_array( $p_project, $t_projects ) ) {
		$t_projects[] = $p_project;
	}

	$t_found = false;
	if( isset( $g_cache_config[$p_option] ) ) {
		reset( $t_users );
		while(( list(, $t_user ) = each( $t_users ) ) && !$t_found ) {
			reset( $t_projects );
			while(( list(, $t_project ) = each( $t_projects ) ) && !$t_found ) {
				if( isset( $g_cache_config[$p_option][$t_user][$t_project] ) ) {
					$t_access = $g_cache_config_access[$p_option][$t_user][$t_project];
					$t_found = true;
				}
			}
		}
	}

	return $t_found ? $t_access : config_get_global( 'admin_site_threshold' );
}

/**
 * Returns true if the specified config option exists (ie. a
 * value or default can be found), false otherwise
 *
 * @param string $p_option config option
 * @param int $p_user user id
 * @param int $p_project project id
 * @return bool
 */
function config_is_set( $p_option, $p_user = null, $p_project = null ) {
	global $g_cache_config, $g_cache_filled;

	if( !$g_cache_filled ) {
		config_get( $p_option, -1, $p_user, $p_project );
	}

	# prepare the user's list
	$t_users = array( ALL_USERS );
	if(( null === $p_user ) && ( auth_is_user_authenticated() ) ) {
		$t_users[] = auth_get_current_user_id();
	}
	else if( !in_array( $p_user, $t_users ) ) {
		$t_users[] = $p_user;
	}
	$t_users[] = ALL_USERS;

	# prepare the projects list
	$t_projects = array( ALL_PROJECTS );
	if(( null === $p_project ) && ( auth_is_user_authenticated() ) ) {
		$t_selected_project = helper_get_current_project();
		if( ALL_PROJECTS <> $t_selected_project ) {
			$t_projects[] = $t_selected_project;
		}
	}
	else if( !in_array( $p_project, $t_projects ) ) {
		$t_projects[] = $p_project;
	}

	$t_found = false;
	reset( $t_users );
	while(( list(, $t_user ) = each( $t_users ) ) && !$t_found ) {
		reset( $t_projects );
		while(( list(, $t_project ) = each( $t_projects ) ) && !$t_found ) {
			if( isset( $g_cache_config[$p_option][$t_user][$t_project] ) ) {
				$t_found = true;
			}
		}
	}

	if( $t_found ) {
		return true;
	}

	return isset( $GLOBALS['g_' . $p_option] );
}

/**
 * Sets the value of the given config option to the given value
 *  If the config option does not exist, an ERROR is triggered
 *
 * @param string $p_option config option
 * @param string $p_value config value
 * @param int $p_user user id
 * @param int $p_project project id
 * @param int $p_access access level
 * @return bool
 */
function config_set( $p_option, $p_value, $p_user = NO_USER, $p_project = ALL_PROJECTS, $p_access = DEFAULT_ACCESS_LEVEL ) {
	if( $p_access == DEFAULT_ACCESS_LEVEL ) {
		$p_access = config_get_global( 'admin_site_threshold' );
	}
	if( is_array( $p_value ) || is_object( $p_value ) ) {
		$t_type = CONFIG_TYPE_COMPLEX;
		$c_value = serialize( $p_value );
	} else if( is_float( $p_value ) ) {
		$t_type = CONFIG_TYPE_FLOAT;
		$c_value = (float) $p_value;
	} else if( is_int( $p_value ) || is_numeric( $p_value ) ) {
		$t_type = CONFIG_TYPE_INT;
		$c_value = (int) $p_value;
	} else {
		$t_type = CONFIG_TYPE_STRING;
		$c_value = $p_value;
	}

	if( config_can_set_in_database( $p_option ) ) {
		# before we set in the database, ensure that the user and project id exist
		if( $p_project !== ALL_PROJECTS ) {
			project_ensure_exists( $p_project );
		}
		if( $p_user !== NO_USER ) {
			user_ensure_exists( $p_user );
		}

		$t_config_table = db_get_table( 'config' );
		$t_query = "SELECT COUNT(*) from $t_config_table
				WHERE config_id = " . db_param() . " AND
					project_id = " . db_param() . " AND
					user_id = " . db_param();
		$t_result = db_query_bound( $t_query, array( $p_option, (int)$p_project, (int)$p_user ) );

		$t_params = array();
		if( 0 < db_result( $t_result ) ) {
			$t_set_query = "UPDATE $t_config_table
					SET value=" . db_param() . ", type=" . db_param() . ", access_reqd=" . db_param() . "
					WHERE config_id = " . db_param() . " AND
						project_id = " . db_param() . " AND
						user_id = " . db_param();
			$t_params = array(
				$c_value,
				$t_type,
				(int)$p_access,
				$p_option,
				(int)$p_project,
				(int)$p_user,
			);
		} else {
			$t_set_query = "INSERT INTO $t_config_table
					( value, type, access_reqd, config_id, project_id, user_id )
					VALUES
					(" . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ',' . db_param() . ' )';
			$t_params = array(
				$c_value,
				$t_type,
				(int)$p_access,
				$p_option,
				(int)$p_project,
				(int)$p_user,
			);
		}

		db_query_bound( $t_set_query, $t_params );
	}

	config_set_cache( $p_option, $c_value, $t_type, $p_user, $p_project, $p_access );

	return true;
}

/**
 * Sets the value of the given config option in the global namespace.
 * Does *not* persist the value between sessions. If override set to
 * false, then the value will only be set if not already existent.
 *
 * @param string $p_option config option
 * @param string $p_value config value
 * @param bool $p_override override existing value if already set
 * @return bool
 */
function config_set_global( $p_option, $p_value, $p_override = true ) {
	global $g_cache_config_eval;

	if( $p_override || !isset( $GLOBALS['g_' . $p_option] ) ) {
		$GLOBALS['g_' . $p_option] = $p_value;
		unset( $g_cache_config_eval['g_' . $p_option] );
	}

	return true;
}

/**
 * Sets the value of the given config option to the given value
 *  If the config option does not exist, an ERROR is triggered
 *
 * @param string $p_option config option
 * @param string $p_value config value
 * @param int $p_type type
 * @param int $p_user user id
 * @param int $p_project project id
 * @param int $p_access access level
 * @return bool
 */
function config_set_cache( $p_option, $p_value, $p_type, $p_user = NO_USER, $p_project = ALL_PROJECTS, $p_access = DEFAULT_ACCESS_LEVEL ) {
	global $g_cache_config, $g_cache_config_access;

	if( $p_access == DEFAULT_ACCESS_LEVEL ) {
		$p_access = config_get_global( 'admin_site_threshold' );
	}

	$g_cache_config[$p_option][$p_user][$p_project] = $p_type . ';' . $p_value;
	$g_cache_config_access[$p_option][$p_user][$p_project] = $p_access;

	return true;
}

/**
 * Checks if the specific configuration option can be set in the database, otherwise it can only be set
 * in the configuration file (config_inc.php / config_defaults_inc.php).
 *
 * @param string $p_option config option
 * @return bool
 */
function config_can_set_in_database( $p_option ) {
	global $g_cache_can_set_in_database, $g_cache_bypass_lookup;

	if( isset( $g_cache_bypass_lookup[$p_option] ) ) {
		return !$g_cache_bypass_lookup[$p_option];
	}

	# bypass table lookup for certain options
	if( $g_cache_can_set_in_database == '' ) {
		$g_cache_can_set_in_database = config_get_global( 'global_settings' );
	}
	$t_bypass_lookup = in_array( $p_option, $g_cache_can_set_in_database, true );

	$g_cache_bypass_lookup[$p_option] = $t_bypass_lookup;

	return !$t_bypass_lookup;
}

/**
 * Checks if the specific configuration option can be deleted from the database.
 *
 * @param string $p_option config option
 * @return bool
 */
function config_can_delete( $p_option ) {
	return( strtolower( $p_option ) != 'database_version' );
}

/**
 * delete the config entry
 *
 * @param string $p_option config option
 * @param int $p_user user id
 * @param int $p_project project id
 */
function config_delete( $p_option, $p_user = ALL_USERS, $p_project = ALL_PROJECTS ) {
	# bypass table lookup for certain options
	$t_bypass_lookup = !config_can_set_in_database( $p_option );

	if(( !$t_bypass_lookup ) && ( TRUE === db_is_connected() ) && ( db_table_exists( db_get_table( 'config' ) ) ) ) {
		if( !config_can_delete( $p_option ) ) {
			return;
		}

		$t_config_table = db_get_table( 'config' );
		$query = "DELETE FROM $t_config_table
				WHERE config_id = " . db_param() . " AND
					project_id=" . db_param() . " AND
					user_id=" . db_param();
		db_query_bound( $query, array( $p_option, $p_project, $p_user ) );
	}

	config_flush_cache( $p_option, $p_user, $p_project );
}

/**
 * Delete the specified option for the specified user across all projects.
 *
 * @param string $p_option - The configuration option to be deleted.
 * @param int $p_user_id - The user id
 */
function config_delete_for_user( $p_option, $p_user_id ) {
	if( !config_can_delete( $p_option ) ) {
		return;
	}

	# Delete the corresponding bugnote texts
	$t_config_table = db_get_table( 'config' );
	$query = "DELETE FROM $t_config_table
					WHERE config_id=" . db_param() . " AND user_id=" . db_param();
	db_query_bound( $query, array( $p_option, $p_user_id ) );
}

/**
 * delete the config entry
 *
 * @param int $p_project project id
 */
function config_delete_project( $p_project = ALL_PROJECTS ) {
	$t_config_table = db_get_table( 'config' );
	$t_query = "DELETE FROM $t_config_table WHERE project_id=" . db_param();
	db_query_bound( $t_query, array( $p_project ) );

	# flush cache here in case some of the deleted configs are in use.
	config_flush_cache();
}

/**
 * delete the config entry from the cache
 * @@@ to be used sparingly
 *
 * @param string $p_option config option
 * @param int $p_user user id
 * @param int $p_project project id
 */
function config_flush_cache( $p_option = '', $p_user = ALL_USERS, $p_project = ALL_PROJECTS ) {
	global $g_cache_filled;

	if( '' !== $p_option ) {
		unset( $GLOBALS['g_cache_config'][$p_option][$p_user][$p_project] );
		unset( $GLOBALS['g_cache_config_access'][$p_option][$p_user][$p_project] );
	} else {
		unset( $GLOBALS['g_cache_config'] );
		unset( $GLOBALS['g_cache_config_access'] );
		$g_cache_filled = false;
	}
}

/**
 * Checks if an obsolete configuration variable is still in use.  If so, an error
 * will be generated and the script will exit.
 *
 * @param string $p_var old config option
 * @param string $p_replace new config option
 */
function config_obsolete( $p_var, $p_replace = '' ) {
	global $g_cache_config;

	# @@@ we could trigger a WARNING here, once we have errors that can
	#     have extra data plugged into them (we need to give the old and
	#     new config option names in the warning text)

	if( config_is_set( $p_var ) ) {
		$t_description = 'The configuration option <em>' . $p_var . '</em> is now obsolete';
		$t_info = '';

		// Check if set in the database
		if( is_array( $g_cache_config ) && array_key_exists( $p_var, $g_cache_config ) ) {
			$t_info .= 'it is currently defined in ';
			if( isset( $GLOBALS['g_' . $p_var] ) ) {
				$t_info .= 'config_inc.php, as well as in ';
			}
			$t_info .= 'the database configuration for: <ul>';

			foreach( $g_cache_config[$p_var] as $t_user_id => $t_user ) {
				$t_info .= '<li>'
					. (($t_user_id == 0)? lang_get('all_users') : user_get_name( $t_user_id ))
					. ': ';
				foreach ( $t_user as $t_project_id => $t_project ) {
					$t_info .= project_get_name( $t_project_id ) . ', ';
				}
				$t_info = rtrim( $t_info, ', ') . '</li>';
			}
			$t_info .= '</ul>';
		}

		// Replacement defined
		if( is_array( $p_replace ) ) {
			$t_info .= 'please see the following options: <ul>';
			foreach( $p_replace as $t_option ) {
				$t_info .= '<li>' . $t_option . '</li>';
			}
			$t_info .= '</ul>';
		}
		else if( !is_blank( $p_replace ) ) {
			$t_info .= 'please use ' . $p_replace . ' instead.';
		}

		check_print_test_warn_row( $t_description, false, $t_info );
	}
}

/**
 * Checks if an obsolete environment variable is set.
 * If so, an error will be generated and the script will exit.
 *
 * @param string $p_env_variable old variable
 * @param string $p_new_env_variable new variable
 */
function env_obsolete( $p_env_variable, $p_new_env_variable ) {
	$t_env = getenv( $p_env_variable );
	if ( $t_env ) {
		$t_description = 'Environment variable <em>' . $p_env_variable . '</em> is obsolete.';
		$t_info = 'please use ' . $p_new_env_variable . ' instead.';
		check_print_test_warn_row( $t_description, false, $t_info );
	}
}

/**
 * check for recursion in defining config variables
 * If there is a %text% in the returned value, re-evaluate the "text" part and replace the string
 *
 * @param string $p_value config variable to evaluate
 * @param bool $p_global if true, gets %text% as a global config, defaults to false
 * @return string
 */
function config_eval( $p_value, $p_global = false ) {
	$t_value = $p_value;
	if( !empty( $t_value ) && is_string( $t_value ) && !is_numeric( $t_value ) ) {
		if( 0 < preg_match_all( '/(?:^|[^\\\\])(%([^%]+)%)/U', $t_value, $t_matches ) ) {
			$t_count = count( $t_matches[0] );
			for( $i = 0;$i < $t_count;$i++ ) {

				# $t_matches[0][$i] is the matched string including the delimiters
				# $t_matches[1][$i] is the target parameter string
				if( $p_global ) {
					$t_repl = config_get_global( $t_matches[2][$i] );
				} else {
					$t_repl = config_get( $t_matches[2][$i] );
				}

				# Handle the simple case where there is no need to do string replace.
				# This will resolve the case where the $t_repl value is of non-string
				# type, e.g. array of access levels.
				if( $t_count == 1 && $p_value == '%' . $t_matches[2][$i] . '%' ) {
					$t_value = $t_repl;
					break;
				}

				$t_value = str_replace( $t_matches[1][$i], $t_repl, $t_value );
			}
		}
		$t_value = str_replace( '\\%', '%', $t_value );
	}
	return $t_value;
}

/**
 * list of configuration variable which may expose webserver details and should not be exposed to users or webservices
 *
 * @param string $p_config_var config option
 * @return bool
 */
function config_is_private( $p_config_var ) {
	switch( $p_config_var ) {
		case 'hostname':
		case 'db_username':
		case 'db_password':
		case 'database_name':
		case 'db_schema':
		case 'db_type':
		case 'master_crypto_salt':
		case 'smtp_host':
		case 'smtp_username':
		case 'smtp_password':
		case 'smtp_connection_mode':
		case 'smtp_port':
		case 'email_send_using_cronjob':
		case 'absolute_path':
		case 'core_path':
		case 'class_path':
		case 'library_path':
		case 'language_path':
		case 'session_save_path':
		case 'session_handler':
		case 'session_validation':
		case 'global_settings':
		case 'system_font_folder':
		case 'phpMailer_method':
		case 'attachments_file_permissions':
		case 'file_upload_method':
		case 'absolute_path_default_upload_folder':
		case 'ldap_server':
		case 'plugin_path':
		case 'ldap_root_dn':
		case 'ldap_organization':
		case 'ldap_uid_field':
		case 'ldap_bind_dn':
		case 'ldap_bind_passwd':
		case 'use_ldap_email':
		case 'ldap_protocol_version':
		case 'login_method':
		case 'cookie_path':
		case 'cookie_domain':
		case 'bottom_include_page':
		case 'top_include_page':
		case 'css_include_file':
		case 'css_rtl_include_file':
		case 'meta_include_file':
		case 'log_level':
		case 'log_destination':
		case 'dot_tool':
		case 'neato_tool':
			return true;

		// Marked obsolete in 1.3.0dev - keep here to make sure they are not disclosed by soap api.
		// These can be removed once complete removal from config and db is enforced by upgrade process.
		case 'file_upload_ftp_server':
		case 'file_upload_ftp_user':
		case 'file_upload_ftp_pass':
			return true;
	}

	return false;
}

