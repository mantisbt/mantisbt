<?php
/**
 * An exception that is triggered due to a Mantis error.
 */
class ServiceException extends MantisException {
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
