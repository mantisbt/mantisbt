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
 * @param array $p_metrics Data set to return colors for (with status labels as keys)
 * @return array An array similar to the status_colors config ordered by status enum codes.
 */
function graph_status_colors_to_colors( $p_metrics = array() ) {
	$t_colors = array();
	# The metrics contain localized status, so we need an extra lookup
    # to retrieve the id before we can get the color code
	$t_status_lookup =  MantisEnum::getAssocArrayIndexedByLabels( lang_get( 'status_enum_string' ) );
	foreach( array_keys( $p_metrics ) as $t_label ) {
		$t_colors[] = get_status_color( $t_status_lookup[$t_label] , null, null, '#e5e5e5' );
	}

	return $t_colors;
}

/**
 * Generate Bar Graph
 *
 * @param array   $p_metrics      Graph Data.
 * @param integer $p_wfactor      Width factor for graph chart. Eg: 2 to make it double wide
 * @param bool    $p_horiz        True for horizontal bars, defaults to false (vertical)
 * @return void
 */
function graph_bar( array $p_metrics, $p_wfactor = 1, $p_horiz = false ) {
	static $s_id = 0;

	$s_id++;
	$t_id = $p_horiz ? 'horizbarchart' : 'barchart';
	$t_json_labels = json_encode( array_keys( $p_metrics ) );
	$t_json_values = json_encode( array_values( $p_metrics ) );

	$t_width = 500 * $p_wfactor;
	$t_height = 400;

?>
	<canvas id="<?php echo $t_id, $s_id ?>"
		width="<?php echo $t_width ?>" height="<?php echo $t_height ?>"
		data-labels="<?php echo htmlspecialchars( $t_json_labels, ENT_QUOTES ) ?>"
		data-values="<?php echo htmlspecialchars( $t_json_values, ENT_QUOTES ) ?>">
	</canvas>
<?php
}

/**
 * Function that displays pie charts
 *
 * @param array $p_metrics       Graph Data.
 * @param bool $p_mantis_colors  True to use colors defined in Mantis config
 *                               {@see $g_status_colors}. By default use
 *                               standard color scheme
 *
 * @return void
 */
function graph_pie( array $p_metrics, $p_mantis_colors = false ) {
	static $s_id = 0;

	$s_id++;

	$t_json_labels = json_encode( array_keys( $p_metrics ) );
	$t_json_values = json_encode( array_values( $p_metrics ) );

?>
	<canvas id="piechart<?php echo $s_id ?>"
		width="500" height="400"
		data-labels="<?php echo htmlspecialchars( $t_json_labels, ENT_QUOTES ) ?>"
		data-values="<?php echo htmlspecialchars( $t_json_values, ENT_QUOTES ) ?>"
<?php
	if( $p_mantis_colors ) {
		$t_colors = graph_colors_to_rgbas( graph_status_colors_to_colors( $p_metrics ), 1.0 );
?>
		data-colors="[<?php echo htmlspecialchars( $t_colors, ENT_QUOTES ) ?>]"
<?php } ?>
	>
	</canvas>
<?php
}

/**
 * Cumulative line graph
 *
 * @param array   $p_metrics      Graph Data.
 * @param integer $p_wfactor      Width factor for graph chart. Eg: 2 to make it double wide
 * @return void
 */
function graph_cumulative_bydate( array $p_metrics, $p_wfactor = 1 ) {
	static $s_id = 0;

	$s_id++;

	$t_labels = array_keys( $p_metrics[0] );
	$t_formatted_labels = array_map( function($label) { return date( 'Ymd', $label ); }, $t_labels );
	$t_json_labels = json_encode( $t_formatted_labels );

	$t_opened_values = json_encode( array_values( $p_metrics[0] ) );
	$t_resolved_values = json_encode( array_values( $p_metrics[1] ) );
	$t_still_open_values = json_encode( array_values( $p_metrics[2] ) );

	$t_legend_opened = plugin_lang_get( 'legend_reported' );
	$t_legend_resolved = plugin_lang_get( 'legend_resolved' );
	$t_legend_still_open = plugin_lang_get( 'legend_still_open' );

	$t_width = 500 * $p_wfactor;
	$t_height = 400;
?>
	<canvas id="linebydate<?php echo $s_id ?>"
		width="<?php echo $t_width ?>" height="<?php echo $t_height ?>"
		data-labels="<?php echo htmlspecialchars( $t_json_labels, ENT_QUOTES ) ?>"
		data-opened-label="<?php echo $t_legend_opened ?>"
		data-opened-values="<?php echo htmlspecialchars( $t_opened_values, ENT_QUOTES ) ?>"
		data-resolved-label="<?php echo $t_legend_resolved ?>"
		data-resolved-values="<?php echo htmlspecialchars( $t_resolved_values, ENT_QUOTES ) ?>"
		data-still-open-label="<?php echo $t_legend_still_open ?>"
		data-still-open-values="<?php echo htmlspecialchars( $t_still_open_values, ENT_QUOTES ) ?>">
	</canvas>
<?php

}

