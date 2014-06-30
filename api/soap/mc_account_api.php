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
 * Get username, realname and email from for a given user id
 * @param integer $p_user_id A valid user identifier.
 * @return array
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
 * @param array $p_user_ids An array of user identifiers.
 * @return array
 */
function mci_account_get_array_by_ids ( array $p_user_ids ) {
	$t_result = array();

	foreach ( $p_user_ids as $t_user_id ) {
		$t_result[] = mci_account_get_array_by_id( $t_user_id );
	}

	return $t_result;
}
