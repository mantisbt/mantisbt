<?php
include( "core_API.php" );
include( $g_summary_jpgraph_function );
create_bug_enum_summary($s_priority_enum_string, "priority");
graph_bug_enum_summary($s_by_priority);
?>