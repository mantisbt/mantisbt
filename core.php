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
 * @package MantisBT
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
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

$g_request_time = microtime(true);

ob_start();

/**
 * Load supplied constants
 */
require_once( dirname( __FILE__ ).DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'constant_inc.php' );

/**
 * Load user-defined constants (if required)
 */
if ( file_exists( dirname( __FILE__ ).DIRECTORY_SEPARATOR.'custom_constants_inc.php' ) ) {
	require_once( dirname( __FILE__ ).DIRECTORY_SEPARATOR.'custom_constants_inc.php' );
# Check for the old name of the user-defined constants file (to be deprecated in 1.3)
} else if ( file_exists( dirname( __FILE__ ).DIRECTORY_SEPARATOR.'custom_constant_inc.php' ) ) {
	require_once( dirname( __FILE__ ).DIRECTORY_SEPARATOR.'custom_constant_inc.php' );
}

$t_config_inc_found = false;

/**
 * Include default configuration settings
 */
require_once( dirname( __FILE__ ).DIRECTORY_SEPARATOR.'config_defaults_inc.php' );

# config_inc may not be present if this is a new install
if ( file_exists( dirname( __FILE__ ).DIRECTORY_SEPARATOR.'config_inc.php' ) ) {
	require_once( dirname( __FILE__ ).DIRECTORY_SEPARATOR.'config_inc.php' );
	$t_config_inc_found = true;
}

# Allow an environment variable (defined in an Apache vhost for example)
#  to specify a config file to load to override other local settings
$t_local_config = getenv( 'MANTIS_CONFIG' );
if ( $t_local_config && file_exists( $t_local_config ) ){
	require_once( $t_local_config );
	$t_config_inc_found = true;
}


# Attempt to find the location of the core files.
$t_core_path = dirname(__FILE__).DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR;
if (isset($GLOBALS['g_core_path']) && !isset( $HTTP_GET_VARS['g_core_path'] ) && !isset( $HTTP_POST_VARS['g_core_path'] ) && !isset( $HTTP_COOKIE_VARS['g_core_path'] ) ) {
	$t_core_path = $g_core_path;
}

$g_core_path = $t_core_path;

/*
 * Set include paths
 */
define ( 'BASE_PATH' , realpath( dirname(__FILE__) ) );
$mantisLibrary = BASE_PATH . DIRECTORY_SEPARATOR . 'library';
$mantisCore = $g_core_path;

/*
 * Prepend the application/ and tests/ directories to the
 * include_path.  
 */
$path = array(
    $mantisCore,
    $mantisLibrary,
    get_include_path()
    );
set_include_path( implode( PATH_SEPARATOR, $path ) );

/*
 * Unset global variables that are no longer needed.
 */
unset($mantisRoot, $mantisLibrary, $mantisCore, $path);

require_once( 'mobile_api.php' );

if ( strlen( $GLOBALS['g_mantistouch_url'] ) > 0 && mobile_is_mobile_browser() ) {
	$t_url = sprintf( $GLOBALS['g_mantistouch_url'], $GLOBALS['g_path'] );

	if ( OFF == $g_use_iis ) {
		header( 'Status: 302' );
	}

	header( 'Content-Type: text/html' );

	if ( ON == $g_use_iis ) {
		header( "Refresh: 0;$t_url" );
	} else {
		header( "Location: $t_url" );
	}

	exit; # additional output can cause problems so let's just stop output here
}

# load UTF8-capable string functions
require_once( 'utf8/utf8.php' );
require_once( UTF8 . '/str_pad.php' );

# Include compatibility file before anything else
require_once( 'php_api.php' );

# Define an autoload function to automatically load classes when referenced.
function __autoload( $className ) {
	global $g_core_path;

	$t_require_path = $g_core_path . 'classes' . DIRECTORY_SEPARATOR . $className . '.class.php';

	if ( file_exists( $t_require_path ) ) {
		require_once( $t_require_path );
		return;
	}

	$t_require_path = BASE_PATH . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'rssbuilder' . DIRECTORY_SEPARATOR . 'class.' . $className . '.inc.php';

	if ( file_exists( $t_require_path ) ) {
		require_once( $t_require_path );
		return;
	}
}

