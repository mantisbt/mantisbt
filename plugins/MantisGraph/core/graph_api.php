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

require_lib( 'ezc/Base/src/base.php' );

/**
 * Get Font to use with graphs from configuration value
 * @return string
 */
function graph_get_font() {
	$t_font = 'arial.ttf';

	$t_font_path = get_font_path();
	if( empty( $t_font_path ) ) {
		error_text( 'Unable to read/find font', 'Unable to read/find font' );
	}

	$t_font_file = $t_font_path . $t_font;
	if( file_exists( $t_font_file ) === false || is_readable( $t_font_file ) === false ) {
		error_text( 'Unable to read/find font', 'Unable to read/find font' );
	}

	return $t_font_file;
}

/**
 * Converts an array of php strings into an array of javascript strings without [].
 * @param array $p_strings The array of strings
 * @return string The js code for the array without [], e.g. "a", "b", "c"
 */
function graph_strings_array( array $p_strings ) {
	$t_js_labels = '';

	foreach ( $p_strings as $t_label ) {
		if ( !empty( $t_js_labels ) ) {
			$t_js_labels .= ', ';
		}

		$t_js_labels .= '"' . $t_label . '"';
	}

	return $t_js_labels;
}

/**
 * Converts an array of php numbers into an array of javascript numbers without [].
 * @param  array $p_values The array of values.
 * @return string The js code for the array without [], e.g. 1, 2, 3.
 */
function graph_numeric_array( array $p_values ) {
	$t_js_values = '';

	foreach( $p_values as $t_value ) {
		if ( !empty( $t_js_values ) ) {
			$t_js_values .= ', ';
		}

		$t_js_values .= $t_value;
	}

	return $t_js_values;
}

/**
 * Converts an html color (e.g. #fcbdbd) to rgba.
 * @param string  $p_color The html color
 * @param float   $p_alpha    The value (e.g. 0.2)
 * @return string The rgba with the surrounding single quotes, 'rgba(252, 189, 189, 0.2)'
 */
function graph_color_to_rgba( $p_color, $p_alpha ) {
	$t_rgba = "'rgba(";

	if ( $p_color[0] == '#' ) {
		$t_color = substr( $p_color, 1 );
	} else {
		$t_color = $p_color;
	}

	$t_rgba .= intval( $t_color[0] . $t_color[1], 16 ) . ', ';
	$t_rgba .= intval( $t_color[2] . $t_color[3], 16 ) . ', ';
	$t_rgba .= intval( $t_color[4] . $t_color[5], 16 ) . ', ';
	$t_rgba .= $p_alpha . ")'";

	return $t_rgba;
}

/**
 * Converts an array of colors + an alpha value to a set of rgbas.
 * @param  array  $p_colors Array of html colors (e.g. #fcbdbd).
 * @param  float  $p_alpha  The alpha value.
 * @return string e.g. 'rgba(252, 189, 189, 0.2)', 'rgba(252, 189, 189, 0.2)'
 */
function graph_colors_to_rgbas( array $p_colors, $p_alpha ) {
	$t_rgbas = '';

	foreach( $p_colors as $t_color ) {
		if ( !empty( $t_rgbas ) ) {
			$t_rgbas .= ', ';
		}

		$t_rgbas .= graph_color_to_rgba( $t_color, $p_alpha );
	}

	return $t_rgbas;
}

/**
 * Gets an array of html colors that corresponds to statuses.
 * @return array An array similar to the status_colors config ordered by status enum codes.
 */
function graph_status_colors_to_colors() {
	$t_status_enum = config_get( 'status_enum_string' );
	$t_status_colors = config_get( 'status_colors' );
	$t_statuses = MantisEnum::getValues( $t_status_enum );
	$t_colors = array();

	foreach( $t_statuses as $t_status ) {
		$t_status_name = MantisEnum::getLabel( $t_status_enum, $t_status );
		$t_status_color = $t_status_colors[$t_status_name];
		$t_colors[] = $t_status_color;
	}

	return $t_colors;
}

/**
 * Generate Bar Graph
 *
 * @param array   $p_metrics      Graph Data.
 * @param string  $p_title        Title.
 * @param integer $p_graph_width  Width of graph in pixels.
 * @param integer $p_graph_height Height of graph in pixels.
 * @param string  $p_series_name  The name of the data series.
 * @param string  $p_color        The bar color.
 * @return void
 */
function graph_bar( array $p_metrics, $p_title = '', $p_graph_width = 350, $p_graph_height = 400, $p_series_name, $p_color = '#fcbdbd' ) {
	static $s_id = 0;

	$s_id++;
	$t_labels = array_keys( $p_metrics );
	$t_js_labels = graph_strings_array( $t_labels );

	$t_values = array_values( $p_metrics );
	$t_js_values = graph_numeric_array( $t_values );

	$t_colors = array( $p_color );
	$t_background_colors = graph_colors_to_rgbas( $t_colors, 0.2 );
	$t_border_colors = graph_colors_to_rgbas( $t_colors, 1 );

echo <<<EOT
<canvas id="barchart{$s_id}" width="{$p_graph_width}" height="{$p_graph_height}"></canvas>
<script>
$(document).ready( function() {
var ctx = document.getElementById("barchart{$s_id}");
var myChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [{$t_js_labels}],
        datasets: [{
            label: '{$p_series_name}',
            data: [{$t_js_values}],
            backgroundColor: {$t_background_colors},
            borderColor: {$t_border_colors},
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero:true
                }
            }]
        }
    }
});
});
</script>
EOT;
}

