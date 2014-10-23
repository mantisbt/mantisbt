<?php
# MantisBT - A PHP based bugtracking system

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
 * Graph API
 *
 * @package CoreAPI
 * @subpackage GraphAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

if( OFF == plugin_config_get( 'eczlibrary' ) ) {
	$t_font_path = get_font_path();
	if( $t_font_path !== '' && !defined( 'TTF_DIR' ) ) {
		define( 'TTF_DIR', $t_font_path );
	}
	$t_jpgraph_path = plugin_config_get( 'jpgraph_path', '' );
	if( $t_jpgraph_path !== '' ) {
		require_once( $t_jpgraph_path . 'jpgraph.php' );
		require_once( $t_jpgraph_path . 'jpgraph_line.php' );
		require_once( $t_jpgraph_path . 'jpgraph_bar.php' );
		require_once( $t_jpgraph_path . 'jpgraph_pie.php' );
		require_once( $t_jpgraph_path . 'jpgraph_pie3d.php' );
		require_once( $t_jpgraph_path . 'jpgraph_canvas.php' );
	} else {
		require_lib( 'jpgraph/jpgraph.php' );
		require_lib( 'jpgraph/jpgraph_line.php' );
		require_lib( 'jpgraph/jpgraph_bar.php' );
		require_lib( 'jpgraph/jpgraph_pie.php' );
		require_lib( 'jpgraph/jpgraph_pie3d.php' );
		require_lib( 'jpgraph/jpgraph_canvas.php' );
	}
} else {
	require_lib( 'ezc/Base/src/base.php' );
}

/**
 * Get Font to use with graphs from configuration value
 * @return string
 */
function graph_get_font() {
	$t_font = plugin_config_get( 'font', 'arial' );

	if( plugin_config_get( 'eczlibrary' ) == ON ) {
		$t_font_map = array(
			'arial' => 'arial.ttf',
			'verdana' => 'verdana.ttf',
			'trebuchet' => 'trebuc.ttf',
			'verasans' => 'Vera.ttf',
			'times' => 'times.ttf',
			'georgia' => 'georgia.ttf',
			'veraserif' => 'VeraSe.ttf',
			'courier' => 'cour.ttf',
			'veramono' => 'VeraMono.ttf',
		);

		if( isset( $t_font_map[$t_font] ) ) {
			$t_font = $t_font_map[$t_font];
		} else {
			$t_font = 'arial.ttf';
		}
		$t_font_path = get_font_path();
		if( empty( $t_font_path ) ) {
			error_text( 'Unable to read/find font', 'Unable to read/find font' );
		}
		$t_font_file = $t_font_path . $t_font;
		if( file_exists( $t_font_file ) === false || is_readable( $t_font_file ) === false ) {
			error_text( 'Unable to read/find font', 'Unable to read/find font' );
		}
		return $t_font_file;
	} else {
		$t_font_map = array(
			'arial' => FF_ARIAL,
			'verdana' => FF_VERDANA,
			'trebuchet' => FF_TREBUCHE,
			'verasans' => FF_VERA,
			'times' => FF_TIMES,
			'georgia' => FF_GEORGIA,
			'veraserif' => FF_VERASERIF,
			'courier' => FF_COURIER,
			'veramono' => FF_VERAMONO,
		);

		if( isset( $t_font_map[$t_font] ) ) {
			return $t_font_map[$t_font];
		} else {
			return FF_FONT1;
		}
	}
}

/**
 * Generate Bar Graph
 *
 * @param array   $p_metrics      Graph Data.
 * @param string  $p_title        Title.
 * @param integer $p_graph_width  Width of graph in pixels.
 * @param integer $p_graph_height Height of graph in pixels.
 * @return void
 */
