<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: core.php,v 1.33 2004-05-24 22:23:06 vboctor Exp $
	# --------------------------------------------------------

	###########################################################################
	# INCLUDES
	###########################################################################

	# Before doing anything else, start output buffering so we don't prevent
	#  headers from being sent if there's a blank line in an included file
	ob_start( 'compress_handler' );

	# Include compatibility file before anything else
	require_once( dirname( __FILE__ ).DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'php_api.php' );

	# Check if Mantis is down for maintenance
	#
	#   To make Mantis 'offline' simply create a file called
	#   'mantis_offline.php' in the mantis root directory.
	#   Users are redirected to that file if it exists.
	#   If you have to test Mantis while it's offline, add the
	#   parameter 'mbadmin=1' to the URL.
	#
	$t_mantis_offline = 'mantis_offline.php';
	if ( file_exists( $t_mantis_offline ) && !isset( $_GET['mbadmin'] ) ) {
		include( $t_mantis_offline );
		exit;
	}


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
	require_once( $t_core_path.'compress_api.php' );
	
	# Load internationalization functions (needed before database_api, in case database connection fails)
	require_once( $t_core_path.'lang_api.php' );

	# error functions should be loaded to allow database to print errors
	require_once( $t_core_path.'authentication_api.php' );
	require_once( $t_core_path.'html_api.php' );
	require_once( $t_core_path.'error_api.php' );
	require_once( $t_core_path.'gpc_api.php' );

	# custom functions (in main directory)
	# @@@ Move all such files to core/
	require_once( $t_core_path . 'custom_function_api.php' );
	$t_overrides = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'custom_functions_inc.php';
	if ( file_exists( $t_overrides ) ) {
		require_once( $t_overrides );
	}

	# initialize our timer
	$g_timer = new BC_Timer;

	# seed random number generator
	list( $usec, $sec ) = explode( ' ', microtime() );
	mt_srand( $sec*$usec );

	# DATABASE WILL BE OPENED HERE!!  THE DATABASE SHOULDN'T BE EXPLICITLY
	# OPENED ANYWHERE ELSE.
	require_once( $t_core_path.'database_api.php' );

	# Headers to prevent caching
	header( 'Pragma: no-cache' );
	header( 'Expires: Fri, 01 Jan 1999 00:00:00 GMT' );
	header( 'Cache-Control: no-store, no-cache, must-revalidate' );
	header( 'Cache-Control: post-check=0, pre-check=0', false );

	# SEND USER-DEFINED HEADERS
	foreach( config_get( 'custom_headers' ) as $t_header ) {
		header( $t_header );
	}

	require_once( $t_core_path.'project_api.php' );
	require_once( $t_core_path.'access_api.php' );
	require_once( $t_core_path.'print_api.php' );
	require_once( $t_core_path.'helper_api.php' );
	require_once( $t_core_path.'user_api.php' );
?>