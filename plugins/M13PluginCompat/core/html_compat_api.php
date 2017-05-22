<?php
/**
 * MantisBT - A PHP based bugtracking system
 *
 * MantisBT is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * MantisBT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 */

require_api( 'layout_api.php' );

if ( !function_exists( 'html_page_top' ) ) {

	/**
	 * Compatibility function for v1.3 layout page calls, to allow some level
	 * of functionality by external code that still has not upgraded to mantis v2.
	 * For example: plugins
	 * WARNING: Full compatibility is not guaranteed.
	 * Use only for development and testing purpose.
	 *
	 * @deprecated
	 * @param string $p_page_title   Html page title.
	 * @param string $p_redirect_url URL to redirect to if necessary.
	 */
	function html_page_top( $p_page_title = null, $p_redirect_url = null ) {
		error_parameters( __FUNCTION__, 'layout_page_header' );
		trigger_error( ERROR_DEPRECATED_SUPERSEDED, DEPRECATED );

		layout_page_header( $p_page_title, $p_redirect_url );
		layout_page_begin();
		echo '<div class="m13">';
	}

}

if ( !function_exists( 'html_page_bottom' ) ) {

	/**
	 * Compatibility function for v1.3 layout page calls, to allow some level
	 * of functionality by external code that still has not upgraded to mantis v2.
	 * For example: plugins
	 * WARNING: Full compatibility is not guaranteed.
	 * Use only for development and testing purpose.
	 *
	 * @deprecated
	 * @return void
	 */
	function html_page_bottom() {
		error_parameters( __FUNCTION__, 'layout_page_end' );
		trigger_error( ERROR_DEPRECATED_SUPERSEDED, DEPRECATED );

		echo '</div>';
		layout_page_end();
	}

}