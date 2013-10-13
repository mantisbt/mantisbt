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
 * Manage configuration for workflow Config
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses form_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses string_api.php
 * @uses user_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'form_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );

auth_reauthenticate();

html_page_top( lang_get( 'manage_threshold_config' ) );

print_manage_menu( 'adm_permissions_report.php' );
print_manage_config_menu( 'manage_config_work_threshold_page.php' );

$t_user = auth_get_current_user_id();
$t_project_id = helper_get_current_project();
$t_access = user_get_access_level( $t_user, $t_project_id );
$t_show_submit = false;

$t_access_levels = MantisEnum::getAssocArrayIndexedByValues( config_get( 'access_levels_enum_string' ) );

$t_overrides = array();

/**
 * Set overrides
 * @param string $p_config config value
 */
function set_overrides( $p_config ) {
	global $t_overrides;
	if ( !in_array( $p_config, $t_overrides ) ) {
		$t_overrides[] = $p_config;
	}
}

/**
 * Section header
 * @param string $p_section_name section name
 */
function get_section_begin_mcwt( $p_section_name ) {
	global $t_access_levels;

	echo '<div class="form-container">'. "\n";
	echo '<table>';
	echo '<thead>';
	echo '<tr><td class="form-title" colspan="' . ( count( $t_access_levels ) + 2 ) . '">' . $p_section_name . '</td></tr>' . "\n";
	echo '<tr class="row-category2">';
	echo '<th class="form-title" width="40%" rowspan="2">' . lang_get( 'perm_rpt_capability' ) . '</th>';
	echo '<th class="form-title" style="text-align:center"  width="40%" colspan="' . count( $t_access_levels ) . '">' . lang_get( 'access_levels' ) . '</th>';
	echo '<th class="form-title" style="text-align:center" rowspan="2">&#160;' . lang_get( 'alter_level' ) . '&#160;</th>';
	echo '</tr><tr class="row-category2">';
	foreach( $t_access_levels as $t_access_level => $t_access_label ) {
		echo '<th class="form-title" style="text-align:center">&#160;' . MantisEnum::getLabel( lang_get( 'access_levels_enum_string' ), $t_access_level ) . '&#160;</th>';
	}
	echo '</tr>' . "\n";
	echo '</thead>';
	echo '<tbody>';
}

/**
 * Defines the cell's background color and sets the overrides
 * @param string $p_threshold  Config option
 * @param string $p_file       System default value
 * @param string $p_global     All projects value
 * @param string $p_project    Current project value
 * @param bool $p_set_override If true, will define an override if needed
 * @return string HTML tag attribute for background color override
 */
function set_color( $p_threshold, $p_file, $p_global, $p_project, $p_set_override ) {
	global $t_color_project, $t_color_global, $t_project_id;

	$t_color = false;

	# all projects override
	if ( $p_global != $p_file ) {
		$t_color = $t_color_global;
		if ( $p_set_override && ALL_PROJECTS == $t_project_id ) {
			set_overrides( $p_threshold );
		}
	}

	# project overrides
	if ( $p_project != $p_global ) {
		$t_color = $t_color_project;
		if ( $p_set_override && ALL_PROJECTS != $t_project_id ) {
			set_overrides( $p_threshold );
		}

	}

	if( false === $t_color ) {
		return '';
	}

	return ' bgcolor="' . $t_color . '" ';
}

/**
 * Prints selection list or value of who is allowed to change the capability
 * @param string $p_threshold  Capability
 * @param bool   $p_can_change If true, prints a selection list otherwise just display value
 */
function print_who_can_change( $p_threshold, $p_can_change ) {
	static $s_file_access = null;

	if( is_null( $s_file_access ) ) {
		$t_file_access = config_get_global( 'admin_site_threshold' );
	}
	$t_global_access = config_get_access( $p_threshold, null, ALL_PROJECTS );
	$t_project_access = config_get_access( $p_threshold );

	$t_color = set_color( $p_threshold, $t_file_access, $t_global_access, $t_project_access, $p_can_change );

	echo "\t<td $t_color>";
	if ( $p_can_change ) {
		echo '<select name="access_' . $p_threshold . '">';
		print_enum_string_option_list( 'access_levels', $t_project_access );
		echo '</select>';
	} else {
		echo MantisEnum::getLabel( lang_get( 'access_levels_enum_string' ), $t_project_access ) . '&#160;';
	}
	echo "</td>\n";
}

