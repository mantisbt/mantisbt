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

# Path to MantisBT is assumed to be the grand parent directory.  If this is not
# the case, then this variable should be set to the MantisBT path.
# This can not be a configuration option, then MantisConnect configuration
# needs MantisBT to be included first to make use of the constants and possibly
# configuration defined in MantisBT.

require_api( 'filter_constants_inc.php' );

// doesn't contain 'custom_fields' and project_id
$g_soap_api_to_filter_names = array (
    'search' => FILTER_PROPERTY_SEARCH,
    'category' => FILTER_PROPERTY_CATEGORY_ID,
    'severity_id' => FILTER_PROPERTY_SEVERITY,
    'status_id' => FILTER_PROPERTY_STATUS,
    'priority_id' => FILTER_PROPERTY_PRIORITY,
    'reporter_id' => FILTER_PROPERTY_REPORTER_ID,
    'handler_id' => FILTER_PROPERTY_HANDLER_ID,
    'note_user_id' => FILTER_PROPERTY_NOTE_USER_ID,
    'resolution_id' => FILTER_PROPERTY_RESOLUTION,
    'product_version' => FILTER_PROPERTY_VERSION,

    'user_monitor_id' => FILTER_PROPERTY_MONITOR_USER_ID,
    'hide_status_id' => FILTER_PROPERTY_HIDE_STATUS,
    'sort' => FILTER_PROPERTY_SORT_FIELD_NAME,
    'sort_direction' => FILTER_PROPERTY_SORT_DIRECTION,
    'sticky' => FILTER_PROPERTY_STICKY,
    'view_state' => FILTER_PROPERTY_VIEW_STATE,
    'fixed_in_version' => FILTER_PROPERTY_FIXED_IN_VERSION,
    'target_version' => FILTER_PROPERTY_TARGET_VERSION,
    'platform' => FILTER_PROPERTY_PLATFORM,
    'os' => FILTER_PROPERTY_OS,
    'os_build' => FILTER_PROPERTY_OS_BUILD,
    'start_day' => FILTER_PROPERTY_DATE_SUBMITTED_START_DAY,
    'start_month' => FILTER_PROPERTY_DATE_SUBMITTED_START_MONTH,
    'start_year' => FILTER_PROPERTY_DATE_SUBMITTED_START_YEAR,
    'end_day' => FILTER_PROPERTY_DATE_SUBMITTED_END_DAY,
    'end_month' => FILTER_PROPERTY_DATE_SUBMITTED_END_MONTH,
    'end_year' => FILTER_PROPERTY_DATE_SUBMITTED_END_YEAR,
    'tag_string' => FILTER_PROPERTY_TAG_STRING,
    'tag_select' => FILTER_PROPERTY_TAG_SELECT,
);


/**
 * Get all user defined issue filters for the given project.
 *
 * @param string  $p_username   The name of the user trying to access the filters.
 * @param string  $p_password   The password of the user.
 * @param integer $p_project_id The id of the project to retrieve filters for.
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
 * @param string  $p_username    The name of the user trying to access the filters.
 * @param string  $p_password    The password of the user.
 * @param integer $p_project_id  The id of the project to retrieve filters for.
 * @param integer $p_filter_id   The id of the filter to apply.
 * @param integer $p_page_number Start with the given page number (zero-based).
 * @param integer $p_per_page    Number of issues to display per page.
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
		return SoapObjectsFactory::newSoapFault( 'Server', 'Invalid Filter' );
	}
	$t_filter = json_decode( $t_filter_detail[1], true );
	$t_filter = filter_ensure_valid_filter( $t_filter );

	$t_result = array();
	$t_rows = filter_get_bug_rows( $p_page_number, $p_per_page, $t_page_count, $t_bug_count, $t_filter, $p_project_id );

	# the page number was moved back, so we have exceeded the actual page number, see bug #12991
	if( $t_orig_page_number > $p_page_number ) {
	    return $t_result;
	}

	foreach( $t_rows as $t_issue_data ) {
		$t_result[] = mci_issue_data_as_array( $t_issue_data, $t_user_id, $t_lang );
	}

	return $t_result;
}

/**
 * Get the issue headers that match the specified filter and paging details.
 *
 * @param string  $p_username    The name of the user trying to access the filters.
 * @param string  $p_password    The password of the user.
 * @param integer $p_project_id  The id of the project to retrieve filters for.
 * @param integer $p_filter_id   The id of the filter to apply.
 * @param integer $p_page_number Start with the given page number (zero-based).
 * @param integer $p_per_page    Number of issues to display per page.
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
	$t_filter = json_decode( $t_filter_detail[1], true );
	$t_filter = filter_ensure_valid_filter( $t_filter );

	$t_result = array();
	$t_rows = filter_get_bug_rows( $p_page_number, $p_per_page, $t_page_count, $t_bug_count, $t_filter, $p_project_id );

	# the page number was moved back, so we have exceeded the actual page number, see bug #12991
	if( $t_orig_page_number > $p_page_number ) {
	    return $t_result;
	}

	foreach( $t_rows as $t_issue_data ) {
		$t_result[] = mci_issue_data_as_header_array( $t_issue_data );
	}

	return $t_result;
}

/**
 * Get all issue rows matching the custom filter.
 *
 * @param integer               $p_user_id          The user id.
 * @param FilterSearchData      $p_filter_search    The custom filter.
 * @param integer               $p_page_number      Start with the given page number (zero-based).
 * @param integer               $p_per_page         Number of issues to display per page.
 * @return array of issue rows
 */
