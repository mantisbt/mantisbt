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
 * Handle configuration required for javascript
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses config_api.php
 */

# Prevent output of HTML in the content if errors occur
define( 'DISABLE_INLINE_ERROR_REPORTING', true );

require_once( 'core.php' );
require_api( 'config_api.php' );

/**
 * Print array of configuration option->values for javascript.
 * @param string $p_config_key Configuration option.
 * @return void
 */
function print_config_value( $p_config_key ) {
	echo "config['" . $p_config_key . "'] = '" . addslashes( config_get( $p_config_key ) ) . "';\n";
}

# Send correct MIME Content-Type header for JavaScript content.
# See http://www.rfc-editor.org/rfc/rfc4329.txt for details on why application/javascript is the correct MIME type.
header( 'Content-Type: application/javascript; charset=UTF-8' );


# Don't let Internet Explorer second-guess our content-type, as per
# http://blogs.msdn.com/b/ie/archive/2008/07/02/ie8-security-part-v-comprehensive-protection.aspx
header( 'X-Content-Type-Options: nosniff' );

# rewrite headers to allow caching
if( gpc_isset( 'cache_key' ) ) {
	http_caching_headers( true );
}

# WARNING: DO NOT EXPOSE SENSITIVE CONFIGURATION VALUES!
#
# All configuration values below are publicly available to visitors of the bug
# tracker regardless of whether they're authenticated. Server paths should not
# be exposed. It is OK to expose paths that the user sees directly (short
# paths) but you do need to be careful in your selections. Consider servers
# using URL rewriting engines to mask/convert user-visible paths to paths that
# should only be known internally to the server.

echo "var config = new Array();\n";
print_config_value( 'datetime_picker_format' );
print_config_value( 'short_path' );
