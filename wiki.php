<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2006  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: wiki.php,v 1.2 2006-08-26 03:34:28 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path . 'wiki_api.php' );

	$f_id = gpc_get_int( 'id' );
	$f_type = gpc_get_string( 'type', 'issue' );
	
	if ( $f_type == 'project' ) {
		if ( $f_id !== 0 ) {
			project_ensure_exists( $f_id );
		}

		$t_url = wiki_get_url_for_project( $f_id );
	} else {
		bug_ensure_exists( $f_id );
		$t_url = wiki_get_url_for_issue( $f_id );
	}

	print_header_redirect( $t_url );
?>
