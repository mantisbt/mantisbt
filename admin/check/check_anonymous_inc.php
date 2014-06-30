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
 * This file contains configuration checks for anonymous access issues
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses check_api.php
 * @uses config_api.php
 * @uses user_api.php
 */

if( !defined( 'CHECK_ANONYMOUS_INC_ALLOW' ) ) {
	return;
}

# MantisBT Check API
require_once( 'check_api.php' );
require_api( 'config_api.php' );
require_api( 'user_api.php' );

check_print_section_header_row( 'Anonymous access' );

$t_anonymous_access_enabled = config_get_global( 'allow_anonymous_login' );
check_print_info_row(
	'Anonymous access is enabled',
	$t_anonymous_access_enabled ? 'Yes' : 'No'
);

if( !$t_anonymous_access_enabled ) {
	return;
}

$t_anonymous_account = config_get_global( 'anonymous_account' );
check_print_test_row(
	'anonymous_account configuration option is specified',
	$t_anonymous_account !== '',
	array(
		true => 'The account currently being used for anonymous access is: ' . htmlentities( $t_anonymous_account ),
		false => 'The anonymous_account configuration option must specify the username of an account to use for anonymous logins.'
	)
);

if( $t_anonymous_account === '' ) {
	return;
}

$t_anonymous_user_id = user_get_id_by_name( $t_anonymous_account );
check_print_test_row(
	'anonymous_account is a valid user account',
	$t_anonymous_user_id !== false,
	array( false => 'You need to specify a valid user account to use with the anonymous_account configuration options.' )
);

check_print_test_row(
	'anonymous_account user has the enabled flag set',
	user_is_enabled( $t_anonymous_user_id ),
	array( false => 'The anonymous user account must be enabled before it can be used.' )
);

check_print_test_row(
	'anonymous_account user has the protected flag set',
	user_get_field( $t_anonymous_user_id, 'protected' ),
	array( false => 'The anonymous user account needs to have the protected flag set to prevent anonymous users modifying the account.' )
);

check_print_test_row(
	'anonymous_account user does not have administrator permissions',
	!user_is_administrator( $t_anonymous_user_id ),
	array(
		true => 'The anonymous user account currently has an access level of: ' . htmlentities( get_enum_element( 'access_levels', user_get_access_level( $t_anonymous_user_id ) ) ),
		false => 'The anonymous user account should not have administrator level permissions.'
	)
);
