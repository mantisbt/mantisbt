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

require_once( dirname( __FILE__ ) . '/mc_core.php' );

use Mantis\Exceptions\ClientException;

/**
 * Check if an issue with the given id exists.
 *
 * @param string  $p_username The name of the user trying to access the issue.
 * @param string  $p_password The password of the user.
 * @param integer $p_issue_id The id of the issue to check.
 * @return boolean true if there is an issue with the given id, false otherwise.
 */
function mc_issue_exists( $p_username, $p_password, $p_issue_id ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_fault_login_failed();
	}

	if( !bug_exists( $p_issue_id ) ) {
		return false;
	}

	$t_project_id = bug_get_field( $p_issue_id, 'project_id' );
	if( !mci_has_readonly_access( $t_user_id, $t_project_id ) ) {

		# if we return an error here, then we answered the question!
		return false;
	}

	return true;
}

/**
 * Get all details about an issue.
 *
 * @param string  $p_username The name of the user trying to access the issue.
 * @param string  $p_password The password of the user.
 * @param integer $p_issue_id The id of the issue to retrieve.
 * @return array that represents an IssueData structure
 */
function mc_issue_get( $p_username, $p_password, $p_issue_id ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_fault_login_failed();
	}

	$t_lang = mci_get_user_lang( $t_user_id );
	$t_project_id = bug_get_field( $p_issue_id, 'project_id' );

	if( !mci_has_readonly_access( $t_user_id, $t_project_id ) ) {
		return mci_fault_access_denied( $t_user_id );
	}

	if( !access_has_bug_level( config_get( 'view_bug_threshold', null, null, $t_project_id ), $p_issue_id, $t_user_id ) ) {
		return mci_fault_access_denied( $t_user_id );
	}

	log_event( LOG_WEBSERVICE, 'getting details for issue \'' . $p_issue_id . '\'' );

	$t_bug = bug_get( $p_issue_id, true );
	$t_issue_data = mci_issue_data_as_array( $t_bug, $t_user_id, $t_lang );
	return $t_issue_data;
}

/**
 * Get history details for an issue that is visible to specified user.
 * This is used by REST APIs and assumes that user access to issue is
 * already done.
 *
 * @param integer $p_issue_id The issue id.
 * @param integer $p_user_id The user id.
 * @param string $p_lang The user language.
 * @return array history entries or empty if user has no access to history.
 */
function mci_issue_get_history( $p_issue_id, $p_user_id, $p_lang ) {
	$t_project_id = bug_get_field( $p_issue_id, 'project_id' );

	$t_view_history_threshold = config_get( 'view_history_threshold', null, null, $t_project_id );
	if( !access_has_bug_level( $t_view_history_threshold, $p_issue_id, $p_user_id ) ) {
		return array();
	}

	$t_history_rows = history_get_raw_events_array( $p_issue_id, $p_user_id );

	$t_history = array();

	$t_files = file_get_visible_attachments( $p_issue_id );

	foreach( $t_history_rows as $t_history_row ) {
		$t_type = (int)$t_history_row['type'];

		$t_skip = false;

		switch( $t_type ) {
			case BUG_ADD_SPONSORSHIP:         # Deprecated, not exposed in REST API
			case BUG_UPDATE_SPONSORSHIP:      # Deprecated, not exposed in REST API
			case BUG_DELETE_SPONSORSHIP:      # Deprecated, not exposed in REST API
			case BUG_REVISION_DROPPED:        # Not Supported
			case BUGNOTE_REVISION_DROPPED:    # Not Supported
				$t_skip = true;
				break;
		}

		$t_event = array();

		$t_event['created_at'] = ApiObjectFactory::datetime( $t_history_row['date'] );
		$t_event['user'] = mci_account_get_array_by_id( $t_history_row['userid'] );

		$t_field = $t_history_row['field'];
		if( !is_blank( $t_field ) ) {
			# map field names to external names
			switch( $t_field )  {
				case 'reporter_id':
					$t_field = 'reporter';
					break;
				case 'handler_id':
					$t_field = 'handler';
					break;
				case 'sponsorship_total':
					# Skip sponsorship fields
					$t_skip = true;
					break;
			}

			$t_event['field'] = array(
				'name' => $t_field,
				'label' => history_localize_field_name( $t_history_row['field'] ) );
		}

		if( $t_skip ) {
			continue;
		}

		$t_event['type'] = array(
			'id' => $t_type,
			'name' => history_get_type_name( $t_history_row['type'] ) );

		$t_old_value_name = 'old_value';
		$t_new_value_name = 'new_value';

		$t_show_old_value = true;
		$t_show_new_value = true;

		switch( $t_type ) {
			case NEW_BUG:
			case DESCRIPTION_UPDATED:
			case ADDITIONAL_INFO_UPDATED:
			case STEP_TO_REPRODUCE_UPDATED:
				$t_show_old_value = false;
				$t_show_new_value = false;
				break;
			case TAG_ATTACHED:
			case TAG_DETACHED:
				$t_show_new_value = false;
				$t_old_value_name = 'tag';
				break;
			case FILE_ADDED:
			case FILE_DELETED:
				$t_show_new_value = false;
				$t_old_value_name = 'file';
				break;
			case BUGNOTE_ADDED:
			case BUGNOTE_UPDATED:
			case BUGNOTE_DELETED:
				$t_show_new_value = false;
				$t_old_value_name = 'note';
				break;
			case BUGNOTE_STATE_CHANGED:
				$t_old_value_name = 'view_state';
				$t_new_value_name = 'note';
				break;
			case BUG_ADD_RELATIONSHIP:
			case BUG_REPLACE_RELATIONSHIP:
			case BUG_DEL_RELATIONSHIP:
				$t_old_value_name = 'relationship';
				$t_new_value_name = 'issue';
				break;
			case BUG_CLONED_TO:
				$t_show_old_value = false;
				$t_new_value_name = 'issue';
				break;
		}

		$fn_process_value = function( $p_issue_id, $p_type, $p_field, $p_value, $p_lang, $p_new_value ) use ( $t_files ) {
			if( is_blank( $p_value ) ) {
				return '';
			}

			switch( $p_type ) {
				case TAG_ATTACHED:
				case TAG_DETACHED:
					$t_tag = tag_get_by_name( $p_value );
					if( $t_tag === false ) {
						return array( 'name' => $p_value );
					}

					return array( 'id' => $t_tag['id'], 'name' => $t_tag['name'] );
				case BUGNOTE_ADDED:
				case BUGNOTE_DELETED:
					return array( 'id' => (int)$p_value );
				case BUGNOTE_UPDATED:
					if( !$p_new_value ) {
						return array( 'id' => (int) $p_value );
					}

					return '';
				case FILE_ADDED:
				case FILE_DELETED:
					$t_value = array();

					$t_id = null;
					foreach( $t_files as $t_file ) {
						if( $t_file['display_name'] == $p_value ) {
							$t_value['id'] = (int)$t_file['id'];
							break;
						}
					}

					$t_value['filename'] = $p_value;

					return $t_value;
				case BUGNOTE_STATE_CHANGED:
					if( $p_new_value ) {
						return array( 'id' => (int)$p_value );
					}

					return mci_enum_get_array_by_id( (int)$p_value, 'view_state', $p_lang );
				case BUG_ADD_RELATIONSHIP:
				case BUG_REPLACE_RELATIONSHIP:
				case BUG_DEL_RELATIONSHIP:
					if( $p_new_value ) {
						return array( 'id' => (int)$p_value );
					}

					return array(
						'id' => (int)$p_value,
						'name' => relationship_get_name_for_api( (int)$p_value ),
						'label' => relationship_get_description_for_history( (int)$p_value ) );
				case BUG_CLONED_TO:
					return array( 'id' => (int)$p_value );
			}

			switch( $p_field ) {
				case 'status':
					$t_value = mci_enum_get_array_by_id( (int)$p_value, 'status', $p_lang );
					break;
				case 'priority':
					$t_value = mci_enum_get_array_by_id( (int)$p_value, 'priority', $p_lang );
					break;
				case 'severity':
					$t_value = mci_enum_get_array_by_id( (int)$p_value, 'severity', $p_lang );
					break;
				case 'view_state':
					$t_value = mci_enum_get_array_by_id( (int)$p_value, 'view_state', $p_lang );
					break;
				case 'resolution':
					$t_value = mci_enum_get_array_by_id( (int)$p_value, 'resolution', $p_lang );
					break;
				case 'reproducibility':
					$t_value = mci_enum_get_array_by_id( (int)$p_value, 'reproducibility', $p_lang );
					break;
				case 'reporter':
				case 'handler':
					$t_value = mci_account_get_array_by_id( (int)$p_value );
					break;
				default:
					$t_value = $p_value;
					break;
			}

			return $t_value;
		};

		if( $t_show_old_value ) {
			$t_event[$t_old_value_name] = $fn_process_value( $p_issue_id, $t_type, $t_field, $t_history_row['old_value'], $p_lang, false );
		}

		if( $t_show_new_value ) {
			$t_event[$t_new_value_name] = $fn_process_value( $p_issue_id, $t_type, $t_field, $t_history_row['new_value'], $p_lang, true );
		}

		$t_localized_row = history_localize_item(
			$p_issue_id,
			$t_history_row['field'],
			$t_history_row['type'],
			$t_history_row['old_value'],
			$t_history_row['new_value'],
			false
		);

		$t_event['message'] = $t_localized_row['note'];

		if( !is_blank( $t_localized_row['change'] ) ) {
			$t_event['change'] = $t_localized_row['change'];
		}

		$t_history[] = $t_event;
	}

	return $t_history;
}

