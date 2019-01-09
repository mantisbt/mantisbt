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
 * Browser Search Functionality - outputs opensearchdescription xml
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses config_api.php
 * @uses gpc_api.php
 */

# Prevent output of HTML in the content if errors occur
define( 'DISABLE_INLINE_ERROR_REPORTING', true );

require_once( 'core.php' );
require_api( 'config_api.php' );
require_api( 'gpc_api.php' );

$f_type = strtolower( gpc_get_string( 'type', 'text' ) );

$t_path = config_get_global( 'path' );
$t_title = config_get( 'search_title' );
$t_icon = $t_path . config_get( 'favicon_image' );
$t_searchform = $t_path . 'view_all_bug_page.php';

# Localized ShortName and Description elements
$t_shortname = sprintf( lang_get( "opensearch_{$f_type}_short" ), $t_title );
$t_description = sprintf( lang_get( "opensearch_{$f_type}_description" ), $t_title );

if( $f_type == 'id' ) {
	$t_url = $t_path . 'view.php?id={searchTerms}';
} else {
	$t_url = $t_path . 'view_all_set.php?type=' . FILTER_ACTION_PARSE_NEW . '&amp;temporary=y&amp;handler_id=[all]&amp;search={searchTerms}';
}

header( 'Content-Type: application/opensearchdescription+xml' );
echo '<?xml version="1.0" encoding="UTF-8" ?>';
?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/"
                       xmlns:moz="http://www.mozilla.org/2006/browser/search/">
	<ShortName><?php echo $t_shortname; ?></ShortName>
	<Description><?php echo $t_description; ?></Description>
	<InputEncoding>UTF-8</InputEncoding>
	<Image width="16" height="16" type="image/x-icon"><?php echo $t_icon; ?></Image>
	<Url type="text/html" method="GET" template="<?php echo $t_url; ?>"></Url>';
	<moz:SearchForm><?php echo $t_searchform; ?></moz:SearchForm>
</OpenSearchDescription>
