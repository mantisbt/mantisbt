<?php
include( 'core_API.php' );
include( $g_summary_jpgraph_function );

 
$height=150;

enum_bug_group($s_resolution_enum_string, 'resolution');
graph_group($s_by_resolution_mix);

?>