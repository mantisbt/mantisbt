<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: graph_by_category.php,v 1.8 2005-02-12 20:03:48 jlatour Exp $
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

	$data_category_arr = array();
	$data_count_arr = array();
	$query = "SELECT category, COUNT(category) as count
			FROM mantis_bug_table
			WHERE project_id='$t_project_id'
			GROUP BY category
			ORDER BY category";
	$result = db_query( $query );
	$category_count = db_num_rows( $result );
	$total = 0;
	$longest_size = 0;
	for ($i=0;$i<$category_count;$i++) {
		$row = db_fetch_array( $result );
		extract( $row );

		$total += $count;
		$data_category_arr[] = $category;
		$data_count_arr[] = $count;

		if ( strlen( $category ) > $longest_size ) {
			$longest_size = strlen( $category );
		}
	}
	$longest_size++;
	for ($i=0;$i<$category_count;$i++) {
		#echo $data_count_arr[$i];
		$percentage = number_format( $data_count_arr[$i] / $total * 100, 1 );
		#echo $percentage;
		$percentage_str = str_pad($percentage, 5, ' ', STR_PAD_LEFT);
		$data_category_arr[$i] = str_pad($data_category_arr[$i], $longest_size);
		$data_category_arr[$i] = $data_category_arr[$i].$percentage_str;
		if ( $percentage < 1 ) {
			$data_count_arr[$i] = 0;
		}
	}

	$proj_name = project_get_field( $t_project_id, 'name' );

	# Setup Graph
	# ---

	$graph = new PieGraph( 800, 600 );
	$graph->SetShadow();

	# Set A title for the plot
	$graph->title->Set( "Category Distribution Graph: $proj_name" );
	$graph->title->SetFont( FF_FONT2, FS_BOLD );

	# Create graph
	$p1 = new PiePlot( $data_count_arr );
	$p1->SetLegends( $data_category_arr );
	$p1->SetSize( 260 );
	$p1->SetCenter( 0.35 );
	$p1->SetSliceColors( $g_color_arr );
	$p1->SetStartAngle( -90 );

	$graph->Add( $p1 );

	$graph->Stroke();
?>
