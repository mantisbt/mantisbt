<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: lang_api.php,v 1.9 2003-01-23 21:44:44 jlatour Exp $
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

		if ( lang_exists( $p_string ) ) {
			return $GLOBALS['s_'.$p_string];
		} else {
			trigger_error( ERROR_LANG_STRING_NOT_FOUND, WARNING );
			return '';
		}
	}

	# ------------------
	# Check the language entry, if found return true, otherwise return false.
	function lang_exists( $p_string ) {
		return ( isset( $GLOBALS['s_' . $p_string] ) );
	}

	# ------------------
	# Get language:
	# - If found, return the appropriate string (as lang_get()).
	# - If not found, no default supplied, return the supplied string as is.
	# - If not found, default supplied, return default.
	function lang_get_defaulted( $p_string, $p_default = null ) {
		if ( lang_exists( $p_string) ) {
			return lang_get( $p_string );
		} else {
			if ( null === $p_default ) {
				return $p_string;
			} else {
				return $p_default;
			}
		}
	}

	# ------------------
	# MAIN CODE
	# ------------------

	# Nasty code to select the proper language file
	# Default language is used if database is unavailable (for error handling)
	if ( function_exists('db_query') && !is_blank( $g_string_cookie_val ) ) {
		$query = "SELECT DISTINCT language
				FROM $g_mantis_user_pref_table p, $g_mantis_user_table u
				WHERE u.cookie_string='$g_string_cookie_val' AND
						u.id=p.user_id";
		$result = db_query( $query );
		$g_active_language = db_result( $result, 0 , 0 );
		if ( false == $g_active_language ) {
			$g_active_language = $g_default_language;
		}
	} else {
		$g_active_language = $g_default_language;
	}

	# in most scenarios English is done first, then translated,
	# hence including the English first would show non-translated
	# strings in English rather than giving errors (if the copying 
	# script is not used)
	$t_lang_dir = dirname ( dirname ( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR;
	if ( $g_active_language != 'english' ) {
		require_once( $t_lang_dir . 'strings_english.txt' );
	}
	require_once( $t_lang_dir . 'strings_'.$g_active_language.'.txt' );

	# Allow overriding strings declared in the language file.
	# custom_strings_inc.php can use $g_active_language
	$t_custom_strings = dirname ( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'custom_strings_inc.php';
	if ( file_exists( $t_custom_strings ) ) {
		require_once( $t_custom_strings );
	}
?>
