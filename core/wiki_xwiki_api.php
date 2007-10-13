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
	# $Id: wiki_xwiki_api.php,v 1.1.2.1 2007-10-13 22:35:51 giallu Exp $
	# --------------------------------------------------------

	# ----------------------
	# Gets the URL for the page with the specified page id.  This function is used
	# internally by this API.
	function wiki_xwiki_get_url_for_page_id( $p_page_id ) {
		$t_root_url = config_get_global( 'wiki_engine_url' );
		return $t_root_url . $p_page_id ;
	}
 
	# ----------------------
	# Gets the page id for the specified issue.  The page id can then be converted
	# to a URL using wiki_xwiki_get_url_for_page_id().
	function wiki_xwiki_get_page_id_for_issue( $p_issue_id ) {
		 
		$t_project_id = project_get_name (bug_get_field( $p_issue_id, 'project_id' ));
		$c_issue_id = db_prepare_int( $p_issue_id );
		return $c_issue_id;
 		return $t_project_id.'/'.$c_issue_id;
	}
 
 	# ----------------------
	# Gets the page url for the specified issue id.
	function wiki_xwiki_get_url_for_issue( $p_issue_id ) {
		return wiki_xwiki_get_url_for_page_id( wiki_xwiki_get_page_id_for_issue( $p_issue_id ) );
	}
 
	# ----------------------
	# Gets the page id for the specified project.  The project id can be ALL_PROJECTS
	# The page id can then be converted to URL using wiki_xwiki_get_url_for_page_id().
	function wiki_xwiki_get_page_id_for_project( $p_project_id ) {
		if ( $p_project_id == ALL_PROJECTS ) {
			return config_get( 'wiki_root_namespace' );
		} else {
			$t_project_name = project_get_name( $p_project_id );
			return $t_project_name;
		}
	}
 
 	# ----------------------
	# Get URL for the specified project id.  The project is can be ALL_PROJECTS.
	function wiki_xwiki_get_url_for_project( $p_project_id ) {
		return wiki_xwiki_get_url_for_page_id( wiki_xwiki_get_page_id_for_project( $p_project_id ) );
	}
?>