/**
* Get history details about an issue, used by SOAP APIs.
*
* @param string  $p_username The name of the user trying to access the issue.
* @param string  $p_password The password of the user.
* @param integer $p_issue_id The id of the issue to retrieve.
* @return array that represents a HistoryDataArray structure
*/
function mc_issue_get_history( $p_username, $p_password, $p_issue_id ) {
	global $g_project_override;

	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_fault_login_failed();
	}

	if( !bug_exists( $p_issue_id ) ) {
		return ApiObjectFactory::faultNotFound( "Issue '$p_issue_id' does not exist" );
	}

	$t_project_id = bug_get_field( $p_issue_id, 'project_id' );
	if( !mci_has_readonly_access( $t_user_id, $t_project_id ) ) {
		return mci_fault_access_denied( $t_user_id );
	}
	$g_project_override = $t_project_id;

	if( !access_has_bug_level( config_get( 'view_bug_threshold', null, null, $t_project_id ), $p_issue_id, $t_user_id ) ) {
		return mci_fault_access_denied( $t_user_id );
	}

	$t_user_access_level = user_get_access_level( $t_user_id, $t_project_id );
	if( !access_compare_level( $t_user_access_level, config_get( 'view_history_threshold' ) ) ) {
		return mci_fault_access_denied( $t_user_id );
	}

	log_event( LOG_WEBSERVICE, 'retrieving history for issue \'' . $p_issue_id . '\'' );

	$t_bug_history = history_get_raw_events_array( $p_issue_id, $t_user_id );

	return $t_bug_history;
}

/**
 * Get due date for a given bug
 * @param BugData $p_bug A BugData object.
 * @return soapval the value to be encoded as the due date
 */
function mci_issue_get_due_date( BugData $p_bug ) {
	$t_value = null;

	if( access_has_bug_level( config_get( 'due_date_view_threshold' ), $p_bug->id )  && !date_is_null( $p_bug->due_date ) ) {
		$t_value = $p_bug->due_date;
	}

	return ApiObjectFactory::datetime( $t_value );
}

/**
 * Sets the supplied array of custom field values to the specified issue id.
 *
 * @param integer $p_issue_id       Issue id to apply custom field values to.
 * @param array   &$p_custom_fields The array of custom field values as described in the webservice complex types.
 * @param boolean $p_log_insert     Create history logs for new values.
 * @return boolean|SoapFault|RestFault true for success, otherwise fault.
 */
function mci_issue_set_custom_fields( $p_issue_id, array &$p_custom_fields = null, $p_log_insert = true ) {
	# set custom field values on the submitted issue
	if( isset( $p_custom_fields ) && is_array( $p_custom_fields ) ) {
		foreach( $p_custom_fields as $t_custom_field ) {
			$t_custom_field = ApiObjectFactory::objectToArray( $t_custom_field );

			# Verify validity of custom field specification
			$t_msg = 'Invalid Custom field specification';
			$t_valid_cf = isset( $t_custom_field['field'] ) && isset( $t_custom_field['value'] );
			if( $t_valid_cf ) {
				$t_field = ApiObjectFactory::objectToArray( $t_custom_field['field'] );
				if( ( !isset( $t_field['id'] ) || $t_field['id'] == 0 ) && !isset( $t_field['name'] ) ) {
					$t_valid_cf = false;
					$t_msg .= ", either 'name' or 'id' != 0 or must be given.";
				}
			}

			if( !$t_valid_cf ) {
				throw new ClientException(
					$t_msg,
					ERROR_EMPTY_FIELD,
					"custom_field['id']" );
			}

			# get custom field id from object ref
			$t_custom_field_id = mci_get_custom_field_id_from_objectref( (object)$t_custom_field['field'] );
			if( $t_custom_field_id == 0 ) {
				throw new ClientException(
					"Custom field '" . $t_field['name'] . "' not found.",
					ERROR_CUSTOM_FIELD_NOT_FOUND,
					"custom_field['id']" );
			}

			# skip if current user doesn't have login access.
			if( !custom_field_has_write_access( $t_custom_field_id, $p_issue_id ) ) {
				continue;
			}

			$t_custom_field_def = custom_field_get_definition( $t_custom_field_id );
			$t_name = $t_custom_field_def['name'];
			$t_value = $t_custom_field['value'];

			if( !custom_field_validate( $t_custom_field_id, $t_value ) ) {
				throw new ClientException(
					"Invalid value for custom field '$t_name'",
					ERROR_INVALID_FIELD_VALUE,
					"value" );
			}

			if( !custom_field_set_value( $t_custom_field_id, $p_issue_id, $t_value, $p_log_insert ) ) {
				throw new ClientException(
					"Invalid value for custom field '$t_name'.",
					ERROR_INVALID_FIELD_VALUE,
					"value" );
			}
		}
	}

	return true;
}

/**
 * Get the custom field values associated with the specified issue id.
 *
 * @param integer $p_issue_id Issue id to get the custom field values for.
 *
 * @return null if no custom field defined for the project that contains the issue, or if no custom
 *              fields are accessible to the current user.
 */
function mci_issue_get_custom_fields( $p_issue_id ) {
	$t_project_id = bug_get_field( $p_issue_id, 'project_id' );

	$t_custom_fields = array();
	$t_related_custom_field_ids = custom_field_get_linked_ids( $t_project_id );
	custom_field_cache_array_rows( $t_related_custom_field_ids );

	# filter out fields not accesible by the user
	foreach( $t_related_custom_field_ids as $t_index => $t_id ) {
		if( !custom_field_has_read_access( $t_id, $p_issue_id ) ) {
			unset( $t_related_custom_field_ids[$t_index] );
		}
	}
	custom_field_cache_values( array( $p_issue_id ), $t_related_custom_field_ids );

	foreach( $t_related_custom_field_ids as $t_id ) {
		$t_def = custom_field_get_definition( $t_id );

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
		$t_custom_field_value['field']['id'] = (int)$t_id;
		$t_custom_field_value['field']['name'] = $t_def['name'];
		$t_custom_field_value['value'] = $t_value;

		$t_custom_fields[] = $t_custom_field_value;
	}

	return count( $t_custom_fields ) == 0 ? null : $t_custom_fields;
}

/**
 * Get the attachments of an issue.
 *
 * @param integer $p_issue_id The id of the issue to retrieve the attachments for.
 * @param integer $p_note_id 0 for issue attachments, an id for note attachments, null for all
 * @return array that represents an AttachmentData structure
 */
function mci_issue_get_attachments( $p_issue_id, $p_note_id = null ) {
	$t_attachment_rows = file_get_visible_attachments( $p_issue_id );
	if( $t_attachment_rows == null ) {
		return array();
	}

	$t_result = array();
	foreach( $t_attachment_rows as $t_attachment_row ) {
		# Filter out attachments that are not requested by caller
		if( !is_null( $p_note_id ) && (int)$t_attachment_row['bugnote_id'] != (int)$p_note_id ) {
			continue;
		}

		$t_attachment = array();
		$t_attachment['id'] = (int)$t_attachment_row['id'];

		$t_created_at = ApiObjectFactory::datetime( $t_attachment_row['date_added'] );

		if( ApiObjectFactory::$soap ) {
			$t_attachment['user_id'] = (int)$t_attachment_row['user_id'];
			$t_attachment['date_submitted'] = $t_created_at;
		} else {
			$t_attachment['reporter'] = mci_account_get_array_by_id( $t_attachment_row['user_id'] );
			$t_attachment['created_at'] = $t_created_at;
		}

		$t_attachment['filename'] = $t_attachment_row['display_name'];
		$t_attachment['size'] = (int)$t_attachment_row['size'];
		$t_attachment['content_type'] = $t_attachment_row['file_type'];

		if( ApiObjectFactory::$soap ) {
			$t_attachment['download_url'] = mci_get_mantis_path() . 'file_download.php?file_id=' . $t_attachment_row['id'] . '&amp;type=bug';
		}

		$t_result[] = $t_attachment;
	}

	return $t_result;
}

