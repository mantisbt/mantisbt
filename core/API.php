<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	###########################################################################
	# INCLUDES
	###########################################################################

	# Include compatibility file before anything else
	include( dirname(__FILE__) . '/php_API.php' );

	# Should be eventually moved to the admin scripts, but keep it here for a while
	# to make sure people don't miss it.
	function obsolete_config_variable($var, $replace) {
		global $$var;
		if (isset($$var)) {
			PRINT '$' . $var . ' is now obsolete';
			if ($replace != '') {
				PRINT ', please use $' . $replace;
			}
			exit;
		}
	}

	# Check for obsolete variables
	obsolete_config_variable('g_notify_developers_on_new', 'g_notify_flags');
	obsolete_config_variable('g_notify_on_new_threshold', 'g_notify_flags');
	obsolete_config_variable('g_notify_admin_on_new', 'g_notify_flags');

	include( dirname(__FILE__) . '/timer_API.php' );

	# initialize our timer
	$g_timer = new BC_Timer;

	# seed random number generator
	list($usec,$sec)=explode(' ',microtime());
	mt_srand($sec*$usec);

	# DATABASE WILL BE OPENED HERE!!  THE DATABASE SHOULDN'T BE EXPLICITLY
	# OPENED ANYWHERE ELSE.
	require( dirname(__FILE__) . '/database_API.php' );

	# Nasty code to select the proper language file
	if ( !empty( $g_string_cookie_val ) ) {
		$query = "SELECT DISTINCT language
				FROM $g_mantis_user_pref_table pref, $g_mantis_user_table user
				WHERE user.cookie_string='$g_string_cookie_val' AND
						user.id=pref.user_id";
		$result = db_query( $query );
		$g_active_language = db_result( $result, 0 , 0 );
		if (empty( $g_active_language )) {
			$g_active_language = $g_default_language;
		}
	} else {
		$g_active_language = $g_default_language;
	}

	include( 'lang/strings_'.$g_active_language.'.txt' );

	# Allow overriding strings declared in the language file.
	# custom_strings_inc.php can use $g_active_language
	if ( file_exists( 'custom_strings_inc.php' ) ) {
		include ( 'custom_strings_inc.php' );
	}

	$t_dir = dirname(__FILE__) . '/';
	require( $t_dir . 'config_API.php' );
	require( $t_dir . 'gpc_API.php' );
	require( $t_dir . 'error_API.php' );
	require( $t_dir . 'security_API.php' );
	require( $t_dir . 'html_API.php' );
	require( $t_dir . 'print_API.php' );
	require( $t_dir . 'helper_API.php' );
	require( $t_dir . 'summary_API.php' );
	require( $t_dir . 'date_API.php' );
	require( $t_dir . 'user_API.php' );
	require( $t_dir . 'email_API.php' );
	require( $t_dir . 'news_API.php' );
	require( $t_dir . 'icon_API.php' );
	require( $t_dir . 'ldap_API.php' );
	require( $t_dir . 'history_API.php' );
	require( $t_dir . 'proj_user_API.php' );
	require( $t_dir . 'category_API.php' );
	require( $t_dir . 'version_API.php' );
	require( $t_dir . 'compress_API.php' );
	require( $t_dir . 'relationship_API.php' );
	require( $t_dir . 'file_API.php' );
	require( $t_dir . 'custom_attribute_API.php' );
	require( $t_dir . 'bugnote_API.php' );
	require( $t_dir . 'bug_API.php' );

	if (ON == $g_use_jpgraph) {
		require( $t_dir . 'graph_API.php' );
		require( $g_jpgraph_path . 'jpgraph.php' );
		require( $g_jpgraph_path . 'jpgraph_line.php' );
		require( $g_jpgraph_path . 'jpgraph_bar.php' );
		require( $g_jpgraph_path . 'jpgraph_pie.php' );
		require( $g_jpgraph_path . 'jpgraph_pie3d.php' );	
	}
	# --------------------
?>
