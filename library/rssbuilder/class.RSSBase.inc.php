<?php
/**
* Class for creating an RSS-feed
* @author Michael Wimmer <flaimo@gmail.com>
* @category flaimo-php
* @copyright Copyright © 2002-2008, Michael Wimmer
* @license GNU General Public License v3
* @link http://code.google.com/p/flaimo-php/
* @package RSS
* @version 2.2.1
*/
abstract class RSSBase {

	protected $allowed_datatypes = array('string', 'int', 'boolean',
								   'object', 'float', 'array'
								  );

	function __construct() {
	} // end constructor

	protected function setVar($data = FALSE, $var_name = '', $type = 'string') {
		if (!in_array($type, $this->allowed_datatypes) ||
			$type != 'boolean' && ($data === FALSE ||
			$this->isFilledString($var_name) === FALSE)) {
			return (boolean) FALSE;
		} // end if

		switch ($type) {
			case 'string':
				if ($this->isFilledString($data) === TRUE) {
					$this->$var_name = (string) trim($data);
					return (boolean) TRUE;
				} // end if
			case 'int':
				if (is_numeric($data)) {
					$this->$var_name = (int) $data;
					return (boolean) TRUE;
				} // end if
			case 'boolean':
				if (is_bool($data)) {
					$this->$var_name = (boolean) $data;
					return (boolean) TRUE;
				}  // end if
			case 'object':
				if (is_object($data)) {
					$this->$var_name =& $data;
					return (boolean) TRUE;
				} // end if
			case 'array':
				if (is_array($data)) {
					$this->$var_name = (array) $data;
					return (boolean) TRUE;
				} // end if
		} // end switch
		return (boolean) FALSE;
	} // end function

	protected function getVar($var_name = 'dummy') {
		return (isset($this->$var_name)) ? $this->$var_name: FALSE;
	} // end function

	public static function isFilledString($string = '', $min_length = 0) {
		if ($min_length == 0) {
			return (boolean) (!ctype_space($string)) ? TRUE : FALSE;
		} // end if

		return (boolean) (strlen(trim($var)) > $min_length) ? TRUE : FALSE;
	} // end function

} // end class
?>
