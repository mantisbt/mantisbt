<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: core.php,v 1.8 2003-01-18 02:14:12 jfitzell Exp $
	# --------------------------------------------------------

	###########################################################################
	# INCLUDES
	###########################################################################

	# Before doing anything else, start output buffering so we don't prevent
	#  headers from being sent if there's a blank line in an included file
	ob_start();

	# we change dirs (and restore below) to compensate for scripts that may
	# include by relative paths
	$t_cwd = getcwd();
	chdir( dirname( __FILE__ ) );

	$t_core_path = dirname( __FILE__ ) . '/core/';
	require_once( $t_core_path . 'php_api.php' );

	# Load constants and configuration files
  	require_once( 'constant_inc.php' );
	if ( file_exists( 'custom_constant_inc.php' ) ) {
		require_once( 'custom_constant_inc.php' );
	}
	require_once( 'config_defaults_inc.php' );
	if ( file_exists( 'custom_config_inc.php' ) ) {
		require_once( 'custom_config_inc.php' );
	}
	# for backward compatability
	if ( file_exists( 'config_inc.php' ) ) {
		require_once( 'config_inc.php' );
	}

	# Allow an environment variable (defined in an Apache vhost for example)
	#  to specify a config file to load to override other local settings
	$t_local_config = getenv( 'MANTIS_CONFIG' );
	if( $t_local_config && file_exists( $t_local_config ) ){
		require_once( $t_local_config );
	}


	# Load rest of core in seperate directory.
	require_once( $g_core_path . 'API.php');

	chdir( $t_cwd );
?>