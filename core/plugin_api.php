<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.

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
# $Id$
# --------------------------------------------------------

/**
 * Plugin API
 * Handles the initialisation, management, and execution of plugins.
 *
 * @package PluginAPI
 */

##### Cache variables #####

$g_plugin_cache = array();
$g_plugin_current = array();

### Public API functions

/**
 * Determine if a given plugin basename has been registered.
 * @return boolean True if registered
 */
function plugin_is_registered( $p_basename ) {
	global $g_plugin_cache;
	return ( isset( $g_plugin_cache[$p_basename] ) && !is_null( $g_plugin_cache[$p_basename] ) );
}

/**
 * Make sure a given plugin basename has been registered.
 * Triggers ERROR_PLUGIN_NOT_REGISTERED otherwise.
 */
function plugin_ensure_registered( $p_basename ) {
	if ( !plugin_is_registered( $p_basename ) ) {
		trigger_error( ERROR_PLUGIN_NOT_REGISTERED, ERROR );
	}
}

/**
 * Get the currently executing plugin's basename.
 * @return string Plugin basename, or null if no current plugin
 */
function plugin_get_current() {
	global $g_plugin_current;
	return ( isset( $g_plugin_current[0] ) ? $g_plugin_current[0] : null );
}

/**
 * Add the current plugin to the stack
 * @param string Plugin basename
 */
function plugin_push_current( $p_basename ) {
	global $g_plugin_current;
	array_unshift( $g_plugin_current, $p_basename );
}

/**
 * Remove the current plugin from the stack
 * @return string Plugin basename, or null if no current plugin
 */
function plugin_pop_current() {
	global $g_plugin_current;
	return ( isset( $g_plugin_current[0] ) ? array_shift( $g_plugin_current ) : null );
}

/**
 * Get the information array registered by the given plugin.
 * @param string Plugin basename (defaults to current plugin)
 * @return array Plugin info (null if unregistered)
 */
function plugin_info( $p_basename=null ) {
	global $g_plugin_cache;

	if ( is_null( $p_basename ) ) {
		$p_basename = plugin_get_current();
	}

	return ( isset( $g_plugin_cache[$p_basename] ) ? $g_plugin_cache[$p_basename] : null );
}

/**
 * Get the URL to the plugin wrapper page.
 * @param string Page name
 * @param string Plugin basename (defaults to current plugin)
 */
function plugin_page( $p_page, $p_basename=null ) {
	if ( is_null( $p_basename ) ) {
		$t_current = plugin_get_current();
	} else {
		$t_current = $p_basename;
	}
	return helper_mantis_url( 'plugin.php?page='.$t_current.'/'.$p_page );
}

/**
 * Given a base table name for a plugin, add appropriate prefix and suffix.
 * Convenience for plugin schema definitions.
 * @param string Table name
 * @param string Plugin basename (defaults to current plugin)
 * @return string Full table name
 */
function plugin_table( $p_name, $p_basename=null ) {
	if ( is_null( $p_basename ) ) {
		$t_current = plugin_get_current();
	} else {
		$t_current = $p_basename;
	}
	return config_get_global( 'db_table_prefix' ) .
		'_plugin_' . $t_current . '_' . $p_name .
		config_get_global( 'db_table_suffix' );
}

/**
 * Hook a plugin's callback function to an event.
 * @param string Event name
 * @param string Callback function
 */
function plugin_event_hook( $p_name, $p_callback ) {
	$t_basename = plugin_get_current();
	$t_function = 'plugin_event_' . $t_basename . '_' . $p_callback;
	event_hook( $p_name, $t_function, $t_basename );
}

### Plugin management functions

/**
 * Include the appropriate script for a plugin.
 * @param srting Plugin basename
 * @param boolean Include events script
 */
