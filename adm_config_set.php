<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

	# --------------------------------------------------------
	# $Id: adm_config_set.php,v 1.4.2.1 2007-10-13 22:32:27 giallu Exp $
	# --------------------------------------------------------

	# This page stores the reported bug

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

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
		eval( '$t_value = ' . $f_value . ';' );
	}

	config_set( $f_config_option, $t_value, $f_user_id, $f_project_id );

	print_successful_redirect( 'adm_config_report.php' );
?>