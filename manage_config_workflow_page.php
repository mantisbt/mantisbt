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
 * Workflow Configuration Page
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses form_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses string_api.php
 * @uses workflow_api.php
 */

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'form_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'string_api.php' );
require_api( 'workflow_api.php' );

auth_reauthenticate();

html_page_top( lang_get( 'manage_workflow_config' ) );

print_manage_menu( 'adm_permissions_report.php' );
print_manage_config_menu( 'manage_config_workflow_page.php' );

# CSS class names for overrides color coding
define( 'COLOR_GLOBAL', 'color-global' );
define( 'COLOR_PROJECT', 'color-project' );

$g_access = current_user_get_access_level();
$t_project = helper_get_current_project();
$g_can_change_workflow = ( $g_access >= config_get_access( 'status_enum_workflow' ) );
$g_can_change_flags = $g_can_change_workflow;
$g_overrides = array();

/**
 * Set overrides
 * @param string $p_config     Configuration value.
 * @param bool   $p_can_change True if user has access level to change config
 * @param string $p_color      CSS class name
 * @return void
 */
function set_overrides( $p_config, $p_can_change, $p_color ) {
	global $g_overrides;

	if( !$p_can_change ) {
		return;
	}

	$t_project = helper_get_current_project();
	if(    $t_project == ALL_PROJECTS && $p_color == COLOR_GLOBAL
		|| $t_project != ALL_PROJECTS && $p_color == COLOR_PROJECT
	) {
		$g_overrides[$p_config] = $p_config;
	}
}

/**
 * Returns a string to define the background color attribute depending
 * on the level where it's overridden
 * @param integer $p_level_file    Config file's access level.
 * @param integer $p_level_global  All projects' access level.
 * @param integer $p_level_project Current project's access level.
 * @return string class name or '' if no override.
 */
function set_color_override( $p_level_file, $p_level_global, $p_level_project ) {
	if( $p_level_project != $p_level_global ) {
		$t_color = COLOR_PROJECT;
	} else if( $p_level_global != $p_level_file ) {
		$t_color = COLOR_GLOBAL;
	} else {
		$t_color = '';
	}

	return $t_color;
}


/**
 * Get the value associated with the specific action and flag.
 * @param integer $p_from_status_id From status id.
 * @param integer $p_to_status_id   To status id.
 * @return string
 */
function show_flag( $p_from_status_id, $p_to_status_id ) {
	global $g_can_change_workflow,
		$g_file_workflow, $g_global_workflow, $g_project_workflow,
		$t_resolved_status, $t_reopen_status, $t_reopen_label;
	if( $p_from_status_id <> $p_to_status_id ) {
		$t_file = isset( $g_file_workflow['exit'][$p_from_status_id][$p_to_status_id] ) ? 1 : 0;
		$t_global = isset( $g_global_workflow['exit'][$p_from_status_id][$p_to_status_id] ) ? 1 : 0;
		$t_project = isset( $g_project_workflow['exit'][$p_from_status_id][$p_to_status_id] ) ? 1 : 0;

		$t_color = set_color_override( $t_file, $t_global, $t_project );
		set_overrides( 'status_enum_workflow', $g_can_change_workflow, $t_color );
		$t_value = '<td class="center ' . $t_color . '">';

		$t_flag = ( 1 == $t_project );

		if( $g_can_change_workflow ) {
			$t_flag_name = $p_from_status_id . ':' . $p_to_status_id;
			$t_set = $t_flag ? 'checked="checked"' : '';
			$t_value .= '<input type="checkbox" name="flag[]" value="' . $t_flag_name . '" ' . $t_set . ' />';
		} else {
			$t_value .= $t_flag ? '<img src="images/ok.gif" width="20" height="15" title="X" alt="X" />' : '&#160;';
		}

		# Add 'reopened' label
		if( $p_from_status_id >= $t_resolved_status && $p_to_status_id == $t_reopen_status ) {
			$t_value .= '<br />(' . $t_reopen_label . ')';
		}
	} else {
		$t_value = '<td>&#160;';
	}

	$t_value .= '</td>';

	return $t_value;
}

/**
 * section header
 * @param string $p_section_name Section name.
 * @return void
 */
