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
 * Set Active Project
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses filter_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses string_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'filter_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'string_api.php' );
require_api( 'utility_api.php' );

$f_project_id	= gpc_get_string( 'project_id' );
$f_make_default	= gpc_get_bool( 'make_default' );
$f_ref			= gpc_get_string( 'ref', '' );

$c_ref = string_prepare_header( $f_ref );

$t_project = explode( ';', $f_project_id );
$t_top     = $t_project[0];
$t_bottom  = $t_project[count( $t_project ) - 1];

if( ALL_PROJECTS != $t_bottom ) {
	project_ensure_exists( $t_bottom );
}

# Set default project
if( $f_make_default ) {
	current_user_set_default_project( $t_top );
}

helper_set_current_project( $f_project_id );

# redirect to 'same page' when switching projects.

# for proxies that clear out HTTP_REFERER
if( !is_blank( $c_ref ) ) {
	$t_redirect_url = $c_ref;
} else if( !isset( $_SERVER['HTTP_REFERER'] ) || is_blank( $_SERVER['HTTP_REFERER'] ) ) {
	$t_redirect_url = config_get_global( 'default_home_page' );
} else {
	$t_home_page = config_get_global( 'default_home_page' );

	# Check that referrer matches our address after squashing case (case insensitive compare)
	$t_path = rtrim( config_get_global( 'path' ), '/' );
	if( preg_match( '@^(' . $t_path . ')/(?:/*([^\?#]*))(.*)?$@', $_SERVER['HTTP_REFERER'], $t_matches ) ) {
		$t_referrer_page = $t_matches[2];
		$t_param = $t_matches[3];

		# if view_all_bug_page, pass on filter
		if( strcasecmp( 'view_all_bug_page.php', $t_referrer_page ) == 0 ) {
			$t_source_filter_id = filter_db_get_project_current( $f_project_id );
			$t_redirect_url = 'view_all_set.php?type=' . FILTER_ACTION_GENERALIZE;

			if( $t_source_filter_id !== null ) {
				$t_redirect_url = 'view_all_set.php?type=' . FILTER_ACTION_LOAD . '&source_query_id=' . $t_source_filter_id;
			}
		} else if( stripos( $t_referrer_page, '_page.php' ) !== false ) {
			switch( $t_referrer_page ) {
				case 'bug_view_page.php':
				case 'bug_view_advanced_page.php':
				case 'bug_update_page.php':
				case 'bug_change_status_page.php':
					$t_path = $t_home_page;
					break;
				default:
					$t_path = $t_referrer_page . $t_param;
					break;
			}
			$t_redirect_url = $t_path;
		} else if( $t_referrer_page == 'plugin.php' ) {
			$t_redirect_url = $t_referrer_page . $t_param; # redirect to same plugin page
		} else {
			$t_redirect_url = $t_home_page;
		}
	} else {
		$t_redirect_url = $t_home_page;
	}
}

print_header_redirect( $t_redirect_url, true, true );

layout_page_header( null, $t_redirect_url );

layout_page_begin();

html_operation_successful( $t_redirect_url );

layout_page_end();