function graph_bar( array $p_metrics, $p_title = '', $p_graph_width = 350, $p_graph_height = 400 ) {
	$t_graph_font = graph_get_font();

	error_check( is_array( $p_metrics ) ? array_sum( $p_metrics ) : 0, $p_title );

	if( plugin_config_get( 'eczlibrary' ) == ON ) {
		$t_graph = new ezcGraphBarChart();
		$t_graph->title = $p_title;
		$t_graph->background->color = '#FFFFFF';
		$t_graph->options->font = $t_graph_font ;
		$t_graph->options->font->maxFontSize = 12;
		$t_graph->legend = false;

		$t_graph->data[0] = new ezcGraphArrayDataSet( $p_metrics );
		$t_graph->data[0]->color = '#FFFF00';

		$t_graph->xAxis->axisLabelRenderer = new ezcGraphAxisRotatedLabelRenderer();
		$t_graph->xAxis->axisLabelRenderer->angle = 45;

		$t_graph->driver = new ezcGraphGdDriver();
		# $t_graph->driver->options->supersampling = 1;
		$t_graph->driver->options->jpegQuality = 100;
		$t_graph->driver->options->imageFormat = IMG_JPEG;

		$t_graph->renderer->options->syncAxisFonts = false;

		$t_graph->renderToOutput( $p_graph_width, $p_graph_height );
	} else {
		$t_graph = new Graph( $p_graph_width, $p_graph_height );
		$t_graph->img->SetMargin( 40, 40, 40, 170 );
		if( ON == plugin_config_get( 'jpgraph_antialias' ) ) {
			$t_graph->img->SetAntiAliasing();
		}
		$t_graph->SetScale( 'textlin' );
		$t_graph->SetMarginColor( 'white' );
		$t_graph->SetFrame( false );
		$t_graph->title->Set( $p_title );
		$t_graph->title->SetFont( $t_graph_font, FS_BOLD );
		$t_graph->xaxis->SetTickLabels( array_keys( $p_metrics ) );
		if( FF_FONT2 <= $t_graph_font ) {
			$t_graph->xaxis->SetLabelAngle( 60 );
		} else {
			$t_graph->xaxis->SetLabelAngle( 90 );
			# can't rotate non truetype fonts
		}
		$t_graph->xaxis->SetFont( $t_graph_font );

		$t_graph->legend->SetFont( $t_graph_font );

		$t_graph->yaxis->scale->ticks->SetDirection( -1 );
		$t_graph->yaxis->SetFont( $t_graph_font );

		$t_plot1 = new BarPlot( array_values( $p_metrics ) );
		$t_plot1->SetFillColor( 'yellow' );
		$t_plot1->SetWidth( 0.8 );
		$t_graph->Add( $t_plot1 );
		if( helper_show_query_count() ) {
			$t_graph->subtitle->Set( db_count_queries() . ' queries (' . db_time_queries() . 'sec)' );
			$t_graph->subtitle->SetFont( $t_graph_font, FS_NORMAL, 8 );
		}

		$t_graph->Stroke();
	}
}

/**
 * Function which displays the charts using the absolute values according to the status (opened/closed/resolved)
 *
 * @param array   $p_metrics      Graph Data.
 * @param string  $p_title        Title.
 * @param integer $p_graph_width  Width of graph in pixels.
 * @param integer $p_graph_height Height of graph in pixels.
 * @param integer $p_baseline     Jpgraph baseline.
 * @return void
 */
function graph_group( array $p_metrics, $p_title = '', $p_graph_width = 350, $p_graph_height = 400, $p_baseline = 100 ) {
	# $p_metrics is an array of three arrays
	#   $p_metrics['open'] = array( 'enum' => value, ...)
	#   $p_metrics['resolved']
	#   $p_metrics['closed']

	$t_graph_font = graph_get_font();

	# count up array portions that are set
	$t_count = 0;
	foreach( array( 'open', 'resolved', 'closed' ) as $t_label ) {
		if( isset( $p_metrics[$t_label] ) && is_array( $p_metrics[$t_label] ) ) {
			$t_count += array_sum( $p_metrics[$t_label] );
		}
	}

	error_check( $t_count, $p_title );

	# calculate totals
	$t_total = graph_total_metrics( $p_metrics );

	if( plugin_config_get( 'eczlibrary' ) == ON ) {
		$t_graph = new ezcGraphBarChart();
		$t_graph->title = $p_title;
		$t_graph->background->color = '#FFFFFF';
		$t_graph->options->font = $t_graph_font ;
		$t_graph->options->font->maxFontSize = 12;
		$t_graph->legend = false;

		foreach( array( 'open', 'resolved', 'closed' ) as $t_label ) {
			$t_graph->data[$t_label] = new ezcGraphArrayDataSet( $p_metrics[$t_label] );
		}
		$t_graph->data['total'] = new ezcGraphArrayDataSet( $t_total );
		# $t_graph->data['total']->displayType = ezcGraph::LINE;
		# $t_graph->data['total']->barMargin = -20;
		$t_graph->options->fillLines = 210;
		$t_graph->xAxis->axisLabelRenderer = new ezcGraphAxisRotatedLabelRenderer();
		$t_graph->xAxis->axisLabelRenderer->angle = 45;

		$t_graph->driver = new ezcGraphGdDriver();
		# $t_graph->driver->options->supersampling = 1;
		$t_graph->driver->options->jpegQuality = 100;
		$t_graph->driver->options->imageFormat = IMG_JPEG;

		$t_graph->renderer->options->syncAxisFonts = false;

		$t_graph->renderToOutput( $p_graph_width, $p_graph_height );
	} else {
		# defines margin according to height
		$t_graph = new Graph( $p_graph_width, $p_graph_height );
		$t_graph->img->SetMargin( 45, 35, 35, $p_baseline );
		if( ON == plugin_config_get( 'jpgraph_antialias' ) ) {
			$t_graph->img->SetAntiAliasing();
		}
		$t_graph->SetScale( 'textlin' );
		$t_graph->SetMarginColor( 'white' );
		$t_graph->SetFrame( false );
		$t_graph->title->SetFont( $t_graph_font, FS_BOLD );
		$t_graph->title->Set( $p_title );
		$t_graph->xaxis->SetTickLabels( array_keys( $p_metrics['open'] ) );
		if( FF_FONT2 <= $t_graph_font ) {
			$t_graph->xaxis->SetLabelAngle( 60 );
		} else {
			$t_graph->xaxis->SetLabelAngle( 90 );
			# can't rotate non truetype fonts
		}
		$t_graph->xaxis->SetFont( $t_graph_font );
		$t_graph->legend->Pos( 0.05, 0.08 );
		$t_graph->legend->SetFont( $t_graph_font );

		$t_graph->yaxis->scale->ticks->SetDirection( -1 );
		$t_graph->yaxis->SetFont( $t_graph_font );
		$t_graph->yscale->SetGrace( 10 );

		# adds on the same graph
		$t_tot = new BarPlot( array_values( $t_total ) );
		$t_tot->SetFillColor( 'lightblue' );
		$t_tot->SetWidth( 0.7 );
		$t_tot->SetLegend( plugin_lang_get( 'legend_total' ) );
		$t_graph->Add( $t_tot );

		$t_plot1 = new BarPlot( array_values( $p_metrics['open'] ) );
		$t_plot1->SetFillColor( 'yellow' );
		$t_plot1->SetWidth( 1 );
		$t_plot1->SetLegend( plugin_lang_get( 'legend_opened' ) );

		$t_plot2 = new BarPlot( array_values( $p_metrics['closed'] ) );
		$t_plot2->SetFillColor( 'blue' );
		$t_plot2->SetWidth( 1 );
		$t_plot2->SetLegend( plugin_lang_get( 'legend_closed' ) );

		$t_plot3 = new BarPlot( array_values( $p_metrics['resolved'] ) );
		$t_plot3->SetFillColor( 'red' );
		$t_plot3->SetWidth( 1 );
		$t_plot3->SetLegend( plugin_lang_get( 'legend_resolved' ) );

		$t_gbplot = new GroupBarPlot( array( $t_plot1, $t_plot3, $t_plot2 ) );
		$t_graph->Add( $t_gbplot );

		if( helper_show_query_count() ) {
			$t_graph->subtitle->Set( db_count_queries() . ' queries (' . db_time_queries() . 'sec)' );
			$t_graph->subtitle->SetFont( $t_graph_font, FS_NORMAL, 8 );
		}
		$t_graph->Stroke();
	}
}

