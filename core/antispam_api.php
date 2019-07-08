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

require_api( 'access_api.php' );
require_api( 'history_api.php' );

use Mantis\Exceptions\ClientException;

/**
 * Antispam API
 *
 * @package CoreAPI
 * @subpackage AntispamAPI
 * @copyright Copyright 2015 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses history_api.php
 */

/**
 * Triggers an error if the current user is suspected to be a spammer.
 * This should be run before actions like adding issues or issue notes. If the
 * user is determined to demonstrate spammy behavior, this method will trigger an
 * error and exit the script.
 */
function antispam_check() {
	if( !auth_signup_enabled() ) {
		return;
	}

	if( access_get_global_level() > auth_signup_access_level() ) {
		return;
	}

	$t_antispam_max_event_count = config_get( 'antispam_max_event_count' );
	if( $t_antispam_max_event_count == 0 ) {
		return;
	}

	# Make sure user has at least one more event to add before exceeding the limit, which will happen
	# after this method returns.
	$t_antispam_time_window_in_seconds = config_get( 'antispam_time_window_in_seconds' );
	if( history_count_user_recent_events( $t_antispam_time_window_in_seconds ) < $t_antispam_max_event_count ) {
		return;
	}

	throw new ClientException(
		"Hit rate limit threshold",
		ERROR_SPAM_SUSPECTED,
		array( $t_antispam_max_event_count, $t_antispam_time_window_in_seconds )
	);
}

/**
 * Checks a note for html/bbcode hyperlinks and raw URLs.
 * This should be run before adding public issue notes from anonymous uses.
 * If the note is determined to contain spammy text, this method will trigger
 * an error and exit the script.
 */
function antispam_note_check( $p_bugnote_text, $p_private ) {
	global $g_allow_anonymous_login, $g_anonymous_account;
	if ( OFF == $g_allow_anonymous_login ) {
		return;
	}
	if ( $p_private ) {
		return;
	}
	$e_user_id = auth_get_current_user_id();
	$e_username = user_get_username( $e_user_id );
	if ( $e_username != $g_anonymous_account ) {
		return;
	}
	$t_limit = false;
	if ( strpos($p_bugnote_text, '<a href=') !== false ) {
		$t_limit = true;
	} else if ( strpos($p_bugnote_text, '[url=') !== false ) {
		$t_limit = true;
	} else if ( substr($p_bugnote_text, 0, 4) === 'http' ) {
		$t_limit = true;
	} else if ( strpos($p_bugnote_text, ' ') !== false ) {
		$t_count = 0;
		$t_msg_arr = explode(' ', $p_bugnote_text);
		foreach ( $t_msg_arr as $t_word ) {
			if ( substr(trim($t_word), 0, 4) === 'http' ) {
				$t_count++;
			}
		}
		if ( $t_count > 3 || $t_count >= count($t_msg_arr) - 1 ) {
			$t_limit = true;
		}
	}
	if ( !$t_limit ) {
		return;
	}

	throw new ClientException(
	"Anonymous note blocked",
		ERROR_SPAM_ANONURL,
		array()
	);
}
