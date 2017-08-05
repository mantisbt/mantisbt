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
 * Project Page
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses config_api.php
 * @uses constant_inc.php
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
require_api( 'access_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'string_api.php' );
require_api( 'utility_api.php' );

$f_project_id	= gpc_get_int( 'project_id' );

$t_view_issues_url = 'set_project.php?project_id=' . $f_project_id . '&ref=view_all_bug_page.php';

if( $f_project_id == ALL_PROJECTS ) {
	print_header_redirect( $t_view_issues_url );
	exit;
}

# Override the current page to make sure we get the appropriate project-specific configuration
$g_project_override = $f_project_id;

layout_page_header( project_get_field( $f_project_id, 'name' ) );

layout_page_begin();

echo '<h1>', string_display( project_get_field( $f_project_id, 'name' ) ), '</h1>';

echo '<p>';

# View Issues
print_link_button( $t_view_issues_url, lang_get( 'view_bugs_link' ) );

# Changelog
print_link_button( 'changelog_page.php?project_id=' . $f_project_id, lang_get( 'changelog_link' ) );

# Roadmap
print_link_button( 'roadmap_page.php?project_id=' . $f_project_id, lang_get( 'roadmap_link' ) );

# Documentation
if( config_get( 'enable_project_documentation' ) == ON ) {
	print_link_button( 'proj_doc_page.php?project_id=' . $f_project_id, lang_get( 'docs_link' ) );
}

# Wiki
if( config_get( 'wiki_enable' ) == ON ) {
	print_link_button( 'wiki.php?type=project&id=' . $f_project_id, lang_get( 'wiki' ) );
}

# Summary Page for Project
if( access_has_project_level( config_get( 'view_summary_threshold' ), $f_project_id ) ) {
	print_link_button( 'summary_page.php?project_id=' . $f_project_id, lang_get( 'summary_link' ) );
}

# Manage Project Page
if( access_has_project_level( config_get( 'manage_project_threshold' ), $f_project_id ) ) {
	print_link_button( 'manage_proj_edit_page.php?project_id=' . $f_project_id, lang_get( 'manage_link' ) );
}

echo '</p>';

# @todo Add status, view state, versions, sub-projects, parent projects, and news.
# @todo Schema change: add home page, license,

$t_description = project_get_field( $f_project_id, 'description' );

if( !is_blank( $t_description ) ) {
	echo '<h2>', lang_get( 'description' ), '</h2>';
	echo '<p>', string_display( $t_description ), '</p>';
}

$t_access_level_for_dev_team = config_get( 'development_team_threshold' );

$t_users = project_get_all_user_rows( $f_project_id, $t_access_level_for_dev_team );
$t_show_real_names = config_get( 'show_realname' ) == ON;

if( count( $t_users ) > 0 ) {
	echo '<h2>', lang_get( 'development_team' ), '</h2>';

	# @todo sort users in DESC order by access level, then ASC by username/realname.
	foreach ( $t_users as $t_user_data ) {
		$t_user_id = $t_user_data['id'];

		if( $t_show_real_names && !is_blank( $t_user_data['realname'] ) ) {
			$t_user_name = $t_user_data['realname'];
		} else {
			$t_user_name = $t_user_data['username'];
		}

		echo $t_user_name, ' (', get_enum_element( 'access_levels', $t_user_data['access_level'] ), ')<br />';
	}
}

layout_page_end();
