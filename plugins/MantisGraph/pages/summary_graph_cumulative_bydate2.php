<?php
# MantisBT - a php based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

	/**
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	require 'library/ezc/Base/src/base.php';

	require_once( 'graph_api.php' );

	access_ensure_project_level( config_get( 'view_summary_threshold' ) );

	$f_width = gpc_get_int( 'width', 300 );
	$t_ar = plugin_config_get( 'bar_aspect' );

	error_check(0,'foo');

	$t_metrics = create_cumulative_bydate2();
	
	graph_cumulative_bydate2( $t_metrics, $f_width, $f_width * $t_ar );


function graph_cumulative_bydate2( $p_metrics, $p_graph_width = 300, $p_graph_height = 380 ) {

	$t_graph_font = 'c:\\windows\\fonts\\arial.ttf' ;//graph_get_font();

	error_check( is_array( $p_metrics ) ? count( $p_metrics ) : 0, plugin_lang_get( 'cumulative' ) . ' ' . lang_get( 'by_date' ) );

	$graph = new ezcGraphLineChart();

	$graph->xAxis = new ezcGraphChartElementNumericAxis();


	$graph->data[0] = new ezcGraphArrayDataSet( $p_metrics[0] );
	$graph->data[0]->label = plugin_lang_get( 'legend_reported' );
	$graph->data[0]->color = '#FF0000';

	$graph->data[1] = new ezcGraphArrayDataSet( $p_metrics[1] );
	$graph->data[1]->label = plugin_lang_get( 'legend_resolved' );
	$graph->data[1]->color = '#0000FF';

	$graph->data[2] = new ezcGraphArrayDataSet( $p_metrics[2] );
	$graph->data[2]->label = plugin_lang_get( 'legend_still_open' );
	$graph->data[2]->color = '#000000';

	$graph->additionalAxis[2] = $nAxis = new ezcGraphChartElementNumericAxis();
	$nAxis->chartPosition = 1;
	$nAxis->background = '#005500';
	$nAxis->border = '#005500';
	$nAxis->position = ezcGraph::BOTTOM;
	$graph->data[2]->yAxis = $nAxis;

$graph->xAxis->labelCallback =  'graph_date_format';
$graph->xAxis->axisLabelRenderer = new ezcGraphAxisRotatedLabelRenderer();
$graph->xAxis->axisLabelRenderer->angle = -45;
//$graph->xAxis->axisSpace = .8;

$graph->legend->position      = ezcGraph::BOTTOM;
$graph->legend->background    = '#FFFFFF80';

$graph->driver = new ezcGraphGdDriver();
//$graph->driver->options->supersampling = 1;
$graph->driver->options->jpegQuality = 100;
$graph->driver->options->imageFormat = IMG_JPEG;

//	$graph->img->SetMargin( 40, 40, 40, 170 );
//	if( ON == config_get_global( 'jpgraph_antialias' ) ) {
//		$graph->img->SetAntiAliasing();
//	}
//	$graph->SetScale( 'linlin');
//	$graph->yaxis->SetColor("red");
//	$graph->SetY2Scale("lin");
//	$graph->SetMarginColor( 'white' );
//	$graph->SetFrame( false );

	$graph->title = plugin_lang_get( 'cumulative' ) . ' ' . lang_get( 'by_date' );
	$graph->options->font = $t_graph_font ;

//	$graph->title->SetFont( $t_graph_font, FS_BOLD );

/*	$graph->legend->Pos( 0.05, 0.9, 'right', 'bottom' );
	$graph->legend->SetShadow( false );
	$graph->legend->SetFillColor( 'white' );
	$graph->legend->SetLayout( LEGEND_HOR );
	$graph->legend->SetFont( $t_graph_font );

	$graph->yaxis->scale->ticks->SetDirection( -1 );
	$graph->yaxis->SetFont( $t_graph_font );
	$graph->y2axis->SetFont( $t_graph_font );

	if( FF_FONT2 <= $t_graph_font ) {
		$graph->xaxis->SetLabelAngle( 60 );
	} else {
		$graph->xaxis->SetLabelAngle( 90 );

		# can't rotate non truetype fonts
	}
	$graph->xaxis->SetLabelFormatCallback( 'graph_date_format' );
	$graph->xaxis->SetFont( $t_graph_font );

	$p1 = new LinePlot( $reported_plot, $plot_date );
	$p1->SetColor( 'blue' );
	$p1->SetCenter();
	$graph->AddY2( $p1 );

	$p3 = new LinePlot( $still_open_plot, $plot_date );
	$p3->SetColor( 'red' );
	$p3->SetCenter();
	$p3->SetLegend(  );
	$graph->Add( $p3 );

	$p2 = new LinePlot( $resolved_plot, $plot_date );
	$p2->SetColor( 'black' );
	$p2->SetCenter();
	$p2->SetLegend(  );
	$graph->AddY2( $p2 );
*/

