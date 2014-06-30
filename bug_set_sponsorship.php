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
 * Set sponsorship on a bug
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
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses sponsorship_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'sponsorship_api.php' );
require_api( 'user_api.php' );
require_api( 'utility_api.php' );

form_security_validate( 'bug_set_sponsorship' );

# anonymous users are not allowed to sponsor issues
if( current_user_is_anonymous() ) {
	access_denied();
}

$f_bug_id	= gpc_get_int( 'bug_id' );
$f_amount	= gpc_get_int( 'amount' );

$t_bug = bug_get( $f_bug_id, true );
if( $t_bug->project_id != helper_get_current_project() ) {
	# in case the current project is not the same project of the bug we are viewing...
	# ... override the current project. This to avoid problems with categories and handlers lists etc.
	$g_project_override = $t_bug->project_id;
}

if( config_get( 'enable_sponsorship' ) == OFF ) {
	trigger_error( ERROR_SPONSORSHIP_NOT_ENABLED, ERROR );
}

access_ensure_bug_level( config_get( 'sponsor_threshold' ), $f_bug_id );

helper_ensure_confirmed(
	sprintf( lang_get( 'confirm_sponsorship' ), $f_bug_id, sponsorship_format_amount( $f_amount ) ),
	lang_get( 'sponsor_issue' ) );

if( $f_amount == 0 ) {
	# if amount == 0, delete sponsorship by current user (if any)
	$t_sponsorship_id = sponsorship_get_id( $f_bug_id );
	if( $t_sponsorship_id !== false ) {
		sponsorship_delete( $t_sponsorship_id );
	}
} else {
	# add sponsorship
	$t_user = auth_get_current_user_id();
	if( is_blank( user_get_email( $t_user ) ) ) {
		trigger_error( ERROR_SPONSORSHIP_SPONSOR_NO_EMAIL, ERROR );
	} else {
		$t_sponsorship = new SponsorshipData;
		$t_sponsorship->bug_id = $f_bug_id;
		$t_sponsorship->user_id = $t_user;
		$t_sponsorship->amount = $f_amount;

		sponsorship_set( $t_sponsorship );
	}
}

form_security_purge( 'bug_set_sponsorship' );

print_header_redirect_view( $f_bug_id );
