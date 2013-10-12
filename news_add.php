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
 * Add News
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses news_api.php
 * @uses print_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'news_api.php' );
require_api( 'print_api.php' );

news_ensure_enabled();

form_security_validate( 'news_add' );

access_ensure_project_level( config_get( 'manage_news_threshold' ) );

$f_view_state	= gpc_get_int( 'view_state' );
$f_headline		= gpc_get_string( 'headline' );
$f_announcement	= gpc_get_bool( 'announcement' );
$f_body			= gpc_get_string( 'body' );

$t_news_id = news_create( helper_get_current_project(), auth_get_current_user_id(), $f_view_state, $f_announcement, $f_headline, $f_body );

form_security_purge( 'news_add' );

$t_news_row = news_get_row( $t_news_id );

html_page_top();

html_operation_successful( 'news_menu_page.php' );

print_news_entry_from_row( $t_news_row );

html_page_bottom();
