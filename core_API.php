<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	###########################################################################
	# INCLUDES
	###########################################################################

	# Before doing anything else, start output buffering so we don't prevent
	#  headers from being sent if there's a blank line in an included file
	ob_start();

	# Load constants and configuration files
  	require( 'constant_inc.php' );
	if ( file_exists( 'custom_constant_inc.php' ) ) {
		include( 'custom_constant_inc.php' );
	}
	require( 'config_defaults_inc.php' );
	if ( file_exists( 'custom_config_inc.php' ) ) {
		include( 'custom_config_inc.php' );
	}
	# for backward compatability
	if ( file_exists( 'config_inc.php' ) ) {
		include( 'config_inc.php' );
	}
	
	# Load rest of core in seperate directory.
	include( $g_core_path . 'API.php');
	# --------------------
?>
