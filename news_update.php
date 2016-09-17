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
 * Update News Post
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses config_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses news_api.php
 * @uses print_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'config_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'news_api.php' );
require_api( 'print_api.php' );

news_ensure_enabled();

form_security_validate( 'news_update' );

$f_news_id		= gpc_get_int( 'news_id' );
$f_project_id	= gpc_get_int( 'project_id' );
$f_view_state	= gpc_get_int( 'view_state' );
$f_headline		= gpc_get_string( 'headline' );
$f_announcement	= gpc_get_bool( 'announcement' );
$f_body			= gpc_get_string( 'body', '' );

$t_row = news_get_row( $f_news_id );

# Check both the old project and the new project
access_ensure_project_level( config_get( 'manage_news_threshold' ), $t_row['project_id'] );
access_ensure_project_level( config_get( 'manage_news_threshold' ), $f_project_id );

news_update( $f_news_id, $f_project_id, $f_view_state, $f_announcement, $f_headline, $f_body );

form_security_purge( 'news_update' );

layout_page_header();

layout_page_begin( 'main_page.php' );

echo '<div class="space-20"></div>';

$t_buttons = array(
	array( 'news_menu_page.php' ),
	array( 'news_edit_page.php?news_id=' . $f_news_id . '&action=edit', lang_get( 'edit_link' ) ),
);
html_operation_confirmation( $t_buttons, CONFIRMATION_TYPE_SUCCESS );

echo '<br />';

print_news_entry( $f_headline, $f_body, $t_row['poster_id'], $f_view_state, $f_announcement, $t_row['date_posted'] );

layout_page_end();