/**
 * Get the relationships of an issue.
 *
 * @param integer $p_issue_id The id of the issue to retrieve the relationships for.
 * @param integer $p_user_id  The user id of the user trying to access the information.
 * @return array that represents an RelationShipData structure
 */
function mci_issue_get_relationships( $p_issue_id, $p_user_id ) {
	$t_relationships = array();

	$t_src_relationships = relationship_get_all_src( $p_issue_id );
	foreach( $t_src_relationships as $t_relship_row ) {
		if( access_has_bug_level( config_get( 'webservice_readonly_access_level_threshold' ), $t_relship_row->dest_bug_id, $p_user_id ) ) {
			$t_related_issue_id = (int)$t_relship_row->dest_bug_id;

			$t_relationship = array();
			$t_reltype = array();
			$t_relationship['id'] = (int)$t_relship_row->id;
			$t_reltype['id'] = (int)$t_relship_row->type;

			if( ApiObjectFactory::$soap ) {
				$t_reltype['name'] = relationship_get_description_src_side( $t_relship_row->type );
			} else {
				$t_reltype['name'] = relationship_get_name_for_api( $t_relship_row->type );
				$t_reltype['label'] = relationship_get_description_src_side( $t_relship_row->type );
			}

			$t_relationship['type'] = $t_reltype;

			if( ApiObjectFactory::$soap ) {
				$t_relationship['target_id'] = $t_related_issue_id;
			} else {
				$t_relationship['issue'] = mci_related_issue_as_array_by_id( $t_related_issue_id );
			}

			$t_relationships[] = $t_relationship;
		}
	}

	$t_dest_relationships = relationship_get_all_dest( $p_issue_id );
	foreach( $t_dest_relationships as $t_relship_row ) {
		if( access_has_bug_level( config_get( 'webservice_readonly_access_level_threshold' ), $t_relship_row->src_bug_id, $p_user_id ) ) {
			$t_relationship = array();
			$t_relationship['id'] = (int)$t_relship_row->id;
			$t_reltype = array();
			$t_complementary_type_id = (int)relationship_get_complementary_type( $t_relship_row->type );
			$t_reltype['id'] = $t_complementary_type_id;

			if( ApiObjectFactory::$soap ) {
				$t_reltype['name'] = relationship_get_description_dest_side( $t_relship_row->type );
			} else {
				$t_reltype['name'] = relationship_get_name_for_api( $t_complementary_type_id );
				$t_reltype['label'] = relationship_get_description_dest_side( $t_relship_row->type );
			}

			$t_relationship['type'] = $t_reltype;
			$t_related_issue_id = (int)$t_relship_row->src_bug_id;

			if( ApiObjectFactory::$soap ) {
				$t_relationship['target_id'] = $t_related_issue_id;
			} else {
				$t_relationship['issue'] = mci_related_issue_as_array_by_id( $t_related_issue_id );
			}

			$t_relationships[] = $t_relationship;
		}
	}

	return (count( $t_relationships ) == 0 ? null : $t_relationships );
}

/**
 * Convert a note row into an array.
 * @param $p_bugnote_row The note row object.
 * @return array The note array.
 */
function mci_issue_note_data_as_array( $p_bugnote_row ) {
	$t_user_id = auth_get_current_user_id();
	$t_lang = mci_get_user_lang( $t_user_id );
	$t_has_time_tracking_access = access_has_bug_level( config_get( 'time_tracking_view_threshold' ), $p_bugnote_row->bug_id );

	$t_bugnote = array();
	$t_bugnote['id'] = (int)$p_bugnote_row->id;
	$t_bugnote['reporter'] = mci_account_get_array_by_id( $p_bugnote_row->reporter_id );
	$t_bugnote['text'] = mci_sanitize_xml_string( $p_bugnote_row->note );
	$t_bugnote['view_state'] = mci_enum_get_array_by_id( $p_bugnote_row->view_state, 'view_state', $t_lang );
	$t_bugnote['time_tracking'] = $t_has_time_tracking_access ? $p_bugnote_row->time_tracking : 0;

	$t_created_at = ApiObjectFactory::datetimeString( $p_bugnote_row->date_submitted );
	$t_modified_at = ApiObjectFactory::datetimeString( $p_bugnote_row->last_modified );

	if( ApiObjectFactory::$soap ) {
		$t_bugnote['note_type'] = $p_bugnote_row->note_type;
		$t_bugnote['note_attr'] = $p_bugnote_row->note_attr;

		$t_bugnote['date_submitted'] = $t_created_at;
		$t_bugnote['last_modified'] = $t_modified_at;
	} else {
		$t_bugnote['attachments'] = mci_issue_get_attachments( $p_bugnote_row->bug_id, $p_bugnote_row->id );

		switch( $p_bugnote_row->note_type ) {
			case REMINDER:
				$t_type = 'reminder';
				break;
			case TIME_TRACKING:
				$t_type = $t_has_time_tracking_access ? 'timelog' : 'note';
				break;
			case BUGNOTE:
			default:
				$t_type = 'note';
				break;
		}

		$t_bugnote['type'] = $t_type;

		if( !is_blank( $p_bugnote_row->note_attr ) ) {
			$t_bugnote['attr'] = $p_bugnote_row->note_attr;
		}

		if( isset( $t_bugnote['time_tracking'] ) ) {
			if ( $t_bugnote['time_tracking'] == 0 || $t_type != 'timelog' ) {
				unset( $t_bugnote['time_tracking'] );
			} else {
				$t_bugnote['time_tracking'] = array( 'duration' => db_minutes_to_hhmm( $t_bugnote['time_tracking'] ) );
			}	
		}

		$t_bugnote['created_at'] = $t_created_at;
		$t_bugnote['updated_at'] = $t_modified_at;
	}

	return $t_bugnote;
}

/**
 * Get all visible notes for a specific issue
 *
 * @param integer $p_issue_id The id of the issue to retrieve the notes for.
 * @return array that represents an SOAP IssueNoteData structure
 */
function mci_issue_get_notes( $p_issue_id ) {
	$t_user_bugnote_order = 'ASC'; # always get the notes in ascending order for consistency to the calling application.

	$t_result = array();
	foreach( bugnote_get_all_visible_bugnotes( $p_issue_id, $t_user_bugnote_order, 0 ) as $t_value ) {
		$t_bugnote = mci_issue_note_data_as_array( $t_value );
		$t_result[] = $t_bugnote;
	}

	return count( $t_result ) == 0 ? null : $t_result;
}

/**
 * Sets the monitors of the specified issue
 *
 * <p>This functions performs access level checks and only performs operations which would
 * modify the existing monitors list.</p>
 *
 * @param integer $p_issue_id           The issue id to set the monitors for.
 * @param integer $p_requesting_user_id The user which requests the monitor change.
 * @param array   $p_monitors           An array of arrays with the <em>id</em> field set to the id
 *                                      of the users which should monitor this issue.
 * @return mixed
 */
function mci_issue_set_monitors( $p_issue_id, $p_requesting_user_id, array $p_monitors ) {
	if( bug_is_readonly( $p_issue_id ) ) {
		return mci_fault_access_denied( $p_requesting_user_id, 'Issue \'' . $p_issue_id . '\' is readonly' );
	}

	# 1. get existing monitor ids
	$t_existing_monitor_ids = bug_get_monitors( $p_issue_id );

	# 2. build new monitors ids
	$t_new_monitor_ids = array();
	foreach ( $p_monitors as $t_monitor ) {
		$t_monitor = ApiObjectFactory::objectToArray( $t_monitor );
		$t_new_monitor_ids[] = $t_monitor['id'];
	}

	# 3. for each of the new monitor ids, add it if it does not already exist
	foreach( $t_new_monitor_ids as $t_user_id ) {
		if( $p_requesting_user_id == $t_user_id ) {
			if( ! access_has_bug_level( config_get( 'monitor_bug_threshold' ), $p_issue_id ) ) {
				continue;
			}
		} else {
			if( !access_has_bug_level( config_get( 'monitor_add_others_bug_threshold' ), $p_issue_id ) ) {
				continue;
			}
		}

		if( in_array( $t_user_id, $t_existing_monitor_ids ) ) {
			continue;
		}

		bug_monitor( $p_issue_id, $t_user_id );
	}

	# 4. for each of the existing monitor ids, remove it if it is not found in the new monitor ids
	foreach ( $t_existing_monitor_ids as $t_user_id ) {
		if( $p_requesting_user_id == $t_user_id ) {
			if( ! access_has_bug_level( config_get( 'monitor_bug_threshold' ), $p_issue_id ) ) {
				continue;
			}
		} else {
			if( !access_has_bug_level( config_get( 'monitor_delete_others_bug_threshold' ), $p_issue_id ) ) {
				continue;
			}
		}

		if( in_array( $t_user_id, $t_new_monitor_ids ) ) {
			continue;
		}

		bug_unmonitor( $p_issue_id, $t_user_id );
	}
}

