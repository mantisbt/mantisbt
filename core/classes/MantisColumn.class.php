<?php
# MantisBT - a php based bugtracking system

# Copyright (C) 2002 - 2009  MantisBT Team - mantisbt-dev@lists.sourceforge.

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
	 * Function to display column data for a given bug row.
	 * @param object Bug object
	 * @param int Column display target
	 */
	abstract function display( $p_bug, $p_columns_target );
}

