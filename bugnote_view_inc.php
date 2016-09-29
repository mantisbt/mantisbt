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

define( 'ENTRY_TYPE_NOTE', 'note' );
define( 'ENTRY_TYPE_ATTACHMENT', 'attachment' );

# grab the user id currently logged in
$t_user_id = auth_get_current_user_id();

# grab bug information
$t_bug_readonly = bug_is_readonly( $f_bug_id );

#precache access levels
access_cache_matrix_project( helper_get_current_project() );

# get the bugnote data
$t_bugnote_order = current_user_get_pref( 'bugnote_order' );
$t_bugnotes = bugnote_get_all_visible_bugnotes( $f_bug_id, $t_bugnote_order, 0, $t_user_id );
$t_show_time_tracking = access_has_bug_level( config_get( 'time_tracking_view_threshold' ), $f_bug_id );

# get attachments data
$t_fields = config_get( $t_fields_config_option );
$t_fields = columns_filter_disabled( $t_fields );
$t_show_attachments = in_array( 'attachments', $t_fields );

if ( $t_show_attachments ) {
	$t_attachments = file_get_visible_attachments( $f_bug_id );
} else {
	$t_attachments = array();
}

if( count( $t_bugnotes ) > 0 || count( $t_attachments ) > 0 ) {
	# access level thresholds
	$t_bugnote_user_edit_threshold = config_get( 'bugnote_user_edit_threshold' );
	$t_bugnote_user_delete_threshold = config_get( 'bugnote_user_delete_threshold' );
	$t_bugnote_user_change_view_state_threshold = config_get( 'bugnote_user_change_view_state_threshold' );
	$t_can_edit_all_bugnotes = access_has_bug_level( config_get( 'update_bugnote_threshold' ), $f_bug_id );
	$t_can_delete_all_bugnotes = access_has_bug_level( config_get( 'delete_bugnote_threshold' ), $f_bug_id );
	$t_can_change_view_state_all_bugnotes = $t_can_edit_all_bugnotes && access_has_bug_level( config_get( 'change_view_status_threshold' ), $f_bug_id );
}

$t_entries = array();

foreach( $t_attachments as $t_attachment ) {
	$t_entry = array(
		'type' => ENTRY_TYPE_ATTACHMENT,
		'timestamp' => $t_attachment['date_added'],
		'modified' => false,
		'last_modified' => $t_attachment['date_added'],
		'id' => $t_attachment['id'],
		'id_formatted' => $t_attachment['id'],
		'user_id' => $t_attachment['user_id'],
		'private' => false,
		'style' => 'bugnote-note',
		'attachment' => $t_attachment );

	$t_entry['can_edit'] = false;
	$t_entry['can_delete'] = !$t_bug_readonly;
	$t_entry['can_change_view_state'] = false;

	$t_entries[] = $t_entry;
}

foreach( $t_bugnotes as $t_bugnote ) {
	$t_entry = array(
		'type' => ENTRY_TYPE_NOTE,
		'timestamp' => $t_bugnote->date_submitted,
		'last_modified' => $t_bugnote->last_modified,
		'modified' => $t_bugnote->date_submitted != $t_bugnote->last_modified,
		'id' => $t_bugnote->id,
		'id_formatted' => bugnote_format_id( $t_bugnote->id ),
		'user_id' => $t_bugnote->reporter_id,
		'private' => $t_bugnote->view_state != VS_PUBLIC,
		'style' => 'bugnote-note',
		'attachments' => array(),
		'note' => $t_bugnote );

	if( $t_entry['private'] ) {
		$t_entry['style'] .= ' bugnote-private';
	} else {
		$t_entry['style'] .= ' bugnote-public';
	}

	if( TIME_TRACKING == $t_entry['note']->note_type ) {
		$t_entry['style'] .= ' bugnote-time-tracking';
	} else if( REMINDER == $t_entry['note']->note_type ) {
		$t_entry['style'] .= ' bugnote-reminder';
	}

	if( $t_bug_readonly ) {
		$t_can_edit_bugnote = false;
		$t_can_delete_bugnote = false;
		$t_can_change_view_state = false;
	} else {
		# check if the user can edit this bugnote
		if( $t_user_id == $t_entry['user_id'] ) {
			$t_can_edit_bugnote = access_has_bugnote_level( $t_bugnote_user_edit_threshold, $t_entry['id'] );
		} else {
			$t_can_edit_bugnote = $t_can_edit_all_bugnotes;
		}

		# check if the user can delete this bugnote
		if( $t_user_id == $t_entry['user_id'] ) {
			$t_can_delete_bugnote = access_has_bugnote_level( $t_bugnote_user_delete_threshold, $t_entry['id'] );
		} else {
			$t_can_delete_bugnote = $t_can_delete_all_bugnotes;
		}

		# check if the user can make this bugnote private
		if( $t_user_id == $t_entry['user_id'] ) {
			$t_can_change_view_state = access_has_bugnote_level( $t_bugnote_user_change_view_state_threshold, $t_entry['id'] );
		} else {
			$t_can_change_view_state = $t_can_change_view_state_all_bugnotes;
		}
	}

	$t_entry['can_edit'] = $t_can_edit_bugnote;
	$t_entry['can_delete'] = $t_can_delete_bugnote;
	$t_entry['can_change_view_state'] = $t_can_change_view_state;

	$t_entries[] = $t_entry;
}