function section_begin( $p_section_name ) {
	$t_enum_statuses = MantisEnum::getValues( config_get( 'status_enum_string' ) );
	echo '<div class="form-container">'. "\n";
	echo '<h2>' . $p_section_name . '</h2>' . "\n";
	echo "\t<table>\n";
	echo "\t\t<thead>\n";
	echo "\t\t" . '<tr class="row-category2">' . "\n";
	echo "\t\t\t" . '<th class="form-title width30" rowspan="2">' . lang_get( 'current_status' ) . '</th>'. "\n";
	echo "\t\t\t" . '<th class="form-title" style="text-align:center" colspan="' . ( count( $t_enum_statuses ) + 1 ) . '">'
		. lang_get( 'next_status' ) . '</th>';
	echo "\n\t\t" . '</tr>'. "\n";
	echo "\t\t" . '<tr class="row-category2">' . "\n";

	foreach( $t_enum_statuses as $t_status ) {
		echo "\t\t\t" . '<th class="form-title" style="text-align:center">&#160;'
			. string_no_break( MantisEnum::getLabel( lang_get( 'status_enum_string' ), $t_status ) )
			. '&#160;</th>' ."\n";
	}

	echo "\t\t\t" . '<th class="form-title" style="text-align:center">' . lang_get( 'custom_field_default_value' ) . '</th>' . "\n";
	echo "\t\t" . '</tr>' . "\n";
	echo "\t\t</thead>\n";
	echo "\t\t<tbody>\n";
}

/**
 * Print row
 * @param integer $p_from_status From status.
 * @return void
 */
function capability_row( $p_from_status ) {
	global $g_file_workflow, $g_global_workflow, $g_project_workflow, $g_can_change_workflow;
	$t_enum_status = MantisEnum::getAssocArrayIndexedByValues( config_get( 'status_enum_string' ) );
	echo "\t\t" .'<tr><td>' . string_no_break( MantisEnum::getLabel( lang_get( 'status_enum_string' ), $p_from_status ) ) . '</td>' . "\n";
	foreach ( $t_enum_status as $t_to_status_id => $t_to_status_label ) {
		echo show_flag( $p_from_status, $t_to_status_id );
	}

	$t_file = isset( $g_file_workflow['default'][$p_from_status] ) ? $g_file_workflow['default'][$p_from_status] : 0 ;
	$t_global = isset( $g_global_workflow['default'][$p_from_status] ) ? $g_global_workflow['default'][$p_from_status] : 0 ;
	$t_project = isset( $g_project_workflow['default'][$p_from_status] ) ? $g_project_workflow['default'][$p_from_status] : 0;

	$t_color = set_color_override( $t_file, $t_global, $t_project );
	set_overrides( 'status_enum_workflow', $g_can_change_workflow, $t_color );

	echo "\t\t\t" . '<td class="center ' . $t_color . '">';
	if( $g_can_change_workflow ) {
		echo '<select name="default_' . $p_from_status . '">';
		print_enum_string_option_list( 'status', $t_project );
		echo '</select>';
	} else {
		echo MantisEnum::getLabel( lang_get( 'status_enum_string' ), $t_project );
	}
	echo ' </td>' . "\n";
	echo "\t\t" . '</tr>' . "\n";
}

/**
 * section footer
 * @return void
 */
function section_end() {
	global $g_can_change_workflow;
	echo '</tbody></table>' . "\n";

	echo '<div class="footer">' . "\n";
	if( $g_can_change_workflow ) {
		echo lang_get( 'workflow_change_access_label' ) . "&nbsp;\n";
		echo '<select name="workflow_access">' . "\n";
		print_enum_string_option_list( 'access_levels', config_get_access( 'status_enum_workflow' ) );
		echo "\n" . '</select>' . "\n";
	}
	echo '</div>' . "\n";

	echo '</div><br />' . "\n";
}

/**
 * threshold section begin
 * @param string $p_section_name Section name.
 * @return void
 */
function threshold_begin( $p_section_name ) {
	echo '<div class="form-container">';
	echo '<h2>' . $p_section_name . '</h2>' . "\n";
	echo '<table>';
	echo '<thead>';
	echo "\t" . '<tr class="row-category2">';
	echo "\t\t" . '<th class="form-title width30">' . lang_get( 'threshold' ) . '</th>' . "\n";
	echo "\t\t" . '<th class="form-title" >' . lang_get( 'status_level' ) . '</th>' . "\n";
	echo "\t\t" . '<th class="form-title" >' . lang_get( 'alter_level' ) . '</th></tr>' . "\n";
	echo "\n";
	echo '</thead>';
	echo '<tbody>';
}

