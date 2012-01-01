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
	 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */

	/**
	 * Prints the title for the custom action page.
	 */
	function action_update_product_build_print_title() {
        echo '<tr class="form-title">';
        echo '<td colspan="2">';
        echo lang_get( 'product_build' );
        echo '</td></tr>';
	}

	/**
	 * Prints the field within the custom action form.  This has an entry for
	 * every field the user need to supply + the submit button.  The fields are
	 * added as rows in a table that is already created by the calling code.
	 * A row has two columns.
	 */
	function action_update_product_build_print_fields() {
		echo '<tr class="row-1" valign="top"><td class="category">', lang_get( 'product_build' ), '</td><td><input type="text" name="build" size="32" maxlength="32" /></td></tr>';
		echo '<tr><td colspan="2"><center><input type="submit" class="button" value="' . lang_get( 'actiongroup_menu_update_product_build' ) . ' " /></center></td></tr>';
	}

	/**
	 * Validates the action on the specified bug id.
	 *
	 * @param $p_bug_id Bug ID
	 * @return true|array  Action can be applied., bug_id => reason for failure
	 */
	function action_update_product_build_validate( $p_bug_id ) {
		$t_bug_id = (int)$p_bug_id;

		if ( bug_is_readonly( $t_bug_id ) ) {
			$t_failed_validation_ids = array();
			$t_failed_validation_ids[$t_bug_id] = lang_get( 'actiongroup_error_issue_is_readonly' );
			return $t_failed_validation_ids;
		}

		if ( !access_has_bug_level( config_get( 'update_bug_threshold' ), $t_bug_id ) ) {
			$t_failed_validation_ids = array();
			$t_failed_validation_ids[$t_bug_id] = lang_get( 'access_denied' );
			return $t_failed_validation_ids;
		}

		return true;
	}

	/**
	 * Executes the custom action on the specified bug id.
	 *
	 * @param $p_bug_id  The bug id to execute the custom action on.
	 * @returns true|array Action executed successfully., ( bug_id => reason for failure )
	 */
	function action_update_product_build_process( $p_bug_id ) {
		$f_build = gpc_get_string( 'build' );
		$t_build = trim( $f_build );

		bug_set_field( $p_bug_id, 'build', $t_build );
		return true;
    }