/**
 * Summarize metrics by a single ENUM field in the bug table.
 *
 * @param string $p_enum_string Enumeration string.
 * @param string $p_enum        Enumeration field.
 * @param array  $p_exclude_codes Array of codes to exclude from the enum.
 * @param array  $p_filter      Filter array.
 * @return array
 */
function create_bug_enum_summary( $p_enum_string, $p_enum, array $p_exclude_codes = array(), array $p_filter = null ) {
	$t_project_id = helper_get_current_project();
	$t_user_id = auth_get_current_user_id();
	$t_specific_where = helper_project_specific_where( $t_project_id, $t_user_id );

	$t_metrics = array();
	$t_assoc_array = MantisEnum::getAssocArrayIndexedByValues( $p_enum_string );

	if( !db_field_exists( $p_enum, db_get_table( 'bug' ) ) ) {
		trigger_error( ERROR_DB_FIELD_NOT_FOUND, ERROR );
	}

	$t_query = new DBQuery();
	$t_sql = 'SELECT ' . $p_enum . ' AS enum, COUNT(*) AS bugcount FROM {bug}'
			. ' WHERE ' . $p_enum . ' IN :enum_values'
			. ' AND ' . $t_specific_where
			. ( $p_filter ? ' AND {bug}.id IN :filter' : '' )
			. ' GROUP BY ' . $p_enum . ' ORDER BY ' . $p_enum;
	$t_query->bind( 'enum_values', array_keys( $t_assoc_array ) );
	if( $p_filter ) {
		$t_query->bind( 'filter', filter_cache_subquery( $p_filter ) );
	}
	$t_query->sql( $t_sql );

	while( $t_row = $t_query->fetch() ) {
		$t_enum_key = (int)$t_row['enum'];
		$t_bugcount = (int)$t_row['bugcount'];
		$t_label = $t_assoc_array[$t_enum_key];
		$t_metrics[$t_label] = $t_bugcount;
	}

	return $t_metrics;
}

/**
 * Calculate distribution of issues by statuses excluding closed status.
 * @param array $p_filter Filter array.
 * @return array An array with keys being status names and values being number of issues with such status.
 */
function create_bug_status_summary( array $p_filter = null ) {
	# When the provided filter is temporary, it's a filter that was explicitly applied to summary pages.
	# Otherwise, it's a filter created by default by summary api.
	# In that case, or if no filter has been provided, keep legacy behaviour where we exclude
	# closed status in this graph
	if( null === $p_filter || !filter_is_temporary( $p_filter ) ) {
		$t_status_enum = config_get( 'status_enum_string' );
		$t_statuses = MantisEnum::getValues( $t_status_enum );
		$t_closed_threshold = config_get( 'bug_closed_status_threshold' );

		$t_closed_statuses = array();
		foreach( $t_statuses as $t_status_code ) {
			if ( $t_status_code >= $t_closed_threshold ) {
				$t_closed_statuses[] = $t_status_code;
			}
		}
	} else {
	# when explicitly using a filter, do not exclude any status, to match the expected filter results
		$t_closed_statuses = array();
	}

	return create_bug_enum_summary( lang_get( 'status_enum_string' ), 'status', $t_closed_statuses, $p_filter );
}

/**
 * Create summary for issues resolved by a developer
 * @param array $p_filter Filter array.
 * @return array with key being username and value being # of issues fixed.
 */
