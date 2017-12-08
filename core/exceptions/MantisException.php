<?php
namespace Mantis\Exceptions;

class MantisException extends \Exception {
	protected $params;

	function __construct( $p_message, $p_code, $p_params = array(), Throwable $p_previous = null ) {
		parent::__construct( $p_message, $p_code, $p_previous );
		$this->params = $p_params;
	}

	function getParams() {
		return $this->params;
	}
}