<?php
	# MantisConnect - A webservice interface to Mantis Bug Tracker
	# Copyright (C) 2004-2007  Victor Boctor - vboctor@users.sourceforge.net
	# This program is distributed under dual licensing.  These include
	# GPL and a commercial licenses.  Victor Boctor reserves the right to
	# change the license of future releases.
	# See docs/ folder for more details

	# --------------------------------------------------------
	# $Id: mc_config_api.php,v 1.1 2007-07-18 06:52:49 vboctor Exp $
	# --------------------------------------------------------

	# --------------------
	function mc_config_get_string( $p_username, $p_password, $p_config_var ) {
		$t_user_id = mci_check_login( $p_username, $p_password );
		if ( $t_user_id === false ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}

		if ( !mci_has_readonly_access( $t_user_id ) ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}

		if ( mci_config_is_private( $p_config_var ) ) {
			return new soap_fault( 'Client', '', "Access to '$p_config_var' is denied" );
		}

		if ( !config_is_set( $p_config_var ) ) {
			return new soap_fault( 'Client', '', "Config '$p_config_var' is undefined" );
		}

		return config_get( $p_config_var );
	}

	# --------------------
	function mci_config_is_private( $p_config_var ) {
		switch( $p_config_var ) {
			case 'hostname':
			case 'port':
			case 'db_username':
			case 'db_password':
			case 'password_confirm_hash_magic_string':
			case 'smtp_host':
			case 'smtp_username':
			case 'smtp_password':
				return true;
		}

		if ( !is_string( $p_config_var ) ) {
			return true;
		}

		return false;
	}
?>
