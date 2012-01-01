<?php
# MantisConnect - A webservice interface to Mantis Bug Tracker
# Copyright (C) 2004-2012  Victor Boctor - vboctor@users.sourceforge.net
# This program is distributed under dual licensing.  These include
# GPL and a commercial licenses.  Victor Boctor reserves the right to
# change the license of future releases.
# See docs/ folder for more details

function mc_project_get_issues( $p_username, $p_password, $p_project_id, $p_page_number, $p_per_page ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}
	
	$t_lang = mci_get_user_lang( $t_user_id );
	
	if( $p_project_id != ALL_PROJECTS && !project_exists( $p_project_id ) ) {
		return new soap_fault( 'Client', '', "Project '$p_project_id' does not exist." );
	}

	if( !mci_has_readonly_access( $t_user_id, $p_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	$t_orig_page_number = $p_page_number < 1 ? 1 : $p_page_number;
	$t_page_count = 0;
	$t_bug_count = 0;

	$t_rows = filter_get_bug_rows( $p_page_number, $p_per_page, $t_page_count, $t_bug_count, null, $p_project_id );
	
	$t_result = array();
	
	// the page number was moved back, so we have exceeded the actual page number, see bug #12991
	if ( $t_orig_page_number > $p_page_number )
	    return $t_result;	

	foreach( $t_rows as $t_issue_data ) {
		$t_result[] = mci_issue_data_as_array( $t_issue_data, $t_user_id, $t_lang );
	}

	return $t_result;
}

/**
 * Get all projects accessible by the given user.
 *
 * @param string $p_username  The name of the user trying to access the project list.
 * @param string $p_password  The password of the user.
 * @return Array  suitable to be converted into a ProjectDataArray
 */
function mc_projects_get_user_accessible( $p_username, $p_password ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	if( !mci_has_readonly_access( $t_user_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	$t_lang = mci_get_user_lang( $t_user_id );

	$t_result = array();
	foreach( user_get_accessible_projects( $t_user_id ) as $t_project_id ) {
		$t_project_row = project_cache_row( $t_project_id );
		$t_project = array();
		$t_project['id'] = $t_project_id;
		$t_project['name'] = $t_project_row['name'];
		$t_project['status'] = mci_enum_get_array_by_id( $t_project_row['status'], 'project_status', $t_lang );
		$t_project['enabled'] = $t_project_row['enabled'];
		$t_project['view_state'] = mci_enum_get_array_by_id( $t_project_row['view_state'], 'project_view_state', $t_lang );
		$t_project['access_min'] = mci_enum_get_array_by_id( $t_project_row['access_min'], 'access_levels', $t_lang );
		$t_project['file_path'] = array_key_exists( 'file_path', $t_project_row ) ? $t_project_row['file_path'] : "";
		$t_project['description'] = array_key_exists( 'description', $t_project_row ) ? $t_project_row['description'] : "";
		$t_project['subprojects'] = mci_user_get_accessible_subprojects( $t_user_id, $t_project_id, $t_lang );
		$t_result[] = $t_project;
	}

	return $t_result;
}

/**
 * Get all categories of a project.
 *
 * @param string $p_username  The name of the user trying to access the categories.
 * @param string $p_password  The password of the user.
 * @param integer $p_project_id  The id of the project to retrieve the categories for.
 * @return Array  of categorie names
 */
function mc_project_get_categories( $p_username, $p_password, $p_project_id ) {
	$t_user_id = mci_check_login( $p_username, $p_password );

	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	if( !project_exists( $p_project_id ) ) {
		return new soap_fault( 'Client', '', "Project '$p_project_id' does not exist." );
	}

	if( !mci_has_readonly_access( $t_user_id, $p_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	$t_result = array();
	$t_cat_array = category_get_all_rows( $p_project_id );
	foreach( $t_cat_array as $t_category_row ) {
		$t_result[] = $t_category_row['name'];
	}
	return $t_result;
}

/**
 * Add a new category to a project
 * @param string $p_username  The name of the user trying to access the categories.
 * @param string $p_password  The password of the user.
 * @param integer $p_project_id  The id of the project to retrieve the categories for.
 * @param string $p_category_name The name of the new category to add
 * @return integer id of the new category
 */

function mc_project_add_category($p_username, $p_password, $p_project_id, $p_category_name ) {
        $t_user_id = mci_check_login( $p_username, $p_password );

        if( $t_user_id === false ) {
                return new soap_fault( 'Client', '', 'Access Denied' );
        }

        if( !project_exists( $p_project_id ) ) {
                return new soap_fault( 'Client', '', "Project '$p_project_id' does not exist." );
        }

        if( !mci_has_access( config_get( 'manage_project_threshold' ), $t_user_id, $p_project_id ) ) {
                return new soap_fault( 'Client', '', 'Access Denied' );
        }

        return category_add( $p_project_id, $p_category_name );
}

/**
 * Delete a category of a project
 * @param string $p_username  The name of the user trying to access the categories.
 * @param string $p_password  The password of the user.
 * @param integer $p_project_id  The id of the project to retrieve the categories for.
 * @param string $p_category_name The name of the category to delete
 * @return bool returns true or false depending on the success of the delete action
 */

function mc_project_delete_category ($p_username, $p_password, $p_project_id, $p_category_name) {
        $t_user_id = mci_check_login( $p_username, $p_password );

        if( $t_user_id === false ) {
                return new soap_fault( 'Client', '', 'Access Denied' );
        }

        if( !project_exists( $p_project_id ) ) {
                return new soap_fault( 'Client', '', "Project '$p_project_id' does not exist." );
        }

        if( !mci_has_access( config_get( 'manage_project_threshold' ), $t_user_id, $p_project_id ) ) {
                return new soap_fault( 'Client', '', 'Access Denied' );
        }

        // find the id of the category
        $p_category_id = category_get_id_by_name( $p_category_name, $p_project_id );

        // delete the category and link all the issue to the general category by default
        return category_remove( $p_category_id, 1 );
}

/**
 * Update a category of a project
 * @param string $p_username  The name of the user trying to access the categories.
 * @param string $p_password  The password of the user.
 * @param integer $p_project_id  The id of the project to retrieve the categories for.
 * @param string $p_category_name The name of the category to rename
 * @param string $p_category_name_new The new name of the category to rename
 * @param int $p_assigned_to User ID that category is assigned to
 * @return bool returns true or false depending on the success of the update action
 */

function mc_project_rename_category_by_name( $p_username, $p_password, $p_project_id, $p_category_name, $p_category_name_new, $p_assigned_to ) {
        $t_user_id = mci_check_login( $p_username, $p_password );

        if ( null === $p_assigned_to ) {
                return new soap_fault( 'Client', '', 'p_assigned_to needed' );
        }

        if( $t_user_id === false ) {
                return new soap_fault( 'Client', '', 'Access Denied' );
        }

        if( !project_exists( $p_project_id ) ) {
                return new soap_fault( 'Client', '', "Project '$p_project_id' does not exist." );
        }

        if( !mci_has_access( config_get( 'manage_project_threshold' ), $t_user_id, $p_project_id ) ) {
                return new soap_fault( 'Client', '', 'Access Denied' );
        }

        // find the id of the category
        $p_category_id = category_get_id_by_name( $p_category_name, $p_project_id );

        // update the category
        return category_update( $p_category_id, $p_category_name_new, $p_assigned_to );
}

/**
 * Get all versions of a project.
 *
 * @param string $p_username  The name of the user trying to access the versions.
 * @param string $p_password  The password of the user.
 * @param integer $p_project_id  The id of the project to retrieve the versions for.
 * @return Array  representing a ProjectVersionDataArray structure.
 */
function mc_project_get_versions( $p_username, $p_password, $p_project_id ) {
	$t_user_id = mci_check_login( $p_username, $p_password );

	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	if( !project_exists( $p_project_id ) ) {
		return new soap_fault( 'Client', '', "Project '$p_project_id' does not exist." );
	}

	if( !mci_has_readonly_access( $t_user_id, $p_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}
	
	$t_result = array();
	foreach( version_get_all_rows( $p_project_id, VERSION_ALL ) as $t_version ) {
		$t_result[] = mci_project_version_as_array ( $t_version );
	}

	return $t_result;
}

/**
 * Get all released versions of a project.
 *
 * @param string $p_username  The name of the user trying to access the versions.
 * @param string $p_password  The password of the user.
 * @param integer $p_project_id  The id of the project to retrieve the versions for.
 * @return Array  representing a ProjectVersionDataArray structure.
 */
function mc_project_get_released_versions( $p_username, $p_password, $p_project_id ) {
	$t_user_id = mci_check_login( $p_username, $p_password );

	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	if( !project_exists( $p_project_id ) ) {
		return new soap_fault( 'Client', '', "Project '$p_project_id' does not exist." );
	}

	if( !mci_has_readonly_access( $t_user_id, $p_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	$t_result = array();

	foreach( version_get_all_rows( $p_project_id, VERSION_RELEASED ) as $t_version ) {
		$t_result[] = mci_project_version_as_array ( $t_version );
	}

	return $t_result;
}

/**
 * Get all unreleased (a.k.a. future) versions of a project.
 *
 * @param string $p_username  The name of the user trying to access the versions.
 * @param string $p_password  The password of the user.
 * @param integer $p_project_id  The id of the project to retrieve the versions for.
 * @return Array  representing a ProjectVersionDataArray structure.
 */
function mc_project_get_unreleased_versions( $p_username, $p_password, $p_project_id ) {
	$t_user_id = mci_check_login( $p_username, $p_password );

	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	if( !project_exists( $p_project_id ) ) {
		return new soap_fault( 'Client', '', "Project '$p_project_id' does not exist." );
	}

	if( !mci_has_readonly_access( $t_user_id, $p_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	$t_result = array();

	foreach( version_get_all_rows( $p_project_id, VERSION_FUTURE ) as $t_version )
		$t_result[] = mci_project_version_as_array ( $t_version );

	return $t_result;
}

/**
 * Submit the specified version details.
 *
 * @param string $p_username  The name of the user trying to add the issue.
 * @param string $p_password  The password of the user.
 * @param Array $p_version  A ProjectVersionData structure containing information about the new verison.
 * @return integer  The id of the created version.
 */
function mc_project_version_add( $p_username, $p_password, $p_version ) {
	$t_user_id = mci_check_login( $p_username, $p_password );

	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	$t_project_id = $p_version['project_id'];
	$t_name = $p_version['name'];
	$t_released = $p_version['released'];
	$t_description = $p_version['description'];
	$t_date_order =  $p_version['date_order'];
	if ( is_blank( $t_date_order ) ) 
	    $t_date_order = null;
	else 
	    $t_date_order = date( "Y-m-d H:i:s", strtotime( $t_date_order ) );
	
	$t_obsolete = isset ( $p_version['obsolete'] ) ? $p_version['obsolete'] : false;
	
	if ( is_blank( $t_project_id ) ) {
		return new soap_fault( 'Client', '', 'Mandatory field "project_id" was missing' );
	}

	if ( !project_exists( $t_project_id ) ) {
		return new soap_fault( 'Client', '', "Project '$t_project_id' does not exist." );
	}

	if ( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	if ( !mci_has_access( config_get( 'manage_project_threshold' ), $t_user_id, $t_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	if ( is_blank( $t_name ) ) {
		return new soap_fault( 'Client', '', 'Mandatory field "name" was missing' );
	}

	if ( !version_is_unique( $t_name, $t_project_id ) ) {
		return new soap_fault( 'Client', '', 'Version exists for project', 'The version you attempted to add already exists for this project' );
	}

	if ( $t_released === false ) {
		$t_released = VERSION_FUTURE;
	} else {
		$t_released = VERSION_RELEASED;
	}
	
	if ( version_add( $t_project_id, $t_name, $t_released, $t_description, $t_date_order, $t_obsolete ) )
		return version_get_id( $t_name, $t_project_id );

	return null;
}

/**
 * Submit the specified version details.
 *
 * @param string $p_username  The name of the user trying to update the issue.
 * @param string $p_password  The password of the user.
 * @param integer $p_version_id A version's id
 * @param Array $p_version  A ProjectVersionData structure containing information about the new verison.
 * @return bool returns true or false depending on the success of the update action
 */
function mc_project_version_update( $p_username, $p_password, $p_version_id, $p_version ) {
	$t_user_id = mci_check_login( $p_username, $p_password );

	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	if( is_blank( $p_version_id ) ) {
		return new soap_fault( 'Client', '', 'Mandatory field "version_id" was missing' );
	}

	if( !version_exists( $p_version_id ) ) {
		return new soap_fault( 'Client', '', "Version '$p_version_id' does not exist." );
	}

	$t_project_id = $p_version['project_id'];
	$t_name = $p_version['name'];
	$t_released = $p_version['released'];
	$t_description = $p_version['description'];
	$t_date_order = $p_version['date_order'];
	$t_obsolete = isset ( $p_version['obsolete'] ) ? $p_version['obsolete'] : false;

	if ( is_blank( $t_project_id ) ) {
		return new soap_fault( 'Client', '', 'Mandatory field "project_id" was missing' );
	}

	if ( !project_exists( $t_project_id ) ) {
		return new soap_fault( 'Client', '', "Project '$t_project_id' does not exist." );
	}

	if ( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	if ( !mci_has_access( config_get( 'manage_project_threshold' ), $t_user_id, $t_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	if ( is_blank( $t_name ) ) {
		return new soap_fault( 'Client', '', 'Mandatory field "name" was missing' );
	}

	# check for duplicates
	$t_old_version_name = version_get_field( $p_version_id, 'version' );
	if ( ( strtolower( $t_old_version_name ) != strtolower( $t_name ) ) && !version_is_unique( $t_name, $t_project_id ) ) {
		return new soap_fault( 'Client', '', 'Version exists for project', 'The version you attempted to update already exists for this project' );
	}

	if ( $t_released === false ) {
		$t_released = VERSION_FUTURE;
	} else {
		$t_released = VERSION_RELEASED;
	}

	$t_version_data = new VersionData();
	$t_version_data->id = $p_version_id;
	$t_version_data->project_id = $t_project_id;
	$t_version_data->version = $t_name;
	$t_version_data->description = $t_description;
	$t_version_data->released = $t_released;
	$t_version_data->date_order = date( "Y-m-d H:i:s", strtotime( $t_date_order ) );
	$t_version_data->obsolete = $t_obsolete;

	return version_update( $t_version_data );
}

/**
 * Delete a version.
 *
 * @param string $p_username  The name of the user trying to delete the version.
 * @param string $p_password  The password of the user.
 * @param integer $p_version_id A version's id
 * @return bool returns true or false depending on the success of the delete action
 */
function mc_project_version_delete( $p_username, $p_password, $p_version_id ) {
	$t_user_id = mci_check_login( $p_username, $p_password );

	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	if( is_blank( $p_version_id ) ) {
		return new soap_fault( 'Client', '', 'Mandatory field "version_id" was missing' );
	}

	if( !version_exists( $p_version_id ) ) {
		return new soap_fault( 'Client', '', "Version '$p_version_id' does not exist." );
	}

	$t_project_id = version_get_field( $p_version_id, 'project_id' );

	if( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	if( !mci_has_access( config_get( 'manage_project_threshold' ), $t_user_id, $t_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	return version_remove( $p_version_id );
}

/**
 * Get the custom fields that belong to the specified project.
 *
 * @param string $p_username  The name of the user trying to access the versions.
 * @param string $p_password  The password of the user.
 * @param integer $p_project_id  The id of the project to retrieve the custom fields for.
 * @return Array  representing a CustomFieldDefinitionDataArray structure.
 */
function mc_project_get_custom_fields( $p_username, $p_password, $p_project_id ) {
	$t_user_id = mci_check_login( $p_username, $p_password );

	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	if( !project_exists( $p_project_id ) ) {
		return new soap_fault( 'Client', '', "Project '$p_project_id' does not exist." );
	}

	if( !mci_has_readonly_access( $t_user_id, $p_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	$t_result = array();
	$t_related_custom_field_ids = custom_field_get_linked_ids( $p_project_id );

	foreach( custom_field_get_linked_ids( $p_project_id ) as $t_id ) {
		$t_def = custom_field_get_definition( $t_id );
		if( access_has_project_level( $t_def['access_level_r'], $p_project_id ) ) {
			$t_result[] = array(
				'field' => array(
					'id' => $t_def['id'],
					'name' => $t_def['name'],
				),
				'type' => $t_def['type'],
				'default_value' => $t_def['default_value'],
				'possible_values' => $t_def['possible_values'],
				'valid_regexp' => $t_def['valid_regexp'],
				'access_level_r' => $t_def['access_level_r'],
				'access_level_rw' => $t_def['access_level_rw'],
				'length_min' => $t_def['length_min'],
				'length_max' => $t_def['length_max'],
				'display_report' => $t_def['display_report'],
				'display_update' => $t_def['display_update'],
				'display_resolved' => $t_def['display_resolved'],
				'display_closed' => $t_def['display_closed'],
				'require_report' => $t_def['require_report'],
				'require_update' => $t_def['require_update'],
				'require_resolved' => $t_def['require_resolved'],
				'require_closed' => $t_def['require_closed'],
			);
		}
	}

	return $t_result;
}

/**
 * Get the attachments that belong to the specified project.
 *
 * @param string $p_username  The name of the user trying to access the versions.
 * @param string $p_password  The password of the user.
 * @param integer $p_project_id  The id of the project to retrieve the attachments for.
 * @return Array  representing a ProjectAttachmentDataArray structure.
 */
function mc_project_get_attachments( $p_username, $p_password, $p_project_id ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	# Check if project documentation feature is enabled.
	if( OFF == config_get( 'enable_project_documentation' ) || !file_is_uploading_enabled() ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	if( !project_exists( $p_project_id ) ) {
		return new soap_fault( 'Client', '', "Project '$p_project_id' does not exist." );
	}

	if( !mci_has_readonly_access( $t_user_id, $p_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	$t_project_file_table = db_get_table( 'mantis_project_file_table' );
	$t_project_table = db_get_table( 'mantis_project_table' );
	$t_project_user_list_table = db_get_table( 'mantis_project_user_list_table' );
	$t_user_table = db_get_table( 'mantis_user_table' );
	$t_pub = VS_PUBLIC;
	$t_priv = VS_PRIVATE;
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
			$t_access_clause = "= " . array_shift( $t_reqd_access ) . " ";
		} else {
			$t_access_clause = "IN (" . implode( ',', $t_reqd_access ) . ")";
		}
	} else {
		$t_access_clause = ">= $t_reqd_access ";
	}

	$query = "SELECT pft.id, pft.project_id, pft.filename, pft.file_type, pft.filesize, pft.title, pft.description, pft.date_added, pft.user_id
		FROM $t_project_file_table pft
		LEFT JOIN $t_project_table pt ON pft.project_id = pt.id
		LEFT JOIN $t_project_user_list_table pult
		ON pft.project_id = pult.project_id AND pult.user_id = $t_user_id
		LEFT JOIN $t_user_table ut ON ut.id = $t_user_id
		WHERE pft.project_id in (" . implode( ',', $t_projects ) . ") AND
		( ( ( pt.view_state = $t_pub OR pt.view_state is null ) AND pult.user_id is null AND ut.access_level $t_access_clause ) OR
		( ( pult.user_id = $t_user_id ) AND ( pult.access_level $t_access_clause ) ) OR
		( ut.access_level = $t_admin ) )
		ORDER BY pt.name ASC, pft.title ASC";
	$result = db_query( $query );
	$num_files = db_num_rows( $result );

	$t_result = array();
	for( $i = 0;$i < $num_files;$i++ ) {
		$row = db_fetch_array( $result );

		$t_attachment = array();
		$t_attachment['id'] = $row['id'];
		$t_attachment['filename'] = $row['filename'];
		$t_attachment['title'] = $row['title'];
		$t_attachment['description'] = $row['description'];
		$t_attachment['size'] = $row['filesize'];
		$t_attachment['content_type'] = $row['file_type'];
		$t_attachment['date_submitted'] = timestamp_to_iso8601( $row['date_added'], false );
		$t_attachment['download_url'] = mci_get_mantis_path() . 'file_download.php?file_id=' . $row['id'] . '&amp;type=doc';
		$t_attachment['user_id'] = $row['user_id'];
		$t_result[] = $t_attachment;
	}

	return $t_result;
}

function mc_project_get_all_subprojects( $p_username, $p_password, $p_project_id ) {
	$t_user_id = mci_check_login( $p_username, $p_password );

	if( $t_user_id === false ) {
		return new soap_fault( 'Client', '', 'Access Denied' );
	}

	if( !project_exists( $p_project_id ) ) {
		return new soap_fault( 'Client', '', "Project '$p_project_id' does not exist." );
	}

	if( !mci_has_readonly_access( $t_user_id, $p_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	return user_get_all_accessible_subprojects( $t_user_id, $p_project_id );
}

/**
 * Get a project definition.
 *
 * @param integer $p_project_id  The id of the project to retrieve.
 * @return Array an Array containing the id and the name of the project.
 */
function mci_project_as_array_by_id( $p_project_id ) {
	$t_result = array();
	$t_result['id'] = $p_project_id;
	$t_result['name'] = project_get_name( $p_project_id );
	return $t_result;
}

/**
 * Get the id of a project via the project's name.
 *
 * @param string $p_username  The name of the user trying to access the versions.
 * @param string $p_password  The password of the user.
 * @param string $p_project_name  The name of the project to retrieve.
 * @return integer  The id of the project with the given name, -1 if there is no such project.
 */
function mc_project_get_id_from_name( $p_username, $p_password, $p_project_name ) {
        $t_user_id = mci_check_login( $p_username, $p_password );
        if( $t_user_id === false ) {
                return mci_soap_fault_login_failed();
        }
        
        return project_get_id_by_name ( $p_project_name );
}


### MantisConnect Administrative Webservices ###

/**
 * Add a new project.
 *
 * @param string $p_username  The name of the user trying to access the versions.
 * @param string $p_password  The password of the user.
 * @param Array $p_project A new ProjectData structure
 * @return integer the new project's project_id
 */
function mc_project_add( $p_username, $p_password, $p_project ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	if( !mci_has_administrator_access( $t_user_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	if ( !isset( $p_project['name'] ) ) {
		return new soap_fault( 'Client', '', 'Missing Field', 'Required Field Missing' );
	} else {
		$t_name = $p_project['name'];
	}
	
	if( isset( $p_project['status'] ) ) {
		$t_status = $p_project['status'];
	} else {
		$t_status = array( 'name' => 'development' ); // development
	}
	
	if( isset( $p_project['view_state'] ) ) {
		$t_view_state = $p_project['view_state'];
	} else {
		$t_view_state = array( 'id' => VS_PUBLIC );
	}
	
	if ( isset( $p_project['enabled'] ) ) {
		$t_enabled = $p_project['enabled'];
	} else {
		$t_enabled = true;
	}
	
	if ( isset( $p_project['description'] ) ) {
		$t_description = $p_project['description'];
	} else {
		$t_description = '';
	}
	
	if ( isset( $p_project['file_path'] ) ) {	
		$t_file_path = $p_project['file_path'];
	} else { 
		$t_file_path = '';
	}
	
	if ( isset( $p_project['inherit_global'] ) ) { 
		$t_inherit_global = $p_project['inherit_global'];
	} else {
		$t_inherit_global = true;
	}
	
	// check to make sure project doesn't already exist
	if( !project_is_name_unique( $t_name ) ) {
		return new soap_fault( 'Client', '', 'Project name exists', 'The project name you attempted to add exists already' );
	}

	$t_project_status = mci_get_project_status_id( $t_status );
	$t_project_view_state = mci_get_project_view_state_id( $t_view_state );

	// project_create returns the new project's id, spit that out to webservice caller
	return project_create( $t_name, $t_description, $t_project_status, $t_project_view_state, $t_file_path, $t_enabled, $t_inherit_global );
}

/**
 * Update a project
 *
 * @param string $p_username  The name of the user
 * @param string $p_password  The password of the user
 * @param integer $p_project_id A project's id
 * @param Array $p_project A new ProjectData structure
 * @return bool returns true or false depending on the success of the update action
 */
function mc_project_update( $p_username, $p_password, $p_project_id, $p_project ) {
    $t_user_id = mci_check_login( $p_username, $p_password );
    if( $t_user_id === false ) {
        return new soap_fault( 'Client', '', 'Access Denied', 'Username/password combination was incorrect' );
    }

    if( !mci_has_administrator_access( $t_user_id, $p_project_id ) ) {
        return new soap_fault( 'Client', '', 'Access Denied', 'User does not have administrator access' );
    }

    if( !project_exists( $p_project_id ) ) {
        return new soap_fault( 'Client', '', "Project '$p_project_id' does not exist." );
    }

    if ( !isset( $p_project['name'] ) ) {
        return new soap_fault( 'Client', '', 'Missing Field', 'Required Field Missing' );
    } else {
        $t_name = $p_project['name'];
    }

    // check to make sure project doesn't already exist
    if ( $t_name != project_get_name( $p_project_id ) ) {
        if( !project_is_name_unique( $t_name ) ) {
            return new soap_fault( 'Client', '', 'Project name exists', 'The project name you attempted to add exists already' );
        }
    }

    if ( !isset( $p_project['description'] ) ) {
        $t_description = project_get_field( $p_project_id, 'description' );
    } else {
        $t_description = $p_project['description'];
    }

    if ( !isset( $p_project['status'] ) ) {
        $t_status = project_get_field( $p_project_id, 'status' );
    } else {
        $t_status = $p_project['status'];
    }

    if ( !isset( $p_project['view_state'] ) ) {
        $t_view_state = project_get_field( $p_project_id, 'view_state' );
    } else {
        $t_view_state = $p_project['view_state'];
    }

    if ( !isset( $p_project['file_path'] ) ) {
        $t_file_path = project_get_field( $p_project_id, 'file_path' );
    } else {
        $t_file_path = $p_project['file_path'];
    }

    if ( !isset( $p_project['enabled'] ) ) {
        $t_enabled = project_get_field( $p_project_id, 'enabled' );
    } else {
        $t_enabled = $p_project['enabled'];
    }

    if ( !isset( $p_project['inherit_global'] ) ) {
        $t_inherit_global = project_get_field( $p_project_id, 'inherit_global' );
    } else {
        $t_inherit_global = $p_project['inherit_global'];
    }

    $t_project_status = mci_get_project_status_id( $t_status );
    $t_project_view_state = mci_get_project_view_state_id( $t_view_state );

    return project_update( $p_project_id, $t_name, $t_description, $t_project_status, $t_project_view_state, $t_file_path, $t_enabled, $t_inherit_global );
}

/**
 * Delete a project.
 *
 * @param string $p_username  The name of the user trying to access the versions.
 * @param string $p_password  The password of the user.
 * @param integer $p_project_id A project's id
 * @return bool returns true or false depending on the success of the delete action
 */
function mc_project_delete( $p_username, $p_password, $p_project_id ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	if( !project_exists( $p_project_id ) ) {
		return new soap_fault( 'Client', '', "Project '$p_project_id' does not exist." );
	}

	if( !mci_has_administrator_access( $t_user_id, $p_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	return project_delete( $p_project_id );
}

function mc_project_get_issue_headers( $p_username, $p_password, $p_project_id, $p_page_number, $p_per_page ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}
	if( $p_project_id != ALL_PROJECTS && !project_exists( $p_project_id ) ) {
		return new soap_fault( 'Client', '', "Project '$p_project_id' does not exist." );
	}

	if( !mci_has_readonly_access( $t_user_id, $p_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}
	
	$t_orig_page_number = $p_page_number < 1 ? 1 : $p_page_number;
	$t_page_count = 0;
	$t_bug_count = 0;

	$t_rows = filter_get_bug_rows( $p_page_number, $p_per_page, $t_page_count, $t_bug_count, null, $p_project_id );
	$t_result = array();
	
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

/**
 * Get appropriate users assigned to a project by access level.
 *
 * @param string $p_username  The name of the user trying to access the versions.
 * @param string $p_password  The password of the user.
 * @param integer $p_project_id  The id of the project to retrieve the users for.
 * @param integer $p_access Minimum access level.
 * @return Array  representing a ProjectAttachmentDataArray structure.
 */
function mc_project_get_users( $p_username, $p_password, $p_project_id, $p_access ) {
	$t_user_id = mci_check_login( $p_username, $p_password );

	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	$t_users = array();

	$t_users = project_get_all_user_rows( $p_project_id, $p_access ); # handles ALL_PROJECTS case

	$t_display = array();
	$t_sort = array();
	$t_show_realname = ( ON == config_get( 'show_realname' ) );
	$t_sort_by_last_name = ( ON == config_get( 'sort_by_last_name' ) );
	foreach( $t_users as $t_user ) {
		$t_user_name = string_attribute( $t_user['username'] );
		$t_sort_name = strtolower( $t_user_name );
		if( $t_show_realname && ( $t_user['realname'] <> "" ) ) {
			$t_user_name = string_attribute( $t_user['realname'] );
			if( $t_sort_by_last_name ) {
				$t_sort_name_bits = explode( ' ', strtolower( $t_user_name ), 2 );
				$t_sort_name = ( isset( $t_sort_name_bits[1] ) ? $t_sort_name_bits[1] . ', ' : '' ) . $t_sort_name_bits[0];
			} else {
				$t_sort_name = strtolower( $t_user_name );
			}
		}
		$t_display[] = $t_user_name;
		$t_sort[] = $t_sort_name;
	}
	array_multisort( $t_sort, SORT_ASC, SORT_STRING, $t_users, $t_display );

	$t_result = array();
	for( $i = 0;$i < count( $t_sort );$i++ ) {
		$t_row = $t_users[$i];

		// This is not very performant - But we have to assure that the data returned is exactly
		// the same as the data that comes with an issue (test for equality - $t_row[] does not
		// contain email fields).
		$t_result[] = mci_account_get_array_by_id( $t_row['id'] );
	}
	return $t_result;
}
