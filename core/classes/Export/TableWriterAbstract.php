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


}
