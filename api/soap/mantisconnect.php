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
 * A webservice interface to Mantis Bug Tracker
 *
 * @package MantisBT
 * @copyright Copyright 2004  Victor Boctor - vboctor@users.sourceforge.net
 * @copyright Copyright 2005  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

# Path to MantisBT is assumed to be the grand parent directory.  If this is not
# the case, then this variable should be set to the MantisBT path.
# This can not be a configuration option, then MantisConnect configuration
# needs MantisBT to be included first to make use of the constants and possibly
# configuration defined in MantisBT.
$t_mantis_dir = dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR;

# include Mantis files
$g_bypass_headers = true;
require_once( $t_mantis_dir . 'core.php' );

/**
 * Checks if the request for the webservice is a documentation request (eg:
 * WSDL) or an actual webservice call.
 * @return boolean
 */
function mci_is_webservice_call() {
	if( isset( $_SERVER['QUERY_STRING'] ) ) {
		$t_qs = $_SERVER['QUERY_STRING'];
	} else if( isset( $GLOBALS['QUERY_STRING'] ) ) {
		$t_qs = $GLOBALS['QUERY_STRING'];
	}

	if( isset( $t_qs ) && preg_match( '/wsdl/', $t_qs ) ) {
		return false;
	} else {
		return true;
	}
}

# If SOAP extension is not enabled, error out.
if( !extension_loaded( 'soap' ) ) {
	echo 'PHP SOAP extension is not enabled.';
	exit();
}

if( !mci_is_webservice_call() ) {
	# if we have a documentation request, do some tidy up to prevent lame bot loops e.g. /mantisconnect.php/mc_enum_etas/mc_project_get_versions/
	$t_parts = explode( 'mantisconnect.php/', strtolower( $_SERVER['SCRIPT_NAME'] ), 2 );
	if( isset( $t_parts[1] ) && (strlen( $t_parts[1] ) > 0 ) ) {
		echo 'This is not a SOAP webservice request, for documentation, see ' .  $t_parts[0] . 'mantisconnect.php';
		exit();
	}

	header( 'Content-Type: text/xml' );
	$t_wsdl = file_get_contents( 'mantisconnect.wsdl' );
	$t_wsdl = str_replace( 'http://www.mantisbt.org/bugs/api/soap/mantisconnect.php', config_get( 'path' ).'api/soap/mantisconnect.php', $t_wsdl );
	echo $t_wsdl;
	exit();
}

require_once( 'mc_core.php' );

$t_server = new SoapServer( 'mantisconnect.wsdl',
	array( 'features' => SOAP_USE_XSI_ARRAY_TYPE + SOAP_SINGLE_ELEMENT_ARRAYS )
);

$t_server->addFunction( SOAP_FUNCTIONS_ALL );
$t_server->handle();
