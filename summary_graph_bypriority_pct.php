<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: summary_graph_bypriority_pct.php,v 1.15 2005-02-12 20:01:08 jlatour Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'graph_api.php' );

	access_ensure_project_level( config_get( 'view_summary_threshold' ) );

	$f_width = gpc_get_int( 'width', 300 );

	$f_token = gpc_get_int( 'token', 0 );
	if ( 0 == $f_token ) {
		$t_metrics = create_bug_enum_summary( lang_get( 'priority_enum_string' ), 'priority');
	} else {
		$t_metrics = graph_total_metrics( unserialize( token_get_value( $f_token ) ) );
	}
	graph_pie( $t_metrics, lang_get( 'by_priority_pct' ), $f_width, $f_width );
?>
