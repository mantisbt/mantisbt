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
 * Icon API
 *
 * @package CoreAPI
 * @subpackage IconAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses helper_api.php
 * @uses utility_api.php
 */

require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'helper_api.php' );
require_api( 'utility_api.php' );

/**
 * Returns HTML to display an icon with optional title pop-up.
 *
 * @param string $p_icon       Fontawesome icon name (fa-xxx)
 * @param string $p_classes    Optional additional CSS classes
 * @param string $p_title      Optional pop-up text
 * @param string $p_inner_html Optional html to insert with the icon.
 *
 * @return string
 */
function icon_get( $p_icon, $p_classes = '', $p_title = '', $p_inner_html = '' ) {
	# Add 'fa-' prefix if missing
	if( substr( $p_icon, 0, 3 ) != 'fa-' ) {
		$p_icon = 'fa-' . $p_icon;
	}

	if( $p_title ) {
		$p_title = 'title="' . $p_title . '"';
	}

	/** @noinspection HtmlUnknownAttribute */
	return sprintf( '<i class="fa %s %s" %s>%s</i>', $p_icon, $p_classes, $p_title, $p_inner_html );
}

/**
 * Prints an icon with optional title pop-up.
 *
 * @param string $p_icon    Fontawesome icon name (fa-xxx)
 * @param string $p_classes Optional additional CSS classes
 * @param string $p_title   Optional pop-up text
 * @param string $p_inner_html Optional html to insert with the icon.
 */
function print_icon( $p_icon, $p_classes = '', $p_title = '', $p_inner_html = '' ) {
	echo icon_get( $p_icon, $p_classes, $p_title, $p_inner_html );
}

/**
 * gets the status icon
 * @param string $p_icon Icon file name.
 * @return string html img tag containing status icon
 * @access public
 */
function icon_get_status_icon( $p_icon ) {
	$t_status_icon_arr = config_get( 'status_icon_arr' );
	$t_priotext = get_enum_element( 'priority', $p_icon );
	if( isset( $t_status_icon_arr[$p_icon] ) && !is_blank( $t_status_icon_arr[$p_icon] ) ) {
		return icon_get( $t_status_icon_arr[$p_icon], '', $t_priotext );
	} else {
		return '&#160;';
	}
}

/**
 * prints the status icon
 * @param string $p_icon Icon file name.
 * @return void
 * @access public
 */
function print_status_icon( $p_icon ) {
	echo icon_get_status_icon( $p_icon );
}

/**
 * The input $p_dir is either ASC or DESC
 * The inputs $p_sort_by and $p_field are compared to see if they match
 * If the fields match then the sort icon is printed
 * This is a convenience feature to push the comparison code into this function instead of in the
 * page(s)
 * $p_field is a constant and $p_sort_by is whatever the page happens to be sorting by at the moment
 * Multiple sort keys are not supported
 * @param integer $p_dir     Direction to sort by ( either ASC or DESC ).
 * @param string  $p_sort_by Field.
 * @param string  $p_field   Field to sort by.
 * @return void
 * @access public
 */
function print_sort_icon( $p_dir, $p_sort_by, $p_field ) {
	$t_sort_icon_arr = config_get( 'sort_icon_arr' );

	if( $p_sort_by != $p_field ) {
		return;
	}

	if( ( 'DESC' == $p_dir ) || ( DESCENDING == $p_dir ) ) {
		$t_dir = DESCENDING;
	} else {
		$t_dir = ASCENDING;
	}

	echo '&#160;';
	if( !is_blank( $t_sort_icon_arr[$t_dir] ) ) {
		echo icon_get( $t_sort_icon_arr[$t_dir], 'fa-lg blue' );
	}
}