/**
 * threshold section row
 * @param string $p_threshold Threshold.
 * @return void
 */
function threshold_row( $p_threshold ) {
	global $g_access, $g_can_change_flags;

	$t_can_change_threshold = ( $g_access >= config_get_access( $p_threshold ) );

	$t_file = config_get_global( $p_threshold );
	$t_global = config_get( $p_threshold, null, ALL_USERS, ALL_PROJECTS );
	$t_project = config_get( $p_threshold );
	$t_color = set_color_override( $t_file, $t_global, $t_project );
	set_overrides( $p_threshold, $t_can_change_threshold, $t_color );

	$t_file_access = config_get_global( 'admin_site_threshold' );
	$t_global_access = config_get_access( $p_threshold, ALL_USERS, ALL_PROJECTS);
	$t_project_access = config_get_access( $p_threshold );
	$t_color_access = set_color_override( $t_file_access, $t_global_access, $t_project_access );
	set_overrides( $p_threshold, $t_can_change_threshold, $t_color_access );

	echo '<tr><td>' . lang_get( 'desc_' . $p_threshold ) . '</td>' . "\n";
	if( $t_can_change_threshold ) {
		echo '<td class="center ' . $t_color . '"><select name="threshold_' . $p_threshold . '">';
		print_enum_string_option_list( 'status', $t_project );
		echo '</select> </td>' . "\n";
		echo '<td class="' . $t_color_access . '"><select name="access_' . $p_threshold . '">';
		print_enum_string_option_list( 'access_levels', $t_project_access );
		echo '</select> </td>' . "\n";
		$g_can_change_flags = true;
	} else {
		echo '<td class="' . $t_color . '">' . MantisEnum::getLabel( lang_get( 'status_enum_string' ), $t_project ) . '&#160;</td>' . "\n";
		echo '<td>' . MantisEnum::getLabel( lang_get( 'access_levels_enum_string' ), config_get_access( $p_threshold ) ) . '&#160;</td>' . "\n";
	}

	echo '</tr>' . "\n";
}

/**
 * threshold section end
 * @return void
 */
function threshold_end() {
	echo '</tbody></table></div><br />' . "\n";
}

/**
 * access begin
 * @param string $p_section_name Section name.
 * @return void
 */
function access_begin( $p_section_name ) {
	echo '<div class="form-container">' . "\n";
	echo '<h2>' . $p_section_name . '</h2>' . "\n";
	echo '<table>';
	echo '<thead>' . "\n";
	echo "\t\t" . '<tr class="row-category2"><th class="form-title" colspan="2">' . lang_get( 'access_change' ) . '</th></tr>' . "\n";
	echo '</thead>' . "\n";
	echo '<tbody>' . "\n";
}

/**
 * access row
 * @return void
 */
function access_row() {
	global $g_access, $g_can_change_flags;

	$t_enum_status = MantisEnum::getAssocArrayIndexedByValues( config_get( 'status_enum_string' ) );

	$t_file_new = config_get_global( 'report_bug_threshold' );
	$t_global_new = config_get( 'report_bug_threshold', null, ALL_USERS, ALL_PROJECTS );
	$t_report_bug_threshold = config_get( 'report_bug_threshold' );
	$t_project_new = access_threshold_min_level( $t_report_bug_threshold );

	$t_file_set = config_get_global( 'set_status_threshold' );
	$t_global_set = config_get( 'set_status_threshold', null, ALL_USERS, ALL_PROJECTS );
	$t_project_set = config_get( 'set_status_threshold' );

	$t_submit_status = config_get( 'bug_submit_status' );

	# Print the table rows
	foreach( $t_enum_status as $t_status => $t_status_label ) {
		echo "\t\t" . '<tr><td class="width30">'
			. string_no_break( MantisEnum::getLabel( lang_get( 'status_enum_string' ), $t_status ) ) . '</td>' . "\n";

		if( $t_status == $t_submit_status ) {
			# 'NEW' status
			$t_level_project = $t_project_new;

			# If report_bug_threshold is an array (instead of an integer value), the input is not editable
			# because it must be configured in manage_config_work_threshold_page.
			$t_can_change = ( $g_access >= config_get_access( 'report_bug_threshold' ) )
					&& !is_array( $t_report_bug_threshold );
			$t_color = set_color_override( $t_file_new, $t_global_new, $t_report_bug_threshold );
			set_overrides( 'report_bug_threshold', $t_can_change, $t_color );
		} else {
			# Other statuses

			# File level: fallback if set_status_threshold is not defined
			if( isset( $t_file_set[$t_status] ) ) {
				$t_level_file = $t_file_set[$t_status];
			} else {
				$t_level_file = config_get_global( 'update_bug_status_threshold' );
			}

			$t_level_global  = isset( $t_global_set[$t_status] ) ? $t_global_set[$t_status] : $t_level_file;
			$t_level_project = isset( $t_project_set[$t_status] ) ? $t_project_set[$t_status] : $t_level_global;

			$t_can_change = ( $g_access >= config_get_access( 'set_status_threshold' ) );
			$t_color = set_color_override( $t_level_file, $t_level_global, $t_level_project );
			set_overrides( 'set_status_threshold', $t_can_change, $t_color );
		}

		if( $t_can_change ) {
			echo '<td class="center ' . $t_color . '"><select name="access_change_' . $t_status . '">' . "\n";
			print_enum_string_option_list( 'access_levels', $t_level_project );
			echo '</select> </td>' . "\n";
			$g_can_change_flags = true;
		} else {
			echo '<td class="center ' . $t_color . '">'
				. MantisEnum::getLabel( lang_get( 'access_levels_enum_string' ), $t_level_project )
				. '</td>' . "\n";
		}

		echo '</tr>' . "\n";
	}
} # end function access_row

