<?php
require_once( 'core.php' );

login_cookie_check();

# if user is below view summary threshold, then re-direct to mainpage.
if ( !access_level_check_greater_or_equal( $g_view_summary_threshold ) ) {
	print_header_redirect( 'main_page.php' );
}
 
$height=150;

if ($g_customize_attributes) {
			# to be deleted when moving to manage_project_page.php	
			$t_project_id = '0000000';

			# custom attributes insertion
			insert_attributes( 'resolution', $t_project_id, 'global' );
			insert_attributes( 'resolution', $t_project_id, 'str' ) ;
}
enum_bug_group($s_resolution_enum_string, 'resolution');
graph_group($s_by_resolution_mix);

?>