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
 * MantisBT Core
 *
 * Initialises the MantisBT core, connects to the database, starts plugins and
 * performs other global operations that either help initialise MantisBT or
 * are required to be executed on every page load.
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses authentication_api.php
 * @uses collapse_api.php
 * @uses compress_api.php
 * @uses config_api.php
 * @uses config_defaults_inc.php
 * @uses config_inc.php
 * @uses constant_inc.php
 * @uses crypto_api.php
 * @uses custom_constants_inc.php
 * @uses custom_functions_inc.php
 * @uses database_api.php
 * @uses event_api.php
 * @uses http_api.php
 * @uses lang_api.php
 * @uses mantis_offline.php
 * @uses plugin_api.php
 * @uses php_api.php
 * @uses user_pref_api.php
 * @uses wiki_api.php
 * @uses utf8/utf8.php
 * @uses utf8/str_pad.php
 */

/**
 * Before doing anything... check if MantisBT is down for maintenance
 *
 *   To make MantisBT 'offline' simply create a file called
 *   'mantis_offline.php' in the MantisBT root directory.
 *   Users are redirected to that file if it exists.
 *   If you have to test MantisBT while it's offline, add the
 *   parameter 'mbadmin=1' to the URL.
 */
if ( file_exists( 'mantis_offline.php' ) && !isset( $_GET['mbadmin'] ) ) {
	include( 'mantis_offline.php' );
	exit;
}

$g_request_time = microtime( true );

ob_start();

# Load supplied constants
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'constant_inc.php' );

# Include default configuration settings
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'config_defaults_inc.php' );

# Load user-defined constants (if required)
if ( file_exists( $g_config_path . 'custom_constants_inc.php' ) ) {
	require_once( $g_config_path . 'custom_constants_inc.php' );
}

# Remember (globally) which API files have already been loaded
$g_api_included = array();

/**
 * Define an API inclusion function to replace require_once
 *
 * @param string $p_api_name api file name
 */
function require_api( $p_api_name ) {
	global $g_api_included;
	global $g_core_path;
	if ( !isset( $g_api_included[$p_api_name] ) ) {
		$t_existing_globals = get_defined_vars();
		require_once( $g_core_path . $p_api_name );
		$t_new_globals = array_diff_key( get_defined_vars(), $GLOBALS, array( 't_existing_globals' => 0, 't_new_globals' => 0 ) );
		foreach ( $t_new_globals as $t_global_name => $t_global_value ) {
			global $$t_global_name;
		}
		extract( $t_new_globals );
		$g_api_included[$p_api_name] = 1;
	}
}

# Remember (globally) which library files have already been loaded
$g_libraries_included = array();

/**
 * Define an API inclusion function to replace require_once
 *
 * @param string $p_library_name lib file name
 */
function require_lib( $p_library_name ) {
	global $g_libraries_included;
	global $g_library_path;
	if ( !isset( $g_libraries_included[$p_library_name] ) ) {
		$t_existing_globals = get_defined_vars();

		$t_library_file_path = $g_library_path . $p_library_name;
		if ( !file_exists( $t_library_file_path ) ) {
			echo "External library '$t_library_file_path' not found.";
			exit;
		}

		require_once( $t_library_file_path );
		$t_new_globals = array_diff_key( get_defined_vars(), $GLOBALS, array( 't_existing_globals' => 0, 't_new_globals' => 0 ) );
		foreach ( $t_new_globals as $t_global_name => $t_global_value ) {
			global $$t_global_name;
		}
		extract( $t_new_globals );
		$g_libraries_included[$p_library_name] = 1;
	}
}

/**
 * Define an autoload function to automatically load classes when referenced
 *
 * @param string $p_class class name
 */
function __autoload( $className ) {
	global $g_class_path;
	global $g_library_path;

	$t_require_path = $g_class_path . $className . '.class.php';

	if ( file_exists( $t_require_path ) ) {
		require_once( $t_require_path );
		return;
	}

	$t_require_path = $g_library_path . 'rssbuilder' . DIRECTORY_SEPARATOR . 'class.' . $className . '.inc.php';

	if ( file_exists( $t_require_path ) ) {
		require_once( $t_require_path );
		return;
	}
}

# Register the autoload function to make it effective immediately
spl_autoload_register( '__autoload' );

# Load UTF8-capable string functions
define( 'UTF8', $g_library_path . 'utf8' );
require_lib( 'utf8/utf8.php' );
require_lib( 'utf8/str_pad.php' );

# Include PHP compatibility file
require_api( 'php_api.php' );

# Enforce our minimum PHP requirements
if( !php_version_at_least( PHP_MIN_VERSION ) ) {
	@ob_end_clean();
	echo '<strong>FATAL ERROR: Your version of PHP is too old. MantisBT requires PHP version ' . PHP_MIN_VERSION . ' or newer</strong><br />Your version of PHP is version ' . phpversion();
	die();
}

