<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Revision: 1.1 $
	# $Author: jlatour $
	# $Date: 2002-08-22 18:05:08 $
	#
	# $Id: core_security_API.php,v 1.1 2002-08-22 18:05:08 jlatour Exp $
	# --------------------------------------------------------

	###########################################################################
	# Security API
	###########################################################################

	# ---------------
	# Retrieves a GPC variable.
	# If the variable is not set, the default is returned. 
	# If magic_quotes_gpc is on, slashes will be stripped from the value before being returned.
	function get_var( $p_var_name, $p_default = 'nil' ) {
		global $_REQUEST;

		if ( isset( $_REQUEST[$p_var_name] ) ) {
			$t_result = $_REQUEST[$p_var_name];
			if (get_magic_quotes_gpc() == 1) {
				$t_result = stripslashes( $t_result );
			}
		} else if ( 'nil' != $p_default) {
			$t_result = $p_default;
		} else {
			# To be implemented later.
			# trigger_error("Variable '$p_var_name' with no default is missing", E_USER_ERROR);
		}
		
		return $t_result;
	}
	# -----------------
	# Retrieves a string GPC variable. Uses get_var().
	function get_var_string( $p_var_name, $p_default = 'nil' ) {
		return get_var( $p_var_name, $p_default );
	}
	# ------------------
	# Retrieves an integer GPC variable. Uses get_var().
	function get_var_int( $p_var_name, $p_default = 'nil' ) {
		return (integer)(get_var( $p_var_name, $p_default ));
	}
	# ------------------
	# Retrieves a boolean GPC variable. Uses get_var();
	function get_var_bool( $p_var_name, $p_default = 'nil' ) {
		$t_result = get_var( $p_var_name, $p_default );

		if ( 0 == strcasecmp( 'off', $t_result ) ||
			 0 == strcasecmp( 'no', $t_result ) ||
			 0 == strcasecmp( 'false', $t_result ) ||
			 0 == strcasecmp( '0', $t_result ) ) {
			return false;
		} else {
			return true;
		}
	}
?>
