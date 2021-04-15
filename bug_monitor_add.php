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
 * This file turns monitoring on or off for a bug for the current user
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses print_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );
require_api( 'error_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'print_api.php' );
require_api( 'utility_api.php' );

form_security_validate( 'bug_monitor_add' );

$f_bug_id = gpc_get_int( 'bug_id' );
$f_usernames = trim( gpc_get_string( 'user_to_add', '' ) );

$t_payload = array();

if( !is_blank( $f_usernames ) ) {
	$t_usernames = preg_split( '/[,|]/', $f_usernames, -1, PREG_SPLIT_NO_EMPTY );
	$t_users = array();
	foreach( $t_usernames as $t_username ) {
		$t_users[] = array( 'name_or_realname' => trim( $t_username ) );
	}

	$t_payload['users'] = $t_users;
}

$t_data = array(
	'query' => array( 'issue_id' => $f_bug_id ),
	'payload' => $t_payload,
);

$t_command = new MonitorAddCommand( $t_data );
$t_command->execute();

form_security_purge( 'bug_monitor_add' );

print_successful_redirect_to_bug( $f_bug_id );
