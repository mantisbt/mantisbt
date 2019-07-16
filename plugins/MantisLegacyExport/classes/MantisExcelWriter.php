<?php

namespace MantisLegacyExport;
use \Mantis\Export\TableWriterInterface;
use \Mantis\Export\Cell;


class MantisExcelWriter implements TableWriterInterface {
	protected $worksheet_title ='';
	protected $date_format;

	public function __construct() {
		$this->date_format = config_get( 'short_date_format' );
	}

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
					$t_value = date( $this->date_format, $t_value );
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
		$t_filename = pathinfo( $p_output_file_name, PATHINFO_FILENAME );
		$t_title = preg_replace( '/[\/:*?"<>|]/', '', $t_filename );
		$this->worksheet_title = $t_title;
		http_caching_headers( false );
		header( 'Content-Type: application/vnd.ms-excel; charset=UTF-8' );
		header( 'Pragma: public' );
		header( 'Content-Disposition: attachment; filename="' . urlencode( file_clean_name( $p_output_file_name ) . '.xml' ) ) ;

		echo $this->xml_header();
	}

	public function close() {
		echo $this->xml_footer();
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
