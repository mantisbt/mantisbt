<?php
include( 'core_API.php' );
include( $g_summary_jpgraph_function );

$height=80;

enum_bug_group( $s_priority_enum_string, 'priority');
graph_group($s_by_priority_mix);
?>