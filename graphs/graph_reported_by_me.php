<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	require_once( '../core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'graph_api.php' );
?>
<?php
	# Grab Data
	# ---

	$t_project_id = helper_get_current_project();

	$data_category_arr = array();
	$data_count_arr = array();
	$t_user_id = auth_get_current_user_id();
	$query = "SELECT status, COUNT( status ) as count
			FROM mantis_bug_table
			WHERE project_id='$t_project_id' AND
				  reporter_id='$t_user_id'
			GROUP BY status
			ORDER BY status";
	$result = db_query( $query );
	$status_count = db_num_rows( $result );

	$status_arr = array();
	$status_arr[10] = 0;
	$status_arr[20] = 0;
	$status_arr[30] = 0;
	$status_arr[40] = 0;
	$status_arr[50] = 0;
	$status_arr[80] = 0;
	$status_arr[90] = 0;
	for ($i=0;$i<$status_count;$i++) {
		$row = db_fetch_array( $result );
		extract( $row );

		$status_arr[$status] = $count;
	}

	# Setup Graph
	# ---

	$graph = new Graph( 150, 150, 'auto' );
	$graph->SetScale('textint');
	$graph->SetMarginColor('whitesmoke');
	$graph->img->SetMargin( 30, 12, 32, 22 );

	$t_label_arr[] = 'Status';
	$graph->xaxis->SetTickLabels( $t_label_arr );
	$graph->xaxis->SetFont( FF_FONT1 );
	$graph->yaxis->SetFont( FF_FONT0 );

	$graph->title->Set( $s_reporter );
	$graph->title->SetFont( FF_FONT1 );
	$graph->title->SetMargin( 0 );

	$t_arr1 = array();
	$t_arr2 = array();
	$t_arr3 = array();
	$t_arr4 = array();
	$t_arr5 = array();
	$t_arr6 = array();
	$t_arr7 = array();

	$t_arr1[0] = $status_arr[10];
	$t_arr2[0] = $status_arr[20];
	$t_arr3[0] = $status_arr[30];
	$t_arr4[0] = $status_arr[40];
	$t_arr5[0] = $status_arr[50];
	$t_arr6[0] = $status_arr[80];
	$t_arr7[0] = $status_arr[90];

	$bplot1 = new BarPlot( $t_arr1 );
	$bplot2 = new BarPlot( $t_arr2 );
	$bplot3 = new BarPlot( $t_arr3 );
	$bplot4 = new BarPlot( $t_arr4 );
	$bplot5 = new BarPlot( $t_arr5 );
	$bplot6 = new BarPlot( $t_arr6 );
	$bplot7 = new BarPlot( $t_arr7 );

	$bplot1->SetFillColor( 'indianred2' );
	$bplot2->SetFillColor( 'maroon1' );
	$bplot3->SetFillColor( 'gold1' );
	$bplot4->SetFillColor( 'lightyellow' );
	$bplot5->SetFillColor( 'cornflowerblue' );
	$bplot6->SetFillColor( 'darkseagreen2' );
	$bplot7->SetFillColor( 'white' );

	$bplot1->SetShadow();
	$bplot2->SetShadow();
	$bplot3->SetShadow();
	$bplot4->SetShadow();
	$bplot5->SetShadow();
	$bplot6->SetShadow();
	$bplot7->SetShadow();

/*	$bplot1->SetLegend( 'New' );
	$bplot2->SetLegend( 'Feedback' );
	$bplot3->SetLegend( 'Acked' );
	$bplot4->SetLegend( 'Fixed' );
	$bplot5->SetLegend( 'Assigned' );
	$bplot6->SetLegend( 'Resolved' );
	$bplot7->SetLegend( 'Closed' );*/

/*	$bplot1->value->Show();
	$bplot2->value->Show();
	$bplot3->value->Show();
	$bplot4->value->Show();
	$bplot5->value->Show();
	$bplot6->value->Show();
	$bplot7->value->Show();*/

	$bplot1->value->SetFont( FF_FONT0 );
	$bplot2->value->SetFont( FF_FONT0 );
	$bplot3->value->SetFont( FF_FONT0 );
	$bplot4->value->SetFont( FF_FONT0 );
	$bplot5->value->SetFont( FF_FONT0 );
	$bplot6->value->SetFont( FF_FONT0 );
	$bplot7->value->SetFont( FF_FONT0 );

	$bplot1->value->SetColor( 'black', 'darkred' );
	$bplot2->value->SetColor( 'black', 'darkred' );
	$bplot3->value->SetColor( 'black', 'darkred' );
	$bplot4->value->SetColor( 'black', 'darkred' );
	$bplot5->value->SetColor( 'black', 'darkred' );
	$bplot6->value->SetColor( 'black', 'darkred' );
	$bplot7->value->SetColor( 'black', 'darkred' );

	$bplot1->value->SetFormat( '%d' );
	$bplot2->value->SetFormat( '%d' );
	$bplot3->value->SetFormat( '%d' );
	$bplot4->value->SetFormat( '%d' );
	$bplot5->value->SetFormat( '%d' );
	$bplot6->value->SetFormat( '%d' );
	$bplot7->value->SetFormat( '%d' );

	$gbarplot = new GroupBarPlot( array( $bplot1, $bplot2, $bplot3, $bplot4, $bplot5, $bplot6, $bplot7 ) );

	$gbarplot->SetWidth( 1.0 );
	$graph->Add( $gbarplot );

	$graph->Stroke();
?>