<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	require_once( 'core.php' );

	login_cookie_check();

	# if user is below view summary threshold, then re-direct to mainpage.
	if ( !access_level_check_greater_or_equal( config_get( 'view_summary_threshold' ) ) ) {
		access_denied();
	}

	#centers the chart
	$center = 0.3;

	#position of the legend
	$poshorizontal = 0.03;
	$posvertical = 0.09;

	create_bug_enum_summary_pct( lang_get( 'status_enum_string' ), 'status');
	graph_bug_enum_summary_pct( lang_get( 'by_status_pct' ) );
?>