/**
 * Sort bugnotes and attachments by timestamp then user_id.  If two entries have
 * the same timestamp and user id, then the note should be before the attachment.
 *
 * @param array $p_entries The array of entries.  The array will be updated.
 * @return void
 */
function entries_sort( &$p_entries ) {
	$t_order = config_get( 'bugnote_order' );
	usort( $p_entries, function( $a, $b ) use( $t_order ) {
		if( $a['timestamp'] < $b['timestamp'] ) {
			return $t_order == 'DESC' ? 1 : -1;
		}

		if( $a['timestamp'] > $b['timestamp'] ) {
			return $t_order == 'DESC' ? -1 : 1;
		}

		if( $a['user_id'] < $b['user_id'] ) {
			return -1;
		}

		if( $a['user_id'] > $b['user_id'] ) {
			return 1;
		}

		if( $a['type'] == ENTRY_TYPE_NOTE && $b['type'] == ENTRY_TYPE_ATTACHMENT ) {
			return -1;
		}

		if( $a['type'] == ENTRY_TYPE_ATTACHMENT && $b['type'] == ENTRY_TYPE_NOTE ) {
			return 1;
		}

		return 0;
	} );
}

/**
 * Combine entries that were submitted together in one entry.  A user can
 * submit N attachments along with a note.  In such case, we want to have
 * a single entry that shows the note followed by the attachments.
 *
 * @param array $p_entries The array of entries.
 * @return The updated array of entries.
 */
function entries_combine( $p_entries ) {
	define( 'TIMESPAN_TO_COMBINE_ATTACHMENTS_IN_SECS', 10 );

	$t_combined_entries = array();
	$t_last_entry = null;

	foreach( $p_entries as $t_entry ) {
		if( $t_last_entry != null ) {
			if( $t_last_entry['user_id'] == $t_entry['user_id'] &&
			    $t_last_entry['type'] == ENTRY_TYPE_NOTE &&
			    $t_entry['type'] == ENTRY_TYPE_ATTACHMENT &&
			    abs( $t_entry['timestamp'] - $t_last_entry['timestamp'] ) <= TIMESPAN_TO_COMBINE_ATTACHMENTS_IN_SECS ) {
			    $t_last_entry['attachments'][] = $t_entry['attachment'];
			} else {
				$t_combined_entries[] = $t_last_entry;
				$t_last_entry = $t_entry;
			}
		} else {
			$t_last_entry = $t_entry;
		}
	}

	if( $t_last_entry !== null ) {
		$t_combined_entries[] = $t_last_entry;
	}

	return $t_combined_entries;
}

entries_sort( $t_entries );
$t_entries = entries_combine( $t_entries );

# Pre-cache users
$t_users_to_cache = array();

foreach( $t_entries as $t_entry ) {
	$t_users_to_cache[$t_entry['user_id']] = true;
}

user_cache_array_rows( array_keys( $t_users_to_cache ) );

$t_num_entries = count( $t_entries );
?>

<?php # Bugnotes BEGIN ?>
<div class="col-md-12 col-xs-12">
<a id="bugnotes"></a>
<div class="space-10"></div>

