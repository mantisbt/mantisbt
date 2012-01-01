<?php
# MantisConnect - A webservice interface to Mantis Bug Tracker
# Copyright (C) 2004-2012  Victor Boctor - vboctor@users.sourceforge.net
# This program is distributed under dual licensing.  These include
# GPL and a commercial licenses.  Victor Boctor reserves the right to
# change the license of future releases.
# See docs/ folder for more details

function mc_config_get_string( $p_username, $p_password, $p_config_var ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	if( !mci_has_readonly_access( $t_user_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	if( config_is_private( $p_config_var ) ) {
		return new soap_fault( 'Client', '', "Access to '$p_config_var' is denied" );
	}

	if( !config_is_set( $p_config_var ) ) {
		return new soap_fault( 'Client', '', "Config '$p_config_var' is undefined" );
	}

	return config_get( $p_config_var );
}

