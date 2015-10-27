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
 * This include file prints out the bug history
 * $f_bug_id must already be defined
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses collapse_api.php
 * @uses config_api.php
 * @uses helper_api.php
 * @uses history_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 */

if( !defined( 'HISTORY_INC_ALLOW' ) ) {
	return;
}

require_api( 'access_api.php' );
require_api( 'collapse_api.php' );
require_api( 'config_api.php' );
require_api( 'helper_api.php' );
require_api( 'history_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );

$t_access_level_needed = config_get( 'view_history_threshold' );
if( !access_has_bug_level( $t_access_level_needed, $f_bug_id ) ) {
	return;
}
?>

<a id="history"></a><br />

<?php
	collapse_open( 'history', '', 'table-container' );
	$t_history = history_get_events_array( $f_bug_id );
?>
<table>
	<thead>
		<tr>
			<td class="form-title" colspan="4"><?php
				collapse_icon( 'history' );
				echo lang_get( 'bug_history' ); ?>
			</td>
		</tr>

		<tr class="row-category-history">
			<th class="small-caption">
				<?php echo lang_get( 'date_modified' ) ?>
			</th>
			<th class="small-caption">
				<?php echo lang_get( 'username' ) ?>
			</th>
			<th class="small-caption">
				<?php echo lang_get( 'field' ) ?>
			</th>
			<th class="small-caption">
				<?php echo lang_get( 'change' ) ?>
			</th>
		</tr>
	</thead>

	<tbody>
<?php
	foreach( $t_history as $t_item ) {
?>
		<tr>
			<td class="small-caption">
				<?php echo $t_item['date'] ?>
			</td>
			<td class="small-caption">
				<?php print_user( $t_item['userid'] ) ?>
			</td>
			<td class="small-caption">
				<?php echo string_display( $t_item['note'] ) ?>
			</td>
			<td class="small-caption">
				<?php echo ( $t_item['raw'] ? string_display_line_links( $t_item['change'] ) : $t_item['change'] ) ?>
			</td>
		</tr>
<?php
	} # end for loop
?>
	</tbody>
</table>
<?php
	collapse_closed( 'history' );
?>
<table class="width100" cellspacing="0">
<tr>
	<td class="form-title" colspan="4"><?php
		collapse_icon( 'history' );
		echo lang_get( 'bug_history' ); ?>
	</td>
</tr>
</table>

<?php
collapse_end( 'history' );