/**
 * Function that displays pie charts
 *
 * @param array         $p_metrics       Graph Data.
 * @param string        $p_title         Title.
 * @param integer       $p_graph_width   Width of graph in pixels.
 * @param integer       $p_graph_height  Height of graph in pixels.
 * @param float|integer $p_center        Jpgraph center.
 * @param float|integer $p_poshorizontal Jpgraph horizontal.
 * @param float|integer $p_posvertical   Jpgraph vertical.
 * @return void
 */
function graph_pie( array $p_metrics, $p_title = '', $p_graph_width = 500, $p_graph_height = 350, $p_center = 0.4, $p_poshorizontal = 0.10, $p_posvertical = 0.09 ) {
	$t_graph_font = graph_get_font();

	error_check( is_array( $p_metrics ) ? array_sum( $p_metrics ) : 0, $p_title );

	if( plugin_config_get( 'eczlibrary' ) == ON ) {
		$t_graph = new ezcGraphPieChart();
		$t_graph->title = $p_title;
		$t_graph->background->color = '#FFFFFF';
		$t_graph->options->font = $t_graph_font ;
		$t_graph->options->font->maxFontSize = 12;
		$t_graph->legend = false;

		$t_graph->data[0] = new ezcGraphArrayDataSet( $p_metrics );
		$t_graph->data[0]->color = '#FFFF00';

		$t_graph->renderer = new ezcGraphRenderer3d();
		$t_graph->renderer->options->dataBorder = false;
		$t_graph->renderer->options->pieChartShadowSize = 10;
		$t_graph->renderer->options->pieChartGleam = .5;
		$t_graph->renderer->options->pieChartHeight = 16;
		$t_graph->renderer->options->legendSymbolGleam = .5;

		$t_graph->driver = new ezcGraphGdDriver();
		# $t_graph->driver->options->supersampling = 1;
		$t_graph->driver->options->jpegQuality = 100;
		$t_graph->driver->options->imageFormat = IMG_JPEG;

		$t_graph->renderer->options->syncAxisFonts = false;

		$t_graph->renderToOutput( $p_graph_width, $p_graph_height );
	} else {
		$t_graph = new PieGraph( $p_graph_width, $p_graph_height );
		$t_graph->img->SetMargin( 40, 40, 40, 100 );
		$t_graph->title->Set( $p_title );
		$t_graph->title->SetFont( $t_graph_font, FS_BOLD );

		$t_graph->SetMarginColor( 'white' );
		$t_graph->SetFrame( false );

		$t_graph->legend->Pos( $p_poshorizontal, $p_posvertical );
		$t_graph->legend->SetFont( $t_graph_font );

		$t_plot1 = new PiePlot3d( array_values( $p_metrics ) );

		# should be reversed?
		$t_plot1->SetTheme( 'earth' );

		# $t_plot1->SetTheme("sand");
		$t_plot1->SetCenter( $p_center );
		$t_plot1->SetAngle( 60 );
		$t_plot1->SetLegends( array_keys( $p_metrics ) );

		# Label format
		$t_plot1->value->SetFormat( '%2.0f' );
		$t_plot1->value->Show();
		$t_plot1->value->SetFont( $t_graph_font );

		$t_graph->Add( $t_plot1 );
		if( helper_show_query_count() ) {
			$t_graph->subtitle->Set( db_count_queries() . ' queries (' . db_time_queries() . 'sec)' );
			$t_graph->subtitle->SetFont( $t_graph_font, FS_NORMAL, 8 );
		}
		$t_graph->Stroke();
	}
}

