<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: graph_api.php,v 1.20 2004-09-10 00:29:50 thraxisp Exp $
	# --------------------------------------------------------

	if ( ON == config_get( 'use_jpgraph' ) ) {
		$t_jpgraph_path = config_get( 'jpgraph_path' );

		require_once( $t_jpgraph_path.'jpgraph.php' );
		require_once( $t_jpgraph_path.'jpgraph_line.php' );
		require_once( $t_jpgraph_path.'jpgraph_bar.php' );
		require_once( $t_jpgraph_path.'jpgraph_pie.php' );
		require_once( $t_jpgraph_path.'jpgraph_pie3d.php' );
	}

	### Graph API ###

	# --------------------
	# Function which gives the absolute values according to the status (opened/closed/resolved)
	function enum_bug_group( $p_enum_string, $p_enum ) {
		global $g_mantis_bug_table, $enum_name, $enum_name_count;
		#these vars are set global so that the other functions can use them
		global $open_bug_count, $closed_bug_count, $resolved_bug_count;

		$enum_name			= null;
		$enum_name_count	= null;

		$t_project_id = helper_get_current_project();
		$t_bug_table = config_get( 'mantis_bug_table' );

		$t_arr = explode_enum_string( $p_enum_string );
		$enum_count = count( $t_arr );
		for ( $i=0; $i < $enum_count; $i++) {
			$t_s = explode( ':', $t_arr[$i] );
			$enum_name[] = get_enum_to_string( $p_enum_string, $t_s[0] );

			if ( ALL_PROJECTS == $t_project_id ) {
				$specific_where = '';
			} else {
				$specific_where = " AND project_id='$t_project_id'";
			}

			# Calculates the number of bugs with $p_enum and puts the results in a table
			$query = "SELECT COUNT(*)
					FROM $t_bug_table
					WHERE $p_enum='$t_s[0]' $specific_where";
			$result = db_query( $query );
			$enum_name_count[]= db_result( $result, 0);

			$t_res_val = config_get( 'bug_resolved_status_threshold' );
			$t_clo_val = CLOSED;

			# Calculates the number of bugs opened and puts the results in a table
			$query = "SELECT COUNT(*)
					FROM $t_bug_table
					WHERE $p_enum='$t_s[0]' AND
						status<'$t_res_val' $specific_where";
			$result2 = db_query( $query );
			$open_bug_count[] = db_result( $result2, 0, 0);

			# Calculates the number of bugs closed and puts the results in a table
			$query = "SELECT COUNT(*)
					FROM $t_bug_table
					WHERE $p_enum='$t_s[0]' AND
						status='$t_clo_val' $specific_where";
			$result2 = db_query( $query );

			$closed_bug_count[] = db_result( $result2, 0, 0);

			# Calculates the number of bugs resolved and puts the results in a table
			$query = "SELECT COUNT(*)
					FROM $t_bug_table
					WHERE $p_enum='$t_s[0]' AND
						status>='$t_res_val'  AND
						status<'$t_clo_val' $specific_where";
			$result2 = db_query( $query );
			$resolved_bug_count[] = db_result( $result2, 0, 0);
		} ### end for
	}

	# --------------------
	# Function which displays the charts using the absolute values according to the status (opened/closed/resolved)
	function graph_group( $p_title='' ){
		global $enum_name, $enum_name_count;
		global $open_bug_count, $closed_bug_count, $resolved_bug_count,$height;

		#defines margin according to height
		$graph = new Graph(350,400);
		$graph->img->SetMargin(35,35,35,$height);
		$graph->img->SetAntiAliasing();
		$graph->SetScale('textlin');
		$graph->SetMarginColor('white');
		$graph->SetFrame(false);
		$graph->title->Set($p_title);
		$graph->xaxis->SetTickLabels($enum_name);
		$graph->xaxis->SetLabelAngle(90);
		$graph->legend->Pos(0.05, 0.08);

		$graph->yaxis->scale->ticks->SetDirection(-1);
		$graph->yscale->SetGrace(10);

		#adds on the same graph
		$tot = new LinePlot($enum_name_count);
        $tot->SetColor('black');
        $tot->SetWeight(2);
        $tot->mark->SetType(MARK_DIAMOND);

		$tot->SetLegend('Total');
		$graph->Add($tot);

		$p1 = new BarPlot($open_bug_count);
		$p1->SetFillColor('yellow');
		$p1->SetWidth(0.8);
		$p1->SetLegend('Opened');

		$p2 = new BarPlot($closed_bug_count);
		$p2->SetFillColor('blue');
		$p2->SetWidth(0.8);
		$p2->SetLegend('Closed');

		$p3 = new BarPlot($resolved_bug_count);
		$p3->SetFillColor('red');
		$p3->SetWidth(0.8);
		$p3->SetLegend('Resolved');

	    $gbplot = new GroupBarPlot(array($p1,$p2,$p3));
        $graph->Add($gbplot);
		if ( ON == config_get( 'show_queries_count' ) ) {
			$graph->subtitle->Set( db_count_queries() . ' queries (' . db_count_unique_queries() . ' unique)' );
		}
		$graph->Stroke();

	}

	# --------------------
	# Function which finds the % according to the status
	function enum_bug_group_pct( $p_enum_string, $p_enum ) {
		global $enum_name, $enum_name_count;
		global $open_bug_count, $closed_bug_count, $resolved_bug_count;
		$enum_name = Null;
		$enum_name_count = Null;

		$t_project_id = helper_get_current_project();
		$t_bug_table = config_get( 'mantis_bug_table' );

		#calculation per status
		$t_res_val = config_get( 'bug_resolved_status_threshold' );
		$t_clo_val = CLOSED;

		if ( ALL_PROJECTS == $t_project_id ) {
			$specific_where = '';
		} else {
			$specific_where = " AND project_id='$t_project_id'";
		}

		$query = "SELECT COUNT(*)
				FROM $t_bug_table
				WHERE   status<'$t_res_val' $specific_where";
		$result = db_query( $query );
		$total_open = db_result( $result, 0);


		# Bugs closed
		$query = "SELECT COUNT(*)
				FROM $t_bug_table
				WHERE   status='$t_clo_val' $specific_where";
		$result = db_query( $query );
		$total_close= db_result( $result, 0);


		# Bugs resolved
		$query = "SELECT COUNT(*)
				FROM $t_bug_table
				WHERE   status>='$t_res_val' AND
					status<'$t_clo_val' $specific_where";
		$result = db_query( $query );
		$total_resolved = db_result( $result, 0);


		$t_arr = explode_enum_string( $p_enum_string );
		$enum_count = count( $t_arr );
		for ($i=0;$i<$enum_count;$i++) {
			$t_s = explode( ':', $t_arr[$i] );
			$enum_name[] = get_enum_to_string( $p_enum_string, $t_s[0] );

			$query = "SELECT COUNT(*)
					FROM $t_bug_table
					WHERE $p_enum='$t_s[0]' $specific_where";
			$result = db_query( $query );
			$t_enum_count[]= db_result( $result, 0 );

			$query = "SELECT COUNT(*)
					FROM $t_bug_table
					WHERE $p_enum='$t_s[0]' AND
						status<'$t_res_val' $specific_where";
			$result2 = db_query( $query );
			if ( 0 < $total_open ) {
				$open_bug_count[] = db_result( $result2, 0, 0) / $total_open * 100;
			}else{
				$open_bug_count[] = 0;
			}

			$query = "SELECT COUNT(*)
					FROM $t_bug_table
					WHERE $p_enum='$t_s[0]' AND
						status='$t_clo_val' $specific_where";
			$result2 = db_query( $query );
			if ( 0 < $total_close ) {
				$closed_bug_count[] = db_result( $result2, 0, 0) / $total_close * 100;
			}else{
				$closed_bug_count[] = 0;
			}

			$query = "SELECT COUNT(*)
					FROM $t_bug_table
					WHERE $p_enum='$t_s[0]' AND
						status>='$t_res_val' AND
						status<'$t_clo_val' $specific_where";
			$result2 = db_query( $query );
			if ( 0 < $total_resolved ) {
				$resolved_bug_count[] = db_result( $result2, 0, 0) / $total_resolved * 100;
			}else{
				$resolved_bug_count[] = 0;
			}

		} ### end for
	}

	# --------------------
	# Function that displays charts in % according to the status
	function graph_group_pct( $p_title='' ){
		global $enum_name, $enum_name_count;
		global $open_bug_count, $closed_bug_count, $resolved_bug_count;

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
		$p1->SetLegend('Opened');

		$p2 = new BarPlot($closed_bug_count);
		$p2->SetFillColor('blue');
		$p2->SetWidth(0.8);
		$p2->SetLegend('Closed');

		$p3 = new BarPlot($resolved_bug_count);
		$p3->SetFillColor('red');
		$p3->SetWidth(0.8);
		$p3->SetLegend('Resolved');

        $gbplot = new GroupBarPlot(array($p1,$p2,$p3));

        $graph->Add($gbplot);
		if ( ON == config_get( 'show_queries_count' ) ) {
			$graph->subtitle->Set( db_count_queries() . ' queries (' . db_count_unique_queries() . ' unique)' );
		}
		$graph->Stroke();
	}

	# --------------------
	# Function which gets the values in %
	function create_bug_enum_summary_pct( $p_enum_string, $p_enum ) {
		global $enum_name, $enum_name_count, $total;
		$enum_name = Null;
		$enum_name_count = Null;

		$t_project_id = helper_get_current_project();
		$t_bug_table = config_get( 'mantis_bug_table' );

		if ( ALL_PROJECTS == $t_project_id ) {
			$specific_where = '1=1';
		} else {
			$specific_where = " project_id='$t_project_id'";
		}

		$query = "SELECT COUNT(*)
	                      FROM $t_bug_table
	                      WHERE $specific_where";
		$result = db_query( $query );
		$total = db_result( $result, 0 );
		if ( 0 == $total ) {
			return;
		}

		$t_arr = explode_enum_string( $p_enum_string );
		$enum_count = count( $t_arr );
		for ($i=0;$i<$enum_count;$i++) {
			$t_s = explode( ':', $t_arr[$i] );
			$enum_name[] = get_enum_to_string( $p_enum_string, $t_s[0] );

			$query = "SELECT COUNT(*)
					FROM $t_bug_table
					WHERE $p_enum='$t_s[0]' AND $specific_where";

			$result = db_query( $query );
			$enum_name_count[] = db_result( $result, 0 ) / $total * 100;
		} ### end for
	}

	# --------------------
	# Function that displays pie charts
	function graph_bug_enum_summary_pct( $p_title=''){
		global $enum_name, $enum_name_count, $center, $poshorizontal, $posvertical;

		if ( 0 == count($enum_name) ) {
			return;
		}
		$graph = new PieGraph(500,350);
		$graph->img->SetMargin(40,40,40,100);
		$graph->title->Set($p_title);

		$graph->SetMarginColor('white');
		$graph->SetFrame(false);

		$graph->legend->Pos($poshorizontal, $posvertical);

		$p1 = new PiePlot3d($enum_name_count);
		$p1->SetTheme('earth');
		#$p1->SetTheme("sand");
		$p1->SetCenter($center);
		$p1->SetAngle(60);
		$p1->SetLegends($enum_name);

		# Label format
		$p1->value->SetFormat('%2.0f');
		$p1->value->Show();

		$graph->Add($p1);
		if ( ON == config_get( 'show_queries_count' ) ) {
			$graph->subtitle->Set( db_count_queries() . ' queries (' . db_count_unique_queries() . ' unique)' );
		}
		$graph->Stroke();
	}

	# --------------------
	function create_category_summary_pct() {
		global $category_name, $category_bug_count;

		$t_project_id = helper_get_current_project();
		$t_bug_table = config_get( 'mantis_bug_table' );
		$t_cat_table = config_get( 'mantis_project_category_table' );

		if ( ALL_PROJECTS == $t_project_id ) {
			$specific_where = '1=1';
		} else {
			$specific_where = " t.project_id='$t_project_id'";
		}

		$query = "SELECT COUNT(*)
				FROM $t_bug_table as t
				WHERE $specific_where";
		$result = db_query( $query );
		$total = db_result( $result, 0 );
		if ( 0 == $total ) {
			return;
		}

		$query = "SELECT t.category, t.project_id, count(b.id) as bugs 
				FROM $t_cat_table as t LEFT JOIN $t_bug_table as b
				ON t.category = b.category AND t.project_id = b.project_id 
				WHERE $specific_where
				GROUP BY project_id, category
				ORDER BY project_id, category";
		$result = db_query( $query );
		$category_count = db_num_rows( $result );

		for ($i=0;$i<$category_count;$i++) {
			$row = db_fetch_array( $result );
			$category_name[] = $row['category'];
			$category_bug_count[] = $row['bugs'] / $total * 100;
		} ### end for
	}

	# --------------------
	# Pie chart which dispays by categories
	function graph_category_summary_pct( $p_title=''){
		global $category_name, $category_bug_count;

		if ( 0 == count( $category_bug_count) ) {
			return;
		}
		$graph = new PieGraph(600,450);
		$graph->img->SetMargin(40,40,40,100);
		$graph->title->Set($p_title);

		$graph->SetMarginColor('white');
		$graph->SetFrame(false);
		$graph->legend->Pos(0.10,0.09);

		$p1 = new PiePlot3d($category_bug_count);
		$p1->SetTheme('earth');
		$p1->SetCenter(0.3);
		$p1->SetAngle(60);
		$p1->SetLegends($category_name);
		$p1->SetSize(0.27);

		# Label format
		$p1->value->SetFormat('%2.0f');
		$p1->value->Show();

		$graph->Add($p1);
		if ( ON == config_get( 'show_queries_count' ) ) {
			$graph->subtitle->Set( db_count_queries() . ' queries (' . db_count_unique_queries() . ' unique)' );
		}
		$graph->Stroke();

	}

	# --------------------
	function create_bug_enum_summary( $p_enum_string, $p_enum ) {
		global $enum_name, $enum_name_count;
		$enum_name = Null;
		$enum_name_count = Null;

		$t_project_id = helper_get_current_project();
		$t_bug_table = config_get( 'mantis_bug_table' );

		$t_arr = explode_enum_string( $p_enum_string );
		$enum_count = count( $t_arr );
		for ($i=0;$i<$enum_count;$i++) {
			$t_s = explode_enum_arr( $t_arr[$i] );
			$c_s[0] = addslashes($t_s[0]);
			$enum_name[] = get_enum_to_string( $p_enum_string, $t_s[0] );

			if ( ALL_PROJECTS == $t_project_id ) {
				$specific_where = '';
			} else {
				$specific_where = " AND project_id='$t_project_id'";
			}

			$query = "SELECT COUNT(*)
					FROM $t_bug_table
					WHERE $p_enum='$c_s[0]' $specific_where";
			$result = db_query( $query );
			$enum_name_count[] = db_result( $result, 0 );
		} # end for
	}

	# --------------------
	function graph_bug_enum_summary( $p_title='' ){
		global $enum_name, $enum_name_count;

		$graph = new Graph(300,380);
		$graph->img->SetMargin(40,40,40,170);
		$graph->img->SetAntiAliasing();
		$graph->SetScale('textlin');
		$graph->SetMarginColor('white');
		$graph->SetFrame(false);
		$graph->title->Set($p_title);
		$graph->xaxis->SetTickLabels($enum_name);
		$graph->xaxis->SetLabelAngle(90);

		$graph->yaxis->scale->ticks->SetDirection(-1);

		$p1 = new BarPlot($enum_name_count);
		$p1->SetFillColor('yellow');
		$p1->SetWidth(0.8);
		$graph->Add($p1);
		if ( ON == config_get( 'show_queries_count' ) ) {
			$graph->subtitle->Set( db_count_queries() . ' queries (' . db_count_unique_queries() . ' unique)' );
		}

		$graph->Stroke();

	}

	# --------------------
	function create_developer_summary() {
		global $developer_name, $open_bug_count,
				$resolved_bug_count, $total_bug_count;

		$t_project_id = helper_get_current_project();
		$t_user_table = config_get( 'mantis_user_table' );
		$t_bug_table = config_get( 'mantis_bug_table' );

		if ( ALL_PROJECTS == $t_project_id ) {
			$specific_where = '';
		} else {
			$specific_where = " AND project_id='$t_project_id'";
		}

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
			}
			if ( $row['status'] >= $t_res_val ) {
				$t_handler_arr[$row['handler_id']]['res']++;
			} else {
				$t_handler_arr[$row['handler_id']]['open']++;
			}
		}

		if ( count( $t_handler_arr ) == 0 ) {
			return;
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

			$open_buff = $t_handler_arr[$v_id]['open'];
			$resolved_buff = $t_handler_arr[$v_id]['res'];

			if (($resolved_buff+$open_buff)>0) {
				$open_bug_count[]=$open_buff;
				$resolved_bug_count[]=$resolved_buff;
				$total_bug_count[]=$resolved_buff+$open_buff;
				$developer_name[]=$v_username;
			}
		} # end for
	}

	# --------------------
	function graph_developer_summary( ){
		global $developer_name, $total_bug_count, $open_bug_count, $resolved_bug_count;

		if ( 0 == count($developer_name) ) {
			return;
		}

		$graph = new Graph(300,380);
		$graph->img->SetMargin(40,40,40,170);
		$graph->img->SetAntiAliasing();
		$graph->SetScale('textlin');
		$graph->SetMarginColor('white');
		$graph->SetFrame(false);
		$graph->title->Set( lang_get( 'by_developer' ) );
		$graph->xaxis->SetTickLabels($developer_name);
		$graph->xaxis->SetLabelAngle(90);
		$graph->yaxis->scale->ticks->SetDirection(-1);

		$graph->legend->Pos(0.1,0.8,'right','top');
		$graph->legend->SetShadow(false);
		$graph->legend->SetFillColor('white');
		$graph->legend->SetLayout(LEGEND_HOR);

		$p1 = new BarPlot($open_bug_count);
		$p1->SetFillColor('red');
		$p1->SetLegend('Still Open');

		$p2 = new BarPlot($resolved_bug_count);
		$p2->SetFillColor('yellow');
		$p2->SetLegend('Resolved');

		$p3 = new BarPlot($total_bug_count);
		$p3->SetFillColor('blue');
		$p3->SetLegend('Assigned');

		$gbplot =  new GroupBarPlot( array($p1, $p2, $p3));
		$graph->Add($gbplot);
		if ( ON == config_get( 'show_queries_count' ) ) {
			$graph->subtitle->Set( db_count_queries() . ' queries (' . db_count_unique_queries() . ' unique)' );
		}

		$graph->Stroke();

	}

	# --------------------
	function create_reporter_summary() {
		global $reporter_name, $reporter_count;


		$t_project_id = helper_get_current_project();
		$t_user_table = config_get( 'mantis_user_table' );
		$t_bug_table = config_get( 'mantis_bug_table' );

		if ( ALL_PROJECTS == $t_project_id ) {
			$specific_where = '';
		} else {
			$specific_where = " AND project_id='$t_project_id'";
		}

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
			return;
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

			if ( $t_reporter_arr[$v_id] > 0){
				$reporter_name[] = $v_username;
				$reporter_count[] = $t_reporter_arr[$v_id];
			}
		} # end for
	}

	# --------------------
	function graph_reporter_summary( ){
		global $reporter_name, $reporter_count;

		if ( 0 == count($reporter_name) ) {
			return;
		}
		$graph = new Graph(300,380);
		$graph->img->SetMargin(40,40,40,170);
		$graph->img->SetAntiAliasing();
		$graph->SetScale('textlin');
		$graph->SetMarginColor('white');
		$graph->SetFrame(false);
		$graph->title->Set( lang_get( 'by_reporter' ) );
		$graph->xaxis->SetTickLabels($reporter_name);
		$graph->xaxis->SetLabelAngle(90);
		$graph->yaxis->scale->ticks->SetDirection(-1);

		$p1 = new BarPlot($reporter_count);
		$p1->SetFillColor('yellow');
		$p1->SetWidth(0.8);
		$graph->Add($p1);

		if ( ON == config_get( 'show_queries_count' ) ) {
			$graph->subtitle->Set( db_count_queries() . ' queries (' . db_count_unique_queries() . ' unique)' );
		}
		$graph->Stroke();

	}

	# --------------------
	function create_category_summary() {
		global $category_name, $category_bug_count;

		$t_project_id = helper_get_current_project();
		$t_cat_table = config_get( 'mantis_project_category_table' );
		$t_bug_table = config_get( 'mantis_bug_table' );

		if ( ALL_PROJECTS == $t_project_id ) {
			$specific_where = ' 1=1';
		} else {
			$specific_where = " project_id='$t_project_id'";
		}

		$query = "SELECT DISTINCT category
				FROM $t_cat_table
				WHERE $specific_where
				ORDER BY category";
		$result = db_query( $query );
		$category_count = db_num_rows( $result );

		for ($i=0;$i<$category_count;$i++) {
			$row = db_fetch_array( $result );
			$category_name[] = $row['category'];
			$c_category_name = addslashes($category_name[$i]);

			$query = "SELECT COUNT(*)
					FROM $t_bug_table
					WHERE category='$c_category_name' AND $specific_where";
			$result2 = db_query( $query );
			$category_bug_count[] = db_result( $result2, 0, 0 );

		} # end for
	}

	# --------------------
	function graph_category_summary(){
		global $category_name, $category_bug_count;

		$graph = new Graph(300,380);
		$graph->img->SetMargin(40,40,40,170);
		$graph->img->SetAntiAliasing();
		$graph->SetScale('textlin');
		$graph->SetMarginColor('white');
		$graph->SetFrame(false);
		$graph->title->Set( lang_get( 'by_category' ) );
		$graph->xaxis->SetTickLabels($category_name);
		$graph->xaxis->SetLabelAngle(90);
		$graph->yaxis->scale->ticks->SetDirection(-1);

		$p1 = new BarPlot($category_bug_count);
		$p1->SetFillColor('yellow');
		$p1->SetWidth(0.8);
		$graph->Add($p1);

		if ( ON == config_get( 'show_queries_count' ) ) {
			$graph->subtitle->Set( db_count_queries() . ' queries (' . db_count_unique_queries() . ' unique)' );
		}
		$graph->Stroke();

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
		global $metrics;

		$t_clo_val = CLOSED;
		$t_res_val = config_get( 'bug_resolved_status_threshold' );
		$t_bug_table = config_get( 'mantis_bug_table' );

		$t_project_id = helper_get_current_project();

		if ( ALL_PROJECTS == $t_project_id ) {
			$specific_where = ' 1=1';
		} else {
			$specific_where = " project_id='$t_project_id'";
		}

		# Get all the submitted dates
		$query = "SELECT date_submitted
				FROM $t_bug_table
				WHERE $specific_where
				ORDER BY date_submitted";
		$result = db_query( $query );
		$bug_count = db_num_rows( $result );

		for ($i=0;$i<$bug_count;$i++) {
			$row = db_fetch_array( $result );
 			$t_date = db_unixtimestamp( $row['date_submitted'] );

			if ( isset( $metrics[$t_date] ) ){
				$metrics[$t_date][0]++;
			} else {
				$metrics[$t_date] = array( 1, 0, 0 );
			}
		}

		### Get all the resolved dates
		$query = "SELECT last_updated
			FROM $t_bug_table
			WHERE $specific_where AND
			status >='$t_res_val'
			ORDER BY last_updated";
		$result = db_query( $query );
		$bug_count = db_num_rows( $result );

		for ($i=0;$i<$bug_count;$i++) {
			$row = db_fetch_array( $result );
			$t_date = db_unixtimestamp( $row['last_updated'] );
			if ( isset( $metrics[$t_date] ) ){
				$metrics[$t_date][1]++;
			} else {
				$metrics[$t_date] = array( 0, 1, 0 );
			}
		}

		ksort($metrics);

		$metrics_count = count($metrics);
		$t_last_opened = 0;
		$t_last_resolved = 0;
		foreach ($metrics as $i=>$vals) {
			$metrics[$i][0] = $metrics[$i][0] + $t_last_opened;
			$metrics[$i][1] = $metrics[$i][1] + $t_last_resolved;
			$metrics[$i][2] = $metrics[$i][0] - $metrics[$i][1];
		  $t_last_opened = $metrics[$i][0];
			$t_last_resolved = $metrics[$i][1];
		}
	}

	function graph_date_format ($p_date) {
		return strftime( "%D", $p_date );
	}
	
	# --------------------
	function graph_cumulative_bydate(){
		global $metrics;

		if ( 0 == count($metrics) ) {
			return;
		}
		foreach ($metrics as $i=>$vals) {
			if ( $i > 0 ) {
				$plot_date[] = $i;
				$reported_plot[] = $metrics[$i][0];
				$resolved_plot[] = $metrics[$i][1];
				$still_open_plot[] = $metrics[$i][2];
			}
		}

		$graph = new Graph(300,380);
		$graph->img->SetMargin(40,40,40,170);
		$graph->img->SetAntiAliasing();
		$graph->SetScale('linlin');
		$graph->SetMarginColor('white');
		$graph->SetFrame(false);
		$graph->title->Set( 'cumulative ' . lang_get( 'by_date' ) );
		$graph->legend->Pos(0.1,0.8,'right','bottom');
		$graph->legend->SetShadow(false);
		$graph->legend->SetFillColor('white');
		$graph->legend->SetLayout(LEGEND_HOR);
		$graph->yaxis->scale->ticks->SetDirection(-1);
#		$graph->xaxis->Hide();
		$graph->xaxis->SetLabelAngle(90);
		$graph->xaxis->SetLabelFormatCallback('graph_date_format');

		$p1 = new LinePlot($reported_plot, $plot_date);
		$p1->SetColor('blue');
		$p1->SetCenter();
		$p1->SetLegend('Reported');
		$graph->Add($p1);

		$p3 = new LinePlot($still_open_plot, $plot_date);
		$p3->SetColor('red');
		$p3->SetCenter();
		$p3->SetLegend('Still Open');
		$graph->Add($p3);

		$p2 = new LinePlot($resolved_plot, $plot_date);
		$p2->SetColor('black');
		$p2->SetCenter();
		$p2->SetLegend('Resolved');
		$graph->Add($p2);

		if ( ON == config_get( 'show_queries_count' ) ) {
			$graph->subtitle->Set( db_count_queries() . ' queries (' . db_count_unique_queries() . ' unique)' );
		}
		$graph->Stroke();
	}
?>