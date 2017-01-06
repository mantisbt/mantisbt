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
require_api( 'bug_activity_api.php' );
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

#precache access levels
access_cache_matrix_project( helper_get_current_project() );

$t_show_time_tracking = access_has_bug_level( config_get( 'time_tracking_view_threshold' ), $f_bug_id );

# get attachments data
if( !isset( $t_fields ) ) {
	$t_fields = config_get( $t_fields_config_option );
	$t_fields = columns_filter_disabled( $t_fields );
}

$t_show_attachments = in_array( 'attachments', $t_fields );

$t_result = bug_activity_get_all( $f_bug_id, /* include_attachments */ $t_show_attachments );
$t_activities = $t_result['activities'];
$t_bugnotes = $t_result['bugnotes'];

# Pre-cache users
$t_users_to_cache = array();

foreach( $t_activities as $t_activity ) {
	$t_users_to_cache[$t_activity['user_id']] = true;
}

user_cache_array_rows( array_keys( $t_users_to_cache ) );

$t_activities_count = count( $t_activities );
?>

<?php # Bugnotes BEGIN ?>
<div class="col-md-12 col-xs-12">
<a id="attachments"></a>
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
		<?php echo lang_get( 'activities_title' ) ?>
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
	if( 0 == $t_activities_count ) {
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

	for( $i=0; $i < $t_activities_count; $i++ ) {
		$t_activity = $t_activities[$i];

		if( $t_activity['type'] == ENTRY_TYPE_NOTE && $t_activity['note']->time_tracking != 0 ) {
			$t_time_tracking_hhmm = db_minutes_to_hhmm( $t_activity['note']->time_tracking );
			$t_total_time += $t_activity['note']->time_tracking;
		} else {
			$t_time_tracking_hhmm = '';
		}
?>
<tr class="bugnote" id="c<?php echo $t_activity['id'] ?>">
		<td class="category">
		<div class="pull-left padding-2"><?php print_avatar( $t_activity['user_id'] ); ?>
		</div>
		<div class="pull-left padding-2">
		<p class="no-margin">
			<?php
			echo '<i class="fa fa-user grey"></i> ';
			print_user( $t_activity['user_id'] );
			?>
		</p>
		<p class="no-margin small lighter">
			<i class="fa fa-clock-o grey"></i> <?php echo date( $t_normal_date_format, $t_activity['timestamp'] ); ?>
			<?php if( $t_activity['private'] ) { ?>
				&#160;&#160;
				<i class="fa fa-eye red"></i> <?php echo lang_get( 'private' ) ?>
			<?php } ?>
		</p>
		<p class="no-margin">
			<?php
			if( user_exists( $t_activity['user_id'] ) ) {
				$t_access_level = access_get_project_level( null, $t_activity['user_id'] );
				$t_label = layout_is_rtl() ? 'arrowed-right' : 'arrowed-in-right';
				echo '<span class="label label-sm label-default ' . $t_label . '">', access_level_get_string( $t_access_level ), '</span>';
			}
			?>
			&#160;
			<?php if( $t_activity['type'] == ENTRY_TYPE_NOTE ) { ?>
			<i class="fa fa-link grey"></i>
			<a rel="bookmark" href="<?php echo string_get_bugnote_view_url( $t_activity['note']->bug_id, $t_activity['note']->id) ?>" class="lighter" title="<?php echo lang_get( 'bugnote_link_title' ) ?>">
				<?php echo htmlentities( config_get_global( 'bugnote_link_tag' ) ) . $t_activity['id_formatted'] ?>
			</a>
			<?php } ?>
		</p>
		<?php
		if( $t_activity['modified'] ) {
			echo '<p class="no-margin small lighter"><i class="fa fa-retweet"></i> ' . lang_get( 'last_edited') . lang_get( 'word_separator' ) . date( $t_normal_date_format, $t_activity['last_modified'] ) . '</p>';
			$t_revision_count = bug_revision_count( $f_bug_id, REV_BUGNOTE, $t_activity['id'] );
			if( $t_revision_count >= 1 ) {
				$t_view_num_revisions_text = sprintf( lang_get( 'view_num_revisions' ), $t_revision_count );
				echo '<p class="no-margin"><span class="small bugnote-revisions-link"><a href="bug_revision_view_page.php?bugnote_id=' . $t_activity['id'] . '">' . $t_view_num_revisions_text . '</a></span></p>';
			}
		}
		?>
		<div class="clearfix"></div>
		<div class="space-2"></div>
		<div class="btn-group-sm">
		<?php
			# show edit button if the user is allowed to edit this bugnote
			if( $t_activity['can_edit'] ) {
				echo '<div class="pull-left">';
				print_form_button(
					'bugnote_edit_page.php',
					lang_get( 'bugnote_edit_link' ),
					array( 'bugnote_id' => $t_activity['id'] ),
					OFF );
				echo '</div>';
			}

			# show delete button if the user is allowed to delete this bugnote
			if( $t_activity['can_delete'] ) {
				echo '<div class="pull-left">';

				if( $t_activity['type'] == ENTRY_TYPE_NOTE ) {
					if ( !$t_security_token_notes_delete ) {
						$t_security_token_notes_delete = form_security_token( 'bugnote_delete' );
					}

					print_form_button(
						'bugnote_delete.php',
						lang_get( 'delete_link' ),
						array( 'bugnote_id' => $t_activity['id'] ),
						$t_security_token_notes_delete );
				} else {
					if ( !$t_security_token_attachments_delete ) {
						$t_security_token_attachments_delete = form_security_token( 'bug_file_delete' );
					}

					if( $t_activity['can_delete'] ) {
						print_link_button( 'bug_file_delete.php?file_id=' . $t_activity['id'] . form_security_param( 'bug_file_delete', $t_security_token_attachments_delete ),
							lang_get( 'delete_link' ), 'btn-xs' );
					}
				}

				echo '</div>';
			}

			# show make public or make private button if the user is allowed to change the view state of this bugnote
			if( $t_activity['can_change_view_state'] ) {
				if ( !$t_security_token_state ) {
					$t_security_token_state = form_security_token( 'bugnote_set_view_state' );
				}

				echo '<div class="pull-left">';
				if( $t_activity['private'] ) {
					print_form_button(
						'bugnote_set_view_state.php',
						lang_get( 'make_public' ),
						array( 'private' => '0', 'bugnote_id' => $t_activity['id'] ),
						$t_security_token_state );
				} else {
					print_form_button(
						'bugnote_set_view_state.php',
						lang_get( 'make_private' ),
						array( 'private' => '1', 'bugnote_id' => $t_activity['id'] ),
						$t_security_token_state );
				}
				echo '</div>';
			}
		?>
		</div>
		</div>
	</td>
	<td class="<?php echo $t_activity['style'] ?>">
	<?php
		if( $t_activity['type'] == ENTRY_TYPE_NOTE ) {
			switch ( $t_activity['note']->note_type ) {
				case REMINDER:
					echo '<strong>';

					# List of recipients; remove surrounding delimiters
					$t_recipients = trim( $t_activity['note']->note_attr, '|' );

					if( empty( $t_recipients ) ) {
						echo lang_get( 'reminder_sent_none' );
					} else {
						# If recipients list's last char is not a delimiter, it was truncated
						$t_truncated = ( '|' != utf8_substr( $t_activity['note']->note_attr, utf8_strlen( $t_activity['note']->note_attr ) - 1 ) );

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

			echo string_display_links( $t_activity['note']->note );

			if( isset( $t_activity['attachments'] ) && count( $t_activity['attachments'] ) > 0 ) {
				echo '<br /><br />';
			}
		} else {
			if ( !$t_security_token_attachments_delete ) {
				$t_security_token_attachments_delete = form_security_token( 'bug_file_delete' );
			}

			print_bug_attachment( $t_activity['attachment'], $t_security_token_attachments_delete );
		}

		if( isset( $t_activity['attachments'] ) && count( $t_activity['attachments'] ) > 0 ) {
			if ( !$t_security_token_attachments_delete ) {
				$t_security_token_attachments_delete = form_security_token( 'bug_file_delete' );
			}

			foreach( $t_activity['attachments'] as $t_attachment ) {
				print_bug_attachment( $t_attachment, $t_security_token_attachments_delete );
			}
		}
	?>
	</td>
</tr>
<?php
if( $t_activity['type'] == ENTRY_TYPE_NOTE ) {
	event_signal( 'EVENT_VIEW_BUGNOTE', array( $f_bug_id, $t_activity['id'], $t_activity['private'] ) );
}
?>
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

