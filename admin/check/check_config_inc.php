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
 * Check Mantis config configuration
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses check_api.php
 */

if( !defined( 'CHECK_CONFIG_INC_ALLOW' ) ) {
	return;
}

# MantisBT Check API
require_once( 'check_api.php' );

check_print_section_header_row( 'Configuration' );

check_print_test_row( 'config_inc.php configuration file exists',
	file_exists( $g_config_path . 'config_inc.php' ),
	array( false => 'Please use <a href="install.php">install.php</a> to perform the initial installation of MantisBT.' )
);

check_print_test_row( 'config_inc.php must not be in MantisBT root folder',
	!file_exists( $g_absolute_path . 'config_inc.php' ),
	array( false => 'Move from MantisBT root folder to config folder.' )
);

check_print_test_row( 'custom_strings_inc.php must not be in MantisBT root folder',
	!file_exists( $g_absolute_path . 'custom_strings_inc.php' ),
	array( false => 'Move from MantisBT root folder to config folder.' )
);

check_print_test_row( 'custom_functions_inc.php must not be in MantisBT root folder',
	!file_exists( $g_absolute_path . 'custom_functions_inc.php' ),
	array( false => 'Move from MantisBT root folder to config folder.' )
);

check_print_test_row( 'custom_constants_inc.php must not be in MantisBT root folder',
	!file_exists( $g_absolute_path . 'custom_constants_inc.php' ),
	array( false => 'Move from MantisBT root folder to config folder.' )
);

check_print_test_row( 'custom_relationships_inc.php must not be in MantisBT root folder',
	!file_exists( $g_absolute_path . 'custom_relationships_inc.php' ),
	array( false => 'Move from MantisBT root folder to config folder.' )
);

check_print_test_row( 'api/soap/mc_config_inc.php is no longer supported',
	!file_exists( $g_absolute_path . 'api/soap/mc_config_inc.php' ),
	array( false => 'Move contents of api/soap/mc_config_inc.php into config/config_inc.php.' )
);

# Debugging / Developer Settings
check_print_test_warn_row( 'Check whether diagnostic logging is enabled',
	$g_log_level == LOG_NONE,
	array( false => 'Global Log Level should usually be set to LOG_NONE for production use' )
);

check_print_test_warn_row( 'Check whether log output is sent to end user',
	$g_log_destination !== 'page',
	array( false => "Diagnostics output destination is currently set to end-user's browser" )
);

check_print_test_warn_row( 'Detailed errors should be OFF',
	$g_show_detailed_errors == OFF,
	array( false => 'Setting show_detailed_errors = ON is a potential security hazard as it can expose sensitive information.' )
);

check_print_test_warn_row( 'Email debugging should be OFF',
	empty( $g_debug_email ),
	array( false => 'All notification e-mails will be sent to: ' . $g_debug_email )
);

check_print_test_row( 'Default move category must exists ("default_category_for_moves")',
	category_exists( config_get( 'default_category_for_moves' ) ),
	array( false => 'Issues moved may end up with invalid category id.' )
);

$t_field_options = array(
	'bug_report_page_fields',
	'bug_view_page_fields',
	'bug_update_page_fields'
);

foreach( $t_field_options as $t_field_option ) {
	$t_fields = config_get( $t_field_option, null, ALL_USERS, ALL_PROJECTS );
	check_print_test_warn_row(
		$t_field_option . ' configuration option does not contain "os_version"',
		!in_array ( 'os_version', $t_fields ),
		array( false => 'You need to replace "os_version" by "os_build" for the ' . $t_field_option . ' configuration option '
			. '(see issue <a href="https://mantisbt.org/bugs/view.php?id=26840">#26840</a>).')
	);
}

# Deprecated Settings
check_print_test_warn_row( 'Deprecated "limit_reporters" setting should no longer be used',
	$g_limit_reporters == OFF,
	array( false => 'Use "limit_view_unless_threshold" instead.' )
);

# Check that 'ldap_server' is a proper URI, starting with either ldap:// or ldaps://
$t_ldap_server = config_get_global( 'ldap_server' );
check_print_test_row(
	'"ldap_server" must be a valid, full LDAP URI',
	( preg_match( '~^ldaps?://~', $t_ldap_server ) == 1 ),
	array( false => '"ldap_server" must be a proper URI, starting with either "ldap://" or "ldaps://"' )
);

# Obsolete Settings
require_api( 'obsolete.php' );
