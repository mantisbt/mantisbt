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
 * @package MantisBT
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses check_api.php
 * @uses config_api.php
 * @uses utility_api.php
 */

if ( !defined( 'CHECK_EMAIL_INC_ALLOW' ) ) {
	return;
}

/**
 * MantisBT Check API
 */
require_once( 'check_api.php' );
require_api( 'config_api.php' );
require_api( 'utility_api.php' );

check_print_section_header_row( 'Email' );

$t_email_options = array(
	'webmaster_email',
	'from_email',
	'return_path_email'
);

foreach( $t_email_options as $t_email_option ) {
	$t_email = config_get_global( $t_email_option );
	check_print_test_row(
		$t_email_option . ' configuration option has a valid email address specified',
		!preg_match( '/@example\.com$/', $t_email ),
		array( false => 'You need to specify a valid email address for the ' . $t_email_option . ' configuration option.' )
	);
}

check_print_test_warn_row(
	'Email addresses are validated',
	config_get_global( 'validate_email' ),
	array( false => 'You have disabled email validation checks. For security reasons it is suggested that you enable these validation checks.' )
);

if( is_windows_server() && config_get_global( 'validate_email' ) && !config_get_global( 'use_ldap_email' ) ) {
	check_print_test_row(
		'Require at least PHP 5.3.0 if the server type is Windows and the check_mx_record configuration option is enabled',
		config_get_global( 'check_mx_record' ) && version_compare( phpversion(), '5.3.0', '>=' ),
		array( false => 'Versions of PHP prior to 5.3.0 are unable to check MX records when validating email addresses.' )
	);
}

check_print_test_row(
	'send_reset_password = ON requires allow_blank_email = OFF',
	!config_get_global( 'send_reset_password' ) || !config_get_global( 'allow_blank_email' )
);

check_print_test_row(
	'send_reset_password = ON requires enable_email_notification = ON',
	!config_get_global( 'send_reset_password' ) || config_get_global( 'enable_email_notification' )
);

check_print_test_row(
	'allow_signup = ON requires enable_email_notification = ON',
	!config_get_global( 'allow_signup' ) || config_get_global( 'enable_email_notification' )
);

check_print_test_row(
	'allow_signup = ON requires send_reset_password = ON',
	!config_get_global( 'allow_signup' ) || config_get_global( 'send_reset_password' )
);
