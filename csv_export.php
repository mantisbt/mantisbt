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
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	require_once( 'filter_api.php' );
	require_once( 'csv_api.php' );
	require_once( 'columns_api.php' );

	auth_ensure_user_authenticated();

	helper_begin_long_process();

	$t_page_number = 1;
	$t_per_page = -1;
	$t_bug_count = null;
	$t_page_count = null;

	$t_nl = csv_get_newline();
 	$t_sep = csv_get_separator();

	# Get bug rows according to the current filter
	$t_rows = filter_get_bug_rows( $t_page_number, $t_per_page, $t_page_count, $t_bug_count );
	if ( $t_rows === false ) {
		print_header_redirect( 'view_all_set.php?type=0' );
	}
	
	# pre-cache custom column data
	columns_plugin_cache_issue_data( $t_rows );

	$t_filename = csv_get_default_filename();

	# Send headers to browser to activate mime loading

	# Make sure that IE can download the attachments under https.
	header( 'Pragma: public' );

	header( 'Content-Type: text/csv; name=' . urlencode( file_clean_name( $t_filename ) ) );
	header( 'Content-Transfer-Encoding: BASE64;' );

	# Added Quotes (") around file name.
	header( 'Content-Disposition: attachment; filename="' . urlencode( file_clean_name( $t_filename ) ) . '"' );

	# Get columns to be exported
	$t_columns = csv_get_columns();

	# export BOM
	if ( config_get( 'csv_add_bom' ) == ON ) {
		echo "\xEF\xBB\xBF";
	}

	# export the titles
	$t_first_column = true;
	ob_start();
	$t_titles = array();
	foreach ( $t_columns as $t_column ) {
		if ( !$t_first_column ) {
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
	if ( strcmp( $t_first_three_chars, 'ID' . $t_sep ) == 0 ) {
		$t_header = str_replace( 'ID' . $t_sep, 'Id' . $t_sep, $t_header );
	}
	# end of fix

	echo $t_header;

	# export the rows
	foreach ( $t_rows as $t_row ) {
		$t_first_column = true;

		foreach ( $t_columns as $t_column ) {
			if ( !$t_first_column ) {
				echo $t_sep;
			} else {
				$t_first_column = false;
			}

			if ( column_get_custom_field_name( $t_column ) !== null || column_is_plugin_column( $t_column ) ) {
				ob_start();
				$t_column_value_function = 'print_column_value';
				helper_call_custom_function( $t_column_value_function, array( $t_column, $t_row, COLUMNS_TARGET_CSV_PAGE ) );
				$t_value = ob_get_clean();

				echo csv_escape_string( $t_value );
			} else {
				$t_function = 'csv_format_' . $t_column;
				echo $t_function( $t_row );
			}
		}

		echo $t_nl;
	}
