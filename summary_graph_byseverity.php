<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'graph_api.php' );

	access_ensure_project_level( config_get( 'view_summary_threshold' ) );

	create_bug_enum_summary( lang_get( 'severity_enum_string' ), 'severity' );
	graph_bug_enum_summary( lang_get( 'by_severity' ) );
?>