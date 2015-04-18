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
 * @package CoreAPI
 * @subpackage GraphAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */


if( OFF == plugin_config_get( 'eczlibrary' ) ) {
	$t_font_path = get_font_path();
	if( $t_font_path !== '' && !defined('TTF_DIR') ) {
		define( 'TTF_DIR', $t_font_path );
	}
	$t_jpgraph_path = plugin_config_get( 'jpgraph_path', '' );
	if( $t_jpgraph_path !== '' ) {
		set_include_path(get_include_path() . PATH_SEPARATOR . $t_jpgraph_path );
		$ip = get_include_path();
		require_once( 'jpgraph.php' );
		require_once( 'jpgraph_line.php' );
		require_once( 'jpgraph_bar.php' );
		require_once( 'jpgraph_pie.php' );
		require_once( 'jpgraph_pie3d.php' );
		require_once( 'jpgraph_canvas.php' );
	} else {
		require_once( 'jpgraph/jpgraph.php' );
		require_once( 'jpgraph/jpgraph_line.php' );
		require_once( 'jpgraph/jpgraph_bar.php' );
		require_once( 'jpgraph/jpgraph_pie.php' );
		require_once( 'jpgraph/jpgraph_pie3d.php' );
		require_once( 'jpgraph/jpgraph_canvas.php' );
	}
} else {
	require_once( 'ezc/Base/src/base.php' );
}

