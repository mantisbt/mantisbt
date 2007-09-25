<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: manage_custom_field_proj_add.php,v 1.2 2007-09-25 23:52:09 nuclear_eclipse Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	auth_reauthenticate();

	$f_field_id = gpc_get_int( 'field_id' );
	$f_project_id = gpc_get_int_array( 'project_id', array() );
	$f_sequence	= gpc_get_int( 'sequence' );

	$t_manage_project_threshold = config_get( 'manage_project_threshold' );

	foreach ( $f_project_id as $t_proj_id ) {
		if ( access_has_project_level( $t_manage_project_threshold, $t_proj_id ) ) {
			if ( !custom_field_is_linked( $f_field_id, $t_proj_id ) ) {
				custom_field_link( $f_field_id, $t_proj_id );
			}

			custom_field_set_sequence( $f_field_id, $t_proj_id, $f_sequence );
		}
	}

	print_header_redirect( 'manage_custom_field_edit_page.php?field_id=' . $f_field_id );
?>
