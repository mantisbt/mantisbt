<?php
# MantisBT - a php based bugtracking system

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


require_once( 'core.php' );

if( current_user_is_anonymous() ) {
	access_denied();
}

$f_redirect_url	= string_sanitize_url( gpc_get_string( 'redirect_url', 'account_filters_page.php' ) );
$f_project_id = gpc_get_int( 'project_id', helper_get_current_project() );

form_security_validate( 'reset_current_filter_form' );
auth_ensure_user_authenticated();

$t_filter_id = filter_db_get_project_current( $f_project_id );
if( $t_filter_id ) {
	filter_db_delete_filter( $t_filter_id );
}

form_security_purge( 'reset_current_filter_form' );

layout_page_header( null, $f_redirect_url );
layout_page_begin();

html_operation_successful( $f_redirect_url );

layout_page_end();
