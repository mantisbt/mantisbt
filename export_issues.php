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
 * @package MantisBT
 * @copyright Copyright 2019  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses columns_api.php
 * @uses database_api.php
 * @uses export_api.php
 * @uses filter_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 */

# Prevent output of HTML in the content if errors occur
define( 'DISABLE_INLINE_ERROR_REPORTING', true );

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'columns_api.php' );
require_api( 'database_api.php' );
require_api( 'export_api.php' );
require_api( 'filter_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );

use Mantis\Export;

auth_ensure_user_authenticated();

$f_provider = gpc_get_string( 'provider', null );
$f_export_type = gpc_get_string( 'type', null );
$f_filename = gpc_get_string( 'filename', null );

$t_provider = null;
if( null !== $f_export_type ) {
	$t_provider = Export\TableWriterFactory::getProviderByType( $f_export_type );
} else {
	$t_provider = Export\TableWriterFactory::getProviderById( $f_provider );
}
if( !$t_provider ) {
	# @TODO error
	exit();
}

if( null == $f_filename ) {
	$t_filename = export_get_default_filename() . '.' . $t_provider->file_extension;
} else {
	$t_filename = urlencode( file_clean_name( $f_filename ) );
}

helper_begin_long_process();

# Get current filter
$t_filter = filter_get_bug_rows_filter();
$t_filter_query = new BugFilterQuery( $t_filter );
$t_filter_query->set_limit( EXPORT_BLOCK_SIZE );

if( 0 == $t_filter_query->get_bug_count() ) {
	print_header_redirect( 'view_all_set.php?type=0' );
}

$t_writer = Export\TableWriterFactory::createWriterFromProvider( $t_provider );
$t_writer->openToBrowser( $t_filename );

# Get columns to be exported
$t_columns = export_get_columns( $t_provider->file_extension );
$t_titles = array();
foreach ( $t_columns as $t_column ) {
	$t_titles[] = column_get_title( $t_column );
}
# Fixed for a problem in Excel where it prompts error message "SYLK: File Format Is Not Valid"
# See Microsoft Knowledge Base Article - 323626
if( isset( $t_titles[0] ) ) {
	if( $t_titles[0] == 'ID' ) {
		$t_titles[0] = 'Id';
	}
}
# end of fix
$t_writer->addRowFromArray( $t_titles );

$t_user_id = auth_get_current_user_id();
$t_end_of_results = false;
$t_offset = 0;
do {
	# Clear cache for next block
	bug_clear_cache_all();

	# select a new block
	$t_filter_query->set_offset( $t_offset );
	$t_result = $t_filter_query->execute();
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
	$t_bugs = filter_cache_result( $t_read_rows, $t_bug_id_array );
	bug_cache_columns_data( $t_bugs, $t_columns );

	# Clear arrays that are not needed
	unset( $t_read_rows );
	unset( $t_unique_user_ids );
	unset( $t_bug_id_array );

	# export the rows
	foreach ( $t_bugs as $t_bug ) {
		$t_row_values = array();
		$t_row_types = array();
		foreach ( $t_columns as $t_column ) {
			$t_row_values[] = export_bugfield_prepare_value( $t_column, $t_bug, $t_user_id );
			$t_row_types[] = export_bugfield_type( $t_column );
		}
		$t_writer->addRowFromArray( $t_row_values, $t_row_types );
	}

} while ( false === $t_end_of_results );

$t_writer->close();