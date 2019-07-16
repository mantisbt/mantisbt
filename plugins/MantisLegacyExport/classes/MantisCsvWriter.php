<?php

namespace MantisLegacyExport;
use \Mantis\Export\TableWriterInterface;
use \Mantis\Export\Cell;


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_api( 'http_api.php' );
require_api( 'file_api.php' );

/**
 * Description of MantisCsvWriter
 *
 * @author cpm
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
