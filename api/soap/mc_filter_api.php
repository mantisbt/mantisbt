<?php
# MantisConnect - A webservice interface to Mantis Bug Tracker
# Copyright (C) 2004-2012  Victor Boctor - vboctor@users.sourceforge.net
# This program is distributed under dual licensing.  These include
# GPL and a commercial licenses.  Victor Boctor reserves the right to
# change the license of future releases.
# See docs/ folder for more details

/**
 * Get all user defined issue filters for the given project.
 *
 * @param string $p_username  The name of the user trying to access the filters.
 * @param string $p_password  The password of the user.
 * @param integer $p_project_id  The id of the project to retrieve filters for.
 * @return Array that represents a FilterDataArray structure
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
 * @param integer $p_filter_id  The id of the filter to apply.
 * @param integer $p_page_number  Start with the given page number (zero-based)
 * @param integer $p_per_page  Number of issues to display per page
 * @return Array that represents an IssueDataArray structure
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
		return new soap_fault( 'Server', '', 'Invalid Filter' );
	}
	$t_filter = unserialize( $t_filter_detail[1] );
	$t_filter = filter_ensure_valid_filter( $t_filter );

	$t_result = array();
	$t_rows = filter_get_bug_rows( $p_page_number, $p_per_page, $t_page_count, $t_bug_count, $t_filter, $p_project_id );

	// the page number was moved back, so we have exceeded the actual page number, see bug #12991
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
 * @param integer $p_filter_id  The id of the filter to apply.
 * @param integer $p_page_number  Start with the given page number (zero-based)
 * @param integer $p_per_page  Number of issues to display per page
 * @return Array that represents an IssueDataArray structure
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
		return new soap_fault( 'Server', '', 'Invalid Filter' );
	}
	$t_filter = unserialize( $t_filter_detail[1] );
	$t_filter = filter_ensure_valid_filter( $t_filter );

	$t_result = array();
	$t_rows = filter_get_bug_rows( $p_page_number, $p_per_page, $t_page_count, $t_bug_count, $t_filter, $p_project_id );

	// the page number was moved back, so we have exceeded the actual page number, see bug #12991
	if ( $t_orig_page_number > $p_page_number )
	    return $t_result;	

	foreach( $t_rows as $t_issue_data ) {
		$t_id = $t_issue_data->id;

		$t_issue = array();

		$t_issue['id'] = $t_id;
		$t_issue['view_state'] = $t_issue_data->view_state;
		$t_issue['last_updated'] = timestamp_to_iso8601( $t_issue_data->last_updated, false );

		$t_issue['project'] = $t_issue_data->project_id;
		$t_issue['category'] = mci_get_category( $t_issue_data->category_id );
		$t_issue['priority'] = $t_issue_data->priority;
		$t_issue['severity'] = $t_issue_data->severity;
		$t_issue['status'] = $t_issue_data->status;

		$t_issue['reporter'] = $t_issue_data->reporter_id;
		$t_issue['summary'] = $t_issue_data->summary;
		if( !empty( $t_issue_data->handler_id ) ) {
			$t_issue['handler'] = $t_issue_data->handler_id;
		}
		$t_issue['resolution'] = $t_issue_data->resolution;

		$t_issue['attachments_count'] = count( mci_issue_get_attachments( $t_issue_data->id ) );
		$t_issue['notes_count'] = count( mci_issue_get_notes( $t_issue_data->id ) );

		$t_result[] = $t_issue;
	}

	return $t_result;
}
