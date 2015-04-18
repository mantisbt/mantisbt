<?php
# MantisBT - a php based bugtracking system

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
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @author Marcello Scata' <marcelloscata at users.sourceforge.net> ITALY
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	access_ensure_project_level( config_get( 'view_configuration_threshold' ) );

	html_page_top( lang_get( 'permissions_summary_report' ) );

	print_manage_menu( 'adm_permissions_report.php' );
	print_manage_config_menu( 'adm_permissions_report.php' );

	function get_section_begin_apr( $p_section_name ) {
		$t_access_levels = MantisEnum::getValues( config_get( 'access_levels_enum_string' ) );

		$t_output = '<table class="width100">';
		$t_output .= '<tr><td class="form-title-caps" colspan="' . ( count( $t_access_levels ) + 1 ) . '">' . $p_section_name . '</td></tr>' . "\n";
		$t_output .= '<tr><td class="form-title" width="40%">' . lang_get( 'perm_rpt_capability' ) . '</td>';

		foreach( $t_access_levels as $t_access_level ) {
			$t_output .= '<td class="form-title" style="text-align:center">&#160;' . MantisEnum::getLabel( lang_get( 'access_levels_enum_string' ), $t_access_level ) . '&#160;</td>';
		}

		$t_output .= '</tr>' . "\n";

		return $t_output;
	}

	function get_capability_row( $p_caption, $p_access_level ) {
		$t_access_levels = MantisEnum::getValues( config_get( 'access_levels_enum_string' ) );

		$t_output = '<tr ' . helper_alternate_class() . '><td>' . string_display( $p_caption ) . '</td>';
		foreach( $t_access_levels as $t_access_level ) {
			if ( $t_access_level >= (int)$p_access_level ) {
				$t_value = '<img src="images/ok.gif" width="20" height="15" alt="X" title="X" />';
			} else {
				$t_value = '&#160;';
			}

			$t_output .= '<td class="center">' . $t_value . '</td>';
		}

		$t_output .= '</tr>' . "\n";

		return $t_output;
	}

	function get_section_end() {
		$t_output = '</table><br />' . "\n";
		return $t_output;
	}

	echo '<br /><br />';

	# News
    if( config_get( 'news_enabled' ) == ON ) {
        echo get_section_begin_apr( lang_get( 'news' ) );
        echo get_capability_row( lang_get( 'view_private_news' ), config_get( 'private_news_threshold' ) );
        echo get_capability_row( lang_get( 'manage_news' ), config_get( 'manage_news_threshold' ) );
        echo get_section_end();
	}

	# Attachments
	if( config_get( 'allow_file_upload' ) == ON ) {
		echo get_section_begin_apr( lang_get( 'attachments' ) );
		echo get_capability_row( lang_get( 'view_list_of_attachments' ), config_get( 'view_attachments_threshold' ) );
		echo get_capability_row( lang_get( 'download_attachments' ), config_get( 'download_attachments_threshold' ) );
		echo get_capability_row( lang_get( 'delete_attachments' ), config_get( 'delete_attachments_threshold' ) );
		echo get_capability_row( lang_get( 'upload_issue_attachments' ), config_get( 'upload_bug_file_threshold' ) );
		echo get_section_end();
	}

	# Filters
	echo get_section_begin_apr( lang_get( 'filters' ) );
	echo get_capability_row( lang_get( 'save_filters' ), config_get( 'stored_query_create_threshold' ) );
	echo get_capability_row( lang_get( 'save_filters_as_shared' ), config_get( 'stored_query_create_shared_threshold' ) );
	echo get_capability_row( lang_get( 'use_saved_filters' ), config_get( 'stored_query_use_threshold' ) );
	echo get_section_end();

	# Projects
	echo get_section_begin_apr( lang_get( 'projects_link' ) );
	echo get_capability_row( lang_get( 'create_project' ), config_get( 'create_project_threshold' ) );
	echo get_capability_row( lang_get( 'delete_project' ), config_get( 'delete_project_threshold' ) );
	echo get_capability_row( lang_get( 'manage_projects_link' ), config_get( 'manage_project_threshold' ) );
	echo get_capability_row( lang_get( 'manage_user_access_to_project' ), config_get( 'project_user_threshold' ) );
	echo get_capability_row( lang_get( 'automatically_included_in_private_projects' ), config_get( 'private_project_threshold' ) );
	echo get_section_end();

	# Project Documents
	if( config_get( 'enable_project_documentation' ) == ON ) {
		echo get_section_begin_apr( lang_get( 'project_documents' ) );
		echo get_capability_row( lang_get( 'view_project_documents' ), config_get( 'view_proj_doc_threshold' ) );
		echo get_capability_row( lang_get( 'upload_project_documents' ), config_get( 'upload_project_file_threshold' ) );
		echo get_section_end();
	}

	# Custom Fields
	echo get_section_begin_apr( lang_get( 'custom_fields_setup' ) );
	echo get_capability_row( lang_get( 'manage_custom_field_link' ), config_get( 'manage_custom_fields_threshold' ) );
	echo get_capability_row( lang_get( 'link_custom_fields_to_projects' ), config_get( 'custom_field_link_threshold' ) );
	echo get_section_end();

	# Sponsorships
	if( config_get( 'enable_sponsorship' ) == ON ) {
		echo get_section_begin_apr( lang_get( 'sponsorships' ) );
		echo get_capability_row( lang_get( 'view_sponsorship_details' ), config_get( 'view_sponsorship_details_threshold' ) );
		echo get_capability_row( lang_get( 'view_sponsorship_total' ), config_get( 'view_sponsorship_total_threshold' ) );
		echo get_capability_row( lang_get( 'sponsor_issue' ), config_get( 'sponsor_threshold' ) );
		echo get_capability_row( lang_get( 'assign_sponsored_issue' ), config_get( 'assign_sponsored_bugs_threshold' ) );
		echo get_capability_row( lang_get( 'handle_sponsored_issue' ), config_get( 'handle_sponsored_bugs_threshold' ) );
		echo get_section_end();
	}

	# Others
	echo get_section_begin_apr( lang_get('others') );
	echo get_capability_row( lang_get( 'view' ) . ' ' . lang_get( 'summary_link' ), config_get( 'view_summary_threshold' ) );
	echo get_capability_row( lang_get( 'see_email_addresses_of_other_users' ), config_get( 'show_user_email_threshold' ) );
	echo get_capability_row( lang_get( 'send_reminders' ), config_get( 'bug_reminder_threshold' ) );
	echo get_capability_row( lang_get( 'add_profiles' ), config_get( 'add_profile_threshold' ) );
	echo get_capability_row( lang_get( 'manage_users_link' ), config_get( 'manage_user_threshold' ) );
	echo get_capability_row( lang_get( 'notify_of_new_user_created' ), config_get( 'notify_new_user_created_threshold_min' ) );
	echo get_section_end();

	html_page_bottom();
