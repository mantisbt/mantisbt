<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: manage_user_proj_delete.php,v 1.16 2003-02-15 10:25:17 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php require_once( 'core.php' ) ?>
<?php auth_ensure_user_authenticated() ?>
<?php
	$f_project_id	= gpc_get_int( 'project_id' );
	$f_user_id		= gpc_get_int( 'user_id' );

	access_ensure_global_level( config_get( 'manage_user_threshold' ) );
	access_ensure_project_level( config_get( 'project_user_threshold' ), $f_project_id );

	$result = project_remove_user( $f_project_id, $f_user_id );

	print_header_redirect( 'manage_user_edit_page.php?user_id=' .$f_user_id );
?>
