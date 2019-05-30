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

$t_can_see_monitors = access_has_bug_level( config_get( 'show_monitor_list_threshold' ), $f_bug_id );
$t_can_add_others = access_has_bug_level( config_get( 'monitor_add_others_bug_threshold' ), $f_bug_id );

if( $t_can_see_monitors || $t_can_add_others ) {
	$t_collapse_block = is_collapsed( 'monitoring' );
	$t_block_css = $t_collapse_block ? 'collapsed' : '';
	$t_block_icon = $t_collapse_block ? 'fa-chevron-down' : 'fa-chevron-up';
?>
<div class="col-md-12 col-xs-12">
	<a id="monitors"></a>
	<div class="space-10"></div>

	<div id="monitoring" class="widget-box widget-color-blue2 <?php echo $t_block_css ?>">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<i class="ace-icon fa fa-users"></i>
				<?php echo lang_get( 'users_monitoring_bug' ) ?>
			</h4>
			<div class="widget-toolbar">
				<a data-action="collapse" href="#">
					<i class="1 ace-icon fa <?php echo $t_block_icon ?> bigger-125"></i>
				</a>
			</div>
		</div>

		<div class="widget-body">
			<div class="widget-main no-padding">
				<div class="table-responsive">
					<table class="table table-bordered table-condensed table-striped">
						<tr>
							<th class="category" width="15%">
								<?php echo lang_get( 'monitoring_user_list' ); ?>
							</th>
							<td>
								<div>
<?php
	# List of users monitoring the issue
	if( $t_can_see_monitors ) {
		$t_users = bug_get_monitors( $f_bug_id );
		if( count( $t_users ) == 0 ) {
			echo lang_get( 'no_users_monitoring_bug' );
		} else {
			$t_can_delete_others = access_has_bug_level( config_get( 'monitor_delete_others_bug_threshold' ), $f_bug_id );
			if( $t_can_delete_others ) {
				$t_button = '&nbsp;'
					. '<a class="btn btn-xs btn-primary btn-white btn-round" '
					. 'href="' . helper_mantis_url( 'bug_monitor_delete.php' )
						. '?bug_id=' . $f_bug_id . '&amp;'
						. 'user_id=%s'
						. htmlspecialchars( form_security_param( 'bug_monitor_delete' ) )
					. '"><i class="fa fa-times"></i></a>';
			}

			foreach( $t_users as $t_user ) {
				$t_print = prepare_user_name( $t_user );
				if( $t_can_delete_others ) {
					$t_print .= sprintf( $t_button, $t_user );
				}
				$t_list[] = $t_print;
			}
			echo implode( ",\n", $t_list );
		}
	} else {
		echo lang_get( 'access_denied' );
		$t_users = null;
	} # End users list
?>

								</div>
<?php
	if( $t_can_add_others ) {
?>

		<div class="space-10"></div>
		<form method="get" action="bug_monitor_add.php" class="form-inline noprint">
			<?php echo form_security_field( 'bug_monitor_add' ) ?>
			<input type="hidden" name="bug_id" value="<?php echo (integer)$f_bug_id; ?>" />
			<label for="bug_monitor_list_username"><?php echo lang_get( 'username' ) ?></label>
			<input type="text" class="input-sm" id="bug_monitor_list_username" name="username" />
			<input type="submit" class="btn btn-primary btn-sm btn-white btn-round" value="<?php echo lang_get( 'add_user_to_monitor' ) ?>" />
		</form>
<?php } ?>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<?php
} # show monitor list
