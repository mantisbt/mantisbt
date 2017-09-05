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
 * This file implements CSV export functionality within MantisBT
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses columns_api.php
 * @uses constant_inc.php
 * @uses csv_api.php
 * @uses file_api.php
 * @uses filter_api.php
 * @uses helper_api.php
 * @uses print_api.php
 */

# Prevent output of HTML in the content if errors occur
define( 'DISABLE_INLINE_ERROR_REPORTING', true );

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'columns_api.php' );
require_api( 'constant_inc.php' );
require_api( 'csv_api.php' );
require_api( 'file_api.php' );
require_api( 'filter_api.php' );
require_api( 'helper_api.php' );
require_api( 'print_api.php' );

auth_ensure_user_authenticated();

helper_begin_long_process();

$t_nl = csv_get_newline();
$t_sep = csv_get_separator();

# Get current filter
$t_filter = filter_get_bug_rows_filter();

# Get the query clauses
$t_query_clauses = filter_get_bug_rows_query_clauses( $t_filter );

# Get the total number of bugs that meet the criteria.
$p_bug_count = filter_get_bug_count( $t_query_clauses, /* pop_params */ false );

if( 0 == $p_bug_count ) {
	print_header_redirect( 'view_all_set.php?type=0' );
}

# Get columns to be exported
$t_columns = csv_get_columns();

csv_start( csv_get_default_filename() );

# export the titles
$t_first_column = true;
ob_start();
$t_titles = array();
foreach ( $t_columns as $t_column ) {
	if( !$t_first_column ) {
		echo $t_sep;
	} else {
		$t_first_column = false;
	}

	echo column_get_title( $t_column );
}

echo $t_nl;

$t_header = ob_get_clean();

# Fixed for a problem in Excel where it prompts error message "SYLK: File Format Is Not Valid"
# See Microsoft Knowledge Base Article - 323626
# http://support.microsoft.com/default.aspx?scid=kb;en-us;323626&Product=xlw
$t_first_three_chars = utf8_substr( $t_header, 0, 3 );
if( strcmp( $t_first_three_chars, 'ID' . $t_sep ) == 0 ) {
	$t_header = str_replace( 'ID' . $t_sep, 'Id' . $t_sep, $t_header );
}
# end of fix

echo $t_header;

$t_end_of_results = false;
$t_offset = 0;
do {
	# Clear cache for next block
	bug_clear_cache_all();

	# select a new block
	$t_result = filter_get_bug_rows_result( $t_query_clauses, EXPORT_BLOCK_SIZE, $t_offset, /* pop params */ false );
	$t_offset += EXPORT_BLOCK_SIZE;

	# Keep reading until reaching max block size or end of result set
	$t_read_rows = array();
	$t_count = 0;
	$t_bug_id_array = array();
	$t_unique_user_ids = array();
	while( $t_count < EXPORT_BLOCK_SIZE ) {
		$t_row = db_fetch_array( $t_result );
		if( false === $t_row ) {
			# a premature end indicates end of query results. Set flag as finished
			$t_end_of_results = true;
			break;
		}
		$t_bug_id_array[] = (int)$t_row['id'];
		$t_read_rows[] = $t_row;
		$t_count++;
	}
	# Max block size has been reached, or no more rows left to complete the block.
	# Either way, process what we have

	# convert and cache data
	$t_rows = filter_cache_result( $t_read_rows, $t_bug_id_array );
	bug_cache_columns_data( $t_rows, $t_columns );

	# Clear arrays that are not needed
	unset( $t_read_rows );
	unset( $t_unique_user_ids );
	unset( $t_bug_id_array );

	# export the rows
	foreach ( $t_rows as $t_row ) {
		$t_first_column = true;

		foreach ( $t_columns as $t_column ) {
			if( !$t_first_column ) {
				echo $t_sep;
			} else {
				$t_first_column = false;
			}

			$t_custom_field = column_get_custom_field_name( $t_column );
			if( $t_custom_field !== null ) {
				echo csv_format_custom_field( $t_row->id, $t_row->project_id, $t_custom_field );
			} else if( column_is_plugin_column( $t_column ) ) {
				echo csv_format_plugin_column_value( $t_column, $t_row );
			} else {
				$t_function = 'csv_format_' . $t_column;
				if( function_exists( $t_function ) ) {
					echo $t_function( $t_row );
				} else {
					# Field is unknown
					echo '';
				}
			}
		}

		echo $t_nl;
	}

} while ( false === $t_end_of_results );

