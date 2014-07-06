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
	 * Function to display column data for a given bug row.
	 * @param BugData $p_bug            A BugData object.
	 * @param integer $p_columns_target Column display target.
	 * @return void
	 */
	abstract public function display( BugData $p_bug, $p_columns_target );
}