function plugin_include( $p_basename, $p_include_events=false ) {
	$t_path = config_get_global( 'plugin_path' );

	$t_register_file = $t_path.$p_basename.DIRECTORY_SEPARATOR.'register.php';
	if ( is_file( $t_register_file ) ) {
		include_once( $t_register_file );
	}

	if ( $p_include_events ) {
		$t_events_file = $t_path.$p_basename.DIRECTORY_SEPARATOR.'events.php';
		if ( is_file( $t_events_file ) ) {
			include_once( $t_events_file );
		}
	}
}

/**
 * Get the script information from the script file or cache.
 * @param string Plugin basename
 * @return array Script information
 */
function plugin_get_info( $p_basename ) {
	global $g_plugin_cache;

	if ( plugin_is_registered( $p_basename ) ) {
		return $g_plugin_cache[$p_basename];
	}

	plugin_push_current( $p_basename );

	plugin_include( $p_basename );

	$t_plugin_info = null;

	$t_info_function = 'plugin_callback_'.$p_basename.'_info';
	if ( function_exists( $t_info_function ) ) {
		$t_plugin_info = $t_info_function();
		
		if ( !isset( $t_plugin_info['name'] ) ) {
			$t_plugin_info['name'] = $p_basename;
		}

		if ( !isset( $t_plugin_info['description'] ) ) {
			$t_plugin_info['description'] = '';
		}

		if ( !isset( $t_plugin_info['version'] ) ) {
			$t_plugin_info['version'] = '';
		}

		if ( !isset( $t_plugin_info['author'] ) ) {
			$t_plugin_info['author'] = '';
		}
	
		if ( !isset( $t_plugin_info['contact'] ) ) {
			$t_plugin_info['contact'] = '';
		}
	
		if ( !isset( $t_plugin_info['url'] ) ) {
			$t_plugin_info['url'] = '';
		}

		if ( !isset( $t_plugin_info['page'] ) ) {
			$t_plugin_info['page'] = '';
		}

		if ( !isset( $t_plugin_info['requires'] ) ) {
			$t_plugin_info['requires'] = array();
		}
		
	}

	plugin_pop_current();

	return $t_plugin_info;
}

/**
 * Get the upgrade schema for a given plugin.
 * @param string Plugin basename
 * @return array Upgrade schema in same format as Mantis schema
 */
function plugin_get_schema( $p_basename ) {

	plugin_include( $p_basename );

	$t_schema_function = 'plugin_callback_'.$p_basename.'_schema';
	if ( !function_exists( $t_schema_function ) ) {
		return null;
	}

	plugin_push_current( $p_basename );
	$t_schema = $t_schema_function();
	plugin_pop_current();

	if ( is_array( $t_schema ) ) {
		return $t_schema;
	}

	return null;
}

/**
 * List all installed plugins.
 * @return array Installed plugins
 */
function plugin_get_installed() {
	$t_plugin_table = config_get_global( 'mantis_plugin_table' );

	$t_query = "SELECT * FROM $t_plugin_table";
	$t_result = db_query( $t_query );

	$t_plugins = array( 'mantis' => '1' );
	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_basename = $t_row['basename'];
		$t_plugins[$t_basename] = $t_row['enabled'];
	}

	return $t_plugins;
}

/**
 * List enabled plugins.
 * @return array Enabled plugin basenames
 */
function plugin_get_enabled() {
	$t_plugin_table = config_get_global( 'mantis_plugin_table' );

	$t_query = "SELECT basename FROM $t_plugin_table WHERE enabled=" . db_param(0);
	$t_result = db_query_bound( $t_query, Array( 1 ) );

	$t_plugins = array();
	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_plugins[] = $t_row['basename'];
	}

	return $t_plugins;
}

/**
 * Search the plugins directory for plugins.
 * @return array Plugin basename/info key/value pairs.
 */
