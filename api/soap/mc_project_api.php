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
 *
 * @noinspection PhpComposerExtensionStubsInspection
 * @noinspection PhpUnused
 */

use Mantis\Exceptions\ClientException;

/**
 * Use a standard filter to get issues associated with the specified user.
 *
 * @param string $p_username    Logged in user name.
 * @param string $p_password    Login password.
 * @param int    $p_project_id  Id of project to filter on, or ALL_PROJECTS.
 * @param string $p_filter_type The name of the filter to apply
 *                              "assigned" - target user specified - issues assigned to target user that are not resolved.
 *                              "assigned" - target user 0 - unassigned issues that are not resolved.
 *                              "reported" - target user specified - issues reported by user.
 *                              "reported" - target user 0 - will throw.
 *                              "monitored" - target user specified - issues monitored by user.
 *                              "monitored" - target user 0 - issues not monitored.
 * @param object $p_target_user AccountData for target user, can include id, name, or both.
 * @param int    $p_page_number The page to return (1 based).
 * @param int    $p_per_page    Number of issues per page.
 *
 * @return array|RestFault|SoapFault a page of matching issues or error.
 */
function mc_project_get_issues_for_user( $p_username, $p_password, $p_project_id, $p_filter_type, $p_target_user, $p_page_number, $p_per_page ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_fault_login_failed();
	}

	if( $p_project_id != ALL_PROJECTS && !project_exists( $p_project_id ) ) {
		return ApiObjectFactory::faultNotFound( 'Project \'' . $p_project_id . '\' does not exist.' );
	}

	if( !mci_has_readonly_access( $t_user_id, $p_project_id ) ) {
		return mci_fault_access_denied( $t_user_id );
	}

	$t_lang = mci_get_user_lang( $t_user_id );

	$t_orig_page_number = max( $p_page_number, 1 );
	$t_page_count = 0;
	$t_bug_count = 0;
	$t_target_user_id = mci_get_user_id( $p_target_user );

	if( strcasecmp( $p_filter_type, 'assigned' ) == 0 ) {
		# If user is filtering on handlers, then they must have access to view handlers
		if( $t_target_user_id != $t_user_id && $t_target_user_id != NO_USER ) {
			$t_view_handler_access = config_get( 'view_handler_threshold', null, $t_user_id, $p_project_id );
			if( !access_has_project_level( $t_view_handler_access, $p_project_id, $t_user_id ) ) {
				return ApiObjectFactory::faultForbidden( 'Issue handlers are not visible to user.' );
			}
		}

		$t_filter = filter_create_assigned_to_unresolved( $p_project_id, $t_target_user_id );
	} else if( strcasecmp( $p_filter_type, 'reported' ) == 0 ) {
		# target id 0 for reporter doesn't make sense.
		if( $t_target_user_id == 0 ) {
			return ApiObjectFactory::faultBadRequest( 'Target user id must be specified for \'reported\' filter.' );
		}

		$t_filter = filter_create_reported_by( $p_project_id, $t_target_user_id );
	} else if( strcasecmp( $p_filter_type, 'monitored' ) == 0 ) {
		$t_filter = filter_create_monitored_by( $p_project_id, $t_target_user_id );
	} else {
		return ApiObjectFactory::faultBadRequest( 'Unknown filter type \'' . $p_filter_type . '\'.' );
	}

	$t_rows = filter_get_bug_rows(
		$p_page_number, $p_per_page, $t_page_count, $t_bug_count, $t_filter,
		$p_project_id, $t_user_id, true );

	$t_result = array();

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
 * Project Get Issues.
 *
 * @param string $p_username    Logged in user name.
 * @param string $p_password    Login password.
 * @param int    $p_project_id  Project Id.
 * @param int    $p_page_number Page number.
 * @param int    $p_per_page    Per page.
 *
 * @return array|RestFault|SoapFault
 */
function mc_project_get_issues( $p_username, $p_password, $p_project_id, $p_page_number, $p_per_page ) {
	global $g_project_override;

	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_fault_login_failed();
	}

	$t_lang = mci_get_user_lang( $t_user_id );
	if( $p_project_id != ALL_PROJECTS && !project_exists( $p_project_id ) ) {
		return ApiObjectFactory::faultNotFound( 'Project \'' . $p_project_id . '\' does not exist.' );
	}

	if( !mci_has_readonly_access( $t_user_id, $p_project_id ) ) {
		return mci_fault_access_denied( $t_user_id );
	}

	$t_orig_page_number = max( $p_page_number, 1 );
	$t_page_count = 0;
	$t_bug_count = 0;
	$g_project_override = $p_project_id;

	$t_rows = filter_get_bug_rows( $p_page_number, $p_per_page, $t_page_count, $t_bug_count, null, $p_project_id );

	$t_result = array();

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
 * Get all projects accessible by the given user.
 *
 * @param string $p_username The name of the user trying to access the project list.
 * @param string $p_password The password of the user.
 *
 * @return array|RestFault|SoapFault Array suitable to be converted into a ProjectDataArray
 * @throws ClientException
 */
function mc_projects_get_user_accessible( $p_username, $p_password ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_fault_login_failed();
	}

	if( !mci_has_readonly_access( $t_user_id ) ) {
		return mci_fault_access_denied( $t_user_id );
	}

	$t_lang = mci_get_user_lang( $t_user_id );

	$t_result = array();
	foreach( user_get_accessible_projects( $t_user_id ) as $t_project_id ) {
		$t_result[] = mci_project_get_row( $t_project_id, $t_user_id, $t_lang );
	}

	return $t_result;
}

