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
 * This file contains configuration checks for display/html issues
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses check_api.php
 * @uses config_api.php
 */

if( !defined( 'CHECK_DISPLAY_INC_ALLOW' ) ) {
	return;
}

# MantisBT Check API
require_once( 'check_api.php' );
require_api( 'config_api.php' );
require_api( 'graphviz_api.php' );

check_print_section_header_row( 'Display' );

# Test length of OpenSearch ShortNames
$t_prefix = config_get( 'search_title' );
$t_shortname_text = sprintf( lang_get( 'opensearch_text_short' ), $t_prefix );
$t_shortname_id = sprintf( lang_get( 'opensearch_id_short' ), $t_prefix );
$t_shortname_length = max( strlen( $t_shortname_text ), strlen( $t_shortname_id ) );

check_print_test_warn_row(
	"Browser Search engine names must be 16 chars or less",
	$t_shortname_length <= 16,
	array( false => 'Either shorten the "search_title" configuration option to '
		. 'a maximum  of ' . ( 16 - $t_shortname_length + strlen( $t_prefix ) )
		. ' characters, or alter the "opensearch_XXX_short" language strings '
		. 'as appropriate to meet the '
		. '<a href="https://github.com/dewitt/opensearch/blob/master/opensearch-1-1-draft-6.md">'
		. 'OpenSearch 1.1</a> specification for the ShortName element.'
	)
);

check_print_test_row(
	'bug_link_tag is not blank/null',
	config_get_global( 'bug_link_tag' ),
	array( false => 'The value of the bug_link_tag option cannot be blank/null.' )
);

check_print_test_row(
	'bugnote_link_tag is not blank/null',
	config_get_global( 'bugnote_link_tag' ),
	array( false => 'The value of the bugnote_link_tag option cannot be blank/null.' )
);

# Graphviz library
if( config_get( 'relationship_graph_enable' ) ) {
	# graphviz_path validity is checked in check_paths_inc.php
	$t_graphviz_path = Graph::graphviz_path();

	# Get list of Graphviz tools from the Graph class constants
	$t_reflect_graph = new ReflectionClass( Graph::class );
	$t_tools = array_filter( $t_reflect_graph->getConstants(),
		function( $p_key ) {
			return strpos( $p_key, 'TOOL_' ) === 0;
		},
		ARRAY_FILTER_USE_KEY
	);

	# Check each tool's availability
	$t_extension = is_windows_server() ? '.exe' : '';
	$t_unavailable = [];
	foreach( $t_tools as $t_tool ) {
		$t_tool_path = $t_graphviz_path . $t_tool . $t_extension;
		if( !is_executable( $t_tool_path ) ) {
			$t_unavailable[] = $t_tool;
		}
	}
	check_print_test_row(
		"Graphviz tools (" .implode( ', ', $t_tools ) . ") are required to display relationship graphs",
		empty( $t_unavailable ),
		[ false => implode( ', ', $t_unavailable )
			. " not found in $t_graphviz_path or not executable. "
		]
	);
}
