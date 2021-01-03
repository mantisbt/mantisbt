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
 * - print_account_menu()
 * - header redirects from account_*.php
 *
 * EXPECTED BEHAVIOUR
 * - Display the user's current preferences
 * - Allow the user to edit the preferences
 * - Provide the option of saving changes or resetting to default values
 *
 * CALLS
 * This page calls the following pages:
 * - acount_prefs_update.php  (to save changes)
 * - account_prefs_reset.php  (to reset preferences to default values)
 *
 * RESTRICTIONS & PERMISSIONS
 * - User must be authenticated
 * - The user's account must not be protected
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses current_user_api.php
 * @uses html_api.php
 * @uses lang_api.php
 */

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'current_user_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );

#============ Parameters ============
# (none)

#============ Permissions ============
auth_ensure_user_authenticated();

current_user_ensure_unprotected();

define( 'ACCOUNT_PREFS_INC_ALLOW', true );
include( dirname( __FILE__ ) . '/account_prefs_inc.php' );

layout_page_header( lang_get( 'change_preferences_link' ) );

layout_page_begin();
edit_account_prefs();
layout_page_end();
