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
 * Check Language Files
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

define( 'PLUGINS_DISABLED', true );
define( 'LANG_LOAD_DISABLED', true );
define( 'STRINGS_ENGLISH', 'strings_english.txt' );
$t_mantis_dir = dirname( dirname( __FILE__ ) ) . '/';

require_once( $t_mantis_dir . 'core.php' );

# Load schema version needed to render admin menu bar
require_once( 'schema.php' );

access_ensure_global_level( config_get_global( 'admin_site_threshold' ) );

if( function_exists( 'xdebug_disable' ) ) {
	xdebug_disable();
}

lang_push( 'english' );

set_time_limit( 0 );

layout_page_header();
layout_admin_page_begin();
print_admin_menu_bar( 'test_langs.php' );
?>

<div class="page-content col-md-12 col-xs-12">
	<div class="space-10"></div>

	<!-- CORE LANGUAGE FILES -->
	<div class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<?php print_icon( 'fa-text-width', 'ace-icon' ); ?>
				Testing Core Language Files
			</h4>
		</div>

		<div class="widget-body">
			<div class="widget-main no-padding table-responsive">
				<table class="table table-bordered table-condensed ">
<?php
checklangdir( $t_mantis_dir );
?>
				</table>
			</div>
		</div>
	</div>

	<div class="space-10"></div>

	<!-- PLUGINS -->
	<div class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<?php print_icon( 'fa-text-width', 'ace-icon' ); ?>
				Testing Plugins Language Files
			</h4>
		</div>

		<div class="widget-body">
			<div class="widget-main no-padding table-responsive">
				<table class="table table-bordered table-condensed ">
<?php
checkplugins( config_get_global( 'plugin_path' ) );
?>
				</table>
			</div>
		</div>
	</div>
</div>

<?php
layout_admin_page_end();


/**
 * Check plugin language files
 * @param string $p_path Plugin path.
 * @return void
 */
function checkplugins( $p_path ) {
	$t_path = rtrim( $p_path, DIRECTORY_SEPARATOR ) . '/';

	$t_plugins = @scandir( $t_path );
	if( false == $t_plugins ) {
		print_error( 'plugin path ' . $t_path . ' not found or not accessible' );
	} else {
		foreach( $t_plugins as $t_plugin ) {
			if( $t_plugin[0] == '.' || $t_plugin == 'Web.config' ) {
				continue;
			}
			echo '<tr><th colspan="2">';
			echo "Checking language files for plugin <strong>$t_plugin</strong>";
			echo '</th></tr>';
			checklangdir( $t_path . $t_plugin );
		}
	}
}

/**
 * Get the language files from the given directory.
 *
 * @param $p_path
 * @return string[] Language file names in ascending order.
 *
 * @throws UnexpectedValueException
 */
function get_lang_files( $p_path ) {
	$t_iter = new FileSystemIterator(
		$p_path,
		FileSystemIterator::KEY_AS_FILENAME | FileSystemIterator::CURRENT_AS_PATHNAME
	);

	# Filter language files, excluding 'qqq' pseudo-language
	# https://translatewiki.net/wiki/FAQ#Special_private_language_codes_qqq,_qqx
	$t_iter = new RegexIterator( $t_iter,
		'|strings_(?(?!qqq).+)\.txt|',
		RegexIterator::MATCH,
		RegexIterator::USE_KEY
	);

	$t_files = array_keys( iterator_to_array( $t_iter ) );
	sort( $t_files );
	return $t_files;
}

/**
 * Check directory of language files
 *
 * @param string $p_path Path to language files.
 *
 * @return void
 */
function checklangdir( $p_path ) {
	$t_path = rtrim( $p_path, DIRECTORY_SEPARATOR ) . '/lang/';
	echo '<tr><td>';
	echo "Retrieving language files from '$t_path'";
	echo '</td>';

	try {
		$t_lang_files = get_lang_files( $t_path );
		print_info( count( $t_lang_files ) . " files found" );
	} catch( UnexpectedValueException $e ) {
		print_fail( $e->getMessage() );
		echo '</tr>';
		return;
	}

	echo '</tr>';

	# Check reference English language file
	$t_key = array_search( STRINGS_ENGLISH, $t_lang_files );
	if( $t_key === false ) {
		print_fail( "File not found" );
	} else {
		flush();
		# No point testing other languages if English fails
		if( !checkfile( $t_path, STRINGS_ENGLISH ) ) {
			return;
		}
		unset( $t_lang_files[$t_key] );
	}

	# Check foreign language files
	foreach( $t_lang_files as $t_lang ) {
		checkfile( $t_path, $t_lang );
	}
}

