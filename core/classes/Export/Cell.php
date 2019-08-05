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

/**
 * @package MantisBT
 * @copyright Copyright 2019  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

namespace Mantis\Export;

/**
 * Class that contains attributes for a cell in a spreadsheet export
 */
class Cell {
	/**
	 * Types should be interpreted as directives to the final consumer
	 * whenever its needed to manipulate the actual value.
	 */
	# Numeric values: integer, floats, etc, where the consumer is responsible
	# to apply a numeric formatting and/or format it as a number-aware representation
    const TYPE_NUMERIC = 0;

	# String values: the consumer will represent the vale as is.
    const TYPE_STRING = 1;

	# The value represent a formula
	const TYPE_FORMULA = 2;

	# An empty value
    const TYPE_EMPTY = 3;

	# A value that should be mapped to, and represented as a boolean
    const TYPE_BOOLEAN = 4;

	# A value that should be translated to a human legible date representation.
	# The actual value provided is an integer value for a timestamp, and the consumer
	# is resposnible for applying the correct format.
	const TYPE_DATE = 5;
}