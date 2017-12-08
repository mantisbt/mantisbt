<?php
/**
 * An exception that is triggered due to an error in the state of Mantis.
 * For example, the data in the database is inconsistent or invalid
 * configuration.  This should not be trigger for invalid user input,
 * bad requests or code errors.
 */
class StateException extends MantisException {
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
