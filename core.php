<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: core.php,v 1.22 2003-02-17 01:50:09 jfitzell Exp $
	# --------------------------------------------------------

	###########################################################################
	# INCLUDES
	###########################################################################

	# Before doing anything else, start output buffering so we don't prevent
	#  headers from being sent if there's a blank line in an included file
	ob_start( 'compress_handler' );

	# Include compatibility file before anything else
	require_once( dirname( __FILE__ ).DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'php_api.php' );

	# Load constants and configuration files
  	require_once( dirname( __FILE__ ).DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'constant_inc.php' );
	if ( file_exists( dirname( __FILE__ ).DIRECTORY_SEPARATOR.'custom_constant_inc.php' ) ) {
		require_once( dirname( __FILE__ ).DIRECTORY_SEPARATOR.'custom_constant_inc.php' );
	}
	require_once( dirname( __FILE__ ).DIRECTORY_SEPARATOR.'config_defaults_inc.php' );
	require_once( dirname( __FILE__ ).DIRECTORY_SEPARATOR.'config_inc.php' );

	# Allow an environment variable (defined in an Apache vhost for example)
	#  to specify a config file to load to override other local settings
	$t_local_config = getenv( 'MANTIS_CONFIG' );
	if( $t_local_config && file_exists( $t_local_config ) ){
		require_once( $t_local_config );
	}


	# Attempt to find the location of the core files.	
	$t_core_path = dirname(__FILE__).DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR;
	if (isset($GLOBALS['g_core_path']) && !isset( $HTTP_GET_VARS['g_core_path'] ) && !isset( $HTTP_POST_VARS['g_core_path'] ) && !isset( $HTTP_COOKIE_VARS['g_core_path'] ) ) {
		$t_core_path = $g_core_path;
	}

	# Load rest of core in seperate directory.

	require_once( $t_core_path.'config_api.php' );
	require_once( $t_core_path.'timer_api.php' );

	# load utility functions used by everything else
	require_once( $t_core_path.'utility_api.php' );
	
	# Load internationalization functions (needed before database_api, in case database connection fails)
	require_once( $t_core_path.'lang_api.php' );

	# error functions should be loaded to allow database to print errors
	require_once( $t_core_path.'html_api.php' );
	require_once( $t_core_path.'error_api.php' );

	# initialize our timer
	$g_timer = new BC_Timer;

	# seed random number generator
	list( $usec, $sec ) = explode( ' ', microtime() );
	mt_srand( $sec*$usec );

	# DATABASE WILL BE OPENED HERE!!  THE DATABASE SHOULDN'T BE EXPLICITLY
	# OPENED ANYWHERE ELSE.
	require_once( $t_core_path.'database_api.php' );

	# SEND USER-DEFINED HEADERS
	foreach( config_get( 'custom_headers' ) as $t_header ) {
		header( $t_header );
	}

	require_once( $t_core_path.'project_api.php' );
	require_once( $t_core_path.'gpc_api.php' );
	require_once( $t_core_path.'authentication_api.php' );
	require_once( $t_core_path.'access_api.php' );
	require_once( $t_core_path.'print_api.php' );
	require_once( $t_core_path.'helper_api.php' );
	require_once( $t_core_path.'user_api.php' );
?>
