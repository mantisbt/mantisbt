<?php
include( 'core_API.php' );
include( $g_summary_jpgraph_function );


create_category_summary_pct();
graph_category_summary_pct($s_by_category_pct);
?>