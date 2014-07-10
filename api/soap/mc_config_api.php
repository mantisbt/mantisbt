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

