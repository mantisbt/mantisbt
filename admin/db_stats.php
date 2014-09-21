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
 * Display Database Statistics - Currently Row Count for each table.
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once( dirname( dirname( __FILE__ ) ) . '/core.php' );

access_ensure_global_level( config_get_global( 'admin_site_threshold' ) );

html_page_top();

/**
 * Output HTML Table Row
 *
 * @param string $p_description Row Description.
 * @param string $p_value       Row Value.
 * @return void
 */
function print_info_row( $p_description, $p_value ) {
	echo "\t\t" . '<tr>' . "\n";
	echo "\t\t\t" . '<td>' . $p_description . '</td>' . "\n";
	echo "\t\t\t" .'<td>' . $p_value . '</td>' . "\n";
	echo "\t\t" . '</tr>' . "\n";
}

/**
 * Function to get row count for a given table
 *
 * @param string $p_table Table name.
 * @return integer row count
 */
function helper_table_row_count( $p_table ) {
	$t_table = $p_table;
	$t_query = 'SELECT COUNT(*) FROM ' . $t_table;
	$t_result = db_query( $t_query );
	$t_count = db_result( $t_result );

	return $t_count;
}
?>
<div class="table-container">
	<h2><?php echo lang_get( 'mantisbt_database_statistics' ) ?></h2>
	<table cellspacing="1">
		<thead>
			<tr class="row-category">
				<th>Table Name</th>
				<th>Record Count</th>
			</tr>
		<thead>
		<tbody>
<?php
foreach( db_get_table_list() as $t_table ) {
	if( db_table_exists( $t_table ) ) {
			print_info_row( $t_table, helper_table_row_count( $t_table ) );
	}
}
?>
		</tbody>
	</table>
</div>
<?php

html_page_bottom();