/**
 * Cumulative line graph
 *
 * @param array   $p_metrics      Graph Data.
 * @param integer $p_graph_width  Width of graph in pixels.
 * @param integer $p_graph_height Height of graph in pixels.
 * @return void
 */
function graph_cumulative_bydate( array $p_metrics, $p_graph_width = 300, $p_graph_height = 380 ) {
	$t_graph_font = graph_get_font();
	error_check( is_array( $p_metrics ) ? count( $p_metrics ) : 0, plugin_lang_get( 'cumulative' ) . ' ' . lang_get( 'by_date' ) );

	if( plugin_config_get( 'eczlibrary' ) == ON ) {
		$t_graph = new ezcGraphLineChart();

		$t_graph->background->color = '#FFFFFF';

		$t_graph->xAxis = new ezcGraphChartElementNumericAxis();

		$t_graph->data[0] = new ezcGraphArrayDataSet( $p_metrics[0] );
		$t_graph->data[0]->label = plugin_lang_get( 'legend_reported' );
		$t_graph->data[0]->color = '#FF0000';

		$t_graph->data[1] = new ezcGraphArrayDataSet( $p_metrics[1] );
		$t_graph->data[1]->label = plugin_lang_get( 'legend_resolved' );
		$t_graph->data[1]->color = '#0000FF';

		$t_graph->data[2] = new ezcGraphArrayDataSet( $p_metrics[2] );
		$t_graph->data[2]->label = plugin_lang_get( 'legend_still_open' );
		$t_graph->data[2]->color = '#000000';

		$t_graph->additionalAxis[2] = $t_n_axis = new ezcGraphChartElementNumericAxis();
		$t_n_axis->chartPosition = 1;
		$t_n_axis->background = '#005500';
		$t_n_axis->border = '#005500';
		$t_n_axis->position = ezcGraph::BOTTOM;
		$t_graph->data[2]->yAxis = $t_n_axis;

		$t_graph->xAxis->labelCallback =  'graph_date_format';
		$t_graph->xAxis->axisLabelRenderer = new ezcGraphAxisRotatedLabelRenderer();
		$t_graph->xAxis->axisLabelRenderer->angle = -45;

		$t_graph->legend->position      = ezcGraph::BOTTOM;
		$t_graph->legend->background    = '#FFFFFF80';

		$t_graph->driver = new ezcGraphGdDriver();
		# $t_graph->driver->options->supersampling = 1;
		$t_graph->driver->options->jpegQuality = 100;
		$t_graph->driver->options->imageFormat = IMG_JPEG;

		$t_graph->title = plugin_lang_get( 'cumulative' ) . ' ' . lang_get( 'by_date' );
		$t_graph->options->font = $t_graph_font ;

		$t_graph->renderToOutput( $p_graph_width, $p_graph_height );
	} else {
		foreach( $p_metrics[0] as $i => $t_values ) {
			if( $i > 0 ) {
				$t_plot_date[] = $i;
				$t_reported_plot[] = $p_metrics[0][$i];
				$t_resolved_plot[] = $p_metrics[1][$i];
				$t_still_open_plot[] = $p_metrics[2][$i];
			}
		}

		$t_graph = new Graph( $p_graph_width, $p_graph_height );
		$t_graph->img->SetMargin( 40, 40, 40, 170 );
		if( ON == plugin_config_get( 'jpgraph_antialias' ) ) {
			$t_graph->img->SetAntiAliasing();
		}
		$t_graph->SetScale( 'linlin' );
		$t_graph->yaxis->SetColor( 'red' );
		$t_graph->SetY2Scale( 'lin' );
		$t_graph->SetMarginColor( 'white' );
		$t_graph->SetFrame( false );
		$t_graph->title->Set( plugin_lang_get( 'cumulative' ) . ' ' . lang_get( 'by_date' ) );
		$t_graph->title->SetFont( $t_graph_font, FS_BOLD );

		$t_graph->legend->Pos( 0.05, 0.9, 'right', 'bottom' );
		$t_graph->legend->SetShadow( false );
		$t_graph->legend->SetFillColor( 'white' );
		$t_graph->legend->SetLayout( LEGEND_HOR );
		$t_graph->legend->SetFont( $t_graph_font );

		$t_graph->yaxis->scale->ticks->SetDirection( -1 );
		$t_graph->yaxis->SetFont( $t_graph_font );
		$t_graph->y2axis->SetFont( $t_graph_font );

		if( FF_FONT2 <= $t_graph_font ) {
			$t_graph->xaxis->SetLabelAngle( 60 );
		} else {
			$t_graph->xaxis->SetLabelAngle( 90 );
			# can't rotate non truetype fonts
		}
		$t_graph->xaxis->SetLabelFormatCallback( 'graph_date_format' );
		$t_graph->xaxis->SetFont( $t_graph_font );

		$t_plot1 = new LinePlot( $t_reported_plot, $t_plot_date );
		$t_plot1->SetColor( 'blue' );
		$t_plot1->SetCenter();
		$t_plot1->SetLegend( plugin_lang_get( 'legend_reported' ) );
		$t_graph->AddY2( $t_plot1 );

		$t_plot3 = new LinePlot( $t_still_open_plot, $t_plot_date );
		$t_plot3->SetColor( 'red' );
		$t_plot3->SetCenter();
		$t_plot3->SetLegend( plugin_lang_get( 'legend_still_open' ) );
		$t_graph->Add( $t_plot3 );

		$t_plot2 = new LinePlot( $t_resolved_plot, $t_plot_date );
		$t_plot2->SetColor( 'black' );
		$t_plot2->SetCenter();
		$t_plot2->SetLegend( plugin_lang_get( 'legend_resolved' ) );
		$t_graph->AddY2( $t_plot2 );

		if( helper_show_query_count() ) {
			$t_graph->subtitle->Set( db_count_queries() . ' queries (' . db_time_queries() . 'sec)' );
			$t_graph->subtitle->SetFont( $t_graph_font, FS_NORMAL, 8 );
		}
		$t_graph->Stroke();
	}
}

