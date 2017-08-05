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
 * Prune old/unused users from database
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses form_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses user_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'database_api.php' );
require_api( 'form_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'user_api.php' );

form_security_validate( 'manage_user_prune' );

auth_reauthenticate();

access_ensure_global_level( config_get( 'manage_user_threshold' ) );


# Delete the users who have never logged in and are older than 1 week
$t_days_old = (int)7 * SECONDS_PER_DAY;

$t_query = 'SELECT id, access_level FROM {user}
		WHERE ( login_count = 0 ) AND ( date_created = last_visit ) AND ' .
		'( protected = 0 ) AND ' . db_helper_compare_time( db_param(), '>', 'date_created', $t_days_old );
$t_result = db_query( $t_query, array( db_now() ) );

if( !$t_result ) {
	trigger_error( ERROR_GENERIC, ERROR );
}

$t_count = db_num_rows( $t_result );

if( $t_count > 0 ) {
	helper_ensure_confirmed( lang_get( 'confirm_account_pruning' ),
							 lang_get( 'prune_accounts_button' ) );
}

for( $i=0; $i < $t_count; $i++ ) {
	$t_row = db_fetch_array( $t_result );
	# Don't prune accounts with a higher global access level than the current user
	if( access_has_global_level( $t_row['access_level'] ) ) {
		user_delete( $t_row['id'] );
	}
}

form_security_purge( 'manage_user_prune' );

print_header_redirect( 'manage_user_page.php' );
