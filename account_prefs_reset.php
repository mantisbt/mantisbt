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
 * CALLERS
 * This page is called from:
 * - account_prefs_inc.php
 *
 * EXPECTED BEHAVIOUR
 * - Reset the user's preferences to default values
 * - Redirect to account_prefs_page.php or another page, if given
 *
 * CALLS
 * This page conditionally redirects upon completion
 *
 * RESTRICTIONS & PERMISSIONS
 * - User must be authenticated
 *	- User must not be protected
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
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses user_pref_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );
require_api( 'user_pref_api.php' );

#============ Parameters ============
$f_user_id = gpc_get_int( 'user_id' );
$f_redirect_url	= string_sanitize_url( gpc_get_string( 'redirect_url', 'account_prefs_page.php' ) );

#============ Permissions ============
form_security_validate( 'account_prefs_reset' );

auth_ensure_user_authenticated();

user_ensure_exists( $f_user_id );

$t_user = user_get_row( $f_user_id );

# This page is currently called from the manage_* namespace and thus we
# have to allow authorised users to update the accounts of other users.
# TODO: split this functionality into manage_user_prefs_reset.php
if( auth_get_current_user_id() != $f_user_id ) {
	access_ensure_global_level( config_get( 'manage_user_threshold' ) );
	access_ensure_global_level( $t_user['access_level'] );
} else {
	# Protected users should not be able to update the preferences of their
	# user account. The anonymous user is always considered a protected
	# user and hence will also not be allowed to update preferences.
	user_ensure_unprotected( $f_user_id );
}

user_pref_reset( $f_user_id, ALL_PROJECTS );

form_security_purge( 'account_prefs_reset' );

print_header_redirect( $f_redirect_url, true, true );
