<?php
include( "core_API.php" );
include( $g_summary_jpgraph_function );
create_bug_enum_summary( $s_severity_enum_string, "severity");
graph_bug_enum_summary($s_by_severity);
?>