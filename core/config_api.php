<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: config_api.php,v 1.14 2004-07-10 23:38:01 vboctor Exp $
	# --------------------------------------------------------

	### Configuration API ###

	# ------------------
	# Retrieves the value of a config option
	#  This function will return one of (in order of preference):
	#    1. The user-defined value (if set)
	#    2. The default value (if known)
	#    3. The value passed as the second parameter of the function
	function config_get( $p_option, $p_default = null ) {

		# ------ global variable implementation ------
		# this function implements getting configuration
		#  from our current global variable scheme. This
		#  interface should remain constant but we could
		#  call out to other functions or replace this code
		#  to use a DB or some other method

		if ( isset( $GLOBALS['g_' . $p_option] ) ) {
			return $GLOBALS['g_' . $p_option];
		} else {
			# unless we were allowing for the option not to exist by passing
			#  a default, trigger a WARNING
			if ( null == $p_default ) {
				error_parameters( $p_option );
				trigger_error( ERROR_CONFIG_OPT_NOT_FOUND, WARNING );
			}
			return $p_default;
		}
	}

	# ------------------
	# Returns true if the specified config option exists (ie. a
	#  value or default can be found), false otherwise
	function config_is_set( $p_option ) {
		if ( isset( $GLOBALS['g_' . $p_option] ) ) {
			return true;
		} else {
			return false;
		}
	}

	# ------------------
	# Sets the value of the given config option to the given value
	#  If the config option does not exist, an ERROR is triggered
	function config_set( $p_option, $p_value ) {
		if ( !isset( $GLOBALS['g_' . $p_option] ) ) {
			trigger_error( ERROR_CONFIG_OPT_NOT_FOUND, ERROR );
		}

		$GLOBALS['g_' . $p_option] = $p_value;

		return true;
	}
	# ------------------
	# Checks if an obsolete configuration variable is still in use.  If so, an error
	# will be generated and the script will exit.  This is called from admin_check.php.
	function config_obsolete( $p_var, $p_replace ) {
		# @@@ we could trigger a WARNING here, once we have errors that can
		#     have extra data plugged into them (we need to give the old and
		#     new config option names in the warning text)

		if ( config_is_set( $p_var ) ) {
			PRINT '<p><b>Warning:</b> The configuration option <tt>$g_' . $p_var . '</tt> is now obsolete';
			if ( is_array( $p_replace ) ) {
				PRINT ', please see the following options: <ul>';
				foreach ( $p_replace as $t_option ) {
					PRINT '<li>$g_' . $t_option . '</li>';
				}
				PRINT '</ul>';
			} else if ( !is_blank( $p_replace ) ) {
				PRINT ', please use <tt>$g_' . $p_replace . '</tt> instead.';
			}
			PRINT '</p>';
		}
	}
?>