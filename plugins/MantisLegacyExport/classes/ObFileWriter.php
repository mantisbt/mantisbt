<?php
namespace MantisLegacyExport;

trait ObFileWriter {
	protected $ob_file_fp = null;

	protected function file_start( $p_filename ) {
		$this->ob_file_fp = fopen( $p_filename, 'w' );
		ob_start( array( $this, 'file_output_handler' ), 4096 );
	}

	protected function file_end() {
		@ob_end_flush();
		if( $this->ob_file_fp ) {
			  fclose( $this->ob_file_fp );
		}
		$this->ob_file_fp = null;
	}

	public function file_output_handler( $p_buffer ) {
		fwrite( $this->ob_file_fp, $p_buffer );
	}
}