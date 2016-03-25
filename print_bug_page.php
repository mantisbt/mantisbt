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
 * Print Bug Page
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses bug_api.php
 * @uses category_api.php
 * @uses columns_api.php
 * @uses compress_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses custom_field_api.php
 * @uses date_api.php
 * @uses file_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses history_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses last_visited_api.php
 * @uses prepare_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses relationship_api.php
 * @uses string_api.php
 * @uses tag_api.php
 * @uses utility_api.php
 * @uses version_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'bug_api.php' );
require_api( 'category_api.php' );
require_api( 'columns_api.php' );
require_api( 'compress_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'custom_field_api.php' );
require_api( 'date_api.php' );
require_api( 'file_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'history_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'last_visited_api.php' );
require_api( 'prepare_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'relationship_api.php' );
require_api( 'string_api.php' );
require_api( 'tag_api.php' );
require_api( 'utility_api.php' );
require_api( 'version_api.php' );

$f_bug_id = gpc_get_int( 'bug_id' );

bug_ensure_exists( $f_bug_id );

$t_bug = bug_get( $f_bug_id, true );

$t_selected_project = helper_get_current_project();
if( $t_bug->project_id != $t_selected_project ) {
	# in case the current project is not the same project of the bug we are viewing...
	# ... override the current project. This to avoid problems with categories and handlers lists etc.
	$g_project_override = $t_bug->project_id;
}

access_ensure_bug_level( config_get( 'view_bug_threshold' ), $f_bug_id );

$t_fields = config_get( 'bug_print_page_fields' );
$t_fields = columns_filter_disabled( $t_fields );

compress_enable();

$t_show_id = in_array( 'id', $t_fields );
$t_show_project = in_array( 'project', $t_fields );
$t_show_category = in_array( 'category_id', $t_fields );
$t_show_date_submitted = in_array( 'date_submitted', $t_fields );
$t_show_last_updated = in_array( 'last_updated', $t_fields );
$t_show_view_state = in_array( 'view_state', $t_fields );
$t_show_reporter = in_array( 'reporter', $t_fields );
$t_show_handler = in_array( 'handler', $t_fields ) && access_has_bug_level( config_get( 'view_handler_threshold' ), $f_bug_id );
$t_show_due_date = in_array( 'due_date', $t_fields ) && access_has_bug_level( config_get( 'due_date_view_threshold' ), $f_bug_id );
$t_show_priority = in_array( 'priority', $t_fields );
$t_show_severity = in_array( 'severity', $t_fields );
$t_show_reproducibility = in_array( 'reproducibility', $t_fields );
$t_show_platform = in_array( 'platform', $t_fields );
$t_show_os = in_array( 'os', $t_fields );
$t_show_os_version = in_array( 'os_version', $t_fields );
$t_show_status = in_array( 'status', $t_fields );
$t_show_resolution = in_array( 'resolution', $t_fields );
$t_show_projection = in_array( 'projection', $t_fields );
$t_show_eta = in_array( 'eta', $t_fields );
$t_show_versions = version_should_show_product_version( $t_bug->project_id );
$t_show_product_version = $t_show_versions && in_array( 'product_version', $t_fields );
$t_show_product_build = $t_show_versions && in_array( 'product_build', $t_fields ) && config_get( 'enable_product_build' );
$t_show_fixed_in_version = $t_show_versions && in_array( 'fixed_in_version', $t_fields );
$t_show_target_version = $t_show_versions && in_array( 'target_version', $t_fields ) && access_has_bug_level( config_get( 'roadmap_view_threshold' ), $f_bug_id );
$t_show_summary = in_array( 'summary', $t_fields );
$t_show_description = in_array( 'description', $t_fields );
$t_show_steps_to_reproduce = !is_blank( $t_bug->steps_to_reproduce ) && in_array( 'steps_to_reproduce', $t_fields );
$t_show_additional_information = !is_blank( $t_bug->additional_information ) && in_array( 'additional_info', $t_fields );
$t_show_tags = in_array( 'tags', $t_fields );
$t_show_attachments = in_array( 'attachments', $t_fields );
$t_show_history = access_has_bug_level( config_get( 'view_history_threshold' ), $f_bug_id );

$t_window_title = string_display_line( config_get( 'window_title' ) );
$t_project_name = $t_show_project ? string_display_line( project_get_name( $t_bug->project_id ) ) : '';
$t_formatted_bug_id = $t_show_id ? bug_format_id( $f_bug_id ) : '';
$t_category_name = $t_show_category ? string_display_line( category_full_name( $t_bug->category_id ) ) : '';
$t_severity = string_display_line( get_enum_element( 'severity', $t_bug->severity ) );
$t_reproducibility = string_display_line( get_enum_element( 'reproducibility', $t_bug->reproducibility ) );
$t_date_submitted = $t_show_date_submitted ? string_display_line( date( config_get( 'normal_date_format' ), $t_bug->date_submitted ) ) : '';
$t_last_updated = $t_show_last_updated ? string_display_line( date( config_get( 'normal_date_format' ), $t_bug->last_updated ) ) : '';
$t_platform = string_display_line( $t_bug->platform );
$t_os = string_display_line( $t_bug->os );
$t_os_version = string_display_line( $t_bug->os_build );
$t_is = string_display_line( $t_bug->os );
$t_status = string_display_line( get_enum_element( 'status', $t_bug->status ) );
$t_priority = string_display_line( get_enum_element( 'priority', $t_bug->priority ) );
$t_resolution = string_display_line( get_enum_element( 'resolution', $t_bug->resolution ) );
$t_product_build = string_display_line( $t_bug->build );
$t_projection = string_display_line( get_enum_element( 'projection', $t_bug->projection ) );
$t_eta = string_display_line( get_enum_element( 'eta', $t_bug->eta ) );
$t_summary = string_display_line_links( bug_format_summary( $f_bug_id, SUMMARY_FIELD ) );
$t_description = string_display_links( $t_bug->description );
$t_steps_to_reproduce = string_display_links( $t_bug->steps_to_reproduce );
$t_additional_information = string_display_links( $t_bug->additional_information );
$t_view_state = $t_show_view_state ? get_enum_element( 'view_state', $t_bug->view_state ) : '';

if( $t_show_due_date ) {
	if( !date_is_null( $t_bug->due_date ) ) {
		$t_due_date = date( config_get( 'normal_date_format' ), $t_bug->due_date );
	} else {
		$t_due_date = '';
	}
}

$t_product_version  =
	$t_show_product_version ?
		string_display_line( prepare_version_string( $t_bug->project_id, version_get_id( $t_bug->version, $t_bug->project_id ) ) ) : '';

$t_target_version =
	$t_show_target_version ?
		string_display_line( prepare_version_string( $t_bug->project_id, version_get_id( $t_bug->target_version, $t_bug->project_id ) ) ) : '';

$t_fixed_in_version =
	$t_show_fixed_in_version ?
		string_display_line( prepare_version_string( $t_bug->project_id, version_get_id( $t_bug->fixed_in_version, $t_bug->project_id ) ) ) : '';

html_page_top1( bug_format_summary( $f_bug_id, SUMMARY_CAPTION ) );
html_head_end();
html_body_begin();

echo '<br />';

echo '<table class="width100" cellspacing="1">';
echo '<tr>';
echo '<td class="form-title" colspan="6">';
echo '<div class="center">', $t_window_title;

if( !is_blank( $t_project_name ) ) {
	echo ' - ' . $t_project_name;
}

echo '</div></td>';
echo '</tr>';

echo '<tr>';
echo '<td class="form-title" colspan="6">', lang_get( 'bug_view_title' ), '</td>';
echo '</tr>';

echo '<tr><td class="print-spacer" colspan="6"><hr /></td></tr>';

if( $t_show_id || $t_show_project || $t_show_category || $t_show_view_state ||
	 $t_show_date_submitted || $t_show_last_updated ) {
	echo '<tr class="print-category">';
	echo '<td class="print" width="16%">', $t_show_id ? lang_get( 'id' ) : '', '</td>';
	echo '<td class="print" width="16%">', $t_show_project ? lang_get( 'email_project' ) : '', '</td>';
	echo '<td class="print" width="16%">', $t_show_category ? lang_get( 'category' ) : '', '</td>';
	echo '<td class="print" width="16%">', $t_show_view_state ? lang_get( 'view_status' ) : '', '</td>';
	echo '<td class="print" width="16%">', $t_show_date_submitted ? lang_get( 'date_submitted' ) : '', '</td>';
	echo '<td class="print" width="16%">', $t_show_last_updated ? lang_get( 'last_update' ) : '', '</td>';
	echo '</tr>';

	echo '<tr class="print">';
	echo '<td class="print">', $t_formatted_bug_id, '</td>';
	echo '<td class="print">', $t_project_name, '</td>';
	echo '<td class="print">', $t_category_name, '</td>';
	echo '<td class="print">', $t_view_state, '</td>';
	echo '<td class="print">', $t_date_submitted, '</td>';
	echo '<td class="print">', $t_last_updated, '</td>';
	echo '</tr>';

	echo '<tr><td class="print-spacer" colspan="6"><hr /></td></tr>';
}

#
# Reporter
#

if( $t_show_reporter ) {
	echo '<tr class="print">';
	echo '<th class="print-category">', lang_get( 'reporter' ), '</th>';
	echo '<td class="print">';
	print_user_with_subject( $t_bug->reporter_id, $f_bug_id );
	echo '</td>';
	echo '<td class="print" colspan="4">&#160;</td>';
	echo '</tr>';
}

#
# Handler, Due Date
#

if( $t_show_handler || $t_show_due_date ) {
	$t_spacer = 2;

	echo '<tr class="print">';

	if( $t_show_handler ) {
		echo '<th class="print-category">', lang_get( 'assigned_to' ), '</th>';
		echo '<td class="print">';
		print_user_with_subject( $t_bug->handler_id, $f_bug_id );
		echo '</td>';
	} else {
		$t_spacer += 2;
	}

	if( $t_show_due_date ) {
		echo '<th class="print-category">', lang_get( 'due_date' ), '</th>';
		echo '<td class="print">', $t_due_date, '</td>';
	} else {
		$t_spacer += 2;
	}

	echo '<td class="print" colspan="', $t_spacer, '">&#160;</td>';
	echo '</tr>';
}

#
# Priority, Severity, Reproducibility
#

if( $t_show_priority || $t_show_severity || $t_show_reproducibility ) {
	echo '<tr class="print">';

	$t_spacer = 0;

	if( $t_show_priority ) {
		echo '<th class="print-category">', lang_get( 'priority' ), '</th>';
		echo '<td class="print">', $t_priority, '</td>';
	} else {
		$t_spacer += 2;
	}

	if( $t_show_severity ) {
		echo '<th class="print-category">', lang_get( 'severity' ), '</th>';
		echo '<td class="print">', $t_severity, '</td>';
	} else {
		$t_spacer += 2;
	}

	if( $t_show_reproducibility ) {
		echo '<th class="print-category">', lang_get( 'reproducibility' ), '</th>';
		echo '<td class="print">', $t_reproducibility, '</td>';
	} else {
		$t_spacer += 2;
	}

	if( $t_spacer > 0 ) {
		echo '<td class="print" colspan="', $t_spacer, '">&#160;</td>';
	}

	echo '</tr>';
}

#
# Status, Resolution
#

if( $t_show_status || $t_show_resolution ) {
	echo '<tr class="print">';

	$t_spacer = 2;

	if( $t_show_status ) {
		echo '<th class="print-category">', lang_get( 'status' ), '</th>';
		echo '<td class="print">', $t_status, '</td>';
	} else {
		$t_spacer += 2;
	}

	if( $t_show_resolution ) {
		echo '<th class="print-category">', lang_get( 'resolution' ), '</th>';
		echo '<td class="print">', $t_resolution, '</td>';
	} else {
		$t_spacer += 2;
	}

	echo '<td class="print" colspan="', $t_spacer, '">&#160;</td>';
	echo '</tr>';
}

#
# Projection, ETA
#

if( $t_show_projection || $t_show_eta ) {
	$t_spacer = 2;

	echo '<tr class="print">';

	if( $t_show_projection ) {
		echo '<th class="print-category">', lang_get( 'projection' ), '</th>';
		echo '<td class="print">', $t_projection, '</td>';
	} else {
		$t_spacer += 2;
	}

	if( $t_show_eta ) {
		echo '<th class="print-category">', lang_get( 'eta' ), '</th>';
		echo '<td class="print">', $t_eta, '</td>';
	} else {
		$t_spacer += 2;
	}

	echo '<td class="print" colspan="', $t_spacer, '">&#160;</td>';
	echo '</tr>';
}

#
# Platform, OS, OS Version
#

if( $t_show_platform || $t_show_os || $t_show_os_version ) {
	echo '<tr class="print">';

	$t_spacer = 0;

	if( $t_show_platform ) {
		echo '<th class="print-category">', lang_get( 'platform' ), '</th>';
		echo '<td class="print">', $t_platform, '</td>';
	} else {
		$t_spacer += 2;
	}

	if( $t_show_os ) {
		echo '<th class="print-category">', lang_get( 'os' ), '</th>';
		echo '<td class="print">', $t_os, '</td>';
	} else {
		$t_spacer += 2;
	}

	if( $t_show_os_version ) {
		echo '<th class="print-category">', lang_get( 'os_version' ), '</th>';
		echo '<td class="print">', $t_os_version, '</td>';
	} else {
		$t_spacer += 2;
	}

	if( $t_spacer > 0 ) {
		echo '<td class="print" colspan="', $t_spacer, '">&#160;</td>';
	}

	echo '</tr>';
}

#
# Product Version, Product Build
#

if( $t_show_product_version || $t_show_product_build ) {
	echo '<tr class="print">';

	$t_spacer = 2;

	if( $t_show_product_version ) {
		echo '<th class="print-category">', lang_get( 'product_version' ), '</th>';
		echo '<td class="print">', $t_product_version, '</td>';
	} else {
		$t_spacer += 2;
	}

	if( $t_show_product_build ) {
		echo '<th class="print-category">', lang_get( 'product_build' ), '</th>';
		echo '<td class="print">', $t_product_build, '</td>';
	} else {
		$t_spacer += 2;
	}

	echo '<td class="print" colspan="', $t_spacer, '">&#160;</td>';
	echo '</tr>';
}

#
# Target Version, Fixed in Version
#

if( $t_show_target_version || $t_show_fixed_in_version ) {
	echo '<tr>';

	$t_spacer = 2;

	if( $t_show_target_version ) {
		echo '<th class="print-category">', lang_get( 'target_version' ), '</th>';
		echo '<td class="print">', $t_target_version, '</td>';
	} else {
		$t_spacer += 2;
	}

	if( $t_show_fixed_in_version ) {
		echo '<th class="print-category">', lang_get( 'fixed_in_version' ), '</th>';
		echo '<td class="print">', $t_fixed_in_version, '</td>';
	} else {
		$t_spacer += 2;
	}

	echo '<td class="print" colspan="', $t_spacer, '">&#160;</td>';
	echo '</tr>';
}

#
# Custom Fields
#

$t_related_custom_field_ids = custom_field_get_linked_ids( $t_bug->project_id );
foreach( $t_related_custom_field_ids as $t_id ) {
	# Don't display the field if user does not have read access to it
	if( !custom_field_has_read_access_by_project_id( $t_id, $t_bug->project_id ) ) {
		continue;
	}

	$t_def = custom_field_get_definition( $t_id );

	echo '<tr class="print">';
	echo '<th class="print-category">', string_display_line( lang_get_defaulted( $t_def['name'] ) ), '</th>';
	echo '<td class="print" colspan="4">';
	print_custom_field_value( $t_def, $t_id, $f_bug_id );
	echo '</td>';
	echo '</tr>';
}       # foreach

echo '<tr><td class="print-spacer" colspan="6"><hr /></td></tr>';

if( $t_show_summary ) {
	echo '<tr class="print">';
	echo '<th class="print-category">', lang_get( 'summary' ), '</th>';
	echo '<td class="print" colspan="5">', $t_summary, '</td>';
	echo '</tr>';
}

if( $t_show_description ) {
	echo '<tr class="print">';
	echo '<th class="print-category">', lang_get( 'description' ), '</th>';
	echo '<td class="print" colspan="5">', $t_description, '</td>';
	echo '</tr>';
}

if( $t_show_steps_to_reproduce ) {
	echo '<tr class="print">';
	echo '<th class="print-category">', lang_get( 'steps_to_reproduce' ), '</th>';
	echo '<td class="print" colspan="5">', $t_steps_to_reproduce, '</td>';
	echo '</tr>';
}

if( $t_show_additional_information ) {
	echo '<tr class="print">';
	echo '<th class="print-category">', lang_get( 'additional_information' ), '</th>';
	echo '<td class="print" colspan="5">', $t_additional_information, '</td>';
	echo '</tr>';
}

# Tagging
if( $t_show_tags ) {
	echo '<tr class="print">';
	echo '<th class="print-category">', lang_get( 'tags' ), '</th>';
	echo '<td class="print" colspan="5">';
	tag_display_attached( $f_bug_id );
	echo '</td></tr>';
}

echo '<tr class="print">';
echo '<td class="print-category">' . lang_get( 'bug_relationships' ) . '</td>';
echo '<td class="print" colspan="5">' . relationship_get_summary_html_preview( $f_bug_id ) . '</td></tr>';

if( $t_show_attachments ) {
	echo '<tr class="print">';
	echo '<th class="print-category">', lang_get( 'attached_files' ), '</th>';
	echo '<td class="print" colspan="5">';

	$t_attachments = file_get_visible_attachments( $f_bug_id );
	$t_first_attachment = true;
	$t_path = config_get_global( 'path' );

	foreach ( $t_attachments as $t_attachment ) {
		if( $t_first_attachment ) {
			$t_first_attachment = false;
		} else {
			echo '<br />';
		}

		$c_filename = string_display_line( $t_attachment['display_name'] );
		$c_download_url = $t_path . htmlspecialchars( $t_attachment['download_url'] );
		$c_filesize = number_format( $t_attachment['size'] );
		$c_date_added = date( config_get( 'normal_date_format' ), $t_attachment['date_added'] );
		if( isset( $t_attachment['icon'] ) ) {
			echo '<img src="', $t_attachment['icon']['url'], '" alt="', $t_attachment['icon']['alt'], '" />&#160;';
		}

		echo $c_filename . ' (' .$c_filesize . ') <span class="italic">' . $c_date_added . '</span><br />' . $c_download_url;

		if( $t_attachment['preview'] && $t_attachment['type'] == 'image' ) {
			echo '<br /><img src="', $t_attachment['download_url'], '" alt="', $t_attachment['alt'], '" /><br />';
		}
	}

	echo '</td></tr>';
}

echo '</table>';

define( 'PRINT_BUGNOTE_INC_ALLOW', true );
include( dirname( __FILE__ ) . '/print_bugnote_inc.php' ) ;

#
# Issue History
#

if( $t_show_history ) {
	echo '<br />';
	echo '<table class="width100" cellspacing="1">';
	echo '<tr><td class="form-title" colspan="2">', lang_get( 'bug_history' ), '</td></tr>';
	echo '<tr><td class="print-spacer" colspan="6"><hr /></td></tr>';

	echo '<tr class="print-category">';
	echo '<th class="row-category-history">', lang_get( 'date_modified' ), '</th>';
	echo '<th class="row-category-history">', lang_get( 'username' ), '</th>';
	echo '<th class="row-category-history">', lang_get( 'field' ), '</th>';
	echo '<th class="row-category-history">', lang_get( 'change' ), '</th>';
	echo '</tr>';

	$t_history = history_get_events_array( $f_bug_id );

	foreach ( $t_history as $t_item ) {
		echo '<tr class="print">';
		echo '<td class="print">', $t_item['date'], '</td>';
		echo '<td class="print">';
		print_user( $t_item['userid'] );
		echo '</td>';
		echo '<td class="print">', string_display( $t_item['note'] ), '</td>';
		echo '<td class="print">', string_display_line_links( $t_item['change'] ), '</td>';
		echo '</tr>';
	}

	echo '</table>';
}

last_visited_issue( $f_bug_id );

html_body_end();
html_end();
