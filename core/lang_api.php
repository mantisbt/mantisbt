<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: lang_api.php,v 1.1 2002-08-25 08:02:14 jfitzell Exp $
	# --------------------------------------------------------

	###########################################################################
	# Language (Internationalization) API
	###########################################################################

	# ------------------
	# Retrieves an internationalized string
	#  This function will return one of (in order of preference):
	#    1. The string in the current user's preferred language (if defined)
	#    2. The string in English
	function lang_get( $p_string ) {
		
		# note in the current implementation we always return the same value
		#  because we don't have a concept of falling back on a language.  The
		#  language files actually *contain* English strings if none has been
		#  defined in the correct language

		if ( isset( $GLOBALS['s_'.$p_string] ) ) {
			return $GLOBALS['s_'.$p_string];
		} else {
			trigger_error( ERROR_LANG_STRING_NOT_FOUND, WARNING );

			return '';
		}
	}

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

?>