/**
 * Line Chart by date
 *
 * @param array   $p_metrics      Graph Data.
 * @param array   $p_labels       Labels.
 * @param string  $p_title        Title.
 * @param integer $p_graph_width  Width of graph in pixels.
 * @param integer $p_graph_height Height of graph in pixels.
 * @return void
 */
function graph_bydate( array $p_metrics, array $p_labels, $p_title, $p_graph_width = 300, $p_graph_height = 380 ) {
	$t_graph_font = graph_get_font();
	error_check( is_array( $p_metrics ) ? count( $p_metrics ) : 0, lang_get( 'by_date' ) );

	if( plugin_config_get( 'eczlibrary' ) == ON ) {
		$t_metrics = array();
		$t_dates = array_shift( $p_metrics );
		$t_cnt = count( $p_metrics );

		foreach( $t_dates as $i => $t_value ) {
			for( $j = 0; $j < $t_cnt; $j++ ) {
				$t_metrics[$j][$t_value] = $p_metrics[$j][$i];
			}
		}

		$t_graph = new ezcGraphLineChart();
		$t_graph->background->color = '#FFFFFF';

		$t_graph->xAxis = new ezcGraphChartElementNumericAxis();
		for( $k = 0; $k < $t_cnt; $k++ ) {
			$t_graph->data[$k] = new ezcGraphArrayDataSet( $t_metrics[$k] );
			$t_graph->data[$k]->label = $p_labels[$k+1];
		}

		$t_graph->xAxis->labelCallback =  'graph_date_format';
		$t_graph->xAxis->axisLabelRenderer = new ezcGraphAxisRotatedLabelRenderer();
		$t_graph->xAxis->axisLabelRenderer->angle = -60;
		$t_graph->xAxis->axisSpace = .15;

		$t_graph->legend->position      = ezcGraph::BOTTOM;
		$t_graph->legend->background    = '#FFFFFF80';

		$t_graph->driver = new ezcGraphGdDriver();
		# $t_graph->driver->options->supersampling = 1;
		$t_graph->driver->options->jpegQuality = 100;
		$t_graph->driver->options->imageFormat = IMG_JPEG;

		$t_graph->title = $p_title . ' ' . lang_get( 'by_date' );
		$t_graph->title->maxHeight = .03;
		$t_graph->options->font = $t_graph_font ;

		$t_graph->renderToOutput( $p_graph_width, $p_graph_height );
	} else {
		$t_graph = new Graph( $p_graph_width, $p_graph_height );
		$t_graph->img->SetMargin( 40, 140, 40, 100 );
		if( ON == plugin_config_get( 'jpgraph_antialias' ) ) {
			$t_graph->img->SetAntiAliasing();
		}
		$t_graph->SetScale( 'linlin' );
		$t_graph->SetMarginColor( 'white' );
		$t_graph->SetFrame( false );
		$t_graph->title->Set( $p_title . ' ' . lang_get( 'by_date' ) );
		$t_graph->title->SetFont( $t_graph_font, FS_BOLD );

		$t_graph->legend->Pos( 0.01, 0.05, 'right', 'top' );
		$t_graph->legend->SetShadow( false );
		$t_graph->legend->SetFillColor( 'white' );
		$t_graph->legend->SetLayout( LEGEND_VERT );
		$t_graph->legend->SetFont( $t_graph_font );

		$t_graph->yaxis->scale->ticks->SetDirection( -1 );
		$t_graph->yaxis->SetFont( $t_graph_font );
		$t_graph->yaxis->scale->SetAutoMin( 0 );

		if( FF_FONT2 <= $t_graph_font ) {
			$t_graph->xaxis->SetLabelAngle( 60 );
		} else {
			$t_graph->xaxis->SetLabelAngle( 90 );
			# can't rotate non truetype fonts
		}
		$t_graph->xaxis->SetLabelFormatCallback( 'graph_date_format' );
		$t_graph->xaxis->SetFont( $t_graph_font );

		# $t_line_colours = plugin_config_get( 'jpgraph_colors' );
		# $t_count_colours = count( $t_line_colours );
		$t_lines = count( $p_metrics ) - 1;
		$t_line = array();
		for( $i = 1;$i <= $t_lines;$i++ ) {
			$t_line[$i] = new LinePlot( $p_metrics[$i], $p_metrics[0] );
			# $t_line[$i]->SetColor( $t_line_colours[$i % $t_count_colours] );
			$t_line[$i]->SetCenter();
			$t_line[$i]->SetLegend( $p_labels[$i] );
			$t_graph->Add( $t_line[$i] );
		}

		if( helper_show_query_count() ) {
			$t_graph->subtitle->Set( db_count_queries() . ' queries (' . db_time_queries() . 'sec)' );
			$t_graph->subtitle->SetFont( $t_graph_font, FS_NORMAL, 8 );
		}
		$t_graph->Stroke();
	}
}

