<?php
# MantisConnect - A webservice interface to Mantis Bug Tracker
# Copyright (C) 2004-2014  Victor Boctor - vboctor@users.sourceforge.net
# This program is distributed under dual licensing.  These include
# GPL and a commercial licenses.  Victor Boctor reserves the right to
# change the license of future releases.
# See docs/ folder for more details

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