/**
 * Get row
 * @param string $p_caption caption
 * @param string $p_threshold threshold
 * @param bool $p_all_projects_only all projects only
 */
function get_capability_row( $p_caption, $p_threshold, $p_all_projects_only=false ) {
	global $t_user, $t_project_id, $t_show_submit, $t_access_levels;

	$t_file = config_get_global( $p_threshold );
	if ( !is_array( $t_file ) ) {
		$t_file_exp = array();
		foreach( $t_access_levels as $t_access_level => $t_label ) {
			if ( $t_access_level >= $t_file ) {
				$t_file_exp[] = $t_access_level;
			}
		}
	} else {
		$t_file_exp = $t_file;
	}

	$t_global = config_get( $p_threshold, null, null, ALL_PROJECTS );
	if ( !is_array( $t_global ) ) {
		$t_global_exp = array();
		foreach( $t_access_levels as $t_access_level => $t_label ) {
			if ( $t_access_level >= $t_global ) {
				$t_global_exp[] = $t_access_level;
			}
		}
	} else {
		$t_global_exp = $t_global;
	}

	$t_project = config_get( $p_threshold );
	if ( !is_array( $t_project ) ) {
		$t_project_exp = array();
		foreach( $t_access_levels as $t_access_level => $t_label ) {
			if ( $t_access_level >= $t_project ) {
				$t_project_exp[] = $t_access_level;
			}
		}
	} else {
		$t_project_exp = $t_project;
	}

	$t_can_change = access_has_project_level( config_get_access( $p_threshold ), $t_project_id, $t_user )
			  && ( ( ALL_PROJECTS == $t_project_id ) || !$p_all_projects_only );

	echo "<tr>\n";

	# Access levels
	echo "\t<td>" . string_display( $p_caption ) . "</td>\n";
	foreach( $t_access_levels as $t_access_level => $t_access_label ) {
		$t_file = in_array( $t_access_level, $t_file_exp );
		$t_global = in_array( $t_access_level, $t_global_exp );
		$t_project = in_array( $t_access_level, $t_project_exp );

		$t_color = set_color( $p_threshold, $t_file, $t_global, $t_project, $t_can_change );

		if ( $t_can_change ) {
			$t_checked = $t_project ? "checked=\"checked\"" : "";
			$t_value = "<input type=\"checkbox\" name=\"flag_thres_" . $p_threshold . "[]\" value=\"$t_access_level\" $t_checked />";
			$t_show_submit = true;
		} else {
			if ( $t_project ) {
				$t_value = '<img src="images/ok.gif" width="20" height="15" alt="X" title="X" />';
			} else {
				$t_value = '&#160;';
			}
		}
		echo "\t" . '<td class="center"' . $t_color . '>' . $t_value . "</td>\n";
	}

	print_who_can_change( $p_threshold, $t_can_change );

	echo "</tr>\n";
}

/**
 * Get boolean row
 * @param string $p_caption caption
 * @param string $p_threshold threshold
 * @param bool $p_all_projects_only all projects only
 */
function get_capability_boolean( $p_caption, $p_threshold, $p_all_projects_only=false ) {
	global $t_user, $t_project_id, $t_show_submit, $t_access_levels;

	$t_file = config_get_global( $p_threshold );
	$t_global = config_get( $p_threshold, null, null, ALL_PROJECTS );
	$t_project = config_get( $p_threshold );

	$t_can_change = access_has_project_level( config_get_access( $p_threshold ), $t_project_id, $t_user )
			  && ( ( ALL_PROJECTS == $t_project_id ) || !$p_all_projects_only );

	echo "<tr>\n\t<td>" . string_display( $p_caption ) . "</td>\n";

	# Value
	$t_color = set_color( $p_threshold, $t_file, $t_global, $t_project, $t_can_change );
	if ( $t_can_change ) {
		$t_checked = ( ON == config_get( $p_threshold ) ) ? "checked=\"checked\"" : "";
		$t_value = "<input type=\"checkbox\" name=\"flag_" . $p_threshold . "\" value=\"1\" $t_checked />";
		$t_show_submit = true;
	} else {
		if ( ON == config_get( $p_threshold ) ) {
			$t_value = '<img src="images/ok.gif" width="20" height="15" title="X" alt="X" />';
		} else {
			$t_value = '&#160;';
		}
	}
	echo "\t<td $t_color>" . $t_value . "</td>\n\t"
		. '<td class="left" colspan="' . ( count( $t_access_levels ) - 1 ). '"></td>';

	print_who_can_change( $p_threshold, $t_can_change );

	echo "</tr>\n";
}