/**
 * Retrieves project data.
 * 
 * @param int    $p_project_id
 * @param int    $p_user_id
 * @param string $p_lang
 *
 * @return array
 * @throws ClientException
 */
function mci_project_get_row( $p_project_id, $p_user_id, $p_lang ) {
	$t_project = project_cache_row( $p_project_id );

	return array(
		'id' => $p_project_id,
		'name' => $t_project['name'],
		'status' => mci_enum_get_array_by_id( $t_project['status'], 'project_status', $p_lang ),
		'enabled' => $t_project['enabled'],
		'view_state' => mci_enum_get_array_by_id( $t_project['view_state'], 'project_view_state', $p_lang ),
		'access_min' => mci_enum_get_array_by_id( $t_project['access_min'], 'access_levels', $p_lang ),
		'file_path' => array_key_exists( 'file_path', $t_project ) ? $t_project['file_path'] : '',
		'description' => array_key_exists( 'description', $t_project ) ? $t_project['description'] : '',
		'subprojects' => mci_user_get_accessible_subprojects( $p_user_id, $p_project_id, $p_lang ),
	);
}

/**
 * Performs initial checks for Project Categories.
 *
 * @param string $p_username   The name of the user trying to access the categories.
 * @param string $p_password   The password of the user.
 * @param int    $p_project_id The id of the project for which to add the category.
 * @param string $p_readonly   Access: True for readonly, False for manage project
 *
 * @return RestFault|SoapFault|true True if caller can proceed
 */
function mci_project_initial_checks( $p_username, $p_password, $p_project_id, $p_readonly ) {
	global $g_project_override;

	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_fault_access_denied();
	}

	if( !project_exists( $p_project_id ) ) {
		return ApiObjectFactory::faultNotFound( 'Project \'' . $p_project_id . '\' does not exist.' );
	}

	$g_project_override = $p_project_id;

	if( $p_readonly ) {
		if( !mci_has_readonly_access( $t_user_id, $p_project_id ) ) {
			return mci_fault_access_denied( $t_user_id );
		}
	} else {
		if( !mci_has_access( config_get( 'manage_project_threshold' ), $t_user_id, $p_project_id ) ) {
			return mci_fault_access_denied();
		}
	}

	return true;
}

/**
 * Get all enabled categories of a project.
 *
 * @param string $p_username   The name of the user trying to access the categories.
 * @param string $p_password   The password of the user.
 * @param int    $p_project_id The id of the project to retrieve the categories for.
 *
 * @return array|RestFault|SoapFault An array of category names
 */
function mc_project_get_categories( $p_username, $p_password, $p_project_id ) {
	$t_result = mci_project_initial_checks( $p_username, $p_password, $p_project_id, true );
	if( $t_result !== true ) {
		return $t_result;
	}

	$t_result = array();
	$t_cat_array = category_get_all_rows( $p_project_id, null, false, true );
	foreach( $t_cat_array as $t_category_row ) {
		$t_result[] = $t_category_row['name'];
	}
	return $t_result;
}

/**
 * Add a new category to a project.
 *
 * @param string $p_username      The name of the user trying to access the categories.
 * @param string $p_password      The password of the user.
 * @param int    $p_project_id    The id of the project for which to add the category.
 * @param string $p_category_name The name of the new category to add.
 *
 * @return int|RestFault|SoapFault Id of the new category
 */
function mc_project_add_category( $p_username, $p_password, $p_project_id, $p_category_name ) {
	$t_result = mci_project_initial_checks( $p_username, $p_password, $p_project_id, false );
	if( $t_result !== true ) {
		return $t_result;
	}

	return category_add( $p_project_id, $p_category_name );
}

/**
 * Delete a category of a project.
 *
 * @param string $p_username      The name of the user trying to access the categories.
 * @param string $p_password      The password of the user.
 * @param int    $p_project_id    The id of the project to retrieve the categories for.
 * @param string $p_category_name The name of the category to delete.
 *
 * @return bool|RestFault|SoapFault True or false depending on the success of the delete action.
 */
function mc_project_delete_category( $p_username, $p_password, $p_project_id, $p_category_name ) {
	$t_result = mci_project_initial_checks( $p_username, $p_password, $p_project_id, false );
	if( $t_result !== true ) {
		return $t_result;
	}

	# find the id of the category
	$p_category_id = category_get_id_by_name( $p_category_name, $p_project_id );

	if( !category_can_remove( $p_category_id ) ) {
		return ApiObjectFactory::fault(
			'Client',
			"'$p_category_name' is used as default category for moves and can't be deleted.",
			HTTP_STATUS_FORBIDDEN
		);
	}

	# delete the category and link all the issues to the default category
	category_remove( $p_category_id, config_get( 'default_category_for_moves' ) );
	return true;
}

/**
 * Update a category of a project.
 *
 * @param string $p_username          The name of the user trying to access the categories.
 * @param string $p_password          The password of the user.
 * @param int    $p_project_id        The id of the project to retrieve the categories for.
 * @param string $p_category_name     The name of the category to rename.
 * @param string $p_category_name_new The new name of the category to rename.
 * @param int    $p_assigned_to       User ID that category is assigned to.
 *
 * @return bool|RestFault|SoapFault True or false depending on the success of the update action
 */
