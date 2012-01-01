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
 * @uses core.php
 * @uses authentication_api.php
 * @uses compress_api.php
 * @uses config_api.php
 * @uses current_user_api.php
 * @uses filter_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses user_api.php
 */

/**
 * MantisBT Core API's
 */
require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'compress_api.php' );
require_api( 'config_api.php' );
require_api( 'current_user_api.php' );
require_api( 'filter_api.php' );
require_api( 'gpc_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'user_api.php' );

require_js( 'bugFilter.js' );
require_css( 'status_config.php' );

auth_ensure_user_authenticated();

$f_page_number		= gpc_get_int( 'page_number', 1 );

$t_per_page = null;
$t_bug_count = null;
$t_page_count = null;

$rows = filter_get_bug_rows( $f_page_number, $t_per_page, $t_page_count, $t_bug_count, null, null, null, true );
if ( $rows === false ) {
	print_header_redirect( 'view_all_set.php?type=0' );
}

$t_bugslist = Array();
$t_users_handlers = Array();
$t_project_ids  = Array();
$t_row_count = count( $rows );
for($i=0; $i < $t_row_count; $i++) {
	array_push($t_bugslist, $rows[$i]->id );
	$t_users_handlers[] = $rows[$i]->handler_id;
	$t_project_ids[] = $rows[$i]->project_id;
}
$t_unique_users_handlers = array_unique( $t_users_handlers );
$t_unique_project_ids = array_unique( $t_project_ids );
user_cache_array_rows( $t_unique_users_handlers );
project_cache_array_rows( $t_unique_project_ids );

gpc_set_cookie( config_get( 'bug_list_cookie' ), implode( ',', $t_bugslist ) );

compress_enable();

# don't index view issues pages
html_robots_noindex();

html_page_top1( lang_get( 'view_bugs_link' ) );

if ( current_user_get_pref( 'refresh_delay' ) > 0 ) {
	html_meta_redirect( 'view_all_bug_page.php?page_number='.$f_page_number, current_user_get_pref( 'refresh_delay' )*60 );
}

html_page_top2();

print_recently_visited();

define( 'VIEW_ALL_INC_ALLOW', true );
include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'view_all_inc.php' );

html_page_bottom();
