<?php
	# MantisConnect - A webservice interface to Mantis Bug Tracker
	# Copyright (C) 2004-2007  Victor Boctor - vboctor@users.sourceforge.net
	# This program is distributed under dual licensing.  These include
	# GPL and a commercial licenses.  Victor Boctor reserves the right to
	# change the license of future releases.
	# See docs/ folder for more details

	# --------------------------------------------------------
	# $Id: mc_issue_api.php,v 1.1 2007-07-18 06:52:55 vboctor Exp $
	# --------------------------------------------------------

	require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'mc_core.php' );

	/**
	 * Check if an issue with the given id exists.
	 *
	 * @param string $p_username  The name of the user trying to access the issue.
	 * @param string $p_password  The password of the user.
	 * @param integer $p_issue_id  The id of the issue to check.
	 * @return boolean  true if there is an issue with the given id, false otherwise.
	 */
	function mc_issue_exists( $p_username, $p_password, $p_issue_id ) {
		$t_user_id = mci_check_login( $p_username, $p_password );
		if ( $t_user_id === false ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}

		if ( !bug_exists( $p_issue_id ) ) {
			return false;
		}

		$t_project_id = bug_get_field( $p_issue_id, 'project_id' );
		if ( !mci_has_readonly_access( $t_user_id, $t_project_id ) ) {
			// if we return an error here, then we answered the question!
			return false;
		}

		return true;
	}

	/**
	 * Get all details about an issue.
	 *
	 * @param string $p_username  The name of the user trying to access the issue.
	 * @param string $p_password  The password of the user.
	 * @param integer $p_issue_id  The id of the issue to retrieve.
	 * @return Array that represents an IssueData structure
	 */
	function mc_issue_get( $p_username, $p_password, $p_issue_id ) {
		$t_user_id = mci_check_login( $p_username, $p_password );
		$t_lang = mci_get_user_lang( $t_user_id );
		if ( $t_user_id === false ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}

		if ( !bug_exists( $p_issue_id ) ) {
			return new soap_fault( 'Server', '', 'Issue does not exist' );
		}

		$t_project_id = bug_get_field( $p_issue_id, 'project_id' );
		if ( !mci_has_readonly_access( $t_user_id, $t_project_id ) ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}

		$t_bug = get_object_vars( bug_get( $p_issue_id, true ) );
		$t_issue_data = array();

		$t_issue_data['id'] = $p_issue_id;
 		$t_issue_data['view_state'] = mci_enum_get_array_by_id( $t_bug['view_state'], 'view_state', $t_lang );
 		$t_issue_data['last_updated'] = timestamp_to_iso8601( $t_bug['last_updated'] );

		$t_issue_data['project'] = mci_project_as_array_by_id( $t_bug['project_id'] );
		$t_issue_data['category'] = mci_null_if_empty( $t_bug['category'] );
 		$t_issue_data['priority'] = mci_enum_get_array_by_id( $t_bug['priority'], 'priority', $t_lang );
 		$t_issue_data['severity'] = mci_enum_get_array_by_id( $t_bug['severity'], 'severity', $t_lang );
 		$t_issue_data['status'] = mci_enum_get_array_by_id( $t_bug['status'], 'status', $t_lang );

 		$t_issue_data['reporter'] = mci_account_get_array_by_id( $t_bug['reporter_id'] );
		$t_issue_data['summary'] = $t_bug['summary'];
		$t_issue_data['version'] = mci_null_if_empty( $t_bug['version'] );
		$t_issue_data['build'] = mci_null_if_empty( $t_bug['build'] );
		$t_issue_data['platform'] = mci_null_if_empty( $t_bug['platform'] );
		$t_issue_data['os'] = mci_null_if_empty( $t_bug['os'] );
		$t_issue_data['os_build'] = mci_null_if_empty( $t_bug['os_build'] );
 		$t_issue_data['reproducibility'] = mci_enum_get_array_by_id( $t_bug['reproducibility'], 'reproducibility', $t_lang );
 		$t_issue_data['date_submitted'] = timestamp_to_iso8601( $t_bug['date_submitted'] );

		$t_issue_data['sponsorship_total'] = $t_bug['sponsorship_total'];

 		if( !empty( $t_bug['handler_id'] ) ) {
 			$t_issue_data['handler'] = mci_account_get_array_by_id( $t_bug['handler_id'] );
 		}
 		$t_issue_data['projection'] = mci_enum_get_array_by_id( $t_bug['projection'], 'projection', $t_lang );
 		$t_issue_data['eta'] = mci_enum_get_array_by_id( $t_bug['eta'], 'eta', $t_lang );

 		$t_issue_data['resolution'] = mci_enum_get_array_by_id( $t_bug['resolution'], 'resolution', $t_lang );
		$t_issue_data['fixed_in_version'] = mci_null_if_empty( $t_bug['fixed_in_version'] );

		$t_issue_data['description'] = $t_bug['description'];
		$t_issue_data['steps_to_reproduce'] = mci_null_if_empty( $t_bug['steps_to_reproduce'] );
		$t_issue_data['additional_information'] = mci_null_if_empty( $t_bug['additional_information'] );

		$t_issue_data['attachments'] = mci_issue_get_attachments( $p_issue_id );
		$t_issue_data['relationships'] = mci_issue_get_relationships( $p_issue_id, $t_user_id );
		$t_issue_data['notes'] = mci_issue_get_notes( $p_issue_id );
		$t_issue_data['custom_fields'] = mci_issue_get_custom_fields( $p_issue_id );

		return $t_issue_data;
	}

	/**
	 * Sets the supplied array of custom field values to the specified issue id.
	 *
	 * @param $p_issue_id   Issue id to apply custom field values to.
	 * @param $p_custom_fields  The array of custom field values as described in the webservice complex types.
	 */
	function mci_issue_set_custom_fields( $p_issue_id, &$p_custom_fields ) {
		# set custom field values on the submitted issue
		if ( isset( $p_custom_fields ) && is_array( $p_custom_fields ) ) {
			foreach( $p_custom_fields as $t_custom_field ) {
				# get custom field id from object ref
				$t_custom_field_id = mci_get_custom_field_id_from_objectref( $t_custom_field['field'] );

				if ( $t_custom_field_id == 0 ) {
					return new soap_fault( 'Client', '', 'Custom field ' . $t_custom_field['field']['name'] . ' not found' );
				}

				# skip if current user doesn't have login access.
				if ( !custom_field_has_write_access( $t_custom_field_id, $p_issue_id ) ) {
					continue;
				}

				$t_value = $t_custom_field['value'];

				if ( !custom_field_validate( $t_custom_field_id, $t_value ) ) {
					return new soap_fault( 'Client', '', 'Invalid custom field value for field id ' . $t_custom_field_id );
				}

				if ( !custom_field_set_value( $t_custom_field_id, $p_issue_id, $t_value ) ) {
					return new soap_fault( 'Server', '', 'Unable to set custom field value for field id ' . $t_custom_field_id . ' to issue ' . $p_issue_id );
				}
			}
		}
	}

	/**
	 * Get the custom field values associated with the specified issue id.
	 *
	 * @param $p_issue_id   Issue id to get the custom field values for.
	 *
	 * @return null if no custom field defined for the project that contains the issue, or if no custom
	 *              fields are accessible to the current user.
	 */
	function mci_issue_get_custom_fields( $p_issue_id ) {
		$t_project_id = bug_get_field( $p_issue_id, 'project_id' );

		$t_custom_fields = array();
		$t_related_custom_field_ids = custom_field_get_linked_ids( $t_project_id );

		foreach( $t_related_custom_field_ids as $t_id ) {
			$t_def = custom_field_get_definition( $t_id );

			if ( custom_field_has_read_access( $t_id, $p_issue_id ) ) {
				# user has not access to read this custom field.
				$t_value = custom_field_get_value( $t_id, $p_issue_id );
				if ( $t_value === false ) {
					continue;
				}

				$t_custom_field_value = array();
				$t_custom_field_value['field'] = array();
				$t_custom_field_value['field']['id'] = $t_id;
				$t_custom_field_value['field']['name'] = $t_def['name'];
				$t_custom_field_value['value'] = $t_value;

				$t_custom_fields[] = $t_custom_field_value;
			}
		} # foreach

		return ( sizeof( $t_custom_fields ) == 0 ? null : $t_custom_fields );
	}

	/**
	 * Get the attachments of an issue.
	 *
	 * @param integer $p_issue_id  The id of the issue to retrieve the attachments for
	 * @return Array that represents an AttachmentData structure
	 */
	function mci_issue_get_attachments( $p_issue_id ) {
		$t_attachment_rows = bug_get_attachments( $p_issue_id );
		$t_result = array();

		foreach( $t_attachment_rows as $t_attachment_row ) {
			$t_attachment = array();
			$t_attachment['id'] = $t_attachment_row['id'];
			$t_attachment['filename'] = $t_attachment_row['filename'];
			$t_attachment['size'] = $t_attachment_row['filesize'];
			$t_attachment['content_type'] = $t_attachment_row['file_type'];
			$t_attachment['date_submitted'] = timestamp_to_iso8601( db_unixtimestamp( $t_attachment_row['date_added'] ) );
			$t_attachment['download_url'] = mci_get_mantis_path() . 'file_download.php?file_id=' . $t_attachment_row['id'] . '&amp;type=bug';
			$t_result[] = $t_attachment;
		}

		return $t_result;
	}

	/**
	 * Get the relationships of an issue.
	 *
	 * @param integer $p_issue_id  The id of the issue to retrieve the relationships for
	 * @return Array that represents an RelationShipData structure
	 */
	function mci_issue_get_relationships( $p_issue_id, $p_user_id ) {
		$t_relationships = array();

		$t_src_relationships = relationship_get_all_src( $p_issue_id );
		foreach( $t_src_relationships as $t_relship_row) {
			if ( access_has_bug_level( config_get( 'mc_readonly_access_level_threshold' ), $t_relship_row->dest_bug_id, $p_user_id ) ) {
				$t_relationship = array();
				$t_reltype = array();
				$t_relationship['id'] = $t_relship_row->id;
				$t_reltype['id'] = $t_relship_row->type;
				$t_reltype['name'] = relationship_get_description_src_side($t_relship_row->type);
				$t_relationship['type'] = $t_reltype;
				$t_relationship['target_id'] = $t_relship_row->dest_bug_id;
				$t_relationships[] = $t_relationship;
			}
		}

		$t_dest_relationships = relationship_get_all_dest( $p_issue_id );
		foreach( $t_dest_relationships as $t_relship_row) {
			if ( access_has_bug_level( config_get( 'mc_readonly_access_level_threshold' ), $t_relship_row->src_bug_id, $p_user_id ) ) {
				$t_relationship = array();
				$t_relationship['id'] = $t_relship_row->id;
				$t_reltype = array();
				$t_reltype['id'] = $t_relship_row->type;
				$t_reltype['name'] = relationship_get_description_dest_side($t_relship_row->type);
				$t_relationship['type'] = $t_reltype;
				$t_relationship['target_id'] = $t_relship_row->src_bug_id;
				$t_relationships[] = $t_relationship;
			}
		}

		return $t_relationships;
	}

	/**
	 * Get all visible notes for a specific issue
	 *
	 * @param integer $p_issue_id  The id of the issue to retrieve the notes for
	 * @return Array that represents an IssueNoteData structure
	 */
	function mci_issue_get_notes( $p_issue_id ) {
		$t_user_id = auth_get_current_user_id();
		$t_lang = mci_get_user_lang( $t_user_id );
		$t_project_id = bug_get_field( $p_issue_id, 'project_id' );
		$t_user_access_level = user_get_access_level( $t_user_id, $t_project_id );
		$t_user_bugnote_order = 'ASC'; // always get the notes in ascending order for consistency to the calling application.

		$t_result = array();
		foreach( bugnote_get_all_visible_bugnotes( $p_issue_id, $t_user_access_level, $t_user_bugnote_order, 0 ) as $t_value ) {
			$t_bugnote = array();
			$t_bugnote['id'] = $t_value->id;
			$t_bugnote['reporter'] = mci_account_get_array_by_id( $t_value->reporter_id );
			$t_bugnote['date_submitted'] = timestamp_to_iso8601( $t_value->date_submitted );
			$t_bugnote['last_modified'] = timestamp_to_iso8601( $t_value->last_modified );
			$t_bugnote['text'] = $t_value->note;
 			$t_bugnote['view_state'] = mci_enum_get_array_by_id( $t_value->view_state, 'view_state', $t_lang );
			$t_result[] = $t_bugnote;
		}

		return $t_result;
	}

	/**
	 * Get the biggest issue id currently used.
	 *
	 * @param string $p_username  The name of the user trying to retrieve the information
	 * @param string $p_password  The password of the user.
	 * @param int    $p_project_id	-1 default project, 0 for all projects, otherwise project id.
	 * @return integer  The biggest used issue id.
	 */
	function mc_issue_get_biggest_id( $p_username, $p_password, $p_project_id ) {
		$t_user_id = mci_check_login( $p_username, $p_password );
		if ( $t_user_id === false ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}

		$t_any = defined( 'META_FILTER_ANY' ) ? META_FILTER_ANY : 'any';
		$t_none = defined( 'META_FILTER_NONE' ) ? META_FILTER_NONE : 'none';

		$t_filter = array(
			'show_category'		=> Array ( '0' => $t_any ),
			'show_severity'		=> Array ( '0' => $t_any ),
			'show_status'		=> Array ( '0' => $t_any ),
			'highlight_changed'	=> 0,
			'reporter_id'		=> Array ( '0' => $t_any ),
			'handler_id'		=> Array ( '0' => $t_any ),
			'show_resolution'	=> Array ( '0' => $t_any ),
			'show_build'		=> Array ( '0' => $t_any ),
			'show_version'		=> Array ( '0' => $t_any ),
			'hide_status'		=> Array ( '0' => $t_none ),
			'user_monitor'		=> Array ( '0' => $t_any ),
			'dir'				=> 'DESC',
			'sort'				=> 'date_submitted'
		);

		$t_page_number = 1;
		$t_per_page = 1;
		$t_bug_count = 0;

		# Get project id, if -1, then retrieve the current which will be the default since there is no cookie.
		$t_project_id = $p_project_id;
		if ( $t_project_id == -1 ) {
			$t_project_id = helper_get_current_project();
		}

		if ( ( $t_project_id > 0 ) && !project_exists( $t_project_id ) ) {
			return new soap_fault( 'Client', '', "Project '$t_project_id' does not exist." );
		}

		if ( !mci_has_readonly_access( $t_user_id, $t_project_id ) ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}

		$t_rows = filter_get_bug_rows( $t_page_number, $t_per_page, $t_page_count, $t_bug_count, $t_filter, $t_project_id, $t_user_id );
		if ( count( $t_rows ) == 0 ) {
			return 0;
		} else {
			return $t_rows[0]['id'];
		}
	}

	/**
	 * Get the id of an issue via the issue's summary.
	 *
	 * @param string $p_username  The name of the user trying to delete the issue.
	 * @param string $p_password  The password of the user.
	 * @param string $p_summary  The summary of the issue to retrieve.
	 * @return integer  The id of the issue with the given summary, 0 if there is no such issue.
	 */
	function mc_issue_get_id_from_summary( $p_username, $p_password, $p_summary ) {
		$t_user_id = mci_check_login( $p_username, $p_password );
		if ( $t_user_id === false ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}

		$t_bug_table = config_get( 'mantis_bug_table' );

		$c_summary = db_prepare_string( $p_summary );

		$query = "SELECT id
				  FROM $t_bug_table
				  WHERE summary = '$c_summary'";

		$result = db_query( $query, 1 );

		if ( db_num_rows( $result ) == 0 ) {
			return 0;
		} else {
			while ( ( $row = db_fetch_array( $result ) ) !== false ) {
				$t_issue_id = (int)$row['id'];
				$t_project_id = bug_get_field( $t_issue_id, 'project_id' );

				if ( mci_has_readonly_access( $t_user_id, $t_project_id ) ) {
					return $t_issue_id;
				}
			}

			// no issue found that belongs to a project that the user has read access to.
			return 0;
		}
	}

	/**
	 * Add an issue to the database.
	 *
	 * @param string $p_username  The name of the user trying to add the issue.
	 * @param string $p_password  The password of the user.
	 * @param Array $p_issue  A IssueData structure containing information about the new issue.
	 * @return integer  The id of the created issue.
	 */
	function mc_issue_add( $p_username, $p_password, $p_issue ) {
		$t_user_id = mci_check_login( $p_username, $p_password );
		if ( $t_user_id === false ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}

		extract( $p_issue, EXTR_PREFIX_ALL, 'v' );

		$t_project_id = mci_get_project_id( $v_project );

		if ( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}

		$t_handler_id = mci_get_user_id( $v_handler );
		$t_priority_id = mci_get_priority_id( $v_priority );
		$t_severity_id = mci_get_severity_id( $v_severity );
		$t_status_id = mci_get_status_id( $v_status );
		$t_reproducibility_id = mci_get_reproducibility_id( $v_reproducibility );
		$t_resolution_id = mci_get_resolution_id( $v_resolution );
		$t_projection_id = mci_get_projection_id( $v_projection );
		$t_eta_id = mci_get_eta_id( $v_eta );
		$t_view_state_id = mci_get_view_state_id( $v_view_state );

		$t_reporter_id = mci_get_user_id( $v_reporter );
		if ( $t_reporter_id == 0 ) {
			$t_reporter_id = $t_user_id;
		} else {
			if ( $t_reporter_id != $t_user_id ) {
				# Make sure that active user has access level required to specify a different reporter.
				$t_specify_reporter_access_level = config_get( 'mc_specify_reporter_on_add_access_level_threshold' );
				if ( !access_has_project_level( $t_specify_reporter_access_level, $t_project_id, $t_user_id ) ) {
					return new soap_fault( 'Client', '', "Active user does not have access level required to specify a different issue reporter." );
				}
			}
		}

		if ( ( $t_project_id == 0 ) || !project_exists( $t_project_id ) ) {
			if ( $t_project_id == 0 ) {
				return new soap_fault( 'Client', '', "Project '" . $v_project['name'] . "' does not exist." );
			} else {
				return new soap_fault( 'Client', '', "Project '$t_project_id' does not exist." );
			}
		}

		if ( !access_has_project_level( config_get( 'report_bug_threshold' ), $t_project_id, $t_user_id ) ) {
			return new soap_fault( 'Client', '', "User '$t_user_id' does not have access right to report issues." );
		}

		#if ( !access_has_project_level( config_get( 'report_bug_threshold' ), $t_project_id ) ||
		#	!access_has_project_level( config_get( 'report_bug_threshold' ), $t_project_id, $v_reporter ) ) {
		#	return new soap_fault( 'Client', '', "User does not have access right to report issues." );
		#}

		if ( ( $t_handler_id != 0 ) && !user_exists( $t_handler_id ) ) {
			return new soap_fault( 'Client', '', "User '$t_handler_id' does not exist." );
		}

		if ( !in_array( $v_category, mci_category_get_all_rows( $t_project_id, $t_user_id ) ) ) {
			$t_error_when_category_not_found = config_get( 'mc_error_when_category_not_found' );
			if ( $t_error_when_category_not_found == ON ) {
				if ( is_blank( $v_category ) && ( count( category_get_all_rows( $t_project_id ) ) == 0 ) ) {
					$v_category = '';	// it is ok to have category as empty if project has no categories
				} else {
					return new soap_fault( 'Client', '', "Category '$v_category' does not exist in project '$t_project_id'." );
				}
			} else {
				$t_category_when_not_found = config_get( 'mc_category_when_not_found' );
				$v_category = $t_category_when_not_found;
			}
		}

		if ( isset( $v_version ) && !is_blank( $v_version ) && !version_get_id( $v_version, $t_project_id ) ) {
			$t_error_when_version_not_found = config_get( 'mc_error_when_version_not_found' );
			if ( $t_error_when_version_not_found == ON ) {
				$t_project_name = project_get_name( $t_project_id );
				return new soap_fault( 'Client', '', "Version '$v_version' does not exist in project '$t_project_name'." );
			} else {
				$t_version_when_not_found = config_get( 'mc_version_when_not_found' );
				$v_version = $t_version_when_not_found;
			}
		}

		if ( is_blank( $v_summary ) ) {
			return new soap_fault( 'Client', '', "Mandatory field 'summary' is missing." );
		}

		if ( is_blank( $v_description ) ) {
			return new soap_fault( 'Client', '', "Mandatory field 'description' is missing." );
		}

		if ( $v_priority == 0 ) {
			$v_priority = config_get( 'default_bug_priority' );
		}

		if ( $v_severity == 0 ) {
			$v_severity = config_get( 'default_bug_severity' );
		}

		if ( $v_view_state == 0 ) {
			$v_view_state = config_get( 'default_bug_view_status' );
		}

		if ( $v_reproducibility == 0 ) {
			$v_reproducibility = 10;
		}

		$t_bug_data = new BugData;
		$t_bug_data->project_id = $t_project_id;
		$t_bug_data->reporter_id = $t_reporter_id;
		$t_bug_data->handler_id = $t_handler_id;
		$t_bug_data->priority = $t_priority_id;
		$t_bug_data->severity = $t_severity_id;
		$t_bug_data->reproducibility = $t_reproducibility_id;
		$t_bug_data->status = $t_status_id;
		$t_bug_data->resolution = $t_resolution_id;
		$t_bug_data->projection = $t_projection_id;
		$t_bug_data->category = $v_category;
		$t_bug_data->date_submitted = isset( $v_date_submitted ) ? $v_date_submitted : '';
		$t_bug_data->last_updated = isset( $v_last_updated ) ? $v_last_updated : '';
		$t_bug_data->eta = $t_eta_id;
		$t_bug_data->os = isset( $v_os ) ? $v_os : '';
		$t_bug_data->os_build = isset( $v_os_build ) ? $v_os_build : '';
		$t_bug_data->platform = isset( $v_platform ) ? $v_platform : '';
		$t_bug_data->version = isset( $v_version ) ? $v_version : '';
		$t_bug_data->fixed_in_version = isset( $v_fixed_in_version ) ? $v_fixed_in_version : '';
		$t_bug_data->build = isset( $v_build ) ? $v_build : '';
		$t_bug_data->view_state = $t_view_state_id;
		$t_bug_data->summary = $v_summary;
		$t_bug_data->sponsorship_total = isset( $v_sponsorship_total ) ? $v_sponsorship_total : 0;

		# omitted:
		# var $bug_text_id
		# $t_bug_data->profile_id;

		# extended info
		$t_bug_data->description = $v_description;
		$t_bug_data->steps_to_reproduce = isset( $v_steps_to_reproduce ) ? $v_steps_to_reproduce : '';
		$t_bug_data->additional_information = isset( $v_additional_information ) ? $v_additional_information : '';

		# submit the issue
		$t_issue_id = bug_create( $t_bug_data );

		mci_issue_set_custom_fields( $t_issue_id, $v_custom_fields );

		if ( isset( $v_notes ) && is_array( $v_notes ) ) {
			foreach( $v_notes as $t_note ) {
		 		if ( isset( $t_note['view_state'] ) ) {
		 			$t_view_state = $t_note['view_state'];
		 		} else {
			 		$t_view_state = config_get( 'default_bugnote_view_status' );
			 	}

				$t_view_state_id = mci_get_enum_id_from_objectref( 'view_state', $t_view_state );
				bugnote_add( $t_issue_id, $t_note['text'], '0:00', $t_view_state_id == VS_PRIVATE, BUGNOTE, '', $t_user_id, FALSE );
			}
		}

		email_new_bug( $t_issue_id );

		return $t_issue_id;
	}

    /**
	 * Update Issue in database
	 *
	 * Created By KGB
	 * @param string $p_username The name of the user trying to add the issue.
	 * @param string $p_password The password of the user.
	 * @param Array $p_issue A IssueData structure containing information about the new issue.
	 * @return integer The id of the created issue.
	 */
    function mc_issue_update( $p_username, $p_password, $p_issue_id, $p_issue ) {
        $t_user_id = mci_check_login( $p_username, $p_password );
        if ( $t_user_id === false ) {
            return new soap_fault( 'Client', '', 'Access Denied' );
        }

		if ( !bug_exists( $p_issue_id ) ) {
			return new soap_fault( 'Server', '', "Issue '$p_issue_id' does not exist." );
		}

		$t_project_id = bug_get_field( $p_issue_id, 'project_id' );
        if ( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
            return new soap_fault( 'Client', '', 'Access Denied' );
        }

        extract( $p_issue, EXTR_PREFIX_ALL, 'v' );

        $t_project_id = mci_get_project_id( $v_project );
        $t_handler_id = mci_get_user_id( $v_handler );
        $t_priority_id = mci_get_priority_id( $v_priority );
        $t_severity_id = mci_get_severity_id( $v_severity );
        $t_status_id = mci_get_status_id( $v_status );
        $t_reproducibility_id = mci_get_reproducibility_id( $v_reproducibility );
        $t_resolution_id = mci_get_resolution_id( $v_resolution );
        $t_projection_id = mci_get_projection_id( $v_projection );
        $t_eta_id = mci_get_eta_id( $v_eta );
        $t_view_state_id = mci_get_view_state_id( $v_view_state );

        $t_reporter_id = mci_get_user_id( $v_reporter );
        if ( $t_reporter_id == 0 ) {
            $t_reporter_id = $t_user_id;
        }

        if ( ( $t_project_id == 0 ) || !project_exists( $t_project_id ) ) {
            if ( $t_project_id == 0 ) {
                return new soap_fault( 'Client', '', "Project '" . $v_project['name'] . "' does not exist." );
            } else {
                return new soap_fault( 'Client', '', "Project '$t_project_id' does not exist." );
            }
        }

        if ( !access_has_bug_level( config_get( 'update_bug_threshold' ), $p_issue_id, $t_user_id ) ) {
            return new soap_fault( 'Client', '', "User '$t_user_id' does not have access right to report issues." );
        }

        if ( ( $t_handler_id != 0 ) && !user_exists( $t_handler_id ) ) {
            return new soap_fault( 'Client', '', "User '$t_handler_id' does not exist." );
        }

        if ( !in_array( $v_category, mci_category_get_all_rows( $t_project_id, $t_user_id ) ) ) {
            $t_error_when_category_not_found = config_get( 'mc_error_when_category_not_found' );
            if ( $t_error_when_category_not_found == ON ) {
                if ( is_blank( $v_category ) && ( count( category_get_all_rows( $t_project_id ) ) == 0 ) ) {
                    $v_category = ''; // it is ok to have category as empty if project has no categories
                } else {
                    return new soap_fault( 'Client', '', "Category '$v_category' does not exist in project '$t_project_id'." );
                }
            } else {
                $t_category_when_not_found = config_get( 'mc_category_when_not_found' );
                $v_category = $t_category_when_not_found;
            }
        }

        if ( isset( $v_version ) && !is_blank( $v_version ) && !version_get_id( $v_version, $t_project_id ) ) {
            $t_error_when_version_not_found = config_get( 'mc_error_when_version_not_found' );
            if ( $t_error_when_version_not_found == ON ) {
                $t_project_name = project_get_name( $t_project_id );
                return new soap_fault( 'Client', '', "Version '$v_version' does not exist in project '$t_project_name'." );
            } else {
                $t_version_when_not_found = config_get( 'mc_version_when_not_found' );
                $v_version = $t_version_when_not_found;
            }
        }

        if ( is_blank( $v_summary ) ) {
            return new soap_fault( 'Client', '', "Mandatory field 'summary' is missing." );
        }

        if ( is_blank( $v_description ) ) {
            return new soap_fault( 'Client', '', "Mandatory field 'description' is missing." );
        }

        if ( $v_priority == 0 ) {
            $v_priority = config_get( 'default_bug_priority' );
        }

        if ( $v_severity == 0 ) {
            $v_severity = config_get( 'default_bug_severity' );
        }

        if ( $v_view_state == 0 ) {
            $v_view_state = config_get( 'default_bug_view_status' );
        }

        if ( $v_reproducibility == 0 ) {
            $v_reproducibility = 10;
        }

        $t_bug_data = new BugData;
        $t_bug_data->project_id = $t_project_id;
        $t_bug_data->reporter_id = $t_reporter_id;
        $t_bug_data->handler_id = $t_handler_id;
        $t_bug_data->priority = $t_priority_id;
        $t_bug_data->severity = $t_severity_id;
        $t_bug_data->reproducibility = $t_reproducibility_id;
        $t_bug_data->status = $t_status_id;
        $t_bug_data->resolution = $t_resolution_id;
        $t_bug_data->projection = $t_projection_id;
        $t_bug_data->category = $v_category;
        $t_bug_data->date_submitted = isset( $v_date_submitted ) ? $v_date_submitted : '';
        $t_bug_data->last_updated = isset( $v_last_updated ) ? $v_last_updated : '';
        $t_bug_data->eta = $t_eta_id;
        $t_bug_data->os = isset( $v_os ) ? $v_os : '';
        $t_bug_data->os_build = isset( $v_os_build ) ? $v_os_build : '';
        $t_bug_data->platform = isset( $v_platform ) ? $v_platform : '';
        $t_bug_data->version = isset( $v_version ) ? $v_version : '';
        $t_bug_data->fixed_in_version = isset( $v_fixed_in_version ) ? $v_fixed_in_version : '';
        $t_bug_data->build = isset( $v_build ) ? $v_build : '';
        $t_bug_data->view_state = $t_view_state_id;
        $t_bug_data->summary = $v_summary;
        $t_bug_data->sponsorship_total = isset( $v_sponsorship_total ) ? $v_sponsorship_total : 0;

        # omitted:
        # var $bug_text_id
        # $t_bug_data->profile_id;

        # extended info
        $t_bug_data->description = $v_description;
        $t_bug_data->steps_to_reproduce = isset( $v_steps_to_reproduce ) ? $v_steps_to_reproduce : '';
        $t_bug_data->additional_information = isset( $v_additional_information ) ? $v_additional_information : '';

        # submit the issue
        $t_is_success = bug_update($p_issue_id,$t_bug_data,true,false );

        mci_issue_set_custom_fields( $p_issue_id, $v_custom_fields );

        if ( isset( $v_notes ) && is_array( $v_notes ) ) {
            foreach( $v_notes as $t_note ) {
                 if ( isset( $t_note['view_state'] ) ) {
                     $t_view_state = $t_note['view_state'];
                 } else {
                     $t_view_state = config_get( 'default_bugnote_view_status' );
                 }

				// TODO: consider supporting updating of bugnotes and detecting the ones that haven't changed.
                $t_view_state_id = mci_get_enum_id_from_objectref( 'view_state', $t_view_state );
                bugnote_add( $p_issue_id, $t_note['text'], '0:00', $t_view_state_id == VS_PRIVATE, BUGNOTE, '', $t_user_id, FALSE );
            }
        }

        return $t_is_success;
    }

	/**
	 * Delete the specified issue.
	 *
	 * @param string $p_username  The name of the user trying to delete the issue.
	 * @param string $p_password  The password of the user.
	 * @param integer $p_issue_id  The id of the issue to delete.
	 * @return boolean  True if the issue has been deleted successfully, false otherwise.
	 */
	function mc_issue_delete( $p_username, $p_password, $p_issue_id ) {
		$t_user_id = mci_check_login( $p_username, $p_password );
		if ( $t_user_id === false ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}

		if ( !bug_exists( $p_issue_id ) ) {
			return new soap_fault( 'Server', '', "Issue '$p_issue_id' does not exist." );
		}

		$t_project_id = bug_get_field( $p_issue_id, 'project_id' );
		if ( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}

		return bug_delete( $p_issue_id );
	}

	/**
	 * Add a note to an existing issue.
	 *
	 * @param string $p_username  The name of the user trying to add a note to an issue.
	 * @param string $p_password  The password of the user.
	 * @param integer $p_issue_id  The id of the issue to add the note to.
	 * @param IssueNoteData $p_note  The note to add.
	 * @return integer The id of the added note.
	 */
 	function mc_issue_note_add( $p_username, $p_password, $p_issue_id, $p_note ) {
 		$t_user_id = mci_check_login( $p_username, $p_password );
 		if ( $t_user_id === false ) {
 			return new soap_fault( 'Client', '', 'Access Denied' );
 		}

 		if ( (integer)$p_issue_id < 1 ) {
 			return new soap_fault( 'Client', '', "Invalid issue id '$p_issue_id'." );
 		}

 		if ( !bug_exists( $p_issue_id ) ) {
 			return new soap_fault( 'Client', '', "Issue '$p_issue_id' does not exist" );
 		}

		$t_project_id = bug_get_field( $p_issue_id, 'project_id' );
 		if ( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
 			return new soap_fault( 'Client', '', 'Access Denied' );
 		}

 		if ( !access_has_bug_level( config_get( 'add_bugnote_threshold' ), $p_issue_id, $t_user_id ) ) {
 			return new soap_fault( 'Client', '', "User '$t_user_id' does not have access right to add notes to this issue." );
 		}

 		if ( bug_is_readonly( $p_issue_id ) ) {
 			return new soap_fault( 'Client', '', "Issue '$p_issue_id' is readonly." );
 		}

 		if ( isset( $p_note['view_state'] ) ) {
 			$t_view_state = $p_note['view_state'];
 		} else {
	 		$t_view_state = array( 'id' => config_get( 'default_bug_view_status' ) );
	 	}

		$t_view_state_id = mci_get_enum_id_from_objectref( 'view_state', $t_view_state );
		return bugnote_add( $p_issue_id, $p_note['text'], '0:00', $t_view_state_id == VS_PRIVATE, BUGNOTE, '', $t_user_id );
 	}

 	/**
 	 * Delete a note given its id.
 	 *
	 * @param string $p_username  The name of the user trying to add a note to an issue.
	 * @param string $p_password  The password of the user.
	 * @param integer $p_issue_note_id  The id of the note to be deleted.
	 * @return true: success, false: failure
	 */
 	function mc_issue_note_delete( $p_username, $p_password, $p_issue_note_id ) {
		$t_user_id = mci_check_login( $p_username, $p_password );
		if ( $t_user_id === false ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}

		if ( (integer)$p_issue_note_id < 1 ) {
 			return new soap_fault( 'Client', '', "Invalid issue note id '$p_issue_note_id'." );
		}

		if ( !bugnote_exists( $p_issue_note_id ) ) {
			return new soap_fault( 'Server', '', "Issue note '$p_issue_note_id' does not exist." );
		}

		$t_issue_id = bugnote_get_field( $p_issue_note_id, 'bug_id' );
		$t_project_id = bug_get_field( $t_issue_id, 'project_id' );
		if ( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}

		return bugnote_delete( $p_issue_note_id );
 	}
 	
 	/**
	 * Submit a new relationship.
	 *
	 * @param string $p_username  The name of the user trying to add a note to an issue.
	 * @param string $p_password  The password of the user.
	 * @param integer $p_issue_id  The id of the issue of the source issue.
	 * @param RelationshipData $p_relationship  The relationship to add.
	 * @return integer The id of the added relationship.
	 */
 	function mc_issue_relationship_add( $p_username, $p_password, $p_issue_id, $p_relationship ) {
 		$t_user_id = mci_check_login( $p_username, $p_password );
 		$t_dest_issue_id = $p_relationship['target_id'];
 		$t_rel_type = $p_relationship['type'];
 		
 		if ( $t_user_id === false ) {
 			return new soap_fault( 'Client', '', 'Access Denied' );
 		}
 		
 		$t_project_id = bug_get_field( $p_issue_id, 'project_id' );
 		if ( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
 			return new soap_fault( 'Client', '', 'Access Denied' );
 		}
 		
 		# user has access to update the bug...
		if ( !access_has_bug_level( config_get( 'update_bug_threshold' ), $p_issue_id, $t_user_id ) ) {
 			return new soap_fault( 'Client', '', "Active user does not have access level required to add a relationship to this issue." );
		}
		
		# source and destination bugs are the same bug...
		if ( $p_issue_id == $t_dest_issue_id ) {
 			return new soap_fault( 'Client', '', "An issue can't be related to itself." );
		}
		
		# the related bug exists...
		if ( !bug_exists( $t_dest_issue_id ) ) {
 			return new soap_fault( 'Client', '', "Issue '$t_dest_issue_id' not found." );
		}
		
		# bug is not read-only...
 		if ( bug_is_readonly( $p_issue_id ) ) {
 			return new soap_fault( 'Client', '', "Issue '$p_issue_id' is readonly." );
 		}
 		
		# user can access to the related bug at least as viewer...
		if ( !access_has_bug_level( VIEWER, $t_dest_issue_id, $t_user_id ) ) {
			return new soap_fault( 'Client', '', "The issue '$t_dest_issue_id' requires higher access level." );
		}

 		$t_old_id_relationship = relationship_same_type_exists( $p_issue_id, $t_dest_issue_id, $t_rel_type['id'] );
 		
 		if ( $t_old_id_relationship == 0 ) {
 			relationship_add( $p_issue_id, $t_dest_issue_id, $t_rel_type['id'] );
 			// The above function call into Mantis doesn't seem to return a valid BugRelationshipData object.
 			// So we call db_insert_id in order to find the id of the created relationship.
 			$t_relationship_id = db_insert_id( config_get( 'mantis_bug_relationship_table' ) );
 			
			# Add log line to the history (both bugs)
			history_log_event_special( $p_issue_id, BUG_ADD_RELATIONSHIP, $t_rel_type['id'], $t_dest_issue_id );
			history_log_event_special( $t_dest_issue_id, BUG_ADD_RELATIONSHIP, relationship_get_complementary_type( $t_rel_type['id'] ), $p_issue_id );
			
			# update bug last updated (just for the src bug)
			bug_update_date( $p_issue_id );

			# send email notification to the users addressed by both the bugs
			email_relationship_added( $p_issue_id, $t_dest_issue_id, $t_rel_type['id'] );
			email_relationship_added( $t_dest_issue_id, $p_issue_id, relationship_get_complementary_type( $t_rel_type['id'] ) );
 			
 			return $t_relationship_id;
 		} else {
 			return new soap_fault( 'Client', '', "Relationship already exists." );
 		}
 	}

 	/**
 	 * Delete the relationship with the specified target id.
 	 *
	 * @param string $p_username  The name of the user trying to add a note to an issue.
	 * @param string $p_password  The password of the user.
	 * @param integer $p_issue_id  The id of the source issue for the relationship
	 * @param integer $p_relationship_id  The id of relationship to delete.
	 * @return true: success, false: failure
	 */
 	function mc_issue_relationship_delete( $p_username, $p_password, $p_issue_id, $p_relationship_id ) {
 		$t_user_id = mci_check_login( $p_username, $p_password );
 		
 		if ( $t_user_id === false ) {
 			return new soap_fault( 'Client', '', 'Access Denied' );
 		}
 		
 		$t_project_id = bug_get_field( $p_issue_id, 'project_id' );
 		if ( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
 			return new soap_fault( 'Client', '', 'Access Denied' );
 		}
 		
		# user has access to update the bug...
		if ( !access_has_bug_level( config_get( 'update_bug_threshold' ), $p_issue_id, $t_user_id ) ) {
 			return new soap_fault( 'Client', '', "Active user does not have access level required to remove a relationship from this issue." );
		}

		# bug is not read-only...
 		if ( bug_is_readonly( $p_issue_id ) ) {
 			return new soap_fault( 'Client', '', "Issue '$p_issue_id' is readonly." );
 		}
 		
		# retrieve the destination bug of the relationship
		$t_dest_issue_id = relationship_get_linked_bug_id( $p_relationship_id, $p_issue_id );
		
		# user can access to the related bug at least as viewer, if it's exist...
		if ( bug_exists( $t_dest_issue_id )) {
			if ( !access_has_bug_level( VIEWER, $t_dest_issue_id, $t_user_id ) ) {
				return new soap_fault( 'Client', '', "The issue '$t_dest_issue_id' requires higher access level." );
			}
		}
		
		$t_bug_relationship_data = relationship_get( $p_relationship_id );
		$t_rel_type = $t_bug_relationship_data->type;

		# delete relationship from the DB
		relationship_delete( $p_relationship_id );

		# update bug last updated (just for the src bug)
		bug_update_date( $p_issue_id );
		
		# set the rel_type for both bug and dest_bug based on $t_rel_type and on who is the dest bug
		if ($p_issue_id == $t_bug_relationship_data->src_bug_id) {
			$t_bug_rel_type = $t_rel_type;
			$t_dest_bug_rel_type = relationship_get_complementary_type( $t_rel_type );
		} else {
			$t_bug_rel_type = relationship_get_complementary_type( $t_rel_type );
			$t_dest_bug_rel_type = $t_rel_type;
		}

		# send email and update the history for the src issue
		history_log_event_special( $p_issue_id, BUG_DEL_RELATIONSHIP, $t_bug_rel_type, $t_dest_issue_id );
		email_relationship_deleted( $p_issue_id, $t_dest_issue_id, $t_bug_rel_type );

		if ( bug_exists( $t_dest_issue_id )) {
			# send email and update the history for the dest issue
			history_log_event_special( $t_dest_issue_id, BUG_DEL_RELATIONSHIP, $t_dest_bug_rel_type, $p_issue_id );
			email_relationship_deleted( $t_dest_issue_id, $p_issue_id, $t_dest_bug_rel_type );
		}
		
		return true; 		
 	}

 	/**
	 * Log a checkin event on the issue
	 *
	 * @param string $p_username  The name of the user trying to access the issue.
	 * @param string $p_password  The password of the user.
	 * @param integer $p_issue_id The id of the issue to log a checkin.
	 * @param string $p_comment   The comment to add
	 * @param boolean $p_fixed    True if the issue is to be set to fixed
	 * @return boolean  true success, false otherwise.
	 */
	function mc_issue_checkin( $p_username, $p_password, $p_issue_id, $p_comment, $p_fixed) {
		$t_user_id = mci_check_login( $p_username, $p_password );
		if ( $t_user_id === false ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}

		if ( !bug_exists( $p_issue_id ) ) {
 			return new soap_fault( 'Client', '', "Issue '$p_issue_id' not found." );
		}

		$t_project_id = bug_get_field( $p_issue_id, 'project_id' );
		if ( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}

		helper_call_custom_function( 'checkin', array( $p_issue_id, $p_comment, '', '', $p_fixed ) );

		return true;
	}
?>
