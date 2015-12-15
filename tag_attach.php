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
 * Tag Attach
 *
 * @package MantisBT
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses bug_api.php
 * @uses constant_inc.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses tag_api.php
 */

require_once( 'core.php' );
require_api( 'bug_api.php' );
require_api( 'constant_inc.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'tag_api.php' );

form_security_validate( 'tag_attach' );

$f_bug_id = gpc_get_int( 'bug_id' );
$f_tag_select = gpc_get_int( 'tag_select' );
$f_tag_string = gpc_get_string( 'tag_string' );

$t_result = tag_attach_many( $f_bug_id, $f_tag_string, $f_tag_select );
if ( $t_result !== true ) {
	$t_tags_failed = $t_result;
} else {
	$t_tags_failed = array();
}

if( count( $t_tags_failed ) > 0 ) {
	html_page_top( lang_get( 'tag_attach_long' ) . ' ' . bug_format_summary( $f_bug_id, SUMMARY_CAPTION ) );
	echo '<br/>';
	echo '<div class="failure-msg">';
	print_tagging_errors_table( $t_tags_failed );
	echo '<br/>';
	print_bracket_link( string_get_bug_view_url( $f_bug_id ), sprintf( lang_get( 'proceed' ), $f_bug_id ) );
	echo '</div>';
	html_page_bottom();
} else {
	form_security_purge( 'tag_attach' );
	print_successful_redirect_to_bug( $f_bug_id );
}

