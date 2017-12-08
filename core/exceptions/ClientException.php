<?php
namespace Mantis\Exceptions;

/**
 * An exception that is triggered where the error is caused by
 * client input.
 */
class ClientException extends MantisException {
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
		parent::__construct( $p_message, $p_code, $p_params, $p_previous );
	}
}
