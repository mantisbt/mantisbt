<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: proj_doc_delete.php,v 1.22 2004-10-08 19:57:46 thraxisp Exp $
	# --------------------------------------------------------
?>
<?php require_once( 'core.php' ) ?>
<?php
	# Check if project documentation feature is enabled.
	if ( OFF == config_get( 'enable_project_documentation' ) ) {
		access_denied();
	}

	access_ensure_project_level( config_get( 'upload_project_file_threshold' ) );

	$f_file_id = gpc_get_int( 'file_id' );

	file_delete( $f_file_id, 'project' );

	$t_redirect_url = 'proj_doc_page.php';
	print_header_redirect( $t_redirect_url );
?>
