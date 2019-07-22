<?php

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'columns_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'export_api.php' );

form_security_validate( 'manage_config_export_set' );

if( !export_can_manage_global_config() ) {
	access_denied();
}

$f_provider_id = gpc_get_string( 'provider_id' );
$f_action = gpc_get_string( 'action' );


$t_export_config = config_get( 'export_plugins', array(), ALL_USERS, ALL_PROJECTS );

switch( $f_action ) {
	case 'ENABLE':
		if( !isset( $t_export_config[$f_provider_id] ) ) {
			#TODO error
			exit();
		}
		$t_export_config[$f_provider_id]['enabled'] = true;
		break;

	case 'DISABLE':
		if( !isset( $t_export_config[$f_provider_id] ) ) {
			#TODO error
			exit();
		}
		$t_export_config[$f_provider_id]['enabled'] = false;
		break;

	case 'REMOVE':
		unset( $t_export_config[$f_provider_id] );
		break;

	default:
		error_parameters( 'ACTION' );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
}

config_set( 'export_plugins', $t_export_config, ALL_USERS, ALL_PROJECTS );

form_security_purge( 'manage_config_export_set' );

$t_redirect_url = 'manage_config_export_page.php';

layout_page_header();

layout_page_begin();

html_operation_successful( $t_redirect_url );

layout_page_end();