<?php
$t_collapse_block = is_collapsed( 'bugnotes' );
$t_block_css = $t_collapse_block ? 'collapsed' : '';
$t_block_icon = $t_collapse_block ? 'fa-chevron-down' : 'fa-chevron-up';

?>
<div id="bugnotes" class="widget-box widget-color-blue2 <?php echo $t_block_css ?>">
<div class="widget-header widget-header-small">
	<h4 class="widget-title lighter">
	<i class="ace-icon fa fa-comments"></i>
		<?php echo lang_get( 'bug_notes_title' ) ?>
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
<?php
	# no bugnotes
	if( 0 == $t_num_entries ) {
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

	# Tokens for action buttons are created only once, if needed
	$t_security_token_state = null;
	$t_security_token_notes_delete = null;
	$t_security_token_attachments_delete = null;

	for( $i=0; $i < $t_num_entries; $i++ ) {
		$t_entry = $t_entries[$i];

		if( $t_entry['type'] == ENTRY_TYPE_NOTE && $t_entry['note']->time_tracking != 0 ) {
			$t_time_tracking_hhmm = db_minutes_to_hhmm( $t_bugnote->time_tracking );
			$t_total_time += $t_entry['note']->time_tracking;
		} else {
			$t_time_tracking_hhmm = '';
		}
?>
<tr class="bugnote" id="c<?php echo $t_entry['id'] ?>">
		<td class="category">
		<div class="pull-left padding-2"><?php print_avatar( $t_entry['user_id'] ); ?>
		</div>
		<div class="pull-left padding-2">
		<p class="no-margin">
			<?php
			echo '<i class="fa fa-user grey"></i> ';
			print_user( $t_entry['user_id'] );
			?>
		</p>
		<p class="no-margin small lighter">
			<i class="fa fa-clock-o grey"></i> <?php echo date( $t_normal_date_format, $t_entry['timestamp'] ); ?>
			<?php if( $t_entry['private'] ) { ?>
				&#160;&#160;
				<i class="fa fa-eye red"></i> <?php echo lang_get( 'private' ) ?>
			<?php } ?>
		</p>
		<p class="no-margin">
			<?php
			if( user_exists( $t_entry['user_id'] ) ) {
				$t_access_level = access_get_project_level( null, $t_entry['user_id'] );
				$t_label = layout_is_rtl() ? 'arrowed-right' : 'arrowed-in-right';
				echo '<span class="label label-sm label-default ' . $t_label . '">', access_level_get_string( $t_access_level ), '</span>';
			}
			?>
			&#160;
			<?php if( $t_entry['type'] == ENTRY_TYPE_NOTE ) { ?>
			<i class="fa fa-link grey"></i>
			<a rel="bookmark" href="<?php echo string_get_bugnote_view_url( $t_entry['note']->bug_id, $t_entry['note']->id) ?>" class="lighter" title="<?php echo lang_get( 'bugnote_link_title' ) ?>">
				<?php echo htmlentities( config_get_global( 'bugnote_link_tag' ) ) . $t_entry['id_formatted'] ?>
			</a>
			<?php } ?>
		</p>
		<?php
		if( $t_entry['modified'] ) {
			echo '<p class="no-margin small lighter"><i class="fa fa-retweet"></i> ' . lang_get( 'last_edited') . lang_get( 'word_separator' ) . date( $t_normal_date_format, $t_entry['last_modified'] ) . '</p>';
			$t_revision_count = bug_revision_count( $f_bug_id, REV_BUGNOTE, $t_entry['id'] );
			if( $t_revision_count >= 1 ) {
				$t_view_num_revisions_text = sprintf( lang_get( 'view_num_revisions' ), $t_revision_count );
				echo '<p class="no-margin"><span class="small bugnote-revisions-link"><a href="bug_revision_view_page.php?bugnote_id=' . $t_entry['id'] . '">' . $t_view_num_revisions_text . '</a></span></p>';
			}
		}
		?>
		<div class="clearfix"></div>
		<div class="space-2"></div>
		<div class="btn-group-sm">
		<?php
			# show edit button if the user is allowed to edit this bugnote
			if( $t_entry['can_edit'] ) {
				echo '<div class="pull-left">';
				print_form_button(
					'bugnote_edit_page.php',
					lang_get( 'bugnote_edit_link' ),
					array( 'bugnote_id' => $t_entry['id'] ),
					OFF );
				echo '</div>';
			}

			# show delete button if the user is allowed to delete this bugnote
			if( $t_entry['can_delete'] ) {
				echo '<div class="pull-left">';

				if( $t_entry['type'] == ENTRY_TYPE_NOTE ) {
					if ( !$t_security_token_notes_delete ) {
						$t_security_token_notes_delete = form_security_token( 'bugnote_delete' );
					}

					print_form_button(
						'bugnote_delete.php',
						lang_get( 'delete_link' ),
						array( 'bugnote_id' => $t_entry['id'] ),
						$t_security_token_notes_delete );
				} else {
					if ( !$t_security_token_attachments_delete ) {
						$t_security_token_attachments_delete = form_security_token( 'bug_file_delete' );
					}

					if( $t_entry['can_delete'] ) {
						echo lang_get( 'word_separator' ) . '&#160;&#160;';
						print_button( 'bug_file_delete.php?file_id=' . $t_entry['id'] . form_security_param( 'bug_file_delete', $t_security_token_attachments_delete ),
							lang_get( 'delete_link' ), 'btn-xs' );
					}
				}

				echo '</div>';
			}

			# show make public or make private button if the user is allowed to change the view state of this bugnote
			if( $t_entry['can_change_view_state'] ) {
				if ( !$t_security_token_state ) {
					$t_security_token_state = form_security_token( 'bugnote_set_view_state' );
				}

				echo '<div class="pull-left">';
				if( $t_entry['private'] ) {
					print_form_button(
						'bugnote_set_view_state.php',
						lang_get( 'make_public' ),
						array( 'private' => '0', 'bugnote_id' => $t_entry['id'] ),
						$t_security_token_state );
				} else {
					print_form_button(
						'bugnote_set_view_state.php',
						lang_get( 'make_private' ),
						array( 'private' => '1', 'bugnote_id' => $t_entry['id'] ),
						$t_security_token_state );
				}
				echo '</div>';
			}
		?>
		</div>
		</div>
	</td>
	<td class="<?php echo $t_entry['style'] ?>">
	<?php
		if( $t_entry['type'] == ENTRY_TYPE_NOTE ) {
			switch ( $t_entry['note']->note_type ) {
				case REMINDER:
					echo '<strong>';

					# List of recipients; remove surrounding delimiters
					$t_recipients = trim( $t_entry['note']->note_attr, '|' );

					if( empty( $t_recipients ) ) {
						echo lang_get( 'reminder_sent_none' );
					} else {
						# If recipients list's last char is not a delimiter, it was truncated
						$t_truncated = ( '|' != utf8_substr( $t_entry['note']->note_attr, utf8_strlen( $t_entry['note']->note_attr ) - 1 ) );

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
						echo '<div class="time-tracked label label-grey label-sm">', lang_get( 'time_tracking_time_spent' ) . ' ' . $t_time_tracking_hhmm, '</div>';
						echo '<div class="clearfix"></div>';
					}
					break;
			}

			echo string_display_links( $t_entry['note']->note );

			if( isset( $t_entry['attachments'] ) && count( $t_entry['attachments'] ) > 0 ) {
				echo '<br /><br />';
				foreach( $t_entry['attachments'] as $t_attachment ) {
					print_bug_attachment( $t_attachment );
				}
			}
		} else {
			print_bug_attachment( $t_entry['attachment'] );
		}
	?>
	</td>
</tr>
<?php event_signal( 'EVENT_VIEW_BUGNOTE', array( $f_bug_id, $t_entry['id'], $t_entry['private'] ) ); ?>
<tr class="spacer">
	<td colspan="2"></td>
</tr>
<?php
	} # end for loop

	event_signal( 'EVENT_VIEW_BUGNOTES_END', $f_bug_id );
?>
</table>
</div>
</div>
</div>
</div>
<?php

if( $t_total_time > 0 && $t_show_time_tracking ) {
	echo '<div class="time-tracking-total pull-right"><i class="ace-icon fa fa-clock-o bigger-110 red"></i> ', sprintf( lang_get( 'total_time_for_issue' ), '<span class="time-tracked">' . db_minutes_to_hhmm( $t_total_time ) . '</span>' ), '</div>';
}
?>
</div>

