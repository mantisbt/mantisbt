<?php
namespace Mantis\Exceptions;

/**
 * A base exception for all Mantis exceptions.
 */
class MantisException extends \Exception {
	/**
	 * @var array array of parameters for localized exception message.
	 */
	protected $params;

    /**
     * Constructor
     *
     * @param string $p_message The internal non-localized error message.
     * @param integer $p_code The Mantis error code.
     * @param array $p_params Localized error message parameters.
     * @param Throwable $p_previous The inner exception.
     * @return void
     */
	function __construct( $p_message, $p_code, $p_params = array(), Throwable $p_previous = null ) {
		parent::__construct( $p_message, $p_code, $p_previous );
		$this->params = $p_params;
	}

	function getParams() {
		return $this->params;
	}
}