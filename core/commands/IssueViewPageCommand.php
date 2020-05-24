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
 *     "id": 1234
 *   },
 *   "payload": {
 *   },
 *   "options: {
 *     "force_readonly": false
 *   }
 * }
 */

/**
 * A command that returns issue information to view.
 */
class IssueViewPageCommand extends Command {
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
		$t_issue_id = $this->query( 'id' );
		bug_ensure_exists( $t_issue_id );
		access_ensure_bug_level( config_get( 'view_bug_threshold' ), $t_issue_id );
	}

	/**
	 * Process the command.
	 *
	 * @returns array Command response
	 */
	protected function process() {
		$t_force_readonly = $this->option( 'force_readonly', false );
		$t_anonymous_user = current_user_is_anonymous();

		$t_user_id = auth_get_current_user_id();
		$t_issue_id = $this->query( 'id' );
		$t_issue_readonly = $t_force_readonly || bug_is_readonly( $t_issue_id );

		ApiObjectFactory::$soap = false;

		$t_issue_data = bug_get( $t_issue_id, true );
		$t_lang = mci_get_user_lang( $t_user_id );
		$t_issue = mci_issue_data_as_array( $t_issue_data, $t_user_id, $t_lang );

		$t_issue_id = (int)$t_issue['id'];
		$t_project_id = (int)$t_issue['project']['id'];

		# in case the current project is not the same project of the bug we are
		# viewing, override the current project. This to avoid problems with
		# categories and handlers lists etc.
		global $g_project_override;
		$g_project_override = $t_project_id;

		$t_issue_view = array();
		$t_flags = array();

		# Fields to show on the issue view page
		$t_fields = config_get( 'bug_view_page_fields' );
		$t_fields = columns_filter_disabled( $t_fields );

		$t_flags['summary_show'] = in_array( 'summary', $t_fields );
		$t_flags['description_show'] = in_array( 'description', $t_fields );

		# Versions
		$t_show_versions = version_should_show_product_version( $t_project_id );
		$t_flags['versions_show'] = $t_show_versions;
		$t_flags['versions_product_version_show'] = $t_show_versions && in_array( 'product_version', $t_fields );
		$t_flags['versions_fixed_in_version_show'] = $t_show_versions && in_array( 'fixed_in_version', $t_fields );

		$t_flags['versions_product_build_show'] =
			$t_show_versions &&
			in_array( 'product_build', $t_fields ) &&
			config_get( 'enable_product_build' ) == ON;

		$t_flags['versions_target_version_show'] =
			$t_show_versions &&
			in_array( 'target_version', $t_fields ) &&
			access_has_bug_level( config_get( 'roadmap_view_threshold' ), $t_issue_id );

		$t_issue_view['form_title'] = lang_get( 'bug_view_title' );

		if( config_get_global( 'wiki_enable' ) == ON ) {
			$t_issue_view['wiki_link'] = 'wiki.php?id=' . $t_issue_id;
		}

		$t_flags['history_show'] =
			access_has_bug_level( config_get( 'view_history_threshold' ), $t_issue_id );

		$t_flags['reminder_can_add'] =
			!current_user_is_anonymous() &&
			!$t_issue_readonly &&
			access_has_bug_level( config_get( 'bug_reminder_threshold' ), $t_issue_id );

		$t_flags['id_show'] = in_array( 'id', $t_fields );
		if( $t_flags['id_show'] ) {
			$t_issue_view['id_formatted'] = bug_format_id( $t_issue_id );
		}

		$t_flags['created_at_show'] = in_array( 'date_submitted', $t_fields );
		if( $t_flags['created_at_show'] ) {
			$t_issue_view['created_at'] = date( config_get( 'normal_date_format' ), strtotime( $t_issue['created_at'] ) );
		}

		$t_flags['updated_at_show'] = in_array( 'last_updated', $t_fields );
		if( $t_flags['updated_at_show'] ) {
			$t_issue_view['updated_at'] = date( config_get( 'normal_date_format' ), strtotime( $t_issue['updated_at'] ) );
		}

		$t_flags['additional_information_show'] =
			isset( $t_issue['additional_information'] ) &&
			!is_blank( $t_issue['additional_information'] ) &&
			in_array( 'additional_info', $t_fields );

		$t_flags['steps_to_reproduce_show'] =
			isset( $t_issue['steps_to_reproduce'] ) &&
			!is_blank( $t_issue['steps_to_reproduce'] ) &&
			in_array( 'steps_to_reproduce', $t_fields );

		$t_flags['tags_show'] =
			in_array( 'tags', $t_fields ) &&
			access_has_bug_level( config_get( 'tag_view_threshold' ), $t_issue_id );

		$t_flags['tags_can_attach'] =
			$t_flags['tags_show'] &&
			!$t_force_readonly &&
			access_has_bug_level( config_get( 'tag_attach_threshold' ), $t_issue_id );

		# Due date
		$t_flags['due_date_show'] = in_array( 'due_date', $t_fields ) && access_has_bug_level( config_get( 'due_date_view_threshold' ), $t_issue_id );
		if( $t_flags['due_date_show'] ) {
			$t_issue_view['overdue'] = bug_overdue_level( $t_issue_id );

			if( isset( $t_issue['due_date'] ) ) {
				$t_issue_view['due_date'] = date( config_get( 'normal_date_format' ), strtotime( $t_issue['due_date'] ) );
			} else {
				$t_issue_view['due_date'] = '';
			}
		}

		$t_flags['relationships_show'] = true;
		$t_flags['relationships_can_update'] =
			!$t_force_readonly &&
			!bug_is_readonly( $t_issue_id ) &&
			access_has_bug_level( config_get( 'update_bug_threshold' ), $t_issue_id );

		$t_flags['sponsorships_show'] =
			config_get( 'enable_sponsorship' ) &&
			access_has_bug_level( config_get( 'view_sponsorship_total_threshold' ), $t_issue_id );

		$t_flags['profiles_show'] = config_get( 'enable_profiles' ) != OFF;
		$t_flags['profiles_platform_show'] = $t_flags['profiles_show'] && in_array( 'platform', $t_fields );
		$t_flags['profiles_os_show'] = $t_flags['profiles_show'] && in_array( 'os', $t_fields );
		$t_flags['profiles_os_build_show'] = $t_flags['profiles_show'] && in_array( 'os_build', $t_fields );

		$t_flags['monitor_show'] =
			!$t_force_readonly &&
			access_has_bug_level( config_get( 'show_monitor_list_threshold' ), $t_issue_id );

		if( $t_flags['monitor_show'] ) {
			$t_flags['monitor_can_delete'] = access_has_bug_level( config_get( 'monitor_delete_others_bug_threshold' ), $t_issue_id ) ? true : false;
			$t_flags['monitor_can_add'] = access_has_bug_level( config_get( 'monitor_add_others_bug_threshold' ), $t_issue_id ) ? true : false;
		}

		if( !$t_force_readonly && !$t_anonymous_user ) {
			$t_is_monitoring = user_is_monitoring_bug( $t_user_id, $t_issue_id );
			$t_flags['can_monitor'] =  !$t_is_monitoring &&
				access_has_bug_level( config_get( 'monitor_bug_threshold' ), $t_issue_id );
			$t_flags['can_unmonitor'] = $t_is_monitoring;
		} else {
			$t_flags['can_monitor'] = false;
			$t_flags['can_unmonitor'] = false;
		}

		$t_flags['attachments_show'] = in_array( 'attachments', $t_fields );
		$t_flags['category_show'] = in_array( 'category_id', $t_fields );
		$t_flags['eta_show'] = in_array( 'eta', $t_fields );
		$t_flags['handler_show'] = in_array( 'handler', $t_fields );
		$t_flags['priority_show'] = in_array( 'priority', $t_fields ) && isset( $t_issue['priority'] );
		$t_flags['project_show'] = in_array( 'project', $t_fields ) && isset( $t_issue['project'] );
		$t_flags['projection_show'] = in_array( 'projection', $t_fields ) && isset( $t_issue['projection'] );
		$t_flags['reporter_show'] = in_array( 'reporter', $t_fields ) && isset( $t_issue['reporter'] );
		$t_flags['reproducibility_show'] = in_array( 'reproducibility', $t_fields ) && isset( $t_issue['reproducibility'] );
		$t_flags['resolution_show'] = in_array( 'resolution', $t_fields ) && isset( $t_issue['resolution'] );
		$t_flags['severity_show'] = in_array( 'severity', $t_fields ) && isset( $t_issue['severity'] );
		$t_flags['status_show'] = in_array( 'status', $t_fields ) && isset( $t_issue['status'] );
		$t_flags['view_state_show'] = in_array( 'view_state', $t_fields ) && isset( $t_issue['view_state'] );

		$t_flags['can_update'] = !$t_issue_readonly && access_has_bug_level( config_get( 'update_bug_threshold' ), $t_issue_id );
		$t_flags['can_assign'] = !$t_issue_readonly &&
			access_has_bug_level( config_get( 'update_bug_assign_threshold', config_get( 'update_bug_threshold' ) ), $t_issue_id );
		$t_flags['can_change_status'] = !$t_issue_readonly && access_has_bug_level( config_get( 'update_bug_status_threshold' ), $t_issue_id );

		$t_flags['can_clone'] = !$t_issue_readonly && access_has_bug_level( config_get( 'report_bug_threshold' ), $t_issue_id );
		$t_flags['can_reopen'] = !$t_force_readonly && access_can_reopen_bug( $t_issue_data );

		$t_closed_status = config_get( 'bug_closed_status_threshold', null, null, $t_issue_data->project_id );
		$t_flags['can_close'] = !$t_force_readonly &&
			access_can_close_bug( $t_issue_data ) && bug_check_workflow( $t_issue_data->status, $t_closed_status );

		$t_flags['can_move'] = !$t_issue_readonly && user_has_more_than_one_project( $t_user_id ) &&
			access_has_bug_level( config_get( 'move_bug_threshold' ), $t_issue_id );
		$t_flags['can_delete'] = !$t_issue_readonly && access_has_bug_level( config_get( 'delete_bug_threshold' ), $t_issue_id );

		if( $t_force_readonly ) {
			$t_flags['can_sticky'] = false;
			$t_flags['can_unsticky'] = false;
		} else {
			$t_sticky = bug_get_field( $t_issue_id, 'sticky' );
			$t_sticky_change = access_has_bug_level( config_get( 'set_bug_sticky_threshold' ), $t_issue_id );
			$t_flags['can_sticky'] = !$t_sticky && $t_sticky_change;
			$t_flags['can_unsticky'] = $t_sticky && $t_sticky_change;
		}

		$t_related_custom_field_ids = custom_field_get_linked_ids( $t_project_id );
		custom_field_cache_values( array( $t_issue_id ), $t_related_custom_field_ids );

		$t_links = event_signal( 'EVENT_MENU_ISSUE', $t_issue_id );
		$t_issue_view['links'] = $t_links;

		# Mark the added issue as visited so that it appears on the last visited list.
		last_visited_issue( $t_issue_id );

		return array(
			'issue' => $t_issue,
			'issue_view' => $t_issue_view,
			'flags' => $t_flags );
	}
}

