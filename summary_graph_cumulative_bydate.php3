<?php
include( "core_API.php" );
include( $g_summary_jpgraph_function );
create_cumulative_bydate();
graph_cumulative_bydate();
?>