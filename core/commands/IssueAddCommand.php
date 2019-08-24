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

require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'custom_field_api.php' );
require_api( 'date_api.php' );
require_api( 'email_api.php' );
require_api( 'error_api.php' );
require_api( 'event_api.php' );
require_api( 'file_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'last_visited_api.php' );
require_api( 'profile_api.php' );
require_api( 'relationship_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );

require_once( dirname( __FILE__ ) . '/../../api/soap/mc_api.php' );
require_once( dirname( __FILE__ ) . '/../../api/soap/mc_enum_api.php' );
require_once( dirname( __FILE__ ) . '/../../api/soap/mc_issue_api.php' );
require_once( dirname( __FILE__ ) . '/../../api/soap/mc_project_api.php' );

use Mantis\Exceptions\ClientException;

/**
 * Sample:
 * {
 *   "query": {
 *   },
 *   "payload": {
 *     ... see rest issue add documentation
 *   },
 *   "options: {
 *     "clone_info": {                # Used only in case issue is cloned
 *       "master_issue_id": 1234,
 *       "relationship_type": 1,      # BUG_RELATED
 *       "copy_files": true,
 *       "copy_notes": true,
 *     }
 *   }
 * }
 */

/**
 * A command that adds an issue.
 */
class IssueAddCommand extends Command {
	/**
	 * The issue to add.
	 *
	 * @var BugData
	 */
	private $issue = null;

	/**
	 * @var integer
	 */
	private $user_id;

	/**
	 * The files to attach with the note.
	 */
	private $files = array();

	/**
	 * Constructor
	 *
	 * @param array $p_data The command data.
	 */
	function __construct( array $p_data ) {
		parent::__construct( $p_data );
	}

	/**
	 * Validate the data.
	 * @throws ClientException
	 */
	protected function validate() {
		$this->user_id = auth_get_current_user_id();
		$t_clone_info = $this->option( 'clone_info', array() );

		$t_issue = $this->payload( 'issue' );

		if( isset( $t_clone_info['master_issue_id'] ) ) {
			if( bug_is_readonly( $t_clone_info['master_issue_id'] ) ) {
				throw new ClientException(
					sprintf( "Master issue '%d' is read-only", $t_clone_info['master_issue_id'] ),
					ERROR_BUG_READ_ONLY_ACTION_DENIED,
					array( $t_clone_info['master_issue_id'] )
				);
			}
		}

		if( !isset( $t_issue['summary'] ) || is_blank( $t_issue['summary'] ) )  {
			throw new ClientException(
				'Summary not specified',
				ERROR_EMPTY_FIELD,
				array( 'summary' ) );
		}

		$t_summary = $t_issue['summary'];

		if( !isset( $t_issue['description'] ) || is_blank( $t_issue['description'] ) )  {
			throw new ClientException(
				'Description not specified',
				ERROR_EMPTY_FIELD,
				array( 'description' ) );
		}

		$t_description = $t_issue['description'];

		if( !isset( $t_issue['project'] ) )  {
			throw new ClientException(
				'Project not specified',
				ERROR_EMPTY_FIELD,
				array( 'project' ) );
		}

		$t_project_id = mci_get_project_id( $t_issue['project'] );

		if( $t_project_id == ALL_PROJECTS ) {
			throw new ClientException(
				'Project not specified',
				ERROR_EMPTY_FIELD,
				array( 'project' ) );
		}

		if( !project_exists( $t_project_id ) ) {
			throw new ClientException(
				sprintf( "Project '%d' not found", $t_project_id ),
				ERROR_PROJECT_NOT_FOUND,
				array( $t_project_id ) );
		}

		# in case the current project is not the same project of the bug we are
		# viewing, override the current project. This to avoid problems with
		# categories and handlers lists etc.
		global $g_project_override;
		$g_project_override = $t_project_id;

		if( !access_has_project_level( config_get( 'report_bug_threshold' ), $t_project_id, $this->user_id ) ) {
			throw new ClientException(
				'User does not have access right to report issues',
				ERROR_ACCESS_DENIED );
		}

		$t_handler_id = isset( $t_issue['handler'] ) ? mci_get_user_id( $t_issue['handler'] ) : NO_USER;
		$t_priority_id = isset( $t_issue['priority'] ) ? mci_get_priority_id( $t_issue['priority'] ) : config_get( 'default_bug_priority' );
		$t_severity_id = isset( $t_issue['severity'] ) ? mci_get_severity_id( $t_issue['severity'] ) : config_get( 'default_bug_severity' );
		$t_status_id = isset( $t_issue['status'] ) ? mci_get_status_id( $t_issue['status'] ) : config_get( 'bug_submit_status' );
		$t_reproducibility_id = isset( $t_issue['reproducibility'] ) ? mci_get_reproducibility_id( $t_issue['reproducibility'] ) : config_get( 'default_bug_reproducibility' );
		$t_resolution_id =  isset( $t_issue['resolution'] ) ? mci_get_resolution_id( $t_issue['resolution'] ) : config_get( 'default_bug_resolution' );
		$t_projection_id = isset( $t_issue['projection'] ) ? mci_get_projection_id( $t_issue['projection'] ) : config_get( 'default_bug_projection' );
		$t_eta_id = isset( $t_issue['eta'] ) ? mci_get_eta_id( $t_issue['eta'] ) : config_get( 'default_bug_eta' );
		$t_view_state_id = isset( $t_issue['view_state'] ) ?  mci_get_view_state_id( $t_issue['view_state'] ) : config_get( 'default_bug_view_status' );

		# TODO: #17777: Add test case for mc_issue_add() and mc_issue_note_add() reporter override
		if( isset( $t_issue['reporter'] ) ) {
			$t_reporter_id = mci_get_user_id( $t_issue['reporter'] );

			if( $t_reporter_id != $this->user_id ) {
				# Make sure that active user has access level required to specify a different reporter.
				$t_specify_reporter_access_level = config_get( 'webservice_specify_reporter_on_add_access_level_threshold' );
				if( !access_has_project_level( $t_specify_reporter_access_level, $t_project_id, $this->user_id ) ) {
					throw new ClientException(
						'Active user does not have access level required to specify a different issue reporter',
						ERROR_ACCESS_DENIED );
				}
			}
		} else {
			$t_reporter_id = $this->user_id;
		}

		# Prevent unauthorized users setting handler when reporting issue
		if( $t_handler_id > 0 ) {
			if ( !access_has_project_level( config_get( 'update_bug_assign_threshold' ) ) ) {
				throw new ClientException(
					'User not allowed to assign issues',
					ERROR_ACCESS_DENIED );
			}
		} else {
			# Ensure that resolved bugs have a handler
			if( $t_handler_id == NO_USER && $t_status_id >= config_get( 'bug_resolved_status_threshold' ) ) {
				$t_handler_id = $this->user_id;
			}
		}

		if( $t_handler_id != NO_USER ) {
			if( !user_exists( $t_handler_id ) ) {
				throw new ClientException(
					sprintf( "User '%d' not found.", $t_handler_id ),
					ERROR_USER_BY_ID_NOT_FOUND,
					array( $t_handler_id ) );
			}

			if( !access_has_project_level( config_get( 'handle_bug_threshold' ), $t_project_id, $t_handler_id ) ) {
				throw new ClientException(
					sprintf( "User '%d' can't be assigned issues.", $t_handler_id ),
					ERROR_ACCESS_DENIED );
			}
		}

		# Validate tags and make sure user is allowed to create them if needed
		if( isset( $t_issue['tags'] ) && is_array( $t_issue['tags'] ) ) {
			foreach( $t_issue['tags'] as $t_tag ) {
				$t_tag_id = $this->get_tag_id( $t_tag );
				if( $t_tag_id === false && !tag_can_create( $this->user_id ) ) {
					throw new ClientException(
						sprintf( "User '%d' can't create tag '%s'.", $this->user_id, $t_tag['name'] ),
						ERROR_TAG_NOT_FOUND,
						array( $t_tag['name'] )
					);
				}
			}
		}

		$t_category = isset( $t_issue['category'] ) ? $t_issue['category'] : null;
		$t_category_id = mci_get_category_id( $t_category, $t_project_id );

		$this->issue = new BugData;
		$this->issue->project_id = $t_project_id;
		$this->issue->reporter_id = $t_reporter_id;
		$this->issue->summary = $t_summary;
		$this->issue->description = $t_description;
		$this->issue->steps_to_reproduce = isset( $t_issue['steps_to_reproduce'] ) ? $t_issue['steps_to_reproduce'] : '';
		$this->issue->additional_information = isset( $t_issue['additional_information'] ) ? $t_issue['additional_information'] : '';
		$this->issue->handler_id = $t_handler_id;
		$this->issue->priority = $t_priority_id;
		$this->issue->severity = $t_severity_id;
		$this->issue->reproducibility = $t_reproducibility_id;
		$this->issue->status = $t_status_id;
		$this->issue->resolution = $t_resolution_id;
		$this->issue->projection = $t_projection_id;
		$this->issue->category_id = $t_category_id;
		$this->issue->eta = $t_eta_id;
		$this->issue->os = isset( $t_issue['os'] ) ? $t_issue['os'] : '';
		$this->issue->os_build = isset( $t_issue['os_build'] ) ? $t_issue['os_build'] : '';
		$this->issue->platform = isset( $t_issue['platform'] ) ? $t_issue['platform'] : '';
		$this->issue->build = isset( $t_issue['build'] ) ? $t_issue['build'] : '';
		$this->issue->view_state = $t_view_state_id;
		$this->issue->sponsorship_total = isset( $t_issue['sponsorship_total'] ) ? $t_issue['sponsorship_total'] : 0;

		if( isset( $t_issue['profile_id'] ) ) {
			$t_profile_id = (int)$t_issue['profile_id'];
		} else if( isset( $t_issue['profile'] ) && isset( $t_issue['profile']['id'] ) ) {
			$t_profile_id = (int)$t_issue['profile']['id'];
		} else {
			$t_profile_id = 0;
		}

		$this->issue->profile_id = $t_profile_id;

		$t_version_id = isset( $t_issue['version'] ) ? mci_get_version_id( $t_issue['version'], $t_project_id, 'version' ) : 0;
		if( $t_version_id != 0 ) {
			$this->issue->version = version_get_field( $t_version_id, 'version' );
		}

		$t_fixed_in_version_id = isset( $t_issue['fixed_in_version'] ) ? mci_get_version_id( $t_issue['fixed_in_version'], $t_project_id, 'fixed_in_version' ) : 0;
		if( $t_fixed_in_version_id != 0 ) {
			$this->issue->fixed_in_version = version_get_field( $t_fixed_in_version_id, 'version' );
		}

		$t_target_version_id = isset( $t_issue['target_version'] ) ? mci_get_version_id( $t_issue['target_version'], $t_project_id, 'target_version' ) : 0;
		if( $t_target_version_id != 0 && access_has_project_level( config_get( 'roadmap_update_threshold' ), $t_project_id, $this->user_id ) ) {
			$this->issue->target_version = version_get_field( $t_target_version_id, 'version' );
		}

		if( isset( $t_issue['sticky'] ) &&
			 access_has_project_level( config_get( 'set_bug_sticky_threshold', null, null, $t_project_id ), $t_project_id ) ) {
			$this->issue->sticky = $t_issue['sticky'];
		}

		if( isset( $t_issue['due_date'] ) &&
			access_has_project_level( config_get( 'due_date_update_threshold' ), $t_project_id ) ) {
			$this->issue->due_date = strtotime( $t_issue['due_date'] );
		} else {
			$this->issue->due_date = date_get_null();
		}

		# if a profile was selected then let's use that information
		if( $this->issue->profile_id != 0 ) {
			if( profile_is_global( $this->issue->profile_id ) ) {
				$t_row = user_get_profile_row( ALL_USERS, $this->issue->profile_id );
			} else {
				$t_row = user_get_profile_row( $this->issue->reporter_id, $this->issue->profile_id );
			}

			if( is_blank( $this->issue->platform ) ) {
				$this->issue->platform = $t_row['platform'];
			}

			if( is_blank( $this->issue->os ) ) {
				$this->issue->os = $t_row['os'];
			}

			if( is_blank( $this->issue->os_build ) ) {
				$this->issue->os_build = $t_row['os_build'];
			}
		}

		mci_project_custom_fields_validate( $t_project_id, $t_issue['custom_fields'] );

		if( isset( $t_issue['files'] ) && !empty( $t_issue['files'] ) ) {
			if( !file_allow_bug_upload( /* issue id */ null, /* user id */ null, $t_project_id ) ) {
				throw new ClientException(
					'User not allowed to attach files.',
					ERROR_ACCESS_DENIED );
			}

			$this->files = $t_issue['files'];
		}

		# Trigger extensibility events to pre-process data before creating issue
		helper_call_custom_function( 'issue_create_validate', array( $this->issue ) );
		$this->issue = event_signal( 'EVENT_REPORT_BUG_DATA', $this->issue );
	}

	/**
	 * Process the command.
	 *
	 * @returns array Command response
	 * @throws ClientException
	 */
	protected function process() {
		$t_issue = $this->payload( 'issue' );

		# Create the bug
		$t_issue_id = $this->issue->create();
		log_event( LOG_WEBSERVICE, "created new issue id '$t_issue_id'" );

		# Add Tags
		if( isset( $t_issue['tags'] ) && is_array( $t_issue['tags'] ) ) {
			$t_tags = array();
			foreach( $t_issue['tags'] as $t_tag ) {
				if( $this->get_tag_id( $t_tag ) === false ) {
					$t_tag['id'] = tag_create( $t_tag['name'], $this->user_id );
					log_event( LOG_WEBSERVICE,
						"created new tag '" . $t_tag['name'] . "' id '" . $t_tag['id'] . "'"
					);
				}

				$t_tags[] = $t_tag;
			}

			# @TODO should this be replaced by TagAttachCommand, as suggested in #24441 ?
			mci_tag_set_for_issue( $t_issue_id, $t_tags, $this->user_id );
		}

		# Handle the file upload
		file_attach_files( $t_issue_id, $this->files );

		# Handle custom field submission
		mci_issue_set_custom_fields( $t_issue_id, $t_issue['custom_fields'], /* history log insert */ false );

		if( isset( $t_issue['monitors'] ) ) {
			mci_issue_set_monitors( $t_issue_id, $this->user_id, $t_issue['monitors'] );
		}

		$t_clone_info = $this->option( 'clone_info', array() );
		if( isset( $t_clone_info['master_issue_id'] ) ) {
			$t_master_issue_id = (int)$t_clone_info['master_issue_id'];

			# it's a child generation... let's create the relationship and add some lines in the history

			# update master bug last updated
			bug_update_date( $t_master_issue_id );

			# Add log line to record the cloning action
			history_log_event_special( $t_issue_id, BUG_CREATED_FROM, '', $t_master_issue_id );
			history_log_event_special( $t_master_issue_id, BUG_CLONED_TO, '', $t_issue_id );

			# copy notes from parent
			if( isset( $t_clone_info['copy_notes'] ) &&  $t_clone_info['copy_notes'] ) {
				$t_parent_bugnotes = bugnote_get_all_bugnotes( $t_master_issue_id );

				foreach ( $t_parent_bugnotes as $t_parent_bugnote ) {
					$t_private = $t_parent_bugnote->view_state == VS_PRIVATE;

					bugnote_add(
						$t_issue_id,
						$t_parent_bugnote->note,
						$t_parent_bugnote->time_tracking,
						$t_private,
						$t_parent_bugnote->note_type,
						$t_parent_bugnote->note_attr,
						$t_parent_bugnote->reporter_id,
						false,
						0,
						0,
						false );

					# Note: we won't trigger mentions in the clone scenario.
				}
			}

			# copy attachments from parent
			if( isset( $t_clone_info['copy_files'] ) &&  $t_clone_info['copy_files'] ) {
				file_copy_attachments( $t_master_issue_id, $t_issue_id );
			}

			if( isset( $t_clone_info['relationship_type'] ) &&  $t_clone_info['relationship_type'] > BUG_REL_ANY ) {
				relationship_add( $t_issue_id, $t_master_issue_id, $t_clone_info['relationship_type'], /* email for source */ false );
			}
		}

		$t_notes = isset( $t_issue['notes'] ) ? $t_issue['notes'] : array();
		if( isset( $t_notes ) && is_array( $t_notes ) ) {
			foreach( $t_notes as $t_note ) {
				if( isset( $t_note['view_state'] ) ) {
					$t_view_state = $t_note['view_state'];
				} else {
					$t_view_state = config_get( 'default_bugnote_view_status' );
				}

				$t_note_type = isset( $t_note['note_type'] ) ? (int)$t_note['note_type'] : BUGNOTE;
				$t_note_attr = isset( $t_note['note_type'] ) ? $t_note['note_attr'] : '';

				$t_view_state_id = mci_get_enum_id_from_objectref( 'view_state', $t_view_state );
				$t_note_id = bugnote_add(
					$t_issue_id,
					$t_note['text'],
					mci_get_time_tracking_from_note( $t_issue_id, $t_note ),
					$t_view_state_id == VS_PRIVATE,
					$t_note_type,
					$t_note_attr,
					$this->user_id,
					false ); # don't send mail

				bugnote_process_mentions( $t_issue_id, $t_note_id, $t_note['text'] );

				log_event( LOG_WEBSERVICE, 'bugnote id \'' . $t_note_id . '\' added to issue \'' . $t_issue_id . '\'' );
			}
		}

		# Mark the added issue as visited so that it appears on the last visited list.
		last_visited_issue( $t_issue_id );

		# Trigger Email Notifications
		$this->issue->process_mentions();
		email_bug_added( $t_issue_id );

		# Trigger extensibility events
		helper_call_custom_function( 'issue_create_notify', array( $t_issue_id ) );
		event_signal( 'EVENT_REPORT_BUG', array( $this->issue, $t_issue_id ) );

		return array( 'issue_id' => $t_issue_id );
	}

	/**
	 * Retrieves the Tag ID for the given Tag element.
	 *
	 * A tag element is an array with either an 'id', a 'name' key, or both.
	 * If id is provided, check that it exists and return it;
	 * if name is supplied, look it up and return the corresponding ID, or
	 * false if not found.
	 *
	 * @param array $p_tag Tag element
	 * @return integer|false Tag ID or false if tag does not exist
	 * @throws ClientException
	 */
	private function get_tag_id( array $p_tag ) {
		if( isset( $p_tag['id'] ) ) {
			$t_tag_id = $p_tag['id'];
			if( !tag_exists( $t_tag_id ) ) {
				throw new ClientException(
					"Tag with id $t_tag_id not found.",
					ERROR_TAG_NOT_FOUND,
					array( $t_tag_id )
				);
			}
		} elseif( isset( $p_tag['name'] ) ) {
			$t_matches = array();
			if( !tag_name_is_valid( $p_tag['name'], $t_matches )) {
				throw new ClientException(
					"Tag name '{$p_tag['name']}' is not valid.",
					ERROR_TAG_NAME_INVALID,
					array( $p_tag['name'] )
				);
			}
			$t_existing_tag = tag_get_by_name( $p_tag['name'] );
			$t_tag_id = $t_existing_tag === false ? false : $t_existing_tag['id'];
		} else {
			throw new ClientException(
				'Tag without id or name.',
				ERROR_TAG_NAME_INVALID
			);
		}
		return $t_tag_id;
	}
}

