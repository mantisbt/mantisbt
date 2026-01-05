<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

namespace Mantis\Exceptions;

use ErrorException;

/**
 * Custom Exception Type to handle PHP errors.
 *
 * This is not meant to be thrown; it is used by {@see error_handler()}
 * to capture required detailed information about the error and pass it on to
 * {@see error_output()}.
 *
 * @internal
 */
class ErrorHandlerException extends ErrorException
{

	/**
	 * MantisBT Error type for display.
	 *
	 * Provides a mapping from PHP's E_* constants to an (English) description
	 * of the error, used as a title/prefix by {@see error_output()}.
	 *
	 * All error severities processed by {@see error_handler()} are covered,
	 * except E_USER_ERROR which is handled by MantisException.
	 *
	 * @return string
	 */
	public function getErrorType() {
		switch( $this->severity ) {
			case E_WARNING:
				return 'SYSTEM WARNING';
			case E_NOTICE:
				return 'SYSTEM NOTICE';
			case E_RECOVERABLE_ERROR:
				# This should generally be considered fatal (like E_ERROR)
				return 'SYSTEM ERROR';
			case E_DEPRECATED:
				return 'DEPRECATED';
			case E_USER_ERROR:
				return 'APPLICATION ERROR #' . $this->code;
			case E_USER_WARNING:
				return 'APPLICATION WARNING #' . $this->code;
			case E_USER_NOTICE:
				# used for debugging
				return 'DEBUG';
			case E_USER_DEPRECATED:
				return 'WARNING';
			default:
				return sprintf( 'UNHANDLED ERROR TYPE (%s)',
					php_sapi_name() == 'cli'
						? $this->severity
						: '<a href="https://www.php.net/errorfunc.constants">' . $this->severity . '</a>'
				);
		}
	}

	/**
	 * Set the Exception's Error code.
	 *
	 * @param int $p_code
	 *
	 * @return void
	 */
	public function setCode( int $p_code ) {
		$this->code = $p_code;
	}

	/**
	 * Set the Exception's message.
	 *
	 * @param string $p_message
	 *
	 * @return void
	 */
	public function setMessage( string $p_message ) {
		$this->message = $p_message;
	}

}
