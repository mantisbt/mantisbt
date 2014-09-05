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
 * Updates printing prefs then redirect to print_all_bug_page_page.php
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses error_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 */

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'constant_inc.php' );
require_api( 'database_api.php' );
require_api( 'error_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );

define( 'PRINT_ALL_BUG_OPTIONS_INC_ALLOW', true );
include( dirname( __FILE__ ) . '/print_all_bug_options_inc.php' );

form_security_validate( 'print_all_bug_options_update' );

auth_ensure_user_authenticated();

$f_user_id		= gpc_get_int( 'user_id' );
$f_redirect_url	= gpc_get_string( 'redirect_url' );

# the check for the protected state is already done in the form, there is
# no need to duplicate it here.

# get the fields list
$t_field_name_arr = get_field_names();
$t_field_name_count = count( $t_field_name_arr );

# check the checkboxes
for( $i=0; $i <$t_field_name_count; $i++ ) {
	$t_name = 'print_' . utf8_strtolower( str_replace( ' ', '_', $t_field_name_arr[$i] ) );
	$t_flag = gpc_get( $t_name, null );

	if( $t_flag === null ) {
		$t_prefs_arr[$i] = 0;
	} else {
		$t_prefs_arr[$i] = 1;
	}
}

# get user id
$t_user_id = $f_user_id;

$c_export = implode( '', $t_prefs_arr );

# update preferences
$t_query = 'UPDATE {user_print_pref} SET print_pref=' . db_param() . ' WHERE user_id=' . db_param();

$t_result = db_query( $t_query, array( $c_export, $t_user_id ) );

form_security_purge( 'print_all_bug_options_update' );

html_page_top( null, $f_redirect_url );

if( $t_result ) {
	html_operation_successful( $f_redirect_url );
} else {
	echo '<div class="failure-msg">';
	print error_string( ERROR_GENERIC ) . '<br />';
	print_bracket_link( $f_redirect_url, lang_get( 'proceed' ) );
	echo '</div>';
}

html_page_bottom();
