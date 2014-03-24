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
 * Plugin API
 *
 * Handles the initialisation, management, and execution of plugins.
 *
 * @package CoreAPI
 * @subpackage PluginAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses error_api.php
 * @uses event_api.php
 * @uses helper_api.php
 * @uses history_api.php
 * @uses lang_api.php
 */

require_api( 'access_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'database_api.php' );
require_api( 'error_api.php' );
require_api( 'event_api.php' );
require_api( 'helper_api.php' );
require_api( 'history_api.php' );
require_api( 'lang_api.php' );

# Cache variables #####

$g_plugin_cache = array();
$g_plugin_cache_priority = array();
$g_plugin_cache_protected = array();
$g_plugin_current = array();

# Public API #####
/**
 * Get the currently executing plugin's basename.
 * @return string Plugin basename, or null if no current plugin
 */
function plugin_get_current() {
	global $g_plugin_current;
	return( isset( $g_plugin_current[0] ) ? $g_plugin_current[0] : null );
}

/**
 * Add the current plugin to the stack
 * @param string $p_base_name Plugin base name
 */
function plugin_push_current( $p_base_name ) {
	global $g_plugin_current;
	array_unshift( $g_plugin_current, $p_base_name );
}

/**
 * Remove the current plugin from the stack
 * @return string Plugin basename, or null if no current plugin
 */
function plugin_pop_current() {
	global $g_plugin_current;
	return( isset( $g_plugin_current[0] ) ? array_shift( $g_plugin_current ) : null );
}

/**
 * Get the URL to the plugin wrapper page.
 * @param string $p_page Page name
 * @param bool $p_redirect return url for redirection
 * @param string $p_base_name Plugin base name (defaults to current plugin)
 * @return string
 */
function plugin_page( $p_page, $p_redirect = false, $p_base_name = null ) {
	if( is_null( $p_base_name ) ) {
		$t_current = plugin_get_current();
	} else {
		$t_current = $p_base_name;
	}
	if( $p_redirect ) {
		return 'plugin.php?page=' . $t_current . '/' . $p_page;
	} else {
		return helper_mantis_url( 'plugin.php?page=' . $t_current . '/' . $p_page );
	}
}

/**
 * Return a path to a plugin file.
 * @param string $p_filename File name
 * @param string $p_base_name Plugin base name
 * @return mixed File path or false if FNF
 */
function plugin_file_path( $p_filename, $p_base_name ) {
	$t_file_path = config_get( 'plugin_path' );
	$t_file_path .= $p_base_name . DIRECTORY_SEPARATOR;
	$t_file_path .= 'files' . DIRECTORY_SEPARATOR . $p_filename;

	return( is_file( $t_file_path ) ? $t_file_path : false );
}

/**
 * Get the URL to the plugin wrapper file page.
 * @param string $p_file file name
 * @param bool $p_redirect return url for redirection
 * @param string $p_base_name Plugin base name (defaults to current plugin)
 * @return string
 */
function plugin_file( $p_file, $p_redirect = false, $p_base_name = null ) {
	if( is_null( $p_base_name ) ) {
		$t_current = plugin_get_current();
	} else {
		$t_current = $p_base_name;
	}
	if( $p_redirect ) {
		return 'plugin_file.php?file=' . $t_current . '/' . $p_file;
	} else {
		return helper_mantis_url( 'plugin_file.php?file=' . $t_current . '/' . $p_file );
	}
}

/**
 * Include the contents of a file as output.
 * @param string $p_filename File name
 * @param string $p_basename Plugin basename
 */
function plugin_file_include( $p_filename, $p_basename = null ) {

    global $g_plugin_mime_types;

	if( is_null( $p_basename ) ) {
		$t_current = plugin_get_current();
	} else {
		$t_current = $p_basename;
	}

	$t_file_path = plugin_file_path( $p_filename, $t_current );
	if( false === $t_file_path ) {
		trigger_error( ERROR_GENERIC, ERROR );
	}

	$t_content_type = '';
	$finfo = finfo_get_if_available();

	if ( $finfo ) {
		$t_file_info_type = $finfo->file( $t_file_path );
		if ( $t_file_info_type !== false ) {
			$t_content_type = $t_file_info_type;
		}
	}

	// allow overriding the content type for specific text and image extensions
	// see bug #13193 for details
	if ( strpos($t_content_type, 'text/') === 0 || strpos( $t_content_type, 'image/') === 0 ) {
		$t_extension = pathinfo( $t_file_path, PATHINFO_EXTENSION );
		if ( $t_extension && array_key_exists( $t_extension , $g_plugin_mime_types ) ) {
			$t_content_type =  $g_plugin_mime_types [ $t_extension ];
		}
	}

	if ( $t_content_type )
    	header('Content-Type: ' . $t_content_type );

	readfile( $t_file_path );
}

/**
 * Given a base table name for a plugin, add appropriate prefix and suffix.
 * Convenience for plugin schema definitions.
 * @param string $p_name Table name
 * @param string $p_basename Plugin basename (defaults to current plugin)
 * @return string Full table name
 */
function plugin_table( $p_name, $p_basename = null ) {
	if( is_null( $p_basename ) ) {
		$t_current = plugin_get_current();
	} else {
		$t_current = $p_basename;
	}

	$t_table_name = config_get_global( 'db_table_prefix' );
	if( !empty( $t_table_name ) ) {
		$t_table_name .= '_';
	}
	$t_table_name .=
		config_get_global( 'db_table_plugin_prefix' ) .
		$t_current . '_' . $p_name .
		config_get_global( 'db_table_suffix' );

	return $t_table_name;
}

/**
 * Get a plugin configuration option.
 * @param string $p_option Configuration option name
 * @param mixed $p_default Default option value
 * @param bool $p_global get global config variables only
 * @param int $p_user User ID
 * @param int $p_project Project ID
 * @return string
 */
function plugin_config_get( $p_option, $p_default = null, $p_global = false, $p_user = null, $p_project = null ) {
	$t_basename = plugin_get_current();
	$t_full_option = 'plugin_' . $t_basename . '_' . $p_option;

	if( $p_global ) {
		return config_get_global( $t_full_option, $p_default );
	} else {
		return config_get( $t_full_option, $p_default, $p_user, $p_project );
	}
}

/**
 * Set a plugin configuration option in the database.
 * @param string $p_option Configuration option name
 * @param mixed $p_value Option value
 * @param int $p_user User ID
 * @param int $p_project Project ID
 * @param int $p_access Access threshold
 */
function plugin_config_set( $p_option, $p_value, $p_user = NO_USER, $p_project = ALL_PROJECTS, $p_access = DEFAULT_ACCESS_LEVEL ) {
	if( $p_access == DEFAULT_ACCESS_LEVEL ) {
		$p_access = config_get_global( 'admin_site_threshold' );
	}

	$t_basename = plugin_get_current();
	$t_full_option = 'plugin_' . $t_basename . '_' . $p_option;

	config_set( $t_full_option, $p_value, $p_user, $p_project, $p_access );
}

/**
 * Delete a plugin configuration option from the database.
 * @param string $p_option Configuration option name
 * @param int $p_user User ID
 * @param int $p_project Project ID
 */
function plugin_config_delete( $p_option, $p_user = ALL_USERS, $p_project = ALL_PROJECTS ) {
	$t_basename = plugin_get_current();
	$t_full_option = 'plugin_' . $t_basename . '_' . $p_option;

	config_delete( $t_full_option, $p_user, $p_project );
}

/**
 * Set plugin default values to global values without overriding anything.
 * @param array $p_options Array of configuration option name/value pairs.
 */
function plugin_config_defaults( $p_options ) {
	if( !is_array( $p_options ) ) {
		return;
	}

	$t_basename = plugin_get_current();
	$t_option_base = 'plugin_' . $t_basename . '_';

	foreach( $p_options as $t_option => $t_value ) {
		$t_full_option = $t_option_base . $t_option;

		config_set_global( $t_full_option, $t_value, false );
	}
}

/**
 * Get a language string for the plugin.
 * Automatically prepends plugin_<basename> to the string requested.
 * @param string Language string name
 * @param string Plugin basename
 * @return string Language string
 */
function plugin_lang_get( $p_name, $p_basename = null ) {
	if( !is_null( $p_basename ) ) {
		plugin_push_current( $p_basename );
	}

	$t_basename = plugin_get_current();
	$t_name = 'plugin_' . $t_basename . '_' . $p_name;
	$t_string = lang_get( $t_name );

	if( !is_null( $p_basename ) ) {
		plugin_pop_current();
	}
	return $t_string;
}

/**
 * log history event from plugin
 * @param int $p_bug_id bug id
 * @param string $p_field_name field name
 * @param string $p_old_value old value
 * @param string $p_new_value new value
 * @param int $p_user_id user id
 * @param string $p_basename plugin basename
 */
function plugin_history_log( $p_bug_id, $p_field_name, $p_old_value, $p_new_value = '', $p_user_id = null, $p_basename = null ) {
	if( is_null( $p_basename ) ) {
		$t_basename = plugin_get_current();
	} else {
		$t_basename = $p_basename;
	}

	$t_field_name = $t_basename . '_' . $p_field_name;

	history_log_event_direct( $p_bug_id, $t_field_name, $p_old_value, $p_new_value, $p_user_id, PLUGIN_HISTORY );
}

/**
 * Trigger a plugin-specific error with the given name and type.
 * @param string Error name
 * @param int Error type
 * @param string Plugin basename
 */
function plugin_error( $p_error_name, $p_error_type = ERROR, $p_basename = null ) {
	if( is_null( $p_basename ) ) {
		$t_basename = plugin_get_current();
	} else {
		$t_basename = $p_basename;
	}

	$t_error_code = "plugin_${t_basename}_${p_error_name}";

	trigger_error( $t_error_code, $p_error_type );

	return null;
}

/**
 * Hook a plugin's callback function to an event.
 * @param string $p_name Event name
 * @param string $p_callback Callback function
 */
function plugin_event_hook( $p_name, $p_callback ) {
	$t_basename = plugin_get_current();
	event_hook( $p_name, $p_callback, $t_basename );
}

/**
 * Hook multiple plugin callbacks at once.
 * @param array $p_hooks Array of event name/callback key/value pairs
 */
function plugin_event_hook_many( $p_hooks ) {
	if( !is_array( $p_hooks ) ) {
		return;
	}

	$t_basename = plugin_get_current();

	foreach( $p_hooks as $t_event => $t_callbacks ) {
		if( !is_array( $t_callbacks ) ) {
			event_hook( $t_event, $t_callbacks, $t_basename );
			continue;
		}

		foreach( $t_callbacks as $t_callback ) {
			event_hook( $t_event, $t_callback, $t_basename );
		}
	}
}

/**
 * Allows a plugin to declare a 'child plugin' that
 * can be loaded from the same parent directory.
 * @param string $p_child Child plugin basename.
 * @return mixed
 */
function plugin_child( $p_child ) {
	$t_base_name = plugin_get_current();

	$t_plugin = plugin_register( $t_base_name, false, $p_child );

	if( !is_null( $t_plugin ) ) {
		plugin_init( $p_child );
	}

	return $t_plugin;
}

/**
 * Checks if a given plugin has been registered and initialized,
 * and returns a boolean value representing the "loaded" state.
 * @param string $p_base_name Plugin basename
 * @return boolean Plugin loaded
 */
function plugin_is_loaded( $p_base_name ) {
	global $g_plugin_cache_init;

	return ( isset( $g_plugin_cache_init[ $p_base_name ] ) && $g_plugin_cache_init[ $p_base_name ] );
}

/**
 * Converts a version string to an array, using some punctuation and
 * number/lettor boundaries as splitting points.
 * @param string $p_version Version string
 * @return array Version array
 */
function plugin_version_array( $p_version ) {
	$t_version = preg_replace( '/([a-zA-Z]+)([0-9]+)/', '\1.\2', $p_version );
	$t_version = preg_replace( '/([0-9]+)([a-zA-Z]+)/', '\1.\2', $t_version );

	$t_search = array(
		',',
		'-',
		'_',
	);

	$t_replace = array(
		'.',
		'.',
		'.',
	);

	$t_version = explode( '.', str_replace( $t_search, $t_replace, $t_version ) );

	return $t_version;
}

/**
 * Checks two version arrays sequentially for minimum or maximum version dependencies.
 * @param array $p_version1 Version array to check
 * @param array $p_version2 Version array required
 * @param boolean $p_maximum Minimum (false) or maximum (true) version check
 * @return int 1 if the version dependency succeeds, -1 if it fails
 */
function plugin_version_check( $p_version1, $p_version2, $p_maximum = false ) {
	while( count( $p_version1 ) > 0 && count( $p_version2 ) > 0 ) {

		# Grab the next version bits
		$t_version1 = array_shift( $p_version1 );
		$t_version2 = array_shift( $p_version2 );

		# Convert to integers if possible
		if( is_numeric( $t_version1 ) ) {
			$t_version1 = (int) $t_version1;
		}
		if( is_numeric( $t_version2 ) ) {
			$t_version2 = (int) $t_version2;
		}

		# Check for immediate version differences
		if( $p_maximum ) {
			if( $t_version1 < $t_version2 ) {
				return 1;
			}
			else if( $t_version1 > $t_version2 ) {
				return -1;
			}
		} else {
			if( $t_version1 > $t_version2 ) {
				return 1;
			}
			else if( $t_version1 < $t_version2 ) {
				return -1;
			}
		}
	}

	# Versions matched exactly
	if ( count( $p_version1 ) == 0 && count( $p_version2 ) == 0 ) {
		return 1;
	}

	# Handle unmatched version bits
	if( $p_maximum ) {
		if ( count( $p_version2 ) > 0 ) {
			return 1;
		}
	} else {
		if ( count( $p_version1 ) > 0 ) {
			return 1;
		}
	}

	# No more comparisons
	return -1;
}

/**
 * Check a plugin dependency given a basename and required version.
 * Versions are checked using PHP's library version_compare routine
 * and allows both minimum and maximum version requirements.
 * Returns 1 if plugin dependency is met, 0 if dependency not met,
 * or -1 if dependency is the wrong version.
 * @param string $p_base_name Plugin base name
 * @param string $p_required Required version
 * @param bool $p_initialized whether plugin is initialized
 * @return integer Plugin dependency status
 */
function plugin_dependency( $p_base_name, $p_required, $p_initialized = false ) {
	global $g_plugin_cache, $g_plugin_cache_init;

	# check for registered dependency
	if( isset( $g_plugin_cache[$p_base_name] ) ) {

		# require dependency initialized?
		if( $p_initialized && !isset( $g_plugin_cache_init[$p_base_name] ) ) {
			return 0;
		}

		$t_required_array = explode( ',', $p_required );

		foreach( $t_required_array as $t_required ) {
			$t_required = trim( $t_required );
			$t_maximum = false;

			# check for a less-than-or-equal version requirement
			$t_ltpos = strpos( $t_required, '<=' );
			if( $t_ltpos !== false ) {
				$t_required = trim( utf8_substr( $t_required, $t_ltpos + 2 ) );
				$t_maximum = true;
			} else {
				$t_ltpos = strpos( $t_required, '<' );
				if( $t_ltpos !== false ) {
					$t_required = trim( utf8_substr( $t_required, $t_ltpos + 1 ) );
					$t_maximum = true;
				}
			}

			$t_version1 = plugin_version_array( $g_plugin_cache[$p_base_name]->version );
			$t_version2 = plugin_version_array( $t_required );

			$t_check = plugin_version_check( $t_version1, $t_version2, $t_maximum );

			if ( $t_check < 1 ) {
				return $t_check;
			}
		}

		return 1;
	} else {
		return 0;
	}
}

/**
 * Checks to see if a plugin is 'protected' from uninstall.
 * @param string $p_base_name Plugin base name
 * @return boolean True if plugin is protected
 */
function plugin_protected( $p_base_name ) {
	global $g_plugin_cache_protected;

	# For pseudo-plugin MantisCore, return protected as 1.
	if( $p_base_name == 'MantisCore' ) {
		return 1;
	}

	return $g_plugin_cache_protected[$p_base_name];
}

/**
 * Gets a plugin's priority.
 * @param string $p_base_name Plugin base name
 * @return int Plugin priority
 */
function plugin_priority( $p_base_name ) {
	global $g_plugin_cache_priority;

	# For pseudo-plugin MantisCore, return priority as 3.
	if( $p_base_name == 'MantisCore' ) {
		return 3;
	}

	return $g_plugin_cache_priority[$p_base_name];
}

/**
 * Determine if a given plugin is installed.
 * @param string $p_basename Plugin basename
 * @return boolean True if plugin is installed
 */
function plugin_is_installed( $p_basename ) {
	$t_plugin_table = db_get_table( 'plugin' );

	$t_forced_plugins = config_get_global( 'plugins_force_installed' );
	foreach( $t_forced_plugins as $t_basename => $t_priority ) {
		if ( $t_basename == $p_basename ) {
			return true;
		}
	}

	$t_query = "SELECT COUNT(*) FROM $t_plugin_table WHERE basename=" . db_param();
	$t_result = db_query_bound( $t_query, array( $p_basename ) );
	return( 0 < db_result( $t_result ) );
}

/**
 * Install a plugin to the database.
 * @param MantisPlugin $p_plugin Plugin basename
 * @return null
 */
function plugin_install( $p_plugin ) {
	access_ensure_global_level( config_get_global( 'manage_plugin_threshold' ) );

	if( plugin_is_installed( $p_plugin->basename ) ) {
		trigger_error( ERROR_PLUGIN_ALREADY_INSTALLED, WARNING );
		return null;
	}

	plugin_push_current( $p_plugin->basename );

	if( !$p_plugin->install() ) {
		plugin_pop_current();
		return null;
	}

	$t_plugin_table = db_get_table( 'plugin' );

	$t_query = "INSERT INTO $t_plugin_table ( basename, enabled )
				VALUES ( " . db_param() . ", '1' )";
	db_query_bound( $t_query, array( $p_plugin->basename ) );

	if( false === ( plugin_config_get( 'schema', false ) ) ) {
		plugin_config_set( 'schema', -1 );
	}

	plugin_upgrade( $p_plugin );

	plugin_pop_current();
}

/**
 * Determine if an installed plugin needs to upgrade its schema.
 * @param string $p_plugin Plugin basename
 * @return boolean True if plugin needs schema ugrades.
 */
function plugin_needs_upgrade( $p_plugin ) {
	$t_plugin_schema = $p_plugin->schema();
	if( is_null( $t_plugin_schema ) ) {
		return false;
	}

	$t_config_option = 'plugin_' . $p_plugin->basename . '_schema';
	$t_plugin_schema_version = config_get( $t_config_option, -1, ALL_USERS, ALL_PROJECTS );

	return( $t_plugin_schema_version < count( $t_plugin_schema ) - 1 );
}

/**
 * Upgrade an installed plugin's schema.
 * @param MantisPlugin $p_plugin Plugin basename
 * @return mixed True if upgrade completed, null if problem
 */
function plugin_upgrade( $p_plugin ) {
	access_ensure_global_level( config_get_global( 'manage_plugin_threshold' ) );

	if( !plugin_is_installed( $p_plugin->basename ) ) {
		return;
	}

	require_api( 'install_helper_functions_api.php' );

	plugin_push_current( $p_plugin->basename );

	$t_schema_version = plugin_config_get( 'schema', -1 );
	$t_schema = $p_plugin->schema();

	global $g_db;
	$t_dict = NewDataDictionary( $g_db );

	$i = $t_schema_version + 1;
	while( $i < count( $t_schema ) ) {
		if( !$p_plugin->upgrade( $i ) ) {
			plugin_pop_current();
			return false;
		}

		$t_target = $t_schema[$i][1][0];

		if( $t_schema[$i][0] == 'InsertData' ) {
			$t_sqlarray = array(
				'INSERT INTO ' . $t_schema[$i][1][0] . $t_schema[$i][1][1],
			);
		} else if( $t_schema[$i][0] == 'UpdateSQL' ) {
			$t_sqlarray = array(
				'UPDATE ' . $t_schema[$i][1][0] . $t_schema[$i][1][1],
			);
			$t_target = $t_schema[$i][1];
		} else if( $t_schema[$i][0] == 'UpdateFunction' ) {
			$t_sqlarray = false;
			if( isset( $t_schema[$i][2] ) ) {
				$t_status = call_user_func( 'install_' . $t_schema[$i][1], $t_schema[$i][2] );
			} else {
				$t_status = call_user_func( 'install_' . $t_schema[$i][1] );
			}
		} else {
			$t_sqlarray = call_user_func_array( array( $t_dict, $t_schema[$i][0] ), $t_schema[$i][1] );
		}

		if( $t_sqlarray ) {
			$t_status = $t_dict->ExecuteSQLArray( $t_sqlarray );
		}

		if( 2 == $t_status ) {
			plugin_config_set( 'schema', $i );
		} else {
			error_parameters( $i );
			trigger_error( ERROR_PLUGIN_UPGRADE_FAILED, ERROR );
			return null;
		}

		$i++;
	}

	plugin_pop_current();

	return true;
}

/**
 * Uninstall a plugin from the database.
 * @param MantisPlugin $p_plugin Plugin basename
 */
function plugin_uninstall( $p_plugin ) {
	access_ensure_global_level( config_get_global( 'manage_plugin_threshold' ) );

	if( !plugin_is_installed( $p_plugin->basename ) || plugin_protected( $p_plugin->basename ) ) {
		return;
	}

	$t_plugin_table = db_get_table( 'plugin' );

	$t_query = "DELETE FROM $t_plugin_table WHERE basename=" . db_param();
	db_query_bound( $t_query, array( $p_plugin->basename ) );

	plugin_push_current( $p_plugin->basename );

	$p_plugin->uninstall();

	plugin_pop_current();
}

/**
 * Search the plugins directory for plugins.
 * @return array Plugin basename/info key/value pairs.
 */
function plugin_find_all() {
	$t_plugin_path = config_get_global( 'plugin_path' );
	$t_plugins = array(
		'MantisCore' => new MantisCorePlugin( 'MantisCore' ),
	);

	if( $t_dir = opendir( $t_plugin_path ) ) {
		while(( $t_file = readdir( $t_dir ) ) !== false ) {
			if( '.' == $t_file || '..' == $t_file ) {
				continue;
			}
			if( is_dir( $t_plugin_path . $t_file ) ) {
				$t_plugin = plugin_register( $t_file, true );

				if( !is_null( $t_plugin ) ) {
					$t_plugins[$t_file] = $t_plugin;
				}
			}
		}
		closedir( $t_dir );
	}
	return $t_plugins;
}

/**
 * Load a plugin's core class file.
 * @param string $p_basename Plugin basename
 * @param string $p_child Child filename
 * @return bool
 */
function plugin_include( $p_basename, $p_child = null ) {
	$t_path = config_get_global( 'plugin_path' ) . $p_basename . DIRECTORY_SEPARATOR;

	if( is_null( $p_child ) ) {
		$t_plugin_file = $t_path . $p_basename . '.php';
	} else {
		$t_plugin_file = $t_path . $p_child . '.php';
	}
	$t_included = false;
	if( is_file( $t_plugin_file ) ) {
		include_once( $t_plugin_file );
		$t_included = true;
	}

	return $t_included;
}

/**
 * Allows a plugin page to require a plugin-specific API
 * @param string $p_file The API to be included
 * @param string $p_basename Plugin's basename (defaults to current plugin)
 */
function plugin_require_api( $p_file, $p_basename = null ) {
	if( is_null( $p_basename ) ) {
		$t_current = plugin_get_current();
	} else {
		$t_current = $p_basename;
	}

	$t_path = config_get_global( 'plugin_path' ) . $t_current . '/';

	require_once( $t_path . $p_file );
}

/**
 * Register a plugin with MantisBT.
 * The plugin class must already be loaded before calling.
 * @param string $p_basename Plugin classname without 'Plugin' postfix
 * @param bool $p_return return
 * @param string $p_child child filename
 * @return mixed
 */
function plugin_register( $p_basename, $p_return = false, $p_child = null ) {
	global $g_plugin_cache;

	$t_basename = is_null( $p_child ) ? $p_basename : $p_child;
	if( !isset( $g_plugin_cache[$t_basename] ) ) {
		if( is_null( $p_child ) ) {
			$t_classname = $p_basename . 'Plugin';
		} else {
			$t_classname = $p_child . 'Plugin';
		}

		# Include the plugin script if the class is not already declared.
		if( !class_exists( $t_classname ) ) {
			if( !plugin_include( $p_basename, $p_child ) ) {
				return null;
			}
		}

		# Make sure the class exists and that it's of the right type.
		if( class_exists( $t_classname ) && is_subclass_of( $t_classname, 'MantisPlugin' ) ) {
			plugin_push_current( is_null( $p_child ) ? $p_basename : $p_child );

			$t_plugin = new $t_classname( is_null( $p_child ) ? $p_basename : $p_child );

			plugin_pop_current();

			# Final check on the class
			if( is_null( $t_plugin->name ) || is_null( $t_plugin->version ) ) {
				return null;
			}

			if( $p_return ) {
				return $t_plugin;
			} else {
				$g_plugin_cache[$t_basename] = $t_plugin;
			}
		}
	}

	return $g_plugin_cache[$t_basename];
}

/**
 * Find and register all installed plugins.
 */
function plugin_register_installed() {
	global $g_plugin_cache_priority, $g_plugin_cache_protected;

	# register plugins specified in the site configuration
	$t_forced_plugins = config_get_global( 'plugins_force_installed' );
	foreach( $t_forced_plugins as $t_basename => $t_priority ) {
		plugin_register( $t_basename );
		$g_plugin_cache_priority[$t_basename] = $t_priority;
		$g_plugin_cache_protected[$t_basename] = true;
	}

	# register plugins installed via the interface/database
	$t_plugin_table = db_get_table( 'plugin' );

	$t_query = "SELECT basename, priority, protected FROM $t_plugin_table WHERE enabled=" . db_param() . ' ORDER BY priority DESC';
	$t_result = db_query_bound( $t_query, array( 1 ) );

	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_basename = $t_row['basename'];
		plugin_register( $t_basename );
		$g_plugin_cache_priority[$t_basename] = $t_row['priority'];
		$g_plugin_cache_protected[$t_basename] = $t_row['protected'];
	}
}

