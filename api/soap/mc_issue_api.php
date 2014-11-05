<?php
# MantisConnect - A webservice interface to Mantis Bug Tracker
# Copyright (C) 2004-2014  Victor Boctor - vboctor@users.sourceforge.net
# This program is distributed under dual licensing.  These include
# GPL and a commercial licenses.  Victor Boctor reserves the right to
# change the license of future releases.
# See docs/ folder for more details

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
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	if( !bug_exists( $p_issue_id ) ) {
		return false;
	}

	$t_project_id = bug_get_field( $p_issue_id, 'project_id' );
	if( !mci_has_readonly_access( $t_user_id, $t_project_id ) ) {

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
	global $g_project_override;

	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	$t_lang = mci_get_user_lang( $t_user_id );

	if( !bug_exists( $p_issue_id ) ) {
		return SoapObjectsFactory::newSoapFault('Client', 'Issue does not exist');
	}

	$t_project_id = bug_get_field( $p_issue_id, 'project_id' );
	$g_project_override = $t_project_id;
	if( !mci_has_readonly_access( $t_user_id, $t_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	if( !access_has_bug_level( VIEWER, $p_issue_id, $t_user_id ) ){
		return mci_soap_fault_access_denied( $t_user_id );
	}

	$t_bug = bug_get( $p_issue_id, true );
	$t_issue_data = array();

	$t_issue_data['id'] = $p_issue_id;
	$t_issue_data['view_state'] = mci_enum_get_array_by_id( $t_bug->view_state, 'view_state', $t_lang );
	$t_issue_data['last_updated'] = SoapObjectsFactory::newDateTimeVar($t_bug->last_updated);

	$t_issue_data['project'] = mci_project_as_array_by_id( $t_bug->project_id );
	$t_issue_data['category'] = mci_get_category( $t_bug->category_id );
	$t_issue_data['priority'] = mci_enum_get_array_by_id( $t_bug->priority, 'priority', $t_lang );
	$t_issue_data['severity'] = mci_enum_get_array_by_id( $t_bug->severity, 'severity', $t_lang );
	$t_issue_data['status'] = mci_enum_get_array_by_id( $t_bug->status, 'status', $t_lang );

	$t_issue_data['reporter'] = mci_account_get_array_by_id( $t_bug->reporter_id );
	$t_issue_data['summary'] = mci_sanitize_xml_string($t_bug->summary);
	$t_issue_data['version'] = mci_null_if_empty( $t_bug->version );
	$t_issue_data['build'] = mci_null_if_empty( $t_bug->build );
	$t_issue_data['profile_id'] = mci_null_if_empty( $t_bug->profile_id );
	$t_issue_data['platform'] = mci_null_if_empty( $t_bug->platform );
	$t_issue_data['os'] = mci_null_if_empty( $t_bug->os );
	$t_issue_data['os_build'] = mci_null_if_empty( $t_bug->os_build );
	$t_issue_data['reproducibility'] = mci_enum_get_array_by_id( $t_bug->reproducibility, 'reproducibility', $t_lang );
	$t_issue_data['date_submitted'] = SoapObjectsFactory::newDateTimeVar($t_bug->date_submitted);
	$t_issue_data['sticky'] = $t_bug->sticky;

	$t_issue_data['sponsorship_total'] = $t_bug->sponsorship_total;

	if( !empty( $t_bug->handler_id ) ) {
		if( access_has_bug_level( VIEWER, $p_issue_id, $t_user_id ) ) {
			$t_issue_data['handler'] = mci_account_get_array_by_id( $t_bug->handler_id );
		}
	}

	$t_issue_data['projection'] = mci_enum_get_array_by_id( $t_bug->projection, 'projection', $t_lang );
	$t_issue_data['eta'] = mci_enum_get_array_by_id( $t_bug->eta, 'eta', $t_lang );

	$t_issue_data['resolution'] = mci_enum_get_array_by_id( $t_bug->resolution, 'resolution', $t_lang );
	$t_issue_data['fixed_in_version'] = mci_null_if_empty( $t_bug->fixed_in_version );
	$t_issue_data['target_version'] = mci_null_if_empty( $t_bug->target_version );
	$t_issue_data['due_date'] = mci_issue_get_due_date( $t_bug );

	$t_issue_data['description'] = mci_sanitize_xml_string($t_bug->description);
	$t_issue_data['steps_to_reproduce'] = mci_null_if_empty( mci_sanitize_xml_string($t_bug->steps_to_reproduce) );
	$t_issue_data['additional_information'] = mci_null_if_empty( mci_sanitize_xml_string($t_bug->additional_information) );

	$t_issue_data['attachments'] = mci_issue_get_attachments( $p_issue_id );
	$t_issue_data['relationships'] = mci_issue_get_relationships( $p_issue_id, $t_user_id );
	$t_issue_data['notes'] = mci_issue_get_notes( $p_issue_id );
	$t_issue_data['custom_fields'] = mci_issue_get_custom_fields( $p_issue_id );
	$t_issue_data['monitors'] = mci_account_get_array_by_ids( bug_get_monitors ( $p_issue_id ) );
	$t_issue_data['tags'] = mci_issue_get_tags_for_bug_id( $p_issue_id , $t_user_id );

	return $t_issue_data;
}

/**
* Get history details about an issue.
*
* @param string $p_username  The name of the user trying to access the issue.
* @param string $p_password  The password of the user.
* @param integer $p_issue_id  The id of the issue to retrieve.
* @return Array that represents a HistoryDataArray structure
*/
function mc_issue_get_history( $p_username, $p_password, $p_issue_id ) {
    global $g_project_override;

	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	if( !bug_exists( $p_issue_id ) ) {
		return SoapObjectsFactory::newSoapFault('Client', 'Issue does not exist');
	}

	$t_project_id = bug_get_field( $p_issue_id, 'project_id' );
	if( !mci_has_readonly_access( $t_user_id, $t_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}
	$g_project_override = $t_project_id;

	if( !access_has_bug_level( VIEWER, $p_issue_id, $t_user_id ) ){
		return mci_soap_fault_access_denied( $t_user_id );
	}
	
	$t_user_access_level = user_get_access_level( $t_user_id, $t_project_id );
	if( !access_compare_level( $t_user_access_level, config_get( 'view_history_threshold' ) ) ){
		return mci_soap_fault_access_denied( $t_user_id );
	}

	$t_bug_history = history_get_raw_events_array($p_issue_id, $t_user_id);

	return $t_bug_history;
}

/**
 * Returns the category name, possibly null if no category is assigned
 *
 * @param int $p_category_id
 * @return string
 */
function mci_get_category( $p_category_id ) {
	if ( $p_category_id == 0 )
		return '';

	return mci_null_if_empty( category_get_name( $p_category_id ) );
}

/**
 *
 * @param BugData $p_bug
 * @return soapval the value to be encoded as the due date
 */
function mci_issue_get_due_date( $p_bug ) {

	$t_value = null;

	if ( access_has_bug_level( config_get( 'due_date_view_threshold' ), $p_bug->id )  && !date_is_null( $p_bug->due_date ) ) {
		$t_value = $p_bug->due_date;
	}

	return SoapObjectsFactory::newDateTimeVar( $t_value) ;
}

/**
 * Sets the supplied array of custom field values to the specified issue id.
 *
 * @param $p_issue_id   Issue id to apply custom field values to.
 * @param $p_custom_fields  The array of custom field values as described in the webservice complex types.
 * @param boolean $p_log_insert create history logs for new values
 */
function mci_issue_set_custom_fields( $p_issue_id, &$p_custom_fields, $p_log_insert ) {
	# set custom field values on the submitted issue
	if( isset( $p_custom_fields ) && is_array( $p_custom_fields ) ) {
		foreach( $p_custom_fields as $t_custom_field ) {

			$t_custom_field = SoapObjectsFactory::unwrapObject( $t_custom_field );

			# get custom field id from object ref
			$t_custom_field_id = mci_get_custom_field_id_from_objectref( $t_custom_field['field'] );

			if( $t_custom_field_id == 0 ) {
				return SoapObjectsFactory::newSoapFault('Client', 'Custom field ' . $t_custom_field['field']['name'] . ' not found.');
			}

			# skip if current user doesn't have login access.
			if( !custom_field_has_write_access( $t_custom_field_id, $p_issue_id ) ) {
				continue;
			}

			$t_value = $t_custom_field['value'];

			if( !custom_field_validate( $t_custom_field_id, $t_value ) ) {
				return SoapObjectsFactory::newSoapFault('Client', 'Invalid custom field value for field id ' . $t_custom_field_id . ' .');
			}

			if( !custom_field_set_value( $t_custom_field_id, $p_issue_id, $t_value, $p_log_insert  ) ) {
				return SoapObjectsFactory::newSoapFault('Server', 'Unable to set custom field value for field id ' . $t_custom_field_id . ' to issue ' . $p_issue_id. ' .');
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

		if( custom_field_has_read_access( $t_id, $p_issue_id ) ) {

			# user has not access to read this custom field.
			$t_value = custom_field_get_value( $t_id, $p_issue_id );
			if( $t_value === false ) {
				continue;
			}

			# return a blank string if the custom field value is undefined
			if( $t_value === null ) {
				$t_value = '';
			}

			$t_custom_field_value = array();
			$t_custom_field_value['field'] = array();
			$t_custom_field_value['field']['id'] = $t_id;
			$t_custom_field_value['field']['name'] = $t_def['name'];
			$t_custom_field_value['value'] = $t_value;

			$t_custom_fields[] = $t_custom_field_value;
		}
	}

	# foreach

	return( count( $t_custom_fields ) == 0 ? null : $t_custom_fields );
}

/**
 * Get the attachments of an issue.
 *
 * @param integer $p_issue_id  The id of the issue to retrieve the attachments for
 * @return Array that represents an AttachmentData structure
 */
function mci_issue_get_attachments( $p_issue_id ) {
	$t_attachment_rows = bug_get_attachments( $p_issue_id );

	if ( $t_attachment_rows == null) {
		return array();
	}

	$t_result = array();
	foreach( $t_attachment_rows as $t_attachment_row ) {
		if ( !file_can_view_bug_attachments( $p_issue_id, (int)$t_attachment_row['user_id'] ) ) {
			continue;
		}
		$t_attachment = array();
		$t_attachment['id'] = $t_attachment_row['id'];
		$t_attachment['filename'] = $t_attachment_row['filename'];
		$t_attachment['size'] = $t_attachment_row['filesize'];
		$t_attachment['content_type'] = $t_attachment_row['file_type'];
		$t_attachment['date_submitted'] = SoapObjectsFactory::newDateTimeVar( $t_attachment_row['date_added'] );
		$t_attachment['download_url'] = mci_get_mantis_path() . 'file_download.php?file_id=' . $t_attachment_row['id'] . '&amp;type=bug';
		$t_attachment['user_id'] = $t_attachment_row['user_id'];
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
	foreach( $t_src_relationships as $t_relship_row ) {
		if( access_has_bug_level( config_get( 'mc_readonly_access_level_threshold' ), $t_relship_row->dest_bug_id, $p_user_id ) ) {
			$t_relationship = array();
			$t_reltype = array();
			$t_relationship['id'] = $t_relship_row->id;
			$t_reltype['id'] = $t_relship_row->type;
			$t_reltype['name'] = relationship_get_description_src_side( $t_relship_row->type );
			$t_relationship['type'] = $t_reltype;
			$t_relationship['target_id'] = $t_relship_row->dest_bug_id;
			$t_relationships[] = $t_relationship;
		}
	}

	$t_dest_relationships = relationship_get_all_dest( $p_issue_id );
	foreach( $t_dest_relationships as $t_relship_row ) {
		if( access_has_bug_level( config_get( 'mc_readonly_access_level_threshold' ), $t_relship_row->src_bug_id, $p_user_id ) ) {
			$t_relationship = array();
			$t_relationship['id'] = $t_relship_row->id;
			$t_reltype = array();
			$t_reltype['id'] = relationship_get_complementary_type( $t_relship_row->type );
			$t_reltype['name'] = relationship_get_description_dest_side( $t_relship_row->type );
			$t_relationship['type'] = $t_reltype;
			$t_relationship['target_id'] = $t_relship_row->src_bug_id;
			$t_relationships[] = $t_relationship;
		}
	}

	return (count( $t_relationships ) == 0 ? null : $t_relationships );
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
	$t_user_bugnote_order = 'ASC'; // always get the notes in ascending order for consistency to the calling application.
	$t_has_time_tracking_access = access_has_bug_level( config_get( 'time_tracking_view_threshold' ), $p_issue_id );

	$t_result = array();
	foreach( bugnote_get_all_visible_bugnotes( $p_issue_id, $t_user_bugnote_order, 0 ) as $t_value ) {
		$t_bugnote = array();
		$t_bugnote['id'] = $t_value->id;
		$t_bugnote['reporter'] = mci_account_get_array_by_id( $t_value->reporter_id );
		$t_bugnote['date_submitted'] = SoapObjectsFactory::newDateTimeString( $t_value->date_submitted );
		$t_bugnote['last_modified'] = SoapObjectsFactory::newDateTimeString( $t_value->last_modified );
		$t_bugnote['text'] = mci_sanitize_xml_string( $t_value->note );
		$t_bugnote['view_state'] = mci_enum_get_array_by_id( $t_value->view_state, 'view_state', $t_lang );
		$t_bugnote['time_tracking'] = $t_has_time_tracking_access ? $t_value->time_tracking : 0;
		$t_bugnote['note_type'] = $t_value->note_type;
		$t_bugnote['note_attr'] = $t_value->note_attr;

		$t_result[] = $t_bugnote;
	}

	return (count( $t_result ) == 0 ? null : $t_result );
}

/**
 * Sets the monitors of the specified issue
 *
 * <p>This functions performs access level checks and only performs operations which would
 * modify the existing monitors list.</p>
 *
 * @param int $p_issue_id the issue id to set the monitors for
 * @param int $p_user_id the user which requests the monitor change
 * @param array $p_monitors An array of arrays with the <em>id</em> field set to the id
 *  of the users which should monitor this issue.
 */
function mci_issue_set_monitors( $p_issue_id , $p_requesting_user_id, $p_monitors ) {
	if ( bug_is_readonly( $p_issue_id ) ) {
		return mci_soap_fault_access_denied( $p_requesting_user_id, "Issue '$p_issue_id' is readonly" );
	}

	# 1. get existing monitor ids
	$t_existing_monitor_ids = bug_get_monitors( $p_issue_id );

	# 2. build new monitors ids
	$t_new_monitor_ids = array();
	foreach ( $p_monitors as $t_monitor ) {
		$t_monitor = SoapObjectsFactory::unwrapObject( $t_monitor );
		$t_new_monitor_ids[] = $t_monitor['id'];
	}

	# 3. for each of the new monitor ids, add it if it does not already exist
	foreach ( $t_new_monitor_ids as $t_user_id ) {

		if ( $p_requesting_user_id == $t_user_id ) {
			if ( ! access_has_bug_level( config_get( 'monitor_bug_threshold' ), $p_issue_id ) )
				continue;
		} else {
			if ( !access_has_bug_level( config_get( 'monitor_add_others_bug_threshold' ), $p_issue_id ) )
				continue;
		}

		if ( in_array( $t_user_id, $t_existing_monitor_ids) )
			continue;

		bug_monitor( $p_issue_id, $t_user_id);
	}

	# 4. for each of the existing monitor ids, remove it if it is not found in the new monitor ids
	foreach ( $t_existing_monitor_ids as $t_user_id ) {

		if ( $p_requesting_user_id == $t_user_id ) {
			if ( ! access_has_bug_level( config_get( 'monitor_bug_threshold' ), $p_issue_id ) )
				continue;
		} else {
			if ( !access_has_bug_level( config_get( 'monitor_delete_others_bug_threshold' ), $p_issue_id ) )
				continue;
		}

		if ( in_array( $t_user_id, $t_new_monitor_ids) )
			continue;

		bug_unmonitor( $p_issue_id, $t_user_id);
	}

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
	global $g_project_override;
	
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	$t_any = defined( 'META_FILTER_ANY' ) ? META_FILTER_ANY : 'any';
	$t_none = defined( 'META_FILTER_NONE' ) ? META_FILTER_NONE : 'none';

	$t_filter = array(
		'show_category' => Array(
			'0' => $t_any,
		),
		'show_severity' => Array(
			'0' => $t_any,
		),
		'show_status' => Array(
			'0' => $t_any,
		),
		'highlight_changed' => 0,
		'reporter_id' => Array(
			'0' => $t_any,
		),
		'handler_id' => Array(
			'0' => $t_any,
		),
		'show_resolution' => Array(
			'0' => $t_any,
		),
		'show_build' => Array(
			'0' => $t_any,
		),
		'show_version' => Array(
			'0' => $t_any,
		),
		'hide_status' => Array(
			'0' => $t_none,
		),
		'user_monitor' => Array(
			'0' => $t_any,
		),
		'dir' => 'DESC',
		'sort' => 'id',
	);

	$t_page_number = 1;
	$t_per_page = 1;
	$t_bug_count = 0;
	$t_page_count = 0;

	# Get project id, if -1, then retrieve the current which will be the default since there is no cookie.
	$t_project_id = $p_project_id;
	if( $t_project_id == -1 ) {
		$t_project_id = helper_get_current_project();
	}
	$g_project_override = $t_project_id;

	if(( $t_project_id > 0 ) && !project_exists( $t_project_id ) ) {
		return SoapObjectsFactory::newSoapFault( 'Client', "Project '$t_project_id' does not exist." );
	}

	if( !mci_has_readonly_access( $t_user_id, $t_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	$t_rows = filter_get_bug_rows( $t_page_number, $t_per_page, $t_page_count, $t_bug_count, $t_filter, $t_project_id, $t_user_id );
	if( count( $t_rows ) == 0 ) {
		return 0;
	} else {
		return $t_rows[0]->id;
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
	global $g_project_override;
	
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	$t_bug_table = db_get_table( 'mantis_bug_table' );

	$query = "SELECT id
		FROM $t_bug_table
		WHERE summary = " . db_param();

	$result = db_query_bound( $query, Array( $p_summary ), 1 );

	if( db_num_rows( $result ) == 0 ) {
		return 0;
	} else {
		while(( $row = db_fetch_array( $result ) ) !== false ) {
			$t_issue_id = (int) $row['id'];
			$t_project_id = bug_get_field( $t_issue_id, 'project_id' );
			$g_project_override = $t_project_id;

			if( mci_has_readonly_access( $t_user_id, $t_project_id ) &&
				access_has_bug_level( VIEWER, $t_issue_id, $t_user_id ) ) {
				return $t_issue_id;
			}
		}

		// no issue found that belongs to a project that the user has read access to.
		return 0;
	}
}

/**
 * Does the actual checks when setting the issue handler.
 * The user existence check is always done even if handler doesn't change.
 * The handler's access level check is done even if handler doesn't change.
 * The current user ability to assign issue access check is only done on change.
 * This behavior would be consistent with the web UI.
 *
 * @param $p_user_id        The id of the logged in user.
 * @param $p_project_id     The id of the project the issue is associated with.
 * @param $p_old_handler_id The old handler id.
 * @param $p_new_handler_id The new handler id.  0 for not assigned.
 * @return true: access ok, otherwise: soap fault.
 */
function mci_issue_handler_access_check( $p_user_id, $p_project_id, $p_old_handler_id, $p_new_handler_id ) {
	if( $p_new_handler_id != 0 ) {
		if ( !user_exists( $p_new_handler_id ) ) {
			return SoapObjectsFactory::newSoapFault( 'Client', 'User \'' . $p_new_handler_id . '\' does not exist.' );
		}

		if( !access_has_project_level( config_get( 'handle_bug_threshold' ), $p_project_id, $p_new_handler_id ) ) {
			return mci_soap_fault_access_denied( 'User \'' . $p_new_handler_id . '\' does not have access right to handle issues' );
		}
	}

	if( $p_old_handler_id != $p_new_handler_id ) {
		if( !access_has_project_level( config_get( 'update_bug_assign_threshold' ), $p_project_id, $p_user_id ) ) {
			return mci_soap_fault_access_denied( 'User \'' . $p_user_id . '\' does not have access right to assign issues' );
		}
	}

	return true;
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

	global $g_project_override;

	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	$p_issue = SoapObjectsFactory::unwrapObject( $p_issue );
	$t_project = $p_issue['project'];

	$t_project_id = mci_get_project_id( $t_project );
	$g_project_override = $t_project_id; // ensure that helper_get_current_project() calls resolve to this project id

	if( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	$t_handler_id = isset( $p_issue['handler'] ) ? mci_get_user_id( $p_issue['handler'] ) : 0;
	$t_priority_id = isset( $p_issue['priority'] ) ? mci_get_priority_id( $p_issue['priority'] ) : config_get( 'default_bug_priority' );
	$t_severity_id = isset( $p_issue['severity'] ) ?  mci_get_severity_id( $p_issue['severity'] ) : config_get( 'default_bug_severity' );
	$t_status_id = isset( $p_issue['status'] ) ? mci_get_status_id( $p_issue['status'] ) : config_get( 'bug_submit_status' );
	$t_reproducibility_id = isset( $p_issue['reproducibility'] ) ?  mci_get_reproducibility_id( $p_issue['reproducibility'] ) : config_get( 'default_bug_reproducibility' );
	$t_resolution_id =  isset( $p_issue['resolution'] ) ? mci_get_resolution_id( $p_issue['resolution'] ) : config_get('default_bug_resolution');
	$t_projection_id = isset( $p_issue['projection'] ) ? mci_get_projection_id( $p_issue['projection'] ) : config_get('default_bug_resolution');
	$t_eta_id = isset( $p_issue['eta'] ) ? mci_get_eta_id( $p_issue['eta'] ) : config_get('default_bug_eta');
	$t_view_state_id = isset( $p_issue['view_state'] ) ?  mci_get_view_state_id( $p_issue['view_state'] ) : config_get( 'default_bug_view_status' );
	$t_reporter_id = isset( $p_issue['reporter'] ) ? mci_get_user_id( $p_issue['reporter'] )  : 0;
	$t_summary = $p_issue['summary'];
	$t_description = $p_issue['description'];
	$t_notes = isset( $p_issue['notes'] ) ? $p_issue['notes'] : array();

	if( $t_reporter_id == 0 ) {
		$t_reporter_id = $t_user_id;
	} else {
		if( $t_reporter_id != $t_user_id ) {

			# Make sure that active user has access level required to specify a different reporter.
			$t_specify_reporter_access_level = config_get( 'mc_specify_reporter_on_add_access_level_threshold' );
			if( !access_has_project_level( $t_specify_reporter_access_level, $t_project_id, $t_user_id ) ) {
				return mci_soap_fault_access_denied( $t_user_id, "Active user does not have access level required to specify a different issue reporter" );
			}
		}
	}

	if(( $t_project_id == 0 ) || !project_exists( $t_project_id ) ) {
		if( $t_project_id == 0 ) {
			return SoapObjectsFactory::newSoapFault('Client', "Project '" . $t_project['name'] . "' does not exist.");
		} else {
			return SoapObjectsFactory::newSoapFault('Client', "Project with id '" . $t_project_id . "' does not exist.");
		}
	}

	if( !access_has_project_level( config_get( 'report_bug_threshold' ), $t_project_id, $t_user_id ) ) {
		return mci_soap_fault_access_denied( "User '$t_user_id' does not have access right to report issues" );
	}

	$t_access_check_result = mci_issue_handler_access_check( $t_user_id, $t_project_id, /* old */ 0, /* new */ $t_handler_id );
	if( $t_access_check_result !== true ) {
		return $t_access_check_result;
 	}

	$t_category = isset ( $p_issue['category'] ) ? $p_issue['category'] : null;

	$t_category_id = translate_category_name_to_id( $t_category, $t_project_id );
	if ( $t_category_id == 0 && !config_get( 'allow_no_category' ) ) {
		if ( !isset( $p_issue['category'] ) || is_blank( $p_issue['category'] ) ) {
			return SoapObjectsFactory::newSoapFault('Client', "Category field must be supplied.");
		} else {
			return SoapObjectsFactory::newSoapFault('Client', "Category '" . $p_issue['category'] . "' not found for project '$t_project_id'.");
		}
	}

	if ( isset( $p_issue['version'] ) && !is_blank( $p_issue['version'] ) && !version_get_id( $p_issue['version'], $t_project_id ) ) {
		$t_version = $p_issue['version'];

		$t_error_when_version_not_found = config_get( 'mc_error_when_version_not_found' );
		if( $t_error_when_version_not_found == ON ) {
			$t_project_name = project_get_name( $t_project_id );
			return SoapObjectsFactory::newSoapFault('Client', "Version '$t_version' does not exist in project '$t_project_name'.");
		} else {
			$t_version_when_not_found = config_get( 'mc_version_when_not_found' );
			$t_version = $t_version_when_not_found;
		}
	}

	if ( is_blank( $t_summary ) ) {
		return SoapObjectsFactory::newSoapFault('Client', "Mandatory field 'summary' is missing.");
	}

	if ( is_blank( $t_description ) ) {
		return SoapObjectsFactory::newSoapFault('Client', "Mandatory field 'description' is missing.");
	}

	$t_bug_data = new BugData;
	$t_bug_data->profile_id = 0;
	$t_bug_data->project_id = $t_project_id;
	$t_bug_data->reporter_id = $t_reporter_id;
	$t_bug_data->handler_id = $t_handler_id;
	$t_bug_data->priority = $t_priority_id;
	$t_bug_data->severity = $t_severity_id;
	$t_bug_data->reproducibility = $t_reproducibility_id;
	$t_bug_data->status = $t_status_id;
	$t_bug_data->resolution = $t_resolution_id;
	$t_bug_data->projection = $t_projection_id;
	$t_bug_data->category_id = $t_category_id;
	$t_bug_data->date_submitted = isset( $p_issue['date_submitted'] ) ? $p_issue['date_submitted'] : '';
	$t_bug_data->last_updated = isset( $p_issue['last_updated'] ) ? $p_issue['last_updated'] : '';
	$t_bug_data->eta = $t_eta_id;
	$t_bug_data->profile_id = isset ( $p_issue['profile_id'] ) ? $p_issue['profile_id'] : 0;
	$t_bug_data->os = isset( $p_issue['os'] ) ? $p_issue['os'] : '';
	$t_bug_data->os_build = isset( $p_issue['os_build'] ) ? $p_issue['os_build'] : '';
	$t_bug_data->platform = isset( $p_issue['platform'] ) ? $p_issue['platform'] : '';
	$t_bug_data->version = isset( $p_issue['version'] ) ? $p_issue['version'] : '';
	$t_bug_data->fixed_in_version = isset( $p_issue['fixed_in_version'] ) ? $p_issue['fixed_in_version'] : '';
	$t_bug_data->build = isset( $p_issue['build'] ) ? $p_issue['build'] : '';
	$t_bug_data->view_state = $t_view_state_id;
	$t_bug_data->summary = $t_summary;
	$t_bug_data->sponsorship_total = isset( $p_issue['sponsorship_total'] ) ? $p_issue['sponsorship_total'] : 0;
	if (  isset ( $p_issue['sticky']) &&
		 access_has_project_level( config_get( 'set_bug_sticky_threshold', null, null, $t_project_id ), $t_project_id ) ) {
		$t_bug_data->sticky = $p_issue['sticky'];
	}

	if ( isset( $p_issue['due_date'] ) && access_has_global_level( config_get( 'due_date_update_threshold' ) ) ) {
		$t_bug_data->due_date = SoapObjectsFactory::parseDateTimeString( $p_issue['due_date'] );
	} else {
		$t_bug_data->due_date = date_get_null();
	}

	if( access_has_project_level( config_get( 'roadmap_update_threshold' ), $t_bug_data->project_id, $t_user_id ) ) {
		$t_bug_data->target_version = isset( $p_issue['target_version'] ) ? $p_issue['target_version'] : '';
	}

	# omitted:
	# var $bug_text_id
	# $t_bug_data->profile_id;
	# extended info
	$t_bug_data->description = $t_description;
	$t_bug_data->steps_to_reproduce = isset( $p_issue['steps_to_reproduce'] ) ? $p_issue['steps_to_reproduce'] : '';
	$t_bug_data->additional_information = isset( $p_issue['additional_information'] ) ? $p_issue['additional_information'] : '';

	# submit the issue
	$t_issue_id = $t_bug_data->create();

	$t_set_custom_field_error = mci_issue_set_custom_fields( $t_issue_id, $p_issue['custom_fields'], false );
	if ( $t_set_custom_field_error != null ) return $t_set_custom_field_error;

	if ( isset ( $p_issue['monitors'] ) )
		mci_issue_set_monitors( $t_issue_id , $t_user_id, $p_issue['monitors'] );

	if( isset( $t_notes ) && is_array( $t_notes ) ) {
		foreach( $t_notes as $t_note ) {

			$t_note = SoapObjectsFactory::unwrapObject( $t_note );

			if( isset( $t_note['view_state'] ) ) {
				$t_view_state = $t_note['view_state'];
			} else {
				$t_view_state = config_get( 'default_bugnote_view_status' );
			}

			$note_type = isset ( $t_note['note_type'] ) ? (int) $t_note['note_type'] : BUGNOTE;
			$note_attr = isset ( $t_note['note_type'] ) ? $t_note['note_attr'] : '';

			$t_view_state_id = mci_get_enum_id_from_objectref( 'view_state', $t_view_state );
			bugnote_add( $t_issue_id, $t_note['text'], mci_get_time_tracking_from_note( $t_issue_id, $t_note ), $t_view_state_id == VS_PRIVATE, $note_type, $note_attr, $t_user_id, FALSE );
		}
	}

	if ( isset ( $p_issue['tags']) && is_array ( $p_issue['tags']) ) {
		mci_tag_set_for_issue( $t_issue_id, $p_issue['tags'], $t_user_id );
	}

	email_new_bug( $t_issue_id );


	if ( $t_bug_data->status != config_get('bug_submit_status') )
        history_log_event($t_issue_id, 'status', config_get('bug_submit_status') );

	if ( $t_bug_data->resolution != config_get('default_bug_resolution') )
	   history_log_event($t_issue_id, 'resolution', config_get('default_bug_resolution') );

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
	global $g_project_override;

	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	if( !bug_exists( $p_issue_id ) ) {
		return SoapObjectsFactory::newSoapFault('Client', "Issue '$p_issue_id' does not exist.");
	}

	if( bug_is_readonly( $p_issue_id ) ) {
		return SoapObjectsFactory::newSoapFault('Client', "Issue '$p_issue_id' is readonly");
	}

	$t_project_id = bug_get_field( $p_issue_id, 'project_id' );

	if( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	$g_project_override = $t_project_id; // ensure that helper_get_current_project() calls resolve to this project id

	$p_issue = SoapObjectsFactory::unwrapObject( $p_issue );

	$t_project_id = mci_get_project_id( $p_issue['project'] );
	$t_reporter_id = isset( $p_issue['reporter'] ) ? mci_get_user_id( $p_issue['reporter'] )  : $t_user_id ;
	$t_handler_id = isset( $p_issue['handler'] ) ? mci_get_user_id( $p_issue['handler'] ) : 0;
	$t_project = $p_issue['project'];
	$t_summary = isset( $p_issue['summary'] ) ? $p_issue['summary'] : '';
	$t_description = isset( $p_issue['description'] ) ? $p_issue['description'] : '';


	if(( $t_project_id == 0 ) || !project_exists( $t_project_id ) ) {
		if( $t_project_id == 0 ) {
			return SoapObjectsFactory::newSoapFault( 'Client', "Project '" . $t_project['name'] . "' does not exist." );
		}
		return SoapObjectsFactory::newSoapFault( 'Client', "Project '$t_project_id' does not exist." );
	}

	if( !access_has_bug_level( config_get( 'update_bug_threshold' ), $p_issue_id, $t_user_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id,  "Not enough rights to update issues" );
	}

	$t_category = isset ( $p_issue['category'] ) ? $p_issue['category'] : null;

	$t_category_id = translate_category_name_to_id( $t_category, $t_project_id );
	if ( $t_category_id == 0 && !config_get( 'allow_no_category' ) ) {
		if ( isset( $p_issue['category'] ) && !is_blank( $p_issue['category'] ) ) {
			return SoapObjectsFactory::newSoapFault( 'Client', "Category field must be supplied." );
		} else {
			$t_project_name = project_get_name( $t_project_id );
			return SoapObjectsFactory::newSoapFault( 'Client', "Category '" . $p_issue['category'] . "' not found for project '$t_project_name'." );
		}
	}

	if ( isset( $p_issue['version'] ) && !is_blank( $p_issue['version'] ) && !version_get_id( $p_issue['version'], $t_project_id ) ) {
		$t_error_when_version_not_found = config_get( 'mc_error_when_version_not_found' );
		if( $t_error_when_version_not_found == ON ) {
			$t_project_name = project_get_name( $t_project_id );
			return SoapObjectsFactory::newSoapFault( 'Client', "Version '" . $p_issue['version'] . "' does not exist in project '$t_project_name'." );
		} else {
			$t_version_when_not_found = config_get( 'mc_version_when_not_found' );
			$p_issue['version'] = $t_version_when_not_found;
		}
	}

	if ( is_blank( $t_summary ) ) {
		return SoapObjectsFactory::newSoapFault( 'Client', "Mandatory field 'summary' is missing." );
	}

	if ( is_blank( $t_description ) ) {
		return SoapObjectsFactory::newSoapFault( 'Client', "Mandatory field 'description' is missing." );
	}

	// fields which we expect to always be set
	$t_bug_data = bug_get( $p_issue_id, true );
	$t_bug_data->project_id = $t_project_id;
	$t_bug_data->reporter_id = $t_reporter_id;

	$t_access_check_result = mci_issue_handler_access_check( $t_user_id, $t_project_id, /* old */ $t_bug_data->handler_id, /* new */ $t_handler_id );
	if( $t_access_check_result !== true ) {
		return $t_access_check_result;
	}

	$t_bug_data->handler_id = $t_handler_id;
	$t_bug_data->category_id = $t_category_id;
	$t_bug_data->summary = $t_summary;
	$t_bug_data->description = $t_description;

	// fields which might not be set
	if ( isset ( $p_issue['steps_to_reproduce'] ) )
		$t_bug_data->steps_to_reproduce = $p_issue['steps_to_reproduce'];
	if ( isset ( $p_issue['additional_information'] ) )
		$t_bug_data->additional_information = $p_issue['additional_information'];
	if ( isset( $p_issue['priority'] ) )
		$t_bug_data->priority = mci_get_priority_id( $p_issue['priority'] );
	if ( isset( $p_issue['severity'] ) )
		$t_bug_data->severity = mci_get_severity_id( $p_issue['severity'] );
	if ( isset( $p_issue['status'] ) )
		$t_bug_data->status = mci_get_status_id ( $p_issue['status'] );
	if ( isset ( $p_issue['reproducibility'] ) )
		$t_bug_data->reproducibility = mci_get_reproducibility_id( $p_issue['reproducibility'] );
	if ( isset ( $p_issue['resolution'] ) )
		$t_bug_data->resolution = mci_get_resolution_id( $p_issue['resolution'] );
	if ( isset ( $p_issue['projection'] ) )
		$t_bug_data->projection = mci_get_projection_id( $p_issue['projection'] );
	if ( isset ( $p_issue['eta'] ) )
		$t_bug_data->eta = mci_get_eta_id( $p_issue['eta'] );
	if ( isset ( $p_issue['view_state'] ) )
		$t_bug_data->view_state = mci_get_view_state_id( $p_issue['view_state'] );
	if ( isset ( $p_issue['date_submitted'] ) )
		$t_bug_data->date_submitted = $p_issue['date_submitted'];
	if ( isset ( $p_issue['date_updated'] ) )
		$t_bug_data->last_updated = $p_issue['last_updated'];
	if ( isset ( $p_issue['profile_id'] ) )
		$t_bug_data->profile_id = $p_issue['profile_id'];
	if ( isset ( $p_issue['os'] ) )
		$t_bug_data->os = $p_issue['os'];
	if ( isset ( $p_issue['os_build'] ) )
		$t_bug_data->os_build = $p_issue['os_build'];
	if ( isset ( $p_issue['build'] ) )
		$t_bug_data->build = $p_issue['build'];
	if ( isset ( $p_issue['platform'] ) )
		$t_bug_data->platform = $p_issue['platform'];
	if ( isset ( $p_issue['version'] ) )
		$t_bug_data->version = $p_issue['version'];
	if ( isset ( $p_issue['fixed_in_version'] ) )
		$t_bug_data->fixed_in_version = $p_issue['fixed_in_version'];
	if (  isset ( $p_issue['sticky']) && access_has_bug_level( config_get( 'set_bug_sticky_threshold' ), $t_bug_data->id ) ) {
		$t_bug_data->sticky = $p_issue['sticky'];
	}

	if ( isset( $p_issue['due_date'] ) && access_has_global_level( config_get( 'due_date_update_threshold' ) ) ) {
		$t_bug_data->due_date = SoapObjectsFactory::parseDateTimeString( $p_issue['due_date'] );
	} else {
		$t_bug_data->due_date = date_get_null();
	}

	if( access_has_project_level( config_get( 'roadmap_update_threshold' ), $t_bug_data->project_id, $t_user_id ) ) {
		$t_bug_data->target_version = isset( $p_issue['target_version'] ) ? $p_issue['target_version'] : '';
	}

	$t_set_custom_field_error = mci_issue_set_custom_fields( $p_issue_id, $p_issue['custom_fields'], true );
	if ( $t_set_custom_field_error != null ) return $t_set_custom_field_error;

	if ( isset ( $p_issue['monitors'] ) )
		mci_issue_set_monitors( $p_issue_id , $t_user_id, $p_issue['monitors'] );

	if ( isset( $p_issue['notes'] ) && is_array( $p_issue['notes'] ) ) {

		$t_bugnotes = bugnote_get_all_visible_bugnotes( $p_issue_id, 'DESC', 0 );
		$t_bugnotes_by_id = array();
		foreach ( $t_bugnotes as $t_bugnote ) {
			$t_bugnotes_by_id[$t_bugnote->id] = $t_bugnote;
		}

		foreach ( $p_issue['notes'] as $t_note ) {

			$t_note = SoapObjectsFactory::unwrapObject( $t_note );

			if ( isset( $t_note['view_state'] ) ) {
				$t_view_state = $t_note['view_state'];
			} else {
				$t_view_state = config_get( 'default_bugnote_view_status' );
			}

			if ( isset( $t_note['id'] ) && ( (int)$t_note['id'] > 0 ) ) {
				$t_bugnote_id = (integer)$t_note['id'];

				$t_view_state_id = mci_get_enum_id_from_objectref( 'view_state', $t_view_state );

				if ( array_key_exists( $t_bugnote_id , $t_bugnotes_by_id) ) {

					$t_bugnote_changed = false;

					if ( $t_bugnote->note !== $t_note['text']) {
						bugnote_set_text( $t_bugnote_id, $t_note['text'] );
						$t_bugnote_changed = true;
					}

					if ( $t_bugnote->view_state != $t_view_state_id ) {
						bugnote_set_view_state( $t_bugnote_id, $t_view_state_id == VS_PRIVATE );
						$t_bugnote_changed = true;
					}

					if ( isset( $t_note['time_tracking']) && $t_note['time_tracking'] != $t_bugnote->time_tracking ) {
						bugnote_set_time_tracking( $t_bugnote_id, mci_get_time_tracking_from_note( $p_issue_id, $t_note ) );
						$t_bugnote_changed = true;
					}

					if ( $t_bugnote_changed ) {
						bugnote_date_update( $t_bugnote_id );
					}

				}
			} else {
				$t_view_state_id = mci_get_enum_id_from_objectref( 'view_state', $t_view_state );

				$note_type = isset ( $t_note['note_type'] ) ? (int) $t_note['note_type'] : BUGNOTE;
				$note_attr = isset ( $t_note['note_type'] ) ? $t_note['note_attr'] : '';

				bugnote_add( $p_issue_id, $t_note['text'], mci_get_time_tracking_from_note( $p_issue_id, $t_note ), $t_view_state_id == VS_PRIVATE, $note_type, $note_attr, $t_user_id, FALSE );
			}
		}

		# The issue has been cached earlier in the bug_get() call.  Flush the cache since it is
		# now stale.  Otherwise, the email notification will be based on the cached data.
		bugnote_clear_cache( $p_issue_id );
	}

	if ( isset ( $p_issue['tags']) && is_array ( $p_issue['tags']) ) {
		mci_tag_set_for_issue( $p_issue_id, $p_issue['tags'] , $t_user_id );
	}

	# submit the issue
	return $t_is_success = $t_bug_data->update( /* update_extended */ true, /* bypass_email */ false);
}

function mc_issue_set_tags ( $p_username, $p_password, $p_issue_id, $p_tags ) {
	global $g_project_override;
	
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	if( !bug_exists( $p_issue_id ) ) {
		return SoapObjectsFactory::newSoapFault( 'Client', "Issue '$p_issue_id' does not exist." );
	}

	$t_project_id = bug_get_field( $p_issue_id, 'project_id' );
	$g_project_override = $t_project_id;

	if( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	if( bug_is_readonly( $p_issue_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id, "Issue '$p_issue_id' is readonly" );
	}

	mci_tag_set_for_issue( $p_issue_id,  $p_tags, $t_user_id );

	return true;
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
	global $g_project_override;
	
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	if( !bug_exists( $p_issue_id ) ) {
		return SoapObjectsFactory::newSoapFault( 'Client', "Issue '$p_issue_id' does not exist.");
	}

	$t_project_id = bug_get_field( $p_issue_id, 'project_id' );
	$g_project_override = $t_project_id;
	
	if( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	if ( !access_has_bug_level( config_get( 'delete_bug_threshold' ), $p_issue_id, $t_user_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
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
	global $g_project_override;
	
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	if( (integer) $p_issue_id < 1 ) {
		return SoapObjectsFactory::newSoapFault( 'Client', "Invalid issue id '$p_issue_id'" );
	}

	if( !bug_exists( $p_issue_id ) ) {
		return SoapObjectsFactory::newSoapFault( 'Client', "Issue '$p_issue_id' does not exist." );
	}

	$p_note = SoapObjectsFactory::unwrapObject( $p_note );

	if ( !isset( $p_note['text'] ) || is_blank( $p_note['text'] ) ) {
		return SoapObjectsFactory::newSoapFault( 'Client', "Issue note text must not be blank." );
	}

	$t_project_id = bug_get_field( $p_issue_id, 'project_id' );
	$g_project_override = $t_project_id;

	if( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	if( !access_has_bug_level( config_get( 'add_bugnote_threshold' ), $p_issue_id, $t_user_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id, "You do not have access rights to add notes to this issue" );
	}

	if( bug_is_readonly( $p_issue_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id, "Issue '$p_issue_id' is readonly" );
	}

	if( isset( $p_note['view_state'] ) ) {
		$t_view_state = $p_note['view_state'];
	} else {
		$t_view_state = array(
			'id' => config_get( 'default_bug_view_status' ),
		);
	}

	$t_view_state_id = mci_get_enum_id_from_objectref( 'view_state', $t_view_state );

	$note_type = isset ( $p_note['note_type'] ) ? (int) $p_note['note_type'] : BUGNOTE;
	$note_attr = isset ( $p_note['note_type'] ) ? $p_note['note_attr'] : '';

	return bugnote_add( $p_issue_id, $p_note['text'], mci_get_time_tracking_from_note( $p_issue_id, $p_note ), $t_view_state_id == VS_PRIVATE, $note_type, $note_attr, $t_user_id );
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
	global $g_project_override;

	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	if( (integer) $p_issue_note_id < 1 ) {
		return SoapObjectsFactory::newSoapFault( 'Client', "Invalid issue note id '$p_issue_note_id'.");
	}

	if( !bugnote_exists( $p_issue_note_id ) ) {
		return SoapObjectsFactory::newSoapFault( 'Client', "Issue note '$p_issue_note_id' does not exist.");
	}

	$t_issue_id = bugnote_get_field( $p_issue_note_id, 'bug_id' );
	$t_project_id = bug_get_field( $t_issue_id, 'project_id' );
	$g_project_override = $t_project_id;
	if( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	$t_reporter_id = bugnote_get_field( $p_issue_note_id, 'reporter_id' );

	// mirrors check from bugnote_delete.php
	if ( ( $t_user_id != $t_reporter_id ) || ( OFF == config_get( 'bugnote_allow_user_edit_delete' ) ) ) {
		if ( !access_has_bugnote_level( config_get( 'delete_bugnote_threshold' ), $p_issue_note_id ) ) {
			return mci_soap_fault_access_denied( $t_user_id );
		}
	}

	if( bug_is_readonly( $t_issue_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id, "Issue '$t_issue_id' is readonly" );
	}

	return bugnote_delete( $p_issue_note_id );
}

/**
 * Update a note
 *
 * @param string $p_username  The name of the user trying to add a note to an issue.
 * param string $p_password  The password of the user.
 * @param IssueNoteData $p_note  The note to update.
 * @return true on success, false on failure
 */
function mc_issue_note_update( $p_username, $p_password, $p_note ) {
	global $g_project_override;

	$t_user_id = mci_check_login( $p_username, $p_password );

	if ( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	$p_note = SoapObjectsFactory::unwrapObject( $p_note );

	if ( !isset( $p_note['id'] ) || is_blank( $p_note['id'] ) ) {
		return SoapObjectsFactory::newSoapFault('Client', "Issue note id must not be blank." );
	}

	if ( !isset( $p_note['text'] ) || is_blank( $p_note['text'] ) ) {
		return SoapObjectsFactory::newSoapFault('Client', "Issue note text must not be blank." );
	}

	$t_issue_note_id = $p_note['id'];

	if ( !bugnote_exists( $t_issue_note_id ) ) {
		return SoapObjectsFactory::newSoapFault('Client', "Issue note '$t_issue_note_id' does not exist." );
	}

	$t_issue_id = bugnote_get_field( $t_issue_note_id, 'bug_id' );
	$t_project_id = bug_get_field( $t_issue_id, 'project_id' );
	$g_project_override = $t_project_id;

	if ( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	$t_issue_author_id = bugnote_get_field( $t_issue_note_id, 'reporter_id' );

	# Check if the user owns the bugnote and is allowed to update their own bugnotes
	# regardless of the update_bugnote_threshold level.
	$t_user_owns_the_bugnote = bugnote_is_user_reporter( $t_issue_note_id, $t_user_id );
	$t_user_can_update_own_bugnote = config_get( 'bugnote_allow_user_edit_delete', null, $t_user_id, $t_project_id );
	if ( $t_user_owns_the_bugnote && !$t_user_can_update_own_bugnote ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	# Check if the user has an access level beyond update_bugnote_threshold for the
	# project containing the bugnote to update.
	$t_update_bugnote_threshold = config_get( 'update_bugnote_threshold', null, $t_user_id, $t_project_id );
	if ( !$t_user_owns_the_bugnote && !access_has_bugnote_level( $t_update_bugnote_threshold, $t_issue_note_id, $t_user_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	# Check if the bug is readonly
	if ( bug_is_readonly( $t_issue_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id, "Issue ' . $t_issue_id . ' is readonly" );
	}

	if ( isset( $p_note['view_state'] ) ) {
		$t_view_state = $p_note['view_state'];
		$t_view_state_id = mci_get_enum_id_from_objectref( 'view_state', $t_view_state );
		bugnote_set_view_state( $t_issue_note_id, $t_view_state_id == VS_PRIVATE );
	}

	bugnote_set_text( $t_issue_note_id, $p_note['text'] );

	return bugnote_date_update( $t_issue_note_id );
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
    global $g_project_override;
    $t_user_id = mci_check_login( $p_username, $p_password );

	$p_relationship = SoapObjectsFactory::unwrapObject( $p_relationship );

	$t_dest_issue_id = $p_relationship['target_id'];
	$t_rel_type = SoapObjectsFactory::unwrapObject( $p_relationship['type'] );

	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	$t_project_id = bug_get_field( $p_issue_id, 'project_id' );
	$g_project_override = $t_project_id;
	if( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	# user has access to update the bug...
	if( !access_has_bug_level( config_get( 'update_bug_threshold' ), $p_issue_id, $t_user_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id, "Active user does not have access level required to add a relationship to this issue" );
	}

	# source and destination bugs are the same bug...
	if( $p_issue_id == $t_dest_issue_id ) {
		return SoapObjectsFactory::newSoapFault( 'Client', "An issue can't be related to itself." );
	}

	# the related bug exists...
	if( !bug_exists( $t_dest_issue_id ) ) {
		return SoapObjectsFactory::newSoapFault( 'Client', "Issue '$t_dest_issue_id' not found." );
	}

	# bug is not read-only...
	if( bug_is_readonly( $p_issue_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id, "Issue '$p_issue_id' is readonly" );
	}

	# user can access to the related bug at least as viewer...
	if( !access_has_bug_level( VIEWER, $t_dest_issue_id, $t_user_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id, "The issue '$t_dest_issue_id' requires higher access level" );
	}

	$t_old_id_relationship = relationship_same_type_exists( $p_issue_id, $t_dest_issue_id, $t_rel_type['id'] );

	if( $t_old_id_relationship == 0 ) {
		relationship_add( $p_issue_id, $t_dest_issue_id, $t_rel_type['id'] );

		// The above function call into MantisBT does not seem to return a valid BugRelationshipData object.
		// So we call db_insert_id in order to find the id of the created relationship.
		$t_relationship_id = db_insert_id( db_get_table( 'mantis_bug_relationship_table' ) );

		# Add log line to the history (both bugs)
		history_log_event_special( $p_issue_id, BUG_ADD_RELATIONSHIP, $t_rel_type['id'], $t_dest_issue_id );
		history_log_event_special( $t_dest_issue_id, BUG_ADD_RELATIONSHIP, relationship_get_complementary_type( $t_rel_type['id'] ), $p_issue_id );

		# update bug last updated for both bugs
		bug_update_date( $p_issue_id );
		bug_update_date( $t_dest_issue_id );

		# send email notification to the users addressed by both the bugs
		email_relationship_added( $p_issue_id, $t_dest_issue_id, $t_rel_type['id'] );
		email_relationship_added( $t_dest_issue_id, $p_issue_id, relationship_get_complementary_type( $t_rel_type['id'] ) );

		return $t_relationship_id;
	} else {
		return SoapObjectsFactory::newSoapFault( 'Client', "Relationship already exists." );
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
	global $g_project_override;

    $t_user_id = mci_check_login( $p_username, $p_password );

	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	$t_project_id = bug_get_field( $p_issue_id, 'project_id' );
	$g_project_override = $t_project_id;
	if( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	# user has access to update the bug...
	if( !access_has_bug_level( config_get( 'update_bug_threshold' ), $p_issue_id, $t_user_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id , "Active user does not have access level required to remove a relationship from this issue." );
	}

	# bug is not read-only...
	if( bug_is_readonly( $p_issue_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id , "Issue '$p_issue_id' is readonly." );
	}

	# retrieve the destination bug of the relationship
	$t_dest_issue_id = relationship_get_linked_bug_id( $p_relationship_id, $p_issue_id );

	# user can access to the related bug at least as viewer, if it's exist...
	if( bug_exists( $t_dest_issue_id ) ) {
		if( !access_has_bug_level( VIEWER, $t_dest_issue_id, $t_user_id ) ) {
			return mci_soap_fault_access_denied( $t_user_id , "The issue '$t_dest_issue_id' requires higher access level." );
		}
	}

	$t_bug_relationship_data = relationship_get( $p_relationship_id );
	$t_rel_type = $t_bug_relationship_data->type;

	# delete relationship from the DB
	relationship_delete( $p_relationship_id );

	# update bug last updated
	bug_update_date( $p_issue_id );
	bug_update_date ( $t_dest_issue_id );

	# set the rel_type for both bug and dest_bug based on $t_rel_type and on who is the dest bug
	if( $p_issue_id == $t_bug_relationship_data->src_bug_id ) {
		$t_bug_rel_type = $t_rel_type;
		$t_dest_bug_rel_type = relationship_get_complementary_type( $t_rel_type );
	} else {
		$t_bug_rel_type = relationship_get_complementary_type( $t_rel_type );
		$t_dest_bug_rel_type = $t_rel_type;
	}

	# send email and update the history for the src issue
	history_log_event_special( $p_issue_id, BUG_DEL_RELATIONSHIP, $t_bug_rel_type, $t_dest_issue_id );
	email_relationship_deleted( $p_issue_id, $t_dest_issue_id, $t_bug_rel_type );

	if( bug_exists( $t_dest_issue_id ) ) {

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
function mc_issue_checkin( $p_username, $p_password, $p_issue_id, $p_comment, $p_fixed ) {
    global $g_project_override;

	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	if( !bug_exists( $p_issue_id ) ) {
		return SoapObjectsFactory::newSoapFault( 'Client', "Issue '$p_issue_id' not found." );
	}

	$t_project_id = bug_get_field( $p_issue_id, 'project_id' );
	$g_project_override = $t_project_id;
	if( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	helper_call_custom_function( 'checkin', array( $p_issue_id, $p_comment, '', '', $p_fixed ) );

	return true;
}

/**
 * Returns an array for SOAP encoding from a BugData object
 *
 * @param BugData $p_issue_data
 * @param int $p_user_id
 * @param string $p_lang
 * @return array The issue as an array
 */
function mci_issue_data_as_array( $p_issue_data, $p_user_id, $p_lang ) {

		$t_id = $p_issue_data->id;

		$t_issue = array();
		$t_issue['id'] = $t_id;
		$t_issue['view_state'] = mci_enum_get_array_by_id( $p_issue_data->view_state, 'view_state', $p_lang );
		$t_issue['last_updated'] = SoapObjectsFactory::newDateTimeVar( $p_issue_data->last_updated );

		$t_issue['project'] = mci_project_as_array_by_id( $p_issue_data->project_id );
		$t_issue['category'] = mci_get_category( $p_issue_data->category_id );
		$t_issue['priority'] = mci_enum_get_array_by_id( $p_issue_data->priority, 'priority', $p_lang );
		$t_issue['severity'] = mci_enum_get_array_by_id( $p_issue_data->severity, 'severity', $p_lang );
		$t_issue['status'] = mci_enum_get_array_by_id( $p_issue_data->status, 'status', $p_lang );

		$t_issue['reporter'] = mci_account_get_array_by_id( $p_issue_data->reporter_id );
		$t_issue['summary'] = mci_sanitize_xml_string( $p_issue_data->summary );
		$t_issue['version'] = mci_null_if_empty( $p_issue_data->version );
		$t_issue['build'] = mci_null_if_empty( $p_issue_data->build );
		$t_issue['profile_id'] = mci_null_if_empty( $p_issue_data->profile_id );
		$t_issue['platform'] = mci_null_if_empty( $p_issue_data->platform );
		$t_issue['os'] = mci_null_if_empty( $p_issue_data->os );
		$t_issue['os_build'] = mci_null_if_empty( $p_issue_data->os_build );
		$t_issue['reproducibility'] = mci_enum_get_array_by_id( $p_issue_data->reproducibility, 'reproducibility', $p_lang );
		$t_issue['date_submitted'] = SoapObjectsFactory::newDateTimeVar( $p_issue_data->date_submitted );
		$t_issue['sticky'] = $p_issue_data->sticky;

		$t_issue['sponsorship_total'] = $p_issue_data->sponsorship_total;

		if( !empty( $p_issue_data->handler_id ) ) {
			$t_issue['handler'] = mci_account_get_array_by_id( $p_issue_data->handler_id );
		}
		$t_issue['projection'] = mci_enum_get_array_by_id( $p_issue_data->projection, 'projection', $p_lang );
		$t_issue['eta'] = mci_enum_get_array_by_id( $p_issue_data->eta, 'eta', $p_lang );

		$t_issue['resolution'] = mci_enum_get_array_by_id( $p_issue_data->resolution, 'resolution', $p_lang );
		$t_issue['fixed_in_version'] = mci_null_if_empty( $p_issue_data->fixed_in_version );
		$t_issue['target_version'] = mci_null_if_empty( $p_issue_data->target_version );

		$t_issue['description'] = mci_sanitize_xml_string( bug_get_text_field( $t_id, 'description' ) );

		$t_steps_to_reproduce = bug_get_text_field( $t_id, 'steps_to_reproduce' );
		$t_issue['steps_to_reproduce'] = mci_null_if_empty( mci_sanitize_xml_string ($t_steps_to_reproduce) );

		$t_additional_information = bug_get_text_field( $t_id, 'additional_information' );
		$t_issue['additional_information'] = mci_null_if_empty( mci_sanitize_xml_string( $t_additional_information ) );

		$t_issue['due_date'] = SoapObjectsFactory::newDateTimeVar( $p_issue_data->due_date );

		$t_issue['attachments'] = mci_issue_get_attachments( $p_issue_data->id );
		$t_issue['relationships'] = mci_issue_get_relationships( $p_issue_data->id, $p_user_id );
		$t_issue['notes'] = mci_issue_get_notes( $p_issue_data->id );
		$t_issue['custom_fields'] = mci_issue_get_custom_fields( $p_issue_data->id );
		$t_issue['tags'] = mci_issue_get_tags_for_bug_id( $p_issue_data->id, $p_user_id );
		$t_issue['monitors'] = mci_account_get_array_by_ids( bug_get_monitors ( $p_issue_data->id ) );

		return $t_issue;
}

function mci_issue_get_tags_for_bug_id( $p_bug_id, $p_user_id ) {

	if ( !access_has_global_level( config_get( 'tag_view_threshold' ), $p_user_id ) )
		return array();

	$t_tag_rows = tag_bug_get_attached( $p_bug_id );
	$t_result = array();

	foreach ( $t_tag_rows as $t_tag_row ) {
		$t_result[] = array (
			'id' => $t_tag_row['id'],
			'name' => $t_tag_row['name']
		);
	}

	return $t_result;
}

/**
 * Returns an array for SOAP encoding from a BugData object
 *
 * @param BugData $p_issue_data
 * @return array The issue header data as an array
 */
function mci_issue_data_as_header_array( $p_issue_data ) {

		$t_issue = array();

		$t_id = $p_issue_data->id;

		$t_issue['id'] = $t_id;
		$t_issue['view_state'] = $p_issue_data->view_state;
		$t_issue['last_updated'] = SoapObjectsFactory::newDateTimeVar( $p_issue_data->last_updated );

		$t_issue['project'] = $p_issue_data->project_id;
		$t_issue['category'] = mci_get_category( $p_issue_data->category_id );
		$t_issue['priority'] = $p_issue_data->priority;
		$t_issue['severity'] = $p_issue_data->severity;
		$t_issue['status'] = $p_issue_data->status;

		$t_issue['reporter'] = $p_issue_data->reporter_id;
		$t_issue['summary'] = mci_sanitize_xml_string( $p_issue_data->summary );
		if( !empty( $p_issue_data->handler_id ) ) {
			$t_issue['handler'] = $p_issue_data->handler_id;
		} else {
			$t_issue['handler'] = null;
		}
		$t_issue['resolution'] = $p_issue_data->resolution;

		$t_issue['attachments_count'] = count( mci_issue_get_attachments( $p_issue_data->id ) );
		$t_issue['notes_count'] = count( mci_issue_get_notes( $p_issue_data->id ) );

		return $t_issue;
}