function mc_project_rename_category_by_name( $p_username, $p_password, $p_project_id, $p_category_name, $p_category_name_new, $p_assigned_to ) {
	global $g_project_override;
	$t_user_id = mci_check_login( $p_username, $p_password );

	if( null === $p_assigned_to ) {
		return ApiObjectFactory::faultBadRequest( 'assigned_to needed' );
	}

	if( $t_user_id === false ) {
		return mci_fault_access_denied();
	}

	if( !project_exists( $p_project_id ) ) {
		return ApiObjectFactory::faultNotFound( 'Project \'' . $p_project_id . '\' does not exist.' );
	}

	$g_project_override = $p_project_id;

	if( !mci_has_access( config_get( 'manage_project_threshold' ), $t_user_id, $p_project_id ) ) {
		return mci_fault_access_denied();
	}

	# find the id of the category
	$p_category_id = category_get_id_by_name( $p_category_name, $p_project_id );

	# update the category
	category_update( $p_category_id, $p_category_name_new, $p_assigned_to );
	return true;
}

/**
 * Get all versions of a project.
 *
 * @param string $p_username   The name of the user trying to access the versions.
 * @param string $p_password   The password of the user.
 * @param int    $p_project_id The id of the project to retrieve the versions for.
 * @param bool   $p_released   Released status (one of VERSION_* constants)
 *
 * @return array|RestFault|SoapFault Array representing a ProjectVersionDataArray structure.
 */
function mci_project_get_versions( $p_username, $p_password, $p_project_id, $p_released = VERSION_ALL ) {
	$t_result = mci_project_initial_checks( $p_username, $p_password, $p_project_id, true );
	if( $t_result !== true ) {
		return $t_result;
	}

	$t_result = array();
	foreach( version_get_all_rows( $p_project_id, $p_released ) as $t_version ) {
		$t_result[] = mci_project_version_as_array( $t_version );
	}

	return $t_result;
}

/**
 * Get all versions of a project.
 *
 * @param string $p_username   The name of the user trying to access the versions.
 * @param string $p_password   The password of the user.
 * @param int    $p_project_id The id of the project to retrieve the versions for.
 *
 * @return array|RestFault|SoapFault Array representing a ProjectVersionDataArray structure.
 */
function mc_project_get_versions( $p_username, $p_password, $p_project_id ) {
	return mci_project_get_versions( $p_username, $p_password, $p_project_id );
}

/**
 * Get all released versions of a project.
 *
 * @param string $p_username   The name of the user trying to access the versions.
 * @param string $p_password   The password of the user.
 * @param int    $p_project_id The id of the project to retrieve the versions for.
 *
 * @return array|RestFault|SoapFault Array representing a ProjectVersionDataArray structure.
 */
function mc_project_get_released_versions( $p_username, $p_password, $p_project_id ) {
	return mci_project_get_versions( $p_username, $p_password, $p_project_id, VERSION_RELEASED );
}

/**
 * Get all unreleased (a.k.a. future) versions of a project.
 *
 * @param string $p_username   The name of the user trying to access the versions.
 * @param string $p_password   The password of the user.
 * @param int    $p_project_id The id of the project to retrieve the versions for.
 *
 * @return array|RestFault|SoapFault Array representing a ProjectVersionDataArray structure.
 */
function mc_project_get_unreleased_versions( $p_username, $p_password, $p_project_id ) {
	return mci_project_get_versions( $p_username, $p_password, $p_project_id, VERSION_FUTURE );
}

/**
 * Submit the specified version details.
 *
 * @param string   $p_username The name of the user trying to add the issue.
 * @param string   $p_password The password of the user.
 * @param stdClass $p_version  A ProjectVersionData structure containing
 *                             information about the new version.
 *
 * @return int|RestFault|SoapFault The id of the created version.
 * @throws ClientException
 */
function mc_project_version_add( $p_username, $p_password, stdClass $p_version ) {
	$t_user_id = mci_check_login( $p_username, $p_password );

	if( $t_user_id === false ) {
		return mci_fault_login_failed();
	}

	$p_version = ApiObjectFactory::objectToArray( $p_version );

	$t_project_id = $p_version['project_id'];

	if( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
		return mci_fault_access_denied( $t_user_id );
	}

	$t_data = array(
		'query' => array(
			'project_id' => $t_project_id,
		),
		'payload' => array(
			'name' => $p_version['name'],
			'description' => $p_version['description'],
			'released' => $p_version['released'],
			'obsolete' => $p_version['obsolete'] ?? false,
			'timestamp' => $p_version['date_order']
		)
	);

	$t_command = new VersionAddCommand( $t_data );
	$t_result = $t_command->execute();

	return $t_result['version']['id'];
}

/**
 * Submit the specified version details.
 *
 * @param string   $p_username   The name of the user trying to update the issue.
 * @param string   $p_password   The password of the user.
 * @param int      $p_version_id A version's id.
 * @param stdClass $p_version    A ProjectVersionData structure containing
 *                               information about the new version.
 *
 * @return bool|RestFault|SoapFault returns true or false depending on the success of the update action.
 * @throws ClientException
 */