/**
 * Initialize all installed plugins.
 * Post-signals EVENT_PLUGIN_INIT.
 */
function plugin_init_installed() {
	if( OFF == config_get_global( 'plugins_enabled' ) || !db_table_exists( db_get_table( 'plugin' ) ) ) {
		return;
	}

	global $g_plugin_cache, $g_plugin_current, $g_plugin_cache_priority, $g_plugin_cache_protected, $g_plugin_cache_init;
	$g_plugin_cache = array();
	$g_plugin_current = array();
	$g_plugin_cache_init = array();
	$g_plugin_cache_priority = array();
	$g_plugin_cache_protected = array();

	plugin_register( 'MantisCore' );
	plugin_register_installed();

	$t_plugins = array_keys( $g_plugin_cache );

	do {
		$t_continue = false;
		$t_plugins_retry = array();

		foreach( $t_plugins as $t_basename ) {
			if( plugin_init( $t_basename ) ) {
				$t_continue = true;

			} else {
				# Dependent plugin
				$t_plugins_retry[] = $t_basename;
			}
		}

		$t_plugins = $t_plugins_retry;
	}
	while( $t_continue );

	event_signal( 'EVENT_PLUGIN_INIT' );
}

/**
 * Initialize a single plugin.
 * @param string $p_basename Plugin basename
 * @return boolean True if plugin initialized, false otherwise.
 */
