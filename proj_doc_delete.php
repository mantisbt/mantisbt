<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: proj_doc_delete.php,v 1.21 2004-10-05 14:59:08 thraxisp Exp $
	# --------------------------------------------------------
?>
<?php require_once( 'core.php' ) ?>
<?php
	# Check if project documentation feature is enabled.
	if ( OFF == config_get( 'enable_project_documentation' ) ) {
		access_denied();
	}

	# @@@ Need to obtain the project_id from the file once we have an API for that	
	access_ensure_project_level( MANAGER );

	$f_file_id = gpc_get_int( 'file_id' );

	file_delete( $f_file_id, 'project' );

	$t_redirect_url = 'proj_doc_page.php';
	print_header_redirect( $t_redirect_url );
?>
