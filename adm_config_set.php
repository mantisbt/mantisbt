<?php
# MantisBT - a php based bugtracking system

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
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	form_security_validate( 'adm_config_set' );

	$f_user_id = gpc_get_int( 'user_id' );
	$f_project_id = gpc_get_int( 'project_id' );
	$f_config_option = gpc_get_string( 'config_option' );
	$f_type = gpc_get_string( 'type' );
	$f_value = gpc_get_string( 'value' );

	if ( is_blank( $f_config_option ) ) {
		error_parameters( 'config_option' );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	if ( $f_project_id == ALL_PROJECTS ) {
		access_ensure_global_level( config_get('set_configuration_threshold' ) );
	} else {
		project_ensure_exists( $f_project_id );
		access_ensure_project_level( config_get('set_configuration_threshold' ), $f_project_id );
	}

	# make sure that configuration option specified is a valid one.
	$t_not_found_value = '***CONFIG OPTION NOT FOUND***';
	if ( config_get_global( $f_config_option, $t_not_found_value ) === $t_not_found_value ) {
		error_parameters( $f_config_option );
		trigger_error( ERROR_CONFIG_OPT_NOT_FOUND, ERROR );
	}

	# make sure that configuration option specified can be stored in the database
	if ( !config_can_set_in_database( $f_config_option ) ) {
		error_parameters( $f_config_option );
		trigger_error( ERROR_CONFIG_OPT_CANT_BE_SET_IN_DB, ERROR );
	}

	if ( $f_type === 'default' ) {
		$t_config_global_value = config_get_global( $f_config_option );
		if ( is_string( $t_config_global_value ) ) {
			$t_type = 'string';
		} else if ( is_int( $t_config_global_value ) ) {
			$t_type = 'integer';
		} else { # note that we consider bool and float as complex.  We use ON/OFF for bools which map to numeric.
			$t_type = 'complex';
		}
	} else {
		$t_type = $f_type;
	}

	if ( $t_type === 'string' ) {
		$t_value = $f_value;
	} else if ( $t_type === 'integer' ) {
		$t_value = (integer)$f_value;
	} else {
		# We support these kind of variables here:
		# 1. constant values (like the ON/OFF switches): they are defined as constants mapping to numeric values
		# 2. simple arrays with the form: array( a, b, c, d )
		# 3. associative arrays with the form: array( a=>1, b=>2, c=>3, d=>4 )
		# TODO: allow multi-dimensional arrays, allow commas and => within strings
		$t_full_string = trim( $f_value );
		if ( preg_match('/array[\s]*\((.*)\)/s', $t_full_string, $t_match ) === 1 ) {
			// we have an array here
			$t_values = explode( ',', trim( $t_match[1] ) );
			foreach ( $t_values as $key => $value ) {
				if ( !trim( $value ) ) {
					continue;
				}
				$t_split = explode( '=>', $value, 2 );
				if ( count( $t_split ) == 2 ) {
					// associative array
					$t_new_key = constant_replace( trim( $t_split[0], " \t\n\r\0\x0B\"'" ) );
					$t_new_value = constant_replace( trim( $t_split[1], " \t\n\r\0\x0B\"'" ) );
					$t_value[ $t_new_key ] = $t_new_value;
				} else {
					// regular array
					$t_value[ $key ] = constant_replace( trim( $value, " \t\n\r\0\x0B\"'" ) );
				}
			}
		} else {
			// scalar value
			$t_value = constant_replace( trim( $t_full_string ) );
		}
	}

	config_set( $f_config_option, $t_value, $f_user_id, $f_project_id );

	form_security_purge( 'adm_config_set' );

	print_successful_redirect( 'adm_config_report.php' );


	/**
	 * Check if the passed string is a constant and return its value
	 */
	function constant_replace( $p_name ) {
		$t_result = $p_name;
		if ( is_string( $p_name ) && defined( $p_name ) ) {
			// we have a constant
			$t_result = constant( $p_name );
		}
		return $t_result;
	}
