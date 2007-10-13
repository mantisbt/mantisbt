<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

	# --------------------------------------------------------
	# $Id: project_page.php,v 1.3.2.1 2007-10-13 22:34:25 giallu Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path . 'project_api.php' );
	require_once( $t_core_path . 'last_visited_api.php' );
	require_once( $t_core_path . 'print_api.php' );

	$f_project_id	= gpc_get_int( 'project_id' );

	$t_view_issues_url = "set_project.php?project_id=$f_project_id&ref=view_all_bug_page.php";

	if ( $f_project_id == ALL_PROJECTS ) {
		$t_redirect_url = $t_view_issues_url;
		print_header_redirect( $t_redirect_url );
		exit;
	}

	# Override the current page to make sure we get the appropriate project-specific configuration
	$g_project_override = $f_project_id;

	$t_changelog_url = "changelog_page.php?project_id=$f_project_id";
	$t_roadmap_url = "roadmap_page.php?project_id=$f_project_id";

	html_page_top1( project_get_field( $f_project_id, 'name' ) );
	html_page_top2();

	print_recently_visited();

	echo '<h1>', string_display( project_get_field( $f_project_id, 'name' ) ), '</h1>';

	echo '<p>';
	
	# View Issues
	echo '<a href="', $t_view_issues_url, '">', lang_get( 'view_bugs_link' ), '</a>';
	
	# Changelog
	echo ' | <a href="', $t_changelog_url, '">', lang_get( 'changelog_link' ), '</a>';
	
	# Roadmap
	echo ' | <a href="', $t_roadmap_url, '">', lang_get( 'roadmap_link' ), '</a>';
	
	# Documentation
	if ( config_get( 'enable_project_documentation' ) == ON ) {
		echo ' | <a href="proj_doc_page.php?project_id=', $f_project_id, '">', lang_get( 'docs_link' ), '</a>';
	}

	# Wiki
	if ( config_get( 'wiki_enable' ) == ON ) {
		echo ' | <a href="wiki.php?type=project&amp;id=', $f_project_id, '">', lang_get( 'wiki' ), '</a>';
	}

	# Summary Page for Project
	if ( access_has_project_level( config_get( 'view_summary_threshold' ), $f_project_id ) ) {
		echo ' | <a href="summary_page.php?project_id=', $f_project_id, '">', lang_get( 'summary_link' ), '</a>';
	}

	# Manage Project Page
	if ( access_has_project_level( config_get( 'manage_project_threshold' ), $f_project_id ) ) {
		echo ' | <a href="manage_proj_edit_page.php?project_id=', $f_project_id, '">', lang_get( 'manage_link' ), '</a>';
	}

	echo '</p>';
	
	# @@@ Add status, view state, versions, sub-projects, parent projects, and news.
	# @@@ Schema change: add home page, license,

	$t_description = project_get_field( $f_project_id, 'description' );

	if ( !is_blank( $t_description ) ) {
		echo '<h2>', lang_get( 'description' ), '</h2>';
		echo '<p>', string_display( $t_description ), '</p>';
	}

	$t_access_level_for_dev_team = config_get( 'development_team_threshold' );

	$t_users = project_get_all_user_rows( $f_project_id, $t_access_level_for_dev_team );
	$t_show_real_names = config_get( 'show_realname' ) == ON;

	if ( count( $t_users ) > 0 ) {
		echo '<h2>', lang_get( 'development_team' ), '</h2>';

		# @@@ sort users in DESC order by access level, then ASC by username/realname.
		foreach ( $t_users as $t_user_data ) {
			$t_user_id = $t_user_data['id'];
			
			if ( $t_show_real_names && !is_blank( $t_user_data['realname'] ) ) {
				$t_user_name = $t_user_data['realname'];
			} else {
				$t_user_name = $t_user_data['username'];
			}

	 		echo $t_user_name, ' (', get_enum_element( 'access_levels', $t_user_data['access_level'] ), ')<br />';
	 	}
 	}

	html_page_bottom1( __FILE__ );
?>
