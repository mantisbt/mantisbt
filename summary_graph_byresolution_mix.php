<?php
	require_once( 'core.php' );

	login_cookie_check();

	# if user is below view summary threshold, then re-direct to mainpage.
	if ( !access_level_check_greater_or_equal( config_get( 'view_summary_threshold' ) ) ) {
		access_denied();
	}

	$height = 150;

	enum_bug_group( lang_get( 'resolution_enum_string' ), 'resolution');
	graph_group( lang_get( 'by_resolution_mix' ) );
?>