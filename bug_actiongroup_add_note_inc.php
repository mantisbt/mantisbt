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
	function action_add_note_print_title() {
        echo '<tr class="form-title">';
        echo '<td colspan="2">';
        echo lang_get( 'add_bugnote_title' );
        echo '</td></tr>';
	}

	/**
	 * Prints the field within the custom action form.  This has an entry for
	 * every field the user need to supply + the submit button.  The fields are
	 * added as rows in a table that is already created by the calling code.
	 * A row has two columns.
	 */
	function action_add_note_print_fields() {
		echo '<tr class="row-1" valign="top"><td class="category">', lang_get( 'add_bugnote_title' ), '</td><td><textarea name="bugnote_text" cols="80" rows="10"></textarea></td></tr>';
	?>
	<!-- View Status -->
	<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'view_status' ) ?>
	</td>
	<td>
<?php
		$t_default_state = config_get( 'default_bugnote_view_status' );
		if ( access_has_project_level( config_get( 'set_view_status_threshold' ) ) ) { ?>
			<select name="view_state">
				<?php print_enum_string_option_list( 'view_state', $t_default_state ) ?>
			</select>
<?php
		} else {
			echo get_enum_element( 'view_state', $t_default_state );
			echo '<input type="hidden" name="view_state" value="', $t_default_state, '" />';
		}
?>
	</td>
	</tr>
	<?php
		echo '<tr><td colspan="2"><center><input type="submit" class="button" value="' . lang_get( 'add_bugnote_button' ) . ' " /></center></td></tr>';
	}

	/**
	 * Validates the action on the specified bug id.
	 *
	 * @returns true|array Action can be applied., ( bug_id => reason for failure )
	 */
	function action_add_note_validate( $p_bug_id ) {
		$f_bugnote_text = gpc_get_string( 'bugnote_text' );

		if ( is_blank( $f_bugnote_text ) ) {
			error_parameters( lang_get( 'bugnote' ) );
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		}

		$t_failed_validation_ids = array();
		$t_add_bugnote_threshold = config_get( 'add_bugnote_threshold' );
		$t_bug_id = $p_bug_id;

		if ( bug_is_readonly( $t_bug_id ) ) {
			$t_failed_validation_ids[$t_bug_id] = lang_get( 'actiongroup_error_issue_is_readonly' );
			return $t_failed_validation_ids;
		}

		if ( !access_has_bug_level( $t_add_bugnote_threshold, $t_bug_id ) ) {
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
	function action_add_note_process( $p_bug_id ) {
		$f_bugnote_text = gpc_get_string( 'bugnote_text' );
		$f_view_state = gpc_get_int( 'view_state' );
		bugnote_add ( $p_bug_id, $f_bugnote_text, '0:00', /* $p_private = */ $f_view_state != VS_PUBLIC  );
        return true;
    }
