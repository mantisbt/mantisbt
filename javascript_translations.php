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
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses lang_api.php
 */

/**
 * MantisBT Core API's
 */
require_once( 'core.php' );
require_api( 'lang_api.php' );

function print_translation( $p_lang_key ) {
	echo "translations['" . $p_lang_key . "'] = '" . addslashes( lang_get( $p_lang_key ) ) . "';\n";
}

/**
 * Send correct MIME Content-Type header for JavaScript content.
 * See http://www.rfc-editor.org/rfc/rfc4329.txt for details on why
 * application/javasscript is the correct MIME type.
 */
header( 'Content-Type: application/javascript; charset=UTF-8' );

/**
 * Disallow Internet Explorer from attempting to second guess the Content-Type
 * header as per http://blogs.msdn.com/ie/archive/2008/07/02/ie8-security-part-v-comprehensive-protection.aspx
 */
header( 'X-Content-Type-Options: nosniff' );

echo "var translations = new Array();\n";
print_translation( 'time_tracking_stopwatch_start' );
print_translation( 'time_tracking_stopwatch_stop' );
print_translation( 'loading' );
