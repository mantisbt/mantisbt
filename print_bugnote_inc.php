<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

	# --------------------------------------------------------
	# $Id: print_bugnote_inc.php,v 1.34.22.1 2007-10-13 22:34:19 giallu Exp $
	# --------------------------------------------------------
?>
<?php
	# This include file prints out the list of bugnotes attached to the bug
	# $f_bug_id must be set and be set to the bug id
?>
<?php
	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'current_user_api.php' );
	require_once( $t_core_path.'string_api.php' );
?>
<?php
	$f_bug_id = gpc_get_int( 'bug_id' );

	# grab the user id currently logged in
	$t_user_id	= auth_get_current_user_id();
	$c_bug_id		= (integer)$f_bug_id;

 	if ( !access_has_bug_level( config_get( 'private_bugnote_threshold' ), $f_bug_id ) ) {
 		$t_restriction = 'AND view_state=' . VS_PUBLIC;
 	} else {
 		$t_restriction = '';
 	}

	$t_bugnote_table		= config_get( 'mantis_bugnote_table' );
	$t_bugnote_text_table	= config_get( 'mantis_bugnote_text_table' );
	# get the bugnote data
	$t_bugnote_order = current_user_get_pref( 'bugnote_order' );

	$query = "SELECT *
			FROM $t_bugnote_table
			WHERE bug_id='$c_bug_id' $t_restriction
			ORDER BY date_submitted $t_bugnote_order";
	$result = db_query($query);
	$num_notes = db_num_rows($result);
?>

<?php # Bugnotes BEGIN ?>
<br />
<table class="width100" cellspacing="1">
<?php
	# no bugnotes
	if ( 0 == $num_notes ) {
?>
<tr>
	<td class="print" colspan="2">
		<?php echo lang_get( 'no_bugnotes_msg' ) ?>
	</td>
</tr>
<?php } else { # print bugnotes ?>
<tr>
	<td class="form-title" colspan="2">
		<?php echo lang_get( 'bug_notes_title' ) ?>
	</td>
</tr>
<?php
	for ( $i=0; $i < $num_notes; $i++ ) {
		# prefix all bugnote data with v3_
		$row = db_fetch_array( $result );
		extract( $row, EXTR_PREFIX_ALL, 'v3' );
		$v3_date_submitted = date( config_get( 'normal_date_format' ), ( db_unixtimestamp( $v3_date_submitted ) ) );
		$v3_last_modified = date( config_get( 'normal_date_format' ), ( db_unixtimestamp( $v3_last_modified ) ) );

		# grab the bugnote text and id and prefix with v3_
		$query = "SELECT note, id
				FROM $t_bugnote_text_table
				WHERE id='$v3_bugnote_text_id'";
		$result2 = db_query( $query );
		$v3_note = db_result( $result2, 0, 0 );
		$v3_bugnote_text_id = db_result( $result2, 0, 1 );

		$v3_note = string_display_links( $v3_note );
?>
<tr>
	<td class="print-spacer" colspan="2">
		<hr size="1" />
	</td>
</tr>
<tr>
	<td class="nopad" valign="top" width="20%">
		<table class="hide" cellspacing="1">
		<tr>
			<td class="print">
				(<?php echo bugnote_format_id( $v3_id ) ?>)
			</td>
		</tr>
		<tr>
			<td class="print">
				<?php
				echo print_user( $v3_reporter_id );
				?>&nbsp;&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td class="print">
				<?php echo $v3_date_submitted ?>&nbsp;&nbsp;&nbsp;
				<?php if ( db_unixtimestamp( $v3_date_submitted ) != db_unixtimestamp( $v3_last_modified ) ) {
					echo '<br />(' . lang_get( 'edited_on').' '. $v3_last_modified . ')';
				} ?>
			</td>
		</tr>
		</table>
	</td>
	<td class="nopad" valign="top" width="85%">
		<table class="hide" cellspacing="1">
		<tr>
			<td class="print">
				<?php
					switch ( $v3_note_type ) {
						case REMINDER:
							echo '<div class="italic">' . lang_get( 'reminder_sent_to' ) . ': ';
							$v3_note_attr = substr( $v3_note_attr, 1, strlen( $v3_note_attr ) - 2 );
							$t_to = array();
							foreach ( explode( '|', $v3_note_attr ) as $t_recipient ) {
								$t_to[] = user_get_name( $t_recipient );
							}
							echo implode( ', ', $t_to ) . '</div><br />';
						default:
							echo $v3_note;
					}
				?>
			</td>
		</tr>
		</table>
	</td>
</tr>
<?php
		} # end for loop
	} # end else
?>
</table>
<?php # Bugnotes END ?>
