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
	if( OFF == config_get_global( 'allow_signup' ) ) {
		return;
	}

	if( access_get_global_level() > config_get( 'default_new_account_access_level' ) ) {
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

	error_parameters( $t_antispam_max_event_count, $t_antispam_time_window_in_seconds );
	trigger_error( ERROR_SPAM_SUSPECTED, ERROR );
}
