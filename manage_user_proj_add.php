<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: manage_user_proj_add.php,v 1.17 2003-02-11 09:08:48 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php require_once( 'core.php' ) ?>
<?php auth_ensure_user_authenticated() ?>
<?php
	check_access( config_get( 'manage_user_threshold' ) );
	check_access( config_get( 'project_user_threshold' ) );

	$f_user_id		= gpc_get_int( 'user_id' );
	$f_access_level	= gpc_get_int( 'access_level' );
	$f_project_id	= gpc_get_int_array( 'project_id', array() );

	foreach ( $f_project_id as $t_proj_id ) {
		project_add_user( $t_proj_id, $f_user_id, $f_access_level );
	}

	print_header_redirect( 'manage_user_edit_page.php?user_id=' . $f_user_id );
?>
