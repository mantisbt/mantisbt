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

# cache environment to speed up lookups
$g_cache_db_table_exists = false;

$g_cache_config_user = null;
$g_cache_config_project = null;

/**
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
 * @param string  $p_option  The configuration option to retrieve.
 * @param string  $p_default The default value to use if not set.
 * @param integer $p_user    A user identifier.
 * @param integer $p_project A project identifier.
 * @return mixed
 */
function config_get( $p_option, $p_default = null, $p_user = null, $p_project = null ) {
	global $g_cache_config, $g_cache_db_table_exists, $g_cache_filled;
	global $g_cache_config_user, $g_cache_config_project, $g_project_override;

	if( config_can_set_in_database( $p_option ) ) {
		if( $g_project_override !== null && $p_project === null ) {
			$p_project = $g_project_override;
		}
		# @@ debug @@ if( ! db_is_connected() ) { echo "no db "; }
		# @@ debug @@ echo "lu table=" . ( db_table_exists( $t_config_table ) ? "yes " : "no " );
		if( !$g_cache_db_table_exists ) {
			$g_cache_db_table_exists = ( true === db_is_connected() ) && db_table_exists( db_get_table( 'config' ) );
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
			if( ( null === $p_project ) ) {
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
				config_cache_all();
				$g_cache_filled = true;
			}

			if( isset( $g_cache_config[$p_option] ) ) {
				$t_found = false;
				foreach( $t_users as $t_user ) {
					foreach( $t_projects as $t_project ) {
						if( isset( $g_cache_config[$p_option][$t_user][$t_project] ) ) {
							$t_value = $g_cache_config[$p_option][$t_user][$t_project];
							$t_found = true;
							# @@ debug @@ echo "clu found u=$t_user, p=$t_project, v=$t_value ";
							break 2;
						}
					}
				}

				if( $t_found ) {
					list( $t_type, $t_raw_value ) = explode( ';', $t_value, 2 );

					switch( $t_type ) {
						case CONFIG_TYPE_FLOAT:
							return (float)$t_raw_value;
						case CONFIG_TYPE_INT:
							return (int)$t_raw_value;
						case CONFIG_TYPE_COMPLEX:
							return json_decode( $t_raw_value, true );
						case CONFIG_TYPE_STRING:
						default:
							return config_eval( $t_raw_value );
					}
				}
			}
		}
	}
	return config_get_global( $p_option, $p_default );
}

/**
 * force config variable from a global to avoid recursion
 *
 * @param string $p_option  Configuration option to retrieve.
 * @param string $p_default Default value if not set.
 * @return mixed
 */
