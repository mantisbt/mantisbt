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
 * This include file prints out the list of bugnotes attached to the bug
 * $f_bug_id must be set and be set to the bug id
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bugnote_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses database_api.php
 * @uses gpc_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses user_api.php
 */

if( !defined( 'PRINT_BUGNOTE_INC_ALLOW' ) ) {
	return;
}

require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bugnote_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'database_api.php' );
require_api( 'gpc_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );

$f_bug_id = gpc_get_int( 'bug_id' );

# grab the user id currently logged in
$t_user_id	= auth_get_current_user_id();
$c_bug_id		= (integer)$f_bug_id;

if( !access_has_bug_level( config_get( 'private_bugnote_threshold' ), $f_bug_id ) ) {
	$t_restriction = 'AND view_state=' . VS_PUBLIC;
} else {
	$t_restriction = '';
}

$t_bugnote_table		= db_get_table( 'bugnote' );
$t_bugnote_text_table	= db_get_table( 'bugnote_text' );
# get the bugnote data
$t_bugnote_order = current_user_get_pref( 'bugnote_order' );

$t_query = 'SELECT * FROM ' . $t_bugnote_table . '
		WHERE bug_id=' . db_param() . ' ' . $t_restriction . '
		ORDER BY date_submitted ' . $t_bugnote_order;
$t_result = db_query_bound( $t_query, array( $c_bug_id ) );
$t_num_notes = db_num_rows( $t_result );
?>

<table class="table table-striped table-bordered table-condensed no-margin">
	<?php
		# no bugnotes
		if( 0 == $t_num_notes ) {
	?>
	<tr>
		<td colspan="2">
			<?php echo lang_get( 'no_bugnotes_msg' ) ?>
		</td>
	</tr>
	<?php } else { # print bugnotes ?>
	<tr>
		<td class="bold bigger-110" colspan="2">
			<?php echo lang_get( 'bug_notes_title' ) ?>
		</td>
	</tr>
	<?php
		for( $i=0; $i < $t_num_notes; $i++ ) {
			# prefix all bugnote data with v3_
			$t_row = db_fetch_array( $t_result );

			$t_date_submitted = date( config_get( 'normal_date_format' ), $t_row['date_submitted'] );
			$t_last_modified = date( config_get( 'normal_date_format' ), $t_row['last_modified'] );

			# grab the bugnote text and id and prefix with v3_
			$t_query = 'SELECT note, id FROM ' . $t_bugnote_text_table  . ' WHERE id=' . db_param();
			$t_result2 = db_query_bound( $t_query, array( $t_row['bugnote_text_id'] ) );
			$t_note = db_result( $t_result2, 0, 0 );
			$t_bugnote_text_id = db_result( $t_result2, 0, 1 );

			$t_note = string_display_links( $t_note );
	?>
	<tr>
		<td class="no-padding" width="20%">
			<div class="small">
						(<?php echo bugnote_format_id( $t_row['id'] ) ?>)
						<br/>
						<?php
						print_user( $t_row['reporter_id'] );
						?>&#160;&#160;&#160;
						<br/>
						<?php echo $t_date_submitted ?>&#160;&#160;&#160;
						<?php if( $t_date_submitted != $t_last_modified ) {
							echo '<br />(' . lang_get( 'last_edited') . lang_get( 'word_separator' ) . $t_last_modified . ')';
						} ?>
			</div>
					</td>
		<td class="no-padding" width="85%">
					<?php
						switch( $t_row['note_type'] ) {
							case REMINDER:
								echo '<p><strong>' . lang_get( 'reminder_sent_to' ) . ': ';
								$t_note_attr = utf8_substr( $t_row['note_attr'], 1, utf8_strlen( $t_row['note_attr'] ) - 2 );
								$t_to = array();
								foreach ( explode( '|', $t_note_attr ) as $t_recipient ) {
									$t_to[] = string_display_line( user_get_name( $t_recipient ) );
								}
								echo implode( ', ', $t_to ) . '</strong></p>';
							default:
								echo $t_note;
								break;
						}
					?>
		</td>
	</tr>
	<?php
			} # end for loop
		} # end else
	?>
</table>
