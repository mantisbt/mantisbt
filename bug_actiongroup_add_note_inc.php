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
 * Bugnote action group add include file
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses bug_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses error_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses utility_api.php
 */

if( !defined( 'BUG_ACTIONGROUP_INC_ALLOW' ) ) {
	return;
}

require_api( 'access_api.php' );
require_api( 'bug_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'error_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'utility_api.php' );

/**
 * Prints the title for the custom action page.
 * @return void
 */
function action_add_note_print_title() {
	echo '<tr>';
	echo '<td class="form-title" colspan="2">';
	echo lang_get( 'add_bugnote_title' );
	echo '</td></tr>';
}

/**
 * Prints the field within the custom action form.  This has an entry for
 * every field the user need to supply + the submit button.  The fields are
 * added as rows in a table that is already created by the calling code.
 * A row has two columns.
 * @return void
 */
function action_add_note_print_fields() {
?>
	<tbody>
		<tr>
			<th class="category">
				<?php lang_get( 'add_bugnote_title' ); ?>
			</th>
			<td>
				<textarea name="bugnote_text" cols="80" rows="10"></textarea>
			</td>
		</tr>

		<!-- View Status -->
		<tr class="row-2">
			<th class="category">
				<?php echo lang_get( 'view_status' ) ?>
			</th>
			<td>
<?php
	$t_default_state = config_get( 'default_bugnote_view_status' );
	if( access_has_project_level( config_get( 'set_view_status_threshold' ) ) ) { ?>
				<select name="view_state">
					<?php print_enum_string_option_list( 'view_state', $t_default_state ) ?>
				</select>
<?php
	} else {
		echo get_enum_element( 'view_state', $t_default_state );
?>
				<input type="hidden" name="view_state" value="<?php echo $t_default_state; ?>" />';
<?php
	}
?>
			</td>
		</tr>
	</tbody>

	<tfoot>
		<tr>
			<td colspan="2" class="center">
				<input type="submit" class="button" value="<?php echo lang_get( 'add_bugnote_button' ); ?>" />
			</td>
		</tr>
	</tfoot>
<?php
}

/**
 * Validates the action on the specified bug id.
 *
 * @param integer $p_bug_id A bug identifier.
 * @return string|null On failure: the reason why the action could not be validated. On success: null.
 */
function action_add_note_validate( $p_bug_id ) {
	$f_bugnote_text = gpc_get_string( 'bugnote_text' );

	if( is_blank( $f_bugnote_text ) ) {
		error_parameters( lang_get( 'bugnote' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	$t_add_bugnote_threshold = config_get( 'add_bugnote_threshold' );
	$t_bug_id = $p_bug_id;

	if( bug_is_readonly( $t_bug_id ) ) {
		return lang_get( 'actiongroup_error_issue_is_readonly' );
	}

	if( !access_has_bug_level( $t_add_bugnote_threshold, $t_bug_id ) ) {
		return lang_get( 'access_denied' );
	}

	return null;
}

/**
 * Executes the custom action on the specified bug id.
 *
 * @param integer $p_bug_id The bug id to execute the custom action on.
 * @return null Previous validation ensures that this function doesn't fail. Therefore we can always return null to indicate no errors occurred.
 */
function action_add_note_process( $p_bug_id ) {
	$f_bugnote_text = gpc_get_string( 'bugnote_text' );
	$f_view_state = gpc_get_int( 'view_state' );
	$t_bugnote_id = bugnote_add( $p_bug_id, $f_bugnote_text, '0:00', $f_view_state != VS_PUBLIC );
	bugnote_process_mentions( $p_bug_id, $t_bugnote_id, $f_bugnote_text );
	return null;
}