function mc_project_version_update( $p_username, $p_password, $p_version_id, stdClass $p_version ) {
	$t_user_id = mci_check_login( $p_username, $p_password );

	if( $t_user_id === false ) {
		return mci_fault_login_failed();
	}

	if( is_blank( $p_version_id ) ) {
		return ApiObjectFactory::faultBadRequest( 'Mandatory field "version_id" was missing' );
	}

	if( !version_exists( $p_version_id ) ) {
		return ApiObjectFactory::faultNotFound( 'Version \'' . $p_version_id . '\' does not exist.' );
	}

	$p_version = ApiObjectFactory::objectToArray( $p_version );

	$t_project_id = $p_version['project_id'];
	$t_name = $p_version['name'];
	$t_released = $p_version['released'];
	$t_description = $p_version['description'];
	$t_date_order = is_blank( $p_version['date_order'] ) ? null : $p_version['date_order'];
	$t_obsolete = $p_version['obsolete'] ?? false;

	if( is_blank( $t_project_id ) ) {
		return ApiObjectFactory::faultBadRequest( 'Mandatory field "project_id" was missing' );
	}

	if( !project_exists( $t_project_id ) ) {
		return ApiObjectFactory::faultNotFound( 'Project \'' . $t_project_id . '\' does not exist.' );
	}

	if( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
		return mci_fault_access_denied( $t_user_id );
	}

	$t_data = array(
		'query' => array(
			'project_id' => $t_project_id,
			'version_id' => $p_version_id
		),
		'payload' => array(
			'name' => $t_name,
			'description' => $t_description,
			'released' => $t_released,
			'obsolete' => $t_obsolete,
			'timestamp' => $t_date_order
		)
	);

	$t_command = new VersionUpdateCommand( $t_data );
	$t_command->execute();

	return true;
}

/**
 * Delete a version.
 *
 * @param string $p_username   The name of the user trying to delete the version.
 * @param string $p_password   The password of the user.
 * @param int    $p_version_id A version's id.
 *
 * @return bool|RestFault|SoapFault True or false depending on the success of the delete action.
 * @throws ClientException
 */
function mc_project_version_delete( $p_username, $p_password, $p_version_id ) {
	$t_user_id = mci_check_login( $p_username, $p_password );

	if( $t_user_id === false ) {
		return mci_fault_login_failed();
	}

	if( is_blank( $p_version_id ) ) {
		return ApiObjectFactory::faultBadRequest( 'Mandatory field "version_id" was missing' );
	}

	if( !version_exists( $p_version_id ) ) {
		return ApiObjectFactory::faultNotFound( 'Version \'' . $p_version_id . '\' does not exist.' );
	}

	$t_project_id = version_get_field( $p_version_id, 'project_id' );

	if( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
		return mci_fault_access_denied( $t_user_id );
	}

	$t_data = array(
		'query' => array(
			'project_id' => $t_project_id,
			'version_id' => $p_version_id
		)
	);

	$t_command = new VersionDeleteCommand( $t_data );
	$t_command->execute();

	return true;
}

/**
 * Get the custom fields that belong to the specified project.
 *
 * @param string  $p_username   The name of the user trying to access the versions.
 * @param string  $p_password   The password of the user.
 * @param int    $p_project_id The id of the project to retrieve the custom fields for.
 *
 * @return array|RestFault|SoapFault  representing a CustomFieldDefinitionDataArray structure.
 */
function mc_project_get_custom_fields( $p_username, $p_password, $p_project_id ) {
	$t_user_id = mci_check_login( $p_username, $p_password );

	if( $t_user_id === false ) {
		return mci_fault_login_failed();
	}

	if( !mci_has_readonly_access( $t_user_id, $p_project_id ) ) {
		return mci_fault_access_denied( $t_user_id );
	}

	return mci_project_get_custom_fields( $p_project_id );
}

/**
 * Validate custom fields before creating/updating issues.
 *
 * @param int   $p_project_id    The project id.
 * @param array $p_custom_fields The custom fields (by reference).
 *                               May be unset; if so, will be set to null.
 *
 * @return void
 * @throws ClientException
 *
 * @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection
 */
