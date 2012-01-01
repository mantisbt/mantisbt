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
 * @package MantisBT
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
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

/**
 * MantisBT Core API's
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

$tpl_bug = bug_get( $f_bug_id, true );

$t_selected_project = helper_get_current_project();
if ( $tpl_bug->project_id != $t_selected_project ) {
	# in case the current project is not the same project of the bug we are viewing...
	# ... override the current project. This to avoid problems with categories and handlers lists etc.
	$g_project_override = $tpl_bug->project_id;
}

access_ensure_bug_level( VIEWER, $f_bug_id );

$t_fields = config_get( 'bug_print_page_fields' );
$t_fields = columns_filter_disabled( $t_fields );

compress_enable();

$tpl_show_id = in_array( 'id', $t_fields );
$tpl_show_project = in_array( 'project', $t_fields );
$tpl_show_category = in_array( 'category_id', $t_fields );
$tpl_show_date_submitted = in_array( 'date_submitted', $t_fields );
$tpl_show_last_updated = in_array( 'last_updated', $t_fields );
$tpl_show_view_state = in_array( 'view_state', $t_fields );
$tpl_show_reporter = in_array( 'reporter', $t_fields );
$tpl_show_handler = in_array( 'handler', $t_fields ) && access_has_bug_level( config_get( 'view_handler_threshold' ), $f_bug_id );
$tpl_show_due_date = in_array( 'due_date', $t_fields ) && access_has_bug_level( config_get( 'due_date_view_threshold' ), $f_bug_id );
$tpl_show_priority = in_array( 'priority', $t_fields );
$tpl_show_severity = in_array( 'severity', $t_fields );
$tpl_show_reproducibility = in_array( 'reproducibility', $t_fields );
$tpl_show_platform = in_array( 'platform', $t_fields );
$tpl_show_os = in_array( 'os', $t_fields );
$tpl_show_os_version = in_array( 'os_version', $t_fields );
$tpl_show_status = in_array( 'status', $t_fields );
$tpl_show_resolution = in_array( 'resolution', $t_fields );
$tpl_show_projection = in_array( 'projection', $t_fields );
$tpl_show_eta = in_array( 'eta', $t_fields );
$tpl_show_versions = version_should_show_product_version( $tpl_bug->project_id );
$tpl_show_product_version = $tpl_show_versions && in_array( 'product_version', $t_fields );
$tpl_show_product_build = $tpl_show_versions && in_array( 'product_build', $t_fields ) && config_get( 'enable_product_build' );
$tpl_show_fixed_in_version = $tpl_show_versions && in_array( 'fixed_in_version', $t_fields );
$tpl_show_target_version = $tpl_show_versions && in_array( 'target_version', $t_fields ) && access_has_bug_level( config_get( 'roadmap_view_threshold' ), $f_bug_id );
$tpl_show_summary = in_array( 'summary', $t_fields );
$tpl_show_description = in_array( 'description', $t_fields );
$tpl_show_steps_to_reproduce = in_array( 'steps_to_reproduce', $t_fields );
$tpl_show_additional_information = in_array( 'additional_info', $t_fields );
$tpl_show_tags = in_array( 'tags', $t_fields );
$tpl_show_attachments = in_array( 'attachments', $t_fields );
$tpl_show_history = access_has_bug_level( config_get( 'view_history_threshold' ), $f_bug_id );

$tpl_window_title = string_display_line( config_get( 'window_title' ) );
$tpl_project_name = $tpl_show_project ? string_display_line( project_get_name( $tpl_bug->project_id ) ) : '';
$tpl_formatted_bug_id = $tpl_show_id ? bug_format_id( $f_bug_id ) : '';
$tpl_category_name = $tpl_show_category ? string_display_line( category_full_name( $tpl_bug->category_id ) ) : '';
$tpl_severity = string_display_line( get_enum_element( 'severity', $tpl_bug->severity ) );
$tpl_reproducibility = string_display_line( get_enum_element( 'reproducibility', $tpl_bug->reproducibility ) );
$tpl_date_submitted = $tpl_show_date_submitted ? string_display_line( date( config_get( 'normal_date_format' ), $tpl_bug->date_submitted ) ) : '';
$tpl_last_updated = $tpl_show_last_updated ? string_display_line( date( config_get( 'normal_date_format' ), $tpl_bug->last_updated ) ) : '';
$tpl_platform = string_display_line( $tpl_bug->platform );
$tpl_os = string_display_line( $tpl_bug->os );
$tpl_os_version = string_display_line( $tpl_bug->os_build );
$tpl_is = string_display_line( $tpl_bug->os );
$tpl_status = string_display_line( get_enum_element( 'status', $tpl_bug->status ) );
$tpl_priority = string_display_line( get_enum_element( 'priority', $tpl_bug->priority ) );
$tpl_resolution = string_display_line( get_enum_element( 'resolution', $tpl_bug->resolution ) );
$tpl_product_build = string_display_line( $tpl_bug->build );
$tpl_projection = string_display_line( get_enum_element( 'projection', $tpl_bug->projection ) );
$tpl_eta = string_display_line ( get_enum_element( 'eta', $tpl_bug->eta ) );
$tpl_summary = string_display_line_links( bug_format_summary( $f_bug_id, SUMMARY_FIELD ) );
$tpl_description = string_display_links( $tpl_bug->description );
$tpl_steps_to_reproduce = string_display_links( $tpl_bug->steps_to_reproduce );
$tpl_additional_information = string_display_links( $tpl_bug->additional_information );
$tpl_view_state = $tpl_show_view_state ? get_enum_element( 'view_state', $tpl_bug->view_state ) : '';

if ( $tpl_show_due_date ) {
	if ( !date_is_null( $tpl_bug->due_date ) ) {
		$tpl_due_date = date( config_get( 'normal_date_format' ), $tpl_bug->due_date );
	} else {
		$tpl_due_date = '';
	}
}

$tpl_product_version  =
	$tpl_show_product_version ?
		string_display_line( prepare_version_string( $tpl_bug->project_id, version_get_id( $tpl_bug->version, $tpl_bug->project_id ) ) ) : '';

$tpl_target_version =
	$tpl_show_target_version ?
		string_display_line( prepare_version_string( $tpl_bug->project_id, version_get_id( $tpl_bug->target_version, $tpl_bug->project_id ) ) ) : '';

$tpl_fixed_in_version =
	$tpl_show_fixed_in_version ?
		string_display_line( prepare_version_string( $tpl_bug->project_id, version_get_id( $tpl_bug->fixed_in_version, $tpl_bug->project_id ) ) ) : '';

html_page_top1( bug_format_summary( $f_bug_id, SUMMARY_CAPTION ) );
html_head_end();
html_body_begin();

echo '<br />';

echo '<table class="width100" cellspacing="1">';
echo '<tr>';
echo '<td class="form-title" colspan="6">';
echo '<div class="center">', $tpl_window_title;

if ( !is_blank( $tpl_project_name ) ) {
	echo ' - ' . $tpl_project_name;
}

echo '</div></td>';
echo '</tr>';

echo '<tr>';
echo '<td class="form-title" colspan="6">', lang_get( 'bug_view_title' ), '</td>';
echo '</tr>';

echo '<tr><td class="print-spacer" colspan="6"><hr /></td></tr>';

if ( $tpl_show_id || $tpl_show_project || $tpl_show_category || $tpl_show_view_state ||
	 $tpl_show_date_submitted || $tpl_show_last_updated ) {
	echo '<tr class="print-category">';
	echo '<td class="print" width="16%">', $tpl_show_id ? lang_get( 'id' ) : '', '</td>';
	echo '<td class="print" width="16%">', $tpl_show_project ? lang_get( 'email_project' ) : '', '</td>';
	echo '<td class="print" width="16%">', $tpl_show_category ? lang_get( 'category' ) : '', '</td>';
	echo '<td class="print" width="16%">', $tpl_show_view_state ? lang_get( 'view_status' ) : '', '</td>';
	echo '<td class="print" width="16%">', $tpl_show_date_submitted ? lang_get( 'date_submitted' ) : '', '</td>';
	echo '<td class="print" width="16%">', $tpl_show_last_updated ? lang_get( 'last_update' ) : '', '</td>';
	echo '</tr>';

	echo '<tr class="print">';
	echo '<td class="print">', $tpl_formatted_bug_id, '</td>';
	echo '<td class="print">', $tpl_project_name, '</td>';
	echo '<td class="print">', $tpl_category_name, '</td>';
	echo '<td class="print">', $tpl_view_state, '</td>';
	echo '<td class="print">', $tpl_date_submitted, '</td>';
	echo '<td class="print">', $tpl_last_updated, '</td>';
	echo '</tr>';

	echo '<tr><td class="print-spacer" colspan="6"><hr /></td></tr>';
}

#
# Reporter
#

if ( $tpl_show_reporter ) {
	echo '<tr class="print">';
	echo '<th class="print-category">', lang_get( 'reporter' ), '</th>';
	echo '<td class="print">';
	print_user_with_subject( $tpl_bug->reporter_id, $f_bug_id );
	echo '</td>';
	echo '<td class="print" colspan="4">&#160;</td>';
	echo '</tr>';
}

#
# Handler, Due Date
#

if ( $tpl_show_handler || $tpl_show_due_date ) {
	$t_spacer = 2;

	echo '<tr class="print">';

	if ( $tpl_show_handler ) {
		echo '<th class="print-category">', lang_get( 'assigned_to' ), '</th>';
		echo '<td class="print">';
		print_user_with_subject( $tpl_bug->handler_id, $f_bug_id );
		echo '</td>';
	} else {
		$t_spacer += 2;
	}

	if ( $tpl_show_due_date ) {
		echo '<th class="print-category">', lang_get( 'due_date' ), '</th>';
		echo '<td class="print">', $tpl_due_date, '</td>';
	} else {
		$t_spacer += 2;
	}

	echo '<td class="print" colspan="', $t_spacer, '">&#160;</td>';
	echo '</tr>';
}

#
# Priority, Severity, Reproducibility
#

if ( $tpl_show_priority || $tpl_show_severity || $tpl_show_reproducibility ) {
	echo '<tr class="print">';

	$t_spacer = 0;

	if ( $tpl_show_priority ) {
		echo '<th class="print-category">', lang_get( 'priority' ), '</th>';
		echo '<td class="print">', $tpl_priority, '</td>';
	} else {
		$t_spacer += 2;
	}

	if ( $tpl_show_severity ) {
		echo '<th class="print-category">', lang_get( 'severity' ), '</th>';
		echo '<td class="print">', $tpl_severity, '</td>';
	} else {
		$t_spacer += 2;
	}

	if ( $tpl_show_reproducibility ) {
		echo '<th class="print-category">', lang_get( 'reproducibility' ), '</th>';
		echo '<td class="print">', $tpl_reproducibility, '</td>';
	} else {
		$t_spacer += 2;
	}

	if ( $t_spacer > 0 ) {
		echo '<td class="print" colspan="', $t_spacer, '">&#160;</td>';
	}

	echo '</tr>';
}

#
# Status, Resolution
#

if ( $tpl_show_status || $tpl_show_resolution ) {
	echo '<tr class="print">';

	$t_spacer = 2;

	if ( $tpl_show_status ) {
		echo '<th class="print-category">', lang_get( 'status' ), '</th>';
		echo '<td class="print">', $tpl_status, '</td>';
	} else {
		$t_spacer += 2;
	}

	if ( $tpl_show_resolution ) {
		echo '<th class="print-category">', lang_get( 'resolution' ), '</th>';
		echo '<td class="print">', $tpl_resolution, '</td>';
	} else {
		$t_spacer += 2;
	}

	echo '<td class="print" colspan="', $t_spacer, '">&#160;</td>';
	echo '</tr>';
}

#
# Projection, ETA
#

if ( $tpl_show_projection || $tpl_show_eta ) {
	$t_spacer = 2;

	echo '<tr class="print">';

	if ( $tpl_show_projection ) {
		echo '<th class="print-category">', lang_get( 'projection' ), '</th>';
		echo '<td class="print">', $tpl_projection, '</td>';
	} else {
		$t_spacer += 2;
	}

	if ( $tpl_show_eta ) {
		echo '<th class="print-category">', lang_get( 'eta' ), '</th>';
		echo '<td class="print">', $tpl_eta, '</td>';
	} else {
		$t_spacer += 2;
	}

	echo '<td class="print" colspan="', $t_spacer, '">&#160;</td>';
	echo '</tr>';
}

#
# Platform, OS, OS Version
#

if ( $tpl_show_platform || $tpl_show_os || $tpl_show_os_version ) {
	echo '<tr class="print">';

	$t_spacer = 0;

	if ( $tpl_show_platform ) {
		echo '<th class="print-category">', lang_get( 'platform' ), '</th>';
		echo '<td class="print">', $tpl_platform, '</td>';
	} else {
		$t_spacer += 2;
	}

	if ( $tpl_show_os ) {
		echo '<th class="print-category">', lang_get( 'os' ), '</th>';
		echo '<td class="print">', $tpl_os, '</td>';
	} else {
		$t_spacer += 2;
	}

	if ( $tpl_show_os_version ) {
		echo '<th class="print-category">', lang_get( 'os_version' ), '</th>';
		echo '<td class="print">', $tpl_os_version, '</td>';
	} else {
		$t_spacer += 2;
	}

	if ( $t_spacer > 0 ) {
		echo '<td class="print" colspan="', $t_spacer, '">&#160;</td>';
	}

	echo '</tr>';
}

#
# Product Version, Product Build
#

if ( $tpl_show_product_version || $tpl_show_product_build ) {
	echo '<tr class="print">';

	$t_spacer = 2;

	if ( $tpl_show_product_version ) {
		echo '<th class="print-category">', lang_get( 'product_version' ), '</th>';
		echo '<td class="print">', $tpl_product_version, '</td>';
	} else {
		$t_spacer += 2;
	}

	if ( $tpl_show_product_build ) {
		echo '<th class="print-category">', lang_get( 'product_build' ), '</th>';
		echo '<td class="print">', $tpl_product_build, '</td>';
	} else {
		$t_spacer += 2;
	}

	echo '<td class="print" colspan="', $t_spacer, '">&#160;</td>';
	echo '</tr>';
}

#
# Target Version, Fixed in Version
#

if ( $tpl_show_target_version || $tpl_show_fixed_in_version ) {
	echo '<tr>';

	$t_spacer = 2;

	if ( $tpl_show_target_version ) {
		echo '<th class="print-category">', lang_get( 'target_version' ), '</th>';
		echo '<td class="print">', $tpl_target_version, '</td>';
	} else {
		$t_spacer += 2;
	}

	if ( $tpl_show_fixed_in_version ) {
		echo '<th class="print-category">', lang_get( 'fixed_in_version' ), '</th>';
		echo '<td class="print">', $tpl_fixed_in_version, '</td>';
	} else {
		$t_spacer += 2;
	}

	echo '<td class="print" colspan="', $t_spacer, '">&#160;</td>';
	echo '</tr>';
}

#
# Custom Fields
#

$t_related_custom_field_ids = custom_field_get_linked_ids( $tpl_bug->project_id );
foreach( $t_related_custom_field_ids as $t_id ) {
	$t_def = custom_field_get_definition( $t_id );

	echo '<tr class="print">';
	echo '<th class="print-category">', string_display_line( lang_get_defaulted( $t_def['name'] ) ), '</th>';
	echo '<td class="print" colspan="4">';
	print_custom_field_value( $t_def, $t_id, $f_bug_id );
	echo '</td>';
	echo '</tr>';
}       // foreach

echo '<tr><td class="print-spacer" colspan="6"><hr /></td></tr>';

if ( $tpl_show_summary ) {
	echo '<tr class="print">';
	echo '<th class="print-category">', lang_get( 'summary' ), '</th>';
	echo '<td class="print" colspan="5">', $tpl_summary, '</td>';
	echo '</tr>';
}

if ( $tpl_show_description ) {
	echo '<tr class="print">';
	echo '<th class="print-category">', lang_get( 'description' ), '</th>';
	echo '<td class="print" colspan="5">', $tpl_description, '</td>';
	echo '</tr>';
}

if ( $tpl_show_steps_to_reproduce ) {
	echo '<tr class="print">';
	echo '<th class="print-category">', lang_get( 'steps_to_reproduce' ), '</th>';
	echo '<td class="print" colspan="5">', $tpl_steps_to_reproduce, '</td>';
	echo '</tr>';
}

if ( $tpl_show_additional_information ) {
	echo '<tr class="print">';
	echo '<th class="print-category">', lang_get( 'additional_information' ), '</th>';
	echo '<td class="print" colspan="5">', $tpl_additional_information, '</td>';
	echo '</tr>';
}

# Tagging
if ( $tpl_show_tags ) {
	echo "<tr class=\"print\">";
	echo '<th class="print-category">', lang_get( 'tags' ), '</th>';
	echo '<td class="print" colspan="5">';
	tag_display_attached( $f_bug_id );
	echo '</td></tr>';
}

echo "<tr class=\"print\">";
echo "<td class=\"print-category\">" . lang_get( 'bug_relationships' ) . "</td>";
echo "<td class=\"print\" colspan=\"5\">" . relationship_get_summary_html_preview( $f_bug_id ) . "</td></tr>";

if ( $tpl_show_attachments ) {
	echo '<tr class="print">';
	echo '<th class="print-category">', lang_get( 'attached_files' ), '</th>';
	echo '<td class="print" colspan="5">';

	$t_attachments = file_get_visible_attachments( $f_bug_id );
	$t_first_attachment = true;
	$t_path = config_get_global( 'path' );

	foreach ( $t_attachments as $t_attachment  ) {
		if ( $t_first_attachment ) {
			$t_first_attachment = false;
		} else {
			echo '<br />';
		}

		$c_filename = string_display_line( $t_attachment['display_name'] );
		$c_download_url = $t_path . htmlspecialchars( $t_attachment['download_url'] );
		$c_filesize = number_format( $t_attachment['size'] );
		$c_date_added = date( config_get( 'normal_date_format' ), $t_attachment['date_added'] );
		if ( isset( $t_attachment['icon'] ) ) {
			echo '<img src="', $t_attachment['icon']['url'], '" alt="', $t_attachment['icon']['alt'], '" />&#160;';
		}

		echo "$c_filename ($c_filesize) <span class=\"italic\">$c_date_added</span><br />$c_download_url";

		if ( $t_attachment['preview'] && $t_attachment['type'] == 'image' ) {
			echo '<br /><img src="', $t_attachment['download_url'], '" alt="', $t_attachment['alt'], '" /><br />';
		}
	}

	echo '</td></tr>';
}

#
# Issue History
#

if ( $tpl_show_history ) {
	echo '<tr><td class="print-spacer" colspan="6"><hr /></td></tr>';

	echo '<tr><th class="form-title">', lang_get( 'bug_history' ), '</th></tr>';

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
}

echo '</table>';

define( 'PRINT_BUGNOTE_INC_ALLOW', true );
include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'print_bugnote_inc.php' ) ;

last_visited_issue( $f_bug_id );

html_body_end();
html_end();
