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
 * Mantis Column Handling
 * @copyright Copyright 2009  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package MantisBT
 * @subpackage classes
 */

/**
 * Base class that implements basic column functionality
 * and integration with MantisBT.
 */
abstract class MantisColumn {

	/**
	 * Column title, as displayed to the user.
	 */
	public $title = null;

	/**
	 * Column name, as selected in the manage columns interfaces.
	 */
	public $column = null;

	/**
	 * Column is sortable by the user.  Setting this to true implies that
	 * the column will properly implement the sortquery() method.
	 */
	public $sortable = false;

	/**
	 * Build the SQL query elements 'join' and 'order' as used by
	 * core/filter_api.php to create the filter sorting query.
	 * @param string $p_direction Sorting order ('ASC' or 'DESC').
	 * @return array Keyed-array with query elements; see developer guide
	 */
	public function sortquery( $p_direction ) {
		return array();
	}

	/**
	 * Allow plugin columns to pre-cache data for all issues
	 * that will be shown in a given view.  This is preferable to
	 * the alternative option of querying the database for each
	 * issue as the display() method is called.
	 * @param array $p_bugs Bug objects.
	 * @return void
	 */
	public function cache( array $p_bugs ) {}

	/**
	 * Function to clear the cache of values that was built with the cache() method.
	 * This can be requested as part of an export of bugs, and clearing the used
	 * memory helps to keep a long export process within memory limits.
	 * @return void
	 */
	public function clear_cache() {}

	/**
	 * Function to display column data for a given bug row.
	 * @param BugData $p_bug            A BugData object.
	 * @param integer $p_columns_target Column display target.
	 * @return void
	 */
	abstract public function display( BugData $p_bug, $p_columns_target );

	/**
	 * Function to return column value for a given bug row.  This should be overridden
	 * to provide value without processing for html display or escaping for a specific target
	 * output.  Default implementation is to capture display output for backward compatibility
	 * with target COLUMNS_TARGET_CSV_PAGE.  The output will be escaped by calling code to the
	 * appropriate format.
	 *
	 * @param BugData $p_bug            A BugData object.
	 * @param integer $p_columns_target Column display target.
	 * @return string The column value.
	 */
	public function value( BugData $p_bug, $p_columns_target ) {
		ob_start();
		$this->display( $p_bug, COLUMNS_TARGET_CSV_PAGE );
		return ob_get_clean();
	}
}

