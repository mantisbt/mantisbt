<?php
include( 'core_API.php' );
include( $g_summary_jpgraph_function );

$height=100;

enum_bug_group($s_severity_enum_string, 'severity');
graph_group($s_by_severity_mix);

?>