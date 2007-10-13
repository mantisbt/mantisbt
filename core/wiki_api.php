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
	# $Id: wiki_api.php,v 1.1.2.1 2007-10-13 22:35:48 giallu Exp $
	# --------------------------------------------------------
 
	require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'helper_api.php' );
	require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'utility_api.php' );
	require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'database_api.php' );
	require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'authentication_api.php' );
	require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'gpc_api.php' );
	require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'access_api.php' );
	require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'project_api.php' );
	require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'wiki_' . config_get( 'wiki_engine' ) . '_api.php' );

	# ----------------------
	# Calls a function with the specified name (not including prefix) and given the array
	# of parameters supplied.  An example prefix is "wiki_dokuwiki_".
	function wiki_call( $p_function, $p_args_array ) {
		$t_function = 'wiki_' . config_get_global( 'wiki_engine' ) . '_' . $p_function;
		return call_user_func_array( $t_function, $p_args_array );
	}

	# ----------------------
	# Checks if the Wiki feature is enabled or not.
	function wiki_is_enabled() {
		return config_get( 'wiki_enable' ) == ON;
	}
 
 	# ----------------------
	# Ensures that the wiki feature is enabled.
	function wiki_ensure_enabled() {
		if ( !wiki_is_enabled() ) {
			access_denied();
		}
	}
 
	# ----------------------
	# Gets the wiki URL for the issue with the specified id.
	function wiki_get_url_for_issue( $p_issue_id ) {
		return wiki_call( 'get_url_for_issue', array( $p_issue_id ) );
	}
 
	# ----------------------
	# Gets the wiki URL for the project with the specified id.  The project id can be ALL_PROJECTS.
	function wiki_get_url_for_project( $p_project_id ) {
		return wiki_call( 'get_url_for_project', array( $p_project_id ) );
	}
 
	# ----------------------
	/*
	function wiki_string_display_links( $p_string ) {
		if ( !wiki_is_enabled() ) {
			return $p_string;
		}
 
		return wiki_call( 'string_display_links', array( $p_string ) );
	}
	*/
?>