/**
 * Check Language File
 *
 * @param string  $p_path  Path.
 * @param string  $p_file  File.
 * @return boolean
 */
function checkfile( $p_path, $p_file ) {
	echo "<tr><td>Testing '$p_file'</td>";
	flush();

	$t_file = $p_path . $p_file;

	$t_result = checktoken( $t_file, ($p_file == STRINGS_ENGLISH ) );
	if( !$t_result ) {
		return false;
	}

	try {
		ob_start();
		$t_result = eval( "require_once( '$t_file' );" );
		$t_data = ob_get_flush();
	} catch( ParseError $e ) {
		ob_end_clean();
		print_fail( $e->getMessage() . ' (line ' . $e->getLine() . ')' );
	}

	if( $t_result === false ) {
		print_fail( 'Failed at eval' );
		return false;
	}

	if( !empty( $t_data ) ) {
		print_fail( 'Failed at require_once (data output: ' . var_export( $t_data, true ) . ')' );
		return false;
	}

	print_pass();
	return true;
}

/**
 * Check Language File Tokens
 *
 * @param string  $p_file Language file to tokenize.
 * @param boolean $p_base Whether language file is default (aka english).
 * @return boolean
 */
function checktoken( $p_file, $p_base = false ) {
	$t_source = file_get_contents( $p_file );
	try {
		$t_tokens = token_get_all( $t_source, TOKEN_PARSE );
	} catch( ParseError $e ) {
		print_fail( $e->getMessage() . ' (line ' . $e->getLine() . ')' );
		return false;
	}

	$t_in_php_code = false;
	$t_variables = array();
	static $s_basevariables;
	$t_current_var = null;
	$t_last_token = 0;
	$t_set_variable = false;
	$t_variable_array = false;
	$t_two_part_string = false;
	$t_need_end_variable = false;
	$t_expect_end_array = false;
	$t_setting_variable = false;
	$t_pass = true;
	$t_fatal = false;

	foreach( $t_tokens as $t_token ) {
		$t_last_token2 = 0;
		if( is_string( $t_token ) ) {
			switch( $t_token ) {
				case '=':
					if( $t_last_token != T_VARIABLE ) {
						print_fail( '\'=\' sign without variable (line ' . $t_line . ')' );
						$t_pass = false;
					}
					$t_set_variable = true;
					break;
				case '[':
					if( $t_last_token != T_VARIABLE ) {
						$t_pass = false;
					}
					$t_variable_array = true;
					break;
				case ']':
					if( !$t_expect_end_array ) {
						$t_pass = false;
					}
					$t_expect_end_array = false;
					$t_variable_array = false;
					break;
				case ';':
					if( !$t_need_end_variable ) {
						print_fail( 'function separator found at unexpected location (line ' . $t_line . ')' );
						$t_pass = false;
					}
					$t_need_end_variable = false;
					break;
				case '.':
					if( $t_last_token == T_CONSTANT_ENCAPSED_STRING ) {
						$t_two_part_string = true;
					} else {
						print_fail( 'string concatenation found at unexpected location (line ' . $t_line . ')' );
						$t_pass = false;
					}
					break;
				default:
					print_fail( 'unknown token ' . $t_token );
					$t_pass = false;
					break;
			}
		} else {
			# token array
			list( $t_id, $t_text, $t_line ) = $t_token;

			if( $t_id == T_WHITESPACE || $t_id == T_COMMENT || $t_id == T_DOC_COMMENT ) {
				continue;
			}
			if( $t_need_end_variable ) {
				if( $t_two_part_string && $t_id == T_CONSTANT_ENCAPSED_STRING ) {
					$t_two_part_string = false;
					continue;
				}
				if( $t_setting_variable && $t_id == T_STRING ) {
					$t_last_token = T_VARIABLE;
					$t_expect_end_array = true;
					continue;
				}

				print_fail( 'token# ' . $t_id . ': ' . token_name( $t_id ) . ' = ' . $t_text . ' (line ' . $t_line . ')' );
				$t_pass = false;
			}

			switch( $t_id ) {
				case T_OPEN_TAG:
					$t_in_php_code = true;
					break;
				case T_CLOSE_TAG:
					$t_in_php_code = false;
					break;
				case T_INLINE_HTML:
					print_fail( 'Whitespace in language file outside of PHP code block (line ' . $t_line . ')' );
					$t_pass = false;
					break;
				case T_VARIABLE:
					if( $t_set_variable && $t_current_var != null ) {
						$t_need_end_variable = true;
						$t_setting_variable = true;
						$t_current_var = null;
						break;
					}
					$t_current_var = $t_text;
					break;
				case T_STRING:
					if( $t_variable_array ) {
						$t_current_var .= $t_text;
						if( !defined( $t_text ) ) {
							print_fail( 'undefined constant: ' . $t_text . ' (line ' . $t_line . ')' );
						}
					} else {
						print_fail( 'T_STRING found at unexpected location (line ' . $t_line . ')' );
						$t_pass = false;
					}
					if( strpos( $t_current_var, "\n" ) !== false ) {
						print_fail( 'NEW LINE in string: ' . $t_id . ' ' . token_name( $t_id ) . ' = ' . $t_text . ' (line ' . $t_line . ')' );
						$t_pass = false;
						$t_fatal = true;
					}
					$t_last_token2 = T_VARIABLE;
					$t_expect_end_array = true;
					break;
				case T_CONSTANT_ENCAPSED_STRING:
					if( $t_token[1][0] != '\'' ) {
						print_warn( 'Language strings should be single-quoted (line ' . $t_line . ')' );
					}
					if( $t_variable_array ) {
						$t_current_var .= $t_text;
						$t_last_token2 = T_VARIABLE;
						$t_expect_end_array = true;
						break;
					}

					if( $t_last_token == T_VARIABLE && $t_set_variable && $t_current_var != null ) {
						if( isset( $t_variables[$t_current_var] ) ) {
							print_fail( 'duplicate language string (' . $t_current_var . ' ) (line ' . $t_line . ')' );
						} else {
							$t_variables[$t_current_var] = $t_text;
						}

						if( $p_base ) {
							# english
							#if( isset( $s_basevariables[$t_current_var] ) ) {
							#	print_fail( "WARN: english string redefined - plugin? $t_current_var" );
							#}
							$s_basevariables[$t_current_var] = true;
						} else {
							if( !isset( $s_basevariables[$t_current_var] ) ) {
								print_warn( '\'' . $t_current_var . '\' is not defined in the English language file' );
							#} else {
							#  missing translation
							}
						}

					}
					if( strpos( $t_current_var, "\n" ) !== false ) {
						print_fail( 'NEW LINE in string: ' . $t_id . ' ' . token_name( $t_id ) . ' = ' . $t_text . ' (line ' . $t_line . ')' );
						$t_pass = false;
						$t_fatal = true;
					}
					$t_current_var = null;
					$t_need_end_variable = true;
					break;
				default:
					print_fail( $t_id . ' ' . token_name( $t_id ) . ' = ' . $t_text . ' (line ' . $t_line . ')' );
					$t_pass = false;
					break;
			}

			$t_last_token = $t_id;
			if( $t_last_token2 > 0 ) {
				$t_last_token = $t_last_token2;
			}
		}

		if( $t_fatal ) {
			break;
		}
	}

	return $t_pass;
}

/**
 * Print Language File Error messages
 *
 * @param string $p_string Error string.
 * @param string $p_type   Message type to display (default ERROR).
 * @return void
 */
function print_error($p_string, $p_type = 'ERROR' ) {
	if ( $p_type === 'WARNING' ) {
		echo '<span class="alert-warning">', $p_type . ': ' . $p_string, '</span><br />';
	} else {
		echo '<span class="alert-danger">', $p_type . ': ' . $p_string, '</span><br />';
	}
}

function print_info( $p_message ) {
	echo '<td class="alert-info">', string_attribute( $p_message ), '</td>';
}

function print_pass( $p_message = 'PASS') {
	echo '<td class="alert-success">', string_attribute( $p_message ), '</td>';
}

function print_warn( $p_message ) {
	echo '<td class="alert-warning">', string_attribute( $p_message ), '</td>';
}

function print_fail( $p_message ) {
	echo '<td class="alert-danger">', string_attribute( $p_message ), '</td>';
}
