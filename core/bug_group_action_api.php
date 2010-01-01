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
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2010  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *	@package CoreAPI
 *	@subpackage BugGroupActionAPI
 */

/**
 * Print the top part for the bug action group page.
 */
function bug_group_action_print_top() {
	html_page_top();
}

/**
 * Print the bottom part for the bug action group page.
 */
function bug_group_action_print_bottom() {
	html_page_bottom();
}

/**
 * Print the list of selected issues and the legend for the status colors.
 *
 * @param $p_bug_ids_array   An array of issue ids.
 */
function bug_group_action_print_bug_list( $p_bug_ids_array ) {
	$t_legend_position = config_get( 'status_legend_position' );

	if( STATUS_LEGEND_POSITION_TOP == $t_legend_position ) {
		html_status_legend();
		echo '<br />';
	}

	echo '<div align="center">';
	echo '<table class="width75" cellspacing="1">';
	echo '<tr class="row-1">';
	echo '<td class="category" colspan="2">';
	echo lang_get( 'actiongroup_bugs' );
	echo '</td>';
	echo '</tr>';

	$t_i = 1;

	foreach( $p_bug_ids_array as $t_bug_id ) {
		$t_class = sprintf( "row-%d", ( $t_i++ % 2 ) + 1 );
		echo sprintf( "<tr bgcolor=\"%s\"> <td>%s</td> <td>%s</td> </tr>\n", get_status_color( bug_get_field( $t_bug_id, 'status' ) ), string_get_bug_view_link( $t_bug_id ), string_attribute( bug_get_field( $t_bug_id, 'summary' ) ) );
	}

	echo '</table>';
	echo '</form>';
	echo '</div>';

	if( STATUS_LEGEND_POSITION_BOTTOM == $t_legend_position ) {
		echo '<br />';
		html_status_legend();
	}
}

/**
 * Print the array of issue ids via hidden fields in the form to be passed on to
 * the bug action group action page.
 *
 * @param $p_bug_ids_array   An array of issue ids.
 */
function bug_group_action_print_hidden_fields( $p_bug_ids_array ) {
	foreach( $p_bug_ids_array as $t_bug_id ) {
		echo '<input type="hidden" name="bug_arr[]" value="' . $t_bug_id . '" />' . "\n";
	}
}

/**
 * Prints the list of fields in the custom action form.  These are the user inputs
 * and the submit button.  This ends up calling action_<action>_print_fields()
 * from bug_actiongroup_<action>_inc.php
 *
 * @param $p_action   The custom action name without the "EXT_" prefix.
 */
function bug_group_action_print_action_fields( $p_action ) {
	require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'bug_actiongroup_' . $p_action . '_inc.php' );
	$t_function_name = 'action_' . $p_action . '_print_fields';
	$t_function_name();
}

/**
 * Prints some title text for the custom action page.  This ends up calling
 * action_<action>_print_title() from bug_actiongroup_<action>_inc.php
 *
 * @param $p_action   The custom action name without the "EXT_" prefix.
 */
function bug_group_action_print_title( $p_action ) {
	require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'bug_actiongroup_' . $p_action . '_inc.php' );
	$t_function_name = 'action_' . $p_action . '_print_title';
	$t_function_name();
}

/**
 * Validates the combination of an action and a bug.  This ends up calling
 * action_<action>_validate() from bug_actiongroup_<action>_inc.php
 *
 * @param $p_action   The custom action name without the "EXT_" prefix.
 * @param $p_bug_id   The id of the bug to validate the action on.
 *
 * @returns true|array true if action can be applied or array of ( bug_id => reason for failure to validate )
 */
function bug_group_action_validate( $p_action, $p_bug_id ) {
	require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'bug_actiongroup_' . $p_action . '_inc.php' );
	$t_function_name = 'action_' . $p_action . '_validate';
	return $t_function_name( $p_bug_id );
}


/**
 * Executes an action on a bug.  This ends up calling
 * action_<action>_process() from bug_actiongroup_<action>_inc.php
 *
 * @param $p_action   The custom action name without the "EXT_" prefix.
 * @param $p_bug_id   The id of the bug to validate the action on.
 * @returns true|array Action can be applied., ( bug_id => reason for failure to process )
 */
function bug_group_action_process( $p_action, $p_bug_id ) {
	require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'bug_actiongroup_' . $p_action . '_inc.php' );
	$t_function_name = 'action_' . $p_action . '_process';
	return $t_function_name( $p_bug_id );
}
