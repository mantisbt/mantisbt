<?php
include( 'core_API.php' );
include( $g_summary_jpgraph_function );
 
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