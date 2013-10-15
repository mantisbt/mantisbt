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
 * Get the value for the specified user preference.
 *
 * @param string   $p_username    The user's username
 * @param string   $p_password    The user's password
 * @param int      $p_project_id  Project ID (0 = ALL_PROJECTS (mantisbt/core/constant_inc.php))
 * @param string   $p_pref_name   The name of the preference
 * @return string  $t_user_pref   The requested preference value
 */
function mc_user_pref_get_pref( $p_username, $p_password, $p_project_id, $p_pref_name ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if ( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	if ( !mci_has_readonly_access( $t_user_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	return user_pref_get_pref( $t_user_id, $p_pref_name, $p_project_id );
}
