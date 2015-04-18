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
 * Base class that implements basic column functionality
 * and integration with MantisBT.
 * @package MantisBT
 * @subpackage classes
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
	 * @param string Sorting order ('ASC' or 'DESC')
	 * @return array Keyed-array with query elements; see developer guide
	 */
	public function sortquery( $p_dir ) {}

	/**
	 * Allow plugin columns to pre-cache data for all issues
	 * that will be shown in a given view.  This is preferable to
	 * the alternative option of querying the database for each
	 * issue as the display() method is called.
	 * @param array Bug objects
	 */
	public function cache( $p_bugs ) {}

	/**
	 * Function to display column data for a given bug row.
	 * @param object Bug object
	 * @param int Column display target
	 */
	abstract public function display( $p_bug, $p_columns_target );
}

