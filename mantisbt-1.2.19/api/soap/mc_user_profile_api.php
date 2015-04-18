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
 * Returns all the profiles for the user, including the global ones
 *
 * @param string   $p_username    The user's username
 * @param string   $p_password    The user's password
 * @param integer  $p_page_number
 * @param integer  $p_per_page
 * 
 */
function mc_user_profiles_get_all( $p_username, $p_password, $p_page_number, $p_per_page ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if ( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	if ( !mci_has_readonly_access( $t_user_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}
	
	$t_results = array();
	$t_start = max ( array ( 0, $p_page_number - 1 )  ) * $p_per_page;
	
	foreach ( profile_get_all_for_user( $t_user_id ) as $t_profile_row ) {
		
		$t_result = array(
			'id' => $t_profile_row['id'],
			'description' => $t_profile_row['description'],
			'os' => $t_profile_row['os'],
			'os_build' => $t_profile_row['os_build'],
			'platform' => $t_profile_row['platform']
		);
		
		if ( $t_profile_row['user_id'] != 0 )
			$t_result['user_id'] = mci_account_get_array_by_id( $t_profile_row['user_id'] );
		
		$t_results[] = $t_result;
	}

	// the profile_api does not implement pagination in the backend, so we emulate it here
	// we can always push the pagination in the database, but this seems unlikely in the
	// near future, as the number of profiles is expected to be small
	$t_paged_results = array_slice ( $t_results, $t_start, $p_per_page );
	
	return array (
		'total_results' => count ( $t_results),
		'results' => $t_paged_results
	);
}
