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
	 * This include file prints out the bug information
	 * $f_bug_id MUST be specified before the file is included
	 *
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2009  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */

	if ( !defined( 'BUG_VIEW_INC_ALLOW' ) ) {
		access_denied();
	}

	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	require_once( 'bug_api.php' );
	require_once( 'custom_field_api.php' );
	require_once( 'file_api.php' );
	require_once( 'compress_api.php' );
	require_once( 'date_api.php' );
	require_once( 'relationship_api.php' );
	require_once( 'last_visited_api.php' );
	require_once( 'tag_api.php' );

	$f_bug_id		= gpc_get_int( 'bug_id' );
	$f_history		= gpc_get_bool( 'history', config_get( 'history_default_visible' ) );

	bug_ensure_exists( $f_bug_id );

	access_ensure_bug_level( VIEWER, $f_bug_id );

	$tpl_bug = bug_get( $f_bug_id, true );

	$t_selected_project = helper_get_current_project();
	if( $tpl_bug->project_id != $t_selected_project ) {
		# in case the current project is not the same project of the bug we are viewing...
		# ... override the current project. This to avoid problems with categories and handlers lists etc.
		$g_project_override = $tpl_bug->project_id;
	}

	$t_allow_view_type = config_get( 'show_view' );

	if ( $tpl_advanced && SIMPLE_ONLY == $t_allow_view_type ) {
		print_header_redirect ( 'bug_view_page.php?bug_id=' . $f_bug_id . '&amp;a=1' );
	}

	if ( !$tpl_advanced && ADVANCED_ONLY == $t_allow_view_type ) {
		print_header_redirect ( 'bug_view_advanced_page.php?bug_id=' . $f_bug_id );
	}

	compress_enable();

	if ( $tpl_show_page_header ) {
		html_page_top( bug_format_summary( $f_bug_id, SUMMARY_CAPTION ) );
		print_recently_visited();
	}

	$t_action_button_position = config_get( 'action_button_position' );
	$t_access_level_needed = config_get( 'view_history_threshold' );

	$t_bugslist = gpc_get_cookie( config_get( 'bug_list_cookie' ), false );

	$tpl_show_product_version = version_should_show_product_version( $tpl_bug->project_id, $tpl_view_id );
	$tpl_show_fixed_in_version = $tpl_show_product_version && ( ( config_get( 'show_fixed_in_version_views' ) & $tpl_view_id ) != 0 ) ;
	$tpl_show_build = $tpl_show_product_version && ( ( config_get( 'show_product_build_views' ) & $tpl_view_id ) != 0 );
	$tpl_show_target_version = $tpl_show_product_version && ( ( config_get( 'show_target_version_views' ) & $tpl_view_id ) != 0 ) && access_has_bug_level( config_get( 'roadmap_view_threshold' ), $f_bug_id );
	$tpl_product_version_string  = '';
	$tpl_target_version_string   = '';
	$tpl_fixed_in_version_string = '';

	if ( $tpl_show_product_version ) {
		$t_version_rows = version_get_all_rows( $tpl_bug->project_id );

		$tpl_product_version_string  = prepare_version_string( $tpl_bug->project_id, version_get_id( $tpl_bug->version, $tpl_bug->project_id ), $t_version_rows );

		if ( $tpl_show_target_version ) {
			$tpl_target_version_string   = prepare_version_string( $tpl_bug->project_id, version_get_id( $tpl_bug->target_version, $tpl_bug->project_id) , $t_version_rows );
		}

		if ( $tpl_show_fixed_in_version ) {
			$tpl_fixed_in_version_string = prepare_version_string( $tpl_bug->project_id, version_get_id( $tpl_bug->fixed_in_version, $tpl_bug->project_id ), $t_version_rows );
		}
	}

	$t_page_name = $tpl_advanced ? 'bug_view_advanced_page.php' : 'bug_view_page.php';

	$tpl_bug_id = $f_bug_id;
	$tpl_form_title = $tpl_advanced ? lang_get( 'viewing_bug_advanced_details_title' ) : lang_get( 'viewing_bug_simple_details_title' );
	$tpl_wiki_link = config_get_global( 'wiki_enable' ) == ON ? 'wiki.php?id=' . $f_bug_id : '';

	if ( $t_allow_view_type == BOTH ) {
		if ( $tpl_advanced ) {
			$tpl_alternate_view_link = 'bug_view_page.php?bug_id=' . $f_bug_id;
			$tpl_alternate_view_label = lang_get( 'view_simple_link' );
		} else {
			$tpl_alternate_view_link = 'bug_view_advanced_page.php?bug_id=' . $f_bug_id;
			$tpl_alternate_view_label = lang_get( 'view_advanced_link' );
		}
	} else {
		$tpl_alternate_view_link = '';
	}

	if ( access_has_bug_level( $t_access_level_needed, $f_bug_id ) ) {
		$tpl_history_link = "{$t_page_name}?bug_id={$f_bug_id}&amp;history=1#history";
	} else {
		$tpl_history_link = '';
	}

	$tpl_show_reminder_link = !current_user_is_anonymous() && !bug_is_readonly( $f_bug_id ) &&
		  access_has_bug_level( config_get( 'bug_reminder_threshold' ), $f_bug_id );
	$tpl_bug_reminder_link = 'bug_reminder_page.php?bug_id=' . $f_bug_id;

	$tpl_print_link = 'print_bug_page.php?bug_id=' . $f_bug_id;

	$tpl_top_buttons_enabled = !$tpl_force_readonly && ( $t_action_button_position == POSITION_TOP || $t_action_button_position == POSITION_BOTH );
	$tpl_bottom_buttons_enabled = !$tpl_force_readonly && ( $t_action_button_position == POSITION_BOTTOM || $t_action_button_position == POSITION_BOTH );

	$tpl_project_name = project_get_name( $tpl_bug->project_id );
	$tpl_formatted_bug_id = bug_format_id( $f_bug_id );

	$tpl_can_view_handler = access_has_bug_level( config_get( 'view_handler_threshold' ), $f_bug_id );
	$tpl_can_attach_tag = !$tpl_force_readonly && access_has_bug_level( config_get( 'tag_attach_threshold' ), $f_bug_id );
	$tpl_can_view_tags = access_has_global_level( config_get( 'tag_view_threshold' ) );

	$tpl_bug_overdue = bug_is_overdue( $f_bug_id );

	$tpl_bug_view_state_enum = get_enum_element( 'project_view_state', $tpl_bug->view_state );

	$tpl_can_view_due_date = access_has_bug_level( config_get( 'due_date_view_threshold' ), $f_bug_id );

	if ( $tpl_can_view_due_date ) {
		if ( !date_is_null( $tpl_bug->due_date ) ) {
			$tpl_bug_due_date = date( config_get( 'normal_date_format' ), $tpl_bug->due_date );
		} else {
			$tpl_bug_due_date = '';
		}
	}

	$tpl_show_additional_information = !is_blank( $tpl_bug->additional_information );
	$tpl_show_steps_to_reproduce = !is_blank( $tpl_bug->steps_to_reproduce );
	$tpl_show_monitor_box = !$tpl_force_readonly;
	$tpl_show_relationships_box = !$tpl_force_readonly;
	$tpl_show_upload_form = !$tpl_force_readonly && !bug_is_readonly( $f_bug_id );
	$tpl_show_history = $f_history;
	$tpl_show_profiles = $tpl_advanced && config_get( 'enable_profiles' );
	$tpl_show_projection = ( config_get( 'show_projection_views' ) & $tpl_view_id ) != 0;
	$tpl_show_eta = ( config_get( 'show_eta_views' ) & $tpl_view_id ) != 0;
	$tpl_show_attachments = ( $tpl_bug->reporter_id == auth_get_current_user_id() ) || access_has_bug_level( config_get( 'view_attachments_threshold' ), $f_bug_id );
	$tpl_show_priority = true;
	$tpl_show_severity = $tpl_advanced;
	$tpl_show_reproducibility = $tpl_advanced;

	$tpl_bug_summary = bug_format_summary( $f_bug_id, SUMMARY_FIELD );
	$tpl_links = event_signal( 'EVENT_MENU_ISSUE', $f_bug_id );

	#
	# Start of Template
	#

	echo '<br />';
	echo '<table class="width100" cellspacing="1">';
	echo '<tr>';

	# Form Title
	echo '<td class="form-title" colspan="', $t_bugslist ? '3' : '4', '">';

	echo $tpl_form_title;

	echo '<span class="small">';

	# Jump to Bugnotes
	print_bracket_link( "#bugnotes", lang_get( 'jump_to_bugnotes' ) );

	# Send Bug Reminder
	if ( $tpl_show_reminder_link ) {
		print_bracket_link( $tpl_bug_reminder_link, lang_get( 'bug_reminder' ) );
	}

	if ( !is_blank( $tpl_wiki_link ) ) {
		print_bracket_link( $tpl_wiki_link, lang_get( 'wiki' ) );
	}

	foreach ( $tpl_links as $t_plugin => $t_hooks ) {
		foreach( $t_hooks as $t_hook ) {
			foreach( $t_hook as $t_label => $t_href ) {
				echo '&nbsp;';
				print_bracket_link( $t_href, $t_label );
			}
		}
	}

	echo '</span></td>';

	# prev/next links
	if ( $t_bugslist ) {
		echo '<td class="center"><span class="small">';

		$t_bugslist = explode( ',', $t_bugslist );
		$t_index = array_search( $f_bug_id, $t_bugslist );
		if ( false !== $t_index ) {
			if ( isset( $t_bugslist[$t_index-1] ) ) {
				print_bracket_link( 'bug_view_advanced_page.php?bug_id='.$t_bugslist[$t_index-1], '&lt;&lt;' );
			}

			if ( isset( $t_bugslist[$t_index+1] ) ) {
				print_bracket_link( 'bug_view_advanced_page.php?bug_id='.$t_bugslist[$t_index+1], '&gt;&gt;' );
			}
		}
	}

	echo '</span></td>';

	# Links
	echo '<td class="right" colspan="2">';

	# Simple View (if enabled)
	if ( !is_blank( $tpl_alternate_view_link ) ) {
		echo '<span class="small">';
		print_bracket_link( $tpl_alternate_view_link, $tpl_alternate_view_label );
		echo '</span>';
	}

	if ( !is_blank( $tpl_history_link ) ) {
		# History
		echo '<span class="small">';
		print_bracket_link( $tpl_history_link, lang_get( 'bug_history' ) );
		echo '</span>';
	}

	# Print Bug
	echo '<span class="small">';
	print_bracket_link( $tpl_print_link, lang_get( 'print' ) );

	echo '</td>';
	echo '</tr>';

	if ( $tpl_top_buttons_enabled ) {
		echo '<tr align="center">';
		echo '<td align="center" colspan="6">';
		html_buttons_view_bug_page( $tpl_bug_id );
		echo '</td>';
		echo '</tr>';
	}

	# Labels
	echo '<tr>';
	echo '<td class="category" width="15%">', lang_get( 'id' ), '</td>';
	echo '<td class="category" width="20%">', lang_get( 'email_project' ), '</td>';
	echo '<td class="category" width="15%">', lang_get( 'category' ), '</td>';
	echo '<td class="category" width="15%">', lang_get( 'view_status' ), '</td>';
	echo '<td class="category" width="15%">', lang_get( 'date_submitted' ), '</td>';
	echo '<td class="category" width="20%">', lang_get( 'last_update' ),'</td>';
	echo '</tr>';

	echo '<tr ', helper_alternate_class(), '>';

	# Bug ID
	echo '<td>', string_display_line( $tpl_formatted_bug_id ), '</td>';

	# Project
	echo '<td>', string_display_line( $tpl_project_name ), '</td>';

	# Category
	echo '<td>', string_display_line( category_full_name( $tpl_bug->category_id ) ), '</td>';

	# View Status
	echo '<td>', $tpl_bug_view_state_enum, '</td>';

	# Date Submitted
	echo '<td>', date( config_get( 'normal_date_format' ), $tpl_bug->date_submitted ), '</td>';

	# Date Updated
	echo '<td>', date( config_get( 'normal_date_format' ), $tpl_bug->last_updated ), '</td>';

	echo '<td></td>';

	echo '</tr>';

	# spacer
	echo '<tr class="spacer"><td colspan="6"></td></tr>';

	#
	# Reporter, Due Date
	#

	echo '<tr ', helper_alternate_class(), '>';

	$t_spacer = 2;

	# Reporter
	echo '<td class="category">', lang_get( 'reporter' ), '</td>';
	echo '<td>';
	print_user_with_subject( $tpl_bug->reporter_id, $tpl_bug_id );
	echo '</td>';

	# Due Date
	if ( $tpl_can_view_due_date ) {
		echo '<td class="category">', lang_get( 'due_date' ), '</td>';

		if ( $tpl_bug_overdue ) {
			echo '<td class="overdue">', $tpl_bug_due_date, '</td>';
		} else {
			echo '<td>', $tpl_bug_due_date, '</td>';
		}
	} else {
		$t_spacer += 2;
	}

	echo '<td colspan="', $t_spacer, '">&nbsp;</td>';

	echo '</tr>';

	#
	# Handler
	#

	if ( $tpl_can_view_handler ) {
		# Handler
		echo '<tr ', helper_alternate_class(), '>';
		echo '<td class="category">', lang_get( 'assigned_to' ), '</td>';
		echo '<td colspan="5">';
		print_user_with_subject( $tpl_bug->handler_id, $tpl_bug_id );
		echo '</td>';
		echo '</tr>';
	}

	#
	# Priority, Severity, Reproducibility
	#

	echo '<tr ', helper_alternate_class(), '>';

	$t_spacer = 0;

	# Priority
	if ( $tpl_show_priority ) {
		echo '<td class="category">', lang_get( 'priority' ), '</td>';
		echo '<td>', get_enum_element( 'priority', $tpl_bug->priority ), '</td>';
	} else {
		$t_spacer += 2;
	}

	# Severity
	if ( $tpl_show_severity ) {
		echo '<td class="category">', lang_get( 'severity' ), '</td>';
		echo '<td>', get_enum_element( 'severity', $tpl_bug->severity ), '</td>';
	} else {
		$t_spacer += 2;
	}

	# Reproducibility
	if ( $tpl_show_reproducibility ) {
		echo '<td class="category">', lang_get( 'reproducibility' ), '</td>';
		echo '<td>', get_enum_element( 'reproducibility', $tpl_bug->reproducibility ), '</td>';
	} else {
		$t_spacer += 2;
	}

	# spacer
	if ( $t_spacer > 0 ) {
		echo '<td colspan="', $t_spacer, '">&nbsp;</td>';
	}

	echo '</tr>';

	#
	# Status, Resolution
	#

	echo '<tr ', helper_alternate_class(), '>';

	# Status
	echo '<td class="category">', lang_get( 'status' ), '</td>';
	echo '<td bgcolor="', get_status_color( $tpl_bug->status ), '">', get_enum_element( 'status', $tpl_bug->status ), '</td>';

	# Resolution
	echo '<td class="category">', lang_get( 'resolution' ), '</td>';
	echo '<td>', get_enum_element( 'resolution', $tpl_bug->resolution ), '</td>';

	echo '<td colspan="2"></td>';
	echo '</tr>';

	#
	# Projection, ETA
	#

	if ( $tpl_show_projection || $tpl_show_eta ) {
		echo '<tr ', helper_alternate_class(), '>';

		$t_spacer = 2;

		if ( $tpl_show_projection ) {
			# Projection
			echo '<td class="category">', lang_get( 'projection' ), '</td>';
			echo '<td>', get_enum_element( 'projection', $tpl_bug->projection ), '</td>';
		} else {
			$t_spacer += 2;
		}

		# ETA
		if ( $tpl_show_eta ) {
			echo '<td class="category">', lang_get( 'eta' ), '</td>';
			echo '<td>', get_enum_element( 'eta', $tpl_bug->eta ), '</td>';
		} else {
			$t_spacer += 2;
		}

		echo '<td colspan="', $t_spacer, '">&nbsp;</td>';
		echo '</tr>';
	}

	#
	# Platform, OS, OS Version
	#

	if ( $tpl_show_profiles ) {
		echo '<tr ', helper_alternate_class(), '>';
		# Platform
		echo '<td class="category">', lang_get( 'platform' ), '</td>';
		echo '<td>', string_display_line( $tpl_bug->platform ), '</td>';

		# Operating System
		echo '<td class="category">', lang_get( 'os' ), '</td>';
		echo '<td>', string_display_line( $tpl_bug->os ), '</td>';

		# OS Version
		echo '<td class="category">', lang_get( 'os_version' ), '</td>';
		echo '<td>', string_display_line( $tpl_bug->os_build ), '</td>';

		echo '</tr>';
	}

	#
	# Product Version, Product Build
	#

	if ( $tpl_show_product_version || $tpl_show_build ) {
		$t_spacer = 2;

		echo '<tr ', helper_alternate_class(), '>';

		# Product Version
		if ( $tpl_show_product_version ) {
			echo '<td class="category">', lang_get( 'product_version' ), '</td>';
			echo '<td>', string_display_line( $tpl_product_version_string ), '</td>';
		} else {
			$t_spacer += 2;
		}

		# Product Build
		if ( $tpl_show_build ) {
			echo '<td class="category">', lang_get( 'product_build' ), '</td>';
			echo '<td>', string_display_line( $tpl_bug->build ), '</td>';
		} else {
			$t_spacer += 2;
		}

		# spacer
		echo '<td colspan="', $t_spacer, '">&nbsp;</td>';

		echo '</tr>';
	}

	#
	# Target Version, Fixed In Version
	#

	if ( $tpl_show_target_version || $tpl_show_fixed_in_version ) {
		$t_spacer = 2;

		echo '<tr ', helper_alternate_class(), '>';

		# target version
		if ( $tpl_show_target_version ) {
			# Target Version
			echo '<td class="category">', lang_get( 'target_version' ), '</td>';
			echo '<td>', string_display_line( $tpl_target_version_string ), '</td>';
		} else {
			$t_spacer += 2;
		}

		# fixed in version
		if ( $tpl_show_fixed_in_version ) {
			echo '<td class="category">', lang_get( 'fixed_in_version' ), '</td>';
			echo '<td>', string_display_line( $tpl_fixed_in_version_string ), '</td>';
		} else {
			$t_spacer += 2;
		}

		# spacer
		echo '<td colspan="', $t_spacer, '">&nbsp;</td>';

		echo '</tr>';
	}

	#
	# Bug Details Event Signal
	#

	event_signal( 'EVENT_VIEW_BUG_DETAILS', array( $tpl_bug_id, true ) );

	# spacer
	echo '<tr class="spacer"><td colspan="6"></td></tr>';

	#
	# Bug Details (screen wide fields)
	#

	# Summary
	echo '<tr ', helper_alternate_class(), '>';
	echo '<td class="category">', lang_get( 'summary' ), '</td>';
	echo '<td colspan="5">', $tpl_bug_summary, '</td>';
	echo '</tr>';

	# Description
	echo '<tr ', helper_alternate_class(), '>';
	echo '<td class="category">', lang_get( 'description' ), '</td>';
	echo '<td colspan="5">', string_display_links( $tpl_bug->description ), '</td>';
	echo '</tr>';

	# Steps to Reproduce
	if ( $tpl_show_steps_to_reproduce ) {
		echo '<tr ', helper_alternate_class(), '>';
		echo '<td class="category">', lang_get( 'steps_to_reproduce' ), '</td>';
		echo '<td colspan="5">', string_display_links( $tpl_bug->steps_to_reproduce ), '</td>';
		echo '</tr>';
	}

	# Additional Information
	if ( $tpl_show_additional_information ) {
		echo '<tr ', helper_alternate_class(), '>';
		echo '<td class="category">', lang_get( 'additional_information' ), '</td>';
		echo '<td colspan="5">', string_display_links( $tpl_bug->additional_information ), '</td>';
		echo '</tr>';
	}

	# Tagging
	if ( $tpl_can_view_tags ) {
		echo '<tr ', helper_alternate_class(), '>';
		echo '<td class="category">', lang_get( 'tags' ), '</td>';
		echo '<td colspan="5">';
		tag_display_attached( $tpl_bug_id );
		echo '</td></tr>';
	}

	# Attachments Form
	if ( $tpl_can_attach_tag ) {
		echo '<tr ', helper_alternate_class(), '>';
		echo '<td class="category">', lang_get( 'tag_attach_long' ), '</td>';
		echo '<td colspan="5">';
		print_tag_attach_form( $tpl_bug_id );
		echo '</td></tr>';
	}

	# spacer
	echo '<tr class="spacer"><td colspan="6"></td></tr>';

	# Custom Fields
	$t_custom_fields_found = false;
	$t_related_custom_field_ids = custom_field_get_linked_ids( $tpl_bug->project_id );

	foreach( $t_related_custom_field_ids as $t_id ) {
		if ( !custom_field_has_read_access( $t_id, $f_bug_id ) ) {
			continue;
		} # has read access

		$t_custom_fields_found = true;
		$t_def = custom_field_get_definition( $t_id );

		echo '<tr ', helper_alternate_class(), '>';
		echo '<td class="category">', string_display( lang_get_defaulted( $t_def['name'] ) ), '</td>';
		echo '<td colspan="5">';
		print_custom_field_value( $t_def, $t_id, $f_bug_id );
		echo '</td></tr>';
	}

	if ( $t_custom_fields_found ) {
		# spacer
		echo '<tr class="spacer"><td colspan="6"></td></tr>';
	} # custom fields found

	# Attachments
	if ( $tpl_show_attachments ) {
		echo '<tr ', helper_alternate_class(), '>';
		echo '<td class="category"><a name="attachments" id="attachments" />', lang_get( 'attached_files' ), '</td>';
		echo '<td colspan="5">';
		print_bug_attachments_list( $tpl_bug_id );
		echo '</td></tr>';
	}

	if ( $tpl_bottom_buttons_enabled ) {
		echo '<tr align="center"><td align="center" colspan="6">';
		html_buttons_view_bug_page( $tpl_bug_id );
		echo '</td></tr>';
	}

	echo '</table>';

	# User list sponsoring the bug
	include( $tpl_mantis_dir . 'bug_sponsorship_list_view_inc.php' );

	# Bug Relationships
	if ( $tpl_show_relationships_box ) {
		relationship_view_box ( $tpl_bug->id );
	}

	# File upload box
	if ( $tpl_show_upload_form ) {
		include( $tpl_mantis_dir . 'bug_file_upload_inc.php' );
	}

	# User list monitoring the bug
	if ( $tpl_show_monitor_box ) {
		include( $tpl_mantis_dir . 'bug_monitor_list_view_inc.php' );
	}

	# Bugnotes and "Add Note" box
	if ( 'ASC' == current_user_get_pref( 'bugnote_order' ) ) {
		include( $tpl_mantis_dir . 'bugnote_view_inc.php' );

		if ( !$tpl_force_readonly ) {
			include( $tpl_mantis_dir . 'bugnote_add_inc.php' );
		}
	} else {
		if ( !$tpl_force_readonly ) {
			include( $tpl_mantis_dir . 'bugnote_add_inc.php' );
		}

		include( $tpl_mantis_dir . 'bugnote_view_inc.php' );
	}

	# Allow plugins to display stuff after notes
	event_signal( 'EVENT_VIEW_BUG_EXTRA', array( $f_bug_id ) );

	# History
	if ( $tpl_show_history ) {
		include( $tpl_mantis_dir . 'history_inc.php' );
	}

	html_page_bottom( $tpl_file );

	last_visited_issue( $tpl_bug_id );
