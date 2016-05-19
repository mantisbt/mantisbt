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
 * @uses bug_api.php
 * @uses bug_revision_api.php
 * @uses bugnote_api.php
 * @uses collapse_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses database_api.php
 * @uses event_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses prepare_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses user_api.php
 */

if( !defined( 'BUGNOTE_VIEW_INC_ALLOW' ) ) {
	return;
}

require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'bug_revision_api.php' );
require_api( 'bugnote_api.php' );
require_api( 'collapse_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'database_api.php' );
require_api( 'event_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'prepare_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );

# grab the user id currently logged in
$t_user_id = auth_get_current_user_id();

#precache access levels
access_cache_matrix_project( helper_get_current_project() );

# get the bugnote data
$t_bugnote_order = current_user_get_pref( 'bugnote_order' );
$t_bugnotes = bugnote_get_all_visible_bugnotes( $f_bug_id, $t_bugnote_order, 0, $t_user_id );
$t_show_time_tracking = access_has_bug_level( config_get( 'time_tracking_view_threshold' ), $f_bug_id );

#precache users
$t_bugnote_users = array();
foreach( $t_bugnotes as $t_bugnote ) {
	$t_bugnote_users[] = $t_bugnote->reporter_id;
}
user_cache_array_rows( $t_bugnote_users );

$t_num_notes = count( $t_bugnotes );
?>

<?php # Bugnotes BEGIN ?>
<a id="bugnotes"></a><br />

<?php
	collapse_open( 'bugnotes' );
?>
<table class="bugnotes width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="2"><?php
		collapse_icon( 'bugnotes' );
		echo lang_get( 'bug_notes_title' ); ?>
	</td>
</tr>
<?php
	# no bugnotes
	if( 0 == $t_num_notes ) {
?>
<tr class="bugnotes-empty">
	<td class="center" colspan="2">
		<?php echo lang_get( 'no_bugnotes_msg' ) ?>
	</td>
</tr>
<?php }

	event_signal( 'EVENT_VIEW_BUGNOTES_START', array( $f_bug_id, $t_bugnotes ) );

	$t_normal_date_format = config_get( 'normal_date_format' );
	$t_total_time = 0;

	$t_bugnote_user_edit_threshold = config_get( 'bugnote_user_edit_threshold' );
	$t_bugnote_user_delete_threshold = config_get( 'bugnote_user_delete_threshold' );
	$t_bugnote_user_change_view_state_threshold = config_get( 'bugnote_user_change_view_state_threshold' );
	$t_can_edit_all_bugnotes = access_has_bug_level( config_get( 'update_bugnote_threshold' ), $f_bug_id );
	$t_can_delete_all_bugnotes = access_has_bug_level( config_get( 'delete_bugnote_threshold' ), $f_bug_id );
	$t_can_change_view_state_all_bugnotes = $t_can_edit_all_bugnotes && access_has_bug_level( config_get( 'change_view_status_threshold' ), $f_bug_id );

	# Tokens for action buttons are created only once, if needed
	$t_security_token_state = null;
	$t_security_token_delete = null;

	for( $i=0; $i < $t_num_notes; $i++ ) {
		$t_bugnote = $t_bugnotes[$i];

		if( $t_bugnote->date_submitted != $t_bugnote->last_modified ) {
			$t_bugnote_modified = true;
		} else {
			$t_bugnote_modified = false;
		}

		$t_bugnote_id_formatted = bugnote_format_id( $t_bugnote->id );

		if( $t_bugnote->time_tracking != 0 ) {
			$t_time_tracking_hhmm = db_minutes_to_hhmm( $t_bugnote->time_tracking );
			$t_total_time += $t_bugnote->time_tracking;
		} else {
			$t_time_tracking_hhmm = '';
		}

		if( VS_PRIVATE == $t_bugnote->view_state ) {
			$t_bugnote_css		= 'bugnote-private';
		} else {
			$t_bugnote_css		= 'bugnote-public';
		}

		if( TIME_TRACKING == $t_bugnote->note_type ) {
		    $t_bugnote_css    .= ' bugnote-time-tracking';
	    } else if( REMINDER == $t_bugnote->note_type ) {
	        $t_bugnote_css    .= ' bugnote-reminder';
		}
?>
<tr class="bugnote <?php echo $t_bugnote_css ?>" id="c<?php echo $t_bugnote->id ?>">
		<td class="bugnote-meta">
		<?php print_avatar( $t_bugnote->reporter_id ); ?>
		<p class="compact"><span class="small bugnote-permalink"><a rel="bookmark" href="<?php echo string_get_bugnote_view_url( $t_bugnote->bug_id, $t_bugnote->id ) ?>" title="<?php echo lang_get( 'bugnote_link_title' ) ?>"><?php echo htmlentities( config_get_global( 'bugnote_link_tag' ) ) . $t_bugnote_id_formatted ?></a></span></p>

		<p class="compact">
		<span class="bugnote-reporter">
		<?php
			print_user( $t_bugnote->reporter_id );
		?>
		<span class="small access-level"><?php
			if( user_exists( $t_bugnote->reporter_id ) ) {
				$t_access_level = access_get_project_level( null, (int)$t_bugnote->reporter_id );
				echo '(', access_level_get_string( $t_access_level ), ')';
			}
		?></span>
		</span>

		<?php if( VS_PRIVATE == $t_bugnote->view_state ) { ?>
		<span class="small bugnote-view-state">[ <?php echo lang_get( 'private' ) ?> ]</span>
		<?php } ?>
		</p>
		<p class="compact"><span class="small bugnote-date-submitted"><?php echo date( $t_normal_date_format, $t_bugnote->date_submitted ); ?></span></p>
		<?php
		if( $t_bugnote_modified ) {
			echo '<p class="compact"><span class="small bugnote-last-modified">' . lang_get( 'last_edited' ) . lang_get( 'word_separator' ) . date( $t_normal_date_format, $t_bugnote->last_modified ) . '</span></p>';
			$t_revision_count = bug_revision_count( $f_bug_id, REV_BUGNOTE, $t_bugnote->id );
			if( $t_revision_count >= 1 ) {
				$t_view_num_revisions_text = sprintf( lang_get( 'view_num_revisions' ), $t_revision_count );
				echo '<p class="compact"><span class="small bugnote-revisions-link"><a href="bug_revision_view_page.php?bugnote_id=' . $t_bugnote->id . '">' . $t_view_num_revisions_text . '</a></span></p>';
			}
		}
		?>
		<div class="small bugnote-buttons">
		<?php
			# bug must be open to be editable
			if( !bug_is_readonly( $f_bug_id ) ) {

				# check if the user can edit this bugnote
				if( $t_user_id == $t_bugnote->reporter_id ) {
					$t_can_edit_bugnote = access_has_bugnote_level( $t_bugnote_user_edit_threshold, $t_bugnote->id );
				} else {
					$t_can_edit_bugnote = $t_can_edit_all_bugnotes;
				}

				# check if the user can delete this bugnote
				if( $t_user_id == $t_bugnote->reporter_id ) {
					$t_can_delete_bugnote = access_has_bugnote_level( $t_bugnote_user_delete_threshold, $t_bugnote->id );
				} else {
					$t_can_delete_bugnote = $t_can_delete_all_bugnotes;
				}

				# check if the user can make this bugnote private
				if( $t_user_id == $t_bugnote->reporter_id ) {
					$t_can_change_view_state = access_has_bugnote_level( $t_bugnote_user_change_view_state_threshold, $t_bugnote->id );
				} else {
					$t_can_change_view_state = $t_can_change_view_state_all_bugnotes;
				}

				# show edit button if the user is allowed to edit this bugnote
				if( $t_can_edit_bugnote ) {
					print_button(
						'bugnote_edit_page.php',
						lang_get( 'bugnote_edit_link' ),
						array( 'bugnote_id' => $t_bugnote->id ),
						OFF );
				}

				# show delete button if the user is allowed to delete this bugnote
				if( $t_can_delete_bugnote ) {
					if ( !$t_security_token_delete ) {
						$t_security_token_delete = form_security_token( 'bugnote_delete' );
					}
					print_button(
						'bugnote_delete.php',
						lang_get( 'delete_link' ),
						array( 'bugnote_id' => $t_bugnote->id ),
						$t_security_token_delete );
				}

				# show make public or make private button if the user is allowed to change the view state of this bugnote
				if( $t_can_change_view_state ) {
					if ( !$t_security_token_state ) {
						$t_security_token_state = form_security_token( 'bugnote_set_view_state' );
					}
					if( VS_PRIVATE == $t_bugnote->view_state ) {
						print_button(
							'bugnote_set_view_state.php',
							lang_get( 'make_public' ),
							array( 'private' => '0', 'bugnote_id' => $t_bugnote->id ),
							$t_security_token_state );
					} else {
						print_button(
							'bugnote_set_view_state.php',
							lang_get( 'make_private' ),
							array( 'private' => '1', 'bugnote_id' => $t_bugnote->id ),
							$t_security_token_state );
					}
				}
			}
		?>
		</div>
	</td>
	<td class="bugnote-note">
		<?php
			switch( $t_bugnote->note_type ) {
				case REMINDER:
					echo '<strong>';

					# List of recipients; remove surrounding delimiters
					$t_recipients = trim( $t_bugnote->note_attr, '|' );

					if( empty( $t_recipients ) ) {
						echo lang_get( 'reminder_sent_none' );
					} else {
						# If recipients list's last char is not a delimiter, it was truncated
						$t_truncated = ( '|' != utf8_substr( $t_bugnote->note_attr, utf8_strlen( $t_bugnote->note_attr ) - 1 ) );

						# Build recipients list for display
						$t_to = array();
						foreach ( explode( '|', $t_recipients ) as $t_recipient ) {
							$t_to[] = prepare_user_name( $t_recipient );
						}

						echo lang_get( 'reminder_sent_to' ) . ': '
							. implode( ', ', $t_to )
							. ( $t_truncated ? ' (' . lang_get( 'reminder_list_truncated' ) . ')' : '' );
					}

					echo '</strong><br /><br />';
					break;

				case TIME_TRACKING:
					if( $t_show_time_tracking ) {
						echo '<div class="time-tracked">', lang_get( 'time_tracking_time_spent' ) . ' ' . $t_time_tracking_hhmm, '</div>';
					}
					break;
			}

			echo string_display_links( $t_bugnote->note );
		?>
	</td>
</tr>
<?php event_signal( 'EVENT_VIEW_BUGNOTE', array( $f_bug_id, $t_bugnote->id, VS_PRIVATE == $t_bugnote->view_state ) ); ?>
<tr class="spacer">
	<td colspan="2"></td>
</tr>
<?php
	} # end for loop

	event_signal( 'EVENT_VIEW_BUGNOTES_END', $f_bug_id );
?>
</table>
<?php

if( $t_total_time > 0 && $t_show_time_tracking ) {
	echo '<p class="time-tracking-total">', sprintf( lang_get( 'total_time_for_issue' ), '<span class="time-tracked">' . db_minutes_to_hhmm( $t_total_time ) . '</span>' ), '</p>';
}
	collapse_closed( 'bugnotes' );
?>

<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="2"><?php
		collapse_icon( 'bugnotes' );
		echo lang_get( 'bug_notes_title' ); ?>
	</td>
</tr>
</table>
<?php
	collapse_end( 'bugnotes' );
