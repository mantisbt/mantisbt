<?php
# MantisConnect - A webservice interface to Mantis Bug Tracker
# Copyright (C) 2004-2014  Victor Boctor - vboctor@users.sourceforge.net
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
		return SoapObjectsFactory::newSoapFault( 'Client', "Access to '$p_config_var' is denied" );
	}

	if( !config_is_set( $p_config_var ) ) {
		return SoapObjectsFactory::newSoapFault( 'Client', "Config '$p_config_var' is undefined" );
	}

	$t_value = config_get( $p_config_var );

	# If array, serialize to string to avoid php error relating to serializing array as string.
	if ( is_array( $t_value ) ) {
		$t_value = mci_serialize_array( $t_value );
	}

	return $t_value;
}

/**
 * Serialize a standard or associative array to a string.
 * Elements are going to be separated by a new line.
 * Key and value are going to eb separated by a tab.
 * Nested arrays are not supported.  Type of keys/values doesn't affect the output.
 * @param $p_array The array to serialize.
 */
function mci_serialize_array( $p_array ) {
	$t_associative = array_keys( $p_array ) !== range( 0, count( $p_array ) - 1 );
	$t_result = '';
	$t_key_value_separator = "\t";
	$t_value_separator = "\n";

	if ( $t_associative ) {
		foreach ( $p_array as $t_key => $t_value ) {
			if ( !empty( $t_result ) ) {
				$t_result .= $t_value_separator;
			}

			$t_result .= $t_key . $t_key_value_separator . $t_value;
		}
	} else {
		foreach ( $p_array as $t_value ) {
			if ( !empty( $t_result ) ) {
				$t_result .= $t_value_separator;
			}

			$t_result .= $t_value;
		}
	}

	return $t_result;
}

