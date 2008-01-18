<?php
	# MantisConnect - A webservice interface to Mantis Bug Tracker
	# Copyright (C) 2004-2007  Victor Boctor - vboctor@users.sourceforge.net
	# This program is distributed under dual licensing.  These include
	# GPL and a commercial licenses.  Victor Boctor reserves the right to
	# change the license of future releases.
	# See docs/ folder for more details

	# --------------------------------------------------------
	# $Id: mc_project_api.php,v 1.1 2007-07-18 06:52:56 vboctor Exp $
	# --------------------------------------------------------
	
	function mc_project_get_issues( $p_username, $p_password, $p_project_id , $p_page_number, $p_per_page ) {
        $t_user_id = mci_check_login( $p_username, $p_password );
        $t_lang = mci_get_user_lang( $t_user_id );
        if ( $t_user_id === false ) {
            return new soap_fault( 'Client', '', 'Access Denied' );
        }
		if ( !project_exists( $p_project_id ) ) {
			return new soap_fault( 'Client', '', "Project '$p_project_id' does not exist." );
		}

        if ( !mci_has_readonly_access( $t_user_id, $p_project_id ) ) {
            return new soap_fault( 'Client', '', 'Access Denied' );
        }

        $t_page_count = 0;
        $t_bug_count = 0;	

        $t_rows = filter_get_bug_rows( $p_page_number, $p_per_page, $t_page_count, $t_bug_count, null, $p_project_id );
        $t_result = array();

        foreach( $t_rows as $t_issue_data ) {
            $t_id = $t_issue_data['id'];

            $t_issue = array();
            $t_issue['id'] = $t_id;
            $t_issue['view_state'] = mci_enum_get_array_by_id( $t_issue_data['view_state'], 'view_state', $t_lang );
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
            $t_issue['steps_to_reproduce'] = mci_null_if_empty( bug_get_text_field( $t_id, 'steps_to_reproduce' ) );
            $t_issue['additional_information'] = mci_null_if_empty( bug_get_text_field( $t_id, 'additional_information' ) );

            $t_issue['attachments'] = mci_issue_get_attachments( $t_issue_data['id'] );
            $t_issue['relationships'] = mci_issue_get_relationships( $t_issue_data['id'], $t_user_id );
            $t_issue['notes'] = mci_issue_get_notes( $t_issue_data['id'] );
            $t_issue['custom_fields'] = mci_issue_get_custom_fields( $t_issue_data['id'] );

            $t_result[] = $t_issue;
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
		if ( $t_user_id === false ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}

		if ( !mci_has_readonly_access( $t_user_id ) ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
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
			$t_project['file_path'] =
				array_key_exists( 'file_path', $t_project_row ) ? $t_project_row['file_path'] : "";
			$t_project['description'] =
				array_key_exists( 'description', $t_project_row ) ? $t_project_row['description'] : "";
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

		if ( $t_user_id === false ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}

		if ( !project_exists( $p_project_id ) ) {
			return new soap_fault( 'Client', '', "Project '$p_project_id' does not exist." );
		}

		if ( !mci_has_readonly_access( $t_user_id, $p_project_id ) ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}
		
		return mci_category_get_all_rows( $p_project_id, $t_user_id );
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

		if ( $t_user_id === false ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}

		if ( !project_exists( $p_project_id ) ) {
			return new soap_fault( 'Client', '', "Project '$p_project_id' does not exist." );
		}

		if ( !mci_has_readonly_access( $t_user_id, $p_project_id ) ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}

		$t_result = array();
		foreach( version_get_all_rows( $p_project_id, VERSION_ALL ) as $t_version) {
			$t_result[] = array(
				'id'			=> $t_version['id'],
				'name'			=> $t_version['version'],
				'project_id'	=> $p_project_id,
				'date_order'	=> timestamp_to_iso8601( $t_version['date_order'] ),
				'description'	=> mci_null_if_empty( $t_version['description'] ),
				'released'		=> $t_version['released'],
			);
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

		if ( $t_user_id === false ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}

		if ( !project_exists( $p_project_id ) ) {
			return new soap_fault( 'Client', '', "Project '$p_project_id' does not exist." );
		}

		if ( !mci_has_readonly_access( $t_user_id, $p_project_id ) ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}

		$t_result = array();

		foreach( version_get_all_rows( $p_project_id, VERSION_RELEASED ) as $t_version) {
			$t_result[] = array(
				'id'			=> $t_version['id'],
				'name'			=> $t_version['version'],
				'project_id'	=> $p_project_id,
				'date_order'	=> timestamp_to_iso8601( db_unixtimestamp( $t_version['date_order'] ) ),
				'description'	=> mci_null_if_empty( $t_version['description'] ),
				'released'		=> $t_version['released'],
			);
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

		if ( $t_user_id === false ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}

		if ( !project_exists( $p_project_id ) ) {
			return new soap_fault( 'Client', '', "Project '$p_project_id' does not exist." );
		}

		if ( !mci_has_readonly_access( $t_user_id, $p_project_id ) ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}

		$t_result = array();

		foreach( version_get_all_rows( $p_project_id, VERSION_FUTURE ) as $t_version) {
			$t_result[] = array(
				'id'			=> $t_version['id'],
				'name'			=> $t_version['version'],
				'project_id'	=> $p_project_id,
				'date_order'	=> timestamp_to_iso8601( $t_version['date_order'] ),
				'description'	=> mci_null_if_empty( $t_version['description'] ),
				'released'		=> $t_version['released'],
			);
		}

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
		if ( $t_user_id === false ) {
			return new soap_fault( 'Client', '', 'Access Denied', 'Username/password combination was incorrect');
		}
		if ( !mci_has_administrator_access( $t_user_id ) ) {
			return new soap_fault( 'Client', '', 'Access Denied', 'User does not have administrator access');
		}
		extract( $p_version, EXTR_PREFIX_ALL, 'v');
		if ( is_blank( $v_project_id ) ) {
			return new soap_fault('Client', '', 'Mandatory field "project_id" was missing');
		}
		if ( is_blank( $v_name ) ) {
			return new soap_fault('Client', '', 'Mandatory field "name" was missing');
		}
		if ( !version_is_unique( $v_name, $v_project_id ) ) {
			return new soap_fault( 'Client', '', 'Version exists for project', 'The version you attempted to add already exists for this project');
		}
		if ( $v_released === false ) {
			$v_released = VERSION_FUTURE;
		} else {
			$v_released = VERSION_RELEASED;
		}
		if ( version_add( $v_project_id, $v_name, $v_released, $v_description ) ) {
			$t_version_id = version_get_id( $v_name, $v_project_id );
			if ( !is_blank( $v_date_order ) ) {
				$t_version = version_get( $t_version_id );
				$t_version->date_order = date("Y-m-d H:i:s", strtotime($v_date_order));
				version_update( $t_version );
			}
			return $t_version_id;
		} else {
			return null;
		}
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
		if ( $t_user_id === false ) {
			return new soap_fault( 'Client', '', 'Access Denied', 'Username/password combination was incorrect');
		}
		if ( !mci_has_administrator_access( $t_user_id ) ) {
			return new soap_fault( 'Client', '', 'Access Denied', 'User does not have administrator access');
		}
		if ( is_blank( $p_version_id ) ) {
			return new soap_fault('Client', '', 'Mandatory field "version_id" was missing');
		}
		if ( !version_exists( $p_version_id ) ) {
			return new soap_fault( 'Client', '', "Version '$p_version_id' does not exist." );
		}
		extract( $p_version, EXTR_PREFIX_ALL, 'v');
		if ( is_blank( $v_project_id ) ) {
			return new soap_fault('Client', '', 'Mandatory field "project_id" was missing');
		}
		if ( !project_exists( $v_project_id ) ) {
			return new soap_fault( 'Client', '', "Version '$v_project_id' does not exist." );
		}
		if ( is_blank( $v_name ) ) {
			return new soap_fault('Client', '', 'Mandatory field "name" was missing');
		}
		# check for duplicates
		$t_old_version_name = version_get_field( $p_version_id, 'version' );
		if ( ( strtolower( $t_old_version_name ) != strtolower( $v_name ) ) && !version_is_unique( $v_name, $v_project_id ) ) {
			return new soap_fault( 'Client', '', 'Version exists for project', 'The version you attempted to update already exists for this project');
		}
		if ( $v_released === false ) {
			$v_released = VERSION_FUTURE;
		} else {
			$v_released = VERSION_RELEASED;
		}
		$t_version_data = new VersionData();
		$t_version_data->id = $p_version_id;
		$t_version_data->project_id = $v_project_id;
		$t_version_data->version = $v_name;
		$t_version_data->description = $v_description;
		$t_version_data->released = $v_released;
		$t_version_data->date_order = date("Y-m-d H:i:s", strtotime($v_date_order));
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
		if ( $t_user_id === false ) {
			return new soap_fault( 'Client', '', 'Access Denied', 'Username/password combination was incorrect');
		}
		if ( !mci_has_administrator_access( $t_user_id ) ) {
			return new soap_fault( 'Client', '', 'Access Denied', 'User does not have administrator access');
		}
		if ( is_blank( $p_version_id ) ) {
			return new soap_fault('Client', '', 'Mandatory field "version_id" was missing');
		}
		if ( !version_exists( $p_version_id ) ) {
			return new soap_fault( 'Client', '', "Version '$p_version_id' does not exist." );
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

		if ( $t_user_id === false ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}

		if ( !project_exists( $p_project_id ) ) {
			return new soap_fault( 'Client', '', "Project '$p_project_id' does not exist." );
		}

		if ( !mci_has_readonly_access( $t_user_id, $p_project_id ) ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}

		$t_result = array();
		
		$t_related_custom_field_ids = custom_field_get_linked_ids( $p_project_id );
		

		foreach( custom_field_get_linked_ids( $p_project_id ) as $t_id ) {
			$t_def = custom_field_get_definition( $t_id );
			if ( access_has_project_level( $t_def['access_level_r'], $p_project_id ) ) {
				$t_result[] = array(
					'field'				=> array( 'id' => $t_def['id'], 'name' => $t_def['name'] ),
					'type'				=> $t_def['type'],
					'default_value'		=> $t_def['default_value'],
					'possible_values'	=> $t_def['possible_values'],
					'valid_regexp'		=> $t_def['valid_regexp'],
					'access_level_r'	=> $t_def['access_level_r'],
					'access_level_rw'	=> $t_def['access_level_rw'],
					'length_min'		=> $t_def['length_min'],
					'length_max'		=> $t_def['length_max'],
					'advanced'			=> $t_def['advanced'],
					'display_report'	=> $t_def['display_report'],
					'display_update'	=> $t_def['display_update'],
					'display_resolved'	=> $t_def['display_resolved'],
					'display_closed'	=> $t_def['display_closed'],
					'require_report'	=> $t_def['require_report'],
					'require_update'	=> $t_def['require_update'],
					'require_resolved'	=> $t_def['require_resolved'],
					'require_closed'	=> $t_def['require_closed'],
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
		if ( $t_user_id === false ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}

		# Check if project documentation feature is enabled.
		if ( OFF == config_get( 'enable_project_documentation' ) || !file_is_uploading_enabled() ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}
	
		if ( !project_exists( $p_project_id ) ) {
			return new soap_fault( 'Client', '', "Project '$p_project_id' does not exist." );
		}

		if ( !mci_has_readonly_access( $t_user_id, $p_project_id ) ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}

		$t_project_file_table = config_get( 'mantis_project_file_table' );
		$t_project_table = config_get( 'mantis_project_table' );
		$t_project_user_list_table = config_get( 'mantis_project_user_list_table' );
		$t_user_table = config_get( 'mantis_user_table' );
		$t_pub = VS_PUBLIC;
		$t_priv = VS_PRIVATE;
		$t_admin = ADMINISTRATOR;
	
		if ( $p_project_id == ALL_PROJECTS ) {
			# Select all the projects that the user has access to
			$t_projects = user_get_accessible_projects( $t_user_id );
		} else {
			# Select the specific project 
			$t_projects = array( $p_project_id );
		}
			
		$t_projects[] = ALL_PROJECTS; # add ALL_PROJECTS to the list of projects to fetch
		
		$t_reqd_access = config_get( 'view_proj_doc_threshold' );
		if ( is_array( $t_reqd_access ) ) {
			if ( 1 == count( $t_reqd_access ) ) {
				$t_access_clause = "= " . array_shift( $t_reqd_access ) . " ";
			} else {
				$t_access_clause = "IN (" . implode( ',', $t_reqd_access ) . ")";
			}
		} else {
			$t_access_clause = ">= $t_reqd_access ";
		}			
	
		$query = "SELECT pft.id, pft.project_id, pft.filename, pft.file_type, pft.filesize, pft.title, pft.description, pft.date_added
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
		for ($i=0;$i<$num_files;$i++) {
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, 'v' );
			$t_attachment = array();
			$t_attachment['id'] = $v_id;
			$t_attachment['filename'] = $v_filename;
			$t_attachment['title'] = $v_title;
			$t_attachment['description'] = $v_description;
			$t_attachment['size'] = $v_filesize;
			$t_attachment['content_type'] = $v_file_type;
			$t_attachment['date_submitted'] = timestamp_to_iso8601( db_unixtimestamp( $v_date_added ) );
			$t_attachment['download_url'] = mci_get_mantis_path() . 'file_download.php?file_id=' . $v_id . '&amp;type=doc';
			$t_result[] = $t_attachment;
		}

		return $t_result;
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
		if ( $t_user_id === false ) {
			return new soap_fault( 'Client', '', 'Access Denied', 'Username/password combination was incorrect');
		}

		if ( !mci_has_administrator_access( $t_user_id ) ) {
			return new soap_fault( 'Client', '', 'Access Denied', 'User does not have administrator access');
		}

		extract( $p_project, EXTR_PREFIX_ALL, 'v');

	/*	if ( is_blank($v_name) )
			return new soap_fault('Client', '', 'Mandatory field "name" was missing');
	*/	
		// check to make sure project doesn't already exist
		if ( !project_is_name_unique( $v_name ) ) {
			return new soap_fault( 'Client', '', 'Project name exists', 
					'The project name you attempted to add exists already');
		}

		if ( is_null( $v_status ) ) {
			$v_status = array( 'name' => 'development' ); // development
		}

		if ( is_null( $v_view_state ) ) {
			$v_view_state = array( 'id' => VS_PUBLIC );
		}

		if ( is_null( $v_enabled ) ) {
			$v_enabled = true;
		}

		$t_project_status = mci_get_project_status_id( $v_status );
		$t_project_view_state = mci_get_project_view_state_id( $v_view_state );

		// project_create returns the new project's id, spit that out to webservice caller
		return project_create($v_name, $v_description, $t_project_status, $t_project_view_state, $v_file_path, $v_enabled);
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
		if ( $t_user_id === false ) {
			return new soap_fault( 'Client', '', 'Access Denied', 'Username/password combination was incorrect');
		}

		if ( !project_exists( $p_project_id ) ) {
			return new soap_fault( 'Client', '', "Project '$p_project_id' does not exist." );
		}

		if ( !mci_has_administrator_access( $t_user_id, $p_project_id ) ) {
			return new soap_fault( 'Client', '', 'Access Denied', 'User does not have administrator access');
		}

		return project_delete( $p_project_id );
	}
	
	function mc_project_get_issue_headers( $p_username, $p_password, $p_project_id , $p_page_number, $p_per_page ) {
        $t_user_id = mci_check_login( $p_username, $p_password );
        if ( $t_user_id === false ) {
            return new soap_fault( 'Client', '', 'Access Denied' );
        }
		if ( !project_exists( $p_project_id ) ) {
			return new soap_fault( 'Client', '', "Project '$p_project_id' does not exist." );
		}        
        
        if ( !mci_has_readonly_access( $t_user_id, $p_project_id ) ) {
            return new soap_fault( 'Client', '', 'Access Denied' );
        }

        $t_page_count = 0;
        $t_bug_count = 0;	

        
        $t_rows = filter_get_bug_rows( $p_page_number, $p_per_page, $t_page_count, $t_bug_count, null, $p_project_id );
        $t_result = array();

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

		if ( $t_user_id === false ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}
		
		$t_users = array();

		$t_users = project_get_all_user_rows( $p_project_id, $p_access ); # handles ALL_PROJECTS case
		
		$t_display = array();
		$t_sort = array();
		$t_show_realname = ( ON == config_get( 'show_realname' ) );
		$t_sort_by_last_name = ( ON == config_get( 'sort_by_last_name' ) );
		foreach ( $t_users as $t_user ) {
			$t_user_name = string_attribute( $t_user['username'] );
			$t_sort_name = strtolower( $t_user_name );
			if ( $t_show_realname && ( $t_user['realname'] <> "" ) ){
				$t_user_name = string_attribute( $t_user['realname'] );
				if ( $t_sort_by_last_name ) {
					$t_sort_name_bits = split( ' ', strtolower( $t_user_name ), 2 );
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
		for ($i = 0; $i < count( $t_sort ); $i++ ) {
			$t_row = $t_users[$i];
			// This is not very performant - But we have to assure that the data returned is exactly
			// the same as the data that comes with an issue (test for equality - $t_row[] does not
			// contain email fields).
            $t_result[] = mci_account_get_array_by_id( $t_row['id'] );
		}
		return $t_result;
	}
?>
