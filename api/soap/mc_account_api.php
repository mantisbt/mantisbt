<?php
# MantisConnect - A webservice interface to Mantis Bug Tracker
# Copyright (C) 2004-2014  Victor Boctor - vboctor@users.sourceforge.net
# This program is distributed under dual licensing.  These include
# GPL and a commercial licenses.  Victor Boctor reserves the right to
# change the license of future releases.
# See docs/ folder for more details

function mci_account_get_array_by_id( $p_user_id ) {
	$t_result = array();
	$t_result['id'] = $p_user_id;

	if( user_exists( $p_user_id ) ) {

		$t_current_user_id = auth_get_current_user_id();
		$t_access_level = user_get_field ( $t_current_user_id, 'access_level' );
		$t_can_manage = access_has_global_level( config_get( 'manage_user_threshold' ) ) &&
			access_has_global_level( $t_access_level );

		# this deviates from the behaviour of view_user_page.php, but it is more intuitive
		$t_is_same_user = $t_current_user_id === $p_user_id;

		$t_can_see_realname = access_has_project_level( config_get( 'show_user_realname_threshold' ) );
		$t_can_see_email = access_has_project_level( config_get( 'show_user_email_threshold' ) );

		$t_result['name'] = user_get_field( $p_user_id, 'username' );

		if ( $t_is_same_user || $t_can_manage || $t_can_see_realname ) {
			$t_realname = user_get_realname( $p_user_id );

			if( !empty( $t_realname ) ) {
				$t_result['real_name'] = $t_realname;
			}
		}

		if ( $t_is_same_user || $t_can_manage || $t_can_see_email ) {
			$t_email = user_get_email( $p_user_id );

			if( !empty( $t_email ) ) {
				$t_result['email'] = $t_email;
			}
		}
	}
	return $t_result;
}

function mci_account_get_array_by_ids ( $p_user_ids ) {
    
    $t_result = array();
    
    foreach ( $p_user_ids as $t_user_id ) {
        $t_result[] = mci_account_get_array_by_id( $t_user_id );
    }
    
    return $t_result;
}

/**
* Add a new user.
*
* @param string $p_username  The name of the user trying to create an account.
* @param string $p_password  The password of the user.
* @param Array $p_user A new AccountData structure
* @return integer The new users's users_id
*/
function mc_account_add( $p_username, $p_password, $p_user, $p_pass ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if ( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}
	if ( !mci_has_administrator_access( $t_user_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	$p_user = SoapObjectsFactory::unwrapObject( $p_user );

	// create user account
	if ( !user_create($p_user['name'], $p_pass, $p_user['email'], $p_user['access'], false, true, $p_user['real_name']))
		return SoapObjectsFactory::newSoapFault( 'Server', 'user could not be created');

	// return id of new user back to caller
	return user_get_id_by_name($p_user['name']);
}

/**
* Delete a user.
*
* @param string $p_username The name of the user trying to delete an account.
* @param string $p_password The password of the user.
* @param integer $p_user_id The id of the user to delete.
* @return bool Returns true or false depending on the success of the delete action.
*/
function mc_account_delete( $p_username, $p_password, $p_user_id ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if ( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}
	if ( !mci_has_administrator_access( $t_user_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	return user_delete($p_user_id);
}