function mci_project_custom_fields_validate( $p_project_id, &$p_custom_fields ) {
	# Load custom field definitions for the specified project
	$t_related_custom_field_ids = custom_field_get_linked_ids( $p_project_id );
	$t_custom_field_defs = array();
	foreach( $t_related_custom_field_ids as $t_custom_field_id ) {
		$t_def = custom_field_get_definition( $t_custom_field_id );
		$t_custom_field_defs[$t_custom_field_id] = $t_def;
	}

	$t_custom_field_values = array();
	if( isset( $p_custom_fields ) ) {
		if( !is_array( $p_custom_fields ) ) {
			throw new ClientException(
				"Invalid Custom Field '$p_custom_fields'",
				ERROR_CUSTOM_FIELD_NOT_FOUND
			);
		}
		foreach( $p_custom_fields as $t_custom_field ) {
			$t_custom_field = ApiObjectFactory::objectToArray( $t_custom_field );

			if( !isset( $t_custom_field['value'] ) ) {
				throw new ClientException(
					'Custom field has no value specified.',
					ERROR_EMPTY_FIELD,
					array( "custom_field['value']" )
				);
			}

			if( !isset( $t_custom_field['field'] ) ) {
				throw new ClientException(
					'Custom field with no specified id or name.',
					ERROR_EMPTY_FIELD,
					array( "custom_field['field']" )
				);
			}

			$t_custom_field_id = mci_get_custom_field_id_from_objectref( (object)$t_custom_field['field'] );
			if( $t_custom_field_id == 0 ) {
				throw new ClientException(
					'Invalid Custom Field '
					# Output JSON stripped of quotes to help caller identify offending field
					. str_replace( '"', '', json_encode( $t_custom_field['field'] ) ),
					ERROR_CUSTOM_FIELD_NOT_FOUND
				);
			} else {
				# Make sure the custom field is linked to the current project
				if( !isset( $t_custom_field_defs[$t_custom_field_id] ) ) {
					throw new ClientException(
						"Custom Field Id '$t_custom_field_id' not found in Project '$p_project_id'.",
						ERROR_CUSTOM_FIELD_NOT_FOUND
					);
				}
				$t_def = $t_custom_field_defs[$t_custom_field_id];
				$t_custom_field_values[$t_def['name']] = $t_custom_field['value'];
			}
		}
	}

	# Validate the custom fields before adding the bug.
	foreach( $t_related_custom_field_ids as $t_custom_field_id ) {
		# Skip custom fields that user doesn't have access to write.
		if( !custom_field_has_write_access_to_project( $t_custom_field_id, $p_project_id ) ) {
			continue;
		}

		$t_def = $t_custom_field_defs[$t_custom_field_id];
		$t_name = custom_field_get_field( $t_custom_field_id, 'name' );

		# Produce an error if the field is required but wasn't posted
		if( $t_def['require_report'] ) {
			if( !isset( $t_custom_field_values[$t_name] ) ||
			    is_blank( $t_custom_field_values[$t_name] ) ) {
				throw new ClientException(
					"Mandatory field '$t_name' is missing.",
					ERROR_EMPTY_FIELD,
					array( $t_name )
				);
			}
		}

		if( isset( $t_custom_field_values[$t_name] ) &&
		    !custom_field_validate( $t_custom_field_id, $t_custom_field_values[$t_name] ) ) {
			throw new ClientException(
				"Invalid custom field '$t_name' value.",
				ERROR_CUSTOM_FIELD_INVALID_VALUE,
				array( $t_name )
			);
		}
	}
}

/**
 * Get the custom fields that belong to the specified project.
 *
 * @param int $p_project_id The id of the project to retrieve the custom fields for.
 *
 * @return array|RestFault|SoapFault  representing a CustomFieldDefinitionDataArray structure.
 */
function mci_project_get_custom_fields( $p_project_id ) {
	global $g_project_override;

	if( !project_exists( $p_project_id ) ) {
		return ApiObjectFactory::faultNotFound( "Project '$p_project_id' does not exist." );
	}

	$g_project_override = $p_project_id;

	$t_result = array();
	$t_related_custom_field_ids = custom_field_get_linked_ids( $p_project_id );
	$t_user_id = auth_get_current_user_id();
	$t_lang = mci_get_user_lang( $t_user_id );

	foreach( $t_related_custom_field_ids as $t_id ) {
		$t_def = custom_field_get_definition( $t_id );
		if( access_has_project_level( $t_def['access_level_r'], $p_project_id ) ) {
			$t_custom_field = array();

			if( ApiObjectFactory::$soap ) {
				$t_custom_field['field'] = array(
					'id' => $t_def['id'],
					'name' => $t_def['name'],
				);
			} else {
				$t_custom_field['id'] = (int)$t_def['id'];
				$t_custom_field['name'] = $t_def['name'];
			}

			$t_custom_field['type'] = $t_def['type'];
			$t_custom_field['default_value'] = $t_def['default_value'];
			$t_custom_field['possible_values'] = $t_def['possible_values'];
			$t_custom_field['valid_regexp'] = $t_def['valid_regexp'];
			$t_custom_field['length_min'] = (int)$t_def['length_min'];
			$t_custom_field['length_max'] = (int)$t_def['length_max'];

			if( ApiObjectFactory::$soap ) {
				$t_custom_field['access_level_r'] = $t_def['access_level_r'];
				$t_custom_field['access_level_rw'] = $t_def['access_level_rw'];
				$t_custom_field['display_report'] = (int)$t_def['display_report'];
				$t_custom_field['display_update'] = (int)$t_def['display_update'];
				$t_custom_field['display_resolved'] = (int)$t_def['display_resolved'];
				$t_custom_field['display_closed'] = (int)$t_def['display_closed'];
				$t_custom_field['require_report'] = (int)$t_def['require_report'];
				$t_custom_field['require_update'] = (int)$t_def['require_update'];
				$t_custom_field['require_resolved'] = (int)$t_def['require_resolved'];
				$t_custom_field['require_closed'] = (int)$t_def['require_closed'];
			} else {
				$t_custom_field['type'] = mci_custom_field_type_name( $t_def['type'] );
				$t_custom_field['access_level_r'] = mci_enum_get_array_by_id( $t_def['access_level_r'], 'access_levels', $t_lang );
				$t_custom_field['access_level_rw'] = mci_enum_get_array_by_id( $t_def['access_level_rw'], 'access_levels', $t_lang );
				$t_custom_field['display_report'] = (bool)$t_def['display_report'];
				$t_custom_field['display_update'] = (bool)$t_def['display_update'];
				$t_custom_field['display_resolved'] = (bool)$t_def['display_resolved'];
				$t_custom_field['display_closed'] = (bool)$t_def['display_closed'];
				$t_custom_field['require_report'] = (bool)$t_def['require_report'];
				$t_custom_field['require_update'] = (bool)$t_def['require_update'];
				$t_custom_field['require_resolved'] = (bool)$t_def['require_resolved'];
				$t_custom_field['require_closed'] = (bool)$t_def['require_closed'];
			}

			$t_result[] = $t_custom_field;
		}
	}

	return $t_result;
}