function plugin_init( $p_basename ) {
	global $g_plugin_cache, $g_plugin_cache_init;

	# handle dependent plugins
	if( isset( $g_plugin_cache[$p_basename] ) ) {
		$t_plugin = $g_plugin_cache[$p_basename];

		# hard dependencies; return false if the dependency is not registered,
		# does not meet the version requirement, or is not yet initialized.
		if( is_array( $t_plugin->requires ) ) {
			foreach( $t_plugin->requires as $t_required => $t_version ) {
				if( plugin_dependency( $t_required, $t_version, true ) !== 1 ) {
					return false;
				}
			}
		}

		# soft dependencies; only return false if the soft dependency is
		# registered, but not yet initialized.
		if( is_array( $t_plugin->uses ) ) {
			foreach( $t_plugin->uses as $t_used => $t_version ) {
				if ( isset( $g_plugin_cache[ $t_used ] ) && !isset( $g_plugin_cache_init[ $t_used ] ) ) {
					return false;
				}
			}
		}

		# if plugin schema needs an upgrade, do not initialize
		if ( plugin_needs_upgrade( $t_plugin ) ) {
			return false;
		}

		plugin_push_current( $p_basename );

		# load plugin error strings
		global $g_lang_strings;
		$t_lang = lang_get_current();
		$t_plugin_errors = $t_plugin->errors();

		foreach( $t_plugin_errors as $t_error_name => $t_error_string ) {
			$t_error_code = "plugin_${p_basename}_${t_error_name}";
			$g_lang_strings[$t_lang]['MANTIS_ERROR'][$t_error_code] = $t_error_string;
		}

		# finish initializing the plugin
		$t_plugin->__init();
		$g_plugin_cache_init[$p_basename] = true;

		plugin_pop_current();

		return true;
	} else {
		return false;
	}
}
