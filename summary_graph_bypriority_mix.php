<?php
	require_once( 'core.php' );

	login_cookie_check();

	# if user is below view summary threshold, then re-direct to mainpage.
	if ( !access_level_check_greater_or_equal( config_get( 'view_summary_threshold' ) ) ) {
		access_denied();
	}

	$height = 80;

	if ( ON == config_get( 'customize_attributes' ) ) {
		# to be deleted when moving to manage_project_page.php	
		$t_project_id = '0000000';

		# custom attributes insertion
		attribute_insert( 'priority', $t_project_id, 'global' );
		attribute_insert( 'priority', $t_project_id, 'str' ) ;
	}

	enum_bug_group( lang_get( 'priority_enum_string' ), 'priority');
	graph_group( lang_get( 'by_priority_mix' ) );
?>