/*	if( helper_show_queries() ) {
		$graph->subtitle->Set( db_count_queries() . ' queries (' . db_time_queries() . 'sec)' );
		$graph->subtitle->SetFont( $t_graph_font, FS_NORMAL, 8 );
	}*/
	
	$graph->renderToOutput( $p_graph_width, $p_graph_height);
}



function create_cumulative_bydate2() {

	$t_clo_val = CLOSED;
	$t_res_val = config_get( 'bug_resolved_status_threshold' );
	$t_bug_table = db_get_table( 'mantis_bug_table' );
	$t_history_table = db_get_table( 'mantis_bug_history_table' );

	$t_project_id = helper_get_current_project();
	$t_user_id = auth_get_current_user_id();
	$specific_where = helper_project_specific_where( $t_project_id, $t_user_id );

	# Get all the submitted dates
	$query = "SELECT date_submitted
				FROM $t_bug_table
				WHERE $specific_where
				ORDER BY date_submitted";
	$result = db_query( $query );
	$bug_count = db_num_rows( $result );

	for( $i = 0;$i < $bug_count;$i++ ) {
		$row = db_fetch_array( $result );

		# rationalise the timestamp to a day to reduce the amount of data
		$t_date = $row['date_submitted'];
		$t_date = (int)( $t_date / SECONDS_PER_DAY );

		if( isset( $metrics[$t_date] ) ) {
			$metrics[$t_date][0]++;
		} else {
			$metrics[$t_date] = array( 1, 0, 0, );
		}
	}

	# ## Get all the dates where a transition from not resolved to resolved may have happened
	#    also, get the last updated date for the bug as this may be all the information we have
	$query = "SELECT $t_bug_table.id, last_updated, date_modified, new_value, old_value
			FROM $t_bug_table LEFT JOIN $t_history_table
			ON $t_bug_table.id = $t_history_table.bug_id
			WHERE $specific_where
						AND $t_bug_table.status >= '$t_res_val'
						AND ( ( $t_history_table.new_value >= '$t_res_val'
								AND $t_history_table.field_name = 'status' )
						OR $t_history_table.id is NULL )
			ORDER BY $t_bug_table.id, date_modified ASC";
	$result = db_query( $query );
	$bug_count = db_num_rows( $result );

	$t_last_id = 0;
	$t_last_date = 0;

	for( $i = 0;$i < $bug_count;$i++ ) {
		$row = db_fetch_array( $result );
		$t_id = $row['id'];

		# if h_last_updated is NULL, there were no appropriate history records
		#  (i.e. pre 0.18 data), use last_updated from bug table instead
		if( NULL == $row['date_modified'] ) {
			$t_date = $row['last_updated'];
		} else {
			if( $t_res_val > $row['old_value'] ) {
				$t_date = $row['date_modified'];
			}
		}
		if( $t_id <> $t_last_id ) {
			if( 0 <> $t_last_id ) {

				# rationalise the timestamp to a day to reduce the amount of data
				$t_date_index = (int)( $t_last_date / SECONDS_PER_DAY );

				if( isset( $metrics[$t_date_index] ) ) {
					$metrics[$t_date_index][1]++;
				} else {
					$metrics[$t_date_index] = array(
						0,
						1,
						0,
					);
				}
			}
			$t_last_id = $t_id;
		}
		$t_last_date = $t_date;
	}

	ksort( $metrics );

	$metrics_count = count( $metrics );
	$t_last_opened = 0;
	$t_last_resolved = 0;
	foreach( $metrics as $i => $vals ) {
		$t_date = $i * SECONDS_PER_DAY;
		$t_metrics[0][$t_date] = $t_last_opened = $metrics[$i][0] + $t_last_opened;
		$t_metrics[1][$t_date] = $t_last_resolved = $metrics[$i][1] + $t_last_resolved;
		$t_metrics[2][$t_date] = $t_metrics[0][$t_date] - $t_metrics[1][$t_date];
	}
	return $t_metrics;
}