/**
 * Get the biggest issue id currently used.
 *
 * @param string  $p_username   The name of the user trying to retrieve the information.
 * @param string  $p_password   The password of the user.
 * @param integer $p_project_id	One of -1 default project, 0 for all projects, otherwise project id.
 * @return integer The biggest used issue id.
 */
function mc_issue_get_biggest_id( $p_username, $p_password, $p_project_id ) {
	global $g_project_override;

	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_fault_login_failed();
	}

	$t_any = defined( 'META_FILTER_ANY' ) ? META_FILTER_ANY : 'any';
	$t_none = defined( 'META_FILTER_NONE' ) ? META_FILTER_NONE : 'none';

	$t_filter = array(
		'category_id' => array(
			'0' => $t_any,
		),
		'severity' => array(
			'0' => $t_any,
		),
		'status' => array(
			'0' => $t_any,
		),
		'highlight_changed' => 0,
		'reporter_id' => array(
			'0' => $t_any,
		),
		'handler_id' => array(
			'0' => $t_any,
		),
		'resolution' => array(
			'0' => $t_any,
		),
		'build' => array(
			'0' => $t_any,
		),
		'version' => array(
			'0' => $t_any,
		),
		'hide_status' => array(
			'0' => $t_none,
		),
		'monitor_user_id' => array(
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

	if( ( $t_project_id > 0 ) && !project_exists( $t_project_id ) ) {
		return ApiObjectFactory::faultNotFound( 'Project \'' . $t_project_id . '\' does not exist.' );
	}

	if( !mci_has_readonly_access( $t_user_id, $t_project_id ) ) {
		return mci_fault_access_denied( $t_user_id );
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
 * @param string $p_username The name of the user trying to retrieve the information.
 * @param string $p_password The password of the user.
 * @param string $p_summary  The summary of the issue to retrieve.
 * @return integer The id of the issue with the given summary, 0 if there is no such issue.
 */
function mc_issue_get_id_from_summary( $p_username, $p_password, $p_summary ) {
	global $g_project_override;

	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_fault_login_failed();
	}

	$t_query = 'SELECT id FROM {bug} WHERE summary = ' . db_param();

	$t_result = db_query( $t_query, array( $p_summary ), 1 );

	if( db_num_rows( $t_result ) == 0 ) {
		return 0;
	} else {
		while( ( $t_row = db_fetch_array( $t_result ) ) !== false ) {
			$t_issue_id = (int)$t_row['id'];
			$t_project_id = bug_get_field( $t_issue_id, 'project_id' );
			$g_project_override = $t_project_id;

			if( mci_has_readonly_access( $t_user_id, $t_project_id ) &&
				access_has_bug_level( config_get( 'view_bug_threshold', null, null, $t_project_id ), $t_issue_id, $t_user_id ) ) {
				return $t_issue_id;
			}
		}

		# no issue found that belongs to a project that the user has read access to.
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
 * @param integer $p_user_id        The id of the logged in user.
 * @param integer $p_project_id     The id of the project the issue is associated with.
 * @param integer $p_old_handler_id The old handler id.
 * @param integer $p_new_handler_id The new handler id.  0 for not assigned.
 * @return true: access ok, otherwise: soap fault.
 */
function mci_issue_handler_access_check( $p_user_id, $p_project_id, $p_old_handler_id, $p_new_handler_id ) {
	if( $p_new_handler_id != 0 ) {
		if( !user_exists( $p_new_handler_id ) ) {
			return ApiObjectFactory::faultNotFound( 'User \'' . $p_new_handler_id . '\' does not exist.' );
		}

		if( !access_has_project_level( config_get( 'handle_bug_threshold' ), $p_project_id, $p_new_handler_id ) ) {
			return mci_fault_access_denied( $p_new_handler_id, 'User does not have access right to handle issues' );
		}
	}

	if( $p_old_handler_id != $p_new_handler_id ) {
		if( !access_has_project_level( config_get( 'update_bug_assign_threshold' ), $p_project_id, $p_user_id ) ) {
			return mci_fault_access_denied( $p_user_id, 'User does not have access right to assign issues' );
		}
	}

	return true;
}

/**
 * Add an issue to the database.
 *
 * @param string   $p_username The name of the user trying to add the issue.
 * @param string   $p_password The password of the user.
 * @param array|stdClass $p_issue    A IssueData structure containing information about the new issue.
 * @return integer|RestFault|SoapFault The id of the created issue.
 */
function mc_issue_add( $p_username, $p_password, $p_issue ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_fault_login_failed();
	}

	$t_issue = ApiObjectFactory::objectToArray( $p_issue, /* recursive */ true );

	$t_project_id = isset( $t_issue['project'] ) ? mci_get_project_id( $t_issue['project'] ) : ALL_PROJECTS;
	if( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
		return mci_fault_access_denied( $t_user_id );
	}

	$t_data = array(
		'payload' => array( 'issue' => $t_issue )
	);

	$t_command = new IssueAddCommand( $t_data );
	$t_result = $t_command->execute();
	$t_issue_id = (int)$t_result['issue_id'];

	return $t_issue_id;
}

/**
 * Update Issue in database
 *
 * Created By KGB
 * @param string   $p_username The name of the user trying to update the issue.
 * @param string   $p_password The password of the user.
 * @param integer  $p_issue_id The issue id of the existing issue being updated.
 * @param stdClass $p_issue    A IssueData structure containing information about the new issue.
 * @return integer|RestFault|SoapFault The id of the created issue.
 */
function mc_issue_update( $p_username, $p_password, $p_issue_id, stdClass $p_issue ) {
	global $g_project_override;

	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_fault_login_failed();
	}

	if( !bug_exists( $p_issue_id ) ) {
		return ApiObjectFactory::faultNotFound( 'Issue \'' . $p_issue_id . '\' does not exist.' );
	}

	if( bug_is_readonly( $p_issue_id ) ) {
		return ApiObjectFactory::faultForbidden( 'Issue \'' . $p_issue_id . '\' is readonly' );
	}

	$t_project_id = bug_get_field( $p_issue_id, 'project_id' );

	if( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
		return mci_fault_access_denied( $t_user_id );
	}

	$g_project_override = $t_project_id; # ensure that helper_get_current_project() calls resolve to this project id

	$p_issue = ApiObjectFactory::objectToArray( $p_issue );

	# If no project specified, default to the Issue's current project
	if( isset( $p_issue['project'] ) ) {
		$t_project = $p_issue['project'];
		$t_project_id = mci_get_project_id( $t_project );
		if( $t_project_id == 0 ) {
			return ApiObjectFactory::faultNotFound( 'Project \'' . $t_project['name'] . '\' does not exist.' );
		} elseif( !project_exists( $t_project_id ) ) {
			return ApiObjectFactory::faultNotFound( 'Project \'' . $t_project_id . '\' does not exist.' );
		}
	}
	$t_reporter_id = isset( $p_issue['reporter'] ) ? mci_get_user_id( $p_issue['reporter'] )  : $t_user_id ;
	$t_handler_id = isset( $p_issue['handler'] ) ? mci_get_user_id( $p_issue['handler'] ) : 0;
	$t_summary = isset( $p_issue['summary'] ) ? $p_issue['summary'] : '';
	$t_description = isset( $p_issue['description'] ) ? $p_issue['description'] : '';

	if( !access_has_bug_level( config_get( 'update_bug_threshold' ), $p_issue_id, $t_user_id ) ) {
		return mci_fault_access_denied( $t_user_id, 'Not enough rights to update issues' );
	}

	$t_category = isset( $p_issue['category'] ) ? $p_issue['category'] : null;
	$t_category_id = mci_get_category_id( $t_category, $t_project_id );

	$t_version_id = isset( $p_issue['version'] ) ? mci_get_version_id( $p_issue['version'], $t_project_id, 'version' ) : 0;
	$t_fixed_in_version_id = isset( $p_issue['fixed_in_version'] ) ? mci_get_version_id( $p_issue['fixed_in_version'], $t_project_id, 'fixed_in_version' ) : 0;
	$t_target_version_id = isset( $p_issue['target_version'] ) ? mci_get_version_id( $p_issue['target_version'], $t_project_id, 'target_version' ) : 0;

	if( is_blank( $t_summary ) ) {
		return ApiObjectFactory::faultBadRequest( 'Mandatory field \'summary\' is missing.' );
	}

	if( is_blank( $t_description ) ) {
		return ApiObjectFactory::faultBadRequest( 'Mandatory field \'description\' is missing.' );
	}

	# fields which we expect to always be set
	$t_bug_data = bug_get( $p_issue_id, true );
	$t_bug_data->project_id = $t_project_id;
	$t_bug_data->reporter_id = $t_reporter_id;

	# Only check that user can handle the issue if it was modified by the update.
	if( $t_bug_data->handler_id != $t_handler_id ) {
		$t_access_check_result = mci_issue_handler_access_check(
			$t_user_id, $t_project_id, /* old */ $t_bug_data->handler_id, /* new */ $t_handler_id );
		if( $t_access_check_result !== true ) {
			return $t_access_check_result;
		}

		$t_bug_data->handler_id = $t_handler_id;
	}

	$t_bug_data->category_id = $t_category_id;
	$t_bug_data->summary = $t_summary;
	$t_bug_data->description = $t_description;

	# fields which might not be set
	if( isset( $p_issue['steps_to_reproduce'] ) ) {
		$t_bug_data->steps_to_reproduce = $p_issue['steps_to_reproduce'];
	}
	if( isset( $p_issue['additional_information'] ) ) {
		$t_bug_data->additional_information = $p_issue['additional_information'];
	}
	if( isset( $p_issue['priority'] ) ) {
		$t_bug_data->priority = mci_get_priority_id( $p_issue['priority'] );
	}
	if( isset( $p_issue['severity'] ) ) {
		$t_bug_data->severity = mci_get_severity_id( $p_issue['severity'] );
	}
	if( isset( $p_issue['status'] ) ) {
		$t_bug_data->status = mci_get_status_id( $p_issue['status'] );
	}
	if( isset( $p_issue['reproducibility'] ) ) {
		$t_bug_data->reproducibility = mci_get_reproducibility_id( $p_issue['reproducibility'] );
	}
	if( isset( $p_issue['resolution'] ) ) {
		$t_bug_data->resolution = mci_get_resolution_id( $p_issue['resolution'] );
	}
	if( isset( $p_issue['projection'] ) ) {
		$t_bug_data->projection = mci_get_projection_id( $p_issue['projection'] );
	}
	if( isset( $p_issue['eta'] ) ) {
		$t_bug_data->eta = mci_get_eta_id( $p_issue['eta'] );
	}
	if( isset( $p_issue['view_state'] ) ) {
		$t_bug_data->view_state = mci_get_view_state_id( $p_issue['view_state'] );
	}
	if( isset( $p_issue['date_submitted'] ) ) {
		$t_bug_data->date_submitted = $p_issue['date_submitted'];
	}
	if( isset( $p_issue['date_updated'] ) ) {
		$t_bug_data->last_updated = $p_issue['last_updated'];
	}
	if( isset( $p_issue['profile_id'] ) ) {
		$t_bug_data->profile_id = $p_issue['profile_id'];
	}
	if( isset( $p_issue['os'] ) ) {
		$t_bug_data->os = $p_issue['os'];
	}
	if( isset( $p_issue['os_build'] ) ) {
		$t_bug_data->os_build = $p_issue['os_build'];
	}
	if( isset( $p_issue['build'] ) ) {
		$t_bug_data->build = $p_issue['build'];
	}
	if( isset( $p_issue['platform'] ) ) {
		$t_bug_data->platform = $p_issue['platform'];
	}
	if( $t_version_id != 0 ) {
		$t_bug_data->version = version_get_field( $t_version_id, 'version' );
	}
	if( $t_fixed_in_version_id != 0 ) {
		$t_bug_data->fixed_in_version = version_get_field( $t_fixed_in_version_id, 'version' );
	}
	if( $t_target_version_id != 0 && access_has_project_level( config_get( 'roadmap_update_threshold' ), $t_bug_data->project_id, $t_user_id ) ) {
		$t_bug_data->target_version = version_get_field( $t_target_version_id, 'version' );
	}
	if( isset( $p_issue['sticky'] ) && access_has_bug_level( config_get( 'set_bug_sticky_threshold' ), $t_bug_data->id ) ) {
		$t_bug_data->sticky = $p_issue['sticky'];
	}

	if( isset( $p_issue['due_date'] ) &&
		access_has_project_level( config_get( 'due_date_update_threshold' ), $t_bug_data->project_id ) ) {
		$t_bug_data->due_date = strtotime( $p_issue['due_date'] );
	} else {
		$t_bug_data->due_date = date_get_null();
	}

	mci_issue_set_custom_fields( $p_issue_id, $p_issue['custom_fields'], true );

	if( isset( $p_issue['monitors'] ) ) {
		mci_issue_set_monitors( $p_issue_id, $t_user_id, $p_issue['monitors'] );
	}

	if( isset( $p_issue['notes'] ) && is_array( $p_issue['notes'] ) ) {
		$t_bugnotes = bugnote_get_all_visible_bugnotes( $p_issue_id, 'DESC', 0 );
		$t_bugnotes_by_id = array();
		foreach( $t_bugnotes as $t_bugnote ) {
			$t_bugnotes_by_id[$t_bugnote->id] = $t_bugnote;
		}

		foreach( $p_issue['notes'] as $t_note ) {
			$t_note = ApiObjectFactory::objectToArray( $t_note );

			if( isset( $t_note['view_state'] ) ) {
				$t_view_state = $t_note['view_state'];
			} else {
				$t_view_state = config_get( 'default_bugnote_view_status' );
			}

			if( isset( $t_note['id'] ) && ( (int)$t_note['id'] > 0 ) ) {
				$t_bugnote_id = (integer)$t_note['id'];

				$t_view_state_id = mci_get_enum_id_from_objectref( 'view_state', $t_view_state );

				if( array_key_exists( $t_bugnote_id, $t_bugnotes_by_id ) ) {
					$t_bugnote_changed = false;

					if( $t_bugnote->note !== $t_note['text'] ) {
						bugnote_set_text( $t_bugnote_id, $t_note['text'] );
						$t_bugnote_changed = true;
					}

					if( $t_bugnote->view_state != $t_view_state_id ) {
						bugnote_set_view_state( $t_bugnote_id, $t_view_state_id == VS_PRIVATE );
						$t_bugnote_changed = true;
					}

					if( isset( $t_note['time_tracking']) && $t_note['time_tracking'] != $t_bugnote->time_tracking ) {
						bugnote_set_time_tracking( $t_bugnote_id, mci_get_time_tracking_from_note( $p_issue_id, $t_note ) );
						$t_bugnote_changed = true;
					}

					if( $t_bugnote_changed ) {
						bugnote_date_update( $t_bugnote_id );
					}

				}
			} else {
				$t_view_state_id = mci_get_enum_id_from_objectref( 'view_state', $t_view_state );

				$t_note_type = isset( $t_note['note_type'] ) ? (int)$t_note['note_type'] : BUGNOTE;
				$t_note_attr = isset( $t_note['note_type'] ) ? $t_note['note_attr'] : '';

				bugnote_add( $p_issue_id, $t_note['text'], mci_get_time_tracking_from_note( $p_issue_id, $t_note ), $t_view_state_id == VS_PRIVATE, $t_note_type, $t_note_attr, $t_user_id, false );
			}
		}

		# The issue has been cached earlier in the bug_get() call.  Flush the cache since it is
		# now stale.  Otherwise, the email notification will be based on the cached data.
		bugnote_clear_bug_cache( $p_issue_id );
	}

	if( isset( $p_issue['tags'] ) && is_array( $p_issue['tags'] ) ) {
		mci_tag_set_for_issue( $p_issue_id, $p_issue['tags'], $t_user_id );
	}

	# submit the issue
	log_event( LOG_WEBSERVICE, 'updating issue \'' . $p_issue_id . '\'' );
	return $t_bug_data->update( /* update extended */ true, /* bypass email */ false );

}

/**
 * Set tags for a given issue
 * @param string  $p_username Username.
 * @param string  $p_password Password.
 * @param integer $p_issue_id A issue identifier.
 * @param array   $p_tags     An array of tags to set.
 * @return mixed
 */
function mc_issue_set_tags ( $p_username, $p_password, $p_issue_id, array $p_tags ) {
	global $g_project_override;

	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_fault_login_failed();
	}

	if( !bug_exists( $p_issue_id ) ) {
		return ApiObjectFactory::faultNotFound( 'Issue \'' . $p_issue_id . '\' does not exist.' );
	}

	$t_project_id = bug_get_field( $p_issue_id, 'project_id' );
	$g_project_override = $t_project_id;

	if( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
		return mci_fault_access_denied( $t_user_id );
	}

	if( bug_is_readonly( $p_issue_id ) ) {
		return mci_fault_access_denied( $t_user_id, 'Issue \'' . $p_issue_id . '\' is readonly' );
	}

	mci_tag_set_for_issue( $p_issue_id, $p_tags, $t_user_id );

	return true;
}

/**
 * Delete the specified issue.
 *
 * @param string  $p_username The name of the user trying to delete the issue.
 * @param string  $p_password The password of the user.
 * @param integer $p_issue_id The id of the issue to delete.
 * @return boolean True if the issue has been deleted successfully, false otherwise.
 */
function mc_issue_delete( $p_username, $p_password, $p_issue_id ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_fault_login_failed();
	}

	if( !bug_exists( $p_issue_id ) ) {
		return ApiObjectFactory::faultNotFound( "Issue '$p_issue_id' does not exist." );
	}

	$t_project_id = bug_get_field( $p_issue_id, 'project_id' );
	if( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
		return mci_fault_access_denied( $t_user_id );
	}

	$t_data = array( 'query' => array( 'id' => $p_issue_id ) );
	$t_command = new IssueDeleteCommand( $t_data );
	$t_command->execute();
}

