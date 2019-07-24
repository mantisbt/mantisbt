<?php
/**
 * MantisBT - A PHP based bugtracking system
 *
 * MantisBT is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * MantisBT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright Copyright 2019  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 */

namespace MantisLegacyExport;
use \Mantis\Export\TableWriterInterface;
use \Mantis\Export\Cell;

# legacy csv_api is included by this plugin init() routine

/**
 * A writer object that implement the mantis Table Writer interface.
 * Provides the functionality of legacy core csv export format.
 */
class MantisCsvWriter implements TableWriterInterface {
	public $newline;
	public $separator;
	public $date_format;

	public function __construct() {
		$this->newline = csv_get_newline();
		$this->separator = config_get( 'csv_separator' );
		$this->date_format = config_get( 'short_date_format' );
	}

	public function openToBrowser( $p_filename ) {
		$t_filename = pathinfo( $p_filename, PATHINFO_FILENAME );
		$t_filename = urlencode( file_clean_name( $t_filename ) );
		http_caching_headers( false );
		header( 'Pragma: public' );
		header( 'Content-Encoding: UTF-8' );
		header( 'Content-Type: text/csv; name=' . $t_filename . ';charset=UTF-8' );
		header( 'Content-Transfer-Encoding: BASE64;' );
		header( 'Content-Disposition: attachment; filename="' . $t_filename . '.csv"' );

		# clear buffer
		if (ob_get_length() > 0) {
			ob_end_clean();
		}
		echo UTF8_BOM;

	}

	public function addRowFromArray( array $p_data_array, array $p_types_array = null ) {
		$t_first_column = true;
		foreach( $p_data_array as $t_index => $t_value ) {
			if( !$t_first_column ) {
				echo $this->separator;
			}
			if( !$p_types_array ) {
				echo csv_escape_string( $t_value );
			} else {
				switch( $p_types_array[$t_index] ) {
					case Cell::TYPE_NUMERIC:
						echo $t_value;
						break;
					case Cell::TYPE_DATE:
						if( !date_is_null( $t_value ) ) {
							echo date( $this->date_format, $t_value );
						}
						break;
					default:
						echo csv_escape_string( $t_value );
				}
			}
			$t_first_column = false;
		}
		echo $this->newline;
	}

	public function close() {
	}


}
