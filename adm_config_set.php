<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: adm_config_set.php,v 1.1 2006-04-21 15:13:14 vboctor Exp $
	# --------------------------------------------------------

	# This page stores the reported bug

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	$f_user_id = gpc_get_int( 'user_id' );
	$f_project_id = gpc_get_int( 'project_id' );
	$f_config_option = gpc_get_string( 'config_option' );
	$f_type = gpc_get_string( 'type' );
	$f_value = gpc_get_string( 'value' );

	if ( $f_project_id == ALL_PROJECTS ) {
		access_ensure_global_level( config_get('set_configuration_threshold' ) );
	} else {
		access_ensure_project_level( config_get('set_configuration_threshold' ), $f_project_id );
	}

	if ( $f_type === 'string' ) {
		$t_value = $f_value;
	} else if ( $f_type === 'integer' ) {
		$t_value = (integer)$f_value;
	} else {
		eval( '$t_value = ' . $f_value . ';' );
	}

	config_set( $f_config_option, $t_value, $f_user_id, $f_project_id );
?>
<br />
<div align="center">
<?php
	print_successful_redirect( 'adm_config_report.php' );
?>
</div>