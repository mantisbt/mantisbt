<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

	# --------------------------------------------------------
	# $Id: graph_by_severity_status.php,v 1.8.22.1 2007-10-13 22:35:54 giallu Exp $
	# --------------------------------------------------------
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

	$critical_count_arr 	= array(2);
	$high_count_arr 		= array(2);
	$medium_count_arr 		= array(2);
	$low_count_arr 			= array(2);
	$none_count_arr 		= array(2);
	$duplicate_count_arr 	= array(2);

	$critical_count_arr[0] = 0;
	$critical_count_arr[1] = 0;
	$critical_count_arr[2] = 0;
	$high_count_arr[0] = 0;
	$high_count_arr[1] = 0;
	$high_count_arr[2] = 0;
	$medium_count_arr[0] = 0;
	$medium_count_arr[1] = 0;
	$medium_count_arr[2] = 0;
	$low_count_arr[0] = 0;
	$low_count_arr[1] = 0;
	$low_count_arr[2] = 0;
	$none_count_arr[0] = 0;
	$none_count_arr[1] = 0;
	$none_count_arr[2] = 0;
	$duplicate_count_arr[0] = 0;
	$duplicate_count_arr[1] = 0;
	$duplicate_count_arr[2] = 0;

	$severity_arr = explode( ',', $g_severity_enum_string );
	$severity_count = count( $severity_arr );

	# GET OPEN
	for ($i=0;$i<$severity_count;$i++) {
		$t_severity_arr = explode( ':', $severity_arr[$i] );
		$t_severity = $t_severity_arr[0];
		$query = "SELECT COUNT(*) as count
				FROM mantis_bug_table
				WHERE project_id='$t_project_id' AND
						status<80 AND
						severity='$t_severity'";
		$result = db_query( $query );
		$count = db_result( $result, 0, 0 );
		switch ( $t_severity ) {
			case 20:$critical_count_arr[0] = $count;
					break;
			case 30:$high_count_arr[0] = $count;
					break;
			case 40:$medium_count_arr[0] = $count;
					break;
			case 50:$low_count_arr[0] = $count;
					break;
			case 60:$none_count_arr[0] = $count;
					break;
			case 70:$duplicate_count_arr[0] = $count;
					break;
		}
	}
	# GET RESOLVED
	for ($i=0;$i<$severity_count;$i++) {
		$t_severity_arr = explode( ':', $severity_arr[$i] );
		$t_severity = $t_severity_arr[0];
		$query = "SELECT COUNT(*) as count
				FROM mantis_bug_table
				WHERE project_id='$t_project_id' AND
						status=80 AND
						severity='$t_severity'";
		$result = db_query( $query );
		$count = db_result( $result, 0, 0 );

		switch ( $t_severity ) {
			case 20:$critical_count_arr[1] = $count;
					break;
			case 30:$high_count_arr[1] = $count;
					break;
			case 40:$medium_count_arr[1] = $count;
					break;
			case 50:$low_count_arr[1] = $count;
					break;
			case 60:$none_count_arr[1] = $count;
					break;
			case 70:$duplicate_count_arr[1] = $count;
					break;
		}
	}
	# GET CLOSED
	for ($i=0;$i<$severity_count;$i++) {
		$t_severity_arr = explode( ':', $severity_arr[$i] );
		$t_severity = $t_severity_arr[0];
		$query = "SELECT COUNT(*) as count
				FROM mantis_bug_table
				WHERE project_id='$t_project_id' AND
						status=90 AND
						severity='$t_severity'";
		$result = db_query( $query );
		$count = db_result( $result, 0, 0 );

		switch ( $t_severity ) {
			case 20:$critical_count_arr[2] = $count;
					break;
			case 30:$high_count_arr[2] = $count;
					break;
			case 40:$medium_count_arr[2] = $count;
					break;
			case 50:$low_count_arr[2] = $count;
					break;
			case 60:$none_count_arr[2] = $count;
					break;
			case 70:$duplicate_count_arr[2] = $count;
					break;
		}
	}

	$proj_name = project_get_field( $t_project_id, 'name' );

	# Setup Graph
	# ---

	$graph = new Graph( 800, 600, 'auto' );
	$graph->SetColor( "whitesmoke" );
	$graph->SetScale( "textlin" );
	$graph->SetShadow();
	$graph->img->SetMargin( 40, 30, 40, 40 );

	$graph->xaxis->SetTickLabels( array( 'Open', 'Resolved', 'Closed' ) );
	$graph->xaxis->title->Set( "Status" );
	$graph->xaxis->title->SetFont( FF_FONT1, FS_BOLD );

	$graph->title->Set( "Severity vs. Status Distribution: $proj_name" );
	$graph->title->SetFont( FF_FONT1, FS_BOLD );

	# Create graph
	$bplot1 = new BarPlot( $critical_count_arr );
	$bplot2 = new BarPlot( $high_count_arr );
	$bplot3 = new BarPlot( $medium_count_arr );
	$bplot4 = new BarPlot( $low_count_arr );
	$bplot5 = new BarPlot( $none_count_arr );
	$bplot6 = new BarPlot( $duplicate_count_arr );

	$bplot1->SetFillColor( "slateblue" );
	$bplot2->SetFillColor( "maroon" );
	$bplot3->SetFillColor( "lightgoldenrodyellow" );
	$bplot4->SetFillColor( "paleturquoise" );
	$bplot5->SetFillColor( "palegreen3" );
	$bplot6->SetFillColor( "sienna2" );

	$bplot1->SetShadow();
	$bplot2->SetShadow();
	$bplot3->SetShadow();
	$bplot4->SetShadow();
	$bplot5->SetShadow();
	$bplot6->SetShadow();

	$bplot1->SetLegend( 'Critical' );
	$bplot2->SetLegend( 'High' );
	$bplot3->SetLegend( 'Medium' );
	$bplot4->SetLegend( 'Low' );
	$bplot5->SetLegend( 'None' );
	$bplot6->SetLegend( 'Duplicate' );

	$bplot1->value->Show();
	$bplot2->value->Show();
	$bplot3->value->Show();
	$bplot4->value->Show();
	$bplot5->value->Show();
	$bplot6->value->Show();

	$bplot1->value->SetFont( FF_FONT1 );
	$bplot2->value->SetFont( FF_FONT1 );
	$bplot3->value->SetFont( FF_FONT1 );
	$bplot4->value->SetFont( FF_FONT1 );
	$bplot5->value->SetFont( FF_FONT1 );
	$bplot6->value->SetFont( FF_FONT1 );

	$bplot1->value->SetColor( "black", "darkred" );
	$bplot2->value->SetColor( "black", "darkred" );
	$bplot3->value->SetColor( "black", "darkred" );
	$bplot4->value->SetColor( "black", "darkred" );
	$bplot5->value->SetColor( "black", "darkred" );
	$bplot6->value->SetColor( "black", "darkred" );

	$bplot1->value->SetFormat( '%d' );
	$bplot2->value->SetFormat( '%d' );
	$bplot3->value->SetFormat( '%d' );
	$bplot4->value->SetFormat( '%d' );
	$bplot5->value->SetFormat( '%d' );
	$bplot6->value->SetFormat( '%d' );

	$gbarplot = new GroupBarPlot( array( $bplot1, $bplot2, $bplot3, $bplot4, $bplot5, $bplot6 ) );

	$gbarplot->SetWidth( 0.9 );
	$graph->Add( $gbarplot );

	$graph->Stroke();
?>
