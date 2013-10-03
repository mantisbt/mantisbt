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
 * @copyright Copyright (C) 2002 - 2013  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses check_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 */

if ( !defined( 'CHECK_WEBSERVICE_INC_ALLOW' ) ) {
	return;
}

/**
 * MantisBT Check API
 */
require_once( 'check_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );

check_print_section_header_row( 'Webservice' );

$t_library_path = config_get_global( 'library_path' );
$t_library_path = realpath( $t_library_path );
if ( $t_library_path[strlen( $t_library_path )-1] != '/' ) {
	$t_library_path .= '/';
}

check_print_test_warn_row(
	"Legacy <em>library/nusoap</em> folder must be deleted.",
	!is_dir( $t_library_path . 'nusoap' )
);

check_print_test_warn_row(
	'SOAP Extension Enabled',
	extension_loaded( 'soap' ),
	array( false => 'Enable the PHP SOAP extension.' )
);