/**
 * Get the custom field type name given its type id.
 *
 * @param int $p_type_id The custom field type id.
 *
 * @return string The type name or id if an unknown type.
 */
function mci_custom_field_type_name( $p_type_id ) {
	switch( $p_type_id ) {
		case CUSTOM_FIELD_TYPE_STRING:
			return 'string';
		case CUSTOM_FIELD_TYPE_TEXTAREA:
			return 'textarea';
		case CUSTOM_FIELD_TYPE_NUMERIC:
			return 'numeric';
		case CUSTOM_FIELD_TYPE_FLOAT:
			return 'float';
		case CUSTOM_FIELD_TYPE_ENUM:
			return 'enum';
		case CUSTOM_FIELD_TYPE_EMAIL:
			return 'email';
		case CUSTOM_FIELD_TYPE_CHECKBOX:
			return 'checkbox';
		case CUSTOM_FIELD_TYPE_LIST:
			return 'list';
		case CUSTOM_FIELD_TYPE_MULTILIST:
			return 'multilist';
		case CUSTOM_FIELD_TYPE_DATE:
			return 'date';
		default:
			return $p_type_id;
	}
}

/**
 * Get list of versions for a project.
 *
 * @param int $p_project_id The project id
 *
 * @return array The project versions.
 */
function mci_project_versions( $p_project_id ) {
	# use VERSION_ALL, true as the fastest way (no additional where clause in query)
	# to get all released / non-released and obsolete / non-obsolete versions
	$t_versions = version_get_all_rows( $p_project_id, VERSION_ALL, true );
	$t_results = array();

	foreach( $t_versions as $t_version ) {
		$t_result = array(
			'id' => (int)$t_version['id'],
			'name' => $t_version['version'],
			'description' => $t_version['description'],
			'released' => (bool)$t_version['released'],
			'obsolete' => (bool)$t_version['obsolete'],
			'timestamp' => ApiObjectFactory::datetime( $t_version['date_order'] ),
		);

		$t_results[] = $t_result;
	}

	return $t_results;
}

/**
 * Get projects for the specified project.
 *
 * @param int $p_project_id The project id
 *
 * @return array The array of categories with their info.
 */
function mci_project_categories( $p_project_id ) {
	$t_categories = category_get_all_rows( $p_project_id );
	$t_results = array();

	foreach( $t_categories as $t_category ) {
		$t_project_id = (int)$t_category['project_id'];
		$t_result = array(
			'id' => (int)$t_category['id'],
			'name' => $t_category['name'],
			'project' => array( 'id' => $t_project_id, 'name' => $t_category['project_name'] ),
			'status' => $t_category['status'],
		);

		# Do access check here to take into consideration the project id that the
		# category is associated with in case of inherited categories.
		$t_default_handler_id = (int)$t_category['user_id'];
		if( $t_default_handler_id != 0 &&
		    access_has_project_level( config_get( 'manage_project_threshold', null, null, $t_project_id ), $t_project_id ) ) {
			$t_result['default_handler'] = mci_account_get_array_by_id( $t_default_handler_id );
		}

		$t_results[] = $t_result;
	}

	return $t_results;
}

/**
 * Get the attachments that belong to the specified project.
 *
 * @param string $p_username   The name of the user trying to access the versions.
 * @param string $p_password   The password of the user.
 * @param int    $p_project_id The id of the project to retrieve the attachments for.
 *
 * @return array|RestFault|SoapFault Array representing a ProjectAttachmentDataArray structure.
 */
