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
 * Generate Common CSS that requires PHP Output, and therefore can not be included in the .css files
 * For example, language specific settings
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses config_api.php
 */

# Prevent output of HTML in the content if errors occur
define( 'DISABLE_INLINE_ERROR_REPORTING', true );

@require_once( dirname( dirname( __FILE__ ) ) . '/core.php' );
require_api( 'lang_api.php' );
require_api( 'config_api.php' );

/**
 * Send correct MIME Content-Type header for css content.
 */
header( 'Content-Type: text/css; charset=UTF-8' );

/**
 * Don't let Internet Explorer second-guess our content-type, as per
 * http://blogs.msdn.com/b/ie/archive/2008/07/02/ie8-security-part-v-comprehensive-protection.aspx
 */
header( 'X-Content-Type-Options: nosniff' );
$g_display_errors = null;

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
?>
div.form-container fieldset.required:after {
	position: absolute;
	margin: -1.75em 0em 0em .5em;
	font-size: 8pt;
	content: '* <?php echo lang_get( 'required' ); ?>';
	color: red;
}