/**
 * Calculate total metrics
 *
 * @param array $p_metrics Data.
 * @return array
 */
function graph_total_metrics( array $p_metrics ) {
	foreach( $p_metrics['open'] as $t_enum => $t_value ) {
		$t_total[$t_enum] = $t_value + $p_metrics['resolved'][$t_enum] + $p_metrics['closed'][$t_enum];
	}
	return $t_total;
}

/**
 * summarize metrics by a single ENUM field in the bug table
 *
 * @param string $p_enum_string Enumeration string.
 * @param string $p_enum        Enumeration field.
 * @return array
 */
function create_bug_enum_summary( $p_enum_string, $p_enum ) {
	$t_project_id = helper_get_current_project();
	$t_user_id = auth_get_current_user_id();
	$t_specific_where = ' AND ' . helper_project_specific_where( $t_project_id, $t_user_id );

	$t_metrics = array();
	$t_assoc_array = MantisEnum::getAssocArrayIndexedByValues( $p_enum_string );

	if( !db_field_exists( $p_enum, db_get_table( 'bug' ) ) ) {
		trigger_error( ERROR_DB_FIELD_NOT_FOUND, ERROR );
	}

	foreach ( $t_assoc_array as $t_value => $t_label ) {
		$t_query = 'SELECT COUNT(*) FROM {bug} WHERE ' . $p_enum . '=' . db_param() . ' ' . $t_specific_where;
		$t_result = db_query( $t_query, array( $t_value ) );
		$t_metrics[$t_label] = db_result( $t_result, 0 );
	}

	return $t_metrics;
}

/**
 * Function which gives the absolute values according to the status (opened/closed/resolved)
 *
 * @param string $p_enum_string Enumeration string.
 * @param string $p_enum        Enumeration field.
 * @return array
 */
function enum_bug_group( $p_enum_string, $p_enum ) {
	$t_project_id = helper_get_current_project();
	$t_user_id = auth_get_current_user_id();
	$t_res_val = config_get( 'bug_resolved_status_threshold' );
	$t_clo_val = config_get( 'bug_closed_status_threshold' );
	$t_specific_where = ' AND ' . helper_project_specific_where( $t_project_id, $t_user_id );

	if( !db_field_exists( $p_enum, db_get_table( 'bug' ) ) ) {
		trigger_error( ERROR_DB_FIELD_NOT_FOUND, ERROR );
	}

	$t_array_indexed_by_enum_values = MantisEnum::getAssocArrayIndexedByValues( $p_enum_string );
	foreach ( $t_array_indexed_by_enum_values as $t_value => $t_label ) {
		# Calculates the number of bugs opened and puts the results in a table
		$t_query = 'SELECT COUNT(*) FROM {bug}
					WHERE ' . $p_enum . '=' . db_param() . ' AND
						status<' . db_param() . ' ' . $t_specific_where;
		$t_result2 = db_query( $t_query, array( $t_value, $t_res_val ) );
		$t_metrics['open'][$t_label] = db_result( $t_result2, 0, 0 );

		# Calculates the number of bugs closed and puts the results in a table
		$t_query = 'SELECT COUNT(*) FROM {bug}
					WHERE ' . $p_enum . '=' . db_param() . ' AND
						status>=' . db_param() . ' ' . $t_specific_where;
		$t_result2 = db_query( $t_query, array( $t_value, $t_clo_val ) );
		$t_metrics['closed'][$t_label] = db_result( $t_result2, 0, 0 );

		# Calculates the number of bugs resolved and puts the results in a table
		$t_query = 'SELECT COUNT(*) FROM {bug}
					WHERE ' . $p_enum . '=' . db_param() . ' AND
						status>=' . db_param() . ' AND
						status<' . db_param() . ' ' . $t_specific_where;
		$t_result2 = db_query( $t_query, array(  $t_value, $t_res_val, $t_clo_val ) );
		$t_metrics['resolved'][$t_label] = db_result( $t_result2, 0, 0 );
	}

	return $t_metrics;
}