function mc_project_get_attachments( $p_username, $p_password, $p_project_id ) {
	global $g_project_override;

	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_fault_login_failed();
	}

	$p_project_id = (int)$p_project_id;
	$g_project_override = $p_project_id;

	# Check if project documentation feature is enabled.
	if( OFF == config_get( 'enable_project_documentation' ) || !file_is_uploading_enabled() ) {
		return mci_fault_access_denied( $t_user_id );
	}

	if( !project_exists( $p_project_id ) ) {
		return ApiObjectFactory::faultNotFound( 'Project \'' . $p_project_id . '\' does not exist.' );
	}

	if( !mci_has_readonly_access( $t_user_id, $p_project_id ) ) {
		return mci_fault_access_denied( $t_user_id );
	}

	$t_admin = config_get_global( 'admin_site_threshold' );

	if( $p_project_id == ALL_PROJECTS ) {
		# Select all the projects that the user has access to
		$t_projects = user_get_accessible_projects( $t_user_id );
	} else {
		# Select the specific project
		$t_projects = array(
			$p_project_id,
		);
	}

	$t_projects[] = ALL_PROJECTS; # add ALL_PROJECTS to the list of projects to fetch

	$t_reqd_access = config_get( 'view_proj_doc_threshold' );
	if( is_array( $t_reqd_access ) ) {
		if( 1 == count( $t_reqd_access ) ) {
			$t_access_clause = '= ' . array_shift( $t_reqd_access ) . ' ';
		} else {
			$t_access_clause = 'IN (' . implode( ',', $t_reqd_access ) . ')';
		}
	} else {
		$t_access_clause = '>= ' . $t_reqd_access;
	}

	$t_query = 'SELECT pft.id, pft.project_id, pft.filename, pft.file_type, pft.filesize, pft.title, pft.description, pft.date_added, pft.user_id
		FROM {project_file} pft
		LEFT JOIN {project} pt ON pft.project_id = pt.id
		LEFT JOIN {project_user_list} pult
		ON pft.project_id = pult.project_id AND pult.user_id = ' . db_param() . '
		LEFT JOIN {user} ut ON ut.id = ' . db_param() . '
		WHERE pft.project_id in (' . implode( ',', $t_projects ) . ') AND
		( ( ( pt.view_state = ' . db_param() . ' OR pt.view_state is null ) AND pult.user_id is null AND ut.access_level ' . $t_access_clause . ' ) OR
		( ( pult.user_id = ' . db_param() . ' ) AND ( pult.access_level ' . $t_access_clause . ' ) ) OR
		( ut.access_level = ' . db_param() . ' ) )
		ORDER BY pt.name ASC, pft.title ASC';

	$t_result = db_query( $t_query, array( $t_user_id, $t_user_id, VS_PUBLIC, $t_user_id, $t_admin ) );
	$t_num_files = db_num_rows( $t_result );

	$t_attachments = array();
	for( $i = 0; $i < $t_num_files; $i++ ) {
		$t_row = db_fetch_array( $t_result );

		$t_attachment = array();
		$t_attachment['id'] = $t_row['id'];
		$t_attachment['filename'] = $t_row['filename'];
		$t_attachment['title'] = $t_row['title'];
		$t_attachment['description'] = $t_row['description'];
		$t_attachment['size'] = $t_row['filesize'];
		$t_attachment['content_type'] = $t_row['file_type'];
		$t_attachment['date_submitted'] = ApiObjectFactory::datetime( $t_row['date_added'] );
		$t_attachment['download_url'] = mci_get_mantis_path() . 'file_download.php?file_id=' . $t_row['id'] . '&amp;type=doc';
		$t_attachment['user_id'] = $t_row['user_id'];
		$t_attachments[] = $t_attachment;
	}

	return $t_attachments;
}

/**
 * Get the list of subprojects for a given project.
 *
 * @param string $p_username   The name of the user trying to access the versions.
 * @param string $p_password   The password of the user.
 * @param int    $p_project_id The id of the project to retrieve the attachments for.
 *
 * @return array|RestFault|SoapFault
 */
function mc_project_get_all_subprojects( $p_username, $p_password, $p_project_id ) {
	global $g_project_override;

	$t_user_id = mci_check_login( $p_username, $p_password );

	if( $t_user_id === false ) {
		return mci_fault_access_denied();
	}

	if( !project_exists( $p_project_id ) ) {
		return ApiObjectFactory::faultNotFound( 'Project \'' . $p_project_id . '\' does not exist.' );
	}

	$g_project_override = $p_project_id;

	if( !mci_has_readonly_access( $t_user_id, $p_project_id ) ) {
		return mci_fault_access_denied( $t_user_id );
	}

	return user_get_all_accessible_subprojects( $t_user_id, $p_project_id );
}

/**
 * Get a project definition.
 *
 * @param int $p_project_id The id of the project to retrieve.
 *
 * @return array an array containing the id and the name of the project.
 */
function mci_project_as_array_by_id( $p_project_id ) {
	$t_result = array();
	$t_result['id'] = (int)$p_project_id;
	$t_result['name'] = project_get_name( $p_project_id );
	return $t_result;
}

/**
 * Get the id of a project via the project's name.
 *
 * @param string $p_username     The name of the user trying to access the versions.
 * @param string $p_password     The password of the user.
 * @param string $p_project_name The name of the project to retrieve.
 *
 * @return int|RestFault|SoapFault The id of the project with the given name, 0 if there is no such project.
 */
function mc_project_get_id_from_name( $p_username, $p_password, $p_project_name ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_fault_login_failed();
	}

	return project_get_id_by_name( $p_project_name );
}


### MantisConnect Administrative Webservices ###

/**
 * Add a new project.
 *
 * @param string   $p_username The name of the user trying to access the versions.
 * @param string   $p_password The password of the user.
 * @param stdClass $p_project  A new ProjectData structure.
 *
 * @return int|RestFault|SoapFault The new project's project_id
 * @throws ClientException
 */
