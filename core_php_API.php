<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	###########################################################################
	# PHP Compatibility API
	#
	# Methods to help in backwards compatibility of PHP versions, etc.
	#
	###########################################################################

	# --------------------
	# Returns true if the current PHP version is higher than the one
	#  specified in the given string
	function php_version_at_least( $p_version_string ) {
		$t_curver = array_pad( explode( '.', phpversion() ), 3, 0 );
		$t_minver = array_pad( explode( '.', $p_version_string ), 3, 0 );

		for ($i = 0 ; $i < 3 ; $i = $i + 1 ) {
			if ( (int)$t_curver[$i] < (int)$t_minver[$i] ) {
				return false;
			} else if ( (int)$t_curver[$i] > (int)$t_minver[$i] ) {
				return true;
			}
		}

		# if we get here, the versions must match exactly so:
		return true; 
	}
	# --------------------

	# Enforce our minimum requirements
	if ( ! php_version_at_least( PHP_MIN_VERSION ) ) {
		ob_end_clean();
		echo "<b>Your version of PHP is too old.  Mantis requires PHP version 4.0.3 or newer to operate</b>";
		die();
	}

	ini_set('magic_quotes_runtime', 0);

	# @@@ Experimental
	# deal with register_globals being Off
	# @@@ NOTE we want to get rid of this once we start getting all
	#      our GPC variables with functions.  In fact we may want to
	#      turn off register_global_variables if we can
	if ( false == ini_get( 'register_globals' ) ) {
		if ( php_version_at_least( '4.1.0' ) ) {
			extract( $_REQUEST );
			extract( $_SERVER );
		} else {
			extract( $HTTP_POST_VARS );
			extract( $HTTP_GET_VARS );
			extract( $HTTP_SERVER_VARS );
		}
	}
	
	# Experimental support for $_* auto-global variables in PHP < 4.1.0
	if ( ! php_version_at_least( '4.1.0' ) ) {
		global $_REQUEST, $_GET, $_POST, $_COOKIE, $_SERVER;
		
		$_GET = $HTTP_GET_VARS;
		$_POST = $HTTP_POST_VARS;
		$_COOKIE = $HTTP_COOKIE_VARS;
		$_SERVER = $HTTP_SERVER_VARS;
		
		$_REQUEST = $HTTP_COOKIE_VARS;
		foreach ($HTTP_POST_VARS as $key => $value) {
			$_REQUEST[$key] = $value;
		}
		foreach ($HTTP_GET_VARS as $key => $value) {
			$_REQUEST[$key] = $value;
		}
	}

	########################
	# PHP Constants
	########################

	# Directory separator was introduced in PHP 4.0.6
	if ( !defined( 'DIRECTORY_SEPARATOR' ) ) {
		if (substr(php_uname(), 0, 7) == 'Windows') {
			define('DIRECTORY_SEPARATOR', '\\');
		} else {
			define('DIRECTORY_SEPARATOR', '/');
		}
	}

?>