/**
 * Function which displays the charts using the absolute values according to the status (opened/closed/resolved)
 *
 * @param array   $p_metrics      Graph Data.
 * @param string  $p_title        Title.
 * @param integer $p_graph_width  Width of graph in pixels.
 * @param integer $p_graph_height Height of graph in pixels.
 * @return void
 */
function graph_group( array $p_metrics, $p_title = '', $p_graph_width = 350, $p_graph_height = 400 ) {
	# $p_metrics is an array of three arrays
	#   $p_metrics['open'] = array( 'enum' => value, ...)
	#   $p_metrics['resolved']
	#   $p_metrics['closed']

	static $s_id = 0;

	# TODO: Fix graphing of group values

	$s_id++;
	$t_labels = array_keys( $p_metrics['open'] );
	$t_js_labels = graph_strings_array( $t_labels );

	$t_values = array_values( $p_metrics['open'] );
	$t_open_values = graph_numeric_array( $t_values );

	$t_values = array_values( $p_metrics['resolved'] );
	$t_resolved_values = graph_numeric_array( $t_values );

	$t_values = array_values( $p_metrics['closed'] );
	$t_closed_values = graph_numeric_array( $t_values );

	$t_colors = array( '#fcbdbd' );
	$t_background_colors = graph_colors_to_rgbas( $t_colors, 0.2 );
	$t_border_colors = graph_colors_to_rgbas( $t_colors, 1 );

echo <<<EOT
<canvas id="groupbarchart{$s_id}" width="{$p_graph_width}" height="{$p_graph_height}"></canvas>
<script>
$(document).ready( function() {
var ctx = document.getElementById("groupbarchart{$s_id}");
var myChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['open', 'resolved', 'closed'],
        datasets: [
        {
            data: [{$t_open_values}],
            backgroundColor: {$t_background_colors},
            borderColor: {$t_border_colors},
            borderWidth: 1
        },
        {
            data: [{$t_resolved_values}],
            backgroundColor: {$t_background_colors},
            borderColor: {$t_border_colors},
            borderWidth: 1
        },
        {
            data: [{$t_closed_values}],
            backgroundColor: {$t_background_colors},
            borderColor: {$t_border_colors},
            borderWidth: 1
        }
        ]
    },
    options: {
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero:true
                }
            }]
        }
    }
});
});
</script>
EOT;
}

/**
 * Function that displays pie charts
 *
 * @param array         $p_metrics       Graph Data.
 * @param string        $p_title         Title.
 * @param integer       $p_graph_width   Width of graph in pixels.
 * @param integer       $p_graph_height  Height of graph in pixels.
 * @return void
 */
function graph_pie( array $p_metrics, $p_title = '', $p_graph_width = 500, $p_graph_height = 350 ) {
	static $s_id = 0;

	$s_id++;

	$t_labels = array_keys( $p_metrics );
	$t_js_labels = graph_strings_array( $t_labels );

	$t_values = array_values( $p_metrics );
	$t_js_values = graph_numeric_array( $t_values );

	$t_colors = graph_status_colors_to_colors();
	$t_background_colors = graph_colors_to_rgbas( $t_colors, 0.2 );
	$t_border_colors = graph_colors_to_rgbas( $t_colors, 1 );

echo <<<EOT
<canvas id="piechart{$s_id}" width="{$p_graph_width}" height="{$p_graph_height}"></canvas>
<script>
$(document).ready( function() {
var ctx = document.getElementById("piechart{$s_id}");
var myChart = new Chart(ctx, {
    type: 'pie',
    data: {
        labels: [{$t_js_labels}],
        datasets: [{
            label: '# of issues',
            data: [{$t_js_values}],
            backgroundColor: [{$t_background_colors}],
            borderColor: [{$t_border_colors}],
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero:true
                }
            }]
        }
    }
});
});
</script>
EOT;
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
 * @param array  $p_exclude_codes Array of codes to exclude from the enum.
 * @return array
 */
function create_bug_enum_summary( $p_enum_string, $p_enum, array $p_exclude_codes = array() ) {
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

		if ( !in_array( $t_value, $p_exclude_codes ) ) {
			$t_metrics[$t_label] = db_result( $t_result, 0 );
		}
	}

	return $t_metrics;
}

/**
 * Calculate distribution of issues by statuses excluding closed status.
 *
 * @return array An array with keys being status names and values being number of issues with such status.
 */
function create_bug_status_summary() {
	$t_status_enum = config_get( 'status_enum_string' );
	$t_statuses = MantisEnum::getValues( $t_status_enum );
	$t_closed_threshold = config_get( 'bug_closed_status_threshold' );

	$t_closed_statuses = array();
	foreach( $t_statuses as $t_status_code ) {
		if ( $t_status_code >= $t_closed_threshold ) {
			$t_closed_statuses[] = $t_status_code;
		}
	}

	return create_bug_enum_summary( lang_get( 'status_enum_string' ), 'status', $t_closed_statuses );
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
	$t_image = imagecreate( 300, 300 );
	$t_text_color = imagecolorallocate( $t_image, 0, 0, 0 );
	imagestring( $t_image, 5, 0, 0, $p_text, $t_text_color );
	header( 'Content-type: image/png' );
	imagepng( $t_image );
	imagedestroy( $t_image );
	die;
}
