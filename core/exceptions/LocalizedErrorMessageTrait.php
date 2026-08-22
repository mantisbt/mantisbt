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
 * Error message localization Trait.
 */
trait LocalizedErrorMessageTrait
{
	/**
	 * @var string Localized error message with placeholders filled in.
	 */
	protected string $localized;

	/**
	 * Set the Localized Error message including placeholder replacement.
	 *
	 * @param int        $p_code   MantisBT Error code.
	 * @param array|null $p_params Error parameters.
	 * @return void
	 */
	public function setLocalizedMessage( int $p_code, ?array $p_params ) {
		$this->localized = error_string( $p_code, $p_params );
	}

	/**
	 * Get the Localized Error message.
	 *
	 * If not set, fallback to the Exception message.
	 *
	 * @return string
	 */
	public function getLocalizedMessage() {
		return $this->localized ?? $this->getMessage();
	}

}