function config_get_global( $p_option, $p_default = null ) {
	global $g_cache_config_eval;

	$t_var_name = 'g_' . $p_option;
	if( isset( $GLOBALS[$t_var_name] ) ) {
		if( !isset( $g_cache_config_eval[$t_var_name] ) ) {
			$t_value = config_eval( $GLOBALS[$t_var_name], true );
			$g_cache_config_eval[$t_var_name] = $t_value;
		} else {
			$t_value = $g_cache_config_eval[$t_var_name];
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
 * Retrieves the access level needed to change a configuration value
 *
 * @param string  $p_option  Configuration option.
 * @param integer $p_user    A user identifier.
 * @param integer $p_project A project identifier.
 * @return integer
 */
function config_get_access( $p_option, $p_user = null, $p_project = null ) {
	global $g_cache_config, $g_cache_config_access, $g_cache_filled;

	if( !$g_cache_filled ) {
		config_get( $p_option, -1, $p_user, $p_project );
	}

	# prepare the user's list
	$t_users = array();
	if( ( null === $p_user ) && ( auth_is_user_authenticated() ) ) {
		$t_users[] = auth_get_current_user_id();
	} else if( !in_array( $p_user, $t_users ) ) {
		$t_users[] = $p_user;
	}
	$t_users[] = ALL_USERS;

	# prepare the projects list
	$t_projects = array();
	if( ( null === $p_project ) && ( auth_is_user_authenticated() ) ) {
		$t_selected_project = helper_get_current_project();
		$t_projects[] = $t_selected_project;
		if( ALL_PROJECTS <> $t_selected_project ) {
			$t_projects[] = ALL_PROJECTS;
		}
	} else if( !in_array( $p_project, $t_projects ) ) {
		$t_projects[] = $p_project;
	}

	if( isset( $g_cache_config[$p_option] ) ) {
		foreach( $t_users as $t_user ) {
			foreach( $t_projects as $t_project ) {
				if( isset( $g_cache_config[$p_option][$t_user][$t_project] ) ) {
					return $g_cache_config_access[$p_option][$t_user][$t_project];
				}
			}
		}
	}

	return config_get_global( 'admin_site_threshold' );
}

/**
 * Returns true if the specified configuration option exists (Either a
 * value or default can be found), false otherwise
 *
 * @param string  $p_option  Configuration option.
 * @param integer $p_user    A user identifier.
 * @param integer $p_project A project identifier.
 * @return boolean
 */
function config_is_set( $p_option, $p_user = null, $p_project = null ) {
	global $g_cache_config, $g_cache_filled;

	if( !$g_cache_filled ) {
		config_get( $p_option, -1, $p_user, $p_project );
	}

	# prepare the user's list
	$t_users = array( ALL_USERS );
	if( ( null === $p_user ) && ( auth_is_user_authenticated() ) ) {
		$t_users[] = auth_get_current_user_id();
	} else if( !in_array( $p_user, $t_users ) ) {
		$t_users[] = $p_user;
	}
	$t_users[] = ALL_USERS;

	# prepare the projects list
	$t_projects = array( ALL_PROJECTS );
	if( ( null === $p_project ) && ( auth_is_user_authenticated() ) ) {
		$t_selected_project = helper_get_current_project();
		if( ALL_PROJECTS <> $t_selected_project ) {
			$t_projects[] = $t_selected_project;
		}
	} else if( !in_array( $p_project, $t_projects ) ) {
		$t_projects[] = $p_project;
	}

	foreach( $t_users as $t_user ) {
		foreach( $t_projects as $t_project ) {
			if( isset( $g_cache_config[$p_option][$t_user][$t_project] ) ) {
				return true;
			}
		}
	}

	return isset( $GLOBALS['g_' . $p_option] );
}

/**
 * Sets the value of the given configuration option to the given value
 * If the configuration option does not exist, an ERROR is triggered
 *
 * @param string  $p_option  Configuration option name.
 * @param string  $p_value   Configuration option value.
 * @param integer $p_user    A user identifier. Defaults to NO_USER.
 * @param integer $p_project A project identifier. Defaults to ALL_PROJECTS.
 * @param integer $p_access  Access level. Defaults to DEFAULT_ACCESS_LEVEL.
 * @return boolean
 */
function config_set( $p_option, $p_value, $p_user = NO_USER, $p_project = ALL_PROJECTS, $p_access = DEFAULT_ACCESS_LEVEL ) {
	if( $p_access == DEFAULT_ACCESS_LEVEL ) {
		$p_access = config_get_global( 'admin_site_threshold' );
	}
	if( is_array( $p_value ) || is_object( $p_value ) ) {
		$t_type = CONFIG_TYPE_COMPLEX;
		$c_value = json_encode( $p_value );
	} else if( is_float( $p_value ) ) {
		$t_type = CONFIG_TYPE_FLOAT;
		$c_value = (float)$p_value;
	} else if( is_int( $p_value ) || is_numeric( $p_value ) ) {
		$t_type = CONFIG_TYPE_INT;
		$c_value = (int)$p_value;
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

		db_param_push();
		$t_query = 'SELECT COUNT(*) from {config}
				WHERE config_id = ' . db_param() . ' AND
					project_id = ' . db_param() . ' AND
					user_id = ' . db_param();
		$t_result = db_query( $t_query, array( $p_option, (int)$p_project, (int)$p_user ) );

		db_param_push();
		if( 0 < db_result( $t_result ) ) {
			$t_set_query = 'UPDATE {config}
					SET value=' . db_param() . ', type=' . db_param() . ', access_reqd=' . db_param() . '
					WHERE config_id = ' . db_param() . ' AND
						project_id = ' . db_param() . ' AND
						user_id = ' . db_param();
			$t_params = array(
				(string)$c_value,
				$t_type,
				(int)$p_access,
				$p_option,
				(int)$p_project,
				(int)$p_user,
			);
		} else {
			$t_set_query = 'INSERT INTO {config}
					( value, type, access_reqd, config_id, project_id, user_id )
					VALUES
					(' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ',' . db_param() . ' )';
			$t_params = array(
				(string)$c_value,
				$t_type,
				(int)$p_access,
				$p_option,
				(int)$p_project,
				(int)$p_user,
			);
		}

		db_query( $t_set_query, $t_params );
	}

	config_set_cache( $p_option, $c_value, $t_type, $p_user, $p_project, $p_access );

	return true;
}

/**
 * Sets the value of the given configuration option in the global namespace.
 * Does *not* persist the value between sessions. If override set to
 * false, then the value will only be set if not already existent.
 *
 * @param string  $p_option   Configuration option.
 * @param string  $p_value    Configuration value.
 * @param boolean $p_override Override existing value if already set.
 * @return boolean
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
 * Sets the value of the given configuration option to the given value
 *  If the configuration option does not exist, an ERROR is triggered
 *
 * @param string  $p_option  Configuration option.
 * @param string  $p_value   Configuration value.
 * @param integer $p_type    Type.
 * @param integer $p_user    A user identifier.
 * @param integer $p_project A project identifier.
 * @param integer $p_access  Access level.
 * @return boolean
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
 * @param string $p_option Configuration option.
 * @return boolean
 */
function config_can_set_in_database( $p_option ) {
	global $g_cache_bypass_lookup, $g_global_settings;

	if( isset( $g_cache_bypass_lookup[$p_option] ) ) {
		return !$g_cache_bypass_lookup[$p_option];
	}

	# bypass table lookup for certain options
	$t_bypass_lookup = in_array( $p_option, $g_global_settings, true );

	$g_cache_bypass_lookup[$p_option] = $t_bypass_lookup;

	return !$t_bypass_lookup;
}

/**
 * Checks if the specific configuration option can be deleted from the database.
 *
 * @param string $p_option Configuration option.
 * @return boolean
 */
function config_can_delete( $p_option ) {
	return( strtolower( $p_option ) != 'database_version' );
}

/**
 * delete the configuration entry
 *
 * @param string  $p_option  Configuration option.
 * @param integer $p_user    A user identifier.
 * @param integer $p_project A project identifier.
 * @return void
 */
function config_delete( $p_option, $p_user = ALL_USERS, $p_project = ALL_PROJECTS ) {
	# bypass table lookup for certain options
	$t_bypass_lookup = !config_can_set_in_database( $p_option );

	if( ( !$t_bypass_lookup ) && ( true === db_is_connected() ) && ( db_table_exists( db_get_table( 'config' ) ) ) ) {
		if( !config_can_delete( $p_option ) ) {
			return;
		}

		db_param_push();
		$t_query = 'DELETE FROM {config}
				WHERE config_id = ' . db_param() . ' AND
					project_id=' . db_param() . ' AND
					user_id=' . db_param();
		db_query( $t_query, array( $p_option, $p_project, $p_user ) );
	}

	config_flush_cache( $p_option, $p_user, $p_project );
}

/**
 * Delete the specified option for the specified user across all projects.
 *
 * @param string  $p_option  The configuration option to be deleted.
 * @param integer $p_user_id The user id.
 * @return void
 */
function config_delete_for_user( $p_option, $p_user_id ) {
	if( !config_can_delete( $p_option ) ) {
		return;
	}

	# Delete the corresponding bugnote texts
	db_param_push();
	$t_query = 'DELETE FROM {config} WHERE config_id=' . db_param() . ' AND user_id=' . db_param();
	db_query( $t_query, array( $p_option, $p_user_id ) );
}

/**
 * delete the config entry
 *
 * @param integer $p_project A project identifier.
 * @return void
 */
function config_delete_project( $p_project = ALL_PROJECTS ) {
	db_param_push();
	$t_query = 'DELETE FROM {config} WHERE project_id=' . db_param();
	db_query( $t_query, array( $p_project ) );

	# flush cache here in case some of the deleted configs are in use.
	config_flush_cache();
}

/**
 * delete the configuration entry from the cache
 * @@@ to be used sparingly
 *
 * @param string  $p_option  Configuration option.
 * @param integer $p_user    A user identifier.
 * @param integer $p_project A project identifier.
 * @return void
 */
function config_flush_cache( $p_option = '', $p_user = ALL_USERS, $p_project = ALL_PROJECTS ) {
	global $g_cache_filled;

	if( '' !== $p_option ) {
		unset( $GLOBALS['g_cache_config'][$p_option][$p_user][$p_project] );
		unset( $GLOBALS['g_cache_config_access'][$p_option][$p_user][$p_project] );
		unset( $GLOBALS['g_cache_config_eval'][$p_option] );
	} else {
		unset( $GLOBALS['g_cache_config'] );
		unset( $GLOBALS['g_cache_config_access'] );
		unset( $GLOBALS['g_cache_config_eval'] );
		$g_cache_filled = false;
	}
}

/**
 * Checks if an obsolete configuration variable is still in use.  If so, an error
 * will be generated and the script will exit.
 *
 * @param string $p_var     Old configuration option.
 * @param string $p_replace New configuration option.
 * @return void
 */
function config_obsolete( $p_var, $p_replace = '' ) {
	global $g_cache_config;

	# @@@ we could trigger a WARNING here, once we have errors that can
	#     have extra data plugged into them (we need to give the old and
	#     new config option names in the warning text)

	if( config_is_set( $p_var ) ) {
		$t_description = 'The configuration option <em>' . $p_var . '</em> is now obsolete';
		$t_info = '';

		# Check if set in the database
		if( is_array( $g_cache_config ) && array_key_exists( $p_var, $g_cache_config ) ) {
			$t_info .= 'it is currently defined in ';
			if( isset( $GLOBALS['g_' . $p_var] ) ) {
				$t_info .= 'config_inc.php, as well as in ';
			}
			$t_info .= 'the database configuration for: <ul>';

			foreach( $g_cache_config[$p_var] as $t_user_id => $t_user ) {
				$t_info .= '<li>'
					. ( ( $t_user_id == 0 ) ? lang_get( 'all_users' ) : user_get_name( $t_user_id ) )
					. ': ';
				foreach ( $t_user as $t_project_id => $t_project ) {
					$t_info .= project_get_name( $t_project_id ) . ', ';
				}
				$t_info = rtrim( $t_info, ', ' ) . '</li>';
			}
			$t_info .= '</ul>';
		}

		# Replacement defined
		if( is_array( $p_replace ) ) {
			$t_info .= 'please see the following options: <ul>';
			foreach( $p_replace as $t_option ) {
				$t_info .= '<li>' . $t_option . '</li>';
			}
			$t_info .= '</ul>';
		} else if( !is_blank( $p_replace ) ) {
			$t_info .= 'please use ' . $p_replace . ' instead.';
		}

		check_print_test_warn_row( $t_description, false, $t_info );
	}
}

/**
 * Checks if an obsolete environment variable is set.
 * If so, an error will be generated and the script will exit.
 *
 * @param string $p_env_variable     Old variable.
 * @param string $p_new_env_variable New variable.
 * @return void
 */
function env_obsolete( $p_env_variable, $p_new_env_variable ) {
	$t_env = getenv( $p_env_variable );
	if( $t_env ) {
		$t_description = 'Environment variable <em>' . $p_env_variable . '</em> is obsolete.';
		$t_info = 'please use ' . $p_new_env_variable . ' instead.';
		check_print_test_warn_row( $t_description, false, $t_info );
	}
}

/**
 * Check for recursion in defining configuration variables.
 *
 * If there is a %text% in the returned value, re-evaluate the "text" part and
 * replace the string. It is possible to escape the '%' with backslash when
 * evaluation is not wanted, e.g. '\%test\%'.
 *
 * @param string  $p_value  Configuration variable to evaluate.
 * @param boolean $p_global If true, gets %text% as a global configuration, defaults to false.
 *
 * @return string
 */
function config_eval( $p_value, $p_global = false ) {
	$t_value = $p_value;
	if( !empty( $t_value ) && is_string( $t_value ) && !is_numeric( $t_value ) ) {
		$t_count = preg_match_all(
			'/(?:^|[^\\\\])(%([^%]+)%)/U',
			$t_value,
			$t_matches,
			PREG_SET_ORDER
		);

		if( $t_count > 0 ) {
			foreach( $t_matches as $t_match ) {
				list(, $t_match_with_delimiters, $t_config ) = $t_match;

				# Make sure the config actually exists before retrieving it
				if( !isset( $GLOBALS['g_' . $t_config ] ) ) {
					continue;
				}

				if( $p_global ) {
					$t_repl = config_get_global( $t_config );
				} else {
					$t_repl = config_get( $t_config );
				}

				# Handle the simple case where there is no need to do string replace.
				# This will resolve the case where the $t_repl value is of non-string
				# type, e.g. array of access levels.
				if( $t_count == 1 && $p_value == $t_match_with_delimiters ) {
					$t_value = $t_repl;
					break;
				}

				$t_value = str_replace( $t_match_with_delimiters, $t_repl, $t_value );
			}
		}

		# Remove escaped '%'
		$t_value = str_replace( '\\%', '%', $t_value );
	}
	return $t_value;
}

/**
 * Check if a configuration is private.
 * Private options must not be exposed to users or web services.
 *
 * @param string $p_config_var Configuration option.
 * @return boolean True if private
 */
function config_is_private( $p_config_var ) {
	global $g_public_config_names;

	return !in_array( $p_config_var, $g_public_config_names, true );
}

/**
 * Check if a configuration is defined in the database with the given value.
 *
 * @param string  $p_option  The configuration option to retrieve.
 * @param string  $p_value   Value to check for (defaults to null = any value).
 * @return boolean True if option is defined
 */
function config_is_defined( $p_option, $p_value = null ) {
	global $g_cache_filled, $g_cache_config;

	if( !$g_cache_filled ) {
		config_get( $p_option, -1 );
	}

	if( !isset( $g_cache_config[$p_option] ) ) {
		# Option is not in cache
		return false;
	} elseif( $p_value === null ) {
		# We're not checking any specific value
		return true;
	}

	# Check the cache for specified value
	foreach( $g_cache_config[$p_option] as $t_project ) {
		foreach( $t_project as $t_opt ) {
			list( , $t_value ) = explode( ';', $t_opt );
			if( $t_value == $p_value ) {
				return true;
			}
		}
	}

	# Value not found in cache
	return false;
}

/**
 * Loads the contents of config table into cache
 * @return void
 */
function config_cache_all() {
	global $g_cache_config, $g_cache_config_access;

	$t_config_rows = array();

	# With oracle database, ADOdb maps column type "L" to clob.
	# Because reading clobs is significantly slower, cast them to varchar for faster query execution
	# Standard max size for varchar is 4000 bytes, so a safe limit is used as 1000 charancters
	# for multibyte strings (up to 4 bytes per char)
	if( db_is_oracle() ) {
		$t_query = 'SELECT config_id, user_id, project_id, type, CAST(value AS VARCHAR(4000)) AS value, access_reqd'
				. ' FROM {config}'
				. ' WHERE dbms_lob.getlength(value)<=1000';
		$t_result = db_query( $t_query );
		while( $t_row = db_fetch_array( $t_result ) ) {
			$t_config_rows[] = $t_row;
		}
		$t_query = 'SELECT config_id, user_id, project_id, type,  value, access_reqd'
				. ' FROM {config}'
				. ' WHERE dbms_lob.getlength(value)>1000';
		$t_result = db_query( $t_query );
		while( $t_row = db_fetch_array( $t_result ) ) {
			$t_config_rows[] = $t_row;
		}
	} else {
		$t_query = 'SELECT config_id, user_id, project_id, type,  value, access_reqd FROM {config}';
		$t_result = db_query( $t_query );
		while( false <> ( $t_row = db_fetch_array( $t_result ) ) ) {
			$t_config_rows[] = $t_row;
		}
	}

	foreach( $t_config_rows as $t_row ) {
		$t_config = $t_row['config_id'];
		$t_user = $t_row['user_id'];
		$t_project = $t_row['project_id'];
		$g_cache_config[$t_config][$t_user][$t_project] = $t_row['type'] . ';' . $t_row['value'];
		$g_cache_config_access[$t_config][$t_user][$t_project] = $t_row['access_reqd'];
	}
}

/**
 * Display a given config value appropriately
 * @param integer $p_type        Configuration type id.
 * @param mixed   $p_value       Configuration value.
 * @param boolean $p_for_display Whether to pass the value via string attribute for web browser display.
 * @return string
 */
function config_get_value_as_string( $p_type, $p_value, $p_for_display = true ) {
	$t_corrupted = false;

	switch( $p_type ) {
		case CONFIG_TYPE_DEFAULT:
			return '';
		case CONFIG_TYPE_FLOAT:
			return (string)(float)$p_value;
		case CONFIG_TYPE_INT:
			return (string)(integer)$p_value;
		case CONFIG_TYPE_STRING:
			$t_value = string_html_specialchars( config_eval( $p_value ) );
			if( $p_for_display ) {
				$t_value = '<p id="adm-config-value">\'' . string_nl2br( $t_value ) . '\'</p>';
			}
			return $t_value;
		case CONFIG_TYPE_COMPLEX:
			$t_value = @json_decode( $p_value, true );
			if( $t_value === false ) {
				$t_corrupted = true;
			}
			break;
		default:
			$t_value = config_eval( $p_value );
			break;
	}

	if( $t_corrupted ) {
		$t_output = $p_for_display ? lang_get( 'configuration_corrupted' ) : '';
	} else {
		$t_output = var_export( $t_value, true );
	}

	if( $p_for_display ) {
		return '<pre id="adm-config-value">' . string_attribute( $t_output ) . '</pre>';
	} else {
		return string_attribute( $t_output );
	}
}

function config_get_types() {
	return array(
		CONFIG_TYPE_DEFAULT => 'default',
		CONFIG_TYPE_INT     => 'integer',
		CONFIG_TYPE_FLOAT   => 'float',
		CONFIG_TYPE_COMPLEX => 'complex',
		CONFIG_TYPE_STRING  => 'string',
		);
}

/**
 * returns the configuration type for a given configuration type id
 * @param integer $p_type Configuration type identifier to check.
 * @return string configuration type
 */
function config_get_type_string( $p_type ) {
	$t_config_types = config_get_types();
	if( array_key_exists( $p_type, $t_config_types ) ) {
		return $t_config_types[$p_type];
	} else {
		return $t_config_types[CONFIG_TYPE_DEFAULT];
	}
}