/**
 * Add a note to an existing issue.
 *
 * @param string   $p_username The name of the user trying to add a note to an issue.
 * @param string   $p_password The password of the user.
 * @param integer  $p_issue_id The id of the issue to add the note to.
 * @param stdClass $p_note     The note to add.
 * @return integer The id of the added note.
 */
function mc_issue_note_add( $p_username, $p_password, $p_issue_id, stdClass $p_note ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_fault_login_failed();
	}

	$t_project_id = bug_get_field( $p_issue_id, 'project_id' );
	if( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
		return mci_fault_access_denied( $t_user_id );
	}

	$p_note = ApiObjectFactory::objectToArray( $p_note );

	$t_note_type = isset( $p_note['note_type'] ) ? (int)$p_note['note_type'] : BUGNOTE;
	if( $t_note_type != REMINDER ) {
		$t_payload = array();

		if( $t_note_type == TIME_TRACKING ) {
			$t_payload['type'] = 'timelog';
		} else {
			$t_payload['type'] = 'note';
		}

		if( isset( $p_note['text'] ) ) {
			$t_payload['text'] = $p_note['text'];
		}

		if( isset( $p_note['view_state'] ) ) {
			$t_payload['view_state'] = ApiObjectFactory::objectToArray( $p_note['view_state'] );
		}

		if( isset( $p_note['reporter'] ) ) {
			$t_payload['reporter'] = array( 'id' => mci_get_user_id( $p_note['reporter'] ) );
		}

		if( isset( $p_note['time_tracking'] ) && is_numeric( $p_note['time_tracking'] ) ) {
			$t_payload['time_tracking'] = array(
				'duration' => db_minutes_to_hhmm( $p_note['time_tracking'] )
			);
		}

		$t_data = array(
			'query' => array( 'issue_id' => $p_issue_id ),
			'payload' => $t_payload
		);

		$t_command = new IssueNoteAddCommand( $t_data );
		$t_result = $t_command->execute();
		return $t_result['id'];
	}

	# TODO: Keep the code path below for adding REMINDERs.
	if( (integer)$p_issue_id < 1 ) {
		return ApiObjectFactory::faultBadRequest( 'Invalid issue id \'' . $p_issue_id . '\'' );
	}

	if( !bug_exists( $p_issue_id ) ) {
		return ApiObjectFactory::faultNotFound( 'Issue \'' . $p_issue_id . '\' does not exist.' );
	}

	if( !isset( $p_note['text'] ) || is_blank( $p_note['text'] ) ) {
		return ApiObjectFactory::faultBadRequest( 'Issue note text must not be blank.' );
	}

	global $g_project_override;
	$g_project_override = $t_project_id;

	if( !access_has_bug_level( config_get( 'add_bugnote_threshold' ), $p_issue_id, $t_user_id ) ) {
		return mci_fault_access_denied( $t_user_id, 'You do not have access rights to add notes to this issue' );
	}

	if( bug_is_readonly( $p_issue_id ) ) {
		return mci_fault_access_denied( $t_user_id, 'Issue \'' . $p_issue_id . '\' is readonly' );
	}

	if( isset( $p_note['view_state'] ) ) {
		$t_view_state = $p_note['view_state'];
	} else {
		$t_view_state = array(
			'id' => config_get( 'default_bug_view_status' ),
		);
	}

	# TODO: #17777: Add test case for mc_issue_add() and mc_issue_note_add() reporter override
	if( isset( $p_note['reporter'] ) ) {
		$t_reporter_id = mci_get_user_id( $p_note['reporter'] );

		if( !$t_reporter_id ) {
			return ApiObjectFactory::faultBadRequest( 'Invalid reporter.' );
		}

		if( $t_reporter_id != $t_user_id ) {
			# Make sure that active user has access level required to specify a different reporter.
			$t_specify_reporter_access_level = config_get( 'webservice_specify_reporter_on_add_access_level_threshold' );
			if( !access_has_project_level( $t_specify_reporter_access_level, $t_project_id, $t_user_id ) ) {
				return mci_fault_access_denied( $t_user_id, "Active user does not have access level required to specify a different issue note reporter" );
			}
		}
	} else {
		$t_reporter_id = $t_user_id;
	}

	$t_view_state_id = mci_get_enum_id_from_objectref( 'view_state', $t_view_state );

	$t_note_attr = isset( $p_note['note_type'] ) ? $p_note['note_attr'] : '';

	log_event( LOG_WEBSERVICE, 'adding bugnote to issue \'' . $p_issue_id . '\'' );
	$t_bugnote_id = bugnote_add( $p_issue_id, $p_note['text'], mci_get_time_tracking_from_note( $p_issue_id, $p_note ), $t_view_state_id == VS_PRIVATE, $t_note_type, $t_note_attr, $t_reporter_id );

	bugnote_process_mentions( $p_issue_id, $t_bugnote_id, $p_note['text'] );

	return $t_bugnote_id;
}

