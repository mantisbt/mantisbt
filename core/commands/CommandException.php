<?php

class CommandException extends Exception
{
	function __construct( $p_http_status_code, $p_http_status_message, $p_error_code, $p_params = array(), Throwable $previous = null)
	{
		parent::__construct( $p_http_status_message, $p_http_status_code, $previous);

		$this->error_code = $p_error_code;

		# TODO: construct error message similar to trigger errors.
		$this->error_message = 'error messag for ' . $p_error_code;
	}

	/**
	 * Error code used to fetch localized error message and used for application logic to know what happened if needed.
	 *
	 * @var integer The error code as defined in constants and lang files.
	 */
	public $error_code;

	/**
	 * Localized error messaged displayed in UI.
	 *
	 * @var string The localized error message.
	 */
	public $error_message;

	function getHttpErrorCode() {
		return parent::getCode();
	}

	function getHttpErrorMessage() {
		return parent::getMessage();
	}

	function getMantisErrorCode() {
		return $this->error_code;
	}

	function getUIErrorMessage() {
		return $this->error_message;
	}
}
