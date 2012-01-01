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
 */

if ( !defined( 'CHECK_CONFIG_INC_ALLOW' ) ) {
	return;
}

/**
 * MantisBT Check API
 */
require_once( 'check_api.php' );

check_print_section_header_row( 'Configuration' );

check_print_test_row( 'config_inc.php configuration file exists',
	file_exists( dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'config_inc.php' ),
	array( false => 'Please use <a href="install.php">install.php</a> to perform the initial installation of MantisBT.' )
);

check_print_test_warn_row( 'Check whether diagnostic logging is enabled',
	$g_log_level == LOG_NONE,
	array( false => 'Global Log Level should usually be set to LOG_NONE for production use' )
);

check_print_test_warn_row( 'Check whether log output is sent to end user',
	!($g_log_destination == 'firebug' || $g_log_destination == 'page'),
	array( false => 'Diagnostic output destination is currently sent to end users browser' )
);

require_api( 'obsolete.php' );
