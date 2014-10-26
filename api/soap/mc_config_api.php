<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * A webservice interface to Mantis Bug Tracker
 *
 * @package MantisBT
 * @copyright Copyright 2004  Victor Boctor - vboctor@users.sourceforge.net
 * @copyright Copyright 2005  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * Get config string
 * @param string $p_username   Username.
 * @param string $p_password   Password.
 * @param string $p_config_var A configuration variable.
 * @return mixed
 */
function mc_config_get_string( $p_username, $p_password, $p_config_var ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	if( !mci_has_readonly_access( $t_user_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	if( config_is_private( $p_config_var ) ) {
		return SoapObjectsFactory::newSoapFault( 'Client', 'Access to \'' . $p_config_var . '\' is denied' );
	}

	if( !config_is_set( $p_config_var ) ) {
		return SoapObjectsFactory::newSoapFault( 'Client', 'Config \'' . $p_config_var . '\' is undefined' );
	}

	return config_get( $p_config_var );
}

/**
 * Gets the specified set of configs for the specified user and project.
 *
 * @param string $p_username   Username.
 * @param string $p_password   Password.
 * @param string $p_user       The user to get the configs for (ObjectRef) or null for current user.
 * @param string $p_project    The project to get the configs for (ObjectRef) or null for default project.
 * @param arrays $p_configs    The array of configs to return.
 * @return string json encoded string
 */
function mc_config_get( $p_username, $p_password, $p_user, $p_project, $p_configs ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	if( !mci_has_readonly_access( $t_user_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	$t_target_user_id = $p_user === null ? $t_user_id : mci_get_user_id( $p_user );
	if( $t_target_user_id != $t_user_id ) {
		# We can add a config threshold in the future if needed.
		if( !access_has_global_level( ADMINISTRATOR, $t_user_id ) ) {
			return mci_soap_fault_access_denied( $t_user_id );
		}
	}

	if( $p_project === null ) {
		$t_project_id = null;
	} else {
		$t_project_id = mci_get_project_id( $p_project );

		if( !access_has_project_level( config_get( 'view_bug_threshold' ), $t_project_id, $t_target_user_id ) ) {
			$t_project_id = null;
		}
	}

	$t_configs = array();

	foreach( $p_configs as $t_config_var ) {
		if( config_is_private( $t_config_var ) ) {
			return SoapObjectsFactory::newSoapFault( 'Client', 'Access to \'' . $t_config_var . '\' is denied' );
		}

		if( !config_is_set( $t_config_var, $t_target_user_id, $t_project_id ) ) {
			return SoapObjectsFactory::newSoapFault( 'Client', 'Config \'' . $t_config_var . '\' is undefined' );
		}

		$t_configs[$t_config_var] = config_get( $t_config_var, /* default */ null, $t_target_user_id, $t_project_id );
	}

	return json_encode( $t_configs );
}
