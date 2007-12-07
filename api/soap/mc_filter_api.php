<?php
	# MantisConnect - A webservice interface to Mantis Bug Tracker
	# Copyright (C) 2004-2007  Victor Boctor - vboctor@users.sourceforge.net
	# This program is distributed under dual licensing.  These include
	# GPL and a commercial licenses.  Victor Boctor reserves the right to
	# change the license of future releases.
	# See docs/ folder for more details

	# --------------------------------------------------------
	# $Id: mc_filter_api.php,v 1.1 2007-07-18 06:52:55 vboctor Exp $
	# --------------------------------------------------------

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
		if ( $t_user_id === false ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}
		if ( !mci_has_readonly_access( $t_user_id, $p_project_id ) ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
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
		$t_lang = mci_get_user_lang( $t_user_id );
		if ( $t_user_id === false ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}
		if ( !mci_has_readonly_access( $t_user_id, $p_project_id ) ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}

		$t_page_count = 0;
		$t_bug_count = 0;
		$t_filter = filter_db_get_filter( $p_filter_id );
		$t_filter_detail = explode( '#', $t_filter, 2 );
		if ( !isset( $t_filter_detail[1] ) ) {
			return new soap_fault( 'Server', '', 'Invalid Filter' );
		}
		$t_filter = unserialize( $t_filter_detail[1] );
		$t_filter = filter_ensure_valid_filter( $t_filter );

		$t_result = array();
		$t_rows = filter_get_bug_rows( $p_page_number, $p_per_page, $t_page_count, $t_bug_count, $t_filter, $p_project_id );
		foreach( $t_rows as $t_issue_data ) {
			$t_id = $t_issue_data['id'];

			$t_issue = array();
			$t_issue['id'] = $t_id;
			$t_issue['view_state'] = mci_enum_get_array_by_id( $t_issue_data['view_state'], 'view_state', $t_lang);
			$t_issue['last_updated'] = timestamp_to_iso8601( $t_issue_data['last_updated'] );

			$t_issue['project'] = mci_project_as_array_by_id( $t_issue_data['project_id'] );
			$t_issue['category'] = mci_null_if_empty( $t_issue_data['category'] );
	 		$t_issue['priority'] = mci_enum_get_array_by_id( $t_issue_data['priority'], 'priority', $t_lang );
	 		$t_issue['severity'] = mci_enum_get_array_by_id( $t_issue_data['severity'], 'severity', $t_lang );
	 		$t_issue['status'] = mci_enum_get_array_by_id( $t_issue_data['status'], 'status', $t_lang );

	 		$t_issue['reporter'] = mci_account_get_array_by_id( $t_issue_data['reporter_id'] );
			$t_issue['summary'] = $t_issue_data['summary'];
			$t_issue['version'] = mci_null_if_empty( $t_issue_data['version'] );
			$t_issue['build'] = mci_null_if_empty( $t_issue_data['build'] );
			$t_issue['platform'] = mci_null_if_empty( $t_issue_data['platform'] );
			$t_issue['os'] = mci_null_if_empty( $t_issue_data['os'] );
			$t_issue['os_build'] = mci_null_if_empty( $t_issue_data['os_build'] );
	 		$t_issue['reproducibility'] = mci_enum_get_array_by_id( $t_issue_data['reproducibility'], 'reproducibility', $t_lang );
			$t_issue['date_submitted'] = timestamp_to_iso8601( $t_issue_data['date_submitted'] );
			$t_issue['sponsorship_total'] = $t_issue_data['sponsorship_total'];

			if( !empty( $t_issue_data['handler_id'] ) ) {
		 		$t_issue['handler'] = mci_account_get_array_by_id( $t_issue_data['handler_id'] );
			}
	 		$t_issue['projection'] = mci_enum_get_array_by_id( $t_issue_data['projection'], 'projection', $t_lang );
	 		$t_issue['eta'] = mci_enum_get_array_by_id( $t_issue_data['eta'], 'eta', $t_lang );

	 		$t_issue['resolution'] = mci_enum_get_array_by_id( $t_issue_data['resolution'], 'resolution', $t_lang );
			$t_issue['fixed_in_version'] = mci_null_if_empty( $t_issue_data['fixed_in_version'] );

			$t_issue['description'] = bug_get_text_field( $t_id, 'description' );

			$t_steps_to_reproduce = bug_get_text_field( $t_id, 'steps_to_reproduce' );
			$t_issue['steps_to_reproduce'] = mci_null_if_empty( $t_steps_to_reproduce );

			$t_additional_information = bug_get_text_field( $t_id, 'additional_information' );
			$t_issue['additional_information'] = mci_null_if_empty( $t_additional_information );

			$t_issue['attachments'] = mci_issue_get_attachments( $t_issue_data['id'] );
			$t_issue['relationships'] = mci_issue_get_relationships( $t_issue_data['id'], $t_user_id );
			$t_issue['notes'] = mci_issue_get_notes( $t_issue_data['id'] );
			$t_issue['custom_fields'] = mci_issue_get_custom_fields( $t_issue_data['id'] );

			$t_result[] = $t_issue;
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
		if ( $t_user_id === false ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}
		if ( !mci_has_readonly_access( $t_user_id, $p_project_id ) ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}

		$t_page_count = 0;
		$t_bug_count = 0;
		$t_filter = filter_db_get_filter( $p_filter_id );
		$t_filter_detail = explode( '#', $t_filter, 2 );
		if ( !isset( $t_filter_detail[1] ) ) {
			return new soap_fault( 'Server', '', 'Invalid Filter' );
		}
		$t_filter = unserialize( $t_filter_detail[1] );
		$t_filter = filter_ensure_valid_filter( $t_filter );

		$t_result = array();
		$t_rows = filter_get_bug_rows( $p_page_number, $p_per_page, $t_page_count, $t_bug_count, $t_filter, $p_project_id );
        foreach( $t_rows as $t_issue_data ) {
            $t_id = $t_issue_data['id'];
            
            $t_issue = array();
            
            $t_issue['id'] = $t_id;
            $t_issue['view_state'] = $t_issue_data['view_state'];
            $t_issue['last_updated'] = timestamp_to_iso8601( $t_issue_data['last_updated'] );

            $t_issue['project'] = $t_issue_data['project_id'];
            $t_issue['category'] = mci_null_if_empty( $t_issue_data['category'] );
            $t_issue['priority'] = $t_issue_data['priority'];
            $t_issue['severity'] = $t_issue_data['severity'];
            $t_issue['status'] = $t_issue_data['status'];

            $t_issue['reporter'] = $t_issue_data['reporter_id'];
            $t_issue['summary'] = $t_issue_data['summary'];
            if( !empty( $t_issue_data['handler_id'] ) ) {
                $t_issue['handler'] = $t_issue_data['handler_id'];
            }
            $t_issue['resolution'] = $t_issue_data['resolution'];
            
            $t_issue['attachments_count'] = count( mci_issue_get_attachments( $t_issue_data['id'] ) );
            $t_issue['notes_count'] = count( mci_issue_get_notes( $t_issue_data['id'] ) );

            $t_result[] = $t_issue;
        }

		return $t_result;
	}
?>