function mc_project_add( $p_username, $p_password, stdClass $p_project ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_fault_login_failed();
	}

	if( !mci_has_administrator_access( $t_user_id ) ) {
		return mci_fault_access_denied( $t_user_id );
	}

	$p_project = ApiObjectFactory::objectToArray( $p_project );

	$t_project_data = array();

	if( isset( $p_project['name'] ) ) {
		$t_project_data['name'] = $p_project['name'];
	}

	if( isset( $p_project['status'] ) ) {
		$t_project_data['status'] = $p_project['status'];
	}

	if( isset( $p_project['view_state'] ) ) {
		$t_project_data['view_state'] = $p_project['view_state'];
	}

	if( isset( $p_project['enabled'] ) ) {
		$t_project_data['enabled'] = $p_project['enabled'];
	}

	if( isset( $p_project['description'] ) ) {
		$t_project_data['description'] = $p_project['description'];
	}

	if( isset( $p_project['file_path'] ) ) {
		$t_project_data['file_path'] = $p_project['file_path'];
	}

	if( isset( $p_project['inherit_global'] ) ) {
		$t_project_data['inherit_global'] = $p_project['inherit_global'];
	}

	$t_data = array( 'payload' => $t_project_data );

	$t_command = new ProjectAddCommand( $t_data );
	$t_result = $t_command->execute();

	# project_create returns the new project's id, spit that out to web service caller
	return $t_result['id'];
}

/**
 * Update a project
 *
 * @param string   $p_username   The name of the user.
 * @param string   $p_password   The password of the user.
 * @param int      $p_project_id A project's identifier.
 * @param stdClass $p_project    A new ProjectData structure.
 *
 * @return bool|RestFault|SoapFault True or false depending on the success of the update action.
 * @throws ClientException
 */
function mc_project_update( $p_username, $p_password, $p_project_id, stdClass $p_project ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_fault_access_denied();
	}

	$p_project = ApiObjectFactory::objectToArray( $p_project );

	$t_project_data = array();

	if( isset( $p_project['name'] ) ) {
		$t_project_data['name'] = $p_project['name'];
	}

	if( isset( $p_project['description'] ) ) {
		$t_project_data['description'] = $p_project['description'];
	}

	if( isset( $p_project['status'] ) ) {
		$t_project_data['status'] = $p_project['status'];
	}

	if( isset( $p_project['view_state'] ) ) {
		$t_project_data['view_state'] = $p_project['view_state'];
	}

	if( isset( $p_project['file_path'] ) ) {
		$t_project_data['file_path'] = $p_project['file_path'];
	}

	if( isset( $p_project['enabled'] ) ) {
		$t_project_data['enabled'] = $p_project['enabled'];
	}

	if( isset( $p_project['inherit_global'] ) ) {
		$t_project_data['inherit_global'] = $p_project['inherit_global'];
	}

	$t_data = array(
		'query' => array(
			'id' => $p_project_id,
		),
		'payload' => $t_project_data,
	);

	$t_command = new ProjectUpdateCommand( $t_data );
	$t_command->execute();

	return true;
}

/**
 * Delete a project.
 *
 * @param string $p_username   The name of the user trying to access the versions.
 * @param string $p_password   The password of the user.
 * @param int    $p_project_id A project's identifier.
 *
 * @return bool|RestFault|SoapFault True or false depending on the success of the delete action.
 * @throws ClientException
 */
function mc_project_delete( $p_username, $p_password, $p_project_id ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_fault_login_failed();
	}

	$t_data = array(
		'query' => array(
			'id' => (int)$p_project_id,
		),
	);

	$t_command = new ProjectDeleteCommand( $t_data );
	$t_command->execute();

	return true;
}

/**
 * Get Issue Headers.
 *
 * @param string $p_username    The name of the user trying to access the versions.
 * @param string $p_password    The password of the user.
 * @param int    $p_project_id  The id of the project to retrieve the attachments for.
 * @param int    $p_page_number Page number.
 * @param int    $p_per_page    Per page.
 *
 * @return array|RestFault|SoapFault
 */
function mc_project_get_issue_headers( $p_username, $p_password, $p_project_id, $p_page_number, $p_per_page ) {
	global $g_project_override;

	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_fault_login_failed();
	}
	if( $p_project_id != ALL_PROJECTS && !project_exists( $p_project_id ) ) {
		return ApiObjectFactory::faultNotFound( 'Project \'' . $p_project_id . '\' does not exist.' );
	}

	$g_project_override = $p_project_id;

	if( !mci_has_readonly_access( $t_user_id, $p_project_id ) ) {
		return mci_fault_access_denied( $t_user_id );
	}

	$t_orig_page_number = max( $p_page_number, 1 );
	$t_page_count = 0;
	$t_bug_count = 0;

	$t_rows = filter_get_bug_rows( $p_page_number, $p_per_page, $t_page_count, $t_bug_count, null, $p_project_id );
	$t_result = array();

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
 * Get appropriate users assigned to a project by access level.
 *
 * @param string $p_username   The name of the user trying to access the versions.
 * @param string $p_password   The password of the user.
 * @param int    $p_project_id The id of the project to retrieve the users for.
 * @param int    $p_access     Minimum access level.
 *
 * @return array|RestFault|SoapFault Array representing a ProjectAttachmentDataArray structure.
 * @throws ClientException
 */
function mc_project_get_users( $p_username, $p_password, $p_project_id, $p_access ) {
	$t_user_id = mci_check_login( $p_username, $p_password );

	if( $t_user_id === false ) {
		return mci_fault_login_failed();
	}

	$t_data = array(
		'query' => array(
			'id' => $p_project_id,
			'page' => 1,
			'page_size' => 0,
			'access_level' => $p_access,
			'include_access_levels' => 0
		)
	);

	$t_command = new ProjectUsersGetCommand( $t_data );
	$t_result = $t_command->execute();
	return $t_result['users'];
}
