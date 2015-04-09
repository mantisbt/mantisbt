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
 * Bug action group include file
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses bug_api.php
 * @uses config_api.php
 * @uses gpc_api.php
 * @uses lang_api.php
 * @uses print_api.php
 */

if( !defined( 'BUG_ACTIONGROUP_INC_ALLOW' ) ) {
	return;
}

require_api( 'access_api.php' );
require_api( 'bug_api.php' );
require_api( 'config_api.php' );
require_api( 'gpc_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );

/**
 * Prints the title for the custom action page.
 * @return void
 */
function action_update_severity_print_title() {
	echo lang_get( 'update_severity_title' );
}

/**
 * Prints the field within the custom action form.  This has an entry for
 * every field the user need to supply + the submit button.  The fields are
 * added as rows in a table that is already created by the calling code.
 * A row has two columns.
 * @return void
 */
function action_update_severity_print_fields() {
?>
	<tr>
		<th class="category">
			<?php echo lang_get( 'update_severity_msg' ); ?>
		</th>
		<td>
			<select name="severity" class="input-sm">';
				<?php print_enum_string_option_list( 'severity' ); ?>
			</select>
		</td>
	</tr>
<?php
}

/**
 * Validates the action on the specified bug id.
 *
 * @param integer $p_bug_id A bug identifier.
 * @return string|null On failure: the reason why the action could not be validated. On success: null.
 */
function action_update_severity_validate( $p_bug_id ) {
	$t_update_severity_threshold = config_get( 'update_bug_threshold' );
	$t_bug_id = $p_bug_id;

	if( bug_is_readonly( $t_bug_id ) ) {
		return lang_get( 'actiongroup_error_issue_is_readonly' );
	}

	if( !access_has_bug_level( $t_update_severity_threshold, $t_bug_id ) ) {
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
function action_update_severity_process( $p_bug_id ) {
	$f_severity = gpc_get_string( 'severity' );
	bug_set_field( $p_bug_id, 'severity', $f_severity );
	return null;
}
