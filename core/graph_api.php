<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: graph_api.php,v 1.13 2004-04-08 02:42:27 prescience Exp $
	# --------------------------------------------------------

	if ( ON == config_get( 'use_jpgraph' ) ) {
		$t_jpgraph_path = config_get( 'jpgraph_path' );

		require_once( $t_jpgraph_path.'jpgraph.php' );
		require_once( $t_jpgraph_path.'jpgraph_line.php' );
		require_once( $t_jpgraph_path.'jpgraph_bar.php' );
		require_once( $t_jpgraph_path.'jpgraph_pie.php' );
		require_once( $t_jpgraph_path.'jpgraph_pie3d.php' );
	}

	###########################################################################
	# Graph API
	###########################################################################

	# Function which gives the absolute values according to the status (opened/closed/resolved)
	function enum_bug_group( $p_enum_string, $p_enum ) {
		global $g_mantis_bug_table, $enum_name, $enum_name_count;
		#these vars are set global so that the other functions can use them
		global $open_bug_count, $closed_bug_count, $resolved_bug_count;

		$enum_name = Null;
		$enum_name_count = Null;

		$t_project_id = helper_get_current_project();

		$t_arr = explode_enum_string( $p_enum_string );
		$enum_count = count( $t_arr );
		for ($i=0;$i<$enum_count;$i++) {
			$t_s = explode( ':', $t_arr[$i] );
			$enum_name[] = get_enum_to_string( $p_enum_string, $t_s[0] );

			if ( ALL_PROJECTS == $t_project_id ) {
				$specific_where = '';
			} else {
				$specific_where = " AND project_id='$t_project_id'";
			}

			# Calculates the number of bugs with $p_enum and puts the results in a table
			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
						WHERE $p_enum='$t_s[0]' $specific_where";
			$result = db_query( $query );
			$enum_name_count[]= db_result( $result, 0);

			$t_res_val = RESOLVED;
			$t_clo_val = CLOSED;


			# Calculates the number of bugs opened and puts the results in a table
			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE $p_enum='$t_s[0]' AND
						status<>'$t_res_val' AND
						status<>'$t_clo_val' $specific_where";
			$result2 = db_query( $query );
			$open_bug_count[] = db_result( $result2, 0, 0);

			# Calculates the number of bugs closed and puts the results in a table
			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE $p_enum='$t_s[0]' AND
						status='$t_clo_val' $specific_where";
			$result2 = db_query( $query );

			$closed_bug_count[] = db_result( $result2, 0, 0);


			# Calculates the number of bugs resolved and puts the results in a table
			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE $p_enum='$t_s[0]' AND
						status='$t_res_val' $specific_where";
			$result2 = db_query( $query );
			$resolved_bug_count[] = db_result( $result2, 0, 0);


		} ### end for
	}

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
		$graph->Stroke();

	}

	# Function which finds the % according to the status
	function enum_bug_group_pct( $p_enum_string, $p_enum ) {
		global $g_mantis_bug_table, $enum_name, $enum_name_count;
		global $open_bug_count, $closed_bug_count, $resolved_bug_count;
		$enum_name = Null;
		$enum_name_count = Null;

		$t_project_id = helper_get_current_project();

		 #calculation per status
		 $t_res_val = RESOLVED;
		 $t_clo_val = CLOSED;

		if ( ALL_PROJECTS == $t_project_id ) {
			$specific_where = '';
		} else {
			$specific_where = " AND project_id='$t_project_id'";
		}

		$query = "SELECT COUNT(*)
				FROM $g_mantis_bug_table
				WHERE   status<>'$t_res_val' AND
					status<>'$t_clo_val' $specific_where";
		$result = db_query( $query );
		$total_open = db_result( $result, 0);


		# Bugs closed
		$query = "SELECT COUNT(*)
				FROM $g_mantis_bug_table
				WHERE   status='$t_clo_val' $specific_where";
		$result = db_query( $query );
		$total_close= db_result( $result, 0);


		# Bugs resolved
		$query = "SELECT COUNT(*)
				FROM $g_mantis_bug_table
				WHERE   status='$t_res_val' $specific_where";
		$result = db_query( $query );
		$total_resolved = db_result( $result, 0);


		$t_arr = explode_enum_string( $p_enum_string );
		$enum_count = count( $t_arr );
		for ($i=0;$i<$enum_count;$i++) {
			$t_s = explode( ':', $t_arr[$i] );
			$enum_name[] = get_enum_to_string( $p_enum_string, $t_s[0] );

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE $p_enum='$t_s[0]' $specific_where";
			$result = db_query( $query );
			$t_enum_count[]= db_result( $result, 0 );

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE $p_enum='$t_s[0]' AND
						status<>'$t_res_val' AND
						status<>'$t_clo_val' $specific_where";
			$result2 = db_query( $query );
			$open_bug_count[] = db_result( $result2, 0, 0) / $total_open * 100;

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE $p_enum='$t_s[0]' AND
						status='$t_clo_val' $specific_where";
			$result2 = db_query( $query );
			$closed_bug_count[] = db_result( $result2, 0, 0) / $total_close * 100;

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE $p_enum='$t_s[0]' AND
						status='$t_res_val' $specific_where";
			$result2 = db_query( $query );
			$resolved_bug_count[] = db_result( $result2, 0, 0) / $total_resolved * 100;

		} ### end for
	}

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
		$graph->Stroke();
	}

	# Function which gets the values in %
	function create_bug_enum_summary_pct( $p_enum_string, $p_enum ) {
		global $g_mantis_bug_table, $enum_name, $enum_name_count, $total;
		$enum_name = Null;
		$enum_name_count = Null;

		$t_project_id = helper_get_current_project();

		if ( ALL_PROJECTS == $t_project_id ) {
			$specific_where = '1=1';
		} else {
			$specific_where = " project_id='$t_project_id'";
		}

		$query = "SELECT COUNT(*)
	                      FROM $g_mantis_bug_table
	                      WHERE $specific_where";
		$result = db_query( $query );
		$total = db_result( $result, 0 );

		$t_arr = explode_enum_string( $p_enum_string );
		$enum_count = count( $t_arr );
		for ($i=0;$i<$enum_count;$i++) {
			$t_s = explode( ':', $t_arr[$i] );
			$enum_name[] = get_enum_to_string( $p_enum_string, $t_s[0] );

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE $p_enum='$t_s[0]' AND $specific_where";

			$result = db_query( $query );
			if ($total > 0){
			$enum_name_count[] = db_result( $result, 0 ) / $total * 100;
			}
			else{$enum_name_count[] = db_result( $result, 0 )*0;

			}
		} ### end for
	}

	# Function that displays pie charts
	function graph_bug_enum_summary_pct( $p_title=''){
		global $enum_name, $enum_name_count, $center, $poshorizontal, $posvertical;

		$graph = new PieGraph(500,350);
		$graph->img->SetMargin(40,40,40,100);
		$graph->title->Set($p_title);

		$graph->SetMarginColor('white');
		$graph->SetFrame(false);

		$graph->legend->Pos($poshorizontal, $posvertical);

		$p1 = new PiePlot3d($enum_name_count);
		$p1->SetTheme('earth');
		//$p1->SetTheme("sand");
		$p1->SetCenter($center);
		$p1->SetAngle(60);
		$p1->SetLegends($enum_name);

		# Label format
		$p1->value->SetFormat('%2.0f');
		$p1->value->Show();

		$graph->Add($p1);
		$graph->Stroke();
	}

	function create_category_summary_pct() {
		global 	$g_mantis_bug_table, $g_mantis_user_table,
				$g_mantis_project_category_table,
				$category_name, $category_bug_count;

		$t_project_id = helper_get_current_project();

		if ( ALL_PROJECTS == $t_project_id ) {
			$specific_where = '1=1';
		} else {
			$specific_where = " project_id='$t_project_id'";
		}

		$query = "SELECT COUNT(*)
				FROM $g_mantis_bug_table
				WHERE $specific_where";
		$result = db_query( $query );
		$total = db_result( $result, 0 );


		$query = "SELECT category
				FROM $g_mantis_project_category_table
				WHERE $specific_where
				ORDER BY category";
		$result = db_query( $query );
		$category_count = db_num_rows( $result );

		for ($i=0;$i<$category_count;$i++) {
			$row = db_fetch_array( $result );
			$category_name[] = $row['category'];
			$c_category_name = addslashes($category_name[$i]);

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE category='$c_category_name' AND $specific_where";

			$result2 = db_query( $query );

			$category_bug_count[] = db_result( $result2, 0 ) / $total * 100;

		} ### end for
	}

	# Pie chart which dispays by categories
	function graph_category_summary_pct( $p_title=''){
		global $category_name, $category_bug_count;

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
		$graph->Stroke();

	}

	function create_bug_enum_summary( $p_enum_string, $p_enum ) {
		global $g_mantis_bug_table, $enum_name, $enum_name_count;
		$enum_name = Null;
		$enum_name_count = Null;

		$t_project_id = helper_get_current_project();

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
					FROM $g_mantis_bug_table
					WHERE $p_enum='$c_s[0]' $specific_where";
			$result = db_query( $query );
			$enum_name_count[] = db_result( $result, 0 );
		} # end for
	}


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

		$graph->Stroke();

	}

	function create_developer_summary() {
		global 	$g_mantis_bug_table, $g_mantis_user_table, $g_mantis_project_user_list_table,
				$developer_name, $open_bug_count,
				$resolved_bug_count, $total_bug_count;

		$t_project_id = helper_get_current_project();

		# selecting all users, some of them might not have a proper default access
		# but be assigned on some particular projects
		$query = "SELECT id, username
				FROM $g_mantis_user_table
				ORDER BY username";
		$result = db_query( $query );
		$user_count = db_num_rows( $result );

		for ($i=0;$i<$user_count;$i++) {
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, 'v' );

			if ( ALL_PROJECTS == $t_project_id ) {
				$specific_where = '';
			} else {
				$specific_where = " AND project_id='$t_project_id'";
			}

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE handler_id='$v_id' $specific_where";
			$result2 = db_query( $query );
			$total_buff = db_result( $result2, 0, 0 );

			$t_res_val = RESOLVED;
			$t_clo_val = CLOSED;
			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE handler_id='$v_id' AND
						status<>'$t_res_val' AND
						status<>'$t_clo_val' $specific_where";
			$result2 = db_query( $query );
			$open_buff = db_result( $result2, 0, 0 );

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE handler_id='$v_id' AND
						(status='$t_res_val' OR status='$t_clo_val' ) $specific_where";
			$result2 = db_query( $query );
			$resolved_buff = db_result( $result2, 0, 0 );

			if (($total_buff+$resolved_buff+$open_buff)>0) {
				$open_bug_count[]=$open_buff;
				$resolved_bug_count[]=$resolved_buff;
				$total_bug_count[]=$total_buff;
				$developer_name[]=$v_username;
			}
		} # end for
	}

	function graph_developer_summary( ){
		global $developer_name, $total_bug_count, $open_bug_count, $resolved_bug_count;

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

		$graph->Stroke();

	}

	function create_reporter_summary() {
		global 	$g_mantis_bug_table, $g_mantis_user_table,
			$reporter_name, $reporter_count;


		$t_project_id = helper_get_current_project();

		$query = "SELECT id, username
				FROM $g_mantis_user_table
				ORDER BY username";
		$result = db_query( $query );
		$user_count = db_num_rows( $result );

		for ($i=0;$i<$user_count;$i++) {
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, 'v' );

			if ( ALL_PROJECTS == $t_project_id ) {
				$specific_where = '';
			} else {
				$specific_where = " AND project_id='$t_project_id'";
			}

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE reporter_id ='$v_id' $specific_where";
			$result2 = db_query( $query );
			$t_count =  db_result( $result2, 0, 0 );
			if ( $t_count > 0){
				$reporter_name[] = $v_username;
				$reporter_count[] = $t_count;
			}

		} # end for
	}

	function graph_reporter_summary( ){
		global $reporter_name, $reporter_count;

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

		$graph->Stroke();

	}

	function create_category_summary() {
		global 	$g_mantis_bug_table,
				$g_mantis_project_category_table,
				$category_name, $category_bug_count;

		$t_project_id = helper_get_current_project();

		if ( ALL_PROJECTS == $t_project_id ) {
			$specific_where = ' 1=1';
		} else {
			$specific_where = " AND project_id='$t_project_id'";
		}

		$query = "SELECT DISTINCT category
				FROM $g_mantis_project_category_table
				WHERE $specific_where
				ORDER BY category";
		$result = db_query( $query );
		$category_count = db_num_rows( $result );

		for ($i=0;$i<$category_count;$i++) {
			$row = db_fetch_array( $result );
			$category_name[] = $row['category'];
			$c_category_name = addslashes($category_name[$i]);

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE category='$c_category_name' AND $specific_where";
			$result2 = db_query( $query );
			$category_bug_count[] = db_result( $result2, 0, 0 );

		} # end for
	}

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

		$graph->Stroke();

	}

	function cmp_dates($a, $b){
		if ($a[0] == $b[0]) {
			return 0;
		}
		return ( $a[0] < $b[0] ) ? -1 : 1;
	}

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

	function create_cumulative_bydate(){
		global $metrics, $g_mantis_bug_table;

		$t_clo_val = CLOSED;
		$t_res_val = RESOLVED;

		$t_project_id = helper_get_current_project();

		if ( ALL_PROJECTS == $t_project_id ) {
			$specific_where = ' 1=1';
		} else {
			$specific_where = " AND project_id='$t_project_id'";
		}

		# Get all the submitted dates
		$query = "SELECT date_submitted
				FROM $g_mantis_bug_table
				WHERE $specific_where
				ORDER BY date_submitted";
		$result = db_query( $query );
		$bug_count = db_num_rows( $result );

		for ($i=0;$i<$bug_count;$i++) {
			$row = db_fetch_array( $result );
 			$t_date = db_unixtimestamp( $row['date_submitted'] );
			$t_date_string = date('Y-m-d', $t_date);

			$index = find_date_in_metrics($t_date_string);
			# Either the date is the same as the last date or it's new
			if  ($index > -1){
				$metrics[$index][1]++;
			} else {
				$metrics[] = array($t_date_string, 1, 0, 0);
			}
		}

		$t_clo_val = CLOSED;
		$t_res_val = RESOLVED;
		### Get all the resolved dates
		$query = "SELECT last_updated
			FROM $g_mantis_bug_table
			WHERE $specific_where AND
			(status='$t_res_val' OR status='$t_clo_val')
			ORDER BY last_updated";
		$result = db_query( $query );
		$bug_count = db_num_rows( $result );

		for ($i=0;$i<$bug_count;$i++) {
			$row = db_fetch_array( $result );
			$t_date = db_unixtimestamp( $row['last_updated'] );
			$t_date_string = date('Y-m-d', $t_date);

			$index = find_date_in_metrics($t_date_string);
			# Either the date is the same as a submitted date or it's new
			if ($index > -1){
				$metrics[$index][2]++;
			} else {
				$metrics[] = array($t_date_string, 0, 1, 0);
			}
		}

		usort($metrics, 'cmp_dates');

		$metrics_count = count($metrics);
		for ($i=1;$i<$metrics_count;$i++) {
			$metrics[$i][1] = $metrics[$i][1] + $metrics[$i-1][1];
			$metrics[$i][2] = $metrics[$i][2] + $metrics[$i-1][2];
			$metrics[$i][3] = $metrics[$i][1] - $metrics[$i][2];
		}

	}

	function graph_cumulative_bydate(){
		global $metrics;

		for ($i=0;$i<count($metrics);$i++) {
			$plot_date[] = strtotime($metrics[$i][0]);
			$reported_plot[] = $metrics[$i][1];
			$resolved_plot[] = $metrics[$i][2];
			$still_open_plot[] = $metrics[$i][3];
		}

		$graph = new Graph(300,380);
		$graph->img->SetMargin(40,40,40,170);
		$graph->img->SetAntiAliasing();
		$graph->SetScale('linlin');
		$graph->SetMarginColor('white');
		$graph->SetFrame(false);
		$graph->title->Set( 'cumulative ' . lang_get( 'by_date' ) );
		$graph->legend->Pos(0.1,0.6,'right','top');
		$graph->legend->SetShadow(false);
		$graph->legend->SetFillColor('white');
		$graph->legend->SetLayout(LEGEND_HOR);
		$graph->xaxis->Hide();
		$graph->xaxis->SetLabelAngle(90);
		$graph->yaxis->scale->ticks->SetDirection(-1);

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


		$graph->Stroke();
	}
	# ----------
?>