/**
 * Delete a note given its id.
 *
 * @param string  $p_username      The name of the user trying to delete a note from an issue.
 * @param string  $p_password      The password of the user.
 * @param integer $p_issue_note_id The id of the note to be deleted.
 * @return boolean true: success, false: failure
 */
function mc_issue_note_delete( $p_username, $p_password, $p_issue_note_id ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_fault_login_failed();
	}

	if( (integer)$p_issue_note_id < 1 ) {
		return ApiObjectFactory::faultBadRequest( 'Invalid issue note id \'' . $p_issue_note_id . '\'.' );
	}

	$t_issue_id = bugnote_get_field( $p_issue_note_id, 'bug_id' );
	$t_project_id = bug_get_field( $t_issue_id, 'project_id' );
	if( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
		return mci_fault_access_denied( $t_user_id );
	}

	$t_data = array(
		'query' => array( 'id' => $p_issue_note_id )
	);

	$t_command = new IssueNoteDeleteCommand( $t_data );
	$t_command->execute();
	return true;
}

/**
 * Update a note
 *
 * @param string   $p_username The name of the user trying to update a note of an issue.
 * @param string   $p_password The password of the user.
 * @param stdClass $p_note     The note to update.
 * @return true on success, false on failure
 */
function mc_issue_note_update( $p_username, $p_password, stdClass $p_note ) {
	global $g_project_override;

	$t_user_id = mci_check_login( $p_username, $p_password );

	if( $t_user_id === false ) {
		return mci_fault_login_failed();
	}

	$p_note = ApiObjectFactory::objectToArray( $p_note );

	if( !isset( $p_note['id'] ) || is_blank( $p_note['id'] ) ) {
		return ApiObjectFactory::faultBadRequest( 'Issue note id must not be blank.' );
	}

	if( !isset( $p_note['text'] ) || is_blank( $p_note['text'] ) ) {
		return ApiObjectFactory::faultBadRequest( 'Issue note text must not be blank.' );
	}

	$t_issue_note_id = $p_note['id'];

	if( !bugnote_exists( $t_issue_note_id ) ) {
		return ApiObjectFactory::faultNotFound( 'Issue note \'' . $t_issue_note_id . '\' does not exist.' );
	}

	$t_issue_id = bugnote_get_field( $t_issue_note_id, 'bug_id' );
	$t_project_id = bug_get_field( $t_issue_id, 'project_id' );
	$g_project_override = $t_project_id;

	if( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
		return mci_fault_access_denied( $t_user_id );
	}

	# Check if the user owns the bugnote and is allowed to update their own bugnotes
	# regardless of the update_bugnote_threshold level.
	$t_user_owns_the_bugnote = bugnote_is_user_reporter( $t_issue_note_id, $t_user_id );
	$t_user_can_update_own_bugnote = config_get( 'bugnote_user_edit_threshold', null, $t_user_id, $t_project_id );
	if( $t_user_owns_the_bugnote && !$t_user_can_update_own_bugnote ) {
		return mci_fault_access_denied( $t_user_id );
	}

	# Check if the user has an access level beyond update_bugnote_threshold for the
	# project containing the bugnote to update.
	$t_update_bugnote_threshold = config_get( 'update_bugnote_threshold', null, $t_user_id, $t_project_id );
	if( !$t_user_owns_the_bugnote && !access_has_bugnote_level( $t_update_bugnote_threshold, $t_issue_note_id, $t_user_id ) ) {
		return mci_fault_access_denied( $t_user_id );
	}

	# Check if the bug is readonly
	if( bug_is_readonly( $t_issue_id ) ) {
		return mci_fault_access_denied( $t_user_id, 'Issue \'' . $t_issue_id . '\' is readonly' );
	}

	if( isset( $p_note['view_state'] ) ) {
		$t_view_state = $p_note['view_state'];
		$t_view_state_id = mci_get_enum_id_from_objectref( 'view_state', $t_view_state );
		bugnote_set_view_state( $t_issue_note_id, $t_view_state_id == VS_PRIVATE );
	}

	log_event( LOG_WEBSERVICE, 'updating bugnote id \'' . $t_issue_note_id . '\'' );
	bugnote_set_text( $t_issue_note_id, $p_note['text'] );

	return bugnote_date_update( $t_issue_note_id );
}

