<?php
# Mantis - a php based bugtracking system

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

/**
 * @package MantisBT
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2009  Mantis Team   - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */
/**
 * Mantis Core API's
 */
require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core.php' );

access_ensure_global_level( ADMINISTRATOR );

set_time_limit( 0 );

if( function_exists( 'xdebug_disable' ) ) {
	xdebug_disable();
}

require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'constant_inc.php' );

if( !defined( 'T_ML_COMMENT' ) ) {
	define( 'T_ML_COMMENT', T_COMMENT );
}
else {
	define( 'T_DOC_COMMENT', T_ML_COMMENT );
}

if( function_exists( 'opendir' ) && function_exists( 'readdir' ) ) {
	$t_lang_files = Array();
	if( $handle = opendir( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'lang' ) ) {
		while( false !== ( $file = readdir( $handle ) ) ) {
			if( $file[0] != '.' && $file != 'langreadme.txt' && $file != 'CVS' && !is_dir( $file ) ) {
				$t_lang_files[] = $file;
			}
		}
		closedir( $handle );
	}
}
else {
	$t_lang_files = Array();
	foreach( $g_language_choices_arr as $t_lang ) {
		if( $t_lang == 'auto' ) {
			continue;
		}
		$t_lang_files[] = $t_lang;
	}
}

if( sizeof( $t_lang_files ) > 0 ) {
	echo 'Retrieved ', sizeof( $t_lang_files ), ' languages<br />';

	foreach( $t_lang_files as $file ) {
		$t_short_name = $file;

		echo "Testing language file '$t_short_name' (phase 1)...<br />";
		flush();

		$file = dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $file;

		$result = checktoken( $file );

		if( !$result ) {
			print_error( "FAILED: Language file '$t_short_name' failed at phase 1." );
		}

		echo "Testing language file '$t_short_name' (phase 2)...<br />";
		flush();

		set_error_handler( 'lang_error_handler' );
		ob_start();
		$result = eval( "require_once( '$file' );" );
		$data = ob_get_contents();;
		ob_end_clean();
		restore_error_handler();

		if( $result === false ) {
			print_error( "FAILED: Language file '$t_short_name' failed at eval" );
		}

		if( strlen( $data ) > 0 ) {
			print_error( "FAILED: Language file '$t_short_name' failed at require_once (data output of length " . strlen( $data ) . ")" );
		}
	}
}

function checktoken( $file ) {
	$in_php_code = false;
	$variables = Array();
	$current_var = null;
	$last_token = 0;
	$set_variable = false;
	$variablearr = false;
	$twopartstring = false;
	$need_end_variable = false;
	$source = file_get_contents( $file );
	$tokens = token_get_all( $source );
	$expectendarr = false;
	$settingvariable = false;
	$pass = true;
	foreach( $tokens as $token ) {
		$last_token2 = 0;
		if( is_string( $token ) ) {
			switch( $token ) {
				case '=':
					if( $last_token != T_VARIABLE ) {
						print_error( "ERROR: = sign without variable" );
						$pass = false;
					}
					$set_variable = true;
					break;
				case '[':
					if( $last_token != T_VARIABLE ) {
						$pass = false;
					}
					$variablearr = true;
					break;
				case ']':
					if( !$expectendarr ) {
						$pass = false;
					}
					$expectendarr = false;
					break;
				case ';':
					if( !$need_end_variable ) {
						print_error( "ERROR: function seperator found at unexpected location (line $line)" );
						$pass = false;
					}
					$need_end_variable = false;
					break;
				case '.':
					if( $last_token == T_CONSTANT_ENCAPSED_STRING ) {
						$twopartstring = true;
					} else {
						print_error( "ERROR: string concat found at unexpected location (line $line)" );
						$pass = false;
					}
					break;
				default:
					print_error( "UNKNOWN TOKEN" . $token );
					$pass = false;
					break;
			}
		} else {
			// token array
			list( $id, $text, $line ) = $token;

			if( $id == T_WHITESPACE || $id == T_COMMENT || $id == T_DOC_COMMENT || $id == T_ML_COMMENT ) {
				continue;
			}
			if( $need_end_variable ) {
				if( $twopartstring && $id == T_CONSTANT_ENCAPSED_STRING ) {
					$twopartstring = false;
					continue;
				}
				if( $settingvariable && $id == T_STRING ) {
					$last_token = T_VARIABLE;
					$expectendarr = true;
					continue;
				}

				print_error( "ERROR" . $id . token_name( $id ) . $text . $line );
				$pass = false;
			}

			switch( $id ) {
				case T_OPEN_TAG:
					$in_php_code = true;
					break;
				case T_CLOSE_TAG:
					$in_php_code = false;
					break;
				case T_INLINE_HTML:
					print_error( "ERROR: Whitespace in language file outside of PHP code block (line $line)" );
					$pass = false;
					break;
				case T_VARIABLE:
					if( $set_variable && $current_var != null ) {
						$need_end_variable = true;
						$settingvariable = true;
						$current_var = null;
						break;
					}
					$current_var = $text;
					break;
				case T_STRING:
					if( $variablearr ) {
						$current_var .= $text;
						if( !defined( $text ) ) {
							print_error( "undefined constant: $current_var" );
						}
					} else {
						print_error( "ERROR: T_STRING found at unexpected location (line $line)" );
						$pass = false;
					}
					$last_token2 = T_VARIABLE;
					$expectendarr = true;
					break;
				case T_CONSTANT_ENCAPSED_STRING:
					if( $last_token == T_VARIABLE && $set_variable && $current_var != null ) {
						if( isset( $variables[$current_var] ) ) {
							print_error( "ERROR: duplicate language string ($current_var ) (line $line)" );
						} else {
							$variables[$current_var] = $text;
						}
					}
					$current_var = null;
					$need_end_variable = true;
					break;
				default:
					// if (!$in_php_code)
					print_error( "PARSER: " . $id . token_name( $id ) . $text . $line );
					$pass = false;
					break;
			}

			$last_token = $id;
			if( $last_token2 > 0 ) {
				$last_token = $last_token2;
			}
		}
	}

	return $pass;
}

function lang_error_handler( $p_type, $p_error, $p_file, $p_line, $p_context ) {
	print_error( "error handler thrown: ", $p_type, $p_error, $p_file, $p_line, $p_context );
}

function print_error( $p_string ) {
	echo "<font color='red'>ERROR: ", $p_string, '</font><br>';
}