function graph_get_font() {
	$t_font = plugin_config_get( 'font', 'arial' );

	if ( plugin_config_get( 'eczlibrary' ) == ON ) {
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
		if( empty($t_font_path) ) {
			error_text('Unable to read/find font', 'Unable to read/find font');
		}
		$t_font_file = $t_font_path . $t_font;
		if( file_exists($t_font_file) === false || is_readable($t_font_file) === false ) {
			error_text('Unable to read/find font', 'Unable to read/find font');
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

# ## Graph API ###
# --------------------
# graphing routines
# --------------------
function graph_bar( $p_metrics, $p_title = '', $p_graph_width = 350, $p_graph_height = 400 ) {
	$t_graph_font = graph_get_font();

	error_check( is_array( $p_metrics ) ? array_sum( $p_metrics ) : 0, $p_title );

	if ( plugin_config_get( 'eczlibrary' ) == ON ) {
		$graph = new ezcGraphBarChart();
		$graph->title = $p_title;
		$graph->background->color = '#FFFFFF';
		$graph->options->font = $t_graph_font ;
		$graph->options->font->maxFontSize = 12;
		$graph->legend = false;

		$graph->data[0] = new ezcGraphArrayDataSet( $p_metrics );
		$graph->data[0]->color = '#FFFF00';

		$graph->xAxis->axisLabelRenderer = new ezcGraphAxisRotatedLabelRenderer();
		$graph->xAxis->axisLabelRenderer->angle = 45;

		$graph->driver = new ezcGraphGdDriver();
		//$graph->driver->options->supersampling = 1;
		$graph->driver->options->jpegQuality = 100;
		$graph->driver->options->imageFormat = IMG_JPEG;

		$graph->renderer->options->syncAxisFonts = false;

		$graph->renderToOutput( $p_graph_width, $p_graph_height);
	} else {
		$graph = new Graph( $p_graph_width, $p_graph_height );
		$graph->img->SetMargin( 40, 40, 40, 170 );
		if( ON == plugin_config_get( 'jpgraph_antialias' ) ) {
			$graph->img->SetAntiAliasing();
		}
		$graph->SetScale( 'textlin' );
		$graph->SetMarginColor( 'white' );
		$graph->SetFrame( false );
		$graph->title->Set( $p_title );
		$graph->title->SetFont( $t_graph_font, FS_BOLD );
		$graph->xaxis->SetTickLabels( array_keys( $p_metrics ) );
		if( FF_FONT2 <= $t_graph_font ) {
			$graph->xaxis->SetLabelAngle( 60 );
		} else {
			$graph->xaxis->SetLabelAngle( 90 );
			# can't rotate non truetype fonts
		}
		$graph->xaxis->SetFont( $t_graph_font );

		$graph->legend->SetFont( $t_graph_font );

		$graph->yaxis->scale->ticks->SetDirection( -1 );
		$graph->yaxis->SetFont( $t_graph_font );

		$p1 = new BarPlot( array_values( $p_metrics ) );
		$p1->SetFillColor( 'yellow' );
		$p1->SetWidth( 0.8 );
		$graph->Add( $p1 );
		if( helper_show_queries() ) {
			$graph->subtitle->Set( db_count_queries() . ' queries (' . db_time_queries() . 'sec)' );
			$graph->subtitle->SetFont( $t_graph_font, FS_NORMAL, 8 );
		}

		$graph->Stroke();
	}
}

# Function which displays the charts using the absolute values according to the status (opened/closed/resolved)
function graph_group( $p_metrics, $p_title = '', $p_graph_width = 350, $p_graph_height = 400, $p_baseline = 100 ) {

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
	$total = graph_total_metrics( $p_metrics );

	if ( plugin_config_get( 'eczlibrary' ) == ON ) {
		$graph = new ezcGraphBarChart();
		$graph->title = $p_title;
		$graph->background->color = '#FFFFFF';
		$graph->options->font = $t_graph_font ;
		$graph->options->font->maxFontSize = 12;
		$graph->legend = false;

		foreach( array( 'open', 'resolved', 'closed' ) as $t_label ) {
			$graph->data[$t_label] = new ezcGraphArrayDataSet( $p_metrics[$t_label] );
		}
		$graph->data['total'] = new ezcGraphArrayDataSet( $total );
		//$graph->data['total']->displayType = ezcGraph::LINE;
		//$graph->data['total']->barMargin = -20;
		$graph->options->fillLines = 210;
		$graph->xAxis->axisLabelRenderer = new ezcGraphAxisRotatedLabelRenderer();
		$graph->xAxis->axisLabelRenderer->angle = 45;

		$graph->driver = new ezcGraphGdDriver();
		//$graph->driver->options->supersampling = 1;
		$graph->driver->options->jpegQuality = 100;
		$graph->driver->options->imageFormat = IMG_JPEG;

		$graph->renderer->options->syncAxisFonts = false;

		$graph->renderToOutput( $p_graph_width, $p_graph_height);
	} else {
		# defines margin according to height
		$graph = new Graph( $p_graph_width, $p_graph_height );
		$graph->img->SetMargin( 45, 35, 35, $p_baseline );
		if( ON == plugin_config_get( 'jpgraph_antialias' ) ) {
			$graph->img->SetAntiAliasing();
		}
		$graph->SetScale( 'textlin' );
		$graph->SetMarginColor( 'white' );
		$graph->SetFrame( false );
		$graph->title->SetFont( $t_graph_font, FS_BOLD );
		$graph->title->Set( $p_title );
		$graph->xaxis->SetTickLabels( array_keys( $p_metrics['open'] ) );
		if( FF_FONT2 <= $t_graph_font ) {
			$graph->xaxis->SetLabelAngle( 60 );
		} else {
			$graph->xaxis->SetLabelAngle( 90 );
			# can't rotate non truetype fonts
		}
		$graph->xaxis->SetFont( $t_graph_font );
		$graph->legend->Pos( 0.05, 0.08 );
		$graph->legend->SetFont( $t_graph_font );

		$graph->yaxis->scale->ticks->SetDirection( -1 );
		$graph->yaxis->SetFont( $t_graph_font );
		$graph->yscale->SetGrace( 10 );

		# adds on the same graph
		$tot = new BarPlot( array_values( $total ) );
		$tot->SetFillColor( 'lightblue' );
		$tot->SetWidth( 0.7 );
		$tot->SetLegend( plugin_lang_get( 'legend_total' ) );
		$graph->Add( $tot );

		$p1 = new BarPlot( array_values( $p_metrics['open'] ) );
		$p1->SetFillColor( 'yellow' );
		$p1->SetWidth( 1 );
		$p1->SetLegend( plugin_lang_get( 'legend_opened' ) );

		$p2 = new BarPlot( array_values( $p_metrics['closed'] ) );
		$p2->SetFillColor( 'blue' );
		$p2->SetWidth( 1 );
		$p2->SetLegend( plugin_lang_get( 'legend_closed' ) );

		$p3 = new BarPlot( array_values( $p_metrics['resolved'] ) );
		$p3->SetFillColor( 'red' );
		$p3->SetWidth( 1 );
		$p3->SetLegend( plugin_lang_get( 'legend_resolved' ) );

		$gbplot = new GroupBarPlot( array( $p1, $p3, $p2 ) );
		$graph->Add( $gbplot );

		if( helper_show_queries() ) {
			$graph->subtitle->Set( db_count_queries() . ' queries (' . db_time_queries() . 'sec)' );
			$graph->subtitle->SetFont( $t_graph_font, FS_NORMAL, 8 );
		}
	$graph->Stroke();
	}
}

# --------------------
# Function that displays pie charts
function graph_pie( $p_metrics, $p_title = '', $p_graph_width = 500, $p_graph_height = 350, $p_center = 0.4, $p_poshorizontal = 0.10, $p_posvertical = 0.09 ) {
	$t_graph_font = graph_get_font();

	error_check( is_array( $p_metrics ) ? array_sum( $p_metrics ) : 0, $p_title );

	if ( plugin_config_get( 'eczlibrary' ) == ON ) {
		$graph = new ezcGraphPieChart();
		$graph->title = $p_title;
		$graph->background->color = '#FFFFFF';
		$graph->options->font = $t_graph_font ;
		$graph->options->font->maxFontSize = 12;
		$graph->legend = false;

		$graph->data[0] = new ezcGraphArrayDataSet( $p_metrics );
		$graph->data[0]->color = '#FFFF00';

		$graph->renderer = new ezcGraphRenderer3d();
		$graph->renderer->options->dataBorder = false;
		$graph->renderer->options->pieChartShadowSize = 10;
		$graph->renderer->options->pieChartGleam = .5;
		$graph->renderer->options->pieChartHeight = 16;
		$graph->renderer->options->legendSymbolGleam = .5;

		$graph->driver = new ezcGraphGdDriver();
		//$graph->driver->options->supersampling = 1;
		$graph->driver->options->jpegQuality = 100;
		$graph->driver->options->imageFormat = IMG_JPEG;

		$graph->renderer->options->syncAxisFonts = false;

		$graph->renderToOutput( $p_graph_width, $p_graph_height);
	} else {
		$graph = new PieGraph( $p_graph_width, $p_graph_height );
		$graph->img->SetMargin( 40, 40, 40, 100 );
		$graph->title->Set( $p_title );
		$graph->title->SetFont( $t_graph_font, FS_BOLD );

		$graph->SetMarginColor( 'white' );
		$graph->SetFrame( false );

		$graph->legend->Pos( $p_poshorizontal, $p_posvertical );
		$graph->legend->SetFont( $t_graph_font );

		$p1 = new PiePlot3d( array_values( $p_metrics ) );

		// should be reversed?
		$p1->SetTheme( 'earth' );

		# $p1->SetTheme("sand");
		$p1->SetCenter( $p_center );
		$p1->SetAngle( 60 );
		$p1->SetLegends( array_keys( $p_metrics ) );

		# Label format
		$p1->value->SetFormat( '%2.0f' );
		$p1->value->Show();
		$p1->value->SetFont( $t_graph_font );

		$graph->Add( $p1 );
		if( helper_show_queries() ) {
			$graph->subtitle->Set( db_count_queries() . ' queries (' . db_time_queries() . 'sec)' );
			$graph->subtitle->SetFont( $t_graph_font, FS_NORMAL, 8 );
		}
		$graph->Stroke();
	}
}

# --------------------
function graph_cumulative_bydate( $p_metrics, $p_graph_width = 300, $p_graph_height = 380 ) {

	$t_graph_font = graph_get_font();
	error_check( is_array( $p_metrics ) ? count( $p_metrics ) : 0, plugin_lang_get( 'cumulative' ) . ' ' . lang_get( 'by_date' ) );

	if ( plugin_config_get( 'eczlibrary' ) == ON ) {
		$graph = new ezcGraphLineChart();

		$graph->background->color = '#FFFFFF';

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

		$graph->legend->position      = ezcGraph::BOTTOM;
		$graph->legend->background    = '#FFFFFF80';

		$graph->driver = new ezcGraphGdDriver();
		//$graph->driver->options->supersampling = 1;
		$graph->driver->options->jpegQuality = 100;
		$graph->driver->options->imageFormat = IMG_JPEG;

		$graph->title = plugin_lang_get( 'cumulative' ) . ' ' . lang_get( 'by_date' );
		$graph->options->font = $t_graph_font ;

		$graph->renderToOutput( $p_graph_width, $p_graph_height);
	} else {
		foreach( $p_metrics[0] as $i => $vals ) {
			if( $i > 0 ) {
				$plot_date[] = $i;
				$reported_plot[] = $p_metrics[0][$i];
				$resolved_plot[] = $p_metrics[1][$i];
				$still_open_plot[] = $p_metrics[2][$i];
			}
		}

		$graph = new Graph( $p_graph_width, $p_graph_height );
		$graph->img->SetMargin( 40, 40, 40, 170 );
		if( ON == plugin_config_get( 'jpgraph_antialias' ) ) {
			$graph->img->SetAntiAliasing();
		}
		$graph->SetScale( 'linlin');
		$graph->yaxis->SetColor("red");
		$graph->SetY2Scale("lin");
		$graph->SetMarginColor( 'white' );
		$graph->SetFrame( false );
		$graph->title->Set( plugin_lang_get( 'cumulative' ) . ' ' . lang_get( 'by_date' ) );
		$graph->title->SetFont( $t_graph_font, FS_BOLD );

		$graph->legend->Pos( 0.05, 0.9, 'right', 'bottom' );
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
		$p1->SetLegend( plugin_lang_get( 'legend_reported' ) );
		$graph->AddY2( $p1 );

		$p3 = new LinePlot( $still_open_plot, $plot_date );
		$p3->SetColor( 'red' );
		$p3->SetCenter();
		$p3->SetLegend( plugin_lang_get( 'legend_still_open' ) );
		$graph->Add( $p3 );

		$p2 = new LinePlot( $resolved_plot, $plot_date );
		$p2->SetColor( 'black' );
		$p2->SetCenter();
		$p2->SetLegend( plugin_lang_get( 'legend_resolved' ) );
		$graph->AddY2( $p2 );

		if( helper_show_queries() ) {
			$graph->subtitle->Set( db_count_queries() . ' queries (' . db_time_queries() . 'sec)' );
			$graph->subtitle->SetFont( $t_graph_font, FS_NORMAL, 8 );
		}
		$graph->Stroke();
	}
}

# --------------------
function graph_bydate( $p_metrics, $p_labels, $p_title, $p_graph_width = 300, $p_graph_height = 380 ) {
	$t_graph_font = graph_get_font();
	error_check( is_array( $p_metrics ) ? count( $p_metrics ) : 0, lang_get( 'by_date' ) );

	if ( plugin_config_get( 'eczlibrary' ) == ON ) {
		$t_metrics = array();
		$t_dates = array_shift($p_metrics); //[0];
		$t_cnt = count($p_metrics);

		foreach( $t_dates as $i => $val ) {
				//$t_metrics[$val]
				for($j = 0; $j < $t_cnt; $j++ ) {
					$t_metrics[$j][$val] = $p_metrics[$j][$i];
				}
		}

		$graph = new ezcGraphLineChart();
		$graph->background->color = '#FFFFFF';

		$graph->xAxis = new ezcGraphChartElementNumericAxis();
		for($k = 0; $k < $t_cnt; $k++ ) {
			$graph->data[$k] = new ezcGraphArrayDataSet( $t_metrics[$k] );
			$graph->data[$k]->label = $p_labels[$k+1];
		}

		$graph->xAxis->labelCallback =  'graph_date_format';
		$graph->xAxis->axisLabelRenderer = new ezcGraphAxisRotatedLabelRenderer();
		$graph->xAxis->axisLabelRenderer->angle = -60;
		$graph->xAxis->axisSpace = .15;

		$graph->legend->position      = ezcGraph::BOTTOM;
		$graph->legend->background    = '#FFFFFF80';

		$graph->driver = new ezcGraphGdDriver();
		//$graph->driver->options->supersampling = 1;
		$graph->driver->options->jpegQuality = 100;
		$graph->driver->options->imageFormat = IMG_JPEG;

		$graph->title = $p_title . ' ' . lang_get( 'by_date' );
		$graph->title->maxHeight = .03;
		$graph->options->font = $t_graph_font ;

		$graph->renderToOutput( $p_graph_width, $p_graph_height);
	} else {
		$graph = new Graph( $p_graph_width, $p_graph_height );
		$graph->img->SetMargin( 40, 140, 40, 100 );
		if( ON == plugin_config_get( 'jpgraph_antialias' ) ) {
			$graph->img->SetAntiAliasing();
		}
		$graph->SetScale( 'linlin' );
		$graph->SetMarginColor( 'white' );
		$graph->SetFrame( false );
		$graph->title->Set( $p_title . ' ' . lang_get( 'by_date' ) );
		$graph->title->SetFont( $t_graph_font, FS_BOLD );

		$graph->legend->Pos( 0.01, 0.05, 'right', 'top' );
		$graph->legend->SetShadow( false );
		$graph->legend->SetFillColor( 'white' );
		$graph->legend->SetLayout( LEGEND_VERT );
		$graph->legend->SetFont( $t_graph_font );

		$graph->yaxis->scale->ticks->SetDirection( -1 );
		$graph->yaxis->SetFont( $t_graph_font );
		$graph->yaxis->scale->SetAutoMin( 0 );

		if( FF_FONT2 <= $t_graph_font ) {
			$graph->xaxis->SetLabelAngle( 60 );
		} else {
			$graph->xaxis->SetLabelAngle( 90 );
			# can't rotate non truetype fonts
		}
		$graph->xaxis->SetLabelFormatCallback( 'graph_date_format' );
		$graph->xaxis->SetFont( $t_graph_font );

/*		$t_line_colours = plugin_config_get( 'jpgraph_colors' );
		$t_count_colours = count( $t_line_colours );*/
		$t_lines = count( $p_metrics ) - 1;
		$t_line = array();
		for( $i = 1;$i <= $t_lines;$i++ ) {
			$t_line[$i] = new LinePlot( $p_metrics[$i], $p_metrics[0] );
			//$t_line[$i]->SetColor( $t_line_colours[$i % $t_count_colours] );
			$t_line[$i]->SetCenter();
			$t_line[$i]->SetLegend( $p_labels[$i] );
			$graph->Add( $t_line[$i] );
		}

		if( helper_show_queries() ) {
			$graph->subtitle->Set( db_count_queries() . ' queries (' . db_time_queries() . 'sec)' );
			$graph->subtitle->SetFont( $t_graph_font, FS_NORMAL, 8 );
		}
		$graph->Stroke();
	}
}

# --------------------
# utilities
# --------------------
function graph_total_metrics( $p_metrics ) {
	foreach( $p_metrics['open'] as $t_enum => $t_value ) {
		$total[$t_enum] = $t_value + $p_metrics['resolved'][$t_enum] + $p_metrics['closed'][$t_enum];
	}
	return $total;
}

# --------------------
# Data Extractions
# --------------------
# --------------------
# summarize metrics by a single field in the bug table
function create_bug_enum_summary( $p_enum_string, $p_enum ) {
	$t_project_id = helper_get_current_project();
	$t_bug_table = db_get_table( 'mantis_bug_table' );
	$t_user_id = auth_get_current_user_id();
	$specific_where = " AND " . helper_project_specific_where( $t_project_id, $t_user_id );

	$t_metrics = array();
	$t_assoc_array = MantisEnum::getAssocArrayIndexedByValues( $p_enum_string );

	if( !db_field_exists( $p_enum, $t_bug_table ) ) {
		trigger_error( ERROR_DB_FIELD_NOT_FOUND, ERROR );
	}

	foreach ( $t_assoc_array as $t_value => $t_label  ) {
		$query = "SELECT COUNT(*)
					FROM $t_bug_table
					WHERE $p_enum=" . db_param() . " $specific_where";
		$result = db_query_bound( $query, array( $t_value ) );
		$t_metrics[$t_label] = db_result( $result, 0 );
	}

	return $t_metrics;
}

# Function which gives the absolute values according to the status (opened/closed/resolved)
function enum_bug_group( $p_enum_string, $p_enum ) {
	$t_bug_table = db_get_table( 'mantis_bug_table' );

	$t_project_id = helper_get_current_project();
	$t_bug_table = db_get_table( 'mantis_bug_table' );
	$t_user_id = auth_get_current_user_id();
	$t_res_val = config_get( 'bug_resolved_status_threshold' );
	$t_clo_val = config_get( 'bug_closed_status_threshold' );
	$specific_where = " AND " . helper_project_specific_where( $t_project_id, $t_user_id );

	if( !db_field_exists( $p_enum, $t_bug_table ) ) {
		trigger_error( ERROR_DB_FIELD_NOT_FOUND, ERROR );
	}

	$t_array_indexed_by_enum_values = MantisEnum::getAssocArrayIndexedByValues( $p_enum_string );
	$enum_count = count( $t_array_indexed_by_enum_values );
	foreach ( $t_array_indexed_by_enum_values as $t_value => $t_label ) {
		# Calculates the number of bugs opened and puts the results in a table
		$query = "SELECT COUNT(*)
					FROM $t_bug_table
					WHERE $p_enum=" . db_param() . " AND
						status<" . db_param() . " $specific_where";
		$result2 = db_query_bound( $query, array( $t_value, $t_res_val ) );
		$t_metrics['open'][$t_label] = db_result( $result2, 0, 0 );

		# Calculates the number of bugs closed and puts the results in a table
		$query = "SELECT COUNT(*)
					FROM $t_bug_table
					WHERE $p_enum=" . db_param() . " AND
						status>=" . db_param() . " $specific_where";
		$result2 = db_query_bound( $query, array( $t_value, $t_clo_val ) );
		$t_metrics['closed'][$t_label] = db_result( $result2, 0, 0 );

		# Calculates the number of bugs resolved and puts the results in a table
		$query = "SELECT COUNT(*)
					FROM $t_bug_table
					WHERE $p_enum=" . db_param() . " AND
						status>=" . db_param() . " AND
						status<" . db_param() . " $specific_where";
		$result2 = db_query_bound( $query, array(  $t_value, $t_res_val, $t_clo_val ) );
		$t_metrics['resolved'][$t_label] = db_result( $result2, 0, 0 );
	}

	# ## end for

	return $t_metrics;
}

# --------------------
function create_developer_summary() {
	$t_project_id = helper_get_current_project();
	$t_user_table = db_get_table( 'mantis_user_table' );
	$t_bug_table = db_get_table( 'mantis_bug_table' );
	$t_user_id = auth_get_current_user_id();
	$specific_where = " AND " . helper_project_specific_where( $t_project_id, $t_user_id );

	$t_res_val = config_get( 'bug_resolved_status_threshold' );
	$t_clo_val = config_get( 'bug_closed_status_threshold' );

	$query = "SELECT handler_id, status
				 FROM $t_bug_table
				 WHERE handler_id > 0 $specific_where";
	$result = db_query_bound( $query );
	$t_total_handled = db_num_rows( $result );

	$t_handler_arr = array();
	$t_handlers = array();
	for( $i = 0;$i < $t_total_handled;$i++ ) {
		$row = db_fetch_array( $result );
		if( !isset( $t_handler_arr[$row['handler_id']] ) ) {
			$t_handler_arr[$row['handler_id']]['res'] = 0;
			$t_handler_arr[$row['handler_id']]['open'] = 0;
			$t_handler_arr[$row['handler_id']]['close'] = 0;
			$t_handlers[] = $row['handler_id'];
		}
		if( $row['status'] >= $t_res_val ) {
			if( $row['status'] >= $t_clo_val ) {
				$t_handler_arr[$row['handler_id']]['close']++;
			} else {
				$t_handler_arr[$row['handler_id']]['res']++;
			}
		} else {
			$t_handler_arr[$row['handler_id']]['open']++;
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
	ksort($t_metrics);

	# end for
	return $t_metrics;
}

# --------------------
function create_reporter_summary() {
	global $reporter_name, $reporter_count;

	$t_project_id = helper_get_current_project();
	$t_user_table = db_get_table( 'mantis_user_table' );
	$t_bug_table = db_get_table( 'mantis_bug_table' );
	$t_user_id = auth_get_current_user_id();
	$specific_where = helper_project_specific_where( $t_project_id, $t_user_id );

	$query = "SELECT reporter_id
				 FROM $t_bug_table
				 WHERE $specific_where";
	$result = db_query_bound( $query );
	$t_total_reported = db_num_rows( $result );

	$t_reporter_arr = array();
	$t_reporters = array();
	for( $i = 0;$i < $t_total_reported;$i++ ) {
		$row = db_fetch_array( $result );

		if( isset( $t_reporter_arr[$row['reporter_id']] ) ) {
			$t_reporter_arr[$row['reporter_id']]++;
		} else {
			$t_reporter_arr[$row['reporter_id']] = 1;
			$t_reporters[] = $row['reporter_id'];
		}
	}

	if( count( $t_reporter_arr ) == 0 ) {
		return array();
	}

	user_cache_array_rows( $t_reporters );

	foreach( $t_reporter_arr as $t_reporter => $t_count ) {
		$t_metrics[ user_get_name( $t_reporter ) ] = $t_count;
	}
	ksort($t_metrics);

	# end for
	return $t_metrics;
}

# --------------------
function create_category_summary() {
	global $category_name, $category_bug_count;

	$t_project_id = helper_get_current_project();
	$t_cat_table = db_get_table( 'mantis_category_table' );
	$t_bug_table = db_get_table( 'mantis_bug_table' );
	$t_user_id = auth_get_current_user_id();
	$specific_where = helper_project_specific_where( $t_project_id, $t_user_id );

	$query = "SELECT id, name
				FROM $t_cat_table
				WHERE $specific_where OR project_id=" . ALL_PROJECTS . "
				ORDER BY name";
	$result = db_query_bound( $query );
	$category_count = db_num_rows( $result );

	$t_metrics = array();
	for( $i = 0;$i < $category_count;$i++ ) {
		$row = db_fetch_array( $result );
		$t_cat_name = $row['name'];
		$t_cat_id = $row['id'];
		$query = "SELECT COUNT(*)
					FROM $t_bug_table
					WHERE category_id=" . db_param() . " AND $specific_where";
		$result2 = db_query_bound( $query, Array( $t_cat_id ) );
		if ( isset($t_metrics[$t_cat_name]) ) {
			$t_metrics[$t_cat_name] = $t_metrics[$t_cat_name] + db_result( $result2, 0, 0 );
		} else {
      if (db_result( $result2, 0, 0 ) > 0)
					$t_metrics[$t_cat_name] = db_result( $result2, 0, 0 );
		}
	}

	# end for
	return $t_metrics;
}

# --------------------
function create_cumulative_bydate() {

	$t_clo_val = config_get( 'bug_closed_status_threshold' );
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
	$result = db_query_bound( $query );
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
						AND $t_bug_table.status >= " . db_param() . "
						AND ( ( $t_history_table.new_value >= " . db_param() . "
								AND $t_history_table.field_name = 'status' )
						OR $t_history_table.id is NULL )
			ORDER BY $t_bug_table.id, date_modified ASC";
	$result = db_query_bound( $query, array( $t_res_val, (string)$t_res_val ) );
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

function graph_date_format( $p_date ) {
	return date( config_get( 'short_date_format' ), $p_date );
}

# ----------------------------------------------------
# Check that there is enough data to create graph
# ----------------------------------------------------
function error_check( $bug_count, $title ) {
	if( 0 == $bug_count ) {
		error_text( $title, plugin_lang_get( 'not_enough_data' ) );
	}
}

function error_text( $title, $text ) {
		if( OFF == plugin_config_get( 'eczlibrary' ) ) {

			$t_graph_font = graph_get_font();

			$graph = new CanvasGraph( 300, 380 );

			$txt = new Text( $text, 150, 100 );
			$txt->Align( "center", "center", "center" );
			$txt->SetFont( $t_graph_font, FS_BOLD );
			$graph->title->Set( $title );
			$graph->title->SetFont( $t_graph_font, FS_BOLD );
			$graph->AddText( $txt );
			$graph->Stroke();
		} else {
			$im = imagecreate(300, 300);
			/* @todo check: error graphs dont support utf8 */
			$bg = imagecolorallocate($im, 255, 255, 255);
			$textcolor = imagecolorallocate($im, 0, 0, 0);
			imagestring($im, 5, 0, 0, $text, $textcolor);
			header('Content-type: image/png');
			imagepng($im);
			imagedestroy($im);
		}
	die;
}
