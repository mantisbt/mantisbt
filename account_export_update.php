<?php

use Mantis\Export\TableWriterFactory;

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

form_security_validate( 'account_export_update' );
auth_ensure_user_authenticated();

$f_provider_id = gpc_get_string( 'provider_id' );
$f_action = gpc_get_string( 'action' );

$t_user_id = auth_get_current_user_id();
if( $f_action == 'DEFAULT' ) {
	$t_providers = TableWriterFactory::getProviders();
	if( !isset( $t_providers[$f_provider_id] ) ) {
		#TODO error
		exit();
	}
	$t_global_default = config_get( 'export_default_plugin', null, ALL_USERS, ALL_PROJECTS );
	$t_current_user_default = config_get( 'export_default_plugin', null, $t_user_id, ALL_PROJECTS );
	if( $t_current_user_default != $f_provider_id ) {
		if( $f_provider_id == $t_global_default ) {
			config_delete( 'export_default_plugin', $t_user_id, ALL_PROJECTS );
		} else {
			config_set( 'export_default_plugin', $f_provider_id, $t_user_id, ALL_PROJECTS );
		}
	}
} else {
	error_parameters( 'ACTION' );
	trigger_error( ERROR_EMPTY_FIELD, ERROR );
}

form_security_purge( 'account_export_update' );

$t_redirect_url = 'account_export_page.php';

layout_page_header();

layout_page_begin();

html_operation_successful( $t_redirect_url );

layout_page_end();
