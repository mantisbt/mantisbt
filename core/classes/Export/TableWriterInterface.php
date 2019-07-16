<?php

namespace Mantis\Export;

interface TableWriterInterface {
	//public function openToFile( $p_output_file_path );
	public function openToBrowser( $p_output_file_name );
	public function close();
	public function addRowFromArray( array $p_data_array, array $p_types_array = null );
}
