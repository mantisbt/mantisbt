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
 * Bug action group additional actions
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses bug_group_action_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses utility_api.php
 */

if( !defined( 'BUG_ACTIONGROUP_INC_ALLOW' ) ) {
	return;
}

require_api( 'authentication_api.php' );
require_api( 'bug_group_action_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );
require_api( 'utility_api.php' );

$t_external_action = utf8_strtolower( utf8_substr( $f_action, utf8_strlen( $t_external_action_prefix ) ) );
$t_form_fields_page = 'bug_actiongroup_' . $t_external_action . '_inc.php';
$t_form_name = 'bug_actiongroup_' . $t_external_action;

bug_group_action_init( $t_external_action );

bug_group_action_print_top();
?>

<br />

<div id="action-group-div" class="form-container" >
	<form method="post" action="bug_actiongroup_ext.php">
		<?php echo form_security_field( $t_form_name ); ?>
		<input type="hidden" name="action" value="<?php echo string_attribute( $t_external_action ) ?>" />
		<table>
			<thead>
				<?php bug_group_action_print_title( $t_external_action ); ?>
			</thead>
			<tbody>
<?php
	bug_group_action_print_hidden_fields( $f_bug_arr );
	bug_group_action_print_action_fields( $t_external_action );
?>
			</tbody>
		</table>
	</form>
</div>

<br />

<?php
bug_group_action_print_bug_list( $f_bug_arr );
bug_group_action_print_bottom();
