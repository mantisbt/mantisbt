<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: manage_proj_user_add.php,v 1.2 2003-02-11 09:08:46 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php require_once( 'core.php' ) ?>
<?php auth_ensure_user_authenticated() ?>
<?php
	# We should check both since we are in the project section and an
	#  admin might raise the first threshold and not realize they need
	#  to raise the second
	check_access( config_get( 'manage_project_threshold' ) );
	check_access( config_get( 'project_user_threshold' ) );

	$f_project_id	= gpc_get_int( 'project_id' );
	$f_user_id		= gpc_get_int_array( 'user_id', array() );
	$f_access_level	= gpc_get_int( 'access_level' );

	# Add user(s) to the current project
	foreach( $f_user_id as $t_user_id ) {
		project_add_user( $f_project_id, $t_user_id, $f_access_level );
	}

	print_header_redirect( 'manage_proj_edit_page.php?project_id=' . $f_project_id );
?>
