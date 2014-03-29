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
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package MantisBT
 */

 /**
 * Mantis Exception
 * @package MantisBT
 * @subpackage classes
 */
abstract class MantisException extends Exception
{
	/**
	 * Exception message
	 */
    protected $message = 'Unknown exception';

	/**
	 * Unknown
	 */
    private $string;
	
	/**
	 * User-defined exception code
	 */
    protected $code    = 0;

	/**
	 * Source filename of exception
	 */
    protected $file;

	/**
	 * Source line of exception
	 */
    protected $line;

	/**
	 * Unknown
	 */
    private $trace;

	/**
	 * Mantis Context
	 */
	private $context = null;

	/**
	 * Constructor
	 * @param int $p_code code
	 * @param int $p_parameters parameters
	 * @param \Exception $p_previous Previous exception
	 */
    public function __construct($p_code = 0, $p_parameters, \Exception $p_previous = null)
    {
		$message = var_export( $p_parameters, true);
		
		$this->context = $p_parameters;
        parent::__construct($message, $p_code, $p_previous);
    }

	/**
	 * Return exception details as string
	 */
    public function __toString()
    {
        return get_class($this) . " '{$this->message}' in {$this->file}({$this->line})\n"
                                . "{$this->getTraceAsString()}";
    }

	/**
	 * Get Exception Context
	 */
	public function getContext() {
		return $this->context;
	}
}