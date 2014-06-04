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

$t_access = current_user_get_access_level();
$t_project = helper_get_current_project();
$t_can_change_workflow = $t_access >= config_get_access( 'status_enum_workflow' );
$t_can_change_flags = $t_can_change_workflow;
$t_overrides = array();

/**
 * Set overrides
 * @param string $p_config config value
 */
function set_overrides( $p_config ) {
	global $t_overrides;
	if( !in_array( $p_config, $t_overrides ) ) {
		$t_overrides[] = $p_config;
	}
}

/**
 * Returns a string to define the background color attribute depending
 * on the level where it's overridden
 * @param int $p_level_file config file's access level
 * @param int $p_level_global all projects' access level
 * @param int $p_level_project current project's access level
 * @return string bgcolor attribute, or '' if no color
 */
function set_colour_override( $p_level_file, $p_level_global, $p_level_project ) {
	global $t_colour_global, $t_colour_project;

	if( $p_level_project != $p_level_global ) {
		$t_colour = $t_colour_project;
	} else if( $p_level_global != $p_level_file ) {
		$t_colour = $t_colour_global;
	} else {
		return '';
	}

	return ' bgcolor="' . $t_colour . '" ';
}


/**
 * Get the value associated with the specific action and flag.
 * @param int $p_from_status_id from status id
 * @param int $p_to_status_id to status id
 * @return string
 */
function show_flag( $p_from_status_id, $p_to_status_id ) {
	global $t_can_change_workflow, $t_overrides,
		$t_file_workflow, $t_global_workflow, $t_project_workflow,
		$t_resolved_status, $t_reopen_status, $t_reopen_label;
	if( $p_from_status_id <> $p_to_status_id ) {
		$t_file = isset( $t_file_workflow['exit'][$p_from_status_id][$p_to_status_id] ) ? 1 : 0 ;
		$t_global = isset( $t_global_workflow['exit'][$p_from_status_id][$p_to_status_id] ) ? 1 : 0 ;
		$t_project = isset( $t_project_workflow['exit'][$p_from_status_id][$p_to_status_id] ) ? 1 : 0;

		$t_colour = set_colour_override( $t_file, $t_global, $t_project );
		if( $t_can_change_workflow && $t_colour != '' ) {
			set_overrides( 'status_enum_workflow' );
		}
		$t_value = '<td class="center"' . $t_colour . '>';

		$t_flag = ( 1 == $t_project );

		if( $t_can_change_workflow ) {
			$t_flag_name = $p_from_status_id . ':' . $p_to_status_id;
			$t_set = $t_flag ? "checked=\"checked\"" : "";
			$t_value .= "<input type=\"checkbox\" name=\"flag[]\" value=\"$t_flag_name\" $t_set />";
		} else {
			$t_value .= $t_flag ? '<img src="images/ok.gif" width="20" height="15" title="X" alt="X" />' : '&#160;';
		}

		# Add 'reopened' label
		if( $p_from_status_id >= $t_resolved_status && $p_to_status_id == $t_reopen_status ) {
			$t_value .= "<br />($t_reopen_label)";
		}
	} else {
		$t_value = '<td>&#160;';
	}

	$t_value .= '</td>';

	return $t_value;
}

/**
 * section header
 * @param string $p_section_name section name
 */
