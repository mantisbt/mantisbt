<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Language (Internationalization) API
 *
 * @package CoreAPI
 * @subpackage LanguageAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses error_api.php
 * @uses plugin_api.php
 * @uses user_pref_api.php
 */

require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'error_api.php' );
require_api( 'plugin_api.php' );
require_api( 'user_pref_api.php' );

# Cache of localization strings in the language specified by the last
# lang_load call
$g_lang_strings = array();

# stack for language overrides
$g_lang_overrides = array();

# To be used in custom_strings_inc.php :
$g_active_language = '';

/**
 * Loads the specified language and stores it in $g_lang_strings, to be used by lang_get
 * @param string $p_lang Name of Language to load.
 * @param string $p_dir  Directory Containing language file.
 * @return void
 */
function lang_load( $p_lang, $p_dir = null ) {
	global $g_lang_strings, $g_active_language;

	$g_active_language = $p_lang;
	if( isset( $g_lang_strings[$p_lang] ) && is_null( $p_dir ) ) {
		return;
	}

	if( !lang_language_exists( $p_lang ) ) {
		return;
	}

	if( $p_dir === null ) {
		include_once( config_get( 'language_path' ) . 'strings_' . $p_lang . '.txt' );
	} else {
		if( is_file( $p_dir . 'strings_' . $p_lang . '.txt' ) ) {
			include_once( $p_dir . 'strings_' . $p_lang . '.txt' );
		}
	}

	# Allow overriding strings declared in the language file.
	# custom_strings_inc.php can use $g_active_language.
	# Include file multiple times to allow for overrides per language.
	global $g_config_path;

	if( file_exists( $g_config_path . 'custom_strings_inc.php' ) ) {
		include( $g_config_path . 'custom_strings_inc.php' );
	}

	$t_vars = get_defined_vars();

	foreach( array_keys( $t_vars ) as $t_var ) {
		$t_lang_var = preg_replace( '/^s_/', '', $t_var );
		if( $t_lang_var != $t_var ) {
			$g_lang_strings[$p_lang][$t_lang_var] = $$t_var;
		} else if( 'MANTIS_ERROR' == $t_var ) {
			if( isset( $g_lang_strings[$p_lang][$t_lang_var] ) ) {
				foreach( $$t_var as $t_key => $t_val ) {
					$g_lang_strings[$p_lang][$t_lang_var][$t_key] = $t_val;
				}
			} else {
				$g_lang_strings[$p_lang][$t_lang_var] = $$t_var;
			}
		}
	}
}

/**
 * Determine the preferred language
 * @return string
 */
function lang_get_default() {
	global $g_active_language;

	$t_lang = false;

	# Confirm that the user's language can be determined
	if( function_exists( 'auth_is_user_authenticated' ) && auth_is_user_authenticated() ) {
		$t_lang = user_pref_get_language( auth_get_current_user_id() );
	}

	# Otherwise fall back to default
	if( !$t_lang ) {
		$t_lang = config_get_global( 'default_language' );
	}

	if( $t_lang == 'auto' ) {
		$t_lang = lang_map_auto();
	}

	# Remember the language
	$g_active_language = $t_lang;

	return $t_lang;
}

/**
 * Auto Map Language from HTTP server data
 * @return string
 */
function lang_map_auto() {
	$t_lang = config_get( 'fallback_language' );

	if( isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
		$t_accept_langs = explode( ',', $_SERVER['HTTP_ACCEPT_LANGUAGE'] );
		$t_auto_map = config_get( 'language_auto_map' );

		# Expand language map
		$t_auto_map_exp = array();
		foreach( $t_auto_map as $t_encs => $t_enc_lang ) {
			$t_encs_arr = explode( ',', $t_encs );

			foreach( $t_encs_arr as $t_enc ) {
				$t_auto_map_exp[trim( $t_enc )] = $t_enc_lang;
			}
		}

		# Find encoding
		foreach( $t_accept_langs as $t_accept_lang ) {
			$t_tmp = explode( ';', utf8_strtolower( $t_accept_lang ) );

			if( isset( $t_auto_map_exp[trim( $t_tmp[0] )] ) ) {
				$t_valid_langs = config_get( 'language_choices_arr' );
				$t_found_lang = $t_auto_map_exp[trim( $t_tmp[0] )];

				if( in_array( $t_found_lang, $t_valid_langs, true ) ) {
					$t_lang = $t_found_lang;
					break;
				}
			}
		}
	}

	return $t_lang;
}

/**
 * Ensures that a language file has been loaded
 * @param string $p_lang The language name.
 * @return void
 */
function lang_ensure_loaded( $p_lang ) {
	global $g_lang_strings;

	if( !isset( $g_lang_strings[$p_lang] ) ) {
		lang_load( $p_lang );
	}
}

