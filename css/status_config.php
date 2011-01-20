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
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2010  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses config_api.php
 */

/**
 * MantisBT Core API's
 */
@require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core.php' );
require_api( 'config_api.php' );

/**
 * Send correct MIME Content-Type header for css content.
 */
header( 'Content-Type: text/css; charset=UTF-8' );

/**
 * Disallow Internet Explorer from attempting to second guess the Content-Type
 * header as per http://blogs.msdn.com/ie/archive/2008/07/02/ie8-security-part-v-comprehensive-protection.aspx
 */
header( 'X-Content-Type-Options: nosniff' );

/**
 * WARNING: DO NOT EXPOSE SENSITIVE CONFIGURATION VALUES!
 *
 * All configuration values below are publicly available to visitors of the bug
 * tracker regardless of whether they're authenticated. Server paths should not
 * be exposed. It is OK to expose paths that the user sees directly (short
 * paths) but you do need to be careful in your selections. Consider servers
 * using URL rewriting engines to mask/convert user-visible paths to paths that
 * should only be known internally to the server.
 */

/**
 *	@todo Modify to run sections only on certain pages.
 *	eg. status colors are only necessary on a few pages.(my view, view all bugs, bug view, etc. )
 *	other pages my need to include dynamic css styles as well
 */
$t_status_string = config_get( 'status_enum_string' );
$t_statuses = MantisEnum::getAssocArrayIndexedByValues( $t_status_string );
$t_colors = config_get( 'status_colors' );
$t_color_count = count( $t_colors );
$t_color_width = ( $t_color_count > 0 ? ( round( 100/$t_color_count ) ) : 0 );
$t_status_percents = get_percentage_by_status();
foreach( $t_statuses AS $t_id=>$t_label ) {
	if( array_key_exists( $t_label, $t_colors ) ) { 
		echo ".$t_label-color { background-color: {$t_colors[$t_label]}; width: $t_color_width%; }\n";
	}
	if( array_key_exists( $t_id, $t_status_percents ) ) {
		echo ".$t_label-percentage { width: {$t_status_percents[$t_id]}%; }\n";
	}
}
