<?php
	require_once( 'core.php' );

	login_cookie_check();

	# if user is below view summary threshold, then re-direct to mainpage.
	if ( !access_level_check_greater_or_equal( config_get( 'view_summary_threshold' ) ) ) {
		access_denied();
	}

	if ( ON == config_get( 'customize_attributes' ) ) {
		# to be deleted when moving to manage_project_page.php	
		$t_project_id = '0000000';

		# custom attributes insertion
		attributes_insert( 'priority', $t_project_id, 'global' );
		attributes_insert( 'priority', $t_project_id, 'str' ) ;
	}

	create_bug_enum_summary( lang_get( 'priority_enum_string' ), 'priority');
	graph_bug_enum_summary( lang_get( 'by_priority' ) );
?>