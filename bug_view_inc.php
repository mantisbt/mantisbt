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
	 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
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
	require_once( 'date_api.php' );
	require_once( 'relationship_api.php' );
	require_once( 'last_visited_api.php' );
	require_once( 'tag_api.php' );

	$f_bug_id = gpc_get_int( 'id' );

	bug_ensure_exists( $f_bug_id );

	$tpl_bug = bug_get( $f_bug_id, true );

	# In case the current project is not the same project of the bug we are
	# viewing, override the current project. This ensures all config_get and other
	# per-project function calls use the project ID of this bug.
	$g_project_override = $tpl_bug->project_id;

	access_ensure_bug_level( VIEWER, $f_bug_id );

	$f_history = gpc_get_bool( 'history', config_get( 'history_default_visible' ) );

	$t_fields = config_get( $tpl_fields_config_option );
	$t_fields = columns_filter_disabled( $t_fields );

	compress_enable();

	if ( $tpl_show_page_header ) {
		html_page_top( bug_format_summary( $f_bug_id, SUMMARY_CAPTION ) );
		print_recently_visited();
	}

	$t_action_button_position = config_get( 'action_button_position' );

	$t_bugslist = gpc_get_cookie( config_get( 'bug_list_cookie' ), false );

	$tpl_show_versions = version_should_show_product_version( $tpl_bug->project_id );
	$tpl_show_product_version = $tpl_show_versions && in_array( 'product_version', $t_fields );
	$tpl_show_fixed_in_version = $tpl_show_versions && in_array( 'fixed_in_version', $t_fields );
	$tpl_show_product_build = $tpl_show_versions && in_array( 'product_build', $t_fields )
		&& ( config_get( 'enable_product_build' ) == ON );
	$tpl_product_build = $tpl_show_product_build ? string_display_line( $tpl_bug->build ) : '';
	$tpl_show_target_version = $tpl_show_versions && in_array( 'target_version', $t_fields )
		&& access_has_bug_level( config_get( 'roadmap_view_threshold' ), $f_bug_id );

	$tpl_product_version_string  = '';
	$tpl_target_version_string   = '';
	$tpl_fixed_in_version_string = '';

	if ( $tpl_show_product_version || $tpl_show_fixed_in_version || $tpl_show_target_version ) {
		$t_version_rows = version_get_all_rows( $tpl_bug->project_id );

		if ( $tpl_show_product_version ) {
			$tpl_product_version_string  = prepare_version_string( $tpl_bug->project_id, version_get_id( $tpl_bug->version, $tpl_bug->project_id ), $t_version_rows );
		}

		if ( $tpl_show_target_version ) {
			$tpl_target_version_string   = prepare_version_string( $tpl_bug->project_id, version_get_id( $tpl_bug->target_version, $tpl_bug->project_id) , $t_version_rows );
		}

		if ( $tpl_show_fixed_in_version ) {
			$tpl_fixed_in_version_string = prepare_version_string( $tpl_bug->project_id, version_get_id( $tpl_bug->fixed_in_version, $tpl_bug->project_id ), $t_version_rows );
		}
	}

	$tpl_product_version_string = string_display_line( $tpl_product_version_string );
	$tpl_target_version_string = string_display_line( $tpl_target_version_string );
	$tpl_fixed_in_version_string = string_display_line( $tpl_fixed_in_version_string );

	$tpl_bug_id = $f_bug_id;
	$tpl_form_title = lang_get( 'bug_view_title' );
	$tpl_wiki_link = config_get_global( 'wiki_enable' ) == ON ? 'wiki.php?id=' . $f_bug_id : '';

	if ( access_has_bug_level( config_get( 'view_history_threshold' ), $f_bug_id ) ) {
		$tpl_history_link = "view.php?id=$f_bug_id&history=1#history";
	} else {
		$tpl_history_link = '';
	}

	$tpl_show_reminder_link = !current_user_is_anonymous() && !bug_is_readonly( $f_bug_id ) &&
		  access_has_bug_level( config_get( 'bug_reminder_threshold' ), $f_bug_id );
	$tpl_bug_reminder_link = 'bug_reminder_page.php?bug_id=' . $f_bug_id;

	$tpl_print_link = 'print_bug_page.php?bug_id=' . $f_bug_id;

	$tpl_top_buttons_enabled = !$tpl_force_readonly && ( $t_action_button_position == POSITION_TOP || $t_action_button_position == POSITION_BOTH );
	$tpl_bottom_buttons_enabled = !$tpl_force_readonly && ( $t_action_button_position == POSITION_BOTTOM || $t_action_button_position == POSITION_BOTH );

	$tpl_show_project = in_array( 'project', $t_fields );
	$tpl_project_name = $tpl_show_project ? string_display_line( project_get_name( $tpl_bug->project_id ) ): '';
	$tpl_show_id = in_array( 'id', $t_fields );
	$tpl_formatted_bug_id = $tpl_show_id ? string_display_line( bug_format_id( $f_bug_id ) ) : '';

	$tpl_show_date_submitted = in_array( 'date_submitted', $t_fields );
	$tpl_date_submitted = $tpl_show_date_submitted ? date( config_get( 'normal_date_format' ), $tpl_bug->date_submitted ) : '';

	$tpl_show_last_updated = in_array( 'last_updated', $t_fields );
	$tpl_last_updated = $tpl_show_last_updated ? date( config_get( 'normal_date_format' ), $tpl_bug->last_updated ) : '';

	$tpl_show_tags = in_array( 'tags', $t_fields ) && access_has_global_level( config_get( 'tag_view_threshold' ) );

	$tpl_bug_overdue = bug_is_overdue( $f_bug_id );

	$tpl_show_view_state = in_array( 'view_state', $t_fields );
	$tpl_bug_view_state_enum = $tpl_show_view_state ? string_display_line( get_enum_element( 'view_state', $tpl_bug->view_state ) ) : '';

	$tpl_show_due_date = in_array( 'due_date', $t_fields ) && access_has_bug_level( config_get( 'due_date_view_threshold' ), $f_bug_id );

	if ( $tpl_show_due_date ) {
		if ( !date_is_null( $tpl_bug->due_date ) ) {
			$tpl_bug_due_date = date( config_get( 'normal_date_format' ), $tpl_bug->due_date );
		} else {
			$tpl_bug_due_date = '';
		}
	}

	$tpl_show_reporter = in_array( 'reporter', $t_fields );
	$tpl_show_handler = in_array( 'handler', $t_fields ) && access_has_bug_level( config_get( 'view_handler_threshold' ), $f_bug_id );
	$tpl_show_additional_information = !is_blank( $tpl_bug->additional_information ) && in_array( 'additional_info', $t_fields );
	$tpl_show_steps_to_reproduce = !is_blank( $tpl_bug->steps_to_reproduce ) && in_array( 'steps_to_reproduce', $t_fields );
	$tpl_show_monitor_box = !$tpl_force_readonly;
	$tpl_show_relationships_box = !$tpl_force_readonly;
	$tpl_show_upload_form = !$tpl_force_readonly && !bug_is_readonly( $f_bug_id );
	$tpl_show_history = $f_history;
	$tpl_show_profiles = config_get( 'enable_profiles' );
	$tpl_show_platform = $tpl_show_profiles && in_array( 'platform', $t_fields );
	$tpl_platform = $tpl_show_platform ? string_display_line( $tpl_bug->platform ) : '';
	$tpl_show_os = $tpl_show_profiles && in_array( 'os', $t_fields );
	$tpl_os = $tpl_show_os ? string_display_line( $tpl_bug->os ) : '';
	$tpl_show_os_version = $tpl_show_profiles && in_array( 'os_version', $t_fields );
	$tpl_os_version = $tpl_show_os_version ? string_display_line( $tpl_bug->os_build ) : '';
	$tpl_show_projection = in_array( 'projection', $t_fields );
	$tpl_projection = $tpl_show_projection ? string_display_line( get_enum_element( 'projection', $tpl_bug->projection ) ) : '';
	$tpl_show_eta = in_array( 'eta', $t_fields );
	$tpl_eta = $tpl_show_eta ? string_display_line( get_enum_element( 'eta', $tpl_bug->eta ) ) : '';
	$tpl_show_attachments = in_array( 'attachments', $t_fields );
	$tpl_can_attach_tag = $tpl_show_tags && !$tpl_force_readonly && access_has_bug_level( config_get( 'tag_attach_threshold' ), $f_bug_id );
	$tpl_show_category = in_array( 'category_id', $t_fields );
	$tpl_category = $tpl_show_category ? string_display_line( category_full_name( $tpl_bug->category_id ) ) : '';
	$tpl_show_priority = in_array( 'priority', $t_fields );
	$tpl_priority = $tpl_show_priority ? string_display_line( get_enum_element( 'priority', $tpl_bug->priority ) ) : '';
	$tpl_show_severity = in_array( 'severity', $t_fields );
	$tpl_severity = $tpl_show_severity ? string_display_line( get_enum_element( 'severity', $tpl_bug->severity ) ) : '';
	$tpl_show_reproducibility = in_array( 'reproducibility', $t_fields );
	$tpl_reproducibility = $tpl_show_reproducibility ? string_display_line( get_enum_element( 'reproducibility', $tpl_bug->reproducibility ) ): '';
	$tpl_show_status = in_array( 'status', $t_fields );
	$tpl_status = $tpl_show_status ? string_display_line( get_enum_element( 'status', $tpl_bug->status ) ) : '';
	$tpl_show_resolution = in_array( 'resolution', $t_fields );
	$tpl_resolution = $tpl_show_resolution ? string_display_line( get_enum_element( 'resolution', $tpl_bug->resolution ) ) : '';
	$tpl_show_summary = in_array( 'summary', $t_fields );
	$tpl_show_description = in_array( 'description', $t_fields );

	$tpl_summary = $tpl_show_summary ? bug_format_summary( $f_bug_id, SUMMARY_FIELD ) : '';
	$tpl_description = $tpl_show_description ? string_display_links( $tpl_bug->description ) : '';
	$tpl_steps_to_reproduce = $tpl_show_steps_to_reproduce ? string_display_links( $tpl_bug->steps_to_reproduce ) : '';
	$tpl_additional_information = $tpl_show_additional_information ? string_display_links( $tpl_bug->additional_information ) : '';

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

	echo '&#160;<span class="small">';

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
			if ( is_array( $t_hook ) ) {
				foreach( $t_hook as $t_label => $t_href ) {
					if ( is_numeric( $t_label ) ) {
						print_bracket_link_prepared( $t_href );
					} else {
						print_bracket_link( $t_href, $t_label );
					}
				}
			} else {
				print_bracket_link_prepared( $t_hook );
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
				print_bracket_link( 'view.php?id='.$t_bugslist[$t_index-1], '&lt;&lt;' );
			}

			if ( isset( $t_bugslist[$t_index+1] ) ) {
				print_bracket_link( 'view.php?id='.$t_bugslist[$t_index+1], '&gt;&gt;' );
			}
		}
		echo '</span></td>';
	}


	# Links
	echo '<td class="right" colspan="2">';

	if ( !is_blank( $tpl_history_link ) ) {
		# History
		echo '<span class="small">';
		print_bracket_link( $tpl_history_link, lang_get( 'bug_history' ) );
		echo '</span>';
	}

	# Print Bug
	echo '<span class="small">';
	print_bracket_link( $tpl_print_link, lang_get( 'print' ) );
	echo '</span>';
	echo '</td>';
	echo '</tr>';

	if ( $tpl_top_buttons_enabled ) {
		echo '<tr align="center">';
		echo '<td align="center" colspan="6">';
		html_buttons_view_bug_page( $tpl_bug_id );
		echo '</td>';
		echo '</tr>';
	}

	if ( $tpl_show_id || $tpl_show_project || $tpl_show_category || $tpl_show_view_state || $tpl_show_date_submitted || $tpl_show_last_updated ) {
		# Labels
		echo '<tr>';
		echo '<td class="category" width="15%">', $tpl_show_id ? lang_get( 'id' ) : '', '</td>';
		echo '<td class="category" width="20%">', $tpl_show_project ? lang_get( 'email_project' ) : '', '</td>';
		echo '<td class="category" width="15%">', $tpl_show_category ? lang_get( 'category' ) : '', '</td>';
		echo '<td class="category" width="15%">', $tpl_show_view_state ? lang_get( 'view_status' ) : '', '</td>';
		echo '<td class="category" width="15%">', $tpl_show_date_submitted ? lang_get( 'date_submitted' ) : '', '</td>';
		echo '<td class="category" width="20%">', $tpl_show_last_updated ? lang_get( 'last_update' ) : '','</td>';
		echo '</tr>';

		echo '<tr ', helper_alternate_class(), '>';

		# Bug ID
		echo '<td>', $tpl_formatted_bug_id, '</td>';

		# Project
		echo '<td>', $tpl_project_name, '</td>';

		# Category
		echo '<td>', $tpl_category, '</td>';

		# View Status
		echo '<td>', $tpl_bug_view_state_enum, '</td>';

		# Date Submitted
		echo '<td>', $tpl_date_submitted, '</td>';

		# Date Updated
		echo '<td>', $tpl_last_updated, '</td>';

		echo '</tr>';

		# spacer
		echo '<tr class="spacer"><td colspan="6"></td></tr>';
	}

	#
	# Reporter
	#

	if ( $tpl_show_reporter ) {
		echo '<tr ', helper_alternate_class(), '>';

		$t_spacer = 4;

		# Reporter
		if ( $tpl_show_reporter ) {
			echo '<td class="category">', lang_get( 'reporter' ), '</td>';
			echo '<td>';
			print_user_with_subject( $tpl_bug->reporter_id, $tpl_bug_id );
			echo '</td>';
		} else {
			$t_spacer += 2;
		}

		echo '<td colspan="', $t_spacer, '">&#160;</td>';

		echo '</tr>';
	}

	#
	# Handler, Due Date
	#

	if ( $tpl_show_handler || $tpl_show_due_date ) {
		echo '<tr ', helper_alternate_class(), '>';

		$t_spacer = 2;

		# Handler
		if ( $tpl_show_handler ) {
			echo '<td class="category">', lang_get( 'assigned_to' ), '</td>';
			echo '<td>';
			print_user_with_subject( $tpl_bug->handler_id, $tpl_bug_id );
			echo '</td>';
		} else {
			$t_spacer += 2;
		}

		# Due Date
		if ( $tpl_show_due_date ) {
			echo '<td class="category">', lang_get( 'due_date' ), '</td>';

			if ( $tpl_bug_overdue ) {
				echo '<td class="overdue">', $tpl_bug_due_date, '</td>';
			} else {
				echo '<td>', $tpl_bug_due_date, '</td>';
			}
		} else {
			$t_spacer += 2;
		}

		echo '<td colspan="', $t_spacer, '">&#160;</td>';
		echo '</tr>';
	}

	#
	# Priority, Severity, Reproducibility
	#

	if ( $tpl_show_priority || $tpl_show_severity || $tpl_show_reproducibility ) {
		echo '<tr ', helper_alternate_class(), '>';

		$t_spacer = 0;

		# Priority
		if ( $tpl_show_priority ) {
			echo '<td class="category">', lang_get( 'priority' ), '</td>';
			echo '<td>', $tpl_priority, '</td>';
		} else {
			$t_spacer += 2;
		}

		# Severity
		if ( $tpl_show_severity ) {
			echo '<td class="category">', lang_get( 'severity' ), '</td>';
			echo '<td>', $tpl_severity, '</td>';
		} else {
			$t_spacer += 2;
		}

		# Reproducibility
		if ( $tpl_show_reproducibility ) {
			echo '<td class="category">', lang_get( 'reproducibility' ), '</td>';
			echo '<td>', $tpl_reproducibility, '</td>';
		} else {
			$t_spacer += 2;
		}

		# spacer
		if ( $t_spacer > 0 ) {
			echo '<td colspan="', $t_spacer, '">&#160;</td>';
		}

		echo '</tr>';
	}

	#
	# Status, Resolution
	#

	if ( $tpl_show_status || $tpl_show_resolution ) {
		echo '<tr ', helper_alternate_class(), '>';

		$t_spacer = 2;

		# Status
		if ( $tpl_show_status ) {
			echo '<td class="category">', lang_get( 'status' ), '</td>';
			echo '<td bgcolor="', get_status_color( $tpl_bug->status ), '">', $tpl_status, '</td>';
		} else {
			$t_spacer += 2;
		}

		# Resolution
		if ( $tpl_show_resolution ) {
			echo '<td class="category">', lang_get( 'resolution' ), '</td>';
			echo '<td>', $tpl_resolution, '</td>';
		} else {
			$t_spacer += 2;
		}

		# spacer
		if ( $t_spacer > 0 ) {
			echo '<td colspan="', $t_spacer, '">&#160;</td>';
		}

		echo '</tr>';
	}

	#
	# Projection, ETA
	#

	if ( $tpl_show_projection || $tpl_show_eta ) {
		echo '<tr ', helper_alternate_class(), '>';

		$t_spacer = 2;

		if ( $tpl_show_projection ) {
			# Projection
			echo '<td class="category">', lang_get( 'projection' ), '</td>';
			echo '<td>', $tpl_projection, '</td>';
		} else {
			$t_spacer += 2;
		}

		# ETA
		if ( $tpl_show_eta ) {
			echo '<td class="category">', lang_get( 'eta' ), '</td>';
			echo '<td>', $tpl_eta, '</td>';
		} else {
			$t_spacer += 2;
		}

		echo '<td colspan="', $t_spacer, '">&#160;</td>';
		echo '</tr>';
	}

	#
	# Platform, OS, OS Version
	#

	if ( $tpl_show_platform || $tpl_show_os || $tpl_show_os_version ) {
		$t_spacer = 0;

		echo '<tr ', helper_alternate_class(), '>';

		# Platform
		if ( $tpl_show_platform ) {
			echo '<td class="category">', lang_get( 'platform' ), '</td>';
			echo '<td>', $tpl_platform, '</td>';
		} else {
			$t_spacer += 2;
		}

		# Operating System
		if ( $tpl_show_os ) {
			echo '<td class="category">', lang_get( 'os' ), '</td>';
			echo '<td>', $tpl_os, '</td>';
		} else {
			$t_spacer += 2;
		}

		# OS Version
		if ( $tpl_show_os_version ) {
			echo '<td class="category">', lang_get( 'os_version' ), '</td>';
			echo '<td>', $tpl_os_version, '</td>';
		} else {
			$t_spacer += 2;
		}

		if ( $t_spacer > 0 ) {
			echo '<td colspan="', $t_spacer, '">&#160;</td>';
		}

		echo '</tr>';
	}

	#
	# Product Version, Product Build
	#

	if ( $tpl_show_product_version || $tpl_show_product_build ) {
		$t_spacer = 2;

		echo '<tr ', helper_alternate_class(), '>';

		# Product Version
		if ( $tpl_show_product_version ) {
			echo '<td class="category">', lang_get( 'product_version' ), '</td>';
			echo '<td>', $tpl_product_version_string, '</td>';
		} else {
			$t_spacer += 2;
		}

		# Product Build
		if ( $tpl_show_product_build ) {
			echo '<td class="category">', lang_get( 'product_build' ), '</td>';
			echo '<td>', $tpl_product_build, '</td>';
		} else {
			$t_spacer += 2;
		}

		# spacer
		echo '<td colspan="', $t_spacer, '">&#160;</td>';

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
			echo '<td>', $tpl_target_version_string, '</td>';
		} else {
			$t_spacer += 2;
		}

		# fixed in version
		if ( $tpl_show_fixed_in_version ) {
			echo '<td class="category">', lang_get( 'fixed_in_version' ), '</td>';
			echo '<td>', $tpl_fixed_in_version_string, '</td>';
		} else {
			$t_spacer += 2;
		}

		# spacer
		echo '<td colspan="', $t_spacer, '">&#160;</td>';

		echo '</tr>';
	}

	#
	# Bug Details Event Signal
	#

	event_signal( 'EVENT_VIEW_BUG_DETAILS', array( $tpl_bug_id ) );

	# spacer
	echo '<tr class="spacer"><td colspan="6"></td></tr>';

	#
	# Bug Details (screen wide fields)
	#

	# Summary
	if ( $tpl_show_summary ) {
		echo '<tr ', helper_alternate_class(), '>';
		echo '<td class="category">', lang_get( 'summary' ), '</td>';
		echo '<td colspan="5">', $tpl_summary, '</td>';
		echo '</tr>';
	}

	# Description
	if ( $tpl_show_description ) {
		echo '<tr ', helper_alternate_class(), '>';
		echo '<td class="category">', lang_get( 'description' ), '</td>';
		echo '<td colspan="5">', $tpl_description, '</td>';
		echo '</tr>';
	}

	# Steps to Reproduce
	if ( $tpl_show_steps_to_reproduce ) {
		echo '<tr ', helper_alternate_class(), '>';
		echo '<td class="category">', lang_get( 'steps_to_reproduce' ), '</td>';
		echo '<td colspan="5">', $tpl_steps_to_reproduce, '</td>';
		echo '</tr>';
	}

	# Additional Information
	if ( $tpl_show_additional_information ) {
		echo '<tr ', helper_alternate_class(), '>';
		echo '<td class="category">', lang_get( 'additional_information' ), '</td>';
		echo '<td colspan="5">', $tpl_additional_information, '</td>';
		echo '</tr>';
	}

	# Tagging
	if ( $tpl_show_tags ) {
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

	# Time tracking statistics
	if ( config_get( 'time_tracking_enabled' ) &&
		access_has_bug_level( config_get( 'time_tracking_view_threshold' ), $f_bug_id ) ) {
		include( $tpl_mantis_dir . 'bugnote_stats_inc.php' );
	}

	# History
	if ( $tpl_show_history ) {
		include( $tpl_mantis_dir . 'history_inc.php' );
	}

	html_page_bottom();

	last_visited_issue( $tpl_bug_id );
