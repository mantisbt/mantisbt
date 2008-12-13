<?php
# Mantis - a php based bugtracking system

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

/**
 * This include file prints out the list of bugnotes attached to the bug
 * $f_bug_id must be set and be set to the bug id
 *
 * @package MantisBT
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2009  Mantis Team   - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

$t_core_path = config_get( 'core_path' );

/**
 * Requires bugnote API
 */
require_once( $t_core_path.'current_user_api.php' );

# grab the user id currently logged in
$t_user_id = auth_get_current_user_id();

# get the bugnote data
$t_bugnote_order = current_user_get_pref( 'bugnote_order' );
$t_bugnotes = bugnote_get_all_visible_bugnotes( $f_bug_id, $t_bugnote_order, 0, $t_user_id );
	
#precache users
$t_bugnote_users = array();
foreach($t_bugnotes as $t_bugnote) {
	$t_bugnote_users[] = $t_bugnote->reporter_id;
}
user_cache_array_rows( $t_bugnote_users );
	
#precache access levels
if ( isset( $g_project_override ) ) { 
	access_cache_matrix_project( $g_project_override );
} else {
	access_cache_matrix_project( helper_get_current_project() );
}
	
$num_notes = sizeof( $t_bugnotes );
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
	event_signal( 'EVENT_VIEW_BUGNOTES_START', array( $f_bug_id, $t_bugnotes ) );

	$t_normal_date_format = config_get( 'normal_date_format' );
	$t_total_time = 0;

	for ( $i=0; $i < $num_notes; $i++ ) {
		$t_bugnote = $t_bugnotes[$i];

		if ( $t_bugnote->date_submitted != $t_bugnote->last_modified )
			$t_bugnote_modified = true;
		else
			$t_bugnote_modified = false;
		
		$t_bugnote_id_formatted = bugnote_format_id( $t_bugnote->id );

		if ( 0 != $t_bugnote->time_tracking ) {
			$t_time_tracking_hhmm = db_minutes_to_hhmm( $t_bugnote->time_tracking );
			$t_bugnote->note_type = TIME_TRACKING;   // for older entries that didn't set the type @@@PLR FIXME
			$t_total_time += $t_bugnote->time_tracking;
		} else {
			$t_time_tracking_hhmm = '';
		}

		if ( VS_PRIVATE == $t_bugnote->view_state ) {
			$t_bugnote_css		= 'bugnote-private';
			$t_bugnote_note_css	= 'bugnote-note-private';
		} else {
			$t_bugnote_css		= 'bugnote-public';
			$t_bugnote_note_css	= 'bugnote-note-public';
		}
?>
<tr class="bugnote" id="c<?php echo $t_bugnote->id ?>">
        <td class="<?php echo $t_bugnote_css ?>">
		<?php if ( ON  == config_get("show_avatar") ) print_avatar( $t_bugnote->reporter_id ); ?>
		<span class="small">(<?php echo $t_bugnote_id_formatted ?>)</span><br />
		<?php
			echo print_user( $t_bugnote->reporter_id );
		?>
		<span class="small"><?php
			if ( user_exists( $t_bugnote->reporter_id ) ) {
				$t_access_level = access_get_project_level( null, (int)$t_bugnote->reporter_id );
				echo '(', get_enum_element( 'access_levels', $t_access_level ), ')';
			} 
		?></span>
		<?php if ( VS_PRIVATE == $t_bugnote->view_state ) { ?>
		<span class="small">[ <?php echo lang_get( 'private' ) ?> ]</span>
		<?php } ?>
		<br />
		<span class="small"><?php echo date( $t_normal_date_format, $t_bugnote->date_submitted ); ?></span><br />
		<?php
		if ( $t_bugnote_modified ) {
			echo '<span class="small">'.lang_get( 'edited_on').' '.date( $t_normal_date_format, $t_bugnote->last_modified ).'</span><br />';
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
					( ( $t_bugnote->reporter_id == $t_user_id ) && ( ON == config_get( 'bugnote_allow_user_edit_delete' ) ) ) ) {
					$t_can_edit_note = true;
					$t_can_delete_note = true;
				}

				# users above update_bugnote_threshold should be able to edit this bugnote
				if ( $t_can_edit_note || access_has_bug_level( config_get( 'update_bugnote_threshold' ), $f_bug_id ) ) {
					print_button( 'bugnote_edit_page.php?bugnote_id='.$t_bugnote->id, lang_get( 'bugnote_edit_link' ) );
				}

				# users above delete_bugnote_threshold should be able to delete this bugnote
				if ( $t_can_delete_note || access_has_bug_level( config_get( 'delete_bugnote_threshold' ), $f_bug_id ) ) {
					echo " ";
					print_button( 'bugnote_delete.php?bugnote_id='.$t_bugnote->id, lang_get( 'delete_link' ) );
				}

				if ( access_has_bug_level( config_get( 'private_bugnote_threshold' ), $f_bug_id ) &&
					access_has_bug_level( config_get( 'change_view_status_threshold' ), $f_bug_id ) ) {
					if ( VS_PRIVATE == $t_bugnote->view_state ) {
						echo " ";
						print_button('bugnote_set_view_state.php?private=0&amp;bugnote_id='.$t_bugnote->id, lang_get( 'make_public' ));
					} else {
						echo " ";
						print_button('bugnote_set_view_state.php?private=1&amp;bugnote_id='.$t_bugnote->id, lang_get( 'make_private' ));
					}
				}
			}
		?>
		</div>
	</td>
	<td class="<?php echo $t_bugnote_note_css ?>">
		<?php
			switch ( $t_bugnote->note_type ) {
				case REMINDER:
					echo '<em>' . lang_get( 'reminder_sent_to' ) . ': ';
					$t_note_attr = substr( $t_bugnote->note_attr, 1, strlen( $t_bugnote->note_attr ) - 2 );
					$t_to = array();
					foreach ( explode( '|', $t_note_attr ) as $t_recipient ) {
						$t_to[] = prepare_user_name( $t_recipient );
					}
					echo implode( ', ', $t_to ) . '</em><br /><br />';
				case TIME_TRACKING:
					if ( access_has_bug_level( config_get( 'time_tracking_view_threshold' ), $f_bug_id ) ) {
						echo '<b><big>', $t_time_tracking_hhmm, '</big></b><br /><br />';
					}
					break;
			}

			echo string_display_links( $t_bugnote->note );;
		?>
	</td>
</tr>
<?php event_signal( 'EVENT_VIEW_BUGNOTE', array( $f_bug_id, $t_bugnote->id, VS_PRIVATE == $t_bugnote->view_state ) ); ?>
<tr class="spacer">
	<td colspan="2"></td>
</tr>
<?php
		} # end for loop

		if ( $t_total_time > 0 && access_has_bug_level( config_get( 'time_tracking_view_threshold' ), $f_bug_id ) ) {
			echo '<tr><td colspan="2">', sprintf ( lang_get( 'total_time_for_issue' ), db_minutes_to_hhmm( $t_total_time ) ), '</td></tr>';
		}
	} # end else

	event_signal( 'EVENT_VIEW_BUGNOTES_END', $f_bug_id );
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
