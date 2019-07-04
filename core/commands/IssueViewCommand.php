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
class IssueViewCommand extends Command {
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

		$t_user_id = auth_get_current_user_id();
		$t_issue_id = $this->query( 'id' );

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

		$t_output_issue = array();
		$t_output_configs = array();

		# Fields to show on the issue view page
		$t_fields = config_get( 'bug_view_page_fields' );
		$t_fields = columns_filter_disabled( $t_fields );

		$t_output_configs['summary_show'] = in_array( 'summary', $t_fields );
		$t_output_configs['description_show'] = in_array( 'description', $t_fields );

		# Versions
		$t_show_versions = version_should_show_product_version( $t_project_id );
		$t_output_configs['versions_show'] = $t_show_versions;

		$t_show_product_version = $t_show_versions && in_array( 'product_version', $t_fields );
		$t_output_configs['versions_product_version_show'] = $t_show_product_version;

		$t_show_fixed_in_version = $t_show_versions && in_array( 'fixed_in_version', $t_fields );
		$t_output_configs['versions_fixed_in_version_show'] = $t_show_fixed_in_version;

		$t_show_product_build =
			$t_show_versions &&
			in_array( 'product_build', $t_fields ) &&
			config_get( 'enable_product_build' ) == ON;

		$t_output_configs['versions_product_build_show'] = $t_show_product_build;

		$t_show_target_version =
			$t_show_versions &&
			in_array( 'target_version', $t_fields ) &&
			access_has_bug_level( config_get( 'roadmap_view_threshold' ), $t_issue_id );
		$t_output_configs['versions_target_version_show'] = $t_show_target_version;

		$t_form_title = lang_get( 'bug_view_title' );
		$t_output_issue['form_title'] = $t_form_title;

		if( config_get_global( 'wiki_enable' ) == ON ) {
			$t_output_issue['wiki_link'] = 'wiki.php?id=' . $t_issue_id;
		}

		$t_output_configs['history_show'] =
			config_get( 'history_default_visible' ) &&
			access_has_bug_level( config_get( 'view_history_threshold' ), $t_issue_id );

		$t_output_configs['reminder_can_add'] =
			!current_user_is_anonymous() &&
			!bug_is_readonly( $t_issue_id ) &&
			access_has_bug_level( config_get( 'bug_reminder_threshold' ), $t_issue_id );

		if( in_array( 'id', $t_fields ) ) {
			$t_output_issue['id_formatted'] = bug_format_id( $t_issue_id );
		}

		if( in_array( 'date_submitted', $t_fields ) ) {
			$t_output_issue['created_at'] = date( config_get( 'normal_date_format' ), strtotime( $t_issue['created_at'] ) );
		}

		if( in_array( 'last_updated', $t_fields ) ) {
			$t_output_issue['updated_at'] = date( config_get( 'normal_date_format' ), strtotime( $t_issue['updated_at'] ) );
		}

		$t_output_configs['additional_information_show'] = isset( $t_issue['additional_information'] ) && !is_blank( $t_issue['additional_information'] ) && in_array( 'additional_info', $t_fields );
		$t_output_configs['steps_to_reproduce_show'] = isset( $t_issue['steps_to_reproduce'] ) && !is_blank( $t_issue['steps_to_reproduce'] ) && in_array( 'steps_to_reproduce', $t_fields );

		$t_output_configs['tags_show'] =
			in_array( 'tags', $t_fields ) &&
			access_has_bug_level( config_get( 'tag_view_threshold' ), $t_issue_id );

		$t_output_configs['tags_can_attach'] =
			$t_output_configs['tags_show'] &&
			!$t_force_readonly &&
			access_has_bug_level( config_get( 'tag_attach_threshold' ), $t_issue_id );

		# Due date
		$t_show_due_date = in_array( 'due_date', $t_fields ) && access_has_bug_level( config_get( 'due_date_view_threshold' ), $t_issue_id );
		if( $t_show_due_date ) {
			$t_output_issue['overdue'] = bug_is_overdue( $t_issue_id );

			if( isset( $t_issue['due_date'] ) ) {
				$t_output_issue['due_date'] = date( config_get( 'normal_date_format' ), strtotime( $t_issue['due_date'] ) );
			} else {
				$t_output_issue['due_date'] = '';
			}
		}

		$t_output_configs['relationships_show'] = !$t_force_readonly;

		$t_output_configs['sponsorships_show'] =
			config_get( 'enable_sponsorship' ) &&
			access_has_bug_level( config_get( 'view_sponsorship_total_threshold' ), $t_issue_id );

		$t_output_configs['profiles_show'] = config_get( 'enable_profiles' ) != OFF;
		$t_output_configs['profiles_platform_show'] = $t_output_configs['profiles_show'] && in_array( 'platform', $t_fields );
		$t_output_configs['profiles_os_show'] = $t_output_configs['profiles_show'] && in_array( 'os', $t_fields );
		$t_output_configs['profiles_os_version_show'] = $t_output_configs['profiles_show'] && in_array( 'os_version', $t_fields );

		$t_output_configs['monitor_show'] =
			!$t_force_readonly &&
			access_has_bug_level( config_get( 'show_monitor_list_threshold' ), $t_issue_id );

		if( $t_output_configs['monitor_show'] ) {
			$t_output_configs['monitor_can_delete'] = access_has_bug_level( config_get( 'monitor_delete_others_bug_threshold' ), $t_issue_id ) ? true : false;
			$t_output_configs['monitor_can_add'] = access_has_bug_level( config_get( 'monitor_add_others_bug_threshold' ), $t_issue_id ) ? true : false;
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

		return array(
			'issue' => $t_issue,
			'issue_view' => $t_output_issue,
			'flags' => $t_output_configs );
	}
}

