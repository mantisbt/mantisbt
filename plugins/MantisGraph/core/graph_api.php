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

/**
 * Converts an array of php strings into an array of javascript strings without [].
 * @param array $p_strings The array of strings
 * @return string The js code for the array without [], e.g. "a", "b", "c"
 */
function graph_strings_array( array $p_strings ) {
	$t_js_labels = '';

	foreach ( $p_strings as $t_label ) {
		if ( $t_js_labels !== '' ) {
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
		if ( $t_js_values !== '' ) {
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
	$t_rgba = '"rgba(';

	if ( $p_color[0] == '#' ) {
		$t_color = substr( $p_color, 1 );
	} else {
		$t_color = $p_color;
	}

	$t_rgba .= intval( $t_color[0] . $t_color[1], 16 ) . ', ';
	$t_rgba .= intval( $t_color[2] . $t_color[3], 16 ) . ', ';
	$t_rgba .= intval( $t_color[4] . $t_color[5], 16 ) . ', ';
	$t_rgba .= $p_alpha . ')"';

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
 * @param string  $p_series_name  The name of the data series.
 * @param string  $p_color        The bar color.
 * @return void
 */
function graph_bar( array $p_metrics, $p_title = '', $p_series_name, $p_color = '#fcbdbd' ) {
	static $s_id = 0;

	$s_id++;
	$t_labels = array_keys( $p_metrics );
	$t_js_labels = graph_strings_array( $t_labels );

	$t_values = array_values( $p_metrics );
	$t_js_values = graph_numeric_array( $t_values );

?>
	<canvas id="barchart<?php echo $s_id ?>" width="500" height="400"
		data-labels="[<?php echo htmlspecialchars( $t_js_labels, ENT_QUOTES ) ?>]"
		data-values="[<?php echo $t_js_values ?>]" />
<?php
}

/**
 * Function that displays pie charts
 *
 * @param array         $p_metrics       Graph Data.
 * @param string        $p_title         Title.
 * @return void
 */
function graph_pie( array $p_metrics, $p_title = '' ) {
	static $s_id = 0;

	$s_id++;

	$t_labels = array_keys( $p_metrics );
	$t_js_labels = graph_strings_array( $t_labels );

	$t_values = array_values( $p_metrics );
	$t_js_values = graph_numeric_array( $t_values );

	$t_colors = graph_status_colors_to_colors();
	$t_background_colors = graph_colors_to_rgbas( $t_colors, 0.2 );
	$t_border_colors = graph_colors_to_rgbas( $t_colors, 1 );
?>
	<canvas id="piechart<?php echo $s_id ?>" width="500" height="400"
		data-labels="[<?php echo htmlspecialchars( $t_js_labels, ENT_QUOTES ) ?>]"
		data-values="[<?php echo $t_js_values ?>]"
		data-background-colors="[<?php echo htmlspecialchars( $t_background_colors, ENT_QUOTES ) ?>]"
		data-border-colors="[<?php echo htmlspecialchars( $t_border_colors, ENT_QUOTES ) ?>]" />
<?php
}

/**
 * Cumulative line graph
 *
 * @param array   $p_metrics      Graph Data.
 * @return void
 */
function graph_cumulative_bydate( array $p_metrics ) {
	static $s_id = 0;

	$s_id++;

	$t_labels = array_keys( $p_metrics[0] );
	$t_formatted_labels = array_map( function($label) { return date( 'Ymd', $label ); }, $t_labels );
	$t_js_labels = graph_strings_array( $t_formatted_labels );

	$t_values = array_values( $p_metrics[0] );
	$t_opened_values = graph_numeric_array( $t_values );

	$t_values = array_values( $p_metrics[1] );
	$t_resolved_values = graph_numeric_array( $t_values );

	$t_values = array_values( $p_metrics[2] );
	$t_still_open_values = graph_numeric_array( $t_values );

	$t_colors = graph_status_colors_to_colors();
	$t_background_colors = graph_colors_to_rgbas( $t_colors, 0.2 );
	$t_border_colors = graph_colors_to_rgbas( $t_colors, 1 );

	$t_legend_opened = plugin_lang_get( 'legend_reported' );
	$t_legend_resolved = plugin_lang_get( 'legend_resolved' );
	$t_legend_still_open = plugin_lang_get( 'legend_still_open' );

?>
	<canvas id="linebydate<?php echo $s_id ?>" width="500" height="400"
			data-labels="[<?php echo htmlspecialchars( $t_js_labels, ENT_QUOTES ) ?>]"
			data-opened-label="<?php echo $t_legend_opened ?>"
			data-opened-values="[<?php echo $t_opened_values ?>]"
			data-resolved-label="<?php echo $t_legend_resolved ?>"
			data-resolved-values="[<?php echo $t_resolved_values ?>]"
			data-still-open-label="<?php echo $t_legend_still_open ?>"
			data-still-open-values="[<?php echo $t_still_open_values ?>]" />
<?php

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
 * Create summary for issues resolved by a developer
 * @return array with key being username and value being # of issues fixed.
 */
function create_developer_resolved_summary() {
	$t_project_id = helper_get_current_project();
	$t_user_id = auth_get_current_user_id();
	$t_specific_where = helper_project_specific_where( $t_project_id, $t_user_id );
	$t_resolved_status_threshold = config_get( 'bug_resolved_status_threshold' );

	$t_query = 'SELECT handler_id, count(*) as count FROM {bug} WHERE ' . $t_specific_where . ' AND handler_id <> ' .
		db_param() . ' AND status >= ' . db_param() . ' AND resolution = ' . db_param() .
		' GROUP BY handler_id ORDER BY count DESC';
	$t_result = db_query( $t_query, array( NO_USER, $t_resolved_status_threshold, FIXED ), 20 );

	$t_handler_array = array();
	$t_handler_ids = array();
	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_handler_array[$t_row['handler_id']] = $t_row['count'];
		$t_handler_ids[] = $t_row['handler_id'];
	}

	if( count( $t_handler_array ) == 0 ) {
		return array();
	}

	user_cache_array_rows( $t_handler_ids );

	foreach( $t_handler_array as $t_handler_id => $t_count ) {
		$t_metrics[user_get_name( $t_handler_id )] = $t_count;
	}

	arsort( $t_metrics );

	return $t_metrics;
}

/**
 * Create summary for issues opened by a developer
 * @return array with key being username and value being # of issues fixed.
 */
function create_developer_open_summary() {
	$t_project_id = helper_get_current_project();
	$t_user_id = auth_get_current_user_id();
	$t_specific_where = helper_project_specific_where( $t_project_id, $t_user_id );
	$t_resolved_status_threshold = config_get( 'bug_resolved_status_threshold' );

	$t_query = 'SELECT handler_id, count(*) as count FROM {bug} WHERE ' . $t_specific_where . ' AND handler_id <> ' .
		db_param() . ' AND status < ' . db_param() . ' GROUP BY handler_id ORDER BY count DESC';
	$t_result = db_query( $t_query, array( NO_USER, $t_resolved_status_threshold ) );

	$t_handler_array = array();
	$t_handler_ids = array();
	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_handler_array[$t_row['handler_id']] = $t_row['count'];
		$t_handler_ids[] = $t_row['handler_id'];
	}

	if( count( $t_handler_array ) == 0 ) {
		return array();
	}

	user_cache_array_rows( $t_handler_ids );

	foreach( $t_handler_array as $t_handler_id => $t_count ) {
		$t_metrics[user_get_name( $t_handler_id )] = $t_count;
	}

	arsort( $t_metrics );

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

	$t_query = 'SELECT reporter_id, count(*) as count FROM {bug} WHERE ' . $t_specific_where . ' AND resolution = ' .
		db_param() . ' GROUP BY reporter_id ORDER BY count DESC';
	$t_result = db_query( $t_query, array( FIXED ), 20 );

	$t_reporter_arr = array();
	$t_reporters = array();
	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_reporter_arr[$t_row['reporter_id']] = $t_row['count'];
		$t_reporters[] = $t_row['reporter_id'];
	}

	if( count( $t_reporter_arr ) == 0 ) {
		return array();
	}

	user_cache_array_rows( $t_reporters );

	foreach( $t_reporter_arr as $t_reporter => $t_count ) {
		$t_metrics[user_get_name( $t_reporter )] = $t_count;
	}

	arsort( $t_metrics );

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
								AND {bug_history}.old_value < ' . db_param() . '
								AND {bug_history}.field_name = \'status\' )
						OR {bug_history}.id is NULL )
			ORDER BY {bug}.id, date_modified ASC';
	$t_result = db_query( $t_query, array( $t_res_val, (string)$t_res_val, (string)$t_res_val ) );
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
	$t_last_still_open = 0;
	foreach( $t_calc_metrics as $i => $t_values ) {
		$t_date = $i * SECONDS_PER_DAY;
		$t_metrics[0][$t_date] = $t_last_opened = $t_last_opened + $t_calc_metrics[$i][0];
		$t_metrics[1][$t_date] = $t_last_resolved = $t_last_resolved + $t_calc_metrics[$i][1];
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

