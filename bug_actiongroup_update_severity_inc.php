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
 * @package MantisBT
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * Prints the title for the custom action page.
 */
function action_update_severity_print_title() {
	echo '<tr class="form-title">';
	echo '<td colspan="2">';
	echo lang_get( 'update_severity_title' );
	echo '</td></tr>';
}

/**
 * Prints the field within the custom action form.  This has an entry for
 * every field the user need to supply + the submit button.  The fields are
 * added as rows in a table that is already created by the calling code.
 * A row has two columns.
 */
function action_update_severity_print_fields() {
	echo '<tr class="row-1" valign="top"><td class="category">';
	echo lang_get( 'update_severity_msg' );
	echo '</td><td><select name="severity">';
	print_enum_string_option_list( 'severity' );
	echo '</select></td></tr>';
	echo '<tr><td colspan="2"><center><input type="submit" class="button" value="' . lang_get( 'update_severity_button' ) . ' " /></center></td></tr>';
}

/**
 * Validates the action on the specified bug id.
 *
 * @returns true    Action can be applied.
 * @returns array( bug_id => reason for failure )
 */
function action_update_severity_validate( $p_bug_id ) {
	$f_severity = gpc_get_string( 'severity' );

	$t_failed_validation_ids = array();

	$t_update_severity_threshold = config_get( 'update_bug_threshold' );
	$t_bug_id = $p_bug_id;

	if ( bug_is_readonly( $t_bug_id ) ) {
		$t_failed_validation_ids[$t_bug_id] = lang_get( 'actiongroup_error_issue_is_readonly' );
		return $t_failed_validation_ids;
	}

	if ( !access_has_bug_level( $t_update_severity_threshold, $t_bug_id ) ) {
		$t_failed_validation_ids[$t_bug_id] = lang_get( 'access_denied' );
		return $t_failed_validation_ids;
	}

	return true;
}

/**
 * Executes the custom action on the specified bug id.
 *
 * @param $p_bug_id  The bug id to execute the custom action on.
 *
 * @returns true   Action executed successfully.
 * @returns array( bug_id => reason for failure )
 */
function action_update_severity_process( $p_bug_id ) {
	$f_severity = gpc_get_string( 'severity' );
	bug_set_field( $p_bug_id, 'severity', $f_severity );
	return true;
}