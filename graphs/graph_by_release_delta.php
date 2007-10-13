<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

	# --------------------------------------------------------
	# $Id: graph_by_release_delta.php,v 1.9.22.1 2007-10-13 22:35:53 giallu Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( '../core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'graph_api.php' );
?>
<?php
	$data_category_arr = array();
	$data_count_arr = array();

	$t_project_id = helper_get_current_project();

	# Grab the Projections/Releases
	$query = "SELECT DISTINCT projection
			FROM mantis_bug_table
			WHERE project_id='$t_project_id'
			ORDER BY projection";
	$result = db_query( $query );
	$projection_count = db_num_rows( $result );
	$projection_arr = array();
	for ($i=0;$i<$projection_count;$i++) {
		$row = db_fetch_array( $result );
		extract( $row );

		$projection_arr[] = $projection;
	}

	$open_count_arr = array();
	$resolved_count_arr = array();
	$closed_count_arr = array();
	foreach ( $projection_arr as $t_projection ) {
		# OPEN
		$query = "SELECT COUNT(*) as count
				FROM mantis_bug_table
				WHERE project_id='$t_project_id' AND
					projection='$t_projection' AND
					status<80";
		$result = db_query ( $query );
		$open_count_arr[] = db_result( $result, 0, 0 );

		# RESOLVED
		$query = "SELECT COUNT(*) as count
				FROM mantis_bug_table
				WHERE project_id='$t_project_id' AND
					projection='$t_projection' AND
					status=80";
		$result = db_query ( $query );
		$resolved_count_arr[] = db_result( $result, 0, 0 );

		# CLOSED
		$query = "SELECT COUNT(*) as count
				FROM mantis_bug_table
				WHERE project_id='$t_project_id' AND
					projection='$t_projection' AND
					status=90";
		$result = db_query ( $query );
		$closed_count_arr[] = db_result( $result, 0, 0 );
	}

	$proj_name = get_project_field( $t_project_id, 'name' );

	$graph = new Graph(800,600,'auto');
	$graph->SetScale("textlin");
	$graph->SetShadow();
	$graph->img->SetMargin(40,30,40,80);

	$graph->xaxis->SetFont(FF_ARIAL,FS_NORMAL,10);
	$graph->xaxis->SetTickLabels($projection_arr);
	$graph->xaxis->SetLabelAngle(45);

	#$graph->xaxis->title->Set("Release Increments");
	#$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

	$graph->title->Set("$proj_name Release Delta Chart");
	$graph->title->SetFont(FF_FONT1,FS_BOLD);

	# Create graph
	$bplot1 = new BarPlot($open_count_arr);
	$bplot2 = new BarPlot($resolved_count_arr);
	$bplot3 = new BarPlot($closed_count_arr);

	$bplot1->SetFillColor("slateblue");
	$bplot2->SetFillColor("maroon");
	$bplot3->SetFillColor("lightgoldenrodyellow");

	$bplot1->SetShadow();
	$bplot2->SetShadow();
	$bplot3->SetShadow();

	$bplot1->SetLegend('Open');
	$bplot2->SetLegend('Resolved');
	$bplot3->SetLegend('Closed');

	$bplot1->value->Show();
	$bplot2->value->Show();
	$bplot3->value->Show();

	$bplot1->value->SetFont(FF_FONT1,FS_NORMAL,8);
	$bplot2->value->SetFont(FF_FONT1,FS_NORMAL,8);
	$bplot3->value->SetFont(FF_FONT1,FS_NORMAL,8);

	$bplot1->value->SetColor("black","darkred");
	$bplot2->value->SetColor("black","darkred");
	$bplot3->value->SetColor("black","darkred");

	$bplot1->value->SetFormat('%d');
	$bplot2->value->SetFormat('%d');
	$bplot3->value->SetFormat('%d');

	$gbarplot = new GroupBarPlot(array($bplot1,$bplot2,$bplot3));

	$gbarplot->SetWidth(0.6);
	$graph->Add($gbarplot);

	$graph->Stroke();
?>
