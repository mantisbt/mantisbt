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
 * XMLHttpRequest API
 *
 * @package CoreAPI
 * @subpackage XMLHttpRequestAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses bug_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses gpc_api.php
 * @uses print_api.php
 * @uses profile_api.php
 */

require_api( 'access_api.php' );
require_api( 'bug_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'gpc_api.php' );
require_api( 'print_api.php' );
require_api( 'profile_api.php' );

/**
 * Filter a set of strings by finding strings that start with a case-insensitive prefix.
 * @param array  $p_set    An array of strings to search through.
 * @param string $p_prefix The prefix to filter by.
 * @return array An array of strings which match the supplied prefix.
 */
function xmlhttprequest_filter_by_prefix( array $p_set, $p_prefix ) {
	$t_matches = array();
	foreach ( $p_set as $p_item ) {
		if( utf8_strtolower( utf8_substr( $p_item, 0, utf8_strlen( $p_prefix ) ) ) === utf8_strtolower( $p_prefix ) ) {
			$t_matches[] = $p_item;
		}
	}
	return $t_matches;
}

/**
 * Outputs a serialized list of platforms starting with the prefix specified in the $_POST
 * @return void
 * @access public
 */
function xmlhttprequest_platform_get_with_prefix() {
	$f_platform = gpc_get_string( 'platform' );

	$t_unique_entries = profile_get_field_all_for_user( 'platform' );
	$t_matching_entries = xmlhttprequest_filter_by_prefix( $t_unique_entries, $f_platform );

	echo json_encode( $t_matching_entries );
}

/**
 * Outputs a serialized list of Operating Systems starting with the prefix specified in the $_POST
 * @return void
 * @access public
 */
function xmlhttprequest_os_get_with_prefix() {
	$f_os = gpc_get_string( 'os' );

	$t_unique_entries = profile_get_field_all_for_user( 'os' );
	$t_matching_entries = xmlhttprequest_filter_by_prefix( $t_unique_entries, $f_os );

	echo json_encode( $t_matching_entries );
}

/**
 * Outputs a serialized list of Operating System Versions starting with the prefix specified in the $_POST
 * @return void
 * @access public
 */
function xmlhttprequest_os_build_get_with_prefix() {
	$f_os_build = gpc_get_string( 'os_build' );

	$t_unique_entries = profile_get_field_all_for_user( 'os_build' );
	$t_matching_entries = xmlhttprequest_filter_by_prefix( $t_unique_entries, $f_os_build );

	echo json_encode( $t_matching_entries );
}

/**
 * Outputs a serialized list of tags starting with the prefix specified in the $_POST
 * @return void
 * @access public
 */
function xmlhttprequest_tag_get_with_prefix() {
	$f_prefix = gpc_get_string( 'tag' );
	$f_bug_id =  0;
	$results = [];

	$t_unique_entries = tag_get_candidates_for_bug( $f_bug_id );
	if( $t_unique_entries ) {
		foreach ($t_unique_entries as $entry) {
			$t_name = $entry['name'];
			if( utf8_strtolower( utf8_substr( $t_name, 0, utf8_strlen( $f_prefix ) ) ) === utf8_strtolower( $f_prefix ) ) {
				$results[] = $t_name;
			}
		}
	}
	echo json_encode( $results );
}

/**
 * Attach given tag string to an issue as specified in the $_POST
 * @return void
 * @access public
 */
function xmlhttprequest_tag_attach_to_issue() {
	$f_tag_string = gpc_get_string( 'tag_string' );
	$f_bug_id =  gpc_get_string( 'bug_id' );

	form_security_validate( 'tag_attach' );
	$t_user_id = auth_get_current_user_id();
	access_ensure_bug_level( config_get( 'tag_attach_threshold' ), $f_bug_id, $t_user_id );

	$t_tag_row = tag_get_by_name( $f_tag_string );
	if( !$t_tag_row ) {
		if( access_has_global_level( config_get( 'tag_create_threshold' ) ) ) {
			$t_tag_id = tag_create( $f_tag_string, $t_user_id );
		} else {
			# access denied
			echo json_encode( lang_get( 'tag_attach_failed' ) );
			return;
		}
	} else {
		$t_tag_id = $t_tag_row['id'];
	}

	# attach tag
	if( !tag_bug_is_attached( $t_tag_id, $f_bug_id ) ) {
		tag_bug_attach( $t_tag_id, $f_bug_id, $t_user_id );

		event_signal( 'EVENT_TAG_ATTACHED', array( $f_bug_id, array( $t_tag_id ) ) );
	}

	echo json_encode( true );
}

/**
 * Detach given tag string from an issue as specified in the $_POST
 * @return void
 * @access public
 */
function xmlhttprequest_tag_detach_from_issue() {
	$f_tag_string = gpc_get_string( 'tag_string' );
	$f_bug_id =  gpc_get_string( 'bug_id' );

	form_security_validate( 'tag_detach' );
	$t_tag_row = tag_get_by_name( $f_tag_string );
	if( $t_tag_row ) {
		$t_tag_id = $t_tag_row['id'];
		tag_bug_detach( $t_tag_id, $f_bug_id );
	}

	event_signal( 'EVENT_TAG_DETACHED', array( $f_bug_id, array( $f_tag_id ) ) );

	echo json_encode( true );
}