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
 * Bug Activity API
 *
 * @package CoreAPI
 * @subpackage BugActivityAPI
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses bugnote_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses user_api.php
 */

require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'bugnote_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'user_api.php' );

define( 'ENTRY_TYPE_NOTE', 'note' );
define( 'ENTRY_TYPE_ATTACHMENT', 'attachment' );

/**
 * Get all activities associated with a bug including notes and attachments.
 *
 * @param int $p_bug_id                 The bug id
 * @param bool $p_include_attachments   Include the attachments
 * @return array An associative array with keys for bugnotes (all notes),
 *               attachments (all attachments), and activities
 *               (combined notes and attachments).
 */
function bug_activity_get_all( $p_bug_id, $p_include_attachments = true ) {
	$t_result = array();

	$t_user_id = auth_get_current_user_id();
	$t_bug_readonly = bug_is_readonly( $p_bug_id );

	if ( $p_include_attachments ) {
		$t_attachments = file_get_visible_attachments( $p_bug_id );
	} else {
		$t_attachments = array();
	}

	$t_result['attachments'] = $t_attachments;

	$t_bugnote_order = current_user_get_pref( 'bugnote_order' );
	$t_bugnotes = bugnote_get_all_visible_bugnotes( $p_bug_id, $t_bugnote_order, 0, $t_user_id );

	$t_result['bugnotes'] = $t_bugnotes;

	if( count( $t_attachments ) > 0 || count( $t_bugnotes ) > 0 ) {
		# access level thresholds
		$t_bugnote_user_edit_threshold = config_get( 'bugnote_user_edit_threshold' );
		$t_bugnote_user_delete_threshold = config_get( 'bugnote_user_delete_threshold' );
		$t_bugnote_user_change_view_state_threshold = config_get( 'bugnote_user_change_view_state_threshold' );
		$t_can_edit_all_bugnotes = access_has_bug_level( config_get( 'update_bugnote_threshold' ), $p_bug_id );
		$t_can_delete_all_bugnotes = access_has_bug_level( config_get( 'delete_bugnote_threshold' ), $p_bug_id );
		$t_can_change_view_state_all_bugnotes = $t_can_edit_all_bugnotes && access_has_bug_level( config_get( 'change_view_status_threshold' ), $p_bug_id );
	}

	$t_activities = array();
	$t_bugnote_attachments = array();

	foreach( $t_attachments as $t_attachment ) {
		$t_bugnote_id = (int)$t_attachment['bugnote_id'];
		if( $t_bugnote_id == 0 ) {
			$t_activity = array(
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
	
			$t_activity['can_edit'] = false;
			$t_activity['can_delete'] = !$t_bug_readonly && $t_attachment['can_delete'];
			$t_activity['can_change_view_state'] = false;
	
			$t_activities[] = $t_activity;
		} else {
			if( !isset( $t_bugnote_attachments[$t_bugnote_id] ) ) {
				$t_bugnote_attachments[$t_bugnote_id] = array();
			}

			$t_bugnote_attachments[$t_bugnote_id][] = $t_attachment;
		}
	}

	foreach( $t_bugnotes as $t_bugnote ) {
		$t_bugnote_id = (int)$t_bugnote->id;
		$t_activity = array(
			'type' => ENTRY_TYPE_NOTE,
			'timestamp' => $t_bugnote->date_submitted,
			'last_modified' => $t_bugnote->last_modified,
			'modified' => $t_bugnote->date_submitted != $t_bugnote->last_modified,
			'id' => $t_bugnote_id,
			'id_formatted' => bugnote_format_id( $t_bugnote_id ),
			'user_id' => $t_bugnote->reporter_id,
			'private' => $t_bugnote->view_state != VS_PUBLIC,
			'style' => 'bugnote-note',
			'attachments' => array(),
			'note' => $t_bugnote );

		if( isset( $t_bugnote_attachments[$t_bugnote_id] ) ) {
			$t_activity['attachments'] = $t_bugnote_attachments[$t_bugnote_id];
			unset( $t_bugnote_attachments[$t_bugnote_id] );
		}

		if( $t_activity['private'] ) {
			$t_activity['style'] .= ' bugnote-private';
		} else {
			$t_activity['style'] .= ' bugnote-public';
		}

		if( TIME_TRACKING == $t_activity['note']->note_type ) {
			$t_activity['style'] .= ' bugnote-time-tracking';
		} else if( REMINDER == $t_activity['note']->note_type ) {
			$t_activity['style'] .= ' bugnote-reminder';
		}

		if( $t_bug_readonly ) {
			$t_can_edit_bugnote = false;
			$t_can_delete_bugnote = false;
			$t_can_change_view_state = false;
		} else {
			# check if the user can edit this bugnote
			if( $t_user_id == $t_activity['user_id'] ) {
				$t_can_edit_bugnote = access_has_bugnote_level( $t_bugnote_user_edit_threshold, $t_activity['id'] );
			} else {
				$t_can_edit_bugnote = $t_can_edit_all_bugnotes;
			}

			# check if the user can delete this bugnote
			if( $t_user_id == $t_activity['user_id'] ) {
				$t_can_delete_bugnote = access_has_bugnote_level( $t_bugnote_user_delete_threshold, $t_activity['id'] );
			} else {
				$t_can_delete_bugnote = $t_can_delete_all_bugnotes;
			}

			# check if the user can make this bugnote private
			if( $t_user_id == $t_activity['user_id'] ) {
				$t_can_change_view_state = access_has_bugnote_level( $t_bugnote_user_change_view_state_threshold, $t_activity['id'] );
			} else {
				$t_can_change_view_state = $t_can_change_view_state_all_bugnotes;
			}
		}

		$t_activity['can_edit'] = $t_can_edit_bugnote;
		$t_activity['can_delete'] = $t_can_delete_bugnote;
		$t_activity['can_change_view_state'] = $t_can_change_view_state;

		$t_activities[] = $t_activity;
	}

	bug_activity_sort( $t_activities );
	$t_activities = bug_activity_combine( $t_activities );
	$t_result['activities'] = $t_activities;

	return $t_result;
}

/**
 * Sort bugnotes and attachments by timestamp then user_id.  If two entries have
 * the same timestamp and user id, then the note should be before the attachment.
 *
 * @param array $p_entries The array of entries.  The array will be updated.
 * @return void
 */
function bug_activity_sort( &$p_entries ) {
	$t_order = current_user_get_pref( 'bugnote_order' );
	usort( $p_entries, function( $a, $b ) use( $t_order ) {
		if( $a['timestamp'] < $b['timestamp'] ) {
			return $t_order == 'DESC' ? 1 : -1;
		}

		if( $a['timestamp'] > $b['timestamp'] ) {
			return $t_order == 'DESC' ? -1 : 1;
		}

		# same timestamp and same type, probably came from cloning an issue
		if( $a['timestamp'] == $b['timestamp'] && $a['type'] == $b['type'] ) {
			return (int)$a['id'] - (int)$b['id'];
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
 * Combine activities that were submitted together in one entry.  A user can
 * submit N attachments along with a note.  In such case, we want to have
 * a single entry that shows the note followed by the attachments.
 *
 * @param array $p_entries The array of entries.
 * @return array The updated array of activities.
 */
function bug_activity_combine( $p_entries ) {
	$t_threshold_in_seconds =
		config_get( 'issue_activity_note_attachments_seconds_threshold' );
	if ( $t_threshold_in_seconds < 1 ) {
		return $p_entries;
	}

	$t_combined_entries = array();
	$t_last_entry = null;

	foreach( $p_entries as $t_activity ) {
		if( $t_last_entry != null ) {
			if( $t_last_entry['user_id'] == $t_activity['user_id'] &&
				$t_activity['type'] == ENTRY_TYPE_ATTACHMENT &&
				abs( $t_activity['timestamp'] - $t_last_entry['timestamp'] ) <=
					$t_threshold_in_seconds ) {
				$t_last_entry['attachments'][] = $t_activity['attachment'];
			} else {
				$t_combined_entries[] = $t_last_entry;
				$t_last_entry = $t_activity;
			}
		} else {
			$t_last_entry = $t_activity;
		}
	}

	if( $t_last_entry !== null ) {
		$t_combined_entries[] = $t_last_entry;
	}

	return $t_combined_entries;
}

/**
 * Link attachments that are part of the bugnote activity.
 * This converts heuristic links into explicit ones.
 *
 * @param integer $p_bugnote_id The bugnote id.
 * @return void
 */
function bug_activity_bugnote_link_attachments( $p_bugnote_id ) {
	$t_bug_id = bugnote_get_field( $p_bugnote_id, 'bug_id' );
	$t_activities = bug_activity_get_all( $t_bug_id, /* include_attachments */ true );

	$t_files = array();
	foreach( $t_activities['activities'] as $t_activity ) {
		if( (int)$t_activity['id'] == (int)$p_bugnote_id ) {
			foreach( $t_activity['attachments'] as $t_attachment ) {
				file_link_to_bugnote( (int)$t_attachment['id'], $p_bugnote_id );
				$t_files[] = $t_attachment;
			}
		}
	}

	# explicitly link the attached files history events to the bugnote to control
	# there visibility based on the view state of the bugnote.
	foreach( $t_files as $t_file ) {
		history_link_file_to_bugnote( $t_bug_id, $t_file['display_name'], $p_bugnote_id );
	}
}

