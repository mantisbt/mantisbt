<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: summary_graph_bypriority.php,v 1.17 2007-09-18 13:06:08 nuclear_eclipse Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'graph_api.php' );

	access_ensure_project_level( config_get( 'view_summary_threshold' ) );

	$f_width = gpc_get_int( 'width', 300 );
	$t_ar = config_get( 'graph_bar_aspect' );

	$t_token = token_get_value( TOKEN_GRAPH );
	$t_metrics = $t_token != null ? unserialize( $t_token ) : create_bug_enum_summary( lang_get( 'priority_enum_string' ), 'priority');

	graph_bar( $t_metrics, lang_get( 'by_priority' ), $f_width, $f_width * $t_ar );
?>
