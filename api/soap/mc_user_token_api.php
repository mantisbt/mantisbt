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
 * Create a user token for another user.
 *
 * @param string  $p_username   The user's username.
 * @param string  $p_password   The user's password.
 * @param string  $p_token_name The name of the token (may be empty).
 * @return string $t_token      The requested token value.
 */
function mc_user_token_create( $p_username, $p_password, $p_token_name ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_fault_login_failed();
	}

	if( !mci_has_readonly_access( $t_user_id ) ) {
		return mci_fault_access_denied( $t_user_id );
	}

	$t_data = array(
		'query' => array(
			'user_id' => (int)$t_user_id
		),
		'payload' => array(
			'name' => $p_token_name
		)
	);

	$t_command = new UserTokenCreateCommand( $t_data );
	$t_result = $t_command->execute();

	return $t_result['token'];
}