/**
 * Submit a new relationship.
 *
 * @param string   $p_username     The name of the user trying to add a relationship to an issue.
 * @param string   $p_password     The password of the user.
 * @param integer  $p_issue_id     The id of the issue of the source issue.
 * @param stdClass $p_relationship The relationship to add (RelationshipData SOAP object).
 * @return integer The id of the added relationship.
 */
function mc_issue_relationship_add( $p_username, $p_password, $p_issue_id, stdClass $p_relationship ) {
	global $g_project_override;
	$t_user_id = mci_check_login( $p_username, $p_password );

	$p_relationship = ApiObjectFactory::objectToArray( $p_relationship );

	$t_dest_issue_id = $p_relationship['target_id'];
	$t_rel_type = ApiObjectFactory::objectToArray( $p_relationship['type'] );

	if( $t_user_id === false ) {
		return mci_fault_login_failed();
	}

	$t_project_id = bug_get_field( $p_issue_id, 'project_id' );
	$g_project_override = $t_project_id;
	if( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
		return mci_fault_access_denied( $t_user_id );
	}

	# user has access to update the bug...
	if( !access_has_bug_level( config_get( 'update_bug_threshold' ), $p_issue_id, $t_user_id ) ) {
		return mci_fault_access_denied( $t_user_id, 'Active user does not have access level required to add a relationship to this issue' );
	}

	# source and destination bugs are the same bug...
	if( $p_issue_id == $t_dest_issue_id ) {
		return ApiObjectFactory::faultBadRequest( 'An issue can\'t be related to itself.' );
	}

	# the related bug exists...
	if( !bug_exists( $t_dest_issue_id ) ) {
		return ApiObjectFactory::faultNotFound( 'Issue \'' . $t_dest_issue_id . '\' not found.' );
	}

	# bug is not read-only...
	if( bug_is_readonly( $p_issue_id ) ) {
		return mci_fault_access_denied( $t_user_id, 'Issue \'' . $p_issue_id . '\' is readonly' );
	}

	# user can access to the related bug at least as viewer...
	if( !access_has_bug_level( config_get( 'view_bug_threshold', null, null, $t_project_id ), $t_dest_issue_id, $t_user_id ) ) {
		return mci_fault_access_denied( $t_user_id, 'The issue \'' . $t_dest_issue_id . '\' requires higher access level' );
	}

	log_event( LOG_WEBSERVICE, 'adding relationship type \'' . $t_rel_type['id'] . '\' between \'' . $p_issue_id . '\' and \'' . $t_dest_issue_id . '\'' );

	$t_relationship_id = relationship_upsert( $p_issue_id, $t_dest_issue_id, $t_rel_type['id'] );

	return $t_relationship_id;
}

/**
 * Delete the relationship with the specified target id.
 *
 * @param string  $p_username        The name of the user trying to delete a relationship from an issue.
 * @param string  $p_password        The password of the user.
 * @param integer $p_issue_id        The id of the source issue for the relationship.
 * @param integer $p_relationship_id The id of relationship to delete.
 * @return boolean true: success, false: failure
 */
function mc_issue_relationship_delete( $p_username, $p_password, $p_issue_id, $p_relationship_id ) {
	global $g_project_override;

	$t_user_id = mci_check_login( $p_username, $p_password );

	if( $t_user_id === false ) {
		return mci_fault_login_failed();
	}

	$t_project_id = bug_get_field( $p_issue_id, 'project_id' );
	$g_project_override = $t_project_id;
	if( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
		return mci_fault_access_denied( $t_user_id );
	}

	# user has access to update the bug...
	if( !access_has_bug_level( config_get( 'update_bug_threshold' ), $p_issue_id, $t_user_id ) ) {
		return mci_fault_access_denied( $t_user_id, 'Active user does not have access level required to remove a relationship from this issue.' );
	}

	# bug is not read-only...
	if( bug_is_readonly( $p_issue_id ) ) {
		return mci_fault_access_denied( $t_user_id, 'Issue \'' . $p_issue_id . '\' is readonly.' );
	}

	# retrieve the destination bug of the relationship
	$t_dest_issue_id = relationship_get_linked_bug_id( $p_relationship_id, $p_issue_id );

	# user can access to the related bug at least as viewer, if it's exist...
	if( bug_exists( $t_dest_issue_id ) ) {
		if( !access_has_bug_level( config_get( 'view_bug_threshold', null, null, $t_project_id ), $t_dest_issue_id, $t_user_id ) ) {
			return mci_fault_access_denied( $t_user_id, 'The issue \'' . $t_dest_issue_id . '\' requires higher access level.' );
		}
	}

	# delete relationship from the DB
	log_event( LOG_WEBSERVICE, 'deleting relationship id \'' . $p_relationship_id . '\'' );
	relationship_delete( $p_relationship_id );

	return true;
}

/**
 * Transforms a `BugData` object into a response for webservice API.
 * This function assumes that user has access to the issue.
 * This function will filter out issue information that user doesn't have
 * access to.
 *
 * @param BugData $p_issue_data A BugData object to process.
 * @param integer $p_user_id    A valid user identifier.
 * @param string  $p_lang       A valid language string.
 * @return array The issue as an array
 */
