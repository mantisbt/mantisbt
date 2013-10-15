<?php
# MantisConnect - A webservice interface to Mantis Bug Tracker
# Copyright 2004  Victor Boctor - vboctor@users.sourceforge.net
# This program is distributed under dual licensing.  These include
# GPL and a commercial licenses.  Victor Boctor reserves the right to
# change the license of future releases.
# See docs/ folder for more details

/**
 * MantisConnect - A webservice interface to Mantis Bug Tracker
 *
 * @package MantisBT
 * @copyright Copyright 2004  Victor Boctor - vboctor@users.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * Get username, realname and email from for a given user id
 */
function mci_account_get_array_by_id( $p_user_id ) {
	$t_result = array();
	$t_result['id'] = $p_user_id;

	if( user_exists( $p_user_id ) ) {
		$t_result['name'] = user_get_field( $p_user_id, 'username' );
		$t_dummy = user_get_field( $p_user_id, 'realname' );

		if( !empty( $t_dummy ) ) {
			$t_result['real_name'] = $t_dummy;
		}

		$t_dummy = user_get_field( $p_user_id, 'email' );

		if( !empty( $t_dummy ) ) {
			$t_result['email'] = $t_dummy;
		}
	}
	return $t_result;
}

/**
 * Get username, realname and email from for a set of given user ids
 */
function mci_account_get_array_by_ids ( $p_user_ids ) {
    
    $t_result = array();
    
    foreach ( $p_user_ids as $t_user_id ) {
        $t_result[] = mci_account_get_array_by_id( $t_user_id );
    }
    
    return $t_result;
}
