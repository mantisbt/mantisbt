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

/**
 * An exception that is triggered from a RestFault or SoapFault which is the legacy
 * way to trigger API errors.  This exception class should be removed once all APIs
 * dependent on exceptions rather than return codes being faults.
 *
 * Don't inherit from MantisException since we don't follow the conventions of having
 * code correspond to Mantis error code, but code here is the http error code.
 */
class LegacyApiFaultException extends \Exception {
    /**
     * Constructor
     *
     * @param string $p_message The internal non-localized error message.
     * @param integer $p_code The Mantis error code.
     * @param Throwable $p_previous The inner exception.
     * @return void
     */
	function __construct( $p_message, $p_code, Throwable $p_previous = null ) {
		parent::__construct( $p_message, $p_code, $p_previous );
	}
}
