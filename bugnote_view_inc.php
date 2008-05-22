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
	# $Id: bugnote_view_inc.php,v 1.46.2.1 2007-10-13 22:33:10 giallu Exp $
	# --------------------------------------------------------
?>
<?php
	# This include file prints out the list of bugnotes attached to the bug
	# $f_bug_id must be set and be set to the bug id
?>
<?php
	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'current_user_api.php' );
?>
<?php
	# grab the user id currently logged in
	$t_user_id = auth_get_current_user_id();

	if ( !access_has_bug_level( config_get( 'private_bugnote_threshold' ), $f_bug_id ) ) {
		$t_restriction = 'AND ( view_state=' . VS_PUBLIC . ' OR reporter_id = ' . $t_user_id . ')';
	} else {
		$t_restriction = '';
	}

	$t_bugnote_table		= config_get( 'mantis_bugnote_table' );
	$t_bugnote_text_table	= config_get( 'mantis_bugnote_text_table' );
	$t_bugnote_order		= current_user_get_pref( 'bugnote_order' );

	# get the bugnote data
	$query = "SELECT *
			FROM $t_bugnote_table
			WHERE bug_id='$f_bug_id' $t_restriction
			ORDER BY date_submitted $t_bugnote_order, id $t_bugnote_order";
	$result = db_query( $query );
	$num_notes = db_num_rows( $result );
?>

<?php # Bugnotes BEGIN ?>
<a name="bugnotes" id="bugnotes" /><br />

<?php 
	collapse_open( 'bugnotes' );
