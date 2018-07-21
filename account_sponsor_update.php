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
 * This page updates a user's sponsorships
 * If an account is protected then changes are forbidden
 * The page gets redirected back to account_page.php
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
 * @uses config_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses sponsorship_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'config_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'sponsorship_api.php' );

if( !config_get( 'enable_sponsorship' ) ) {
	trigger_error( ERROR_SPONSORSHIP_NOT_ENABLED, ERROR );
}

form_security_validate( 'account_sponsor_update' );

auth_ensure_user_authenticated();

$f_bug_list = gpc_get_string( 'buglist', '' );
$t_bug_list = explode( ',', $f_bug_list );

foreach( $t_bug_list as $t_bug ) {
	list( $t_bug_id, $t_sponsor_id ) = explode( ':', $t_bug );
	$c_bug_id = (int)$t_bug_id;

	bug_ensure_exists( $c_bug_id ); # dies if bug doesn't exist

	access_ensure_bug_level( config_get( 'handle_sponsored_bugs_threshold' ), $c_bug_id ); # dies if user can't handle bug

	$t_bug = bug_get( $c_bug_id );
	$t_sponsor = sponsorship_get( (int)$t_sponsor_id );

	$t_new_payment = gpc_get_int( 'sponsor_' . $c_bug_id . '_' . $t_sponsor->id, $t_sponsor->paid );
	if( $t_new_payment != $t_sponsor->paid ) {
		sponsorship_update_paid( $t_sponsor_id, $t_new_payment );
	}
}

form_security_purge( 'account_sponsor_update' );

$t_redirect_url = 'account_sponsor_page.php';
layout_page_header( null, $t_redirect_url );

layout_page_begin();

html_operation_successful( $t_redirect_url, lang_get( 'payment_updated' ) );

layout_page_end();
