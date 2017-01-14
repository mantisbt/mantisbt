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
 * Handler to delete a stored query.
 *
 * Takes filter_id as a parameter
 *
 * @package MantisBT
 * @copyright Copyright 2016  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses compress_api.php
 * @uses filter_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 */

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'compress_api.php' );
require_api( 'filter_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );

form_security_validate( 'manage_filter_delete' );

auth_ensure_user_authenticated();

$f_filter_id = gpc_get_int( 'filter_id' );
$t_redirect_url = 'manage_filter_page.php';

if( !filter_db_can_delete_filter( $f_filter_id ) ) {
	print_header_redirect( $t_redirect_url );
	exit;
}

helper_ensure_confirmed( lang_get( 'query_delete_msg' ) . '<br>' . lang_get( 'query_name' ) . ': ' . filter_get_field( $f_filter_id, 'name' ),
		lang_get( 'delete_query' ) );

filter_db_delete_filter( $f_filter_id );

form_security_purge( 'manage_filter_delete' );

$t_redirect_page = 'manage_filter_page.php';
layout_page_header( null, $t_redirect_url );

layout_page_begin();

html_operation_successful( $t_redirect_page );

layout_page_end();