/**
* Check if the given language exists
*
* @param string $p_lang The language name.
* @return boolean
*/
function lang_language_exists( $p_lang ) {
	$t_valid_langs = config_get( 'language_choices_arr' );
	$t_valid = in_array( $p_lang, $t_valid_langs, true );
	return $t_valid;
}

/**
 * language stack implementation
 * push a language onto the stack
 * @param string $p_lang The language name.
 * @return void
 */
function lang_push( $p_lang = null ) {
	global $g_lang_overrides;

	# If no specific language is requested, we'll
	#  try to determine the language from the users
	#  preferences

	$t_lang = $p_lang;

	if( null === $t_lang ) {
		$t_lang = config_get( 'default_language' );
	}

	# don't allow 'auto' as a language to be pushed onto the stack
	#  The results from auto are always the local user, not what the
	#  override wants, unless this is the first language setting
	if( ( 'auto' == $t_lang ) && ( 0 < count( $g_lang_overrides ) ) ) {
		$t_lang = config_get( 'fallback_language' );
	}

	$g_lang_overrides[] = $t_lang;

	# Remember the language
	$g_active_language = $t_lang;

	# make sure it's loaded
	lang_ensure_loaded( $t_lang );
}

/**
 * pop a language from the stack and return it
 * @return string
 */
function lang_pop() {
	global $g_lang_overrides;

	return array_pop( $g_lang_overrides );
}

/**
 * return value on top of the language stack
 * return default if stack is empty
 * @return string
 */
function lang_get_current() {
	global $g_lang_overrides;

	$t_count_overrides = count( $g_lang_overrides );
	if( $t_count_overrides > 0 ) {
		$t_lang = $g_lang_overrides[$t_count_overrides - 1];
	} else {
		$t_lang = lang_get_default();
	}

	return $t_lang;
}

/**
 * Retrieves an internationalized string
 * This function will return one of (in order of preference):
 *  1. The string in the current user's preferred language (if defined)
 *  2. The string in English
 * @param string $p_string The language string to retrieve.
 * @param string $p_lang   The language name.
 * @return string
 */
function lang_get( $p_string, $p_lang = null ) {
	global $g_lang_strings;

	# If no specific language is requested, we'll try to
	# determine the language from the users preferences

	$t_lang = $p_lang;

	if( null === $t_lang ) {
		$t_lang = lang_get_current();
	}

	# Now we'll make sure that the requested language is loaded
	lang_ensure_loaded( $t_lang );

	# note in the current implementation we always return the same value
	#  because we don't have a concept of falling back on a language.  The
	#  language files actually *contain* English strings if none has been
	#  defined in the correct language
	# @todo thraxisp - not sure if this is still true. Strings from last language loaded
	#      may still be in memeory if a new language is loaded.

	if( lang_exists( $p_string, $t_lang ) ) {
		return $g_lang_strings[$t_lang][$p_string];
	} else {
		$t_plugin_current = plugin_get_current();
		if( !is_null( $t_plugin_current ) ) {
			lang_load( $t_lang, config_get( 'plugin_path' ) . $t_plugin_current . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR );
			if( lang_exists( $p_string, $t_lang ) ) {
				return $g_lang_strings[$t_lang][$p_string];
			}
		}

		if( $t_lang == 'english' ) {
			error_parameters( $p_string );
			trigger_error( ERROR_LANG_STRING_NOT_FOUND, WARNING );
			return '';
		} else {

			# if string is not found in a language other than english, then retry using the english language.
			return lang_get( $p_string, 'english' );
		}
	}
}

/**
 * Check the language entry, if found return true, otherwise return false.
 * @param string $p_string The language string to retrieve.
 * @param string $p_lang   The language name.
 * @return boolean
 */
function lang_exists( $p_string, $p_lang ) {
	global $g_lang_strings;

	return( isset( $g_lang_strings[$p_lang] ) && isset( $g_lang_strings[$p_lang][$p_string] ) );
}

/**
 * Get language:
 * - If found, return the appropriate string (as lang_get()).
 * - If not found, no default supplied, return the supplied string as is.
 * - If not found, default supplied, return default.
 * @param string $p_string  The language string to retrieve.
 * @param string $p_default The default value to return.
 * @param string $p_lang    The language name.
 * @return string
 */
function lang_get_defaulted( $p_string, $p_default = null, $p_lang = null ) {
	$t_lang = $p_lang;

	if( null === $t_lang ) {
		$t_lang = lang_get_current();
	}

	# Now we'll make sure that the requested language is loaded
	lang_ensure_loaded( $t_lang );

	if( lang_exists( $p_string, $t_lang ) ) {
		return lang_get( $p_string );
	} else {
		if( null === $p_default ) {
			return $p_string;
		} else {
			return $p_default;
		}
	}
}