/**
 * Create summary table of developers
 * @return array
 */
function create_developer_summary() {
	$t_project_id = helper_get_current_project();
	$t_user_id = auth_get_current_user_id();
	$t_specific_where = ' AND ' . helper_project_specific_where( $t_project_id, $t_user_id );

	$t_res_val = config_get( 'bug_resolved_status_threshold' );
	$t_clo_val = config_get( 'bug_closed_status_threshold' );

	$t_query = 'SELECT handler_id, status FROM {bug} WHERE handler_id > 0 ' . $t_specific_where;
	$t_result = db_query( $t_query );

	$t_handler_arr = array();
	$t_handlers = array();
	while( $t_row = db_fetch_array( $t_result ) ) {
		if( !isset( $t_handler_arr[$t_row['handler_id']] ) ) {
			$t_handler_arr[$t_row['handler_id']]['res'] = 0;
			$t_handler_arr[$t_row['handler_id']]['open'] = 0;
			$t_handler_arr[$t_row['handler_id']]['close'] = 0;
			$t_handlers[] = $t_row['handler_id'];
		}
		if( $t_row['status'] >= $t_res_val ) {
			if( $t_row['status'] >= $t_clo_val ) {
				$t_handler_arr[$t_row['handler_id']]['close']++;
			} else {
				$t_handler_arr[$t_row['handler_id']]['res']++;
			}
		} else {
			$t_handler_arr[$t_row['handler_id']]['open']++;
		}
	}

	if( count( $t_handler_arr ) == 0 ) {
		return array( 'open' => array() );
	}

	user_cache_array_rows( $t_handlers );

	foreach( $t_handler_arr as $t_handler => $t_data ) {
		$t_username = user_get_name( $t_handler );

		$t_metrics['open'][$t_username] = $t_data['open'];
		$t_metrics['resolved'][$t_username] = $t_data['res'];
		$t_metrics['closed'][$t_username] = $t_data['close'];
	}
	ksort( $t_metrics );

	return $t_metrics;
}

/**
 * Create summary table of reporters
 * @return array
 */
function create_reporter_summary() {
	$t_project_id = helper_get_current_project();
	$t_user_id = auth_get_current_user_id();
	$t_specific_where = helper_project_specific_where( $t_project_id, $t_user_id );

	$t_query = 'SELECT reporter_id FROM {bug} WHERE ' . $t_specific_where;
	$t_result = db_query( $t_query );

	$t_reporter_arr = array();
	$t_reporters = array();
	while( $t_row = db_fetch_array( $t_result ) ) {
		if( isset( $t_reporter_arr[$t_row['reporter_id']] ) ) {
			$t_reporter_arr[$t_row['reporter_id']]++;
		} else {
			$t_reporter_arr[$t_row['reporter_id']] = 1;
			$t_reporters[] = $t_row['reporter_id'];
		}
	}

	if( count( $t_reporter_arr ) == 0 ) {
		return array();
	}

	user_cache_array_rows( $t_reporters );

	foreach( $t_reporter_arr as $t_reporter => $t_count ) {
		$t_metrics[user_get_name( $t_reporter )] = $t_count;
	}
	ksort( $t_metrics );

	return $t_metrics;
}

/**
 * Create summary table of categories
 * @return array
 */
function create_category_summary() {
	$t_project_id = helper_get_current_project();
	$t_user_id = auth_get_current_user_id();
	$t_specific_where = helper_project_specific_where( $t_project_id, $t_user_id );

	$t_query = 'SELECT id, name FROM {category}
				WHERE ' . $t_specific_where . ' OR project_id=' . ALL_PROJECTS . '
				ORDER BY name';
	$t_result = db_query( $t_query );
	$t_category_count = db_num_rows( $t_result );

	$t_metrics = array();
	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_cat_name = $t_row['name'];
		$t_cat_id = $t_row['id'];
		$t_query = 'SELECT COUNT(*) FROM {bug} WHERE category_id=' . db_param() . ' AND ' . $t_specific_where;
		$t_result2 = db_query( $t_query, array( $t_cat_id ) );
		if( isset($t_metrics[$t_cat_name]) ) {
			$t_metrics[$t_cat_name] = $t_metrics[$t_cat_name] + db_result( $t_result2, 0, 0 );
		} else {
			if( db_result( $t_result2, 0, 0 ) > 0 ) {
			    $t_metrics[$t_cat_name] = db_result( $t_result2, 0, 0 );
			}
		}
	}

	return $t_metrics;
}

/**
 * Create cumulative graph by date
 * @return array
 */