/**
 * access section end
 * @return void
 */
function access_end() {
	global $g_access;

	echo '</tbody></table>' . "\n";

	echo '<div class="footer">' . "\n";
	if( $g_access >= config_get_access( 'set_status_threshold' ) ) {
		echo lang_get( 'access_change_access_label' ) . "&nbsp;\n";
		echo '<select name="status_access">' . "\n\t\t";
		print_enum_string_option_list( 'access_levels', config_get_access( 'set_status_threshold' ) );
		echo "\n" . '</select>' . "\n";
	}
	echo '</div>' . "\n";

	echo '</div>' . "\n";
	echo '<br />' . "\n\n";
}

echo '<br /><br />';

# count arcs in and out of each status
$t_enum_status = config_get( 'status_enum_string' );
$t_status_arr  = MantisEnum::getAssocArrayIndexedByValues( $t_enum_status );

$t_extra_enum_status = '0:non-existent,' . $t_enum_status;
$t_lang_enum_status = '0:' . lang_get( 'non_existent' ) . ',' . lang_get( 'status_enum_string' );
$t_all_status = explode( ',', $t_extra_enum_status );

# gather all versions of the workflow
$g_file_workflow = workflow_parse( config_get_global( 'status_enum_workflow' ) );
$g_global_workflow = workflow_parse( config_get( 'status_enum_workflow', null, ALL_USERS, ALL_PROJECTS ) );
$g_project_workflow = workflow_parse( config_get( 'status_enum_workflow', null, ALL_USERS, $t_project ) );

# validate the project workflow
$t_validation_result = '';
foreach( $t_status_arr as $t_status => $t_label ) {
	if( isset( $g_project_workflow['exit'][$t_status][$t_status] ) ) {
		$t_validation_result .= '<tr><td>'
						. MantisEnum::getLabel( $t_lang_enum_status, $t_status )
						. '</td><td bgcolor="#FFED4F">' . lang_get( 'superfluous' ) . '</td></tr>';
	}
}

# check for entry == 0 without exit == 0, unreachable state
foreach( $t_status_arr as $t_status => $t_status_label ) {
	if( ( 0 == count( $g_project_workflow['entry'][$t_status] ) ) && ( 0 < count( $g_project_workflow['exit'][$t_status] ) ) ) {
		$t_validation_result .= '<tr><td>'
						. MantisEnum::getLabel( $t_lang_enum_status, $t_status )
						. '</td><td bgcolor="#FF0088">' . lang_get( 'unreachable' ) . '</td></tr>';
	}
}

# check for exit == 0 without entry == 0, unleaveable state
foreach( $t_status_arr as $t_status => $t_status_label ) {
	if( ( 0 == count( $g_project_workflow['exit'][$t_status] ) ) && ( 0 < count( $g_project_workflow['entry'][$t_status] ) ) ) {
		$t_validation_result .= '<tr><td>'
						. MantisEnum::getLabel( $t_lang_enum_status, $t_status )
						. '</td><td bgcolor="#FF0088">' . lang_get( 'no_exit' ) . '</td></tr>';
	}
}

