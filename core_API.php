<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	###########################################################################
	# INCLUDES
	###########################################################################

  	require( 'constant_inc.php' );
	require( './default/config_inc1.php' );
	if ( file_exists( 'config_inc.php' ) ) {
		include( 'config_inc.php' );
	}
	# Load file globals # @@@ ugly hack for ugly problem.  Find better solution soon
	require( './default/config_inc2.php' );

	ini_set('magic_quotes_runtime', 0);
	if ( OFF == $g_register_globals ) {
		extract( $HTTP_POST_VARS );
		extract( $HTTP_GET_VARS );
		extract( $HTTP_SERVER_VARS );
	}
	/*foreach ( $HTTP_POST_VARS as $key => $value) {
		$$key = $value;
	}
	foreach ( $HTTP_GET_VARS as $key => $value) {
		$$key = $value;
	}*/

	include( 'core_timer_API.php' );
	$g_timer = new BC_Timer;
	$g_timer->start_time();

	# seed random number generator
	list($usec,$sec)=explode(' ',microtime());
	mt_srand($sec*$usec);

	# @@@ Experimental
	# deal with register_globals being Off
	if ( OFF == $g_register_globals ) {
		foreach ( $HTTP_POST_VARS as $key => $value) {
			$$key = $value;
		}
		foreach ( $HTTP_GET_VARS as $key => $value) {
			$$key = $value;
		}
	}

	require( 'core_database_API.php' );

	# Nasty code to select the proper language file
	if ( !empty( $g_string_cookie_val ) ) {
		$t_language = '';
		db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
		$query = "SELECT language
				FROM $g_mantis_user_pref_table pref, $g_mantis_user_table user
				WHERE user.cookie_string='$g_string_cookie_val' AND
						user.id=pref.user_id";
		$result = db_query( $query );
		$t_language = db_result( $result, 0 , 0 );
		if (!empty( $t_language )) {
			include( 'lang/strings_'.$t_language.'.txt' );
		} else {
			include( 'lang/strings_'.$g_default_language.'.txt' );
		}
		db_close();
	} else {
		include( 'lang/strings_'.$g_default_language.'.txt' );
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
	#require( 'core_history_API.php' );
	require( 'core_proj_user_API.php' );
	require( 'core_category_API.php' );
	require( 'core_version_API.php' );
	# --------------------
?>
