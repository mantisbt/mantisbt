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
require_api( 'string_api.php' );
require_api( 'utility_api.php' );

form_security_validate( 'tag_attach' );

$f_bug_id = gpc_get_int( 'bug_id' );
$f_tag_select = gpc_get_int( 'tag_select' );
$f_tag_string = gpc_get_string( 'tag_string' );

$t_tags = array();

$t_strings = explode( config_get( 'tag_separator' ), $f_tag_string );
foreach( $t_strings as $t_name ) {
	$t_name = trim( $t_name );
	if( is_blank( $t_name ) ) {
		continue;
	}

	$t_tags[] = array( 'name' => $t_name );
}

if( $f_tag_select > 0 ) {
	$t_tags[] = array( 'id' => tag_get( $f_tag_select ) );
}

$t_data = array(
	'query' => array( 'issue_id' => $f_bug_id ),
	'payload' => array(
		'tags' => $t_tags
	)
);

$t_command = new TagAttachCommand( $t_data );
$t_command->execute();

form_security_purge( 'tag_attach' );
print_successful_redirect_to_bug( $f_bug_id );