# check for exit == 0 and entry == 0, isolated state
foreach ( $t_status_arr as $t_status => $t_status_label ) {
	if( ( 0 == count( $g_project_workflow['exit'][$t_status] ) ) && ( 0 == count( $g_project_workflow['entry'][$t_status] ) ) ) {
		$t_validation_result .= '<tr><td>'
						. MantisEnum::getLabel( $t_lang_enum_status, $t_status )
						. '</td><td bgcolor="#FF0088">' . lang_get( 'unreachable' ) . '<br />' . lang_get( 'no_exit' ) . '</td></tr>';
	}
}

echo '<form id="workflow_config_action" method="post" action="manage_config_workflow_set.php">' . "\n";
echo '<fieldset>';
echo form_security_field( 'manage_config_workflow_set' );
echo '</fieldset>';

if( ALL_PROJECTS == $t_project ) {
	$t_project_title = lang_get( 'config_all_projects' );
} else {
	$t_project_title = sprintf( lang_get( 'config_project' ), string_display( project_get_name( $t_project ) ) );
}
echo '<p class="bold">' . $t_project_title . '</p>' . "\n";
echo '<p>' . lang_get( 'colour_coding' ) . '<br />';
if( ALL_PROJECTS <> $t_project ) {
	echo '<span class="' . COLOR_PROJECT . '">' . lang_get( 'colour_project' ) .'</span><br />';
}
echo '<span class="' . COLOR_GLOBAL . '">' . lang_get( 'colour_global' ) . '</span></p>';

# show the settings used to derive the table
threshold_begin( lang_get( 'workflow_thresholds' ) );
if( !is_array( config_get( 'bug_submit_status' ) ) ) {
	threshold_row( 'bug_submit_status' );
}
threshold_row( 'bug_resolved_status_threshold' );
threshold_row( 'bug_reopen_status' );
threshold_end();

if( '' <> $t_validation_result ) {
	echo '<table class="width100">';
	echo '<tr><td class="form-title" colspan="3">' . lang_get( 'validation' ) . '</td></tr>' . "\n";
	echo '<tr><td class="form-title width30">' . lang_get( 'status' ) . '</td>';
	echo '<td class="form-title" >' . lang_get( 'comment' ) . '</td></tr>';
	echo "\n";
	echo $t_validation_result;
	echo '</table><br /><br />';
}

# Initialization for 'reopened' label handling
$t_resolved_status = config_get( 'bug_resolved_status_threshold' );
$t_reopen_status = config_get( 'bug_reopen_status' );
$t_reopen_label = MantisEnum::getLabel( lang_get( 'resolution_enum_string' ), config_get( 'bug_reopen_resolution' ) );

# display the graph as a matrix
section_begin( lang_get( 'workflow' ) );
foreach ( $t_status_arr as $t_from_status => $t_from_label ) {
	capability_row( $t_from_status );
}
section_end();

# display the access levels required to move an issue
echo "\n\n";
access_begin( lang_get( 'access_levels' ) );
access_row();
access_end();

if( $g_can_change_flags ) {
	echo '<div class="center">' . "\n";
	echo '<input type="submit" class="button" value="' . lang_get( 'change_configuration' ) . '" />' . "\n";
	echo '</div>' . "\n";

	echo '</form>' . "\n";

	if( 0 < count( $g_overrides ) ) {
		echo '<div class="form-container">';
		echo '<div class="submit-button">' . "\n";
		echo '<form id="mail_config_action" method="post" action="manage_config_revert.php">' ."\n";
		echo '<fieldset>' . "\n";
		echo form_security_field( 'manage_config_revert' );
		echo '<input name="revert" type="hidden" value="' . implode( ',', $g_overrides ) . '" />';
		echo '<input name="project" type="hidden" value="' . $t_project . '" />';
		echo '<input name="return" type="hidden" value="' . string_attribute( form_action_self() ) .'" />';
		echo '<input type="submit" class="button" value="';
		if( ALL_PROJECTS == $t_project ) {
			echo lang_get( 'revert_to_system' );
		} else {
			echo lang_get( 'revert_to_all_project' );
		}
		echo '" />' . "\n";
		echo '</fieldset>' . "\n";
		echo '</form>' . "\n";
		echo '</div></div>' . "\n";
	}


} else {
	echo '</form>' . "\n";
}

html_page_bottom();
