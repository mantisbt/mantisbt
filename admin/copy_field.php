<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# This upgrade moves attachments from the database to the disk

	# --------------------------------------------------------
	# $Id: copy_field.php,v 1.1 2004-07-25 00:13:08 thraxisp Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( '../core.php' );
	
	$f_source = gpc_get_int( 'source_id' );
	$f_dest = gpc_get( 'dest_id' );
?>
<html>
<head>
<title> Mantis Administration - Copy Custom Fields to Built-in </title>
<link rel="stylesheet" type="text/css" href="admin.css" />
</head>
<body>

<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
	<tr class="top-bar">
		<td class="links">
			[ <a href="system_utils.php">Back to System Utilities</a> ]
			[ <a href="copy_field.php">Refresh view</a> ]
		</td>
		<td class="title">
			Mantis Administration - Copy Custom Fields to Built-in
		</td>
	</tr>
</table>
<br /><br />

<?php
	#checks on validity
	#@@@ check that source and destination are compatible

	$t_string_table = config_get( 'mantis_custom_field_string_table' );
	$t_bug_table = config_get( 'mantis_bug_table' );
	$query = 'SELECT * FROM ' . $t_string_table . ' WHERE field_id = ' . $f_source . ' and value <> \'\'';

	$result = @db_query( $query );
	if ( false == $result ) {
		echo '<p>No fields need to be updated.';
	}else{

		$count = db_num_rows( $result );
		echo '<p>Found ' . $count . ' fields to be updated.';
		$t_failures = 0;

		if ( $count > 0 ) {
			echo '<table width="80%" bgcolor="#222222" border="0" cellpadding="10" cellspacing="1">';
			# Headings
			echo '<tr bgcolor="#ffffff"><th width="10%">Bug Id</th><th width="20%">Field Value</th><th width="70%">Status</th></tr>';
		}

		for ( $i=0 ; $i < $count ; $i++ ) {
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, 'v' );

			# trace bug id back to project
			$t_project_id = bug_get_field( $v_bug_id, 'project_id' );
			$t_bug_id = $v_bug_id;
			$t_cust_value = $v_value;
			printf("\n<tr %s><td>%8d</td><td>%s</td><td>", helper_alternate_class(), $t_bug_id, $v_value);

			switch ( $f_dest ) {
				case 'fixed in':
					$t_version_id = version_get_id( $t_cust_value, $t_project_id );
					if ( $t_version_id <> FALSE ) {
						# it matched, update value
						$query2 = "UPDATE $t_bug_table SET fixed_in_version = '$t_cust_value' WHERE id = $t_bug_id";
						$update = @db_query( $query2 );
						if ( ! $update ) {
							echo 'database update failed';
							$t_failures++;
						}else{
							echo 'applied';
						}
					}else{
						echo 'no matching version found';
						$t_failures++;
					}
					break;
				# other conversions go here
				default:
			}
			echo '</td></tr>';
		}

		echo '</table><br />' . $count . ' fields processed, ' . $t_failures . ' failures';
	}
	echo '<p> Completed...';
?>
</body>
</html>