<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2006  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
 
	# --------------------------------------------------------
	# $Id: wiki_api.php,v 1.1 2006-08-09 07:55:01 vboctor Exp $
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