function plugin_find_all() {
	$t_plugin_path = config_get_global( 'plugin_path' );
	$t_plugins = array( 'mantis' => plugin_get_info( 'mantis' ) );

	if ( $t_dir = opendir( $t_plugin_path ) ) {
		while ( ($t_file = readdir( $t_dir )) !== false ) {
			if ( '.' == $t_file || '..' == $t_file ) {
				continue;
			}
			if ( is_dir( $t_plugin_path.$t_file ) ) {
				$t_plugin_info = plugin_get_info( $t_file );
				if ( !is_null( $t_plugin_info ) ) {
					$t_plugins[$t_file] = $t_plugin_info;
				}
			}
		}
		closedir( $t_dir );
	}
	return $t_plugins;
}

/**
 * Determine if a given plugin is installed.
 * @param string Plugin basename
 * @retrun boolean True if plugin is installed
 */
function plugin_is_installed( $p_basename ) {
	$t_plugin_table	= config_get_global( 'mantis_plugin_table' );
	$c_basename 	= db_prepare_string( $p_basename );

	$t_query = "SELECT COUNT(*) FROM $t_plugin_table WHERE basename=" . db_param(0);
	$t_result = db_query_bound( $t_query, array( $c_basename ) );
	return ( 0 < db_result( $t_result ) );
}

/**
 * Install a plugin to the database.
 * @param string Plugin basename
 */
function plugin_install( $p_basename ) {
	access_ensure_global_level( config_get_global( 'manage_plugin_threshold' ) );

	if ( plugin_is_installed( $p_basename ) ) {
		trigger_error( ERROR_PLUGIN_ALREADY_INSTALLED, WARNING );
		return null;
	}

	$t_plugin_table	= config_get_global( 'mantis_plugin_table' );
	$t_plugin = plugin_get_info( $p_basename );

	$c_basename = db_prepare_string( $p_basename );

	$t_query = "INSERT INTO $t_plugin_table ( basename, enabled )
				VALUES ( ".db_param(0).", 1 )";
	db_query_bound( $t_query, array( $c_basename ) );

	if ( false === ( config_get( 'schema_' . $p_basename . '_schema', false ) ) ) {
		config_set( 'plugin_' . $p_basename . '_schema', -1 );
	}
	plugin_upgrade( $p_basename );
}

/**
 * Determine if an installed plugin needs to upgrade its schema.
 * @param string Plugin basename
 * @return boolean True if plugin needs schema ugrades.
 */
function plugin_needs_upgrade( $p_basename = null ) {
	if ( is_null( $p_basename ) ) {
		$t_current = plugin_get_current();
	} else {
		$t_current = $p_basename;
	}
	$t_plugin = plugin_get_info( $p_basename );

	$t_plugin_schema = plugin_get_schema( $p_basename );
	if ( is_null( $t_plugin_schema ) ) {
		return false;
	}

	$t_plugin_schema_version = config_get( 'plugin_' . $p_basename . '_schema', -1 );

	return ( $t_plugin_schema_version < count( $t_plugin_schema ) - 1 );
}

/**
 * Upgrade an installed plugin's schema.
 * @param string Plugin basename
 * @return multi True if upgrade completed, null if problem
 */
function plugin_upgrade( $p_basename ) {
	access_ensure_global_level( config_get_global( 'manage_plugin_threshold' ) );

	$t_schema_version = config_get( 'plugin_' . $p_basename . '_schema', -1 );
	$t_schema = plugin_get_schema( $p_basename );

	global $g_db;
	$t_dict = NewDataDictionary( $g_db );

	$i = $t_schema_version + 1;
	while ( $i < count( $t_schema ) ) {
		$t_target = $t_schema[$i][1][0];

		if ( $t_schema[$i][0] == 'InsertData' ) {
			$t_sqlarray = call_user_func_array( $t_schema[$i][0], $t_schema[$i][1] );
		} else if ( $t_schema[$i][0] == 'UpdateSQL' ) {
			$t_sqlarray = array( $t_schema[$i][1] );
			$t_target = $t_schema[$i][1];
		} else {
			$t_sqlarray = call_user_func_array( Array( $t_dict, $t_schema[$i][0] ), $t_schema[$i][1] );
		}
		$t_status = $t_dict->ExecuteSQLArray( $t_sqlarray );

		if ( 2 == $t_status ) {
			config_set( 'plugin_' . $p_basename . '_schema', $i );
		} else {
			return null;
		}

		$i++;
	}

	return true;
}

