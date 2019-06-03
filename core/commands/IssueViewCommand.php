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

require_once( dirname( __FILE__ ) . '/../../api/soap/mc_account_api.php' );
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
 * A command that returns issue information to view.
 */
class IssueViewCommand extends Command {
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
	 */
	function validate() {
		$this->user_id = auth_get_current_user_id();
		$t_issue_id = $this->query( 'id' );

		$this->issue = bug_get( $t_issue_id, true );

		access_ensure_bug_level( config_get( 'view_bug_threshold' ), $t_issue_id );
	}

	/**
	 * Process the command.
	 *
	 * @returns array Command response
	 */
	protected function process() {
		$t_force_readonly = $this->option( 'force_readonly', false );

		$t_issue = $this->issue;
		$t_issue_id = (int)$t_issue->id;
		$t_project_id = (int)$t_issue->project_id;

		# in case the current project is not the same project of the bug we are
		# viewing, override the current project. This to avoid problems with
		# categories and handlers lists etc.
		global $g_project_override;
		$g_project_override = $t_project_id;

		$t_output_issue = array();
		$t_output_issue['id'] = $t_issue_id;

		$t_output_configs = array();

		# Fields to show on the issue view page
		$t_fields = config_get( 'bug_view_page_fields' );
		$t_fields = columns_filter_disabled( $t_fields );
		$t_output_configs['fields'] = $t_fields;

		# Versions
		$t_show_versions = version_should_show_product_version( $t_project_id );
		$t_output_configs['show_versions'] = $t_show_versions;

		$t_show_product_version = $t_show_versions && in_array( 'product_version', $t_fields );
		$t_output_configs['show_product_version'] = $t_show_product_version;

		$t_show_fixed_in_version = $t_show_versions && in_array( 'fixed_in_version', $t_fields );
		$t_output_configs['show_fixed_in_version'] = $t_show_fixed_in_version;

		$t_show_product_build = $t_show_versions && in_array( 'product_build', $t_fields )
			&& ( config_get( 'enable_product_build' ) == ON );
		$t_output_configs['show_product_build'] = $t_show_product_build;

		$t_product_build = $t_show_product_build ? string_display_line( $t_issue->build ) : '';

		$t_show_target_version = $t_show_versions && in_array( 'target_version', $t_fields )
			&& access_has_bug_level( config_get( 'roadmap_view_threshold' ), $t_issue_id );
		$t_output_configs['show_target_version'] = $t_show_target_version;

		$t_product_version_string  = null;
		$t_target_version_string   = null;
		$t_fixed_in_version_string = null;

		if( $t_show_product_version || $t_show_fixed_in_version || $t_show_target_version ) {
			$t_version_rows = version_get_all_rows( $t_project_id );

			if( $t_show_product_version ) {
				$t_product_version_string  = prepare_version_string( $t_project_id, version_get_id( $t_issue->version, $t_project_id ) );
			}

			if( $t_show_target_version ) {
				$t_target_version_string   = prepare_version_string( $t_project_id, version_get_id( $t_issue->target_version, $t_project_id ) );
			}

			if( $t_show_fixed_in_version ) {
				$t_fixed_in_version_string = prepare_version_string( $t_project_id, version_get_id( $t_issue->fixed_in_version, $t_project_id ) );
			}
		}

		if( !is_null( $t_product_version_string ) ) {
			$t_output_issue['version'] = $t_product_version_string;
		}

		if( !is_null( $t_target_version_string ) ) {
			$t_output_issue['target_version'] = $t_target_version_string;
		}

		if( !is_null( $t_fixed_in_version_string ) ) {
			$t_output_issue['fixed_in_version'] = $t_fixed_in_version_string;
		}

		$t_form_title = lang_get( 'bug_view_title' );
		$t_output_issue['form_title'] = $t_form_title;

		if( config_get_global( 'wiki_enable' ) == ON ) {
			$t_output_issue['wiki_link'] = 'wiki.php?id=' . $t_issue_id;
		}

		$t_output_configs['history_show'] =
			config_get( 'history_default_visible' ) &&
			access_has_bug_level( config_get( 'view_history_threshold' ), $t_issue_id );

		$t_show_reminder = !current_user_is_anonymous() && !bug_is_readonly( $t_issue_id ) &&
			  access_has_bug_level( config_get( 'bug_reminder_threshold' ), $t_issue_id );
		$t_output_configs['reminder_can_add'] = $t_show_reminder;

		if( in_array( 'project', $t_fields ) ) {
			$t_output_issue['project_name'] = project_get_name( $t_project_id );
		}

		if( in_array( 'id', $t_fields ) ) {
			$t_output_issue['id_formatted'] = bug_format_id( $t_issue_id );
		}

		if( in_array( 'date_submitted', $t_fields ) ) {
			$t_output_issue['created_at'] = date( config_get( 'normal_date_format' ), $t_issue->date_submitted );
		}

		if( in_array( 'last_updated', $t_fields ) ) {
			$t_output_issue['updated_at'] = date( config_get( 'normal_date_format' ), $t_issue->last_updated );
		}

		$t_output_configs['tags_show'] =
			in_array( 'tags', $t_fields ) &&
			access_has_bug_level( config_get( 'tag_view_threshold' ), $t_issue_id );

		$t_output_configs['tag_can_attach'] =
			$t_output_configs['tags_show'] &&
			!$t_force_readonly &&
			access_has_bug_level( config_get( 'tag_attach_threshold' ), $t_issue_id );

		if( in_array( 'view_state', $t_fields ) ) {
			$t_output_issue['view_state'] = get_enum_element( 'view_state', $t_issue->view_state );
		}

		# Due date
		$t_show_due_date = in_array( 'due_date', $t_fields ) && access_has_bug_level( config_get( 'due_date_view_threshold' ), $t_issue_id );
		if( $t_show_due_date ) {
			$t_output_issue['overdue'] = bug_is_overdue( $t_issue_id );

			if( !date_is_null( $t_issue->due_date ) ) {
				$t_output_issue['due_date'] = date( config_get( 'normal_date_format' ), $t_issue->due_date );
			} else {
				$t_output_issue['due_date'] = '';
			}
		}

		if( in_array( 'reporter', $t_fields ) ) {
			$t_output_issue['reporter'] = mci_account_get_array_by_id( $t_issue->reporter_id );
		}

		if( in_array( 'handler', $t_fields ) && access_has_bug_level( config_get( 'view_handler_threshold' ), $t_issue_id ) ) {
			$t_output_issue['handler'] = mci_account_get_array_by_id( $t_issue->handler_id );
		}

		$t_output_configs['relationship_show'] = !$t_force_readonly;

		$t_output_configs['sponsorship_show'] =
			config_get( 'enable_sponsorship' ) &&
			access_has_bug_level( config_get( 'view_sponsorship_total_threshold' ), $t_issue_id );

		$t_show_profiles = config_get( 'enable_profiles' );
		$t_output_configs['show_profiles'] = $t_show_profiles;
		if( $t_show_profiles ) {
			if( in_array( 'platform', $t_fields ) ) {
				$t_output_issue['platform'] = $t_issue->platform;
			}
	
			if( in_array( 'os', $t_fields ) ) {
				$t_output_issue['os'] = $t_issue->os;
			}

			if( in_array( 'os_version', $t_fields ) ) {
				$t_output_issue['os_version'] = $t_issue->os_build;
			}
		}

		if( in_array( 'projection', $t_fields ) ) {
			$t_output_issue['projection'] = get_enum_element( 'projection', $t_issue->projection );
		}

		if( in_array( 'eta', $t_fields ) ) {
			$t_output_issue['eta'] = get_enum_element( 'eta', $t_issue->eta );
		}

		if( in_array( 'category_id', $t_fields ) ) {
			$t_output_issue['category'] = category_full_name( $t_issue->category_id );
		}

		if( in_array( 'priority', $t_fields ) ) {
			$t_output_issue['priority'] = get_enum_element( 'priority', $t_issue->priority );
		}

		if( in_array( 'severity', $t_fields ) ) {
			$t_output_issue['severity'] = get_enum_element( 'severity', $t_issue->severity );
		}

		if( in_array( 'reproducibility', $t_fields ) ) {
			$t_output_issue['reproducibility'] = get_enum_element( 'reproducibility', $t_issue->reproducibility );
		}

		if( in_array( 'status', $t_fields ) ) {
			$t_output_issue['status'] = get_enum_element( 'status', $t_issue->status );
		}

		if( in_array( 'resolution', $t_fields ) ) {
			$t_output_issue['resolution'] = get_enum_element( 'resolution', $t_issue->resolution );
		}

		if( in_array( 'summary', $t_fields ) ) {
			$t_output_issue['summary'] = bug_format_summary( $t_issue_id, SUMMARY_FIELD );
		}

		if( in_array( 'description', $t_fields ) ) {
			$t_output_issue['description'] = $t_issue->description;
		}

		if( !is_blank( $t_issue->steps_to_reproduce ) && in_array( 'steps_to_reproduce', $t_fields ) ) {
			$t_output_issue['steps_to_reproduce'] = $t_issue->steps_to_reproduce;
		}

		if( !is_blank( $t_issue->additional_information ) && in_array( 'additional_info', $t_fields ) ) {
			$t_output_issue['additional_information'] = $t_issue->additional_information;
		}

		$t_output_configs['show_monitor'] = !$t_force_readonly &&
			access_has_bug_level( config_get( 'show_monitor_list_threshold' ), $t_issue_id );

		if( $t_output_configs['show_monitor'] ) {
			$t_output_configs['monitor_can_delete'] = access_has_bug_level( config_get( 'monitor_delete_others_bug_threshold' ), $t_issue_id ) ? true : false;
			$t_output_configs['monitor_can_add'] = access_has_bug_level( config_get( 'monitor_add_others_bug_threshold' ), $t_issue_id ) ? true : false;
			$t_monitor_user_ids = bug_get_monitors( $t_issue_id );
			$t_monitor_users = [];
			foreach( $t_monitor_user_ids as $t_user_id ) {
				$t_monitor_user = mci_account_get_array_by_id( $t_user_id );
				$t_monitor_users[] = $t_monitor_user;
			}

			$t_output_issue['monitor_users'] = $t_monitor_users;
		}

		$t_related_custom_field_ids = custom_field_get_linked_ids( $t_project_id );
		custom_field_cache_values( array( $t_issue_id ), $t_related_custom_field_ids );

		$t_custom_fields = array();
		foreach( $t_related_custom_field_ids as $t_id ) {
			if( !custom_field_has_read_access( $t_id, $t_issue_id ) ) {
				continue;
			} # has read access

			$t_def = custom_field_get_definition( $t_id );
			$t_custom_fields[] = array( 'id' => $t_id, 'definition' => $t_def );
		}

		if( !empty( $t_custom_fields ) ) {
			$t_output_issue['custom_fields'] = $t_custom_fields;
		}

		$t_links = event_signal( 'EVENT_MENU_ISSUE', $t_issue_id );
		$t_output_issue['links'] = $t_links;

		# Mark the added issue as visited so that it appears on the last visited list.
		last_visited_issue( $t_issue_id );

		return array( 'issue_view' => $t_output_issue, 'flags' => $t_output_configs );
	}
}

