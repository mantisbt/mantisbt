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
 * Get all user defined issue filters for the given project.
 *
 * @param string $p_username  The name of the user trying to access the filters.
 * @param string $p_password  The password of the user.
 * @param int $p_project_id  The id of the project to retrieve filters for.
 * @return array that represents a FilterDataArray structure
 */
function mc_filter_get( $p_username, $p_password, $p_project_id ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}
	if( !mci_has_readonly_access( $t_user_id, $p_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}
	$t_result = array();
	foreach( mci_filter_db_get_available_queries( $p_project_id, $t_user_id ) as $t_filter_row ) {
		$t_filter = array();
		$t_filter['id'] = $t_filter_row['id'];
		$t_filter['owner'] = mci_account_get_array_by_id( $t_filter_row['user_id'] );
		$t_filter['project_id'] = $t_filter_row['project_id'];
		$t_filter['is_public'] = $t_filter_row['is_public'];
		$t_filter['name'] = $t_filter_row['name'];
		$t_filter['filter_string'] = $t_filter_row['filter_string'];
		$t_filter['url'] = $t_filter_row['url'];
		$t_result[] = $t_filter;
	}
	return $t_result;
}

/**
 * Get all issues matching the specified filter.
 *
 * @param string $p_username  The name of the user trying to access the filters.
 * @param string $p_password  The password of the user.
 * @param int $p_project_id  The id of the project to retrieve filters for.
 * @param int $p_filter_id  The id of the filter to apply.
 * @param int $p_page_number  Start with the given page number (zero-based)
 * @param int $p_per_page  Number of issues to display per page
 * @return array that represents an IssueDataArray structure
 */
function mc_filter_get_issues( $p_username, $p_password, $p_project_id, $p_filter_id, $p_page_number, $p_per_page ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}
	$t_lang = mci_get_user_lang( $t_user_id );

	if( !mci_has_readonly_access( $t_user_id, $p_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	$t_orig_page_number = $p_page_number < 1 ? 1 : $p_page_number;
	$t_page_count = 0;
	$t_bug_count = 0;
	$t_filter = filter_db_get_filter( $p_filter_id );
	$t_filter_detail = explode( '#', $t_filter, 2 );
	if( !isset( $t_filter_detail[1] ) ) {
		return SoapObjectsFactory::newSoapFault( 'Server',  'Invalid Filter' );
	}
	$t_filter = unserialize( $t_filter_detail[1] );
	$t_filter = filter_ensure_valid_filter( $t_filter );

	$t_result = array();
	$t_rows = filter_get_bug_rows( $p_page_number, $p_per_page, $t_page_count, $t_bug_count, $t_filter, $p_project_id );

	# the page number was moved back, so we have exceeded the actual page number, see bug #12991
	if ( $t_orig_page_number > $p_page_number )
	    return $t_result;	

	foreach( $t_rows as $t_issue_data ) {
		$t_result[] = mci_issue_data_as_array( $t_issue_data, $t_user_id, $t_lang );
	}

	return $t_result;
}

/**
 * Get the issue headers that match the specified filter and paging details.
 *
 * @param string $p_username  The name of the user trying to access the filters.
 * @param string $p_password  The password of the user.
 * @param int $p_project_id  The id of the project to retrieve filters for.
 * @param int $p_filter_id  The id of the filter to apply.
 * @param int $p_page_number  Start with the given page number (zero-based)
 * @param int $p_per_page  Number of issues to display per page
 * @return array that represents an IssueDataArray structure
 */
function mc_filter_get_issue_headers( $p_username, $p_password, $p_project_id, $p_filter_id, $p_page_number, $p_per_page ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}
	if( !mci_has_readonly_access( $t_user_id, $p_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	$t_orig_page_number = $p_page_number < 1 ? 1 : $p_page_number;
	$t_page_count = 0;
	$t_bug_count = 0;
	$t_filter = filter_db_get_filter( $p_filter_id );
	$t_filter_detail = explode( '#', $t_filter, 2 );
	if( !isset( $t_filter_detail[1] ) ) {
		return SoapObjectsFactory::newSoapFault( 'Server', 'Invalid Filter' );
	}
	$t_filter = unserialize( $t_filter_detail[1] );
	$t_filter = filter_ensure_valid_filter( $t_filter );

	$t_result = array();
	$t_rows = filter_get_bug_rows( $p_page_number, $p_per_page, $t_page_count, $t_bug_count, $t_filter, $p_project_id );

	# the page number was moved back, so we have exceeded the actual page number, see bug #12991
	if ( $t_orig_page_number > $p_page_number )
	    return $t_result;	

	foreach( $t_rows as $t_issue_data ) {
		$t_result[] = mci_issue_data_as_header_array($t_issue_data);
	}

	return $t_result;
}
