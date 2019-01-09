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

layout_page_header( lang_get( 'manage_threshold_config' ) );

layout_page_begin( 'manage_overview_page.php' );

print_manage_menu( PAGE_CONFIG_DEFAULT );
print_manage_config_menu( 'manage_config_work_threshold_page.php' );

$g_user = auth_get_current_user_id();
$g_project_id = helper_get_current_project();
$t_show_submit = false;

$g_access_levels = MantisEnum::getAssocArrayIndexedByValues( config_get( 'access_levels_enum_string' ) );
$g_overrides = array();

/**
 * Set overrides
 * @param string $p_config Configuration value.
 * @return void
 */
function set_overrides( $p_config ) {
	global $g_overrides;
	if( !in_array( $p_config, $g_overrides ) ) {
		$g_overrides[] = $p_config;
	}
}

/**
 * Section header
 * @param string $p_section_name Section name.
 * @return void
 */
function get_section_begin_mcwt( $p_section_name ) {
	global $g_access_levels;

	echo '<div class="space-10"></div>';
	echo '<div class="widget-box widget-color-blue2">';
	echo '   <div class="widget-header widget-header-small">';
	echo '        <h4 class="widget-title lighter uppercase">';
	echo '            <i class="ace-icon fa fa-sliders"></i>';
	echo $p_section_name;
	echo '       </h4>';
	echo '   </div>';
	echo '   <div class="widget-body">';
	echo '   <div class="widget-main no-padding">';
	echo '       <div class="table-responsive">';
	echo '<table class="table table-striped table-bordered table-condensed">';
	echo '<thead>';
	echo '<tr>';
	echo '<th class="bold" width="40%" rowspan="2">' . lang_get( 'perm_rpt_capability' ) . '</th>';
	echo '<th class="bold" style="text-align:center"  width="40%" colspan="' . count( $g_access_levels ) . '">' . lang_get( 'allowed_access_levels' ) . '</th>';
	echo '<th class="bold" style="text-align:center" rowspan="2">&#160;' . lang_get( 'alter_level' ) . '&#160;</th>';
	echo '</tr><tr>';
	foreach( $g_access_levels as $t_access_level => $t_access_label ) {
		echo '<th class="bold" style="text-align:center">&#160;' . MantisEnum::getLabel( lang_get( 'access_levels_enum_string' ), $t_access_level ) . '&#160;</th>';
	}
	echo '</tr>' . "\n";
	echo '</thead>';
	echo '<tbody>';
}

/**
 * Defines the cell's background color and sets the overrides
 * @param string  $p_threshold    Configuration option.
 * @param string  $p_file         System default value.
 * @param string  $p_global       All projects value.
 * @param string  $p_project      Current project value.
 * @param boolean $p_set_override If true, will define an override if needed.
 * @return string HTML tag attribute for background color override
 */
function set_color( $p_threshold, $p_file, $p_global, $p_project, $p_set_override ) {
	global $g_project_id;

	$t_color = '';

	# all projects override
	if( $p_global != $p_file ) {
		$t_color = 'color-global';
		if( $p_set_override && ALL_PROJECTS == $g_project_id ) {
			set_overrides( $p_threshold );
		}
	}

	# project overrides
	if( $p_project != $p_global ) {
		$t_color = 'color-project';
		if( $p_set_override && ALL_PROJECTS != $g_project_id ) {
			set_overrides( $p_threshold );
		}
	}

	return $t_color;
}

/**
 * Prints selection list or value of who is allowed to change the capability
 * @param string  $p_threshold  Capability.
 * @param boolean $p_can_change If true, prints a selection list otherwise just display value.
 * @return void
 */
function print_who_can_change( $p_threshold, $p_can_change ) {
	static $s_file_access = null;

	if( is_null( $s_file_access ) ) {
		$t_file_access = config_get_global( 'admin_site_threshold' );
	}
	$t_global_access = config_get_access( $p_threshold, ALL_USERS, ALL_PROJECTS );
	$t_project_access = config_get_access( $p_threshold );

	$t_color = set_color( $p_threshold, $t_file_access, $t_global_access, $t_project_access, $p_can_change );

	echo '<td class="' . $t_color . '">';
	if( $p_can_change ) {
		echo '<select name="access_' . $p_threshold . '" class="input-sm">';
		print_enum_string_option_list( 'access_levels', $t_project_access );
		echo '</select>';
	} else {
		echo MantisEnum::getLabel( lang_get( 'access_levels_enum_string' ), $t_project_access ) . '&#160;';
	}
	echo "</td>\n";
}

/**
 * Get row
 * @param string  $p_caption           Caption.
 * @param string  $p_threshold         Threshold.
 * @param boolean $p_all_projects_only All projects only.
 * @return void
 */