/**
 * Get enum row
 * @param string $p_caption caption
 * @param string $p_threshold threshold
 * @param string $p_enum enum
 * @param bool $p_all_projects_only all projects only
 */
function get_capability_enum( $p_caption, $p_threshold, $p_enum, $p_all_projects_only=false ) {
	global $t_user, $t_project_id, $t_show_submit, $t_access_levels;

	$t_file = config_get_global( $p_threshold );
	$t_global = config_get( $p_threshold, null, null, ALL_PROJECTS );
	$t_project = config_get( $p_threshold );

	$t_can_change = access_has_project_level( config_get_access( $p_threshold ), $t_project_id, $t_user )
			  && ( ( ALL_PROJECTS == $t_project_id ) || !$p_all_projects_only );

	echo "<tr>\n\t<td>" . string_display( $p_caption ) . "</td>\n";

	# Value
	$t_color = set_color( $p_threshold, $t_file, $t_global, $t_project, $t_can_change );
	echo "\t" . '<td class="left" colspan="3"' . $t_color . '>';
	if ( $t_can_change ) {
		echo '<select name="flag_' . $p_threshold . '">';
		print_enum_string_option_list( $p_enum, config_get( $p_threshold ) );
		echo '</select>';
		$t_show_submit = true;
	} else {
		$t_value = MantisEnum::getLabel( lang_get( $p_enum . '_enum_string' ), config_get( $p_threshold ) ) . '&#160;';
		echo $t_value;
	}
	echo "</td>\n\t" . '<td colspan="' . ( count( $t_access_levels ) - 3 ) . '"></td>' . "\n";

	print_who_can_change( $p_threshold, $t_can_change );

	echo "</tr>\n";
}

function get_section_end() {
	echo '</tbody></table></div><br />' . "\n";
}


$t_color_project = config_get( 'colour_project' );
$t_color_global = config_get( 'colour_global' );

echo "<br /><br />\n";

if ( ALL_PROJECTS == $t_project_id ) {
	$t_project_title = lang_get( 'config_all_projects' );
} else {
	$t_project_title = sprintf( lang_get( 'config_project' ) , string_display( project_get_name( $t_project_id ) ) );
}
echo '<p class="bold">' . $t_project_title . '</p>' . "\n";
echo '<p>' . lang_get( 'colour_coding' ) . '<br />';
if ( ALL_PROJECTS <> $t_project_id ) {
	echo '<span style="background-color:' . $t_color_project . '">' . lang_get( 'colour_project' ) .'</span><br />';
}
echo '<span style="background-color:' . $t_color_global . '">' . lang_get( 'colour_global' ) . '</span></p>';

echo "<form name=\"mail_config_action\" method=\"post\" action=\"manage_config_work_threshold_set.php\">\n";
echo form_security_field( 'manage_config_work_threshold_set' );

