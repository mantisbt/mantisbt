<?php
# MantisBT - a php based bugtracking system

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
 * @package CoreAPI
 * @subpackage ConfigurationAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * requires error_api
 */
require_once( 'error_api.php' );

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

# ## Configuration API ###
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
	global $g_cache_config, $g_cache_config_access, $g_cache_db_table_exists, $g_cache_filled;
	global $g_cache_config_user, $g_cache_config_project, $g_project_override;

	# @@ debug @@ echo "lu o=$p_option ";
	# bypass table lookup for certain options
	$t_bypass_lookup = !config_can_set_in_database( $p_option );

	# @@ debug @@ if ($t_bypass_lookup) { echo "bp=$p_option match=$t_match_pattern <br />"; }

	if( !$t_bypass_lookup ) {
		if( $g_project_override != null && $p_project == null ) {
			$p_project = $g_project_override;
		}
		# @@ debug @@ if ( ! db_is_connected() ) { echo "no db "; }
		# @@ debug @@ echo "lu table=" . ( db_table_exists( $t_config_table ) ? "yes " : "no " );
		if( !$g_cache_db_table_exists ) {
			$t_config_table = db_get_table( 'mantis_config_table' );
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
				$t_config_table = db_get_table( 'mantis_config_table' );
				$query = "SELECT config_id, user_id, project_id, type, value, access_reqd FROM $t_config_table";
				$result = db_query_bound( $query );
				while( false <> ( $row = db_fetch_array( $result ) ) ) {
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

# ----------------------
# force config variable from a global to avoid recursion
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

# ------------------
# Retrieves the access level needed to change a config value
function config_get_access( $p_option, $p_user = null, $p_project = null ) {
	global $g_cache_config, $g_cache_config_access, $g_cache_filled;

	# @@ debug @@ echo "lu o=$p_option ";

	if( !$g_cache_filled ) {
		$t = config_get( $p_option, -1, $p_user, $p_project );
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
		if( ALL_PROJECTS <> $t_selected_project ) {
			$t_projects[] = $t_selected_project;
		}
	}
	else if( !in_array( $p_project, $t_projects ) ) {
		$t_projects[] = $p_project;
	}

	# @@ debug @@ echo 'pr= '; var_dump($t_projects);
	# @@ debug @@ echo 'u= '; var_dump($t_users);

	$t_found = false;
	if( isset( $g_cache_config[$p_option] ) ) {
		reset( $t_users );
		while(( list(, $t_user ) = each( $t_users ) ) && !$t_found ) {
			reset( $t_projects );
			while(( list(, $t_project ) = each( $t_projects ) ) && !$t_found ) {
				if( isset( $g_cache_config[$p_option][$t_user][$t_project] ) ) {
					$t_access = $g_cache_config_access[$p_option][$t_user][$t_project];
					$t_found = true;

					# @@ debug @@ echo "clua found u=$t_user, p=$t_project, a=$t_access ";
				}
			}
		}
	}

	return $t_found ? $t_access : config_get_global( 'admin_site_threshold' );
}

# ------------------
# Returns true if the specified config option exists (ie. a
#  value or default can be found), false otherwise
function config_is_set( $p_option, $p_user = null, $p_project = null ) {
	global $g_cache_config, $g_cache_filled;

	if( !$g_cache_filled ) {
		$t = config_get( $p_option, -1, $p_user, $p_project );
	}

	# prepare the user's list
	$t_users = array(
		ALL_USERS,
	);
	if(( null === $p_user ) && ( auth_is_user_authenticated() ) ) {
		$t_users[] = auth_get_current_user_id();
	}
	else if( !in_array( $p_user, $t_users ) ) {
		$t_users[] = $p_user;
	}
	$t_users[] = ALL_USERS;

	# prepare the projects list
	$t_projects = array(
		ALL_PROJECTS,
	);
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

# ------------------
# Sets the value of the given config option to the given value
#  If the config option does not exist, an ERROR is triggered
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
		$c_value = db_prepare_int( $p_value );
	} else {
		$t_type = CONFIG_TYPE_STRING;
		$c_value = $p_value;
	}

	if( config_can_set_in_database( $p_option ) ) {
		$c_option = $p_option;
		$c_user = db_prepare_int( $p_user );
		$c_project = db_prepare_int( $p_project );
		$c_access = db_prepare_int( $p_access );

		$t_config_table = db_get_table( 'mantis_config_table' );
		$query = "SELECT COUNT(*) from $t_config_table
				WHERE config_id = " . db_param() . " AND
					project_id = " . db_param() . " AND
					user_id = " . db_param();
		$result = db_query_bound( $query, Array( $c_option, $c_project, $c_user ) );

		$t_params = Array();
		if( 0 < db_result( $result ) ) {
			$t_set_query = "UPDATE $t_config_table
					SET value=" . db_param() . ", type=" . db_param() . ", access_reqd=" . db_param() . "
					WHERE config_id = " . db_param() . " AND
						project_id = " . db_param() . " AND
						user_id = " . db_param();
			$t_params = Array(
				(string)$c_value,
				$t_type,
				$c_access,
				$c_option,
				$c_project,
				$c_user,
			);
		} else {
			$t_set_query = "INSERT INTO $t_config_table
					( value, type, access_reqd, config_id, project_id, user_id )
					VALUES
					(" . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ',' . db_param() . ' )';
			$t_params = Array(
				(string)$c_value,
				$t_type,
				$c_access,
				$c_option,
				$c_project,
				$c_user,
			);
		}

		$result = db_query_bound( $t_set_query, $t_params );
	}

	config_set_cache( $p_option, $c_value, $t_type, $p_user, $p_project, $p_access );

	return true;
}

# ------------------
# Sets the value of the given config option in the global namespace.
#  Does *not* persist the value between sessions. If override set to
#  false, then the value will only be set if not already existent.
function config_set_global( $p_option, $p_value, $p_override = true ) {
	global $g_cache_config_eval;

	if( $p_override || !isset( $GLOBALS['g_' . $p_option] ) ) {
		$GLOBALS['g_' . $p_option] = $p_value;
		unset( $g_cache_config_eval['g_' . $p_option] );
	}

	return true;
}

# ------------------
# Sets the value of the given config option to the given value
#  If the config option does not exist, an ERROR is triggered
function config_set_cache( $p_option, $p_value, $p_type, $p_user = NO_USER, $p_project = ALL_PROJECTS, $p_access = DEFAULT_ACCESS_LEVEL ) {
	global $g_cache_config, $g_cache_config_access;

	if( $p_access == DEFAULT_ACCESS_LEVEL ) {
		$p_access = config_get_global( 'admin_site_threshold' );
	}

	$g_cache_config[$p_option][$p_user][$p_project] = $p_type . ';' . $p_value;
	$g_cache_config_access[$p_option][$p_user][$p_project] = $p_access;

	return true;
}

# ------------------
# Checks if the specific configuration option can be set in the database, otherwise it can only be set
# in the configuration file (config_inc.php / config_defaults_inc.php).
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

# ------------------
# Checks if the specific configuration option can be deleted from the database.
function config_can_delete( $p_option ) {
	return( utf8_strtolower( $p_option ) != 'database_version' );
}

# ------------------
# delete the config entry
function config_delete( $p_option, $p_user = ALL_USERS, $p_project = ALL_PROJECTS ) {
	global $g_cache_config, $g_cache_config_access;

	# bypass table lookup for certain options
	$t_bypass_lookup = !config_can_set_in_database( $p_option );

	# @@ debug @@ if ($t_bypass_lookup) { echo "bp=$p_option match=$t_match_pattern <br />"; }
	# @@ debug @@ if ( ! db_is_connected() ) { echo "no db"; }

	if(( !$t_bypass_lookup ) && ( TRUE === db_is_connected() ) && ( db_table_exists( db_get_table( 'mantis_config_table' ) ) ) ) {
		if( !config_can_delete( $p_option ) ) {
			return;
		}

		$t_config_table = db_get_table( 'mantis_config_table' );

		# @@ debug @@ echo "lu table=" . ( db_table_exists( $t_config_table ) ? "yes" : "no" );
		# @@ debug @@ error_print_stack_trace();

		$c_user = db_prepare_int( $p_user );
		$c_project = db_prepare_int( $p_project );
		$query = "DELETE FROM $t_config_table
				WHERE config_id = " . db_param() . " AND
					project_id=" . db_param() . " AND
					user_id=" . db_param();

		$result = @db_query_bound( $query, Array( $p_option, $c_project, $c_user ) );
	}

	config_flush_cache( $p_option, $p_user, $p_project );
}

/**
 * Delete the specified option for the specified user.across all projects.
 * @param $p_option - The configuration option to be deleted.
 * @param $p_user_id - The user id
 */
function config_delete_for_user( $p_option, $p_user_id ) {
	if( !config_can_delete( $p_option ) ) {
		return;
	}

	$t_config_table = db_get_table( 'mantis_config_table' );
	$c_user_id = db_prepare_int( $p_user_id );

	# Delete the corresponding bugnote texts
	$query = "DELETE FROM $t_config_table
					WHERE config_id=" . db_param() . " AND user_id=" . db_param();
	db_query_bound( $query, array( $p_option, $c_user_id ) );
}

# ------------------
# delete the config entry
function config_delete_project( $p_project = ALL_PROJECTS ) {
	global $g_cache_config, $g_cache_config_access;
	$t_config_table = db_get_table( 'mantis_config_table' );
	$c_project = db_prepare_int( $p_project );
	$query = "DELETE FROM $t_config_table
				WHERE project_id=" . db_param();

	$result = @db_query_bound( $query, Array( $c_project ) );

	# flush cache here in case some of the deleted configs are in use.
	config_flush_cache();
}

# ------------------
# delete the config entry from the cache
# @@@ to be used sparingly
function config_flush_cache( $p_option = '', $p_user = ALL_USERS, $p_project = ALL_PROJECTS ) {
	global $g_cache_config, $g_cache_config_access, $g_cache_filled;

	if( '' !== $p_option ) {
		unset( $GLOBALS['g_cache_config'][$p_option][$p_user][$p_project] );
		unset( $GLOBALS['g_cache_config_access'][$p_option][$p_user][$p_project] );
	} else {
		unset( $GLOBALS['g_cache_config'] );
		unset( $GLOBALS['g_cache_config_access'] );
		$g_cache_filled = false;
	}
}

# ------------------
# Checks if an obsolete configuration variable is still in use.  If so, an error
# will be generated and the script will exit.  This is called from admin_check.php.
function config_obsolete( $p_var, $p_replace = '' ) {
	global $g_cache_config;

	# @@@ we could trigger a WARNING here, once we have errors that can
	#     have extra data plugged into them (we need to give the old and
	#     new config option names in the warning text)

	if( config_is_set( $p_var ) ) {
		$t_description = '<p><b>Warning:</b> The configuration option <tt>' . $p_var . '</tt> is now obsolete</p>';
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
			$t_info .= 'please use <tt>' . $p_replace . '</tt> instead.';
		}

		print_test_warn_row( $t_description, false, $t_info );
	}
}

/**
 * check for recursion in defining config variables
 *
 * If there is a %text% in the returned value, re-evaluate the "text"
 * part and replace the string
 *
 * @param string $p_value config variable to evaluate
 * @param bool if true, gets  %text% as a global config, defaults to false
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
				$t_value = str_replace( $t_matches[1][$i], $t_repl, $t_value );
			}
		}
		$t_value = str_replace( '\\%', '%', $t_value );
	}
	return $t_value;
}

/**
 * Defines if a config variable is considered "private"
 *
 * The purpose of this function is to avoid exposure of potentially
 * sensitive or confidential information to users or web services.
 * This includes things such as webserver details, paths, usernames and
 * passwords, hostnames/IP addresses, etc.
 *
 * @param string $p_config_var config variable to evaluate
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
		case 'password_confirm_hash_magic_string':
		case 'smtp_host':
		case 'smtp_username':
		case 'smtp_password':
		case 'smtp_connection_mode':
		case 'smtp_port':
		case 'email_send_using_cronjob':
		case 'absolute_path':
		case 'core_path':
		case 'class_path':
		case 'use_iis':
		case 'session_save_path':
		case 'session_handler':
		case 'session_validation':
		case 'global_settings':
		case 'system_font_folder':
		case 'phpMailer_method':
		case 'file_upload_ftp_server':
		case 'file_upload_ftp_user':
		case 'file_upload_ftp_pass':
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
		case 'rss_key_seed':
		case 'dot_tool':
		case 'neato_tool':
		case 'twitter_username':
		case 'twitter_password':
			return true;
	}

	return false;
}