/**
 * Uninstall a plugin from the database.
 * @param string Plugin basename
 */
function plugin_uninstall( $p_basename ) {
	access_ensure_global_level( config_get_global( 'manage_plugin_threshold' ) );

	if ( !plugin_is_installed( $p_basename ) ) {
		return;
	}

	$t_plugin_table	= config_get_global( 'mantis_plugin_table' );
	$c_basename = db_prepare_string( $p_basename );

	$t_query = "DELETE FROM $t_plugin_table WHERE basename=" . db_param(0);
	db_query_bound( $t_query, array( $c_basename ) );
}

### Core usage only.

/**
 * Initialize all enabled plugins.
 * Post-signals EVENT_PLUGIN_INIT.
 */
function plugin_init_all() {
	if ( OFF == config_get_global( 'plugins_enabled' ) || !db_table_exists( config_get_global( 'mantis_plugin_table' ) ) ) {
		return;
	}

	global $g_plugin_cache;
	if ( !isset( $g_plugin_cache ) ) {
		$g_plugin_cache = array();
	}

	global $g_plugin_current;
	if ( !isset( $g_plugin_current ) ) {
		$g_plugin_current = array();
	}

	# Initial plugin for version dependencies
	$g_plugin_cache['mantis'] = array(
		'name' => 'Mantis Bug Tracker',
		'description' => 'Core plugin API for the Mantis Bug Tracker.',
		'contact' => 'mantisbt-dev@lists.sourceforge.net',
		'version' => MANTIS_VERSION,
		'requires' => array(),
		'author' => 'Mantis Team',
		'page' => '',
		'url' => 'http://www.mantisbt.org',
	);

	plugin_init_array( plugin_get_enabled() );

	event_signal( 'EVENT_PLUGIN_INIT' );
}

/**
 * Recursive plugin initialization to handle dependencies.
 * @param array Plugin basenames to initialize.
 */
function plugin_init_array( $p_plugins, $p_depth=0 ) {
	$t_plugins_retry = array();

	foreach( $p_plugins as $t_basename ) {
		if ( !plugin_init( $t_basename ) ) {
			# Dependent plugin
			$t_plugins_retry[] = $t_basename;
		}
	}

	# Recurse on dependent plugins
	if ( $p_depth < count( $p_plugins ) ) {
		plugin_init_array( $t_plugins_retry, $p_depth + 1 );
	}
}

/**
 * Initialize a single plugin.
 * @param string Plugin basename
 * @return boolean True if plugin initialized, false otherwise.
 */
function plugin_init( $p_basename ) {
	global $g_plugin_cache;

	$t_plugin_info = plugin_get_info( $p_basename );

	if ( $t_plugin_info !== null ) {
		$g_plugin_cache[$p_basename] = $t_plugin_info;

		# handle dependent plugins
		if ( isset( $t_plugin_info['requires'] ) ) {
			foreach ( $t_plugin_info['requires'] as $t_required => $t_version ) {
				if ( !isset( $g_plugin_cache[$t_required] ) ||
					( !is_null( $t_version ) &&
					$g_plugin_cache[$t_required]['version'] < $t_version ) ) {
					return false;
				}
			}
		}

		$t_init_function = 'plugin_callback_'.$p_basename.'_init';
		if ( function_exists( $t_init_function ) ) {
			plugin_push_current( $p_basename );
			$t_init_function();
			plugin_pop_current();
		}
	}

	return true;
}

