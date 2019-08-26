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
 * Provides the functionality of legacy core excel/sml export format.
 */
class MantisExcelWriter extends TableWriterAbstract {
	use ObFileWriter;

	const BROWSER = 0;
	const FILE = 1;

	protected $worksheet_title ='';
	protected $destination = null;

	public function addRowFromArray( array $p_data_array, array $p_types_array = null ) {
		echo '<Row>';
		foreach( $p_data_array as $t_index => $t_value ) {
			$t_celltype = $p_types_array ? $p_types_array[$t_index] : Cell::TYPE_STRING;
			switch( $t_celltype ) {
				case Cell::TYPE_NUMERIC:
					$t_xmlcelltype = 'Number';
					break;
				case Cell::TYPE_DATE:
					$t_xmlcelltype = 'String';
					$t_value = $this->convertTimestampToString( $t_value );
					break;
				default:
					$t_xmlcelltype = 'String';
			}
			if( 'String' == $t_xmlcelltype ) {
				$t_value = str_replace( array( '&', "\n", '<', '>' ), array( '&amp;', '&#10;', '&lt;', '&gt;' ), $t_value );
			}
			echo '<Cell><Data ss:Type="', $t_xmlcelltype, '">', $t_value, "</Data></Cell>\n";
		}
		echo '</Row>';
	}

	public function openToBrowser( $p_output_file_name ) {
		$this->destination = self::BROWSER;
		$t_filename = pathinfo( $p_output_file_name, PATHINFO_FILENAME );
		$t_title = preg_replace( '/[\/:*?"<>|]/', '', $t_filename );
		$this->worksheet_title = $t_title;
		http_caching_headers( false );
		header( 'Content-Type: application/vnd.ms-excel; charset=UTF-8' );
		header( 'Pragma: public' );
		header( 'Content-Disposition: attachment; filename="' . urlencode( file_clean_name( $p_output_file_name ) . '.xml' ) ) ;

		echo $this->xml_header();
	}

	public function openToFile( $p_output_file_path ) {
		$this->destination = self::FILE;
		$this->file_start( $this->ensureLocalFilePath( $p_output_file_path ) );

		echo $this->xml_header();
	}

	public function close() {
		echo $this->xml_footer();
		if( $this->destination == self::FILE ) {
			$this->file_end();
		}
	}

	protected function xml_header() {
		return "<?xml version=\"1.0\" encoding=\"UTF-8\"?><?mso-application progid=\"Excel.Sheet\"?>"
		. "<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\""
		. " xmlns:x=\"urn:schemas-microsoft-com:office:excel\""
		. " xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\""
		. " xmlns:html=\"http://www.w3.org/TR/REC-html40\">\n "
		. '<Worksheet ss:Name="' . urlencode( $this->worksheet_title ) . "\">\n<Table>\n<Column ss:Index=\"1\" ss:AutoFitWidth=\"0\" ss:Width=\"110\"/>\n";
	}

	protected function xml_footer() {
		return "</Table>\n</Worksheet></Workbook>\n";
	}
}
