<?php
include( "core_API.php" );
include( $g_summary_jpgraph_function );
create_bug_enum_summary($s_resolution_enum_string, "resolution");
graph_bug_enum_summary($s_by_resolution);
?>