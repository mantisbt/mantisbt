<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: graph_api.php,v 1.31 2005-07-22 15:33:59 thraxisp Exp $
	# --------------------------------------------------------

	if ( ON == config_get( 'use_jpgraph' ) ) {
		$t_jpgraph_path = config_get( 'jpgraph_path' );

		require_once( $t_jpgraph_path.'jpgraph.php' );
		require_once( $t_jpgraph_path.'jpgraph_line.php' );
		require_once( $t_jpgraph_path.'jpgraph_bar.php' );
		require_once( $t_jpgraph_path.'jpgraph_pie.php' );
		require_once( $t_jpgraph_path.'jpgraph_pie3d.php' );
		require_once( $t_jpgraph_path.'jpgraph_canvas.php' );

	}

	function graph_get_font() {
		$t_font_map = array(
			'arial' => FF_ARIAL,
			'verdana' => FF_VERDANA,
			'courier' => FF_COURIER,
			'book' => FF_BOOK,
			'comic' => FF_COMIC,
			'times' => FF_TIMES,
			'georgia' => FF_GEORGIA,
			'trebuche' => FF_TREBUCHE,
			'vera' => FF_VERA,
			'veramono' => FF_VERAMONO,
			'veraserif' => FF_VERASERIF );

		$t_font = config_get( 'graph_font', '');
		if ( isset( $t_font_map[$t_font] ) ) {
			return $t_font_map[$t_font];
		} else {
			return FF_FONT1;
		}
	}

	### Graph API ###
	# --------------------
	# graphing routines
	# --------------------
	function graph_bar( $p_metrics, $p_title='', $p_graph_width = 350, $p_graph_height = 400 ){

		$t_graph_font = graph_get_font();

		error_check( is_array( $p_metrics ) ? array_sum( $p_metrics ) : 0, $p_title );

		$graph = new Graph( $p_graph_width, $p_graph_height );
		$graph->img->SetMargin(40,40,40,170);
		$graph->img->SetAntiAliasing();
		$graph->SetScale('textlin');
		$graph->SetMarginColor('white');
		$graph->SetFrame(false);
		$graph->title->Set($p_title);
		$graph->title->SetFont( $t_graph_font, FS_BOLD );
		$graph->xaxis->SetTickLabels( array_keys( $p_metrics ) );
		if ( FF_FONT2 <= $t_graph_font ) {
			$graph->xaxis->SetLabelAngle(60);
		} else {
			$graph->xaxis->SetLabelAngle(90);	# can't rotate non truetype fonts
		}
		$graph->xaxis->SetFont( $t_graph_font );

		$graph->legend->SetFont( $t_graph_font );

		$graph->yaxis->scale->ticks->SetDirection(-1);
		$graph->yaxis->SetFont( $t_graph_font );

		$p1 = new BarPlot( array_values( $p_metrics ) );
		$p1->SetFillColor('yellow');
		$p1->SetWidth(0.8);
		$graph->Add($p1);
		if ( ON == config_get( 'show_queries_count' ) ) {
			$graph->subtitle->Set( db_count_queries() . ' queries (' . db_count_unique_queries() . ' unique) (' . db_time_queries() . 'sec)' );
			$graph->subtitle->SetFont( $t_graph_font, FS_NORMAL, 8 );
		}

		$graph->Stroke();

	}

	# Function which displays the charts using the absolute values according to the status (opened/closed/resolved)
	function graph_group( $p_metrics, $p_title='', $p_graph_width = 350, $p_graph_height = 400, $p_baseline = 100 ){
		# $p_metrics is an array of three arrays
		#   $p_metrics['open'] = array( 'enum' => value, ...)
		#   $p_metrics['resolved']
		#   $p_metrics['closed']

		$t_graph_font = graph_get_font();

		# count up array portions that are set
		$t_count = 0;
		foreach ( array( 'open', 'resolved', 'closed' ) as $t_label ) {
			if ( is_array( $p_metrics[$t_label] ) ) {
				$t_count += array_sum( $p_metrics[$t_label] );
			}
		}

		error_check( $t_count, $p_title );

		# calculate totals
		$total = graph_total_metrics( $p_metrics );

		#defines margin according to height
		$graph = new Graph( $p_graph_width, $p_graph_height );
		$graph->img->SetMargin( 45, 35, 35, $p_baseline );
		$graph->img->SetAntiAliasing();
		$graph->SetScale('textlin');
		$graph->SetMarginColor('white');
		$graph->SetFrame(false);
		$graph->title->SetFont( $t_graph_font, FS_BOLD );
		$graph->title->Set($p_title);
		$graph->xaxis->SetTickLabels( array_keys( $p_metrics['open'] ) );
		if ( FF_FONT2 <= $t_graph_font ) {
			$graph->xaxis->SetLabelAngle(60);
		} else {
			$graph->xaxis->SetLabelAngle(90);	# can't rotate non truetype fonts
		}
		$graph->xaxis->SetFont( $t_graph_font );
		$graph->legend->Pos(0.05, 0.08);
		$graph->legend->SetFont( $t_graph_font );

		$graph->yaxis->scale->ticks->SetDirection(-1);
		$graph->yaxis->SetFont( $t_graph_font );
		$graph->yscale->SetGrace(10);

		#adds on the same graph
		$tot = new BarPlot( array_values( $total ) );
		$tot->SetFillColor('lightblue');
		$tot->SetWidth(0.7);
		$tot->SetLegend( lang_get( 'legend_total' ) );
		$graph->Add($tot);

		$p1 = new BarPlot( array_values( $p_metrics['open'] ) );
		$p1->SetFillColor('yellow');
		$p1->SetWidth(1);
		$p1->SetLegend( lang_get( 'legend_opened' ) );

		$p2 = new BarPlot( array_values( $p_metrics['closed'] ) );
		$p2->SetFillColor('blue');
		$p2->SetWidth(1);
		$p2->SetLegend( lang_get( 'legend_closed' ) );

		$p3 = new BarPlot( array_values( $p_metrics['resolved'] ) );
		$p3->SetFillColor('red');
		$p3->SetWidth(1);
		$p3->SetLegend( lang_get( 'legend_resolved' ) );

		$gbplot = new GroupBarPlot(array($p1,$p3,$p2));
		$graph->Add($gbplot);

		if ( ON == config_get( 'show_queries_count' ) ) {
			$graph->subtitle->Set( db_count_queries() . ' queries (' . db_count_unique_queries() . ' unique) (' . db_time_queries() . 'sec)' );
			$graph->subtitle->SetFont( $t_graph_font, FS_NORMAL, 8 );
		}

		$graph->Stroke();

	}

	# --------------------
	# Function that displays charts in % according to the status
	# @@@ this function is not used...
	function graph_group_pct( $p_title='', $p_graph_width = 350, $p_graph_height = 400 ){
		global $enum_name, $enum_name_count;
		global $open_bug_count, $closed_bug_count, $resolved_bug_count;

		error_check( $open_bug_count + $closed_bug_count + $resolved_bug_count, $p_title );

		$graph = new Graph(250,400);
		$graph->img->SetMargin(35,35,35,150);
		$graph->img->SetAntiAliasing();
		$graph->SetScale('textlin');
		$graph->SetMarginColor('white');
		$graph->SetFrame(false);
		$graph->title->Set($p_title);
		$graph->xaxis->SetTickLabels($enum_name);
		$graph->xaxis->SetLabelAngle(90);

		$graph->yaxis->scale->ticks->SetDirection(-1);

		$p1 = new BarPlot($open_bug_count);
		$p1->SetFillColor('yellow');
		$p1->SetWidth(0.8);
		$p1->SetLegend( lang_get( 'legend_opened' ) );

		$p2 = new BarPlot($closed_bug_count);
		$p2->SetFillColor('blue');
		$p2->SetWidth(0.8);
		$p2->SetLegend( lang_get( 'legend_closed' ) );

		$p3 = new BarPlot($resolved_bug_count);
		$p3->SetFillColor('red');
		$p3->SetWidth(0.8);
		$p3->SetLegend( lang_get( 'legend_resolved' ) );

        $gbplot = new GroupBarPlot(array($p1,$p2,$p3));

        $graph->Add($gbplot);
		if ( ON == config_get( 'show_queries_count' ) ) {
			$graph->subtitle->Set( db_count_queries() . ' queries (' . db_count_unique_queries() . ' unique)' );
		}
		$graph->Stroke();
	}

	# --------------------
	# Function that displays pie charts
	function graph_pie( $p_metrics, $p_title='',
			$p_graph_width = 500, $p_graph_height = 350, $p_center = 0.4, $p_poshorizontal = 0.10, $p_posvertical = 0.09 ){

		$t_graph_font = graph_get_font();

		error_check( is_array( $p_metrics ) ? array_sum( $p_metrics ) : 0, $p_title );

		$graph = new PieGraph( $p_graph_width, $p_graph_height);
		$graph->img->SetMargin(40,40,40,100);
		$graph->title->Set($p_title);
		$graph->title->SetFont( $t_graph_font, FS_BOLD );

		$graph->SetMarginColor('white');
		$graph->SetFrame(false);

		$graph->legend->Pos($p_poshorizontal, $p_posvertical);
		$graph->legend->SetFont( $t_graph_font );

		$p1 = new PiePlot3d( array_values( $p_metrics ) );
		$p1->SetTheme('earth');
		#$p1->SetTheme("sand");
		$p1->SetCenter($p_center);
		$p1->SetAngle(60);
		$p1->SetLegends( array_keys( $p_metrics ) );

		# Label format
		$p1->value->SetFormat('%2.0f');
		$p1->value->Show();
		$p1->value->SetFont( $t_graph_font );

		$graph->Add($p1);
		if ( ON == config_get( 'show_queries_count' ) ) {
			$graph->subtitle->Set( db_count_queries() . ' queries (' . db_count_unique_queries() . ' unique) (' . db_time_queries() . 'sec)' );
			$graph->subtitle->SetFont( $t_graph_font, FS_NORMAL, 8 );
		}
		$graph->Stroke();
	}

	# --------------------
	function graph_cumulative_bydate( $p_metrics, $p_graph_width = 300, $p_graph_height = 380 ){

		$t_graph_font = graph_get_font();
		error_check( is_array( $p_metrics ) ? count($p_metrics) : 0, lang_get( 'cumulative' ) . ' ' . lang_get( 'by_date' ) );

		foreach ($p_metrics as $i=>$vals) {
			if ( $i > 0 ) {
				$plot_date[] = $i;
				$reported_plot[] = $p_metrics[$i][0];
				$resolved_plot[] = $p_metrics[$i][1];
				$still_open_plot[] = $p_metrics[$i][2];
			}
		}

		$graph = new Graph( $p_graph_width, $p_graph_height );
		$graph->img->SetMargin(40,40,40,170);
		$graph->img->SetAntiAliasing();
		$graph->SetScale('linlin');
		$graph->SetMarginColor('white');
		$graph->SetFrame(false);
		$graph->title->Set( lang_get( 'cumulative' ) . ' ' . lang_get( 'by_date' ) );
		$graph->title->SetFont( $t_graph_font, FS_BOLD );

		$graph->legend->Pos(0.05,0.9,'right','bottom');
		$graph->legend->SetShadow(false);
		$graph->legend->SetFillColor('white');
		$graph->legend->SetLayout(LEGEND_HOR);
		$graph->legend->SetFont( $t_graph_font );

				$graph->yaxis->scale->ticks->SetDirection(-1);
		$graph->yaxis->SetFont( $t_graph_font );

		if ( FF_FONT2 <= $t_graph_font ) {
			$graph->xaxis->SetLabelAngle(60);
		} else {
			$graph->xaxis->SetLabelAngle(90);	# can't rotate non truetype fonts
		}
		$graph->xaxis->SetLabelFormatCallback('graph_date_format');
		$graph->xaxis->SetFont( $t_graph_font );

		$p1 = new LinePlot($reported_plot, $plot_date);
		$p1->SetColor('blue');
		$p1->SetCenter();
		$p1->SetLegend( lang_get( 'legend_reported' ) );
		$graph->Add($p1);

		$p3 = new LinePlot($still_open_plot, $plot_date);
		$p3->SetColor('red');
		$p3->SetCenter();
		$p3->SetLegend( lang_get( 'legend_still_open' ) );
		$graph->Add($p3);

		$p2 = new LinePlot($resolved_plot, $plot_date);
		$p2->SetColor('black');
		$p2->SetCenter();
		$p2->SetLegend( lang_get( 'legend_resolved' ) );
		$graph->Add($p2);

		if ( ON == config_get( 'show_queries_count' ) ) {
			$graph->subtitle->Set( db_count_queries() . ' queries (' . db_count_unique_queries() . ' unique) (' . db_time_queries() . 'sec)' );
			$graph->subtitle->SetFont( $t_graph_font, FS_NORMAL, 8 );
		}
		$graph->Stroke();
	}

	# --------------------
	# utilities
	# --------------------
	function graph_total_metrics( $p_metrics ){
		foreach ( $p_metrics['open'] as $t_enum => $t_value ) {
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
		$t_bug_table = config_get( 'mantis_bug_table' );
		$t_user_id = auth_get_current_user_id();
		$specific_where = " AND " . helper_project_specific_where( $t_project_id, $t_user_id );

		$t_arr = explode_enum_string( $p_enum_string );
		$enum_count = count( $t_arr );
		for ($i=0;$i<$enum_count;$i++) {
			$t_s = explode_enum_arr( $t_arr[$i] );
			$c_s[0] = addslashes($t_s[0]);
			$t_key = get_enum_to_string( $p_enum_string, $t_s[0] );

			$query = "SELECT COUNT(*)
					FROM $t_bug_table
					WHERE $p_enum='$c_s[0]' $specific_where";
			$result = db_query( $query );
			$t_metrics[$t_key] = db_result( $result, 0 );
		} # end for
		return $t_metrics;
	}

	# Function which gives the absolute values according to the status (opened/closed/resolved)
	function enum_bug_group( $p_enum_string, $p_enum ) {
		$t_bug_table = config_get( 'mantis_bug_table' );

		$t_project_id = helper_get_current_project();
		$t_bug_table = config_get( 'mantis_bug_table' );
		$t_user_id = auth_get_current_user_id();
		$t_res_val = config_get( 'bug_resolved_status_threshold' );
		$t_clo_val = CLOSED;
		$specific_where = " AND " . helper_project_specific_where( $t_project_id, $t_user_id );

		$t_arr = explode_enum_string( $p_enum_string );
		$enum_count = count( $t_arr );
		for ( $i=0; $i < $enum_count; $i++) {
			$t_s = explode( ':', $t_arr[$i] );
			$t_key = get_enum_to_string( $p_enum_string, $t_s[0] );

			# Calculates the number of bugs opened and puts the results in a table
			$query = "SELECT COUNT(*)
					FROM $t_bug_table
					WHERE $p_enum='$t_s[0]' AND
						status<'$t_res_val' $specific_where";
			$result2 = db_query( $query );
			$t_metrics['open'][$t_key] = db_result( $result2, 0, 0);

			# Calculates the number of bugs closed and puts the results in a table
			$query = "SELECT COUNT(*)
					FROM $t_bug_table
					WHERE $p_enum='$t_s[0]' AND
						status='$t_clo_val' $specific_where";
			$result2 = db_query( $query );
			$t_metrics['closed'][$t_key] = db_result( $result2, 0, 0);

			# Calculates the number of bugs resolved and puts the results in a table
			$query = "SELECT COUNT(*)
					FROM $t_bug_table
					WHERE $p_enum='$t_s[0]' AND
						status>='$t_res_val'  AND
						status<'$t_clo_val' $specific_where";
			$result2 = db_query( $query );
			$t_metrics['resolved'][$t_key] = db_result( $result2, 0, 0);
		} ### end for

		return $t_metrics;
	}

	# --------------------
	function create_developer_summary() {

		$t_project_id = helper_get_current_project();
		$t_user_table = config_get( 'mantis_user_table' );
		$t_bug_table = config_get( 'mantis_bug_table' );
		$t_user_id = auth_get_current_user_id();
		$specific_where = " AND " . helper_project_specific_where( $t_project_id, $t_user_id );

		$t_res_val = config_get( 'bug_resolved_status_threshold' );
		$t_clo_val = CLOSED;

		$query = "SELECT handler_id, status
				 FROM $t_bug_table
				 WHERE handler_id != '' $specific_where";
		$result = db_query( $query );
		$t_total_handled = db_num_rows( $result );

		$t_handler_arr = array();
		for ( $i = 0; $i < $t_total_handled; $i++ ) {
			$row = db_fetch_array( $result );
			if ( !isset( $t_handler_arr[$row['handler_id']] ) ) {
				$t_handler_arr[$row['handler_id']]['res'] = 0;
				$t_handler_arr[$row['handler_id']]['open'] = 0;
				$t_handler_arr[$row['handler_id']]['close'] = 0;
			}
			if ( $row['status'] >= $t_res_val ) {
				if ( $row['status'] >= $t_clo_val ) {
					$t_handler_arr[$row['handler_id']]['close']++;
				} else {
					$t_handler_arr[$row['handler_id']]['res']++;
				}
			} else {
				$t_handler_arr[$row['handler_id']]['open']++;
			}
		}

		if ( count( $t_handler_arr ) == 0 ) {
			return array( 'open' => array() );
		}

		$t_imploded_handlers = implode( ',', array_keys( $t_handler_arr ) );
		$query = "SELECT id, username
				FROM $t_user_table
				WHERE id IN ($t_imploded_handlers)
				ORDER BY username";
		$result = db_query( $query );
		$user_count = db_num_rows( $result );

		for ($i=0;$i<$user_count;$i++) {
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, 'v' );

			$t_metrics['open'][$v_username] = $t_handler_arr[$v_id]['open'];
			$t_metrics['resolved'][$v_username] = $t_handler_arr[$v_id]['res'];
			$t_metrics['closed'][$v_username] = $t_handler_arr[$v_id]['close'];
		} # end for
		return $t_metrics;
	}

	# --------------------
	function create_reporter_summary() {
		global $reporter_name, $reporter_count;


		$t_project_id = helper_get_current_project();
		$t_user_table = config_get( 'mantis_user_table' );
		$t_bug_table = config_get( 'mantis_bug_table' );
		$t_user_id = auth_get_current_user_id();
		$specific_where = " AND " . helper_project_specific_where( $t_project_id, $t_user_id );

		$query = "SELECT reporter_id
				 FROM $t_bug_table
				 WHERE id != '' $specific_where";
		$result = db_query( $query );
		$t_total_reported = db_num_rows( $result );

		$t_reporter_arr = array();
		for ( $i = 0; $i < $t_total_reported; $i++ ) {
			$row = db_fetch_array( $result );

			if ( isset( $t_reporter_arr[$row['reporter_id']] ) ) {
				$t_reporter_arr[$row['reporter_id']]++;
			} else {
				$t_reporter_arr[$row['reporter_id']] = 1;
			}
		}

		if ( count( $t_reporter_arr ) == 0 ) {
			return array();
		}

		$t_imploded_reporters = implode( ',', array_keys( $t_reporter_arr ) );
		$query = "SELECT id, username
				FROM $t_user_table
				WHERE id IN ($t_imploded_reporters)
				ORDER BY username";
		$result = db_query( $query );
		$user_count = db_num_rows( $result );

		for ($i=0;$i<$user_count;$i++) {
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, 'v' );

			$t_metrics[$v_username] = $t_reporter_arr[$v_id];
		} # end for
		return $t_metrics;
	}

	# --------------------
	function create_category_summary() {
		global $category_name, $category_bug_count;

		$t_project_id = helper_get_current_project();
		$t_cat_table = config_get( 'mantis_project_category_table' );
		$t_bug_table = config_get( 'mantis_bug_table' );
		$t_user_id = auth_get_current_user_id();
		$specific_where = helper_project_specific_where( $t_project_id, $t_user_id );

		$query = "SELECT DISTINCT category
				FROM $t_cat_table
				WHERE $specific_where
				ORDER BY category";
		$result = db_query( $query );
		$category_count = db_num_rows( $result );
		if ( 0 == $category_count ) {
			return array();
		}

		for ($i=0;$i<$category_count;$i++) {
			$row = db_fetch_array( $result );
			$t_cat_name = $row['category'];
			$c_category_name = addslashes($t_cat_name);
			$query = "SELECT COUNT(*)
					FROM $t_bug_table
					WHERE category='$c_category_name' AND $specific_where";
			$result2 = db_query( $query );
			$t_metrics[$t_cat_name] = db_result( $result2, 0, 0 );
		} # end for
		return $t_metrics;
	}

	# --------------------
	function cmp_dates($a, $b){
		if ($a[0] == $b[0]) {
			return 0;
		}
		return ( $a[0] < $b[0] ) ? -1 : 1;
	}

	# --------------------
	function find_date_in_metrics($aDate){
		global $metrics;
		$index = -1;
		for ($i=0;$i<count($metrics);$i++) {
			if ($aDate == $metrics[$i][0]){
				$index = $i;
				break;
			}
		}
		return $index;
	}

	# --------------------
	function create_cumulative_bydate(){

		$t_clo_val = CLOSED;
		$t_res_val = config_get( 'bug_resolved_status_threshold' );
		$t_bug_table = config_get( 'mantis_bug_table' );
		$t_history_table = config_get( 'mantis_bug_history_table' );

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

		for ($i=0;$i<$bug_count;$i++) {
			$row = db_fetch_array( $result );
			# rationalise the timestamp to a day to reduce the amount of data
 			$t_date = db_unixtimestamp( $row['date_submitted'] );
			$t_date = (int) ( $t_date / 86400 );

			if ( isset( $metrics[$t_date] ) ){
				$metrics[$t_date][0]++;
			} else {
				$metrics[$t_date] = array( 1, 0, 0 );
			}
		}

		### Get all the dates where a transition from not resolved to resolved may have happened
		#    also, get the last updated date for the bug as this may be all the information we have
		$query = "SELECT $t_bug_table.id, last_updated, date_modified, new_value, old_value
			FROM $t_bug_table LEFT JOIN $t_history_table
			ON mantis_bug_table.id = mantis_bug_history_table.bug_id
			WHERE $specific_where
						AND $t_bug_table.status >= '$t_res_val'
						AND ( ( $t_history_table.new_value >= '$t_res_val'
								AND $t_history_table.field_name = 'status' )
						OR $t_history_table.id is NULL )
			ORDER BY $t_bug_table.id, date_modified ASC";
		$result = db_query( $query );
		$bug_count = db_num_rows( $result );

		$t_last_id = 0;
		for ($i=0;$i<$bug_count;$i++) {
			$row = db_fetch_array( $result );
			$t_id = $row['id'];
			# if h_last_updated is NULL, there were no appropriate history records
			#  (i.e. pre 0.18 data), use last_updated from bug table instead
			if (NULL == $row['date_modified']) {
				$t_date = db_unixtimestamp( $row['last_updated'] );
			} else {
				if ( $t_res_val > $row['old_value'] ) {
					$t_date = db_unixtimestamp( $row['date_modified'] );
				}
			}
			if ( $t_id <> $t_last_id ) {
				if ( 0 <> $t_last_id ) {
					# rationalise the timestamp to a day to reduce the amount of data
					$t_date_index = (int) ( $t_last_date / 86400 );

					if ( isset( $metrics[$t_date_index] ) ){
						$metrics[$t_date_index][1]++;
					} else {
						$metrics[$t_date_index] = array( 0, 1, 0 );
					}
				}
				$t_last_id = $t_id;
			}
			$t_last_date = $t_date;
		}

		ksort($metrics);

		$metrics_count = count($metrics);
		$t_last_opened = 0;
		$t_last_resolved = 0;
		foreach ($metrics as $i=>$vals) {
			$t_date = $i * 86400;
			$t_metrics[$t_date][0] = $t_last_opened = $metrics[$i][0] + $t_last_opened;
			$t_metrics[$t_date][1] = $t_last_resolved = $metrics[$i][1] + $t_last_resolved;
			$t_metrics[$t_date][2] = $t_metrics[$t_date][0] - $t_metrics[$t_date][1];
		}
		return $t_metrics;
	}

	function graph_date_format ($p_date) {
		return date( config_get( 'short_date_format' ), $p_date );
	}


	# ----------------------------------------------------
	#
	# Check that there is enough data to create graph
	#
	# ----------------------------------------------------
	function error_check( $bug_count, $title ) {

		if ( 0 == $bug_count ) {
			$t_graph_font = graph_get_font();

			$graph = new CanvasGraph(300,380);

			$txt = new Text( lang_get( 'not_enough_data' ), 150, 100);
			$txt->Align("center","center","center");
			$txt->SetFont( $t_graph_font, FS_BOLD );
			$graph->title->Set( $title );
			$graph->title->SetFont( $t_graph_font, FS_BOLD );
			$graph->AddText($txt);
			$graph->Stroke();
			die();
		}
	}
?>