spl_autoload_register( '__autoload' );

if ( ($t_output = ob_get_contents()) != '') {
	echo 'Possible Whitespace/Error in Configuration File - Aborting. Output so far follows:<br />';
	echo var_dump($t_output);
	die;
}

require_once( 'utility_api.php' );
require_once( 'compress_api.php' );

compress_start_handler();

if ( false === $t_config_inc_found ) {
	# if not found, redirect to the admin page to install the system
	# this needs to be long form and not replaced by is_page_name as that function isn't loaded yet
	if ( !( isset( $_SERVER['SCRIPT_NAME'] ) && ( 0 < strpos( $_SERVER['SCRIPT_NAME'], 'admin' ) ) ) ) {
		if ( OFF == $g_use_iis ) {
			header( 'Status: 302' );
		}
		header( 'Content-Type: text/html' );

		if ( ON == $g_use_iis ) {
			header( "Refresh: 0;url=admin/install.php" );
		} else {
			header( "Location: admin/install.php" );
		}

		exit; # additional output can cause problems so let's just stop output here
	}
}

# Load rest of core in separate directory.

require_once( 'config_api.php' );
require_once( 'logging_api.php' );

# Load internationalization functions (needed before database_api, in case database connection fails)
require_once( 'lang_api.php' );

# error functions should be loaded to allow database to print errors
require_once( 'error_api.php' );
require_once( 'helper_api.php' );

# DATABASE WILL BE OPENED HERE!!  THE DATABASE SHOULDN'T BE EXPLICITLY
# OPENED ANYWHERE ELSE.
require_once( 'database_api.php' );

# PHP Sessions
require_once( 'session_api.php' );

# Initialize Event System
require_once( 'event_api.php' );
require_once( 'events_inc.php' );

# Plugin initialization
require_once( 'plugin_api.php' );
if ( !defined( 'PLUGINS_DISABLED' ) ) {
	plugin_init_installed();
}

# Authentication and user setup
require_once( 'authentication_api.php' );
require_once( 'project_api.php' );
require_once( 'project_hierarchy_api.php' );
require_once( 'user_api.php' );
require_once( 'access_api.php' );

# Wiki Integration
if( config_get_global( 'wiki_enable' ) == ON ) {
	require_once( 'wiki_api.php' );
	wiki_init();
}

# Display API's
require_once( 'http_api.php' );
require_once( 'html_api.php' );
require_once( 'gpc_api.php' );
require_once( 'form_api.php' );
require_once( 'print_api.php' );
require_once( 'collapse_api.php' );

if ( !isset( $g_login_anonymous ) ) {
	$g_login_anonymous = true;
}

# Attempt to set the current timezone to the user's desired value
# Note that PHP 5.1 on RHEL/CentOS doesn't support the timezone functions
# used here so we just skip this action on RHEL/CentOS platforms.
if ( function_exists( 'timezone_identifiers_list' ) ) {
	if ( !is_blank ( config_get_global( 'default_timezone' ) ) ) {
		// if a default timezone is set in config, set it here, else we use php.ini's value
		// having a timezone set avoids a php warning
		date_default_timezone_set( config_get_global( 'default_timezone' ) );
	} else {
		config_set_global( 'default_timezone', date_default_timezone_get(), true );
	}
	if ( auth_is_user_authenticated() ) {
		date_default_timezone_set( user_pref_get_pref( auth_get_current_user_id(), 'timezone' ) );
	}
}

if ( !defined( 'MANTIS_INSTALLER' ) ) {
	collapse_cache_token();
}

// custom functions (in main directory)
/** @todo Move all such files to core/ */
require_once( 'custom_function_api.php' );
$t_overrides = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'custom_functions_inc.php';
if ( file_exists( $t_overrides ) ) {
	require_once( $t_overrides );
}

// set HTTP response headers
http_all_headers();

// push push default language to speed calls to lang_get
if ( !isset( $g_skip_lang_load ) ) {
	lang_push( lang_get_default() );
}

# signal plugins that the core system is loaded
event_signal( 'EVENT_CORE_READY' );

