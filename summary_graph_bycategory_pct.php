<?php
include( 'core_API.php' );
include( $g_summary_jpgraph_function );

if ($g_customize_attributes) {
			# to be deleted when moving to manage_project_page.php	
			$t_project_id = '0000000';

			# custom attributes insertion
			insert_attributes( 'category', $t_project_id, 'global' );
			insert_attributes( 'category', $t_project_id, 'str' ) ;
}
create_category_summary_pct();
graph_category_summary_pct($s_by_category_pct);
?>