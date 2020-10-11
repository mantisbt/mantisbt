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
 * Bug Group Action API
 *
 * @package CoreAPI
 * @subpackage BugGroupActionAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses bug_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses string_api.php
 */

require_api( 'bug_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'string_api.php' );

require_css( 'status_config.php' );

/**
 * Initialise bug action group api
 * @param string $p_action Custom action to run.
 * @return void
 */
function bug_group_action_init( $p_action ) {
	$t_valid_actions = bug_group_action_get_commands( current_user_get_accessible_projects() );
	$t_action = strtoupper( $p_action );

	if( !isset( $t_valid_actions[$t_action] ) &&
		!isset( $t_valid_actions['EXT_' . $t_action] )
		) {
		trigger_error( ERROR_GENERIC, ERROR );
	}

	$t_include_file = config_get_global( 'absolute_path' ) . 'bug_actiongroup_' . $p_action . '_inc.php';
	if( !file_exists( $t_include_file ) ) {
		trigger_error( ERROR_GENERIC, ERROR );
	} else {
		require_once( $t_include_file );
	}
}

/**
 * Print the top part for the bug action group page.
 * @return void
 */
function bug_group_action_print_top() {
	layout_page_header();
	layout_page_begin();
}

/**
 * Print the bottom part for the bug action group page.
 * @return void
 */
function bug_group_action_print_bottom() {
	layout_page_end();
}

/**
 * Print the list of selected issues and the legend for the status colors.
 *
 * @param array $p_bug_ids_array An array of issue ids.
 * @return void
 */
function bug_group_action_print_bug_list( array $p_bug_ids_array ) {
	echo '<tr>';
	echo '<th class="category" colspan="2">';
	echo lang_get( 'actiongroup_bugs' );
	echo '</th>';
	echo '</tr>';

	foreach( $p_bug_ids_array as $t_bug_id ) {
		# choose color based on status
		$t_status_css = html_get_status_css_fg( bug_get_field( $t_bug_id, 'status' ), auth_get_current_user_id(), bug_get_field( $t_bug_id, 'project_id' ) );
		$t_lead = icon_get( 'fa-square', 'fa-status-box ' . $t_status_css );
		$t_lead .= ' ' . string_get_bug_view_link( $t_bug_id );
		echo sprintf( "<tr> <td>%s</td> <td>%s</td> </tr>\n", $t_lead, string_attribute( bug_get_field( $t_bug_id, 'summary' ) ) );
	}
}

/**
 * Print a table listing the failed results following a group action.
 *
 * @param array $p_failed_ids List of failed results [bug_id => reason for failure]
 *
 * @return void
 */
function bug_group_action_print_results( array $p_failed_ids ) {
	$t_format = "<tr>"
		. "\n\t" . '<td width="50%%">%s' . lang_get( 'word_separator' ) . '%s</td>'
		. "\n\t" . '<td>%s</td>'
		. "\n</tr>\n";
	$t_label = lang_get( 'label' );

	echo '<div><br />', PHP_EOL;
	echo '<div class="table-responsive">', PHP_EOL;
	echo '<table class="table table-bordered table-condensed table-striped">', PHP_EOL;

	foreach( $p_failed_ids as $t_id => $t_reason ) {
		printf( $t_format,
			sprintf( $t_label, string_get_bug_view_link( $t_id ) ),
			string_display_line( bug_get_field( $t_id, 'summary' ) ),
			$t_reason
		);
	}
	echo '</table>', PHP_EOL;
	echo '</div>', PHP_EOL;

	print_link_button( 'view_all_bug_page.php', lang_get( 'proceed' ) );
	echo '</div>', PHP_EOL;
}

/**
 * Print the array of issue ids via hidden fields in the form to be passed on to
 * the bug action group action page.
 *
 * @param array $p_bug_ids_array An array of issue ids.
 * @return void
 */
function bug_group_action_print_hidden_fields( array $p_bug_ids_array ) {
	foreach( $p_bug_ids_array as $t_bug_id ) {
		echo '<input type="hidden" name="bug_arr[]" value="' . $t_bug_id . '" />' . "\n";
	}
}

/**
 * Prints the list of fields in the custom action form.  These are the user inputs
 * and the submit button.  This ends up calling action_<action>_print_fields()
 * from bug_actiongroup_<action>_inc.php
 *
 * @param string $p_action The custom action name without the "EXT_" prefix.
 * @return void
 */
function bug_group_action_print_action_fields( $p_action ) {
	$t_function_name = 'action_' . $p_action . '_print_fields';
	$t_function_name();
}

/**
 * Prints some title text for the custom action page.  This ends up calling
 * action_<action>_print_title() from bug_actiongroup_<action>_inc.php
 *
 * @param string $p_action The custom action name without the "EXT_" prefix.
 * @return void
 */
function bug_group_action_print_title( $p_action ) {
	$t_function_name = 'action_' . $p_action . '_print_title';
	$t_function_name();
}

/**
 * Validates the combination of an action and a bug.  This ends up calling
 * action_<action>_validate() from bug_actiongroup_<action>_inc.php
 *
 * @param string  $p_action The custom action name without the "EXT_" prefix.
 * @param integer $p_bug_id The id of the bug to validate the action on.
 *
 * @return boolean|array true if action can be applied or array of ( bug_id => reason for failure to validate )
 */
function bug_group_action_validate( $p_action, $p_bug_id ) {
	$t_function_name = 'action_' . $p_action . '_validate';
	return $t_function_name( $p_bug_id );
}

/**
 * Executes an action on a bug.  This ends up calling
 * action_<action>_process() from bug_actiongroup_<action>_inc.php
 *
 * @param string  $p_action The custom action name without the "EXT_" prefix.
 * @param integer $p_bug_id The id of the bug to validate the action on.
 * @return boolean|array Action can be applied., ( bug_id => reason for failure to process )
 */
function bug_group_action_process( $p_action, $p_bug_id ) {
	$t_function_name = 'action_' . $p_action . '_process';
	return $t_function_name( $p_bug_id );
}

/**
 * Get a list of bug group actions available to the current user for one or
 * more projects.
 * @param array $p_project_ids An array containing one or more project IDs.
 * @return array
 */
function bug_group_action_get_commands( array $p_project_ids = null ) {
	if( $p_project_ids === null || count( $p_project_ids ) == 0 ) {
		$p_project_ids = array( ALL_PROJECTS );
	}

	$t_user_id = auth_get_current_user_id();

	$t_commands = array();
	version_cache_array_rows( $p_project_ids );
	foreach( $p_project_ids as $t_project_id ) {

		$t_update_bug_allowed = access_has_project_level( config_get( 'update_bug_threshold', null, $t_user_id, $t_project_id ), $t_project_id );
		$t_update_bug_status_allowed = access_has_project_level( config_get( 'update_bug_status_threshold', null, $t_user_id, $t_project_id ), $t_project_id );

		if( !isset( $t_commands['MOVE'] ) &&
			user_has_more_than_one_project( $t_user_id ) &&
			access_has_project_level( config_get( 'move_bug_threshold', null, $t_user_id, $t_project_id ), $t_project_id ) ) {
			$t_commands['MOVE'] = lang_get( 'move' );
		}

		if( !isset( $t_commands['COPY'] ) &&
			access_has_any_project_level( 'report_bug_threshold' ) ) {
			$t_commands['COPY'] = lang_get( 'actiongroup_menu_copy' );
		}

		if( !isset( $t_commands['ASSIGN'] ) &&
			access_has_project_level( config_get( 'update_bug_assign_threshold', null, $t_user_id, $t_project_id ), $t_project_id ) ) {
			$t_commands['ASSIGN'] = lang_get( 'actiongroup_menu_assign' );
		}

		if( !isset( $t_commands['CLOSE'] ) && $t_update_bug_status_allowed &&
			( access_has_project_level( access_get_status_threshold( config_get( 'bug_closed_status_threshold', null, $t_user_id, $t_project_id ), $t_project_id ), $t_project_id ) ||
				access_has_project_level( config_get( 'allow_reporter_close', null, $t_user_id, $t_project_id ), $t_project_id ) ) ) {
			$t_commands['CLOSE'] = lang_get( 'close' );
		}

		if( !isset( $t_commands['DELETE'] ) &&
			access_has_project_level( config_get( 'delete_bug_threshold', null, $t_user_id, $t_project_id ), $t_project_id ) ) {
			$t_commands['DELETE'] = lang_get( 'delete' );
		}

		if( !isset( $t_commands['RESOLVE'] ) && $t_update_bug_status_allowed &&
			access_has_project_level( access_get_status_threshold( config_get( 'bug_resolved_status_threshold', null, $t_user_id, $t_project_id ), $t_project_id ), $t_project_id ) ) {
			$t_commands['RESOLVE'] = lang_get( 'actiongroup_menu_resolve' );
		}

		if( !isset( $t_commands['SET_STICKY'] ) &&
			access_has_project_level( config_get( 'set_bug_sticky_threshold', null, $t_user_id, $t_project_id ), $t_project_id ) ) {
			$t_commands['SET_STICKY'] = lang_get( 'actiongroup_menu_set_sticky' );
		}

		if( !isset( $t_commands['UP_PRIOR'] ) && $t_update_bug_allowed ) {
			$t_commands['UP_PRIOR'] = lang_get( 'actiongroup_menu_update_priority' );
		}

		if( !isset( $t_commands['EXT_UPDATE_SEVERITY'] ) && $t_update_bug_allowed ) {
			$t_commands['EXT_UPDATE_SEVERITY'] = lang_get( 'actiongroup_menu_update_severity' );
		}

		if( !isset( $t_commands['UP_STATUS'] ) && $t_update_bug_status_allowed ) {
			$t_commands['UP_STATUS'] = lang_get( 'actiongroup_menu_update_status' );
		}

		if( !isset( $t_commands['UP_CATEGORY'] ) && $t_update_bug_allowed ) {
			$t_commands['UP_CATEGORY'] = lang_get( 'actiongroup_menu_update_category' );
		}

		if( !isset( $t_commands['VIEW_STATUS'] ) &&
			access_has_project_level( config_get( 'change_view_status_threshold', null, $t_user_id, $t_project_id ), $t_project_id ) ) {
			$t_commands['VIEW_STATUS'] = lang_get( 'actiongroup_menu_update_view_status' );
		}

		if( !isset( $t_commands['EXT_UPDATE_PRODUCT_BUILD'] ) &&
			config_get( 'enable_product_build', null, $t_user_id, $t_project_id ) == ON &&
			$t_update_bug_allowed ) {
			$t_commands['EXT_UPDATE_PRODUCT_BUILD'] = lang_get( 'actiongroup_menu_update_product_build' );
		}

		if( !isset( $t_commands['EXT_ADD_NOTE'] ) &&
			access_has_project_level( config_get( 'add_bugnote_threshold', null, $t_user_id, $t_project_id ), $t_project_id ) ) {
			$t_commands['EXT_ADD_NOTE'] = lang_get( 'actiongroup_menu_add_note' );
		}

		if( !isset( $t_commands['EXT_ATTACH_TAGS'] ) &&
			access_has_project_level( config_get( 'tag_attach_threshold', null, $t_user_id, $t_project_id ), $t_project_id ) ) {
			$t_commands['EXT_ATTACH_TAGS'] = lang_get( 'actiongroup_menu_attach_tags' );
		}

		if( !isset( $t_commands['UP_DUE_DATE'] ) &&
			access_has_project_level( config_get( 'due_date_update_threshold', null, $t_user_id, $t_project_id ), $t_project_id ) ) {
			$t_commands['UP_DUE_DATE'] = lang_get( 'actiongroup_menu_update_due_date' );
		}

		if( !isset( $t_commands['UP_PRODUCT_VERSION'] ) &&
			version_should_show_product_version( $t_project_id ) && $t_update_bug_allowed ) {
			$t_commands['UP_PRODUCT_VERSION'] = lang_get( 'actiongroup_menu_update_product_version' );
		}

		if( !isset( $t_commands['UP_FIXED_IN_VERSION'] ) &&
			version_should_show_product_version( $t_project_id ) && $t_update_bug_allowed ) {
			$t_commands['UP_FIXED_IN_VERSION'] = lang_get( 'actiongroup_menu_update_fixed_in_version' );
		}

		if( !isset( $t_commands['UP_TARGET_VERSION'] ) &&
			version_should_show_product_version( $t_project_id ) &&
			access_has_project_level( config_get( 'roadmap_update_threshold', null, $t_user_id, $t_project_id ), $t_project_id ) ) {
			$t_commands['UP_TARGET_VERSION'] = lang_get( 'actiongroup_menu_update_target_version' );
		}

		if ( $t_update_bug_allowed ) {
			$t_custom_field_ids = custom_field_get_linked_ids( $t_project_id );
			foreach( $t_custom_field_ids as $t_custom_field_id ) {
				if( custom_field_has_write_access_to_project( $t_custom_field_id, $t_project_id ) ) {
					$t_custom_field_def = custom_field_get_definition( $t_custom_field_id );
					$t_command_id = 'custom_field_' . $t_custom_field_id;
					$t_command_caption = sprintf( lang_get( 'actiongroup_menu_update_field' ), lang_get_defaulted( $t_custom_field_def['name'] ) );
					$t_commands[$t_command_id] = string_display_line( $t_command_caption );
				}
			}
		}

	}

	$t_custom_group_actions = config_get( 'custom_group_actions' );

	foreach( $t_custom_group_actions as $t_custom_group_action ) {
		# use label if provided to get the localized text, otherwise fallback to action name.
		if( isset( $t_custom_group_action['label'] ) ) {
			$t_commands[$t_custom_group_action['action']] = lang_get_defaulted( $t_custom_group_action['label'] );
		} else {
			$t_commands[$t_custom_group_action['action']] = lang_get_defaulted( $t_custom_group_action['action'] );
		}
	}

	return $t_commands;
}
