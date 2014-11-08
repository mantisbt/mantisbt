<?php
# Copyright (c) 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

form_security_validate( 'plugin_XmlImportExport_config' );
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

/**
 * Sets plugin config option if value is different from current/default
 * @param string $p_name  option name
 * @param string $p_value value to set
 * @return void
 */
function config_set_if_needed( $p_name, $p_value ) {
	if ( $p_value != plugin_config_get( $p_name ) ) {
		plugin_config_set( $p_name, $p_value );
	}
}

$t_redirect_url = plugin_page( 'config_page', true );
html_page_top( null, $t_redirect_url );

config_set_if_needed( 'import_threshold' , gpc_get_int( 'import_threshold' ) );
config_set_if_needed( 'export_threshold' , gpc_get_int( 'export_threshold' ) );

form_security_purge( 'plugin_XmlImportExport_config' );

html_operation_successful( $t_redirect_url );
html_page_bottom();
