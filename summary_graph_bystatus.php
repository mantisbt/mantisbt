<?php
include( 'core_API.php' );
include( $g_summary_jpgraph_function );
create_bug_enum_summary($s_status_enum_string, 'status');
graph_bug_enum_summary($s_by_status);
?>