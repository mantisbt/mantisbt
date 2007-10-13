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
	# $Id: wiki_dokuwiki_api.php,v 1.1.2.1 2007-10-13 22:35:49 giallu Exp $
	# --------------------------------------------------------
 
	# ----------------------
	# Gets the URL for the page with the specified page id.  This function is used
	# internally by this API.
	function wiki_dokuwiki_get_url_for_page_id( $p_page_id ) {
		$t_root_url = config_get_global( 'wiki_engine_url' );
 
		$t_root_namespace = config_get( 'wiki_root_namespace' );
 
		if ( is_blank( $t_root_namespace ) ) {
			$t_page_id = $p_page_id;
		} else {
			$t_page_id = $t_root_namespace . ':' . $p_page_id;
		}
 
		return $t_root_url . 'doku.php?id=' . urlencode( $t_page_id );
	}
 
	# ----------------------
	# Gets the page id for the specified issue.  The page id can then be converted
	# to a URL using wiki_dokuwiki_get_url_for_page_id().
	function wiki_dokuwiki_get_page_id_for_issue( $p_issue_id ) {
		$c_issue_id = db_prepare_int( $p_issue_id );
 
		$t_project_id = bug_get_field( $p_issue_id, 'project_id' );
		$t_project_name = project_get_name( $t_project_id );
 
		# create a namespace for the project to contain all project documentation.
		# create within it a namespace for issues.  This is to allow the creation of a _template.txt
		# file to act as the template for issues belonging to this project.
		return $t_project_name . ':issue:' . $c_issue_id;
	}
 
 	# ----------------------
	# Gets the page url for the specified issue id.
	function wiki_dokuwiki_get_url_for_issue( $p_issue_id ) {
		return wiki_dokuwiki_get_url_for_page_id( wiki_dokuwiki_get_page_id_for_issue( $p_issue_id ) );
	}
 
	# ----------------------
	# Gets the page id for the specified project.  The project id can be ALL_PROJECTS
	# The page id can then be converted to URL using wiki_dokuwiki_get_url_for_page_id().
	function wiki_dokuwiki_get_page_id_for_project( $p_project_id ) {
		$t_home = 'start';
		if ( $p_project_id == ALL_PROJECTS ) {
			return $t_home;
		} else {
			$t_project_name = project_get_name( $p_project_id );
			return $t_project_name . ':' . $t_home;
		}
	}
 
 	# ----------------------
	# Get URL for the specified project id.  The project is can be ALL_PROJECTS.
	function wiki_dokuwiki_get_url_for_project( $p_project_id ) {
		return wiki_dokuwiki_get_url_for_page_id( wiki_dokuwiki_get_page_id_for_project( $p_project_id ) );
	}

	/*
	function wiki_dokuwiki_string_display_links( $p_string ) {
		#$t_string = $p_string;
		#$t_string = str_replace( '[[', '{', $p_string );
	
		$t_wiki_web = config_get_global( 'wiki_engine_url' );
		
		preg_match_all( '/(^|.+?)(?:(?<=^|\W)' . '\[\[' . '([a-zA-Z0-9_:]+)\]\]|$)/s',
								$p_string, $t_matches, PREG_SET_ORDER );
		$t_result = '';
 
		foreach ( $t_matches as $t_match ) {
			$t_result .= $t_match[1];
 
			if ( isset( $t_match[2] ) ) {
				$t_result .= '<a href="' . wiki_dokuwiki_get_url_for_page_id( $t_match[2] ) . '">[[' . $t_match[2] . ']]</a>'; 
			}
		}
 
		return $t_result;
	}
	*/
?>