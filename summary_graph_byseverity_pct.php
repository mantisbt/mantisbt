<?php
include( 'core_API.php' );
include( $g_summary_jpgraph_function );

#centers the chart
$center = 0.30;

#position of the legend
$poshorizontal = 0.10;
$posvertical = 0.09;

create_bug_enum_summary_pct( $s_severity_enum_string, 'severity');
graph_bug_enum_summary_pct($s_by_severity_pct);
?>