function mci_issue_data_as_array( BugData $p_issue_data, $p_user_id, $p_lang ) {
	global $g_project_override;
	$t_project_id = $p_issue_data->project_id;
	$g_project_override = $t_project_id;

	$t_id = (int)$p_issue_data->id;

	$t_issue = array();
	$t_issue['id'] = $t_id;
	$t_issue['summary'] = mci_sanitize_xml_string( $p_issue_data->summary );
	$t_issue['description'] = mci_sanitize_xml_string( bug_get_text_field( $t_id, 'description' ) );

	$t_steps_to_reproduce = bug_get_text_field( $t_id, 'steps_to_reproduce' );
	$t_issue['steps_to_reproduce'] = mci_null_if_empty( mci_sanitize_xml_string( $t_steps_to_reproduce ) );

	$t_additional_information = bug_get_text_field( $t_id, 'additional_information' );
	$t_issue['additional_information'] = mci_null_if_empty( mci_sanitize_xml_string( $t_additional_information ) );

	$t_issue['project'] = mci_project_as_array_by_id( $p_issue_data->project_id );
	$t_issue['category'] = mci_get_category( $p_issue_data->category_id );
	$t_issue['version'] = mci_get_version( $p_issue_data->version, $p_issue_data->project_id );
	$t_issue['fixed_in_version'] = mci_get_version( $p_issue_data->fixed_in_version, $p_issue_data->project_id );
	if( access_has_bug_level( config_get( 'roadmap_view_threshold' ), $t_id ) ) {
		$t_issue['target_version'] = mci_get_version( $p_issue_data->target_version, $p_issue_data->project_id );
	}

	$t_issue['reporter'] = mci_account_get_array_by_id( $p_issue_data->reporter_id );

	if( !empty( $p_issue_data->handler_id ) &&
		access_has_bug_level( config_get( 'view_handler_threshold', null, null, $t_project_id ), $t_id, $p_user_id ) ) {
		$t_issue['handler'] = mci_account_get_array_by_id($p_issue_data->handler_id);
	}

	$t_issue['status'] = mci_enum_get_array_by_id( $p_issue_data->status, 'status', $p_lang );
	$t_issue['resolution'] = mci_enum_get_array_by_id( $p_issue_data->resolution, 'resolution', $p_lang );
	$t_issue['view_state'] = mci_enum_get_array_by_id( $p_issue_data->view_state, 'view_state', $p_lang );
	$t_issue['priority'] = mci_enum_get_array_by_id( $p_issue_data->priority, 'priority', $p_lang );
	$t_issue['severity'] = mci_enum_get_array_by_id( $p_issue_data->severity, 'severity', $p_lang );
	$t_issue['reproducibility'] = mci_enum_get_array_by_id( $p_issue_data->reproducibility, 'reproducibility', $p_lang );

	if( config_get( 'enable_projection' ) != OFF ) {
		$t_issue['projection'] = mci_enum_get_array_by_id( $p_issue_data->projection, 'projection', $p_lang );
	}

	if( config_get( 'enable_product_build' ) != OFF ) {
		$t_issue['build'] = mci_null_if_empty( $p_issue_data->build );
	}

	if( config_get( 'allow_freetext_in_profile_fields' ) != OFF ) {
		$t_issue['platform'] = mci_null_if_empty( $p_issue_data->platform );
		$t_issue['os'] = mci_null_if_empty( $p_issue_data->os );
		$t_issue['os_build'] = mci_null_if_empty( $p_issue_data->os_build );
	}

	if( config_get( 'enable_eta' ) != OFF ) {
		$t_issue['eta'] = mci_enum_get_array_by_id( $p_issue_data->eta, 'eta', $p_lang );
	}

	if( access_has_bug_level( config_get( 'due_date_view_threshold' ), $t_id ) ) {
		$t_issue['due_date'] = ApiObjectFactory::datetime( $p_issue_data->due_date );
	}

	$t_created_at = ApiObjectFactory::datetime( $p_issue_data->date_submitted );
	$t_updated_at = ApiObjectFactory::datetime( $p_issue_data->last_updated );

	if( ApiObjectFactory::$soap ) {
		if( config_get( 'enable_profiles' ) != OFF ) {
			$t_issue['profile_id'] = (int)$p_issue_data->profile_id;
		}

		if( access_has_bug_level( config_get( 'view_sponsorship_total_threshold' ), $t_id ) ) {
			$t_issue['sponsorship_total'] = $p_issue_data->sponsorship_total;
		} else {
			$t_issue['sponsorship_total'] = 0;
		}

		$t_issue['sticky'] = $p_issue_data->sticky;
		$t_issue['date_submitted'] = $t_created_at;
		$t_issue['last_updated'] = $t_updated_at;
	} else {
		if( config_get( 'enable_profiles' ) != OFF ) {
			if ((int)$p_issue_data->profile_id != 0) {
				$t_issue['profile'] = mci_profile_as_array_by_id($p_issue_data->profile_id);
			}
		}

		$t_issue['sticky'] = (bool)$p_issue_data->sticky;
		$t_issue['created_at'] = $t_created_at;
		$t_issue['updated_at'] = $t_updated_at;
	}

	# Get attachments - access checked as part of returning attachments
	$t_issue['attachments'] = mci_issue_get_attachments( $p_issue_data->id, /* note_id */ 0 );

	# Get notes - access checked as part of returning notes.
	$t_issue['notes'] = mci_issue_get_notes( $p_issue_data->id );

	# Get attachments - access checked as part of returning relationships
	$t_issue['relationships'] = mci_issue_get_relationships( $p_issue_data->id, $p_user_id );

	# Get custom fields - access checked as part of returning custom fields
	$t_issue['custom_fields'] = mci_issue_get_custom_fields( $p_issue_data->id );

	# Get tags - access checked as part of returning tags
	$t_issue['tags'] = mci_issue_get_tags_for_bug_id( $p_issue_data->id, $p_user_id );

	# Get users monitoring issue - access checked as part of returning user list.
	$t_issue['monitors'] = mci_account_get_array_by_ids( bug_get_monitors( $p_issue_data->id ) );

	if( !ApiObjectFactory::$soap ) {
		$t_history = mci_issue_get_history( $p_issue_data->id, $p_user_id, $p_lang );
		if( !empty( $t_history ) ) {
			$t_issue['history'] = $t_history;
		}

		mci_remove_null_keys( $t_issue );
		mci_remove_empty_arrays( $t_issue );
	}

	return $t_issue;
}

/**
 * Get tags linked to a given bug id
 * @param integer $p_bug_id  A bug identifier.
 * @param integer $p_user_id User accessing the information.
 * @return array
 */
function mci_issue_get_tags_for_bug_id( $p_bug_id, $p_user_id ) {
	if( !access_has_bug_level( config_get( 'tag_view_threshold' ), $p_bug_id, $p_user_id ) ) {
		return array();
	}

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
 * @param BugData $p_issue_data A BugData object to process.
 * @return array The issue header data as an array
 */
function mci_issue_data_as_header_array( BugData $p_issue_data ) {
		$t_issue = array();

		$t_id = $p_issue_data->id;

		$t_issue['id'] = $t_id;
		$t_issue['view_state'] = $p_issue_data->view_state;
		$t_issue['last_updated'] = ApiObjectFactory::datetime( $p_issue_data->last_updated );

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

		$t_attachments = mci_issue_get_attachments( $p_issue_data->id );
		$t_issue['attachments_count'] = $t_attachments === null ? 0 : count( $t_attachments );

		$t_notes = mci_issue_get_notes( $p_issue_data->id );
		$t_issue['notes_count'] = $t_notes === null ? 0 : count( $t_notes );

		return $t_issue;
}

/**
 * Check if the bug exists and the user has a access right to read it.
 *
 * @param integer   $p_user_id         The user id.
 * @param integer   $p_bug_id          The bug id.
 * @return true if the user has access rights and the bug exists, otherwise return false
 */
function mci_check_access_to_bug( $p_user_id, $p_bug_id ) {

    if( !bug_exists( $p_bug_id ) ) {
        return false;
    }

    $t_project_id = bug_get_field( $p_bug_id, 'project_id' );
    $g_project_override = $t_project_id;
    if( !mci_has_readonly_access( $p_user_id, $t_project_id ) ) {
        return false;
    }

    if( !access_has_bug_level( config_get( 'view_bug_threshold', null, null, $t_project_id ), $p_bug_id, $p_user_id ) ) {
        return false;
    }

    return true;
}

/**
 * Get all issues matching the ids.
 *
 * @param string                $p_username         The name of the user trying to access the issues.
 * @param string                $p_password         The password of the user.
 * @param IntegerArray          $p_issue_ids        Number of issues to display per page.
 * @return array that represents an IssueDataArray structure
 */
function mc_issues_get( $p_username, $p_password, $p_issue_ids ) {
    $t_user_id = mci_check_login( $p_username, $p_password );
    if( $t_user_id === false ) {
        return mci_fault_login_failed();
    }

    $t_lang = mci_get_user_lang( $t_user_id );

    $t_result = array();
    foreach( $p_issue_ids as $t_id ) {
        if( mci_check_access_to_bug( $t_user_id, $t_id ) === false ) {
			continue;
		}

        log_event( LOG_WEBSERVICE, 'getting details for issue \'' . $t_id . '\'' );

        $t_issue_data = bug_get( $t_id, true );
        $t_result[] = mci_issue_data_as_array( $t_issue_data, $t_user_id, $t_lang );
    }

    return $t_result;
}

/**
 * Get all issues header matching the ids.
 *
 * @param string                $p_username         The name of the user trying to access the issues.
 * @param string                $p_password         The password of the user.
 * @param IntegerArray          $p_issue_ids        Number of issues to display per page.
 * @return array that represents an IssueHeaderDataArray structure
 */
function mc_issues_get_header( $p_username, $p_password, $p_issue_ids ) {
    $t_user_id = mci_check_login( $p_username, $p_password );
    if( $t_user_id === false ) {
        return mci_fault_login_failed();
    }

    $t_result = array();
    foreach( $p_issue_ids as $t_id ) {

        if( mci_check_access_to_bug( $t_user_id, $t_id ) === false )
            continue;

        log_event( LOG_WEBSERVICE, 'getting details for issue \'' . $t_id . '\'' );

        $t_issue_data = bug_get( $t_id, true );
        $t_result[] = mci_issue_data_as_header_array( $t_issue_data );
    }

    return $t_result;
}

/**
 * Calculate hash for an issue.
 *
 * @param integer $p_issue_id The issue id.
 * @param null|string|array $p_issue The issue or null if issue doesn't exist.
 * @param integer $p_user_id The user id.
 * @return string The hash of the issue.
 */
function mc_issue_hash( $p_issue_id, $p_issue, $p_user_id = null ) {
	if( $p_user_id === null ) {
		if( auth_is_user_authenticated() ) {
			$t_user_id = auth_get_current_user_id();
		} else {
			$t_user_id = 0;
		}
	} else {
		$t_user_id = (int)$p_user_id;
	}

	if( $p_issue === null ) {
		$t_issue = '';
	} else if( is_array( $p_issue ) ) {
		$t_issue = json_encode( $p_issue );
	} else {
		$t_issue = $p_issue;
	}

	$t_issue_id = (int)$p_issue_id;

	$t_str_to_hash = 'v1_' . $t_user_id . '_' . $t_issue_id . '_' . $t_issue;

	return mci_etag_hash( $t_str_to_hash );
}
