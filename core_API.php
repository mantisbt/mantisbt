<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	###########################################################################
	# INCLUDES
	###########################################################################

  	require( 'constant_inc.php' );
	if ( file_exists( 'custom_constant_inc.php' ) ) {
		include( 'custom_constant_inc.php' );
	}
	require( 'config_defaults_inc.php' );
	if ( file_exists( 'custom_config_inc.php' ) ) {
		include( 'custom_config_inc.php' );
	}
	# for backward compatability
	if ( file_exists( 'config_inc.php' ) ) {
		include( 'config_inc.php' );
	}
	
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

	ini_set('magic_quotes_runtime', 0);

	# @@@ Experimental
	# deal with register_globals being Off
	$t_phpversion = explode('.', phpversion()); 
	if ( OFF == $g_register_globals ) {
		if ( $t_phpversion[0] == 4 && $t_phpversion[1] >= 1 ) { 
			extract( $_REQUEST );
			extract( $_SERVER );
		} else {
			extract( $HTTP_POST_VARS );
			extract( $HTTP_GET_VARS );
			extract( $HTTP_SERVER_VARS );
		}
	}

	include( 'core_timer_API.php' );

	# initialize our timer
	$g_timer = new BC_Timer;

	# seed random number generator
	list($usec,$sec)=explode(' ',microtime());
	mt_srand($sec*$usec);

	# DATABASE WILL BE OPENED HERE!!  THE DATABASE SHOULDN'T BE EXPLICITLY
	# OPENED ANYWHERE ELSE.
	require( 'core_database_API.php' );

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

	require( 'core_html_API.php' );
	require( 'core_print_API.php' );
	require( 'core_helper_API.php' );
	require( 'core_summary_API.php' );
	require( 'core_date_API.php' );
	require( 'core_user_API.php' );
	require( 'core_email_API.php' );
	require( 'core_news_API.php' );
	require( 'core_icon_API.php' );
	require( 'core_ldap_API.php' );
	require( 'core_history_API.php' );
	require( 'core_proj_user_API.php' );
	require( 'core_category_API.php' );
	require( 'core_version_API.php' );
	require( 'core_compress_API.php' );
	require( 'core_relationship_API.php' );
	require( 'core_file_API.php' );
	require( 'core_custom_attribute_API.php' );
	# --------------------
?>