function section_begin( $p_section_name ) {
	$t_enum_statuses = MantisEnum::getValues( config_get( 'status_enum_string' ) );
	echo '<div class="form-container">'. "\n";
	echo "\t<table>\n";
	echo "\t\t<thead>\n";
	echo "\t\t" . '<tr>' . "\n\t\t\t" . '<td class="form-title-caps" colspan="' . ( count( $t_enum_statuses ) + 2 ) . '">'
		. $p_section_name . '</td>' . "\n\t\t" . '</tr>' . "\n";
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
 * @param int $p_from_status from status
 */
function capability_row( $p_from_status ) {
	global $t_file_workflow, $t_global_workflow, $t_project_workflow, $t_can_change_workflow;
	$t_enum_status = MantisEnum::getAssocArrayIndexedByValues( config_get( 'status_enum_string' ) );
	echo "\t\t" .'<tr><td>' . string_no_break( MantisEnum::getLabel( lang_get( 'status_enum_string' ), $p_from_status ) ) . '</td>' . "\n";
	foreach ( $t_enum_status as $t_to_status_id => $t_to_status_label ) {
		echo show_flag( $p_from_status, $t_to_status_id );
	}

	$t_file = isset( $t_file_workflow['default'][$p_from_status] ) ? $t_file_workflow['default'][$p_from_status] : 0 ;
	$t_global = isset( $t_global_workflow['default'][$p_from_status] ) ? $t_global_workflow['default'][$p_from_status] : 0 ;
	$t_project = isset( $t_project_workflow['default'][$p_from_status] ) ? $t_project_workflow['default'][$p_from_status] : 0;

	$t_colour = set_colour_override( $t_file, $t_global, $t_project );
	if( $t_can_change_workflow && $t_colour != '' ) {
		set_overrides( 'status_enum_workflow' );
	}
	echo "\t\t\t" . '<td class="center"' . $t_colour . '>';
	if( $t_can_change_workflow ) {
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
 */
function section_end() {
	echo '</tbody></table></div><br />' . "\n";
}

/**
 * threshold section begin
 * @param string $p_section_name section name
 */
function threshold_begin( $p_section_name ) {
	echo '<div class="form-container">';
	echo '<table>';
	echo '<thead>';
	echo "\t" . '<tr><td class="form-title" colspan="3">' . $p_section_name . '</td></tr>' . "\n";
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
 * @param string $p_threshold threshold
 */
function threshold_row( $p_threshold ) {
	global $t_access, $t_can_change_flags;

	$t_file = config_get_global( $p_threshold );
	$t_global = config_get( $p_threshold, null, null, ALL_PROJECTS );
	$t_project = config_get( $p_threshold );
	$t_can_change_threshold = $t_access >= config_get_access( $p_threshold );

	$t_colour = set_colour_override( $t_file, $t_global, $t_project );
	if( $t_can_change_threshold && $t_colour != '' ) {
		set_overrides( $p_threshold );
	}

	echo '<tr><td>' . lang_get( 'desc_' . $p_threshold ) . '</td>' . "\n";
	if( $t_can_change_threshold ) {
		echo '<td' . $t_colour . '><select name="threshold_' . $p_threshold . '">';
		print_enum_string_option_list( 'status', $t_project );
		echo '</select> </td>' . "\n";
		echo '<td><select name="access_' . $p_threshold . '">';
		print_enum_string_option_list( 'access_levels', config_get_access( $p_threshold ) );
		echo '</select> </td>' . "\n";
		$t_can_change_flags = true;
	} else {
		echo '<td' . $t_colour . '>' . MantisEnum::getLabel( lang_get( 'status_enum_string' ), $t_project ) . '&#160;</td>' . "\n";
		echo '<td>' . MantisEnum::getLabel( lang_get( 'access_levels_enum_string' ), config_get_access( $p_threshold ) ) . '&#160;</td>' . "\n";
	}

	echo '</tr>' . "\n";
}

/**
 * threshold section end
 */
function threshold_end() {
	echo '</tbody></table></div><br />' . "\n";
}

/**
 * access begin
 * @param string $p_section_name section name
 */
function access_begin( $p_section_name ) {
	echo '<div class="form-container">';
	echo '<table>';
	echo '<thead>';
	echo "\t\t" . '<tr><td class="form-title" colspan="2">' . $p_section_name . '</td></tr>' . "\n";
	echo "\t\t" . '<tr class="row-category2"><th class="form-title" colspan="2">' . lang_get( 'access_change' ) . '</th></tr>' . "\n";
	echo '</thead>';
	echo '<tbody>';
}

/**
 * access row
 */
function access_row() {
	global $t_access, $t_can_change_flags;

	$t_enum_status = MantisEnum::getAssocArrayIndexedByValues( config_get( 'status_enum_string' ) );

	$t_file_new = config_get_global( 'report_bug_threshold' );
	$t_global_new = config_get( 'report_bug_threshold', null, null, ALL_PROJECTS );
	$t_project_new = config_get( 'report_bug_threshold' );

	$t_file_set = config_get_global( 'set_status_threshold' );
	$t_global_set = config_get( 'set_status_threshold', null, null, ALL_PROJECTS );
	$t_project_set = config_get( 'set_status_threshold' );

	$t_submit_status = config_get( 'bug_submit_status' );

	# Print the table rows
	foreach( $t_enum_status as $t_status => $t_status_label ) {

		echo "\t\t" . '<tr><td class="width30">'
			. string_no_break( MantisEnum::getLabel( lang_get( 'status_enum_string' ), $t_status ) ) . '</td>' . "\n";

		if( $t_status == $t_submit_status ) {
			# 'NEW' status
			$t_level_project = $t_project_new;

			$t_can_change = ( $t_access >= config_get_access( 'report_bug_threshold' ) );
			$t_colour = set_colour_override( $t_file_new, $t_global_new, $t_project_new );
			if( $t_can_change  && $t_colour != '' ) {
				set_overrides( 'report_bug_threshold' );
			}
		} else {
			# Other statuses

			# File level: fallback if set_status_threshold is not defined
			if( isset( $t_file_set[$t_status] ) ) {
				$t_level_file = $t_file_set[$t_status];
			} else {
				$t_level_file = config_get_global('update_bug_status_threshold');
			}

			$t_level_global  = isset( $t_global_set[$t_status] ) ? $t_global_set[$t_status] : $t_level_file;
			$t_level_project = isset( $t_project_set[$t_status] ) ? $t_project_set[$t_status] : $t_level_global;

			$t_can_change = ( $t_access >= config_get_access( 'set_status_threshold' ) );
			$t_colour = set_colour_override( $t_level_file, $t_level_global, $t_level_project );
			if( $t_can_change  && $t_colour != '' ) {
				set_overrides( 'set_status_threshold' );
			}
		}

		if( $t_can_change ) {
			echo '<td' . $t_colour . '><select name="access_change_' . $t_status . '">' . "\n";
			print_enum_string_option_list( 'access_levels', $t_level_project );
			echo '</select> </td>' . "\n";
			$t_can_change_flags = true;
		} else {
			echo '<td class="center"' . $t_colour . '>'
				. MantisEnum::getLabel( lang_get( 'access_levels_enum_string' ), $t_level_project )
				. '</td>' . "\n";
		}

		echo '</tr>' . "\n";
	}
} # end function access_row

/**
 * access section end
 */
function access_end() {
	echo '</tbody></table></div><br />' . "\n";
}

echo '<br /><br />';

# count arcs in and out of each status
$t_enum_status = config_get( 'status_enum_string' );
$t_status_arr  = MantisEnum::getAssocArrayIndexedByValues( $t_enum_status );

$t_extra_enum_status = '0:non-existent,' . $t_enum_status;
$t_lang_enum_status = '0:' . lang_get( 'non_existent' ) . ',' . lang_get( 'status_enum_string' );
$t_all_status = explode( ',', $t_extra_enum_status);

# gather all versions of the workflow
$t_file_workflow = workflow_parse( config_get_global( 'status_enum_workflow' ) );
$t_global_workflow = workflow_parse( config_get( 'status_enum_workflow', null, null, ALL_PROJECTS ) );
$t_project_workflow = workflow_parse( config_get( 'status_enum_workflow' ) );

# validate the project workflow
$t_validation_result = '';
foreach ( $t_status_arr as $t_status => $t_label ) {
	if( isset( $t_project_workflow['exit'][$t_status][$t_status] ) ) {
		$t_validation_result .= '<tr><td>'
						. MantisEnum::getLabel( $t_lang_enum_status, $t_status )
						. '</td><td bgcolor="#FFED4F">' . lang_get( 'superfluous' ) . '</td></tr>';
	}
}

# check for entry == 0 without exit == 0, unreachable state
foreach ( $t_status_arr as $t_status => $t_status_label) {
	if( ( 0 == count( $t_project_workflow['entry'][$t_status] ) ) && ( 0 < count( $t_project_workflow['exit'][$t_status] ) ) ){
		$t_validation_result .= '<tr><td>'
						. MantisEnum::getLabel( $t_lang_enum_status, $t_status )
						. '</td><td bgcolor="#FF0088">' . lang_get( 'unreachable' ) . '</td></tr>';
	}
}

# check for exit == 0 without entry == 0, unleaveable state
foreach ( $t_status_arr as $t_status => $t_status_label ) {
	if( ( 0 == count( $t_project_workflow['exit'][$t_status] ) ) && ( 0 < count( $t_project_workflow['entry'][$t_status] ) ) ){
		$t_validation_result .= '<tr><td>'
						. MantisEnum::getLabel( $t_lang_enum_status, $t_status )
						. '</td><td bgcolor="#FF0088">' . lang_get( 'no_exit' ) . '</td></tr>';
	}
}

# check for exit == 0 and entry == 0, isolated state
foreach ( $t_status_arr as $t_status => $t_status_label ) {
	if( ( 0 == count( $t_project_workflow['exit'][$t_status] ) ) && ( 0 == count( $t_project_workflow['entry'][$t_status] ) ) ){
		$t_validation_result .= '<tr><td>'
						. MantisEnum::getLabel( $t_lang_enum_status, $t_status )
						. '</td><td bgcolor="#FF0088">' . lang_get( 'unreachable' ) . '<br />' . lang_get( 'no_exit' ) . '</td></tr>';
	}
}

$t_colour_project = config_get( 'colour_project');
$t_colour_global = config_get( 'colour_global');

echo "<form name=\"workflow_config_action\" method=\"post\" action=\"manage_config_workflow_set.php\">\n";
echo form_security_field( 'manage_config_workflow_set' );

if( ALL_PROJECTS == $t_project ) {
	$t_project_title = lang_get( 'config_all_projects' );
} else {
	$t_project_title = sprintf( lang_get( 'config_project' ) , string_display( project_get_name( $t_project ) ) );
}
echo '<p class="bold">' . $t_project_title . '</p>' . "\n";
echo '<p>' . lang_get( 'colour_coding' ) . '<br />';
if( ALL_PROJECTS <> $t_project ) {
	echo '<span style="background-color:' . $t_colour_project . '">' . lang_get( 'colour_project' ) .'</span><br />';
}
echo '<span style="background-color:' . $t_colour_global . '">' . lang_get( 'colour_global' ) . '</span></p>';

# show the settings used to derive the table
threshold_begin( lang_get( 'workflow_thresholds' ) );
if( !is_array( config_get( 'bug_submit_status' ) ) ) {
	threshold_row( 'bug_submit_status' );
}
threshold_row( 'bug_resolved_status_threshold' );
threshold_row( 'bug_reopen_status' );
threshold_end();
echo '<br />';

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
foreach ( $t_status_arr as $t_from_status => $t_from_label) {
	capability_row( $t_from_status );
}
section_end();

if( $t_can_change_workflow ) {
	echo '<p>' . lang_get( 'workflow_change_access_label' );
	echo '<select name="workflow_access">';
	print_enum_string_option_list( 'access_levels', config_get_access( 'status_enum_workflow' ) );
	echo '</select> </p><br />';
}

# display the access levels required to move an issue
access_begin( lang_get( 'access_levels' ) );
access_row();
access_end();

if( $t_access >= config_get_access( 'set_status_threshold' ) ) {
	echo '<p>' . lang_get( 'access_change_access_label' );
	echo '<select name="status_access">';
	print_enum_string_option_list( 'access_levels', config_get_access( 'set_status_threshold' ) );
	echo '</select> </p><br />';
}

if( $t_can_change_flags ) {
	echo "<input type=\"submit\" class=\"button\" value=\"" . lang_get( 'change_configuration' ) . "\" />\n";
	echo "</form>\n";

	if( 0 < count( $t_overrides ) ) {
		echo "<div class=\"right\"><form name=\"mail_config_action\" method=\"post\" action=\"manage_config_revert.php\">\n";
		echo form_security_field( 'manage_config_revert' );
		echo "<input name=\"revert\" type=\"hidden\" value=\"" . implode( ',', $t_overrides ) . "\"></input>";
		echo "<input name=\"project\" type=\"hidden\" value=\"$t_project\"></input>";
		echo "<input name=\"return\" type=\"hidden\" value=\"" . string_attribute( form_action_self() ) ."\"></input>";
		echo "<input type=\"submit\" class=\"button\" value=\"";
		if( ALL_PROJECTS == $t_project ) {
			echo lang_get( 'revert_to_system' );
		} else {
			echo lang_get( 'revert_to_all_project' );
		}
		echo "\" />\n";
		echo "</form></div>\n";
	}

} else {
	echo "</form>\n";
}

html_page_bottom();
