<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Mantis\Export;
use \Mantis\Export\TableWriterInterface;

/**
 * Description of TableWriterAbstract
 *
 * @author mantisdev
 */
abstract class TableWriterAbstract  implements TableWriterInterface {
	const DATE_FORMAT_SHORT = 'short_date_format';
	const DATE_FORMAT_NORMAL = 'normal_date_format';
	const DATE_FORMAT_COMPLETE = 'complete_date_format';

	/**
	 * Converts a mantis date, represented as integer timestamp, to a string representation
	 * @param integer $p_time	Mantis date as integer timestamp
	 * @param string $p_format	String format, see DATE_FORMAT_xxx constants
	 * @return string		String representation for the date.
	 */
	function convertTimestampToString( $p_time, $p_format = self::DATE_FORMAT_SHORT ) {
		$t_date_format = config_get( $p_format );
		return date( $t_date_format, $p_time );
	}

	/**
	 * Always use this method to get a secure output file path.
	 * Returns a validated, and transformed file path for the target output file. The resulting
	 * path is a complete, absolute, path including the file name. Also checks if the system
	 * configuration allows exporting to files.
	 * If any validation fails, an exception will be thrown.
	 * The input path parameter may copntain relative, or empty, directories parts. In that case
	 * the returned path will be an absolute route to the actual file to be used, according to
	 * configuration.
	 *
	 * @param string $p_filepath	Requested file path for the output file.
	 * @return string		A valid file path, according to configuration.
	 * @throws ServiceException
	 * @throws ExportFileIOException
	 */
	function ensureLocalFilePath( $p_filepath ){
		export_ensure_file_is_allowed();
		return export_real_file_path( $p_filepath );
	}
}
