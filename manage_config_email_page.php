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
 * Manage Email Configuration
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

auth_reauthenticate();

/**
 * array_merge_recursive2()
 *
 * Similar to array_merge_recursive but keyed-valued are always overwritten.
 * Priority goes to the 2nd array.
 *
 * @public yes
 * @param array|string|integer $p_array1 Array.
 * @param array|string|integer $p_array2 Array.
 * @return array
 */
function array_merge_recursive2( $p_array1, $p_array2 ) {
	if( !is_array( $p_array1 ) || !is_array( $p_array2 ) ) {
		return $p_array2;
	}
	$t_merged_array = $p_array1;
	foreach( $p_array2 as $t_key2 => $t_value2 ) {
		if( array_key_exists( $t_key2, $t_merged_array ) && is_array( $t_value2 ) ) {
			$t_merged_array[$t_key2] = array_merge_recursive2( $t_merged_array[$t_key2], $t_value2 );
		} else {
			$t_merged_array[$t_key2] = $t_value2;
		}
	}
	return $t_merged_array;
}

/**
 * get_notify_flag cloned from email_notify_flag
 * Get the value associated with the specific action and flag.
 * For example, you can get the value associated with notifying "admin"
 * on action "new", i.e. notify administrators on new bugs which can be
 * ON or OFF.
 *
 * @param string $p_action Action.
 * @param string $p_flag   Flag.
 * @return string
 */
function get_notify_flag( $p_action, $p_flag ) {
	global $g_notify_flags, $g_default_notify_flags;

	$t_val = OFF;
	if( isset( $g_notify_flags[$p_action][$p_flag] ) ) {
		$t_val = $g_notify_flags[$p_action][$p_flag];
	} else if( isset( $g_default_notify_flags[$p_flag] ) ) {
		$t_val = $g_default_notify_flags[$p_flag];
	}
	return $t_val;
}

/**
 * Return CSS for flag
 *
 * @param string $p_action Action.
 * @param string $p_flag   Flag.
 * @return string
 */
function color_notify_flag( $p_action, $p_flag ) {
	global $g_notify_flags, $g_global_notify_flags, $g_file_notify_flags;

	$t_file = isset( $g_file_notify_flags[$p_action][$p_flag] ) ? ( $g_file_notify_flags[$p_action][$p_flag] ? 1 : 0 ): -1;
	$t_global = isset( $g_global_notify_flags[$p_action][$p_flag] ) ? ( $g_global_notify_flags[$p_action][$p_flag]  ? 1 : 0 ): -1;
	$t_project = isset( $g_notify_flags[$p_action][$p_flag] ) ? ( $g_notify_flags[$p_action][$p_flag]  ? 1 : 0 ): -1;

	$t_color = ' class="center" ';

	$t_effective_value = $t_file;

	if( $t_global >= 0 ) {
		if ( $t_global != $t_effective_value ) {
			$t_color = ' class="color-global center" '; # all projects override
		}

		$t_effective_value = $t_global;
	}

	if( $t_project >= 0 && $t_project != $t_effective_value ) {
		$t_color = ' class="color-project center" '; # project overrides
	}

	return $t_color;
}

/**
 * Get the value associated with the specific action and flag.
 *
 * @param string $p_action Action.
 * @param string $p_flag   Flag.
 * @return string
 */
function show_notify_flag( $p_action, $p_flag ) {
	global $g_can_change_flags , $g_can_change_defaults;
	$t_flag = (bool)get_notify_flag( $p_action, $p_flag );
	if( $g_can_change_flags || $g_can_change_defaults ) {
		$t_flag_name = $p_action . ':' . $p_flag;
		$t_set = $t_flag ? 'checked="checked"' : '';
		return '<label><input type="checkbox" class="ace" name="flag[]" value="' . $t_flag_name. '" ' . $t_set . ' /><span class="lbl"></span></label>';
	} else {
		return ( $t_flag ? '<i class="fa fa-check fa-lg blue"></i>' : '&#160;' );
	}
}

/**
 * Get CSS for threshold flags
 *
 * @param string $p_access Access.
 * @param string $p_action Action.
 * @return string
 */
