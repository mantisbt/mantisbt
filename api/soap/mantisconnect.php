<?php
# MantisConnect - A webservice interface to Mantis Bug Tracker
# Copyright (C) 2004-2014  Victor Boctor - vboctor@users.sourceforge.net
# This program is distributed under dual licensing.  These include
# GPL and a commercial licenses.  Victor Boctor reserves the right to
# change the license of future releases.
# See docs/ folder for more details

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

if ( config_get('mc_use_nusoap') ) {
	// NuSOAP already performs compression,
	// so we prevent a double-compression.
	// See issue #11868 for details
	define( 'COMPRESSION_DISABLED', true);
	ini_set( 'zlib.output_compression', false );
	
	require_once( 'nusoap/nusoap.php' );
	
	# create server
	$l_oServer = new soap_server('mantisconnect.wsdl');

	// WS-I Basic Profile requires UTF-8 or UTF-16 as the encoding for interoperabilty
	// reasons.  This will correctly handle a large number of languages besides English.
	$l_oServer->xml_encoding = "UTF-8";
	$l_oServer->soap_defencoding = "UTF-8";
	$l_oServer->decode_utf8 = false;
	
	###
	###  IMPLEMENTATION
	###
	
	# pass incoming (posted) data
	if ( isset( $HTTP_RAW_POST_DATA ) ) {
		$t_input = $HTTP_RAW_POST_DATA;
	} else {
		$t_input = implode( "\r\n", file( 'php://input' ) );
	}
	
	# only include the MantisBT / MantisConnect related files, if the current
	# request is a webservice call (rather than webservice documentation request,
	# eg: WSDL).
	if ( mci_is_webservice_call() ) {
		require_once( 'mc_core.php' );
	} else {
		# if we have a documentation request, do some tidy up to prevent lame bot loops e.g. /mantisconnect.php/mc_enum_etas/mc_project_get_versions/
		$parts = explode ( 'mantisconnect.php/', strtolower($_SERVER['SCRIPT_NAME'] ), 2 );
		if (isset( $parts[1] ) && (strlen ( $parts[1] ) > 0 ) ) {
			echo 'This is not a SOAP webservice request, for documentation, see ' .  $parts[0] . 'mantisconnect.php';
			exit();
		}
	}
	
	# Execute whatever is requested from the webservice.
	$l_oServer->service( $t_input );
} else {
	
	require_once( 'mc_core.php' );
	
	$server = new SoapServer("mantisconnect.wsdl",
			array('features' => SOAP_USE_XSI_ARRAY_TYPE + SOAP_SINGLE_ELEMENT_ARRAYS)
	);
	$server->addFunction(SOAP_FUNCTIONS_ALL);
	$server->handle();
}