# Ensure that output is blank so far (output at this stage generally denotes
# that an error has occurred)
if ( ( $t_output = ob_get_contents() ) != '' ) {
	echo 'Possible Whitespace/Error in Configuration File - Aborting. Output so far follows:<br />';
	echo var_dump( $t_output );
	die;
}
unset( $t_output );

# Start HTML compression handler (if enabled)
require_api( 'compress_api.php' );
compress_start_handler();

# config_inc may not be present if this is a new install
$t_config_inc_found = file_exists( $g_config_path . 'config_inc.php' );

if ( $t_config_inc_found ) {
	require_once( $g_config_path . 'config_inc.php' );
}

# If no configuration file exists, redirect the user to the admin page so
# they can complete installation and configuration of MantisBT
if ( false === $t_config_inc_found ) {
	if( php_sapi_name() == 'cli' ) {
		echo "Error: " . $g_config_path . "config_inc.php file not found; ensure MantisBT is properly setup.\n";
		exit(1);
	}

	if ( !( isset( $_SERVER['SCRIPT_NAME'] ) && ( 0 < strpos( $_SERVER['SCRIPT_NAME'], 'admin' ) ) ) ) {
		header( 'Content-Type: text/html' );
		# Temporary redirect (307) instead of Found (302) default
		header( 'Location: admin/install.php', true, 307 );
		# Make sure it's not cached
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		exit;
	}
}

# Initialise cryptographic keys
require_api( 'crypto_api.php' );
crypto_init();

# Connect to the database
require_api( 'database_api.php' );
require_api( 'config_api.php' );

if ( !defined( 'MANTIS_MAINTENANCE_MODE' ) ) {
	if( OFF == $g_use_persistent_connections ) {
		db_connect( config_get_global( 'dsn', false ), $g_hostname, $g_db_username, $g_db_password, $g_database_name, config_get_global( 'db_schema' ) );
	} else {
		db_connect( config_get_global( 'dsn', false ), $g_hostname, $g_db_username, $g_db_password, $g_database_name, config_get_global( 'db_schema' ), true );
	}
}

# Initialise plugins
if ( !defined( 'PLUGINS_DISABLED' ) && !defined( 'MANTIS_MAINTENANCE_MODE' ) ) {
	require_api( 'plugin_api.php' );
	plugin_init_installed();
}

# Initialise Wiki integration
if( config_get_global( 'wiki_enable' ) == ON ) {
	require_api( 'wiki_api.php' );
	wiki_init();
}

if ( !isset( $g_login_anonymous ) ) {
	$g_login_anonymous = true;
}

# Attempt to set the current timezone to the user's desired value
# Note that PHP 5.1 on RHEL/CentOS doesn't support the timezone functions
# used here so we just skip this action on RHEL/CentOS platforms.
if ( function_exists( 'timezone_identifiers_list' ) ) {
	if ( in_array ( config_get_global( 'default_timezone' ), timezone_identifiers_list() ) ) {
		// if a default timezone is set in config, set it here, else we use php.ini's value
		// having a timezone set avoids a php warning
		date_default_timezone_set( config_get_global( 'default_timezone' ) );
	} else {
		# To ensure proper detection of timezone settings issues, we must not
		# initialize the default timezone when executing admin checks
		if( basename( $g_short_path ) != 'check' ) {
			config_set_global( 'default_timezone', date_default_timezone_get(), true );
		}
	}

	require_api( 'authentication_api.php' );
	if( auth_is_user_authenticated() ) {
		require_api( 'user_pref_api.php' );

		$t_user_timezone = user_pref_get_pref( auth_get_current_user_id(), 'timezone' );
		if ( !is_blank( $t_user_timezone ) ) {
			date_default_timezone_set( $t_user_timezone );
		}
	}
}

if ( !defined( 'MANTIS_MAINTENANCE_MODE' ) ) {
	require_api( 'collapse_api.php' );
	collapse_cache_token();
}

# Load custom functions
require_api( 'custom_function_api.php' );

if ( file_exists( $g_config_path . 'custom_functions_inc.php' ) ) {
	require_once( $g_config_path . 'custom_functions_inc.php' );
}

# Set HTTP response headers
require_api( 'http_api.php' );
http_all_headers();

# Push default language to speed calls to lang_get
if ( !defined( 'LANG_LOAD_DISABLED' ) ) {
	require_api( 'lang_api.php' );
	lang_push( lang_get_default() );
}

# Signal plugins that the core system is loaded
if ( !defined( 'PLUGINS_DISABLED' ) && !defined( 'MANTIS_MAINTENANCE_MODE' ) ) {
	require_api( 'event_api.php' );
	event_signal( 'EVENT_CORE_READY' );
}
