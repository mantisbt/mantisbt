<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: adm_permissions_report.php,v 1.4 2004-11-02 19:50:45 marcelloscata Exp $
	# --------------------------------------------------------

	# ======================================================================
	# Author: Marcello Scata' <marcelloscata at users.sourceforge.net> ITALY
	# ======================================================================

	require_once( 'core.php' );

	access_ensure_global_level( ADMINISTRATOR );

	$t_core_path = config_get( 'core_path' );

	html_page_top1( lang_get( 'permissions_summary_report' ) );
	html_page_top2();

	print_manage_menu( 'adm_permissions_report.php' );

	function get_section_begin( $p_section_name ) {
		$t_access_levels = explode_enum_string( config_get( 'access_levels_enum_string' ) );
		$t_output = '<table class="width100">';
		$t_output .= '<tr><td class="form-title" colspan=' . ( count( $t_access_levels ) + 1 ) . '>' . strtoupper( $p_section_name ) . '</td></tr>' . "\n";
		$t_output .= '<tr><td class="form-title" width="40%">' . lang_get( 'perm_rpt_capability' ) . '</td>';
		foreach( $t_access_levels as $t_access_level ) {
			$t_entry_array = explode_enum_arr( $t_access_level );
			$t_output .= '<td class="form-title" style="text-align:center">&nbsp;' . get_enum_to_string( config_get( 'access_levels_enum_string' ), $t_entry_array[0] ) . '&nbsp;</td>';
		}
		$t_output .= '</tr>' . "\n";

		return $t_output;
	}

	function get_capability_row( $p_caption, $p_access_level ) {
		$t_access_levels = explode_enum_string( config_get( 'access_levels_enum_string' ) );

		$t_output = '<tr ' . helper_alternate_class() . '><td>' . string_display( $p_caption ) . '</td>';
		foreach( $t_access_levels as $t_access_level ) {
			$t_entry_array = explode_enum_arr( $t_access_level );

			if ( (int)$t_entry_array[0] >= (int)$p_access_level ) {
				$t_value = '<img src="images/ok.gif" width=20 height=15 title="X">';
			} else {
				$t_value = '&nbsp;';
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

	function get_section_begin_for_email( $p_section_name ) {
		$t_access_levels = explode_enum_string( config_get( 'access_levels_enum_string' ) );
		$t_output = '<table class="width100">';
		$t_output .= '<tr><td class="form-title" colspan=' . ( count( $t_access_levels ) + 5 ) . '>' . strtoupper( $p_section_name ) . '</td></tr>' . "\n";
		$t_output .= '<tr><td class="form-title" width="30%">Message</td>';
		$t_output .= '<td class="form-title" style="text-align:center">&nbsp;reporter&nbsp;</td><td class="form-title" style="text-align:center">&nbsp;handler&nbsp;</td><td class="form-title" style="text-align:center">user<br>&nbsp;monitoring&nbsp;</td><td class="form-title" style="text-align:center">user<br>added<br>&nbsp;bugnote&nbsp;</td>';
		foreach( $t_access_levels as $t_access_level ) {
			$t_entry_array = explode_enum_arr( $t_access_level );
			$t_output .= '<td class="form-title" style="text-align:center">&nbsp;' . get_enum_to_string( config_get( 'access_levels_enum_string' ), $t_entry_array[0] ) . '&nbsp;</td>';
		}
		$t_output .= '</tr>' . "\n";

		return $t_output;
	}

	function get_capability_row_for_email( $p_caption, $p_message_type ) {
		$t_access_levels = explode_enum_string( config_get( 'access_levels_enum_string' ) );

		$t_output = '<tr ' . helper_alternate_class() . '><td>' . string_display( $p_caption ) . '</td>';
		$t_output .= '<td class="center">' . ( email_notify_flag( $p_message_type, 'reporter' ) ? '<img src="images/ok.gif" width=20 height=15 title="X">' : '&nbsp;' ) . '</td>';
		$t_output .= '<td class="center">' . ( email_notify_flag( $p_message_type, 'handler' ) ? '<img src="images/ok.gif" width=20 height=15 title="X">' : '&nbsp;' ) . '</td>';
		$t_output .= '<td class="center">' . ( email_notify_flag( $p_message_type, 'monitor' ) ? '<img src="images/ok.gif" width=20 height=15 title="X">' : '&nbsp;' ) . '</td>';
		$t_output .= '<td class="center">' . ( email_notify_flag( $p_message_type, 'bugnotes' ) ? '<img src="images/ok.gif" width=20 height=15 title="X">' : '&nbsp;' ) . '</td>';

		$t_threshold_min = email_notify_flag( $p_message_type, 'threshold_min' );
		$t_threshold_max = email_notify_flag( $p_message_type, 'threshold_max' );

		foreach( $t_access_levels as $t_access_level ) {
			$t_entry_array = explode_enum_arr( $t_access_level );
			if ( ( (int)$t_entry_array[0] >= (int)$t_threshold_min ) &&
					( (int)$t_entry_array[0] <= (int)$t_threshold_max ) ) {
				$t_value = '<img src="images/ok.gif" width=20 height=15 title="X">';
			} else {
				$t_value = '&nbsp;';
			}
			$t_output .= '<td class="center">' . $t_value . '</td>';
		}
		$t_output .= '</tr>' . "\n";

		return $t_output;
	}

	function get_section_end_for_email() {
		$t_output = '</table><br />' . "\n";
		return $t_output;
	}

	echo '<br><br>';

	# Issues
	echo get_section_begin( lang_get( 'issues' ) );
	echo get_capability_row( lang_get( 'report_issue' ), config_get( 'report_bug_threshold' ) );
	echo get_capability_row( lang_get( 'update_issue' ), config_get( 'update_bug_threshold' ) );
	echo get_capability_row( lang_get( 'monitor_issue' ), config_get( 'monitor_bug_threshold' ) );
	echo get_capability_row( lang_get( 'handle_issue' ), config_get( 'handle_bug_threshold' ) );
	echo get_capability_row( lang_get( 'move_issue' ), config_get( 'move_bug_threshold' ) );
	echo get_capability_row( lang_get( 'delete_issue' ), config_get( 'delete_bug_threshold' ) );
	echo get_capability_row( lang_get( 'reopen_issue' ), config_get( 'reopen_bug_threshold' ) );
	echo get_capability_row( lang_get( 'view_private_issues' ), config_get( 'private_bug_threshold' ) );
	echo get_capability_row( lang_get( 'update_readonly_issues' ), config_get( 'update_readonly_bug_threshold' ) );
	echo get_capability_row( lang_get( 'update_issue_status' ), config_get( 'update_bug_status_threshold' ) );
	echo get_capability_row( lang_get( 'set_view_status' ), config_get( 'set_view_status_threshold' ) );
	echo get_capability_row( lang_get( 'update_view_status' ), config_get( 'change_view_status_threshold' ) );
	echo get_capability_row( lang_get( 'show_list_of_users_monitoring_issue' ), config_get( 'show_monitor_list_threshold' ) );
	echo get_section_end();

	# Notes
	echo get_section_begin( lang_get( 'notes' ) );
	echo get_capability_row( lang_get( 'add_notes' ), config_get( 'add_bugnote_threshold' ) );
	echo get_capability_row( lang_get( 'update_notes' ), config_get( 'update_bugnote_threshold' ) );
	echo get_capability_row( lang_get( 'delete_note' ), config_get( 'delete_bugnote_threshold' ) );
	echo get_capability_row( lang_get( 'view_private_notes' ), config_get( 'private_bugnote_threshold' ) );
	echo get_section_end();

	# News
	echo get_section_begin( lang_get( 'news' ) );
	echo get_capability_row( lang_get( 'view_private_news' ), config_get( 'private_news_threshold' ) );
	echo get_capability_row( lang_get( 'manage_news' ), config_get( 'manage_news_threshold' ) );
	echo get_section_end();

	# Attachments
	if( config_get( 'allow_file_upload' ) == ON ) {
		echo get_section_begin( lang_get( 'attachments' ) );
		echo get_capability_row( lang_get( 'view_list_of_attachments' ), config_get( 'view_attachments_threshold' ) );
		echo get_capability_row( lang_get( 'download_attachments' ), config_get( 'download_attachments_threshold' ) );
		echo get_capability_row( lang_get( 'delete_attachments' ), config_get( 'delete_attachments_threshold' ) );
		echo get_capability_row( lang_get( 'upload_issue_attachments' ), config_get( 'upload_bug_file_threshold' ) );
		echo get_section_end();
	}

	# Filters
	echo get_section_begin( lang_get( 'filters' ) );
	echo get_capability_row( lang_get( 'save_filters' ), config_get( 'stored_query_create_threshold' ) );
	echo get_capability_row( lang_get( 'save_filters_as_shared' ), config_get( 'stored_query_create_shared_threshold' ) );
	echo get_capability_row( lang_get( 'use_saved_filters' ), config_get( 'stored_query_use_threshold' ) );
	echo get_section_end();

	# Projects
	echo get_section_begin( lang_get( 'projects_link' ) );
	echo get_capability_row( lang_get( 'create_project' ), config_get( 'create_project_threshold' ) );
	echo get_capability_row( lang_get( 'delete_project' ), config_get( 'delete_project_threshold' ) );
	echo get_capability_row( lang_get( 'manage_projects_link' ), config_get( 'manage_project_threshold' ) );
	echo get_capability_row( lang_get( 'manage_user_access_to_project' ), config_get( 'project_user_threshold' ) );
	echo get_capability_row( lang_get( 'automatically_included_in_private_projects' ), config_get( 'private_project_threshold' ) );
	echo get_section_end();

	# Project Documents
	if( config_get( 'enable_project_documentation' ) == ON ) {
		echo get_section_begin( lang_get( 'project_documents' ) );
		echo get_capability_row( lang_get( 'view_project_documents' ), config_get( 'view_proj_doc_threshold' ) );
		echo get_capability_row( lang_get( 'upload_project_documents' ), config_get( 'upload_project_file_threshold' ) );
		echo get_section_end();
	}

	# Custom Fields
	echo get_section_begin( lang_get( 'custom_fields_setup' ) );
	echo get_capability_row( lang_get( 'manage_custom_field_link' ), config_get( 'manage_custom_fields_threshold' ) );
	echo get_capability_row( lang_get( 'link_custom_fields_to_projects' ), config_get( 'custom_field_link_threshold' ) );
	echo get_section_end();

	# Sponsorships
	if( config_get( 'enable_sponsorship' ) == ON ) {
		echo get_section_begin( lang_get( 'sponsorships' ) );
		echo get_capability_row( lang_get( 'view_sponsorship_details' ), config_get( 'view_sponsorship_details_threshold' ) );
		echo get_capability_row( lang_get( 'view_sponsorship_total' ), config_get( 'view_sponsorship_total_threshold' ) );
		echo get_capability_row( lang_get( 'sponsor_issue' ), config_get( 'sponsor_threshold' ) );
		echo get_capability_row( lang_get( 'assign_sponsored_issue' ), config_get( 'assign_sponsored_bugs_threshold' ) );
		echo get_capability_row( lang_get( 'handle_sponsored_issue' ), config_get( 'handle_sponsored_bugs_threshold' ) );
		echo get_section_end();
	}

	# Others
	echo get_section_begin( lang_get('others') );
	echo get_capability_row( lang_get( 'view' ) . ' ' . lang_get( 'summary_link' ), config_get( 'view_summary_threshold' ) );
	echo get_capability_row( lang_get( 'view' ) . ' ' . lang_get( 'changelog_link' ), config_get( 'view_changelog_threshold' ) );
	echo get_capability_row( lang_get( 'view' ) . ' ' . lang_get( 'assigned_to' ), config_get( 'view_handler_threshold' ) );
	echo get_capability_row( lang_get( 'view' ) . ' ' . lang_get( 'bug_history' ), config_get( 'view_history_threshold' ) );
	echo get_capability_row( lang_get( 'see_email_addresses_of_other_users' ), config_get( 'show_user_email_threshold' ) );
	echo get_capability_row( lang_get( 'send_reminders' ), config_get( 'bug_reminder_threshold' ) );
	echo get_capability_row( lang_get( 'add_profiles' ), config_get( 'add_profile_threshold' ) );
	echo get_capability_row( lang_get( 'manage_users_link' ), config_get( 'manage_user_threshold' ) );
	echo get_capability_row( lang_get( 'notify_of_new_user_created' ), config_get( 'notify_new_user_created_threshold_min' ) );
	echo get_section_end();

	# Email notifications
	if( config_get( 'enable_email_notification' ) == ON ) {
		echo get_section_begin_for_email( lang_get( 'email_notification' ) );
		echo get_capability_row_for_email( lang_get( 'email_on_new' ), 'new' );
		echo get_capability_row_for_email( lang_get( 'email_on_assigned' ), 'owner' );
		echo get_capability_row_for_email( lang_get( 'email_on_reopened' ), 'reopen' );
		echo get_capability_row_for_email( lang_get( 'email_on_deleted' ), 'deleted' );
		echo get_capability_row_for_email( lang_get( 'email_on_bugnote_added' ), 'bugnote' );
		if( config_get( 'enable_sponsorship' ) == ON ) {
			echo get_capability_row_for_email( lang_get( 'email_on_sponsorship_changed' ), 'sponsor' );
		}
		if( config_get( 'enable_relationship' ) == ON ) {
			echo get_capability_row_for_email( lang_get( 'email_on_relationship_changed' ), 'relationship' );
		}
		$t_statuses = explode_enum_string( config_get( 'status_enum_string' ) );
		foreach( $t_statuses as $t_status ) {
			list( $t_state, $t_label ) = explode_enum_arr( $t_status );
			echo get_capability_row_for_email( lang_get( 'status_changed_to' ) . ' \'' . get_enum_element( 'status', $t_state ) . '\'', $t_label );
		}

		echo get_section_end_for_email();
	}

	html_page_bottom1( __FILE__ );
?>