function get_capability_row( $p_caption, $p_threshold, $p_all_projects_only = false ) {
	global $g_user, $g_project_id, $t_show_submit, $g_access_levels;

	$t_file = config_get_global( $p_threshold );
	if( !is_array( $t_file ) ) {
		$t_file_exp = array();
		foreach( $g_access_levels as $t_access_level => $t_label ) {
			if( $t_access_level >= $t_file ) {
				$t_file_exp[] = $t_access_level;
			}
		}
	} else {
		$t_file_exp = $t_file;
	}

	$t_global = config_get( $p_threshold, null, ALL_USERS, ALL_PROJECTS );
	if( !is_array( $t_global ) ) {
		$t_global_exp = array();
		foreach( $g_access_levels as $t_access_level => $t_label ) {
			if( $t_access_level >= $t_global ) {
				$t_global_exp[] = $t_access_level;
			}
		}
	} else {
		$t_global_exp = $t_global;
	}

	$t_project = config_get( $p_threshold );
	if( !is_array( $t_project ) ) {
		$t_project_exp = array();
		foreach( $g_access_levels as $t_access_level => $t_label ) {
			if( $t_access_level >= $t_project ) {
				$t_project_exp[] = $t_access_level;
			}
		}
	} else {
		$t_project_exp = $t_project;
	}

	$t_can_change = access_has_project_level( config_get_access( $p_threshold ), $g_project_id, $g_user )
			  && ( ( ALL_PROJECTS == $g_project_id ) || !$p_all_projects_only );

	echo "<tr>\n";

	# Access levels
	echo '  <td>' . string_display( $p_caption ) . "</td>\n";
	foreach( $g_access_levels as $t_access_level => $t_access_label ) {
		$t_file = in_array( $t_access_level, $t_file_exp );
		$t_global = in_array( $t_access_level, $t_global_exp );
		$t_project = in_array( $t_access_level, $t_project_exp );

		$t_color = set_color( $p_threshold, $t_file, $t_global, $t_project, $t_can_change );

		if( $t_can_change ) {
			$t_checked = $t_project ? 'checked="checked"' : '';
			$t_value = '<label><input type="checkbox" class="ace" name="flag_thres_' . $p_threshold .
				'[]" value="' . $t_access_level . '" ' . $t_checked . ' /><span class="lbl"></span></label>';
			$t_show_submit = true;
		} else {
			if( $t_project ) {
				$t_value = '<i class="fa fa-check fa-lg blue"></i>';
			} else {
				$t_value = '&#160;';
			}
		}
		echo '  <td class="center ' . $t_color . '">' . $t_value . "</td>\n";
	}

	print_who_can_change( $p_threshold, $t_can_change );

	echo "</tr>\n";
}

/**
 * Get boolean row
 * @param string  $p_caption           Caption.
 * @param string  $p_threshold         Threshold.
 * @param boolean $p_all_projects_only All projects only.
 * @return void
 */
function get_capability_boolean( $p_caption, $p_threshold, $p_all_projects_only = false ) {
	global $g_user, $g_project_id, $t_show_submit, $g_access_levels;

	$t_file = config_get_global( $p_threshold );
	$t_global = config_get( $p_threshold, null, ALL_USERS, ALL_PROJECTS );
	$t_project = config_get( $p_threshold );

	$t_can_change = access_has_project_level( config_get_access( $p_threshold ), $g_project_id, $g_user )
			  && ( ( ALL_PROJECTS == $g_project_id ) || !$p_all_projects_only );

	echo "<tr>\n\t<td>" . string_display_line( $p_caption ) . "</td>\n";

	# Value
	$t_color = set_color( $p_threshold, $t_file, $t_global, $t_project, $t_can_change );
	if( $t_can_change ) {
		$t_checked = ( ON == config_get( $p_threshold ) ) ? 'checked="checked"' : '';
		$t_value = '<label><input type="checkbox" class="ace" name="flag_' . $p_threshold . '" value="1" ' .
			$t_checked . ' /><span class="lbl"></span></label>';
		$t_show_submit = true;
	} else {
		if( ON == config_get( $p_threshold ) ) {
			$t_value = '<i class="fa fa-check fa-lg blue"></i>';
		} else {
			$t_value = '&#160;';
		}
	}
	echo "\t" . '<td class="center ' . $t_color . '">' . $t_value . '</td>' . "\n\t"
		. '<td class="left" colspan="' . ( count( $g_access_levels ) - 1 ). '"></td>';

	print_who_can_change( $p_threshold, $t_can_change );

	echo '</tr>' . "\n";
}

/**
 * Get enumeration row
 * @param string  $p_caption           Caption.
 * @param string  $p_threshold         Threshold.
 * @param string  $p_enum              Enumeration.
 * @param boolean $p_all_projects_only All projects only.
 * @return void
 */
