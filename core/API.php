<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	###########################################################################
	# INCLUDES
	###########################################################################

	$t_core_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;

	# Include compatibility file before anything else
	require_once( $t_core_dir . 'php_api.php' );
	require_once( $t_core_dir . 'timer_api.php' );

	# initialize our timer
	$g_timer = new BC_Timer;

	# seed random number generator
	list($usec,$sec)=explode(' ',microtime());
	mt_srand($sec*$usec);

	# DATABASE WILL BE OPENED HERE!!  THE DATABASE SHOULDN'T BE EXPLICITLY
	# OPENED ANYWHERE ELSE.
	require_once( $t_core_dir . 'database_api.php' );

	require_once( $t_core_dir . 'config_api.php' );
	require_once( $t_core_dir . 'gpc_api.php' );
	require_once( $t_core_dir . 'error_api.php' );
	require_once( $t_core_dir . 'authentication_api.php' );
	require_once( $t_core_dir . 'access_api.php' );
	require_once( $t_core_dir . 'lang_api.php' );
	require_once( $t_core_dir . 'string_api.php' );
	require_once( $t_core_dir . 'html_api.php' );
	require_once( $t_core_dir . 'print_api.php' );
	require_once( $t_core_dir . 'helper_api.php' );
	require_once( $t_core_dir . 'summary_api.php' );
	require_once( $t_core_dir . 'date_api.php' );
	require_once( $t_core_dir . 'user_api.php' );
	require_once( $t_core_dir . 'current_user_api.php' );
	require_once( $t_core_dir . 'email_api.php' );
	require_once( $t_core_dir . 'news_api.php' );
	require_once( $t_core_dir . 'icon_api.php' );
	require_once( $t_core_dir . 'history_api.php' );
	require_once( $t_core_dir . 'proj_user_api.php' );
	require_once( $t_core_dir . 'category_api.php' );
	require_once( $t_core_dir . 'version_api.php' );
	require_once( $t_core_dir . 'compress_api.php' );
	require_once( $t_core_dir . 'relationship_api.php' );
	require_once( $t_core_dir . 'file_api.php' );
	require_once( $t_core_dir . 'custom_attribute_api.php' );
	require_once( $t_core_dir . 'bugnote_api.php' );
	require_once( $t_core_dir . 'bug_api.php' );
	require_once( $t_core_dir . 'project_api.php' );

	# Include LDAP only if needed.
	if ( ( ON == config_get( 'use_ldap_email' ) ) || ( LDAP == config_get ( 'login_method' ) ) ) {
		require_once( $t_core_dir . 'ldap_api.php' );
	}

	if (ON == $g_use_jpgraph) {
		require_once( $t_core_dir . 'graph_api.php' );
		require_once( $g_jpgraph_path . 'jpgraph.php' );
		require_once( $g_jpgraph_path . 'jpgraph_line.php' );
		require_once( $g_jpgraph_path . 'jpgraph_bar.php' );
		require_once( $g_jpgraph_path . 'jpgraph_pie.php' );
		require_once( $g_jpgraph_path . 'jpgraph_pie3d.php' );	
	}
	# --------------------
?>
