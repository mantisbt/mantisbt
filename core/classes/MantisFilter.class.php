<?php
# MantisBT - a php based bugtracking system

# Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.

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
 * Base class that implements basic filter functionality
 * and integration with MantisBT.
 * @package MantisBT
 * @subpackage classes
 */
abstract class MantisFilter {

	/**
	 * Field name, as used in the form element and processing.
	 */
	public $field = null;

	/**
	 * Filter title, as displayed to the user.
	 */
	public $title = null;

	/**
	 * Filter type, as defined in core/constant_inc.php
	 */
	public $type = null;

	/**
	 * Default filter value, used for non-list filter types.
	 */
	public $default = null;

	/**
	 * Form element size, used for non-boolean filter types.
	 */
	public $size = null;

	/**
	 * Validate the filter input, returning true if input is
	 * valid, or returning false if invalid.  Invalid inputs will
	 * be replaced with the filter's default value.
	 * @param multi Filter field input
	 * @return boolean Input valid (true) or invalid (false)
	 */
	public function validate( $p_filter_input ) {
		return true;
	}

	/**
	 * Build the SQL query elements 'join', 'where', and 'params'
	 * as used by core/filter_api.php to create the filter query.
	 * @param multi Filter field input
	 * @return array Keyed-array with query elements; see developer guide
	 */
	abstract function query( $p_filter_input );

	/**
	 * Display the current value of the filter field.
	 * @param multi Filter field input
	 * @return string Current value output
	 */
	abstract function display( $p_filter_value );

	/**
	 * For list type filters, define a keyed-array of possible
	 * filter options, not including an 'any' value.
	 * @return array Filter options keyed by value=>display
	 */
	public function options() {}
}

