<?php
include( 'core_API.php' );
include( $g_summary_jpgraph_function );

#centers the chart
$center = 0.3;

#position of the legend
$poshorizontal = 0.03;
$posvertical = 0.09;

create_bug_enum_summary_pct($s_status_enum_string, 'status');
graph_bug_enum_summary_pct($s_by_status_pct);


?>