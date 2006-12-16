<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: adm_config_set.php,v 1.4 2006-12-16 19:54:58 vboctor Exp $
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