function color_threshold_flag( $p_access, $p_action ) {
	global $g_notify_flags, $g_global_notify_flags, $g_file_notify_flags;

	$t_file = ( $p_access >= $g_file_notify_flags[$p_action]['threshold_min'] )
					 && ( $p_access <= $g_file_notify_flags[$p_action]['threshold_max'] );
	$t_global = ( $p_access >= $g_global_notify_flags[$p_action]['threshold_min'] )
					 && ( $p_access <= $g_global_notify_flags[$p_action]['threshold_max'] );
	$t_project = ( $p_access >= $g_notify_flags[$p_action]['threshold_min'] )
					 && ( $p_access <= $g_notify_flags[$p_action]['threshold_max'] );

	$t_color = ' class="center" ';

	if( $t_global != $t_file ) {
		$t_color = ' class="color-global center" '; # all projects override
	}

	if( $t_project != $t_global ) {
		$t_color = ' class="color-project center" '; # project overrides
	}

	return $t_color;
}

/**
 * HTML for Show notify threshold
 *
 * @param string $p_access Access.
 * @param string $p_action Action.
 * @return string
 */
function show_notify_threshold( $p_access, $p_action ) {
	global $g_can_change_flags , $g_can_change_defaults;
	$t_flag = ( $p_access >= get_notify_flag( $p_action, 'threshold_min' ) )
		&& ( $p_access <= get_notify_flag( $p_action, 'threshold_max' ) );
	if( $g_can_change_flags  || $g_can_change_defaults ) {
		$t_flag_name = $p_action . ':' . $p_access;
		$t_set = $t_flag ? 'checked="checked"' : '';
		return '<label><input class="ace" type="checkbox" name="flag_threshold[]" value="' . $t_flag_name . '" ' . $t_set . ' />' . '<span class="lbl"></span></label>';
	} else {
		return $t_flag ? '<i class="fa fa-check fa-lg blue"></i>' : '&#160;';
	}
}

/**
 * HTML for email section
 *
 * @param string $p_section_name Section name.
 * @return void
 */
function get_section_begin_for_email( $p_section_name ) {
	$t_access_levels = MantisEnum::getValues( config_get( 'access_levels_enum_string' ) );
	echo '<div class="space-10"></div>';
	echo '<div class="widget-box widget-color-blue2">';
	echo '   <div class="widget-header widget-header-small">';
	echo '        <h4 class="widget-title lighter uppercase">';
	echo '            <i class="ace-icon fa fa-envelope"></i>';
	echo $p_section_name;
	echo '       </h4>';
	echo '   </div>';
	echo '   <div class="widget-body">';
	echo '   <div class="widget-main no-padding">';
	echo '       <div class="table-responsive">';
	echo '<table class="table table-striped table-bordered table-condensed">' . "\n";
	echo '<thead>' . "\n";
	echo '<tr>' . "\n";
	echo '<th width="30%" rowspan="2">' . lang_get( 'message' ) . '</th>';
	echo '<th class="bold" style="text-align:center" rowspan="2">&#160;' . lang_get( 'issue_reporter' ) . '&#160;</th>' . "\n";
	echo '<th class="bold" style="text-align:center" rowspan="2">&#160;' . lang_get( 'issue_handler' ) . '&#160;</th>' . "\n";
	echo '<th class="bold" style="text-align:center" rowspan="2">&#160;' . lang_get( 'users_monitoring_bug' ) . '&#160;</th>' . "\n";
	echo '<th class="bold" style="text-align:center" rowspan="2">&#160;' . lang_get( 'users_added_bugnote' ) . '&#160;</th>' . "\n";
	echo '<th class="bold" style="text-align:center" rowspan="2">&#160;' . lang_get( 'category_assigned_to' ) . '&#160;</th>' . "\n";
	echo '<th class="bold" style="text-align:center" colspan="' . count( $t_access_levels ) . '">&#160;' . lang_get( 'email_notify_users' ) . '&#160;</th>' . "\n";
	echo '  </tr><tr>' . "\n";

	foreach( $t_access_levels as $t_access_level ) {
		echo '  <th>&#160;' . MantisEnum::getLabel( lang_get( 'access_levels_enum_string' ), $t_access_level ) . '&#160;</th>' . "\n";
	}

	echo '</tr>' . "\n";
	echo '</thead>' . "\n";
	echo '<tbody>' . "\n";
}

