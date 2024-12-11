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
 * Return Dynamic Filters
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses compress_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses custom_field_api.php
 * @uses error_api.php
 * @uses filter_api.php
 * @uses filter_constants_inc.php
 * @uses filter_form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 */

use Mantis\Exceptions\StateException;

# Prevent output of HTML in the content if errors occur
define( 'DISABLE_INLINE_ERROR_REPORTING', true );

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'compress_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'custom_field_api.php' );
require_api( 'error_api.php' );
require_api( 'filter_api.php' );
require_api( 'filter_constants_inc.php' );
require_api( 'filter_form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );

if( !auth_is_user_authenticated() ) {
	trigger_error( ERROR_ACCESS_DENIED, ERROR );
}

compress_enable();

$f_filter_id = gpc_get( 'filter_id', null );
if( null !== $f_filter_id ) {
	$t_filter = filter_get( $f_filter_id, null );
	if( null === $t_filter ) {
		trigger_error( ERROR_ACCESS_DENIED, ERROR );
	}
} else {
	$t_filter = current_user_get_bug_filter();
}

$f_view_type = gpc_get_string( 'view_type', $t_filter['_view_type'] );
$t_filter['_view_type'] = $f_view_type;
# call to filter_ensure_valid_filter to clean up after adding unsafe values from gpc vars
$t_filter = filter_ensure_valid_filter( $t_filter );

/**
 * Prepend headers to the dynamic filter forms that are sent as the response from this page.
 * @return void
 */
function return_dynamic_filters_prepend_headers() {
	if( !headers_sent() ) {
		header( 'Content-Type: text/html; charset=utf-8' );
	}
}

$f_filter_target = gpc_get_string( 'filter_target' );
$filter_target = mb_substr( $f_filter_target, 0, -7 ); # -7 for '_filter'
$t_found = false;
try {
	$t_content = filter_form_get_input( $t_filter, $filter_target );
} catch( StateException $e ) {
	$t_content = false;
}

if( false !== $t_content ) {
	return_dynamic_filters_prepend_headers();
	$t_found = true;
	echo $t_content;
} else if( 'custom_field' == mb_substr( $f_filter_target, 0, 12 ) ) {
	# Check existence of custom field id, and if the user has access to read and filter by
	$t_custom_id = mb_substr( $f_filter_target, 13, -7 );
	$t_cfdef = custom_field_get_definition( $t_custom_id );
	if( $t_cfdef && access_has_any_project_level( $t_cfdef['access_level_r'] ) && $t_cfdef['filter_by'] ) {
		$t_found = true;
		return_dynamic_filters_prepend_headers();
		print_filter_custom_field( $t_custom_id, $t_filter );
	} else {
		trigger_error( ERROR_ACCESS_DENIED, ERROR );
	}
} else {
	$t_plugin_filters = filter_get_plugin_filters();
	foreach ( $t_plugin_filters as $t_field_name => $t_filter_object ) {
		if( $t_field_name . '_filter' == $f_filter_target ) {
			return_dynamic_filters_prepend_headers();
			print_filter_plugin_field( $t_field_name, $t_filter_object, $t_filter );
			$t_found = true;
			break;
		}
	}
}

if( !$t_found ) {
	# error - no function to populate the target (e.g., print_filter_foo)
	error_parameters( $f_filter_target );
	trigger_error( ERROR_FILTER_NOT_FOUND, ERROR );
}
