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
 * This page stores the reported bug
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses error_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'error_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'utility_api.php' );

form_security_validate( 'adm_config_set' );

$f_user_id = gpc_get_int( 'user_id' );
$f_project_id = gpc_get_int( 'project_id' );
$f_config_option = trim( gpc_get_string( 'config_option' ) );
$f_type = gpc_get_string( 'type' );
$f_value = gpc_get_string( 'value' );
$f_original_user_id = gpc_get_int( 'original_user_id' );
$f_original_project_id = gpc_get_int( 'original_project_id' );
$f_original_config_option = gpc_get_string( 'original_config_option' );
$f_edit_action = gpc_get_string( 'action' );


if( is_blank( $f_config_option ) ) {
	error_parameters( 'config_option' );
	trigger_error( ERROR_EMPTY_FIELD, ERROR );
}

access_ensure_global_level( config_get( 'set_configuration_threshold' ) );

if( $f_project_id != ALL_PROJECTS ) {
	project_ensure_exists( $f_project_id );
}

# make sure that configuration option specified is a valid one.
$t_not_found_value = '***CONFIG OPTION NOT FOUND***';
if( config_get( $f_config_option, $t_not_found_value ) === $t_not_found_value ) {
	error_parameters( $f_config_option );
	trigger_error( ERROR_CONFIG_OPT_NOT_FOUND, ERROR );
}

# make sure that configuration option specified can be stored in the database
if( !config_can_set_in_database( $f_config_option ) ) {
	error_parameters( $f_config_option );
	trigger_error( ERROR_CONFIG_OPT_CANT_BE_SET_IN_DB, ERROR );
}

if( !config_can_delete( $f_config_option ) ) {
	error_parameters( $f_config_option );
	# @TODO define an error code for values that cant be set in DB, nor config_inc
	trigger_error( ERROR_CONFIG_OPT_CANT_BE_SET_IN_DB, ERROR );
}


# For 'default', behavior is based on the global variable's type
# If value is empty, process as per default to ensure proper typecast
if( $f_type == CONFIG_TYPE_DEFAULT || empty( $f_value ) ) {
	$t_config_global_value = config_get_global( $f_config_option );
	if( is_string( $t_config_global_value ) ) {
		$t_type = CONFIG_TYPE_STRING;
	} else if( is_int( $t_config_global_value ) ) {
		$t_type = CONFIG_TYPE_INT;
	} else if( is_float( $t_config_global_value ) ) {
		$t_type = CONFIG_TYPE_FLOAT;
	} else {
		# note that we consider bool and float as complex.
		# We use ON/OFF for bools which map to numeric.
		$t_type = CONFIG_TYPE_COMPLEX;
	}
} else {
	$t_type = $f_type;
}

# Parse the value
# - Strings are returned as-is
# - Empty values are typecast as appropriate
$t_value = $f_value;
if( $t_type != CONFIG_TYPE_STRING ) {
	try {
		if( !empty( $f_value ) ) {
			$t_parser = new ConfigParser( $f_value );
			$t_value = $t_parser->parse( ConfigParser::EXTRA_TOKENS_IGNORE );
		}

		switch( $t_type ) {
			case CONFIG_TYPE_INT:
				$t_value = (int)$t_value;
				break;
			case CONFIG_TYPE_FLOAT:
				$t_value = (float)$t_value;
				break;
		}
	}
	catch (Exception $e) {
		error_parameters( $f_config_option, $e->getMessage() );
		trigger_error(ERROR_CONFIG_OPT_BAD_SYNTAX, ERROR);
	}
}

if( 'action_edit' === $f_edit_action ){
	# EDIT action doesn't keep original if key values are different.
	if ( $f_original_config_option !== $f_config_option
			|| $f_original_user_id !== $f_user_id
			|| $f_original_project_id !== $f_project_id ){
		config_delete( $f_original_config_option, $f_original_user_id, $f_original_project_id );
		}
}

config_set( $f_config_option, $t_value, $f_user_id, $f_project_id );

form_security_purge( 'adm_config_set' );

print_successful_redirect( 'adm_config_report.php' );