/**
 * HTML for Row
 *
 * @param string $p_caption      Caption.
 * @param string $p_message_type Message type.
 * @return void
 */
function get_capability_row_for_email( $p_caption, $p_message_type ) {
	$t_access_levels = MantisEnum::getValues( config_get( 'access_levels_enum_string' ) );

	echo '<tr><td>' . string_display( $p_caption ) . '</td>' . "\n";
	echo '  <td' . color_notify_flag( $p_message_type, 'reporter' ) . '>' . show_notify_flag( $p_message_type, 'reporter' )  . '</td>' . "\n";
	echo '  <td' . color_notify_flag( $p_message_type, 'handler' ) . '>' . show_notify_flag( $p_message_type, 'handler' ) . '</td>' . "\n";
	echo '  <td' . color_notify_flag( $p_message_type, 'monitor' ) . '>' . show_notify_flag( $p_message_type, 'monitor' ) . '</td>' . "\n";
	echo '  <td' . color_notify_flag( $p_message_type, 'bugnotes' ) . '>' . show_notify_flag( $p_message_type, 'bugnotes' ) . '</td>' . "\n";
	echo '  <td' . color_notify_flag( $p_message_type, 'category' ) . '>' . show_notify_flag( $p_message_type, 'category' ) . '</td>' . "\n";

	foreach( $t_access_levels as $t_access_level ) {
		echo '  <td' . color_threshold_flag( $t_access_level, $p_message_type ) . '>' . show_notify_threshold( $t_access_level, $p_message_type ) . '</td>' . "\n";
	}

	echo '</tr>' . "\n";
}

/**
 * HTML for email section end
 * @return void
 */
function get_section_end_for_email() {
	echo '</tbody></table></div>' . "\n";
	echo '</div></div></div> ' . "\n";
	echo '<div class="space-10"></div>';
}


layout_page_header( lang_get( 'manage_email_config' ) );

layout_page_begin( 'manage_overview_page.php' );

print_manage_menu( PAGE_CONFIG_DEFAULT );
print_manage_config_menu( 'manage_config_email_page.php' );

$t_access = current_user_get_access_level();
$t_project = helper_get_current_project();

# build a list of all of the actions
$t_actions = email_get_actions();

# build a composite of the status flags, exploding the defaults
$t_global_default_notify_flags = config_get( 'default_notify_flags', null, ALL_USERS, ALL_PROJECTS );
$g_global_notify_flags = array();
foreach ( $t_global_default_notify_flags as $t_flag => $t_value ) {
	foreach ( $t_actions as $t_action ) {
		$g_global_notify_flags[$t_action][$t_flag] = $t_value;
	}
}
$g_global_notify_flags = array_merge_recursive2( $g_global_notify_flags, config_get( 'notify_flags', null, ALL_USERS, ALL_PROJECTS ) );

$t_file_default_notify_flags = config_get_global( 'default_notify_flags' );
$g_file_notify_flags = array();
foreach ( $t_file_default_notify_flags as $t_flag => $t_value ) {
	foreach ( $t_actions as $t_action ) {
		$g_file_notify_flags[$t_action][$t_flag] = $t_value;
	}
}
$g_file_notify_flags = array_merge_recursive2( $g_file_notify_flags, config_get_global( 'notify_flags' ) );

$g_default_notify_flags = config_get( 'default_notify_flags' );
$g_notify_flags = array();
foreach ( $g_default_notify_flags as $t_flag => $t_value ) {
	foreach ( $t_actions as $t_action ) {
		$g_notify_flags[$t_action][$t_flag] = $t_value;
	}
}
$g_notify_flags = array_merge_recursive2( $g_notify_flags, config_get( 'notify_flags' ) );