function get_capability_enum( $p_caption, $p_threshold, $p_enum, $p_all_projects_only = false ) {
	global $g_user, $g_project_id, $t_show_submit, $g_access_levels;

	$t_file = config_get_global( $p_threshold );
	$t_global = config_get( $p_threshold, null, ALL_USERS, ALL_PROJECTS );
	$t_project = config_get( $p_threshold );

	$t_can_change = access_has_project_level( config_get_access( $p_threshold ), $g_project_id, $g_user )
			  && ( ( ALL_PROJECTS == $g_project_id ) || !$p_all_projects_only );

	echo '<tr>' . "\n";
	echo "\t" . '<td>' . string_display_line( $p_caption ) . '</td>' . "\n";

	# Value
	$t_color = set_color( $p_threshold, $t_file, $t_global, $t_project, $t_can_change );
	echo "\t" . '<td class="left ' . $t_color . '" colspan="3">';
	if( $t_can_change ) {
		echo '<select name="flag_' . $p_threshold . '" class="input-sm">';
		print_enum_string_option_list( $p_enum, config_get( $p_threshold ) );
		echo '</select>';
		$t_show_submit = true;
	} else {
		$t_value = MantisEnum::getLabel( lang_get( $p_enum . '_enum_string' ), config_get( $p_threshold ) ) . '&#160;';
		echo $t_value;
	}
	echo '</td>' . "\n\t" . '<td colspan="' . ( count( $g_access_levels ) - 3 ) . '"></td>' . "\n";

	print_who_can_change( $p_threshold, $t_can_change );

	echo '</tr>' . "\n";
}

/**
 * Print section end
 * @return void
 */
function get_section_end() {
	echo '</tbody></table></div>' . "\n";
	echo '</div></div></div> ' . "\n";
	echo '<div class="space-10"></div>';
}


echo '<br />' . "\n";

if( ALL_PROJECTS == $g_project_id ) {
	$t_project_title = lang_get( 'config_all_projects' );
} else {
	$t_project_title = sprintf( lang_get( 'config_project' ), string_display_line( project_get_name( $g_project_id ) ) );
}

echo '<div class="col-md-12 col-xs-12">' . "\n";
echo '<div class="well">' . "\n";
echo '<p class="bold"><i class="fa fa-info-circle"></i> ' . $t_project_title . '</p>' . "\n";
echo '<p>' . lang_get( 'colour_coding' ) . '<br />';
if( ALL_PROJECTS <> $g_project_id ) {
	echo '<span class="color-project">' . lang_get( 'colour_project' ) .'</span><br />';
}
echo '<span class="color-global">' . lang_get( 'colour_global' ) . '</span></p>';
echo '</div>' . "\n";

echo '<form id="mail_config_action" method="post" action="manage_config_work_threshold_set.php">' . "\n";
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
get_section_begin_mcwt( lang_get( 'others' ) );
get_capability_row( lang_get( 'view' ) . ' ' . lang_get( 'changelog_link' ), 'view_changelog_threshold' );
get_capability_row( lang_get( 'view' ) . ' ' . lang_get( 'roadmap_link' ), 'roadmap_view_threshold' );
get_capability_row( lang_get( 'view' ) . ' ' . lang_get( 'summary_link' ), 'view_summary_threshold' );
get_capability_row( lang_get( 'view' ) . ' ' . lang_get( 'assigned_to' ), 'view_handler_threshold' );
get_capability_row( lang_get( 'view' ) . ' ' . lang_get( 'bug_history' ), 'view_history_threshold' );
get_capability_row( lang_get( 'send_reminders' ), 'bug_reminder_threshold' );
get_capability_row( lang_get( 'receive_reminders' ), 'reminder_receive_threshold' );
get_section_end();


if( $t_show_submit ) {
	echo '<input type="submit" class="btn btn-primary btn-white btn-round" value="' . lang_get( 'change_configuration' ) . '" />' . "\n";
}

echo '</form>' . "\n";

if( $t_show_submit && ( 0 < count( $g_overrides ) ) ) {
	echo '<div class="pull-right"><form id="threshold_config_action" method="post" action="manage_config_revert.php">' . "\n";
	echo form_security_field( 'manage_config_revert' );
	echo '<input name="revert" type="hidden" value="' . implode( ',', $g_overrides ) . '"></input>';
	echo '<input name="project" type="hidden" value="' . $g_project_id . '"></input>';
	echo '<input name="return" type="hidden" value="' . string_attribute( form_action_self() ) .'"></input>';
	echo '<input type="submit" class="btn btn-primary btn-sm btn-white btn-round" value="';
	if( ALL_PROJECTS == $g_project_id ) {
		echo lang_get( 'revert_to_system' );
	} else {
		echo lang_get( 'revert_to_all_project' );
	}
	echo '" />' . "\n";
	echo '</form></div>' . "\n";
}
echo '</div>';
layout_page_end();