# Issues
get_section_begin_mcwt( lang_get( 'issues' ) );
get_capability_row( lang_get( 'report_issue' ), 'report_bug_threshold' );
get_capability_enum( lang_get( 'submit_status' ), 'bug_submit_status', 'status' );
get_capability_row( lang_get( 'update_issue' ), 'update_bug_threshold' );
get_capability_boolean( lang_get( 'allow_reporter_close' ), 'allow_reporter_close' );
get_capability_row( lang_get( 'monitor_issue' ), 'monitor_bug_threshold' );
get_capability_row( lang_get( 'handle_issue' ), 'handle_bug_threshold' );
get_capability_row( lang_get( 'assign_issue' ), 'update_bug_assign_threshold' );
get_capability_row( lang_get( 'move_issue' ), 'move_bug_threshold', true );
get_capability_row( lang_get( 'delete_issue' ), 'delete_bug_threshold' );
get_capability_row( lang_get( 'reopen_issue' ), 'reopen_bug_threshold' );
get_capability_boolean( lang_get( 'allow_reporter_reopen' ), 'allow_reporter_reopen' );
get_capability_enum( lang_get( 'reopen_status' ), 'bug_reopen_status', 'status' );
get_capability_enum( lang_get( 'reopen_resolution' ), 'bug_reopen_resolution', 'resolution' );
get_capability_enum( lang_get( 'resolved_status' ), 'bug_resolved_status_threshold', 'status' );
get_capability_enum( lang_get( 'readonly_status' ), 'bug_readonly_status_threshold', 'status' );
get_capability_row( lang_get( 'update_readonly_issues' ), 'update_readonly_bug_threshold' );
get_capability_row( lang_get( 'update_issue_status' ), 'update_bug_status_threshold' );
get_capability_row( lang_get( 'view_private_issues' ), 'private_bug_threshold' );
get_capability_row( lang_get( 'set_view_status' ), 'set_view_status_threshold' );
get_capability_row( lang_get( 'update_view_status' ), 'change_view_status_threshold' );
get_capability_row( lang_get( 'show_list_of_users_monitoring_issue' ), 'show_monitor_list_threshold' );
get_capability_boolean( lang_get( 'set_status_assigned' ), 'auto_set_status_to_assigned' );
get_capability_enum( lang_get( 'assigned_status' ), 'bug_assigned_status', 'status' );
get_capability_boolean( lang_get( 'limit_access' ), 'limit_reporters', true );
get_section_end();

# Notes
get_section_begin_mcwt( lang_get( 'notes' ) );
get_capability_row( lang_get( 'add_notes' ), 'add_bugnote_threshold' );
get_capability_row( lang_get( 'edit_others_bugnotes' ), 'update_bugnote_threshold' );
get_capability_row( lang_get( 'edit_own_bugnotes' ), 'bugnote_user_edit_threshold' );
get_capability_row( lang_get( 'delete_others_bugnotes' ), 'delete_bugnote_threshold' );
get_capability_row( lang_get( 'delete_own_bugnotes' ), 'bugnote_user_delete_threshold' );
get_capability_row( lang_get( 'view_private_notes' ), 'private_bugnote_threshold' );
get_capability_row( lang_get( 'change_view_state_own_bugnotes' ), 'bugnote_user_change_view_state_threshold' );
get_section_end();

# Others
get_section_begin_mcwt( lang_get('others' ) );
get_capability_row( lang_get( 'view' ) . ' ' . lang_get( 'changelog_link' ), 'view_changelog_threshold' );
get_capability_row( lang_get( 'view' ) . ' ' . lang_get( 'assigned_to' ), 'view_handler_threshold' );
get_capability_row( lang_get( 'view' ) . ' ' . lang_get( 'bug_history' ), 'view_history_threshold' );
get_capability_row( lang_get( 'send_reminders' ), 'bug_reminder_threshold' );
get_capability_row( lang_get( 'receive_reminders' ), 'reminder_receive_threshold' );
get_section_end();


if ( $t_show_submit ) {
	echo "<input type=\"submit\" class=\"button\" value=\"" . lang_get( 'change_configuration' ) . "\" />\n";
}

echo "</form>\n";

if ( $t_show_submit && ( 0 < count( $t_overrides ) ) ) {
	echo "<div class=\"right\"><form name=\"threshold_config_action\" method=\"post\" action=\"manage_config_revert.php\">\n";
	echo form_security_field( 'manage_config_revert' );
	echo "<input name=\"revert\" type=\"hidden\" value=\"" . implode( ',', $t_overrides ) . "\"></input>";
	echo "<input name=\"project\" type=\"hidden\" value=\"$t_project_id\"></input>";
	echo "<input name=\"return\" type=\"hidden\" value=\"" . string_attribute( form_action_self() ) ."\"></input>";
	echo "<input type=\"submit\" class=\"button\" value=\"";
	if ( ALL_PROJECTS == $t_project_id ) {
		echo lang_get( 'revert_to_system' );
	} else {
	echo lang_get( 'revert_to_all_project' );
	}
	echo "\" />\n";
	echo "</form></div>\n";
}

html_page_bottom();