$g_can_change_flags = $t_access >= config_get_access( 'notify_flags' );
$g_can_change_defaults = $t_access >= config_get_access( 'default_notify_flags' );

echo '<div class="col-md-12 col-xs-12">' . "\n";
echo '<div class="space-10"></div>';

# Email notifications
if( config_get( 'enable_email_notification' ) == ON ) {

	if( $g_can_change_flags  || $g_can_change_defaults ) {
		echo '<form id="mail_config_action" method="post" action="manage_config_email_set.php">' . "\n";
		echo form_security_field( 'manage_config_email_set' );
	}

	if( ALL_PROJECTS == $t_project ) {
		$t_project_title = lang_get( 'config_all_projects' );
	} else {
		$t_project_title = sprintf( lang_get( 'config_project' ), string_display( project_get_name( $t_project ) ) );
	}

	echo '<div class="well">' . "\n";
	echo '<p class="bold"><i class="fa fa-info-circle"></i> ' . $t_project_title . '</p>' . "\n";
	echo '<p>' . lang_get( 'colour_coding' ) . '<br />';
	if( ALL_PROJECTS <> $t_project ) {
		echo '<span class="color-project">' . lang_get( 'colour_project' ) . '</span><br />';
	}
	echo '<span class="color-global">' . lang_get( 'colour_global' ) . '</span></p>';
	echo '</div>' . "\n";
	
	get_section_begin_for_email( lang_get( 'email_notification' ) );
#		get_capability_row_for_email( lang_get( 'email_on_new' ), 'new' );  # duplicate of status change to 'new'
	get_capability_row_for_email( lang_get( 'email_on_updated' ), 'updated' );
	get_capability_row_for_email( lang_get( 'email_on_assigned' ), 'owner' );
	get_capability_row_for_email( lang_get( 'email_on_reopened' ), 'reopened' );
	get_capability_row_for_email( lang_get( 'email_on_deleted' ), 'deleted' );
	get_capability_row_for_email( lang_get( 'email_on_bugnote_added' ), 'bugnote' );
	if( config_get( 'enable_sponsorship' ) == ON ) {
		get_capability_row_for_email( lang_get( 'email_on_sponsorship_changed' ), 'sponsor' );
	}

	get_capability_row_for_email( lang_get( 'email_on_relationship_changed' ), 'relation' );

	$t_statuses = MantisEnum::getAssocArrayIndexedByValues( config_get( 'status_enum_string' ) );
	foreach ( $t_statuses as $t_status => $t_label ) {
		get_capability_row_for_email( lang_get( 'status_changed_to' ) . ' \'' . get_enum_element( 'status', $t_status ) . '\'', $t_label );
	}

	get_section_end_for_email();

	if( $g_can_change_flags  || $g_can_change_defaults ) {
		echo '<p>' . lang_get( 'notify_actions_change_access' ) . "\n";
		echo '<select name="notify_actions_access" class="input-sm">' . "\n";
		print_enum_string_option_list( 'access_levels', config_get_access( 'notify_flags' ) );
		echo "\n</select></p>";

		echo '<input type="submit" class="btn btn-primary btn-white btn-round" value="' . lang_get( 'change_configuration' ) . '" />' . "\n";

		echo "</form>\n";

		echo '<div>' . "\n";
		echo '<form name="mail_config_action" method="post" action="manage_config_revert.php">' . "\n";
		echo form_security_field( 'manage_config_revert' ) . "\n";
		echo '<input name="revert" type="hidden" value="notify_flags,default_notify_flags" />' . "\n";
		echo '<input name="project" type="hidden" value="' . $t_project . '" />' . "\n";
		echo '<input name="return" type="hidden" value="' . string_attribute( form_action_self() ) . '" />' . "\n";
		echo '<input type="submit" class="btn btn-primary btn-sm btn-white btn-round" value="';
		if( ALL_PROJECTS == $t_project ) {
			echo lang_get( 'revert_to_system' );
		} else {
			echo lang_get( 'revert_to_all_project' );
		}
		echo '" />' . "\n";
		echo "</form></div>\n";
	}

}
echo '</div>';
layout_page_end();
