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
 * This include file prints out the list of users monitoring the current
 * bug.	$f_bug_id must be set and be set to the bug id
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses collapse_api.php
 * @uses config_api.php
 * @uses database_api.php
 * @uses form_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses user_api.php
 */

if( !defined( 'BUG_MONITOR_LIST_VIEW_INC_ALLOW' ) ) {
	return;
}

require_api( 'access_api.php' );
require_api( 'collapse_api.php' );
require_api( 'config_api.php' );
require_api( 'database_api.php' );
require_api( 'form_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'user_api.php' );

if( access_has_bug_level( config_get( 'show_monitor_list_threshold' ), $f_bug_id ) ) {
	$t_users = bug_get_monitors( $f_bug_id );
	$num_users = sizeof ( $t_users );

	echo '<a id="monitors"></a><br />';

	collapse_open( 'monitoring' );
?>
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
<?php
	collapse_icon( 'monitoring' );
?>
		<?php echo lang_get( 'users_monitoring_bug' ); ?>
	</td>
</tr>
<tr class="row-1">
	<th class="category" width="15%">
		<?php echo lang_get( 'monitoring_user_list' ); ?>
	</th>
	<td>
<?php
		if( 0 == $num_users ) {
			echo lang_get( 'no_users_monitoring_bug' );
		} else {
			$t_can_delete_others = access_has_bug_level( config_get( 'monitor_delete_others_bug_threshold' ), $f_bug_id );
	 		for ( $i = 0; $i < $num_users; $i++ ) {
				echo ($i > 0) ? ', ' : '';
				print_user( $t_users[$i] );
				if( $t_can_delete_others ) {
					echo ' [<a class="small" href="' . helper_mantis_url( 'bug_monitor_delete.php' ) . '?bug_id=' . $f_bug_id . '&amp;user_id=' . $t_users[$i] . htmlspecialchars(form_security_param( 'bug_monitor_delete' )) . '">' . lang_get( 'delete_link' ) . '</a>]';
				}
	 		}
		}

		if( access_has_bug_level( config_get( 'monitor_add_others_bug_threshold' ), $f_bug_id ) ) {
?>
		<br /><br />
		<form method="get" action="bug_monitor_add.php">
		<?php echo form_security_field( 'bug_monitor_add' ) ?>
			<input type="hidden" name="bug_id" value="<?php echo (integer)$f_bug_id; ?>" />
			<label for="bug_monitor_list_username"><?php echo lang_get( 'username' ) ?></label>
			<input type="text" id="bug_monitor_list_username" name="username" />
			<input type="submit" class="button" value="<?php echo lang_get( 'add_user_to_monitor' ) ?>" />
		</form>
		<?php } ?>
	</td>
</tr>
</table>
<?php
	collapse_closed( 'monitoring' );
?>
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="2"><?php collapse_icon( 'monitoring' ); ?>
		<?php echo lang_get( 'users_monitoring_bug' ); ?>
	</td>
</tr>
</table>
<?php
	collapse_end( 'monitoring' );
?>

<?php
} # show monitor list

