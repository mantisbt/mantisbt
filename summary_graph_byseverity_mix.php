<?php
	require_once( 'core.php' );

	login_cookie_check();

	# if user is below view summary threshold, then re-direct to mainpage.
	if ( !access_level_check_greater_or_equal( config_get( 'view_summary_threshold' ) ) ) {
		access_denied();
	}

	$height = 100;

	if ( ON == config_get( 'customize_attributes' ) ) {
		# to be deleted when moving to manage_project_page.php	
		$t_project_id = '0000000';

		# custom attributes insertion
		attribute_insert( 'severity', $t_project_id, 'global' );
		attribute_insert( 'severity', $t_project_id, 'str' ) ;
	}

	enum_bug_group( lang_get( 'severity_enum_string' ), 'severity' );
	graph_group( lang_get( 'by_severity_mix' ) );
?>