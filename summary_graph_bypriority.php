<?php
require_once( 'core.php' );

login_cookie_check();

# if user is below view summary threshold, then re-direct to mainpage.
if ( !access_level_check_greater_or_equal( $g_view_summary_threshold ) ) {
	print_header_redirect( 'main_page.php' );
}

if ($g_customize_attributes) {
			# to be deleted when moving to manage_project_page.php	
			$t_project_id = '0000000';

			# custom attributes insertion
			insert_attributes( 'priority', $t_project_id, 'global' );
			insert_attributes( 'priority', $t_project_id, 'str' ) ;
}
create_bug_enum_summary($s_priority_enum_string, 'priority');
graph_bug_enum_summary($s_by_priority);
?>