<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: manage_proj_delete.php,v 1.28 2004-12-14 20:37:07 marcelloscata Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	$f_project_id = gpc_get_int( 'project_id' );

	access_ensure_project_level( config_get( 'delete_project_threshold' ), $f_project_id );

	$t_project_name = project_get_name( $f_project_id );

	helper_ensure_confirmed( lang_get( 'project_delete_msg' ) .
			'<br/>' . lang_get( 'project_name' ) . ': ' . $t_project_name,
			lang_get( 'project_delete_button' ) );

	project_delete( $f_project_id );

	# Don't leave the current project set to a deleted project -
	#  set it to All Projects
	if ( helper_get_current_project() == $f_project_id ) {
		helper_set_current_project( ALL_PROJECTS );
	}

    $t_redirect_url = 'manage_proj_page.php';
	print_header_redirect( $t_redirect_url );
?>
