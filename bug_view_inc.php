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
 * This include file prints out the bug information
 * $f_bug_id MUST be specified before the file is included
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses category_api.php
 * @uses columns_api.php
 * @uses compress_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses custom_field_api.php
 * @uses date_api.php
 * @uses event_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses last_visited_api.php
 * @uses prepare_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses string_api.php
 * @uses tag_api.php
 * @uses utility_api.php
 * @uses version_api.php
 */

if( !defined( 'BUG_VIEW_INC_ALLOW' ) ) {
	return;
}

require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'category_api.php' );
require_api( 'columns_api.php' );
require_api( 'compress_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'custom_field_api.php' );
require_api( 'date_api.php' );
require_api( 'event_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'last_visited_api.php' );
require_api( 'prepare_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'string_api.php' );
require_api( 'tag_api.php' );
require_api( 'utility_api.php' );
require_api( 'version_api.php' );

require_css( 'status_config.php' );

$f_bug_id = gpc_get_int( 'id' );

bug_ensure_exists( $f_bug_id );

$t_bug = bug_get( $f_bug_id, true );

# In case the current project is not the same project of the bug we are
# viewing, override the current project. This ensures all config_get and other
# per-project function calls use the project ID of this bug.
$g_project_override = $t_bug->project_id;

access_ensure_bug_level( config_get( 'view_bug_threshold' ), $f_bug_id );

$f_history = gpc_get_bool( 'history', config_get( 'history_default_visible' ) );

$t_fields = config_get( $t_fields_config_option );
$t_fields = columns_filter_disabled( $t_fields );

compress_enable();

if( $t_show_page_header ) {
	html_page_top( bug_format_summary( $f_bug_id, SUMMARY_CAPTION ) );
	print_recently_visited();
}

$t_action_button_position = config_get( 'action_button_position' );

$t_bugslist = gpc_get_cookie( config_get( 'bug_list_cookie' ), false );

$t_show_versions = version_should_show_product_version( $t_bug->project_id );
$t_show_product_version = $t_show_versions && in_array( 'product_version', $t_fields );
$t_show_fixed_in_version = $t_show_versions && in_array( 'fixed_in_version', $t_fields );
$t_show_product_build = $t_show_versions && in_array( 'product_build', $t_fields )
	&& ( config_get( 'enable_product_build' ) == ON );
$t_product_build = $t_show_product_build ? string_display_line( $t_bug->build ) : '';
$t_show_target_version = $t_show_versions && in_array( 'target_version', $t_fields )
	&& access_has_bug_level( config_get( 'roadmap_view_threshold' ), $f_bug_id );

$t_product_version_string  = '';
$t_target_version_string   = '';
$t_fixed_in_version_string = '';

if( $t_show_product_version || $t_show_fixed_in_version || $t_show_target_version ) {
	$t_version_rows = version_get_all_rows( $t_bug->project_id );

	if( $t_show_product_version ) {
		$t_product_version_string  = prepare_version_string( $t_bug->project_id, version_get_id( $t_bug->version, $t_bug->project_id ) );
	}

	if( $t_show_target_version ) {
		$t_target_version_string   = prepare_version_string( $t_bug->project_id, version_get_id( $t_bug->target_version, $t_bug->project_id ) );
	}

	if( $t_show_fixed_in_version ) {
		$t_fixed_in_version_string = prepare_version_string( $t_bug->project_id, version_get_id( $t_bug->fixed_in_version, $t_bug->project_id ) );
	}
}

$t_product_version_string = string_display_line( $t_product_version_string );
$t_target_version_string = string_display_line( $t_target_version_string );
$t_fixed_in_version_string = string_display_line( $t_fixed_in_version_string );

$t_bug_id = $f_bug_id;
$t_form_title = lang_get( 'bug_view_title' );
$t_wiki_link = config_get_global( 'wiki_enable' ) == ON ? 'wiki.php?id=' . $f_bug_id : '';

if( access_has_bug_level( config_get( 'view_history_threshold' ), $f_bug_id ) ) {
	$t_history_link = 'view.php?id=' . $f_bug_id . '&history=1#history';
} else {
	$t_history_link = '';
}

$t_show_reminder_link = !current_user_is_anonymous() && !bug_is_readonly( $f_bug_id ) &&
	  access_has_bug_level( config_get( 'bug_reminder_threshold' ), $f_bug_id );
$t_bug_reminder_link = 'bug_reminder_page.php?bug_id=' . $f_bug_id;

$t_print_link = 'print_bug_page.php?bug_id=' . $f_bug_id;

$t_top_buttons_enabled = !$t_force_readonly && ( $t_action_button_position == POSITION_TOP || $t_action_button_position == POSITION_BOTH );
$t_bottom_buttons_enabled = !$t_force_readonly && ( $t_action_button_position == POSITION_BOTTOM || $t_action_button_position == POSITION_BOTH );

$t_show_project = in_array( 'project', $t_fields );
$t_project_name = $t_show_project ? string_display_line( project_get_name( $t_bug->project_id ) ): '';
$t_show_id = in_array( 'id', $t_fields );
$t_formatted_bug_id = $t_show_id ? string_display_line( bug_format_id( $f_bug_id ) ) : '';

$t_show_date_submitted = in_array( 'date_submitted', $t_fields );
$t_date_submitted = $t_show_date_submitted ? date( config_get( 'normal_date_format' ), $t_bug->date_submitted ) : '';

$t_show_last_updated = in_array( 'last_updated', $t_fields );
$t_last_updated = $t_show_last_updated ? date( config_get( 'normal_date_format' ), $t_bug->last_updated ) : '';

$t_show_tags = in_array( 'tags', $t_fields ) && access_has_global_level( config_get( 'tag_view_threshold' ) );

$t_bug_overdue = bug_is_overdue( $f_bug_id );

$t_show_view_state = in_array( 'view_state', $t_fields );
$t_bug_view_state_enum = $t_show_view_state ? string_display_line( get_enum_element( 'view_state', $t_bug->view_state ) ) : '';

$t_show_due_date = in_array( 'due_date', $t_fields ) && access_has_bug_level( config_get( 'due_date_view_threshold' ), $f_bug_id );

if( $t_show_due_date ) {
	if( !date_is_null( $t_bug->due_date ) ) {
		$t_bug_due_date = date( config_get( 'normal_date_format' ), $t_bug->due_date );
	} else {
		$t_bug_due_date = '';
	}
}

$t_show_reporter = in_array( 'reporter', $t_fields );
$t_show_handler = in_array( 'handler', $t_fields ) && access_has_bug_level( config_get( 'view_handler_threshold' ), $f_bug_id );
$t_show_additional_information = !is_blank( $t_bug->additional_information ) && in_array( 'additional_info', $t_fields );
$t_show_steps_to_reproduce = !is_blank( $t_bug->steps_to_reproduce ) && in_array( 'steps_to_reproduce', $t_fields );
$t_show_monitor_box = !$t_force_readonly;
$t_show_relationships_box = !$t_force_readonly;
$t_show_sponsorships_box = config_get( 'enable_sponsorship' ) && access_has_bug_level( config_get( 'view_sponsorship_total_threshold' ), $f_bug_id );
$t_show_upload_form = !$t_force_readonly && !bug_is_readonly( $f_bug_id );
$t_show_history = $f_history;
$t_show_profiles = config_get( 'enable_profiles' );
$t_show_platform = $t_show_profiles && in_array( 'platform', $t_fields );
$t_platform = $t_show_platform ? string_display_line( $t_bug->platform ) : '';
$t_show_os = $t_show_profiles && in_array( 'os', $t_fields );
$t_os = $t_show_os ? string_display_line( $t_bug->os ) : '';
$t_show_os_version = $t_show_profiles && in_array( 'os_version', $t_fields );
$t_os_version = $t_show_os_version ? string_display_line( $t_bug->os_build ) : '';
$t_show_projection = in_array( 'projection', $t_fields );
$t_projection = $t_show_projection ? string_display_line( get_enum_element( 'projection', $t_bug->projection ) ) : '';
$t_show_eta = in_array( 'eta', $t_fields );
$t_eta = $t_show_eta ? string_display_line( get_enum_element( 'eta', $t_bug->eta ) ) : '';
$t_show_attachments = in_array( 'attachments', $t_fields );
$t_can_attach_tag = $t_show_tags && !$t_force_readonly && access_has_bug_level( config_get( 'tag_attach_threshold' ), $f_bug_id );
$t_show_category = in_array( 'category_id', $t_fields );
$t_category = $t_show_category ? string_display_line( category_full_name( $t_bug->category_id ) ) : '';
$t_show_priority = in_array( 'priority', $t_fields );
$t_priority = $t_show_priority ? string_display_line( get_enum_element( 'priority', $t_bug->priority ) ) : '';
$t_show_severity = in_array( 'severity', $t_fields );
$t_severity = $t_show_severity ? string_display_line( get_enum_element( 'severity', $t_bug->severity ) ) : '';
$t_show_reproducibility = in_array( 'reproducibility', $t_fields );
$t_reproducibility = $t_show_reproducibility ? string_display_line( get_enum_element( 'reproducibility', $t_bug->reproducibility ) ): '';
$t_show_status = in_array( 'status', $t_fields );
$t_status = $t_show_status ? string_display_line( get_enum_element( 'status', $t_bug->status ) ) : '';
$t_show_resolution = in_array( 'resolution', $t_fields );
$t_resolution = $t_show_resolution ? string_display_line( get_enum_element( 'resolution', $t_bug->resolution ) ) : '';
$t_show_summary = in_array( 'summary', $t_fields );
$t_show_description = in_array( 'description', $t_fields );

$t_summary = $t_show_summary ? bug_format_summary( $f_bug_id, SUMMARY_FIELD ) : '';
$t_description = $t_show_description ? string_display_links( $t_bug->description ) : '';
$t_steps_to_reproduce = $t_show_steps_to_reproduce ? string_display_links( $t_bug->steps_to_reproduce ) : '';
$t_additional_information = $t_show_additional_information ? string_display_links( $t_bug->additional_information ) : '';

$t_links = event_signal( 'EVENT_MENU_ISSUE', $f_bug_id );

#
# Start of Template
#

echo '<br />';
echo '<div id="view-issue-details" class="table-container">';
echo '<table>';
echo '<thead><tr class="bug-nav">';

# Form Title
echo '<td class="form-title" colspan="', $t_bugslist ? '3' : '4', '">';

echo $t_form_title;

echo '&#160;<span class="small">';

# Jump to Bugnotes
print_bracket_link( '#bugnotes', lang_get( 'jump_to_bugnotes' ), false, 'jump-to-bugnotes' );

# Send Bug Reminder
if( $t_show_reminder_link ) {
	print_bracket_link( $t_bug_reminder_link, lang_get( 'bug_reminder' ), false, 'bug-reminder' );
}

if( !is_blank( $t_wiki_link ) ) {
	print_bracket_link( $t_wiki_link, lang_get( 'wiki' ), false, 'wiki' );
}

foreach ( $t_links as $t_plugin => $t_hooks ) {
	foreach( $t_hooks as $t_hook ) {
		if( is_array( $t_hook ) ) {
			foreach( $t_hook as $t_label => $t_href ) {
				if( is_numeric( $t_label ) ) {
					print_bracket_link_prepared( $t_href );
				} else {
					print_bracket_link( $t_href, $t_label );
				}
			}
		} elseif( !empty( $t_hook ) ) {
			print_bracket_link_prepared( $t_hook );
		}
	}
}

echo '</span></td>';

# prev/next links
if( $t_bugslist ) {
	echo '<td class="center prev-next-links"><span class="small">';

	$t_bugslist = explode( ',', $t_bugslist );
	$t_index = array_search( $f_bug_id, $t_bugslist );
	if( false !== $t_index ) {
		if( isset( $t_bugslist[$t_index-1] ) ) {
			print_bracket_link( 'view.php?id='.$t_bugslist[$t_index-1], '&lt;&lt;', false, 'previous-bug' );
		}

		if( isset( $t_bugslist[$t_index+1] ) ) {
			print_bracket_link( 'view.php?id='.$t_bugslist[$t_index+1], '&gt;&gt;', false, 'next-bug' );
		}
	}
	echo '</span></td>';
}


# Links
echo '<td class="right alternate-views-links" colspan="2">';

if( !is_blank( $t_history_link ) ) {
	# History
	echo '<span class="small">';
	print_bracket_link( $t_history_link, lang_get( 'bug_history' ), false, 'bug-history' );
	echo '</span>';
}

# Print Bug
echo '<span class="small">';
print_bracket_link( $t_print_link, lang_get( 'print' ), false, 'print' );
echo '</span>';
echo '</td>';
echo '</tr>';

if( $t_top_buttons_enabled ) {
	echo '<tr class="top-buttons">';
	echo '<td colspan="6">';
	html_buttons_view_bug_page( $t_bug_id );
	echo '</td>';
	echo '</tr>';
}

echo '</thead>';

if( $t_bottom_buttons_enabled ) {
	echo '<tfoot>';
	echo '<tr class="details-footer"><td colspan="6">';
	html_buttons_view_bug_page( $t_bug_id );
	echo '</td></tr>';
	echo '</tfoot>';
}

echo '<tbody>';

if( $t_show_id || $t_show_project || $t_show_category || $t_show_view_state || $t_show_date_submitted || $t_show_last_updated ) {
	# Labels
	echo '<tr class="bug-header">';
	echo '<th class="bug-id category" width="15%">', $t_show_id ? lang_get( 'id' ) : '', '</th>';
	echo '<th class="bug-project category" width="20%">', $t_show_project ? lang_get( 'email_project' ) : '', '</th>';
	echo '<th class="bug-category category" width="15%">', $t_show_category ? lang_get( 'category' ) : '', '</th>';
	echo '<th class="bug-view-status category" width="15%">', $t_show_view_state ? lang_get( 'view_status' ) : '', '</th>';
	echo '<th class="bug-date-submitted category" width="15%">', $t_show_date_submitted ? lang_get( 'date_submitted' ) : '', '</th>';
	echo '<th class="bug-last-modified category" width="20%">', $t_show_last_updated ? lang_get( 'last_update' ) : '','</th>';
	echo '</tr>';

	echo '<tr class="bug-header-data">';

	# Bug ID
	echo '<td class="bug-id">', $t_formatted_bug_id, '</td>';

	# Project
	echo '<td class="bug-project">', $t_project_name, '</td>';

	# Category
	echo '<td class="bug-category">', $t_category, '</td>';

	# View Status
	echo '<td class="bug-view-status">', $t_bug_view_state_enum, '</td>';

	# Date Submitted
	echo '<td class="bug-date-submitted">', $t_date_submitted, '</td>';

	# Date Updated
	echo '<td class="bug-last-modified">', $t_last_updated, '</td>';

	echo '</tr>';

	# spacer
	echo '<tr class="spacer"><td colspan="6"></td></tr>';
	echo '<tr class="hidden"></tr>';
}

#
# Reporter
#

if( $t_show_reporter ) {
	echo '<tr>';

	$t_spacer = 4;

	# Reporter
	echo '<th class="bug-reporter category">', lang_get( 'reporter' ), '</th>';
	echo '<td class="bug-reporter">';
	print_user_with_subject( $t_bug->reporter_id, $t_bug_id );
	echo '</td>';
	echo '<td colspan="', $t_spacer, '">&#160;</td>';

	echo '</tr>';
}

#
# Handler, Due Date
#

if( $t_show_handler || $t_show_due_date ) {
	echo '<tr>';

	$t_spacer = 2;

	# Handler
	if( $t_show_handler ) {
		echo '<th class="bug-assigned-to category">', lang_get( 'assigned_to' ), '</th>';
		echo '<td class="bug-assigned-to">';
		print_user_with_subject( $t_bug->handler_id, $t_bug_id );
		echo '</td>';
	} else {
		$t_spacer += 2;
	}

	# Due Date
	if( $t_show_due_date ) {
		echo '<th class="bug-due-date category">', lang_get( 'due_date' ), '</th>';

		if( $t_bug_overdue ) {
			echo '<td class="bug-due-date overdue">', $t_bug_due_date, '</td>';
		} else {
			echo '<td class="bug-due-date">', $t_bug_due_date, '</td>';
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

if( $t_show_priority || $t_show_severity || $t_show_reproducibility ) {
	echo '<tr>';

	$t_spacer = 0;

	# Priority
	if( $t_show_priority ) {
		echo '<th class="bug-priority category">', lang_get( 'priority' ), '</th>';
		echo '<td class="bug-priority">', $t_priority, '</td>';
	} else {
		$t_spacer += 2;
	}

	# Severity
	if( $t_show_severity ) {
		echo '<th class="bug-severity category">', lang_get( 'severity' ), '</th>';
		echo '<td class="bug-severity">', $t_severity, '</td>';
	} else {
		$t_spacer += 2;
	}

	# Reproducibility
	if( $t_show_reproducibility ) {
		echo '<th class="bug-reproducibility category">', lang_get( 'reproducibility' ), '</th>';
		echo '<td class="bug-reproducibility">', $t_reproducibility, '</td>';
	} else {
		$t_spacer += 2;
	}

	# spacer
	if( $t_spacer > 0 ) {
		echo '<td colspan="', $t_spacer, '">&#160;</td>';
	}

	echo '</tr>';
}

#
# Status, Resolution
#

if( $t_show_status || $t_show_resolution ) {
	echo '<tr>';

	$t_spacer = 2;

	# Status
	if( $t_show_status ) {
		echo '<th class="bug-status category">', lang_get( 'status' ), '</th>';

		# choose color based on status
		$t_status_label = html_get_status_css_class( $t_bug->status );

		echo '<td class="bug-status ', $t_status_label, '">', $t_status, '</td>';
	} else {
		$t_spacer += 2;
	}

	# Resolution
	if( $t_show_resolution ) {
		echo '<th class="bug-resolution category">', lang_get( 'resolution' ), '</th>';
		echo '<td class="bug-resolution">', $t_resolution, '</td>';
	} else {
		$t_spacer += 2;
	}

	# spacer
	if( $t_spacer > 0 ) {
		echo '<td colspan="', $t_spacer, '">&#160;</td>';
	}

	echo '</tr>';
}

#
# Projection, ETA
#

if( $t_show_projection || $t_show_eta ) {
	echo '<tr>';

	$t_spacer = 2;

	if( $t_show_projection ) {
		# Projection
		echo '<th class="bug-projection category">', lang_get( 'projection' ), '</th>';
		echo '<td class="bug-projection">', $t_projection, '</td>';
	} else {
		$t_spacer += 2;
	}

	# ETA
	if( $t_show_eta ) {
		echo '<th class="bug-eta category">', lang_get( 'eta' ), '</th>';
		echo '<td class="bug-eta">', $t_eta, '</td>';
	} else {
		$t_spacer += 2;
	}

	echo '<td colspan="', $t_spacer, '">&#160;</td>';
	echo '</tr>';
}

#
# Platform, OS, OS Version
#

if( ( $t_show_platform || $t_show_os || $t_show_os_version ) &&
	( $t_platform || $t_os || $t_os_version )) {
	$t_spacer = 0;

	echo '<tr>';

	# Platform
	if( $t_show_platform ) {
		echo '<th class="bug-platform category">', lang_get( 'platform' ), '</th>';
		echo '<td class="bug-platform">', $t_platform, '</td>';
	} else {
		$t_spacer += 2;
	}

	# Operating System
	if( $t_show_os ) {
		echo '<th class="bug-os category">', lang_get( 'os' ), '</th>';
		echo '<td class="bug-os">', $t_os, '</td>';
	} else {
		$t_spacer += 2;
	}

	# OS Version
	if( $t_show_os_version ) {
		echo '<th class="bug-os-version category">', lang_get( 'os_version' ), '</th>';
		echo '<td class="bug-os-version">', $t_os_version, '</td>';
	} else {
		$t_spacer += 2;
	}

	if( $t_spacer > 0 ) {
		echo '<td colspan="', $t_spacer, '">&#160;</td>';
	}

	echo '</tr>';
}

#
# Product Version, Product Build
#

if( $t_show_product_version || $t_show_product_build ) {
	$t_spacer = 2;

	echo '<tr>';

	# Product Version
	if( $t_show_product_version ) {
		echo '<th class="bug-product-version category">', lang_get( 'product_version' ), '</th>';
		echo '<td class="bug-product-version">', $t_product_version_string, '</td>';
	} else {
		$t_spacer += 2;
	}

	# Product Build
	if( $t_show_product_build ) {
		echo '<th class="bug-product-build category">', lang_get( 'product_build' ), '</th>';
		echo '<td class="bug-product-build">', $t_product_build, '</td>';
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

if( $t_show_target_version || $t_show_fixed_in_version ) {
	$t_spacer = 2;

	echo '<tr>';

	# target version
	if( $t_show_target_version ) {
		# Target Version
		echo '<th class="bug-target-version category">', lang_get( 'target_version' ), '</th>';
		echo '<td class="bug-target-version">', $t_target_version_string, '</td>';
	} else {
		$t_spacer += 2;
	}

	# fixed in version
	if( $t_show_fixed_in_version ) {
		echo '<th class="bug-fixed-in-version category">', lang_get( 'fixed_in_version' ), '</th>';
		echo '<td class="bug-fixed-in-version">', $t_fixed_in_version_string, '</td>';
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

event_signal( 'EVENT_VIEW_BUG_DETAILS', array( $t_bug_id ) );

# spacer
echo '<tr class="spacer"><td colspan="6"></td></tr>';
echo '<tr class="hidden"></tr>';

#
# Bug Details (screen wide fields)
#

# Summary
if( $t_show_summary ) {
	echo '<tr>';
	echo '<th class="bug-summary category">', lang_get( 'summary' ), '</th>';
	echo '<td class="bug-summary" colspan="5">', $t_summary, '</td>';
	echo '</tr>';
}

# Description
if( $t_show_description ) {
	echo '<tr>';
	echo '<th class="bug-description category">', lang_get( 'description' ), '</th>';
	echo '<td class="bug-description" colspan="5">', $t_description, '</td>';
	echo '</tr>';
}

# Steps to Reproduce
if( $t_show_steps_to_reproduce ) {
	echo '<tr>';
	echo '<th class="bug-steps-to-reproduce category">', lang_get( 'steps_to_reproduce' ), '</th>';
	echo '<td class="bug-steps-to-reproduce" colspan="5">', $t_steps_to_reproduce, '</td>';
	echo '</tr>';
}

# Additional Information
if( $t_show_additional_information ) {
	echo '<tr>';
	echo '<th class="bug-additional-information category">', lang_get( 'additional_information' ), '</th>';
	echo '<td class="bug-additional-information" colspan="5">', $t_additional_information, '</td>';
	echo '</tr>';
}

# Tagging
if( $t_show_tags ) {
	echo '<tr>';
	echo '<th class="bug-tags category">', lang_get( 'tags' ), '</th>';
	echo '<td class="bug-tags" colspan="5">';
	tag_display_attached( $t_bug_id );
	echo '</td></tr>';
}

# Attachments Form
if( $t_can_attach_tag ) {
	echo '<tr>';
	echo '<th class="bug-attach-tags category">', lang_get( 'tag_attach_long' ), '</th>';
	echo '<td class="bug-attach-tags" colspan="5">';
	print_tag_attach_form( $t_bug_id );
	echo '</td></tr>';
}

# spacer
echo '<tr class="spacer"><td colspan="6"></td></tr>';
echo '<tr class="hidden"></tr>';

# Custom Fields
$t_custom_fields_found = false;
$t_related_custom_field_ids = custom_field_get_linked_ids( $t_bug->project_id );

foreach( $t_related_custom_field_ids as $t_id ) {
	if( !custom_field_has_read_access( $t_id, $f_bug_id ) ) {
		continue;
	} # has read access

	$t_custom_fields_found = true;
	$t_def = custom_field_get_definition( $t_id );

	echo '<tr>';
	echo '<th class="bug-custom-field category">', string_display( lang_get_defaulted( $t_def['name'] ) ), '</th>';
	echo '<td class="bug-custom-field" colspan="5">';
	print_custom_field_value( $t_def, $t_id, $f_bug_id );
	echo '</td></tr>';
}

if( $t_custom_fields_found ) {
	# spacer
	echo '<tr class="spacer"><td colspan="6"></td></tr>';
	echo '<tr class="hidden"></tr>';
}

# Attachments
if( $t_show_attachments ) {
	echo '<tr id="attachments">';
	echo '<th class="bug-attachments category">', lang_get( 'attached_files' ), '</th>';
	echo '<td class="bug-attachments" colspan="5">';
	print_bug_attachments_list( $t_bug_id );
	echo '</td></tr>';
}

echo '</tbody></table>';
echo '</div>';

# User list sponsoring the bug
if( $t_show_sponsorships_box ) {
	define( 'BUG_SPONSORSHIP_LIST_VIEW_INC_ALLOW', true );
	include( $t_mantis_dir . 'bug_sponsorship_list_view_inc.php' );
}

# Bug Relationships
if( $t_show_relationships_box ) {
	relationship_view_box( $t_bug->id );
}

# File upload box
if( $t_show_upload_form ) {
	define( 'BUG_FILE_UPLOAD_INC_ALLOW', true );
	include( $t_mantis_dir . 'bug_file_upload_inc.php' );
}

# User list monitoring the bug
if( $t_show_monitor_box ) {
	define( 'BUG_MONITOR_LIST_VIEW_INC_ALLOW', true );
	include( $t_mantis_dir . 'bug_monitor_list_view_inc.php' );
}

# Bugnotes and "Add Note" box
if( 'ASC' == current_user_get_pref( 'bugnote_order' ) ) {
	define( 'BUGNOTE_VIEW_INC_ALLOW', true );
	include( $t_mantis_dir . 'bugnote_view_inc.php' );

	if( !$t_force_readonly ) {
		define( 'BUGNOTE_ADD_INC_ALLOW', true );
		include( $t_mantis_dir . 'bugnote_add_inc.php' );
	}
} else {
	if( !$t_force_readonly ) {
		define( 'BUGNOTE_ADD_INC_ALLOW', true );
		include( $t_mantis_dir . 'bugnote_add_inc.php' );
	}

	define( 'BUGNOTE_VIEW_INC_ALLOW', true );
	include( $t_mantis_dir . 'bugnote_view_inc.php' );
}

# Allow plugins to display stuff after notes
event_signal( 'EVENT_VIEW_BUG_EXTRA', array( $f_bug_id ) );

# Time tracking statistics
if( config_get( 'time_tracking_enabled' ) &&
	access_has_bug_level( config_get( 'time_tracking_view_threshold' ), $f_bug_id ) ) {
	define( 'BUGNOTE_STATS_INC_ALLOW', true );
	include( $t_mantis_dir . 'bugnote_stats_inc.php' );
}

# History
if( $t_show_history ) {
	define( 'HISTORY_INC_ALLOW', true );
	include( $t_mantis_dir . 'history_inc.php' );
}

html_page_bottom();

last_visited_issue( $t_bug_id );
