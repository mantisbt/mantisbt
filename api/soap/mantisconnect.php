<?php
# MantisConnect - A webservice interface to Mantis Bug Tracker
# 
# This program is distributed under dual licensing.  These include
# GPL and a commercial licenses.  Victor Boctor reserves the right to
# change the license of future releases.
# See docs/ folder for more details

/**
 * MantisConnect - A webservice interface to Mantis Bug Tracker
 *
 * @package MantisBT
 * @copyright Copyright 2004  Victor Boctor - vboctor@users.sourceforge.net
 * @link http://www.mantisbt.org
 */

set_include_path( '../../library' );

# Path to MantisBT is assumed to be the grand parent directory.  If this is not
# the case, then this variable should be set to the MantisBT path.
# This can not be a configuration option, then MantisConnect configuration
# needs MantisBT to be included first to make use of the constants and possibly
# configuration defined in MantisBT.
$t_mantis_dir = dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR;

# include Mantis files
$g_bypass_headers = true;
require_once( $t_mantis_dir . 'core.php' );

# constants and configurations
$t_current_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
require_once( $t_current_dir . 'mc_config_defaults_inc.php' );

$t_user_configs = $t_current_dir . 'mc_config_inc.php';
if( file_exists( $t_user_configs ) ) {
	require_once( $t_user_configs );
}

/**
 * Checks if the request for the webservice is a documentation request (eg:
 * WSDL) or an actual webservice call.
 */
function mci_is_webservice_call()
{
	global $QUERY_STRING;
	global $_SERVER;

	if ( isset( $_SERVER['QUERY_STRING'] ) ) {
		$t_qs = $_SERVER['QUERY_STRING'];
	} else if( isset( $GLOBALS['QUERY_STRING'] ) ) {
		$t_qs = $GLOBALS['QUERY_STRING'];
	} else if( isset( $QUERY_STRING ) && $QUERY_STRING != '' ) {
		$t_qs = $QUERY_STRING;
	}

	if ( isset( $t_qs ) && preg_match( '/wsdl/', $t_qs ) ){
		return false;
	} else {
		return true;
	}
}

# If SOAP extension is not enabled, error out.
if ( !extension_loaded( 'soap' ) ) {
	echo 'PHP SOAP extension is not enabled.';
	exit();
}

if ( !mci_is_webservice_call() ) {
	# if we have a documentation request, do some tidy up to prevent lame bot loops e.g. /mantisconnect.php/mc_enum_etas/mc_project_get_versions/
	$parts = explode ( 'mantisconnect.php/', strtolower($_SERVER['SCRIPT_NAME'] ), 2 );
	if (isset( $parts[1] ) && (strlen ( $parts[1] ) > 0 ) ) {
		echo 'This is not a SOAP webservice request, for documentation, see ' .  $parts[0] . 'mantisconnect.php';
		exit();
	}
	
	header('Content-Type: text/xml');
	$wsdl = file_get_contents('mantisconnect.wsdl');
	$wsdl = str_replace('http://www.mantisbt.org/bugs/api/soap/mantisconnect.php', config_get('path').'api/soap/mantisconnect.php', $wsdl);
	echo $wsdl;
	exit();
}

require_once( 'mc_core.php' );

$server = new SoapServer("mantisconnect.wsdl",
		array('features' => SOAP_USE_XSI_ARRAY_TYPE + SOAP_SINGLE_ELEMENT_ARRAYS)
);

$server->addFunction(SOAP_FUNCTIONS_ALL);
$server->handle();
