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
# This upgrade moves attachments from the database to the disk

/**
 * Copy Custom fields to built in fields
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */
require_once( dirname( dirname( __FILE__ ) ) . '/core.php' );

access_ensure_global_level( config_get_global( 'admin_site_threshold' ) );

$f_source_field_id = gpc_get_int( 'source_id' );
$f_dest_field = gpc_get( 'dest_id' );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title> MantisBT Administration - Copy Custom Fields to Built-in </title>
<link rel="stylesheet" type="text/css" href="admin.css" />
</head>
<body>

<table width="100%" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
	<tr class="top-bar">
		<td class="links">
			[ <a href="system_utils.php">Back to System Utilities</a> ]
			[ <a href="copy_field.php?source_id=<?php echo $f_source_field_id?>&amp;dest_id=<?php echo $f_dest_field?>">Refresh view</a> ]
		</td>
		<td class="title">
			MantisBT Administration - Copy Custom Fields to Built-in
		</td>
	</tr>
</table>
<br /><br />

<?php
# checks on validity
$t_valid_fields = array(
	'fixed_in_version',
);
if( !in_array( $f_dest_field, $t_valid_fields ) ) {
	echo '<p>Invalid destination field (' . $f_dest_field . ') specified.</p>';
	echo '</body></html>';
	exit;
}

# @@@ check that source and destination are compatible

$t_query = 'SELECT * FROM {custom_field_string} WHERE field_id=%d and value <> %s';

$t_result = @db_query( $t_query, array( $f_source_field_id, '' ) );
if( FALSE == $t_result ) {
	echo '<p>No fields need to be updated.</p>';
}
else {

	$count = db_num_rows( $t_result );
	echo '<p>Found ' . $count . ' fields to be updated.</p>';
	$t_failures = 0;

	if( $count > 0 ) {
		echo '<table width="80%" bgcolor="#222222" cellpadding="10" cellspacing="1">';

		# Headings
		echo '<tr bgcolor="#ffffff"><th width="10%">Bug Id</th><th width="20%">Field Value</th><th width="70%">Status</th></tr>';
	}

	for( $i = 0;$i < $count;$i++ ) {
		$t_row = db_fetch_array( $t_result );
		extract( $t_row, EXTR_PREFIX_ALL, 'v' );

		# trace bug id back to project
		$t_project_id = bug_get_field( $v_bug_id, 'project_id' );
		$t_cust_value = $v_value;
		printf("\n<tr><td><a href=\"../view.php?id=%d\">%07d</a></td><td>%s</td><td>",
			$v_bug_id, $v_bug_id, $v_value);

		# validate field contents
		switch( $f_dest_field ) {
			case 'fixed_in_version':
				$t_valid = ( version_get_id( $t_cust_value, $t_project_id ) == FALSE ) ? FALSE : TRUE;
				break;
			default:
				$t_valid = FALSE;
		}
		if( $t_valid ) {

			# value was valid, update value
			if( !bug_set_field( $v_bug_id, $f_dest_field, $t_cust_value ) ) {
				echo 'database update failed';
				$t_failures++;
			} else {
				echo 'applied';
			}
		} else {
			echo 'field value was not valid or previously defined';
			$t_failures++;
		}
		echo '</td></tr>';
	}

	echo '</table><br />' . $count . ' fields processed, ' . $t_failures . ' failures';
}
echo '<p> Completed...<p>';
?>
</body>
</html>
