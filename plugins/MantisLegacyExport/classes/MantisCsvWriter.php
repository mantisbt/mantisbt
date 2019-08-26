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
use \Mantis\Export\TableWriterAbstract;
use \Mantis\Export\Cell;

/**
 * A writer object that implement the mantis Table Writer interface.
 * Provides the functionality of legacy core csv export format.
 */
class MantisCsvWriter extends TableWriterAbstract {
	use ObFileWriter;

	const BROWSER = 0;
	const FILE = 1;

	public $newline;
	public $separator;
	protected $destination = null;

	public function __construct() {
		$this->newline = "\r\n";
		$this->separator = config_get( 'csv_separator' );
	}

	public function openToBrowser( $p_filename ) {
		$this->destination = self::BROWSER;
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

	public function openToFile( $p_output_file_path ) {
		$this->destination = self::FILE;
		$this->file_start( $this->ensureLocalFilePath( $p_output_file_path ) );
		echo UTF8_BOM;
	}

	public function addRowFromArray( array $p_data_array, array $p_types_array = null ) {
		$t_first_column = true;
		foreach( $p_data_array as $t_index => $t_value ) {
			if( !$t_first_column ) {
				echo $this->separator;
			}
			if( !$p_types_array ) {
				echo $this->escapeString( $t_value );
			} else {
				switch( $p_types_array[$t_index] ) {
					case Cell::TYPE_NUMERIC:
						echo $t_value;
						break;
					case Cell::TYPE_DATE:
						if( !date_is_null( $t_value ) ) {
							echo $this->convertTimestampToString( $t_value );
						}
						break;
					default:
						echo $this->escapeString( $t_value );
				}
			}
			$t_first_column = false;
		}
		echo $this->newline;
	}

	public function close() {
		if( $this->destination == self::FILE ) {
			$this->file_end();
		}
	}

	protected function escapeString( $p_string ) {
			$t_escaped = str_split( '"' . $this->separator . $this->newline );
			$t_must_escape = false;
			while( ( $t_char = current( $t_escaped ) ) !== false && !$t_must_escape ) {
				$t_must_escape = strpos( $p_string, $t_char ) !== false;
				next( $t_escaped );
			}
			if( $t_must_escape ) {
				$p_string = '"' . str_replace( '"', '""', $p_string ) . '"';
			}

			return $p_string;
	}
}
