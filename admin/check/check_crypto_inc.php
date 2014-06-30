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

check_print_test_row(
	'login_method is not equal to CRYPT_FULL_SALT',
	config_get_global( 'login_method' ) != CRYPT_FULL_SALT,
	array( false => 'Login method CRYPT_FULL_SALT has been deprecated and should not be used.' )
);

if( config_get_global( 'login_method' ) != LDAP ) {
	check_print_test_warn_row(
		'login_method is set to MD5',
		config_get_global( 'login_method' ) == MD5,
		'MD5 password encryption is currently the strongest password storage method supported by MantisBT.'
	);
}
