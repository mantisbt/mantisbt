<?php
# MantisBT - a php based bugtracking system

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
 * @package CoreAPI
 * @subpackage IconAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * gets the status icon
 * @param string $p_icon
 * @return string html img tag containing status icon
 * @access public
 */
function icon_get_status_icon( $p_icon ) {
	$t_icon_path = config_get( 'icon_path' );
	$t_status_icon_arr = config_get( 'status_icon_arr' );
	$t_priotext = get_enum_element( 'priority', $p_icon );
	if( isset( $t_status_icon_arr[$p_icon] ) && !is_blank( $t_status_icon_arr[$p_icon] ) ) {
		return "<img src=\"$t_icon_path$t_status_icon_arr[$p_icon]\" alt=\"\" title=\"$t_priotext\" />";
	} else {
		return "&#160;";
	}
}

/**
 * prints the status icon
 * @param string $p_icon
 * @return null
 * @access public
 */
function print_status_icon( $p_icon ) {
	echo icon_get_status_icon( $p_icon );
}

/**
 * The input $p_dir is either ASC or DESC
 * The inputs $p_sort_by and $p_field are compared to see if they match
 * If the fields match then the sort icon is printed
 * This is a convenience feature to push the comparison code into this
 * function instead of in the page(s)
 * $p_field is a constant and $p_sort_by is whatever the page happens to
 * be sorting by at the moment
 * Multiple sort keys are not supported
 * @param int $p_dir
 * @param string $p_sort_by
 * @param string $p_field
 * @return null
 * @access public
 */
function print_sort_icon( $p_dir, $p_sort_by, $p_field ) {
	$t_icon_path = config_get( 'icon_path' );
	$t_sort_icon_arr = config_get( 'sort_icon_arr' );

	if( $p_sort_by != $p_field ) {
		return;
	}

	if(( 'DESC' == $p_dir ) || ( DESCENDING == $p_dir ) ) {
		$t_dir = DESCENDING;
	} else {
		$t_dir = ASCENDING;
	}

	$t_none = NONE;
	if( !is_blank( $t_sort_icon_arr[$t_dir] ) ) {
		echo "<img src=\"$t_icon_path$t_sort_icon_arr[$t_dir]\" alt=\"\" />";
	} else {
		echo "<img src=\"$t_icon_path$t_status_icon_arr[$t_none]\" alt=\"\" />";
	}
}

