<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_relationship_graph_img.php,v 1.2 2005-02-12 20:01:04 jlatour Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'compress_api.php' );
	require_once( $t_core_path.'current_user_api.php' );
	require_once( $t_core_path.'relationship_graph_api.php' );

	# If relationship graphs were made disabled, we disallow any access to
	# this script.

	auth_ensure_user_authenticated();

	if ( ON != config_get( 'relationship_graph_enable' ) )
		access_denied();

	$f_bug_id		= gpc_get_int( 'bug_id' );
	$f_type			= gpc_get_string( 'graph', 'relation' );
	$f_orientation	= gpc_get_string( 'orientation', config_get( 'relationship_graph_orientation' ) );

	access_ensure_bug_level( VIEWER, $f_bug_id );

	$t_bug = bug_prepare_display( bug_get( $f_bug_id, true ) );

	compress_enable();

	$t_graph_relation = ( 'relation' == $f_type );
	$t_graph_horizontal = ( 'horizontal' == $f_orientation );

	if ( $t_graph_relation )
		$t_graph = relgraph_generate_rel_graph( $f_bug_id, $t_bug );
	else
		$t_graph = relgraph_generate_dep_graph( $f_bug_id, $t_bug, $t_graph_horizontal );

	relgraph_output_image( $t_graph );
?>