function mci_filter_search_get_rows( $p_user_id, $p_filter_search, $p_page_number, $p_per_page) {

    global $g_soap_api_to_filter_names;

    // object to array
    if( is_object($p_filter_search) ) {
        $p_filter_search = get_object_vars($p_filter_search);
    }

    $t_project_id = array();
    if( isset( $p_filter_search['project_id'] ) ) {
        // check access right to all projects
        foreach( $p_filter_search['project_id'] as $t_id ) {
            if( mci_has_readonly_access( $p_user_id, $t_id ) ) {
                $t_project_id[] = $t_id;
            }
            else {
                error_log( 'User: ' . $p_user_id . ' has not access right to project: ' . $t_id . '.' );
            }
        }
        // user has not access right to any project
        if( count($t_project_id) < 1 ) {
            return mci_soap_fault_access_denied( $p_user_id );
        }
    }
    else {
        if( !mci_has_readonly_access( $p_user_id, ALL_PROJECTS ) ) {
            return mci_soap_fault_access_denied( $p_user_id );
        }

        $t_project_id = array( ALL_PROJECTS );
    }

    $t_filter = array( '_view_type' => 'advanced' );
    $t_filter['project_id'] = $t_project_id;

    // default fields
    foreach( $g_soap_api_to_filter_names as $t_soap_name => $t_filter_name ) {
        if( isset ( $p_filter_search[$t_soap_name]) ) {

            $t_value = $p_filter_search[$t_soap_name];
            $t_filter[$t_filter_name] = $t_value;
        }
    }

    // custom fields
    if( isset ( $p_filter_search['custom_fields']) ) {
        foreach(  $p_filter_search['custom_fields'] as $t_custom_field ) {

            // object to array
            if( is_object($t_custom_field) ) {
                $t_custom_field = get_object_vars($t_custom_field);
            }

            $t_field = $t_custom_field['field'];
            if( is_object($t_field) ) {
                $t_field = get_object_vars($t_field);
            }

            // if is set custom_field's id, use it primary
            if( isset($t_field['id']) ) {
                $t_custom_field_id = $t_field['id'];
            }
            else {
                $t_custom_field_id = custom_field_get_id_from_name( $t_field['name'] );
            }

            $t_value = $t_custom_field['value'];
            $t_filter['custom_fields'][$t_custom_field_id] =  $t_value;
        }
    }

    $t_filter = filter_ensure_valid_filter( $t_filter );

    $t_page_number = $p_page_number < 1 ? 1 : $p_page_number;
    $t_page_count = 0;
    $t_bug_count = 0;
    return filter_get_bug_rows( $t_page_number, $p_per_page, $t_page_count, $t_bug_count, $t_filter );
}

/**
 * Get all issue headers matching the custom filter.
 *
 * @param string                $p_username         The name of the user trying to access the filters.
 * @param string                $p_password         The password of the user.
 * @param FilterSearchData      $p_filter_search    The custom filter.
 * @param integer               $p_page_number      Start with the given page number (zero-based).
 * @param integer               $p_per_page         Number of issues to display per page.
 * @return array that represents an IssueHeaderDataArray structure
 */
function mc_filter_search_issue_headers( $p_username, $p_password, $p_filter_search, $p_page_number, $p_per_page) {

    $t_user_id = mci_check_login( $p_username, $p_password );

    if( $t_user_id === false ) {
        return mci_soap_fault_login_failed();
    }

    $t_rows = mci_filter_search_get_rows( $t_user_id, $p_filter_search, $p_page_number, $p_per_page);

    $t_result = array();
    foreach( $t_rows as $t_issue_data ) {
        $t_result[] = mci_issue_data_as_header_array( $t_issue_data );
    }

    return $t_result;
}

/**
 * Get all issues matching the custom filter.
 *
 * @param string                $p_username         The name of the user trying to access the filters.
 * @param string                $p_password         The password of the user.
 * @param FilterSearchData      $p_filter_search    The custom filter.
 * @param integer               $p_page_number Start with the given page number (zero-based).
 * @param integer               $p_per_page    Number of issues to display per page.
 * @return array that represents an IssueDataArray structure
 */
function mc_filter_search_issues( $p_username, $p_password, $p_filter_search, $p_page_number, $p_per_page ) {

    $t_user_id = mci_check_login( $p_username, $p_password );

    if( $t_user_id === false ) {
        return mci_soap_fault_login_failed();
    }

    $t_rows = mci_filter_search_get_rows( $t_user_id, $p_filter_search, $p_page_number, $p_per_page);

    $t_lang = mci_get_user_lang( $t_user_id );

    $t_result = array();
    foreach( $t_rows as $t_issue_data ) {
        $t_result[] = mci_issue_data_as_array( $t_issue_data, $t_user_id, $t_lang );
    }

    return $t_result;
}

/**
 * Get all issue ids matching the custom filter.
 *
 * @param string                $p_username         The name of the user trying to access the filters.
 * @param string                $p_password         The password of the user.
 * @param FilterSearchData      $p_filter_search    The custom filter.
 * @param integer               $p_page_number Start with the given page number (zero-based).
 * @param integer               $p_per_page    Number of issues to display per page.
 * @return array that represents an IntegerArray structure
 */
function mc_filter_search_issue_ids( $p_username, $p_password, $p_filter_search, $p_page_number, $p_per_page ) {

    $t_user_id = mci_check_login( $p_username, $p_password );

    if( $t_user_id === false ) {
        return mci_soap_fault_login_failed();
    }

    $t_rows = mci_filter_search_get_rows( $t_user_id, $p_filter_search, $p_page_number, $p_per_page);

    $t_result = array();
    foreach( $t_rows as $t_issue_data ) {
        $t_result[] = $t_issue_data->id;
    }

    return $t_result;
}