?>
<table class="width100" cellspacing="1">
<?php
	# no bugnotes
	if ( 0 == $num_notes ) {
?>
<tr>
	<td class="center" colspan="2">
		<?php echo lang_get( 'no_bugnotes_msg' ) ?>
	</td>
</tr>
<?php } else { # print bugnotes ?>
<tr>
	<td class="form-title" colspan="2">
<?php
		collapse_icon( 'bugnotes' ); ?>
		<?php echo lang_get( 'bug_notes_title' ) ?>
	</td>
</tr>
<?php
	$t_normal_date_format = config_get( 'normal_date_format' );
	$t_total_time = 0;

	for ( $i=0; $i < $num_notes; $i++ ) {
		# prefix all bugnote data with v3_
		$row = db_fetch_array( $result );
		extract( $row, EXTR_PREFIX_ALL, 'v3' );
		if ( db_unixtimestamp( $v3_date_submitted ) != db_unixtimestamp( $v3_last_modified ) )
			$t_bugnote_modified = true;
		else
			$t_bugnote_modified = false;

		$v3_date_submitted = date( $t_normal_date_format, ( db_unixtimestamp( $v3_date_submitted ) ) );
		$v3_last_modified = date( $t_normal_date_format, ( db_unixtimestamp( $v3_last_modified ) ) );

		# grab the bugnote text and id and prefix with v3_
		$query = "SELECT note
				FROM $t_bugnote_text_table
				WHERE id='$v3_bugnote_text_id'";
		$result2 = db_query( $query );
		$row = db_fetch_array( $result2 );

		$v3_note = $row['note'];
		$v3_note = string_display_links( $v3_note );
		$t_bugnote_id_formatted = bugnote_format_id( $v3_id );

		if ( 0 != $v3_time_tracking ) {
			$v3_time_tracking_hhmm = db_minutes_to_hhmm( $v3_time_tracking );
			$v3_note_type = TIME_TRACKING;   // for older entries that didn't set the type
			$t_total_time += $v3_time_tracking;
		} else {
			$v3_time_tracking_hhmm = '';
		}

		if ( VS_PRIVATE == $v3_view_state ) {
			$t_bugnote_css		= 'bugnote-private';
			$t_bugnote_note_css	= 'bugnote-note-private';
		} else {
			$t_bugnote_css		= 'bugnote-public';
			$t_bugnote_note_css	= 'bugnote-note-public';
		}
?>
<tr class="bugnote" id="c<?php echo $v3_id ?>">
        <td class="<?php echo $t_bugnote_css ?>">
		<?php if ( ON  == config_get("show_avatar") ) print_avatar( $v3_reporter_id ); ?>
		<span class="small">(<?php echo $t_bugnote_id_formatted ?>)</span><br />
		<?php
			echo print_user( $v3_reporter_id );
		?>
		<span class="small"><?php
			if ( user_exists( $v3_reporter_id ) ) {
				$t_access_level = access_get_project_level( null, $v3_reporter_id );
				echo '(', get_enum_element( 'access_levels', $t_access_level ), ')';
			} 
		?></span>
		<?php if ( VS_PRIVATE == $v3_view_state ) { ?>
		<span class="small">[ <?php echo lang_get( 'private' ) ?> ]</span>
		<?php } ?>
		<br />
		<span class="small"><?php echo $v3_date_submitted ?></span><br />
		<?php
		if ( $t_bugnote_modified ) {
			echo '<span class="small">'.lang_get( 'edited_on').' '.$v3_last_modified.'</span><br />';
		}
		?>
		<br /><div class="small">
		<?php
			# bug must be open to be editable
			if ( !bug_is_readonly( $f_bug_id ) ) {
				$t_can_edit_note = false;
				$t_can_delete_note = false;

				# admins and the bugnote creator can edit/delete this bugnote
				if ( ( access_has_bug_level( config_get( 'manage_project_threshold' ), $f_bug_id ) ) ||
					( ( $v3_reporter_id == $t_user_id ) && ( ON == config_get( 'bugnote_allow_user_edit_delete' ) ) ) ) {
					$t_can_edit_note = true;
					$t_can_delete_note = true;
				}

				# users above update_bugnote_threshold should be able to edit this bugnote
				if ( $t_can_edit_note || access_has_bug_level( config_get( 'update_bugnote_threshold' ), $f_bug_id ) ) {
					print_button( 'bugnote_edit_page.php?bugnote_id='.$v3_id, lang_get( 'bugnote_edit_link' ) );
				}

				# users above delete_bugnote_threshold should be able to delete this bugnote
				if ( $t_can_delete_note || access_has_bug_level( config_get( 'delete_bugnote_threshold' ), $f_bug_id ) ) {
					echo " ";
					print_button( 'bugnote_delete.php?bugnote_id='.$v3_id, lang_get( 'delete_link' ) );
				}

				if ( access_has_bug_level( config_get( 'private_bugnote_threshold' ), $f_bug_id ) &&
					access_has_bug_level( config_get( 'change_view_status_threshold' ), $f_bug_id ) ) {
					if ( VS_PRIVATE == $v3_view_state ) {
						echo " ";
						print_button('bugnote_set_view_state.php?private=0&amp;bugnote_id='.$v3_id, lang_get( 'make_public' ));
					} else {
						echo " ";
						print_button('bugnote_set_view_state.php?private=1&amp;bugnote_id='.$v3_id, lang_get( 'make_private' ));
					}
				}
			}
		?>
		</div>
	</td>
	<td class="<?php echo $t_bugnote_note_css ?>">
		<?php
			switch ( $v3_note_type ) {
				case REMINDER:
					echo '<em>' . lang_get( 'reminder_sent_to' ) . ': ';
					$v3_note_attr = substr( $v3_note_attr, 1, strlen( $v3_note_attr ) - 2 );
					$t_to = array();
					foreach ( explode( '|', $v3_note_attr ) as $t_recipient ) {
						$t_to[] = prepare_user_name( $t_recipient );
					}
					echo implode( ', ', $t_to ) . '</em><br /><br />';
				case TIME_TRACKING:
					if ( access_has_bug_level( config_get( 'time_tracking_view_threshold' ), $f_bug_id ) ) {
						echo '<b><big>', $v3_time_tracking_hhmm, '</big></b><br /><br />';
					}
					break;
			}

			echo $v3_note;
		?>
	</td>
</tr>
<tr class="spacer">
	<td colspan="2"></td>
</tr>
<?php
		} # end for loop

		if ( $t_total_time > 0 && access_has_bug_level( config_get( 'time_tracking_view_threshold' ), $f_bug_id ) ) {
			echo '<tr><td colspan="2">', sprintf ( lang_get( 'total_time_for_issue' ), db_minutes_to_hhmm( $t_total_time ) ), '</td></tr>';
		}
	} # end else
?>
</table>

<?php 
	collapse_closed( 'bugnotes' );
?>

<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<?php collapse_icon( 'bugnotes' ); ?>
		<?php echo lang_get( 'bug_notes_title' ) ?>
	</td>
</tr>
</table>
<?php
	collapse_end( 'bugnotes' );
?>

<?php # Bugnotes END ?>