function create_cumulative_bydate() {
	$t_clo_val = config_get( 'bug_closed_status_threshold' );
	$t_res_val = config_get( 'bug_resolved_status_threshold' );

	$t_project_id = helper_get_current_project();
	$t_user_id = auth_get_current_user_id();
	$t_specific_where = helper_project_specific_where( $t_project_id, $t_user_id );

	# Get all the submitted dates
	$t_query = 'SELECT date_submitted FROM {bug} WHERE ' . $t_specific_where . ' ORDER BY date_submitted';
	$t_result = db_query( $t_query );

	while( $t_row = db_fetch_array( $t_result ) ) {
		# rationalise the timestamp to a day to reduce the amount of data
		$t_date = $t_row['date_submitted'];
		$t_date = (int)( $t_date / SECONDS_PER_DAY );

		if( isset( $t_calc_metrics[$t_date] ) ) {
			$t_calc_metrics[$t_date][0]++;
		} else {
			$t_calc_metrics[$t_date] = array( 1, 0, 0, );
		}
	}

	# ## Get all the dates where a transition from not resolved to resolved may have happened
	#    also, get the last updated date for the bug as this may be all the information we have
	$t_query = 'SELECT {bug}.id, last_updated, date_modified, new_value, old_value
			FROM {bug} LEFT JOIN {bug_history}
			ON {bug}.id = {bug_history}.bug_id
			WHERE ' . $t_specific_where . '
						AND {bug}.status >= ' . db_param() . '
						AND ( ( {bug_history}.new_value >= ' . db_param() . '
								AND {bug_history}.field_name = \'status\' )
						OR {bug_history}.id is NULL )
			ORDER BY {bug}.id, date_modified ASC';
	$t_result = db_query( $t_query, array( $t_res_val, (string)$t_res_val ) );
	$t_bug_count = db_num_rows( $t_result );

	$t_last_id = 0;
	$t_last_date = 0;

	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_id = $t_row['id'];

		# if h_last_updated is NULL, there were no appropriate history records
		#  (i.e. pre 0.18 data), use last_updated from bug table instead
		if( null == $t_row['date_modified'] ) {
			$t_date = $t_row['last_updated'];
		} else {
			if( $t_res_val > $t_row['old_value'] ) {
				$t_date = $t_row['date_modified'];
			}
		}
		if( $t_id <> $t_last_id ) {
			if( 0 <> $t_last_id ) {

				# rationalise the timestamp to a day to reduce the amount of data
				$t_date_index = (int)( $t_last_date / SECONDS_PER_DAY );

				if( isset( $t_calc_metrics[$t_date_index] ) ) {
					$t_calc_metrics[$t_date_index][1]++;
				} else {
					$t_calc_metrics[$t_date_index] = array(
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

	ksort( $t_calc_metrics );

	$t_last_opened = 0;
	$t_last_resolved = 0;
	foreach( $t_calc_metrics as $i => $t_values ) {
		$t_date = $i * SECONDS_PER_DAY;
		$t_metrics[0][$t_date] = $t_last_opened = $t_calc_metrics[$i][0] + $t_last_opened;
		$t_metrics[1][$t_date] = $t_last_resolved = $t_calc_metrics[$i][1] + $t_last_resolved;
		$t_metrics[2][$t_date] = $t_metrics[0][$t_date] - $t_metrics[1][$t_date];
	}
	return $t_metrics;
}

/**
 * Get formatted date string
 *
 * @param integer $p_date Date.
 * @return string
 */
function graph_date_format( $p_date ) {
	return date( config_get( 'short_date_format' ), $p_date );
}

/**
 * Check that there is enough data to create graph
 *
 * @param integer $p_bug_count Bug count.
 * @param string  $p_title     Title.
 * @return void
 */
function error_check( $p_bug_count, $p_title ) {
	if( 0 == $p_bug_count ) {
		error_text( $p_title, plugin_lang_get( 'not_enough_data' ) );
	}
}

/**
 * Display Error 'graph'
 *
 * @param string $p_title Error title.
 * @param string $p_text  Error text.
 * @todo check error graphs do not support utf8
 * @return void
 */
function error_text( $p_title, $p_text ) {
	if( OFF == plugin_config_get( 'eczlibrary' ) ) {
		$t_graph = new CanvasGraph( 300, 380 );
		$t_graph_font = graph_get_font();

		$t_text = new Text( $p_text, 150, 100 );
		$t_text->Align( 'center', 'center', 'center' );
		$t_text->SetFont( $t_graph_font, FS_BOLD );
		$t_graph->title->Set( $p_title );
		$t_graph->title->SetFont( $t_graph_font, FS_BOLD );
		$t_graph->AddText( $t_text );
		$t_graph->Stroke();
	} else {
		$t_image = imagecreate( 300, 300 );
		$t_text_color = imagecolorallocate( $t_image, 0, 0, 0 );
		imagestring( $t_image, 5, 0, 0, $p_text, $t_text_color );
		header( 'Content-type: image/png' );
		imagepng( $t_image );
		imagedestroy( $t_image );
	}
	die;
}
