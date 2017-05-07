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
 * This file contains configuration checks for cryptography issues
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses check_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 */

if( !defined( 'CHECK_CRYPTO_INC_ALLOW' ) ) {
	return;
}

# MantisBT Check API
require_once( 'check_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );

check_print_section_header_row( 'Cryptography' );

check_print_test_row(
	'Master salt value has been specified',
	strlen( config_get_global( 'crypto_master_salt' ) ) >= 16,
	array( false => 'The crypto_master_salt option needs to be specified in config_inc.php with a minimum string length of 16 characters.' )
);

# Login method checks
$t_login_method = config_get_global( 'login_method' );
$t_switch_to_method = ' You should switch to '
	. login_method_name( LOGIN_METHOD_HASH_BCRYPT )
	. ', which is currently the strongest password storage method supported by MantisBT.';

$t_deprecated_login_methods = array( LOGIN_METHOD_HASH_MD5, LOGIN_METHOD_HASH_CRYPT, LOGIN_METHOD_HASH_CRYPT_FULL_SALT, LOGIN_METHOD_PLAIN );
check_print_test_row(
	'Do not use an outdated login method',
	!in_array( $t_login_method, $t_deprecated_login_methods ),
	array( false => 'Login method ' . login_method_name( $t_login_method )
		. ' has been deprecated and should no longer be used for security reasons. '
		. $t_switch_to_method
	)
);

if( $t_login_method != LOGIN_METHOD_LDAP ) {
	$t_plain_text_login_methods = array( LOGIN_METHOD_PLAIN, LOGIN_METHOD_BASIC_AUTH, LOGIN_METHOD_HTTP_AUTH );
	check_print_test_warn_row(
		'Passwords should be stored encrypted in the database',
		!in_array( $t_login_method, $t_plain_text_login_methods ),
		'Login method ' . login_method_name( $t_login_method )
		. ' causes passwords to be stored in clear text. '
		. $t_switch_to_method
	);
}

/**
 * Returns the login method name
 * @param int $p_method One of the login methods constants
 * @return string Login method name
 */
function login_method_name( $p_method ) {
	switch( $p_method ) {
		case LOGIN_METHOD_PLAIN:                return 'PLAIN';
		case LOGIN_METHOD_BASIC_AUTH:           return 'BASIC_AUTH';
		case LOGIN_METHOD_HTTP_AUTH:            return 'HTTP_AUTH';
		case LOGIN_METHOD_HASH_CRYPT:           return 'CRYPT';
		case LOGIN_METHOD_HASH_CRYPT_FULL_SALT: return 'CRYPT_FULL_SALT';
		case LOGIN_METHOD_HASH_MD5:             return 'MD5';
		case LOGIN_METHOD_HASH_BCRYPT:          return 'HASH_BCRYPT';
		case LOGIN_METHOD_LDAP:                 return 'LDAP';
	}
	return 'UNKNOWN';
}