function create_developer_resolved_summary( array $p_filter = null ) {
	$t_project_id = helper_get_current_project();
	$t_user_id = auth_get_current_user_id();
	$t_specific_where = helper_project_specific_where( $t_project_id, $t_user_id );
	$t_resolved_status_threshold = config_get( 'bug_resolved_status_threshold' );

	$t_query = new DBQuery();
	$t_sql = 'SELECT handler_id, count(*) as count FROM {bug} WHERE ' . $t_specific_where
		. ' AND handler_id <> :nouser AND status >= :status_resolved AND resolution = :resolution_fixed';
	if( !empty( $p_filter ) ) {
		$t_subquery = filter_cache_subquery( $p_filter );
		$t_sql .= ' AND {bug}.id IN :filter';
		$t_query->bind( 'filter', $t_subquery );
	}
	$t_sql .= ' GROUP BY handler_id ORDER BY count DESC';
	$t_query->sql( $t_sql );
	$t_query->bind( array(
		'nouser' => NO_USER,
		'status_resolved' => (int)$t_resolved_status_threshold,
		'resolution_fixed' => FIXED
		) );
	$t_query->set_limit( 20 );

	$t_handler_array = array();
	$t_handler_ids = array();
	while( $t_row = $t_query->fetch() ) {
		$t_handler_array[$t_row['handler_id']] = (int)$t_row['count'];
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
 * @param array $p_filter Filter array.
 * @return array with key being username and value being # of issues fixed.
 */
function create_developer_open_summary( array $p_filter = null ) {
	$t_project_id = helper_get_current_project();
	$t_user_id = auth_get_current_user_id();
	$t_specific_where = helper_project_specific_where( $t_project_id, $t_user_id );
	$t_resolved_status_threshold = config_get( 'bug_resolved_status_threshold' );

	$t_query = new DBQuery();
	$t_sql = 'SELECT handler_id, count(*) as count FROM {bug} WHERE ' . $t_specific_where
		. ' AND handler_id <> :nouser AND status < :status_resolved';
	if( !empty( $p_filter ) ) {
		$t_subquery = filter_cache_subquery( $p_filter );
		$t_sql .= ' AND {bug}.id IN :filter';
		$t_query->bind( 'filter', $t_subquery );
	}
	$t_sql .= ' GROUP BY handler_id ORDER BY count DESC';
	$t_query->sql( $t_sql );
	$t_query->bind( array(
		'nouser' => NO_USER,
		'status_resolved' => (int)$t_resolved_status_threshold
		) );

	$t_handler_array = array();
	$t_handler_ids = array();
	while( $t_row = $t_query->fetch() ) {
		$t_handler_array[$t_row['handler_id']] = (int)$t_row['count'];
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
 * @param array $p_filter    Filter array.
 * @param integer $p_limit   Number of records to return.
 * @return array
 */
function create_reporter_summary( array $p_filter = null ) {
	$t_project_id = helper_get_current_project();
	$t_user_id = auth_get_current_user_id();
	$t_specific_where = helper_project_specific_where( $t_project_id, $t_user_id );

	$t_query = new DBQuery();
	$t_sql = 'SELECT reporter_id, count(*) as count FROM {bug} WHERE ' . $t_specific_where
		. ' AND resolution = :resolution_fixed';
	if( !empty( $p_filter ) ) {
		$t_subquery = filter_cache_subquery( $p_filter );
		$t_sql .= ' AND {bug}.id IN :filter';
		$t_query->bind( 'filter', $t_subquery );
	}
	$t_sql .= ' GROUP BY reporter_id ORDER BY count DESC';
	$t_query->sql( $t_sql );
	$t_query->bind( 'resolution_fixed', FIXED );
	$t_query->set_limit( 25 );

	$t_reporter_arr = array();
	$t_reporters = array();
	while( $t_row = $t_query->fetch() ) {
		$t_reporter_arr[$t_row['reporter_id']] = (int)$t_row['count'];
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
 * @param array $p_filter Filter array.
 * @return array
 */
function create_category_summary( array $p_filter = null ) {
	$t_project_id = helper_get_current_project();
	$t_user_id = auth_get_current_user_id();
	$t_specific_where = helper_project_specific_where( $t_project_id, $t_user_id );

	$t_query_cat = new DBQuery();
	$t_sql = 'SELECT id, name FROM {category} WHERE ' . $t_specific_where
			. ' OR project_id = :all_projects';
	$t_query_cat->sql( $t_sql );
	$t_query_cat->bind( 'all_projects', ALL_PROJECTS );

	$t_metrics = array();
	$t_query_cnt = new DBQuery();
	$t_query_cnt->sql( 'SELECT COUNT(*) FROM {bug} WHERE category_id = :cat_id AND ' . $t_specific_where );
	if( !empty( $p_filter ) ) {
		$t_subquery = filter_cache_subquery( $p_filter );
		$t_query_cnt->append_sql( ' AND {bug}.id IN :filter' );
		$t_query_cnt->bind( 'filter', $t_subquery );
	}

	while( $t_row = $t_query_cat->fetch() ) {
		$t_cat_name = $t_row['name'];
		$t_cat_id = $t_row['id'];
		$t_query_cnt->bind( 'cat_id', (int)$t_cat_id );
		$t_query_cnt->execute();
		$t_bugcount = (int)$t_query_cnt->value();

		if( isset($t_metrics[$t_cat_name]) ) {
			$t_metrics[$t_cat_name] = $t_metrics[$t_cat_name] + $t_bugcount;
		} else {
			if( $t_bugcount > 0 ) {
			    $t_metrics[$t_cat_name] = $t_bugcount;
			}
		}
	}

	return $t_metrics;
}

/**
 * Create cumulative graph by date
 * @param array $p_filter Filter array.
 * @return array | null
 */
function create_cumulative_bydate( array $p_filter = null ) {
	$t_clo_val = config_get( 'bug_closed_status_threshold' );
	$t_res_val = config_get( 'bug_resolved_status_threshold' );

	$t_project_id = helper_get_current_project();
	$t_user_id = auth_get_current_user_id();
	$t_specific_where = helper_project_specific_where( $t_project_id, $t_user_id );

	# For this metric to make sense, we need to remove the filter properties related to status
	if( $p_filter ) {
		$p_filter[FILTER_PROPERTY_STATUS] = array( META_FILTER_ANY );
		$p_filter[FILTER_PROPERTY_HIDE_STATUS] = array( META_FILTER_NONE );
	}

	# Get all the submitted dates
	$t_query = new DBQuery();
	$t_sql = 'SELECT date_submitted FROM {bug} WHERE ' . $t_specific_where;
	if( $p_filter ) {
		$t_subquery = filter_cache_subquery( $p_filter );
		$t_sql .= ' AND {bug}.id IN :filter';
		$t_query->bind( 'filter', $t_subquery );
	}
	$t_sql .= ' ORDER BY date_submitted';
	$t_query->sql( $t_sql );

	$t_calc_metrics = array();
	while( $t_row = $t_query->fetch() ) {
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
	$t_query = new DBQuery();
	$t_sql = 'SELECT {bug}.id, last_updated, date_modified, new_value, old_value'
		. ' FROM {bug} LEFT JOIN {bug_history} ON {bug}.id = {bug_history}.bug_id'
		. ' WHERE ' . $t_specific_where
		. ' AND {bug}.status >= :int_resolved'
		. ' AND ( ( {bug_history}.new_value >= :str_resolved AND {bug_history}.old_value < :str_resolved AND {bug_history}.field_name = :field_name )'
		. ' OR {bug_history}.id is NULL )';
	if( $p_filter ) {
		$t_subquery = filter_cache_subquery( $p_filter );
		$t_sql .= ' AND {bug}.id IN :filter';
		$t_query->bind( 'filter', $t_subquery );
	}
	$t_sql .= ' ORDER BY {bug}.id, date_modified ASC';
	$t_query->sql( $t_sql );
	$t_query->bind( array(
		'int_resolved' => (int)$t_res_val,
		'str_resolved' => (string)$t_res_val,
		'field_name' => 'status'
		) );

	$t_last_id = 0;
	$t_last_date = 0;

	while( $t_row = $t_query->fetch() ) {
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

	if ( $t_last_id == 0 ) {
		return null;
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

