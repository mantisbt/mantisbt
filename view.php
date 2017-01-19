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
 * View Bug
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses gpc_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'gpc_api.php' );

$t_file = __FILE__;
$t_mantis_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
$t_show_page_header = true;
$t_force_readonly = false;
$t_fields_config_option = 'bug_view_page_fields';
$f_bug_id = gpc_get_int( 'id' );
if( (ON === config_get_global( 'public_urls')) ) {
	$f_bug_token = gpc_get_string( 'token', '' );
	$f_full_view = false;

	if( '' == $f_bug_token ) {
		$f_full_view = true;
	} else if ( auth_is_user_authenticated() ) {
		if ( access_has_bug_level( config_get( 'view_bug_threshold' ), $f_bug_id ) ) {
			$f_full_view = true;
		} 
	} 
} else {
	$f_bug_token = '';
	$f_full_view = true;
}	

if( $f_full_view ) {
	define( 'BUG_VIEW_INC_ALLOW', true );
	include( dirname( __FILE__ ) . '/bug_view_inc.php' );
} else {
	define( 'BUG_VIEW_LIMITED_INC_ALLOW', true );
	include( dirname( __FILE__ ) . '/bug_view_limited_inc.php' );
}  
