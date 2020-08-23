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
 * Update bugnote data then redirect to the appropriate viewing page
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses bugnote_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses error_api.php
 * @uses event_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses print_api.php
 * @uses string_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'bugnote_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'error_api.php' );
require_api( 'event_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );

form_security_validate( 'bugnote_update' );

$f_bugnote_id	 = gpc_get_int( 'bugnote_id' );
$f_bugnote_text	 = gpc_get_string( 'bugnote_text', '' );
$f_time_tracking = gpc_get_string( 'time_tracking', '0:00' );

# Check if the current user is allowed to edit the bugnote
$t_user_id = auth_get_current_user_id();
$t_reporter_id = bugnote_get_field( $f_bugnote_id, 'reporter_id' );

if( $t_user_id == $t_reporter_id ) {
	access_ensure_bugnote_level( config_get( 'bugnote_user_edit_threshold' ), $f_bugnote_id );
} else {
	access_ensure_bugnote_level( config_get( 'update_bugnote_threshold' ), $f_bugnote_id );
}

# Check if the bug is readonly
$t_bug_id = bugnote_get_field( $f_bugnote_id, 'bug_id' );
if( bug_is_readonly( $t_bug_id ) ) {
	error_parameters( $t_bug_id );
	trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
}

$f_bugnote_text = trim( $f_bugnote_text ) . "\n";

bugnote_set_text( $f_bugnote_id, $f_bugnote_text );
bugnote_set_time_tracking( $f_bugnote_id, $f_time_tracking );

# Plugin integration
event_signal( 'EVENT_BUGNOTE_EDIT', array( $t_bug_id, $f_bugnote_id ) );

form_security_purge( 'bugnote_update' );

print_successful_redirect( string_get_bugnote_view_url( $t_bug_id, $f_bugnote_id ) );
