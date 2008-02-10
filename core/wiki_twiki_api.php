<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2008  Mantis Team   - mantisbt-dev@lists.sourceforge.net

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
	# $Id: 
	# --------------------------------------------------------

	# ----------------------
	# Gets the URL for the page with the specified page id.  This function is used
	# internally by this API.
	function wiki_twiki_get_url_for_page_id( $p_page_id ) {
		$t_root_url = config_get_global( 'wiki_engine_url' );
		$t_wiki_namespace = config_get( 'wiki_root_namespace' );
		return $t_root_url . $t_wiki_namespace . '/' . $p_page_id ;
	}
 
	# ----------------------
	# Gets the page id for the specified issue.  The page id can then be converted
	# to a URL using wiki_twiki_get_url_for_page_id().
	function wiki_twiki_get_page_id_for_issue( $p_issue_id ) {
		return 'IssueNumber' . db_prepare_int( $p_issue_id );
	}
 
	# ----------------------
	# Gets the page url for the specified issue id.
	function wiki_twiki_get_url_for_issue( $p_issue_id ) {
		return wiki_twiki_get_url_for_page_id( wiki_twiki_get_page_id_for_issue( $p_issue_id ) );
	}
 
	# ----------------------
	# Gets the page id for the specified project.  The project id can be ALL_PROJECTS
	# The page id can then be converted to URL using wiki_twiki_get_url_for_page_id().
	function wiki_twiki_get_page_id_for_project( $p_project_id ) {
		if ( $p_project_id == ALL_PROJECTS ) {
			return '';
		}

		$t_project_name = project_get_name( $p_project_id );
		$wikiword_regex = '/^[A-Z][^A-Z]+[A-Z]+.*$/';

		// Normalize (remove) all whitespace
		$t_project_name_normalized = preg_replace( '/\s\s*/', '', $t_project_name );
		if ( preg_match( $wikiword_regex, $t_project_name_normalized, $matches ) ) {
			return $t_project_name_normalized;
		}

		// Try uppercasing each word first
		$t_project_name_uppercased = preg_replace( '/\s\s*/', '', ucwords( $t_project_name_normalized ) );
		if ( preg_match( $wikiword_regex, $t_project_name_uppercased, $matches ) ) {
			return $t_project_name_uppercased;
		}

		// Then try adding 'ProjectName' to the front
		$t_project_name_prepended = 'ProjectName' . preg_replace( '/\s\s*/', '', ucwords( $t_project_name_uppercased ) );
		return $t_project_name_prepended;
	}
 
	# ----------------------
	# Get URL for the specified project id.  The project is can be ALL_PROJECTS.
	function wiki_twiki_get_url_for_project( $p_project_id ) {
		return wiki_twiki_get_url_for_page_id( wiki_twiki_get_page_id_for_project( $p_project_id ) );
	}
?>