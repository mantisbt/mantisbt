<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# This upgrade moves attachments from the database to the disk

	# --------------------------------------------------------
	# $Id: workflow.php,v 1.2 2004-09-30 18:31:24 thraxisp Exp $
	# --------------------------------------------------------
?>
<?php
	require_once ( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core.php' );
	
?>
<html>
<head>
<title> Mantis Administration - Analyse Workflow </title>
<link rel="stylesheet" type="text/css" href="admin.css" />
</head>
<body>

<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
	<tr class="top-bar">
		<td class="links">
			[ <a href="system_utils.php">Back to System Utilities</a> ]
			[ <a href="workflow.php">Refresh view</a> ]
		</td>
		<td class="title">
			Mantis Administration - Analyse Workflow
		</td>
	</tr>
</table>
<br /><br />

<?php
	# count arcs in and out of each status
	$t_enum_status = config_get( 'status_enum_string' );
	$t_enum_workflow = config_get( 'status_enum_workflow' );
	$t_reopen = config_get( 'bug_reopen_status' );

	$t_status_arr  = explode_enum_string( $t_enum_status );
	$t_entry = array();
	$t_exit = array();

	echo '<center><table class="width50" cellspacing="1">';
	echo '<tr><th>Validation</th><th>Status</th><th></th></tr>';
	
	# prepopulate new bug state (bugs go from nothing to here)
	$t_submit_status_array = config_get( 'bug_submit_status' );
	if ( true == is_array( $t_submit_status_array ) ) {
		foreach ($t_submit_status_array as $t_access => $t_status ) {
			$t_entry[$t_status][0] = 'new'; 
			$t_exit[0][$t_status] = 'new'; 
		}
	}else{
			$t_status = $t_submit_status_array;
			$t_entry[$t_status][0] = 'new'; 
			$t_exit[0][$t_status] = 'new'; 
	}

  # add user defined arcs and implicit reopen arcs
	$t_reopen = config_get( 'bug_reopen_status' );
	$t_resolved_status = config_get( 'bug_resolved_status_threshold' );
	foreach ( $t_status_arr as $t_status ) {
		list( $t_status_id, $t_status_label ) = explode_enum_arr( $t_status );
		if ( isset( $t_enum_workflow[$t_status_id] ) ) {
			$t_next_arr = explode_enum_string( $t_enum_workflow[$t_status_id] );
			foreach ( $t_next_arr as $t_next ) {
				if ( !is_blank( $t_next ) ) {
					list( $t_next_id, $t_next_label ) = explode_enum_arr( $t_next );
					$t_exit[$t_status_id][$t_next_id] = '';
					$t_entry[$t_next_id][$t_status_id] = '';
					if ( $t_status_id == $t_next_id ) {
						echo '<tr ' . helper_alternate_class() . '><td>Superfluous arc to itself</td><td>' . $t_next_label . '</td><td bgcolor="#FFED4F">NOTE</td>';
					}
				}
			}
		}else{
			$t_exit[$t_status_id] = array();
		}
		if ( $t_status_id >= $t_resolved_status ) {
			$t_exit[$t_status_id][$t_reopen] = 'reopen';
			$t_entry[$t_reopen][$t_status_id] = 'reopen';
		}
		if ( ! isset( $t_entry[$t_status_id] ) ) {
			$t_entry[$t_status_id] = array();
		}
	}

	# check for entry == 0 without exit == 0, unreachable state
	foreach ( $t_status_arr as $t_status ) {
		list( $t_status_id, $t_status_label ) = explode_enum_arr( $t_status );
		if ( ( 0 == count( $t_entry[$t_status_id] ) ) && ( 0 < count( $t_exit[$t_status_id] ) ) ){
			echo '<tr ' . helper_alternate_class() . '><td>Status is unreachable</td><td>' . $t_status_label . '</td><td bgcolor="#FF0088">BAD</td>';
		}
	}

	# check for exit == 0 without entry == 0, unleaveable state
	foreach ( $t_status_arr as $t_status ) {
		list( $t_status_id, $t_status_label ) = explode_enum_arr( $t_status );
		if ( ( 0 == count( $t_exit[$t_status_id] ) ) && ( 0 < count( $t_entry[$t_status_id] ) ) ){
			echo '<tr ' . helper_alternate_class() . '><td>Can\'t leave status</td><td>' . $t_status_label . '</td><td bgcolor="#FF0088">BAD</td>';
		}
	}
	echo '</table></center><br /><br />';

	# display the graph as text
	$t_extra_enum_status = '0:non-existent,' . $t_enum_status;
	echo '<center><table class="width50" cellspacing="1">';
	echo '<tr><th>Status</th><th>Possible Next Status</th><th>Possible Previous Status</th></tr>';
	foreach ( $t_status_arr as $t_status ) {
		list( $t_status_id, $t_status_label ) = explode_enum_arr( $t_status );
		echo '<tr ' . helper_alternate_class() . '><td>' . $t_status_label . '</td><td>';
		foreach ( $t_exit[$t_status_id] as $t_next_id => $t_label) {
			echo get_enum_to_string( $t_extra_enum_status, $t_next_id );
			if ( '' != $t_label ) {
				echo ' (' . $t_label . ')';
			}
			echo '<br />';
		}
		echo '</td><td>';
		foreach ( $t_entry[$t_status_id] as $t_last_id => $t_label) {
			echo get_enum_to_string( $t_extra_enum_status, $t_last_id );
			if ( '' != $t_label ) {
				echo ' (' . $t_label . ')';
			}
			echo '<br />';
		}
		echo '</td></tr>';
	}
	echo '</table></center>';
	echo '<br />';
	echo '<br />';
	
	# display the graph as a matrix
	$t_all_status = explode( ',', $t_extra_enum_status);
	$t_status_count = count( $t_all_status);
	
	echo '<center><table class="width100" cellspacing="1">';
	echo '<tr><th width="25%">Current Status</th><th colspan="' . $t_status_count . '" class="center">Next Status</th></tr>';
	echo '<tr><th></th>';
	foreach ( $t_all_status as $t_status ) {
		list( $t_status_id, $t_status_label ) = explode_enum_arr( $t_status );
		echo '<th>' . $t_status_label . '</th>';
	}
	echo '</tr>';

	echo '<tr bgcolor="#326EFF"><td>Minumum Access Level to Change to Next Status</td>';
	echo '<td></td>'; # no path into non-existent
	foreach ( $t_status_arr as $t_status ) {
		list( $t_status_id, $t_status_label ) = explode_enum_arr( $t_status );
		if ( NEW_ == $t_status_id ) {
			$t_access = config_get( 'report_bug_threshold' );
		}else{
			$t_access = access_get_status_threshold( $t_status_id );
		}
		echo '<td class="center">' . get_enum_to_string( config_get( 'access_levels_enum_string' ), $t_access ) . '</td>';
	}
	echo '</tr>';
		
	foreach ( $t_all_status as $t_from_status ) {
		list( $t_from_status_id, $t_from_status_label ) = explode_enum_arr( $t_from_status );
		echo '<tr ' . helper_alternate_class() . '><td>' . $t_from_status_label . '</td>';
		foreach ( $t_all_status as $t_to_status) {
			list( $t_to_status_id, $t_to_status_label ) = explode_enum_arr( $t_to_status );
			if ( isset( $t_exit[$t_from_status_id][$t_to_status_id] ) ) {
				$t_label = $t_exit[$t_from_status_id][$t_to_status_id];
				echo '<td class="center">' . ( '' != $t_label ? '(' . $t_label . ')' : 'x' ) . '</td>';
			}else{
				echo '<td>&nbsp;</td>';
			}
		}
		echo '</tr>';
	}
	echo '</table></center>';

		echo '<p> Completed...<p>';
?>
</body>
</html>