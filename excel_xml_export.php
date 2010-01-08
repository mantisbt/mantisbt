<?php
# MantisBT - a php based bugtracking system

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
	 * Excel (2003 SP2 and above) export page
	 *
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2010  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	require_once( 'current_user_api.php' );
	require_once( 'bug_api.php' );
	require_once( 'string_api.php' );
	require_once( 'columns_api.php' );
	require_once( 'excel_api.php' );

	require( 'print_all_bug_options_inc.php' );

	auth_ensure_user_authenticated();

	$f_export = gpc_get_string( 'export', '' );

	helper_begin_long_process();

	$t_export_title = excel_get_default_filename();

	$t_short_date_format = config_get( 'short_date_format' );

	# This is where we used to do the entire actual filter ourselves
	$t_page_number = gpc_get_int( 'page_number', 1 );
	$t_per_page = 100;
	$t_bug_count = null;
	$t_page_count = null;

	$result = filter_get_bug_rows( $t_page_number, $t_per_page, $t_page_count, $t_bug_count );
	if ( $result === false ) {
		print_header_redirect( 'view_all_set.php?type=0&print=1' );
	}

	header( 'Content-Type: application/vnd.ms-excel; charset=UTF-8' );
	header( 'Pragma: public' );
	header( 'Content-Type: application/vnd.ms-excel' );
	header( 'Content-Disposition: attachment; filename="' . urlencode( file_clean_name( $t_export_title ) ) . '.xml"' ) ;

	echo excel_get_header( $t_export_title );
	echo excel_get_titles_row();

	$f_bug_arr = explode( ',', $f_export );

	$t_columns = excel_get_columns();

	do
	{
		$t_more = true;
		$t_row_count = count( $result );

		for( $i = 0; $i < $t_row_count; $i++ ) {
			$t_row = $result[$i];
			$t_bug = null;

			if ( is_blank( $f_export ) || in_array( $t_row->id, $f_bug_arr ) ) {
				echo excel_get_start_row();

				foreach ( $t_columns as $t_column ) {
					if ( column_is_extended( $t_column ) ) {
						if ( $t_bug === null ) {
							$t_bug = bug_get( $t_row->id, /* extended = */ true );
						}

						$t_function = 'excel_format_' . $t_column;
						echo $t_function( $t_bug->$t_column );
					} else {
						$t_custom_field = column_get_custom_field_name( $t_column );
						if ( $t_custom_field !== null ) {
							echo excel_format_custom_field( $t_row->id, $t_row->project_id, $t_custom_field );
						} else {
							$t_function = 'excel_format_' . $t_column;
							echo $t_function( $t_row->$t_column );
						}
					}
				}

				echo excel_get_end_row();
			} #in_array
		} #for loop

		// If got a full page, then attempt for the next one.
		// @@@ Note that since we are not using a transaction, there is a risk that we get a duplicate record or we miss
		// one due to a submit or update that happens in parallel.
		if ( $t_row_count == $t_per_page ) {
			$t_page_number++;
			$t_bug_count = null;
			$t_page_count = null;

			$result = filter_get_bug_rows( $t_page_number, $t_per_page, $t_page_count, $t_bug_count );
			if ( $result === false ) {
				$t_more = false;
			}
		} else {
			$t_more = false;
		}
	} while ( $t_more );

	echo excel_get_footer();
