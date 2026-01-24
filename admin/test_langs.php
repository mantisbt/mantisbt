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
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

define( 'PLUGINS_DISABLED', true );
define( 'LANG_LOAD_DISABLED', true );

# Use DIRECTORY_SEPARATOR to ensure a consistent display of the directory on Windows
$t_mantis_dir = dirname( __DIR__ ) . DIRECTORY_SEPARATOR;

require_once( $t_mantis_dir . 'core.php' );

$t_show_untranslated = gpc_get_bool( 'show_untranslated', false );
$t_show_translation_errors = gpc_get_bool( 'translation_errors', false );

/**
 * Returns a URL to implement filtering.
 *
 * @param bool $p_show_untranslated       Whether to show untranslated strings.
 * @param bool $p_show_translation_errors Whether to show translation errors.
 *
 * @return string url
 */
function mode_url( $p_show_untranslated, $p_show_translation_errors ) {
	return helper_url_combine( basename( __FILE__ ), [
		'show_untranslated'  => (int)$p_show_untranslated,
		'translation_errors' => (int)$p_show_translation_errors,
	] );
}

/**
 * Class CheckLangFile.
 *
 * Processes a language strings file (strings_xxx.txt)
 */
class LangCheckFile {
	const BASE = 'strings_english.txt';

	/**
	 * Comma-delimited list of valid tags and attributes for language strings
	 * @see http://htmlpurifier.org/live/configdoc/plain.html#HTML.Allowed
	 */
	const VALID_TAGS = 'em,strong,i,b,br,p,ul,ol,li,table,tr,td,code,a[href|title],span[class],abbr[title]';

	/**
	 * @var bool True if HTML syntax checks can be performed
	 * (i.e. if DOM and libxml PHP extensions are available).
	 * @see canDoHtmlChecks().
	 */
	protected static $can_do_html_check;

	/**
	 * @var string Full path to to the language file
	 */
	protected $file;

	/**
	 * @var string Group of translations
	 */
	protected $group;

	/**
	 * @var string Language code (translatewiki-compatible)
	 */
	protected $lang;

	/**
	 * @var bool True if file is base language (i.e. English)
	 */
	protected $is_base_language;

	/**
	 * @var string[] List of errors messages detected during checks.
	 */
	protected $errors = [];

	/**
	 * @var string[] List of warning messages detected during checks.
	 */
	protected $warnings = [];

	/**
	 * @var HTMLPurifier
	 * @see http://htmlpurifier.org/
	 */
	protected $purifier;

	/**
	 * @var array Array of base variables loaded first from the reference English file.
	 */
	protected static $basevariables = [];

	/**
	 * CheckLangFile constructor.
	 *
	 * @param string $p_path  Path to language files
	 * @param string $p_file  Language file name
	 * @param string $p_group Group of translations
	 */
	public function __construct( $p_path, $p_file, $p_group ) {
		$this->file = $p_path . $p_file;
		$this->group = $p_group;
		$this->is_base_language = ( $p_file == self::BASE );

		# Initialize HTML Purifier object
		# - define list of tags and attributes allowed in language strings
		# - disable the cache for now, as this is only used sporadically by
		#   admins/devs, so the performance hit (~ 1-2 sec) is acceptable.
		$t_purifier_config = HTMLPurifier_Config::create( [
			'Cache.DefinitionImpl' => null,
			'HTML.Allowed' => self::VALID_TAGS,
		] );
		$this->purifier = new HTMLPurifier( $t_purifier_config );
	}

	/**
	 * Log an error.
	 *
	 * @param string $p_message Log message
	 * @param int    $p_line    Line number (0 - without number)
	 * @param bool   $p_encode  Encode HTML
	 *
	 * @return void
	 */
	protected function logFail( $p_message, $p_line = 0, $p_encode = true ) {
		if( $p_line ) {
			$p_message = "Line $p_line: $p_message";
		}
		$this->errors[] = $p_encode ? string_attribute( $p_message ) : $p_message;
	}

	/**
	 * Log a warning.
	 *
	 * @param string $p_message Log message
	 * @param int    $p_line    Line number (0 - without number)
	 * @param bool   $p_encode  Encode HTML
	 *
	 * @return void
	 */
	protected function logWarn( $p_message, $p_line = 0, $p_encode = true ) {
		if( $p_line ) {
			$p_message = "Line $p_line: $p_message";
		}
		$this->warnings[] = $p_encode ? string_attribute( $p_message ) : $p_message;
	}

	/**
	 * Returns True if HTML tags validation can be performed.
	 * @return bool
	 */
	public static function canDoHtmlChecks() {
		if( self::$can_do_html_check === null ) {
			self::$can_do_html_check = extension_loaded( 'dom' ) && extension_loaded( 'libxml' );
		}
		return self::$can_do_html_check;
	}

	/**
	 * Check Language File.
	 *
	 * @return bool True if success (possibly with warnings), False if errors
	 */
	public function check() {
		$this->checkConfig();

		if( $this->checkTokens() ) {
			try {
				ob_start();
				$t_result = eval( "require_once( '$this->file' );" );
				$t_data = ob_get_flush();

				if( $t_result === false ) {
					$this->logFail( 'Failed at eval' );
				} elseif( !empty( $t_data ) ) {
					$this->logFail(
						'Failed at require_once (data output: ' . var_export( $t_data, true ) . ')'
					);
				}
			} catch( ParseError $e ) {
				ob_end_clean();
				$this->logFail( $e->getMessage(), $e->getLine() );
			}
		}

		return empty( $this->errors );
	}

	/**
	 * Get error count.
	 *
	 * @return int Error count
	 */
	public function countErrors() {
		return count( $this->errors );
	}

	/**
	 * Print check results.
	 *
	 * Prints a table row with 2 cells, the first contains the name of the file
	 * being checked, and the second the outcome of the check including error
	 * and warning messages as unordered list if any, with a colored background
	 * depending on overall status (PASS, WARNINGS, ERRORS).
	 */
	public function printResults() {
		echo '<tr><td class="col-xs-4"';
		if( $this->warnings && $this->errors ) echo ' rowspan="2"';
		echo '>Testing \'' . basename( $this->file ) . '\'</td>';

		if( $this->warnings ) {
			echo '<td class="alert-warning">WARNINGS<ul>';
			foreach( $this->warnings as $t_msg ) {
				echo '<li>' . $t_msg . '</li>';
			}
			echo '</ul></td></tr>' . PHP_EOL;
		}

		if( $this->errors ) {
			if( $this->warnings ) echo '<tr>';
			echo '<td class="alert-danger">ERRORS<ul>';
			foreach( $this->errors as $t_msg ) {
				echo '<li>' . $t_msg . '</li>';
			}
			echo '</ul></td></tr>' . PHP_EOL;
		}

		if( !$this->warnings && !$this->errors ) {
			echo '<td class="alert-success">PASS</td></tr>' . PHP_EOL;
		}
	}

	/**
	 * Check Language File and print results.
	 *
	 * @return bool True if success (possibly with warnings), False if errors
	 */
	public function checkAndPrint() {
		$t_status = $this->check();
		$this->printResults();
		return $t_status;
	}

	/**
	 * Check language existence in configuration.
	 *
	 * TranslateWiki.net sometimes adds brand new languages; we need to make
	 * sure that these are properly defined in
	 * - {@see $g_language_choices_arr}
	 * - {@see $g_language_auto_map}
	 * If not, this is reported as a warning as this just means the new language
	 * cannot be used within MantisBT.
	 */
	protected function checkConfig() {
		global /** @noinspection PhpUnusedLocalVariableInspection */
		$g_language_choices_arr, $g_language_auto_map;

		# Extract language from file name
		# preg_match() can't fail, get_lang_files() uses the same regex
		preg_match( '/strings_(.+)\.txt$/', $this->file, $t_matches );
		$t_lang = $t_matches[1];

		$t_lang_map = [
			'bengali'             => 'bn',
			'bosnian'             => 'bs',
			'chinese_simplified'  => 'zh-hans',  # replaces $g_language_auto_map
			'chinese_traditional' => 'zh-hant',  # replaces $g_language_auto_map
			'indonesian'          => 'id',
			'laki'                => 'lki',
			'lezgi'               => 'lez',
			'lower_sorbian'       => 'dsb',
			'malay'               => 'ms',
			'malayalam'           => 'ml',
			'mirandese'           => 'mwl',
			'piedmontese'         => 'pms',
			'saraiki'             => 'skr-arab', # replaces $g_language_auto_map
			'serbian'             => 'sr-ec',    # replaces $g_language_auto_map
			'skr'                 => 'skr-arab',
			'sr'                  => 'sr-ec',
			'tachelhit'           => 'shi',
			'telugu'              => 'te',
			'tunisian_arabic'     => 'aeb-arab',
			'upper_sorbian'       => 'hsb',
			'zh'                  => 'zh-hans',
			'zh-cn'               => 'zh-hans',
			'zh-hk'               => 'zh-hant',
			'zh-tw'               => 'zh-hant',
		];
		$t_lang_map = array_replace( array_flip( $g_language_auto_map ), $t_lang_map );
		ksort( $t_lang_map );
		$this->lang = explode( ',', array_key_exists( $t_lang, $t_lang_map ) ? $t_lang_map[$t_lang] : $t_lang );
		$this->lang = trim( end( $this->lang ) );

		# Check for matching entries in configuration
		$t_check = ['g_language_choices_arr', 'g_language_auto_map'];
		foreach( $t_check as $t_key => $t_config ) {
			if( in_array( $t_lang, $$t_config ) ) {
				unset( $t_check[$t_key] );
			}
		}

		# Report errors
		if( $t_check ) {
			$this->logWarn( "'$t_lang' language is not defined in "
				. implode(', ', $t_check )
			);
		}
	}

	/**
	 * Reset translation state before new English reference file loading.
	 *
	 * @return void
	 */
	 public static function resetVars() {
		self::$basevariables = [];
	}

	/**
	 * Check Language File Tokens
	 *
	 * @return boolean
	 */
	protected function checkTokens() {
		global $t_show_untranslated, $t_show_translation_errors;

		$t_source = file_get_contents( $this->file );
		try {
			$t_tokens = token_get_all( $t_source, TOKEN_PARSE );
		} catch( ParseError $e ) {
			$this->logFail( $e->getMessage(), $e->getLine() );
			return false;
		}

		$t_line = 0;
		$t_variables = array();
		$t_current_var = null;
		$t_last_token = 0;
		$t_set_variable = false;
		$t_variable_array = false;
		$t_two_part_string = false;
		$t_need_end_variable = false;
		$t_expect_end_array = false;
		$t_expect_double_quote = false;
		$t_setting_variable = false;
		$t_pass = true;
		$t_fatal = false;

		# Safe variables are ignored during argument checks
		$t_safe_variables = [
			'$s_percentage_fixed',
			'$s_percentage_errors',
			'$s_issue_status_percentage',
		];

		# Optional variables may be omitted in the translation
		$t_optional_variables = [
			# known optional variables, marked {{Optional}}
			'$s_directionality',
			'$s_p',
			'$s_priority_abbreviation',
			'$s_kib',
			'$s_phpmailer_language',
			'$s_sponsorship_process_url',
			'$s_word_separator',
			'$s_label',
			# known empty variables
			'$s_dropzone_fallback_text',
			'$s_dropzone_remove_file_confirmation',
		];

		# Reset translation state for a new run
		if( $this->is_base_language ) {
			self::resetVars();
		} else {
			foreach( self::$basevariables as & $t_var ) {
				if( isset( $t_var['translated'] ) ) {
					$t_var['translated'] = false;
				}
			}
		}

		foreach( $t_tokens as $t_token ) {
			$t_last_token2 = 0;
			if( is_string( $t_token ) ) {
				switch( $t_token ) {
					case '=':
						#
						if( $t_last_token != T_VARIABLE ) {
							$this->logFail( "'=' sign without variable", $t_line );
							$t_pass = false;
						}
						$t_set_variable = true;
						break;
					case '[':
						if( $t_last_token != T_VARIABLE ) {
							$this->logFail( "Unexpected opening square bracket '['", $t_line);
							$t_pass = false;
						}
						$t_variable_array = true;
						break;
					case ']':
						if( !$t_expect_end_array ) {
							$this->logFail( "Unexpected closing square bracket ']'", $t_line);
							$t_pass = false;
						}
						$t_expect_end_array = false;
						$t_variable_array = false;
						break;
					case ';':
						if( !$t_need_end_variable ) {
							$this->logFail( "Unexpected semicolon", $t_line );
							$t_pass = false;
						}
						$t_need_end_variable = false;
						break;
					case '.':
						if( $t_last_token == T_CONSTANT_ENCAPSED_STRING ) {
							$t_two_part_string = true;
						} else {
							$this->logFail( "String concatenation found at unexpected location", $t_line );
							$t_pass = false;
						}
						break;
					case '"':
						$t_expect_double_quote = !$t_expect_double_quote;
						break;
					default:
						$this->logFail( "Unknown token '$t_token'", $t_line );
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
					$this->logFail( "Unexpected " . token_name( $t_id ) . " token '$t_text'", $t_line );
					$t_pass = false;
				}

				switch( $t_id ) {
					case T_OPEN_TAG:
						# We expect an initial <?php tag, but it does not require any processing
						break;
					case T_INLINE_HTML:
						$this->logFail( 'Whitespace in language file outside of PHP code block', $t_line );
						$t_pass = false;
						break;
					case T_VARIABLE:
						if( $t_expect_double_quote ) {
							$this->logFail( "Unexpected " . token_name( $t_id ) . " token '$t_text'", $t_line );
							break;
						}
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
							$t_current_var .= '[' . $t_text . ']';
							if( !defined( $t_text ) ) {
								$this->logFail( "undefined constant: '$t_text'", $t_line );
							}
						} else {
							$this->logFail( "T_STRING token found at unexpected location", $t_line );
							$t_pass = false;
						}
						if( strpos( $t_current_var, "\n" ) !== false ) {
							$this->logFail( "NEW LINE in string: $t_id " . token_name( $t_id ) . " = $t_text", $t_line );
							$t_pass = false;
							$t_fatal = true;
						}
						$t_last_token2 = T_VARIABLE;
						$t_expect_end_array = true;
						break;
					case T_CONSTANT_ENCAPSED_STRING:
						if( $t_text[0] != '\'' ) {
							$this->logWarn( "Language strings should be single-quoted", $t_line );
						}
						if( $t_variable_array ) {
							$t_current_var .= $t_text;
							$t_last_token2 = T_VARIABLE;
							$t_expect_end_array = true;
							break;
						}

						if( $t_last_token == T_VARIABLE && $t_set_variable && $t_current_var != null ) {
							if( isset( $t_variables[$t_current_var] ) ) {
								$this->logFail( "Duplicate language string '$t_current_var'", $t_line );
							} else {
								$t_variables[$t_current_var] = $t_text;
							}

							if( $this->is_base_language ) {
								self::$basevariables[$t_current_var]['translated'] = false;
								self::$basevariables[$t_current_var]['text'] = $t_text;
							} elseif( !isset( self::$basevariables[$t_current_var] ) ) {
								$this->logWarn( "String '$t_current_var' not defined in English language file" );
							}

						}
						if( strpos( $t_current_var, "\n" ) !== false ) {
							$this->logFail( "NEW LINE in string: $t_id " . token_name( $t_id ) . " = $t_text", $t_line );
							$t_pass = false;
							$t_fatal = true;
						}

						# Perform HTML tags validation if the string contains any
						if( ( $t_show_translation_errors || $this->is_base_language )
							&& $this::canDoHtmlChecks()
							&& preg_match( '~</?[[:alpha:]][[:alnum:]]*>~iU', $t_text )
						) {
							/** @noinspection PhpComposerExtensionStubsInspection */
							$t_dom = new DOMDocument();
							set_error_handler(
								function( $p_type, $p_error ) {
									$t_msg = preg_replace( '/^DOM.*: (.*), line.*$/U', '\\1', $p_error );
									/** @noinspection PhpUnhandledExceptionInspection See try/catch block below*/
									throw new Exception( $t_msg );
								},
								E_WARNING
							);
							try {
								/** @noinspection PhpComposerExtensionStubsInspection */
								$t_dom->loadHTML( $t_text, LIBXML_HTML_NOIMPLIED );
							}
							catch( Exception $e ) {
								$this->logWarn( $e->getMessage() . " for string $t_current_var", $t_line );
							}
							restore_error_handler();

							$this->checkInvalidTags( $t_current_var, $t_text, $t_line );
						}

						if( ( $t_show_translation_errors || $this->is_base_language )
							# ignoring known safe variables
							&& !in_array( $t_current_var, $t_safe_variables )
						) {
							# Validate the argument format
							$t_arg_pattern = '(?:\d+\$)?[-+0 ]?(?:\'.)?(?:\d+|\*)?(?:\.(?:\d+|\*))?[bcdeEfFgGhHosuxX]';
							if( preg_match( '/%(?!' . $t_arg_pattern . ')/', str_replace( '%%', '', $t_text ) ) ) {
								$this->logFail( $this->url( $t_current_var ) . ' contains unknown format specifier(s)'
									. ( !$this->hasUrl()
										? ':<ul><li>Text: ' . string_attribute( $t_text ) . '</li></ul>'
										: '.'
									  ), $t_line, false );
							} elseif( $t_show_translation_errors ) {
								# Validate the argument count and type
								$t_args = [];
								preg_match_all( '/(%' . $t_arg_pattern . ')/', $t_text, $t_args );
								if( isset( $t_args[1] ) ) {
									$t_args = $t_args[1];
									sort( $t_args );
								} else {
									$t_args = [];
								}
								if( $this->is_base_language ) {
									self::$basevariables[$t_current_var]['arguments'] = $t_args;
								} elseif( isset( self::$basevariables[$t_current_var]['arguments'] ) ) {
									$t_count = count( $t_args );
									$t_error = ( $t_count != count( self::$basevariables[$t_current_var]['arguments'] ) );
									if( !$t_error ){
										for( $t_index = 0; $t_index < $t_count; ++$t_index ) {
											if( $t_args[$t_index] !== self::$basevariables[$t_current_var]['arguments'][$t_index] ) {
												$t_error = true;
											}
										}
									}
									if( $t_error ) {
										$this->logFail( $this->url( $t_current_var ) . ' contains inconsistent argument(s)'
											. ( !$this->hasUrl()
												? ':<ul><li>English text: '
													. string_attribute( self::$basevariables[$t_current_var]['text'] )
													. '</li><li>Translated text: '
													. string_attribute( $t_text ) . '</li></ul>'
												: '.'
											  ), $t_line, false );
									}
								}
							}
						}

						if( !$this->is_base_language && isset( self::$basevariables[$t_current_var]['translated'] ) ) {
							self::$basevariables[$t_current_var]['translated'] = true;
						}

						$t_current_var = null;
						$t_need_end_variable = true;
						break;
					default:
						$this->logFail( "Unexpected " . token_name( $t_id ) . " token '$t_text'", $t_line );
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

		# Report untranslated strings
		if( $t_show_untranslated && !$this->is_base_language && !$t_fatal ) {
			$t_untranslated = 0;
			foreach( self::$basevariables as $t_name => $t_var ){
				if( isset( $t_var['translated'] )
					&& $t_var['translated'] === false
					&& !in_array( $t_name, $t_optional_variables )
				) {
					if( ++$t_untranslated <= 3 ) {
						$this->logWarn( 'Untranslated ' . $this->url( $t_name ), 0, false );
					}
				}
			}
			if( ( $t_untranslated -= 3 ) > 0 ) {
				$this->logWarn( "... and $t_untranslated more." );
			}
		}

		if( $this->is_base_language ) {
			ksort( self::$basevariables );
		}

		return $t_pass;
	}

	/**
	 * Use HTML Purifier to ensure the string does not contain unsupported tags.
	 *
	 * Will log an error if the purified string is not equal to the original.
	 * @see LangCheckFile::compareToPurified()
	 *
	 * @param string $p_var  Name of language string variable to check
	 * @param string $p_text Language string
	 * @param int    $p_line Line number in language file
	 *
	 * @return void
	 */
	private function checkInvalidTags( $p_var, $p_text, $p_line ) {
		$t_pure = $this->purifier->purify( $p_text );

		# Special cases handling
		# - sprintf variable '%1$s' inside href gets urlencoded
		if( $p_var == '$s_webmaster_contact_information' ) {
			$t_pure = str_replace( '%25', '%', $t_pure );
		}

		# Prepare original language string for comparison with HTML Purifier output
		# - transform <br> tag with or without trailing '/' into '<br />'
		$p_text = preg_replace( '#<br\s*/?>#i', '<br />', $p_text );

		if( $t_pure != $p_text ) {
			$this->logFail( $this->url( $p_var )
				. " contains unsupported or invalid tags or attributes.", $p_line, false );
		}
	}

	/**
	 * Returns the direct URL to the translation of variables.
	 *
	 * @param string $p_var Name of language string variable
	 *
	 * @return string
	 */
	private function url( $p_var ) {
		return $this->hasUrl()
			? '<a href="https://translatewiki.net/w/i.php?'
				. http_build_query( [
					'title' => 'Special:Translate',
					'group' => $this->group,
					'showMessage' => str_replace( [ '$', '[', ']' ], [ '', '\x5b', '\x5d' ], $p_var ),
					'language' => $this->lang,
				] ) . '">' . string_attribute( $p_var ). '</a>'
			: string_attribute( $p_var );
	}

	/**
	 * Detects that the translation of variables is performed by an external service.
	 *
	 * @return bool
	 */
	private function hasUrl() {
		$t_external_groups = [
			'out-mantis-core',
			'out-mantis-plugin-gravatar',
			'out-mantis-plugin-mantiscoreformatting',
			'out-mantis-plugin-mantisgraph',
			'out-mantis-plugin-xmlimportexport',
		];
		return in_array( $this->group, $t_external_groups );
	}
}

access_ensure_global_level( config_get_global( 'admin_site_threshold' ) );

lang_push( 'english' );

set_time_limit( 0 );

layout_page_header( 'MantisBT Administration - Test Lang' );
layout_admin_page_begin();
print_admin_menu_bar( 'test_langs.php' );
?>

<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>

	<!-- CORE LANGUAGE FILES -->
	<div id="core" class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<?php print_icon( 'fa-text-width', 'ace-icon' ); ?>
				Testing Core Language Files
			</h4>
			<div class="widget-toolbar no-border hidden-xs">
				<div class="widget-menu">
					<?php print_extra_small_button( '#plugins', 'Scroll down to Plugins'); ?>
				</div>
			</div>
		</div>

		<div class="widget-body">
			<div class="widget-toolbox padding-8 clearfix">
				Verbosity:
				<a href="<?php echo mode_url( !$t_show_untranslated, $t_show_translation_errors ) ?>"><?php echo $t_show_untranslated ? 'Hide' : 'Show' ?> untranslated strings</a> |
				<a href="<?php echo mode_url( $t_show_untranslated, !$t_show_translation_errors ) ?>"><?php echo $t_show_translation_errors ? 'Hide' : 'Show' ?> translation errors</a>
			</div>
			<div class="widget-main no-padding table-responsive">
				<table class="table table-bordered table-condensed test-langs">
<?php
if( !LangCheckFile::canDoHtmlChecks() ) {
?>
					<tr>
						<td>
							Check for required <em>DOM</em> and <em>libxml</em>
							PHP extensions
						</td>
						<td class="alert-warning">HTML syntax checks will not be performed.
							<br>
							NOTE: This warning applies to all languages and plugins
							tested on this page.
						</td>
					</tr>
<?php
}

checklangdir( $t_mantis_dir, 'out-mantis-core' );
?>
				</table>
			</div>
		</div>
	</div>

	<div class="space-10"></div>

	<!-- PLUGINS -->
	<div id="plugins" class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<?php print_icon( 'fa-text-width', 'ace-icon' ); ?>
				Testing Plugins Language Files
			</h4>
			<div class="widget-toolbar no-border hidden-xs">
				<div class="widget-menu">
					<?php print_extra_small_button( '#', 'Scroll back to top'); ?>
				</div>
			</div>
		</div>

		<div class="widget-body">
			<div class="widget-main no-padding table-responsive">
				<table class="table table-bordered table-condensed test-langs">
<?php
checkplugins();
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
 */
function checkplugins() {
	$t_path = config_get_global( 'plugin_path' );
	echo '<tr><td>';
	echo "Retrieving Plugins from '$t_path'";
	echo '</td>';

	try {
		$t_plugins = get_plugins( $t_path );
		$t_toc = '<ol class="plugins-toc">';
		foreach( $t_plugins as $t_plugin => $t_path ) {
			$t_toc .= '<li><a href="#plugin-' . $t_plugin . '">' . $t_plugin . '</a></li>';
		}
		$t_toc .= '</ol>';
		print_info( count( $t_plugins ) . " Plugins found" . $t_toc );
	} catch( UnexpectedValueException $e ) {
		print_fail( $e->getMessage() );
		echo '</tr>' . PHP_EOL;
		return;
	}
	echo '</tr>' . PHP_EOL;

	foreach( $t_plugins as $t_plugin => $t_path ) {
		echo PHP_EOL;
		echo '<tr id="plugin-' . $t_plugin . '"><th colspan="2">';
		echo "Checking language files for plugin <em>$t_plugin</em>";
		echo '</th></tr>';
		checklangdir( $t_path, 'out-mantis-plugin-' . strtolower( $t_plugin ) );
	}
}

/**
 * Get the list of plugins.
 *
 * @param string $p_path
 * @return string[] Plugin names in ascending order.
 *
 * @throws UnexpectedValueException
 */
function get_plugins( $p_path ) {
	$t_iter = new CallbackFilterIterator(
		new FileSystemIterator(
			$p_path,
			FileSystemIterator::SKIP_DOTS | FileSystemIterator::KEY_AS_FILENAME
		),
		/**
		 * Callback filter function
		 * @param SplFileInfo $p_current
		 * @return bool
		 */
		function( SplFileInfo $p_current ) {
			return $p_current->isDir();
		}
	);

	$t_plugins = iterator_to_array( $t_iter );
	ksort( $t_plugins, SORT_FLAG_CASE );
	return $t_plugins;
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
		'|^strings_(?(?!qqq).+)\.txt$|',
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
 * @param string $p_path  Path to language files.
 * @param string $p_group Group of translations
 *
 * @return void
 */
function checklangdir( $p_path, $p_group ) {
	# Use DIRECTORY_SEPARATOR to ensure a consistent display of the directory on Windows
	$t_path = rtrim( $p_path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR;
	echo PHP_EOL . '<tr><td>';
	echo "Retrieving language files from '$t_path'";
	echo '</td>';

	if( !is_dir( $t_path ) ) {
		print_info( "Directory does not exist" );
		echo '</tr>' . PHP_EOL;
		return;
	} else {
		try {
			$t_lang_files = get_lang_files( $t_path );
			print_info( count( $t_lang_files ) . " files found" );
		} catch( UnexpectedValueException $e ) {
			print_fail( $e->getMessage() );
			echo '</tr>' . PHP_EOL;
			return;
		}
	}

	echo '</tr>' . PHP_EOL;

	LangCheckFile::resetVars();

	# Check reference English language file
	$t_key = array_search( LangCheckFile::BASE, $t_lang_files );
	if( $t_key === false ) {
		echo '<tr><td></td>';
		print_fail( "Reference English file not found" );
		echo '</tr>' . PHP_EOL;
	} else {
		$t_file = new LangCheckFile( $t_path, LangCheckFile::BASE, $p_group );
		# No point testing other languages if English fails
		if( !$t_file->checkAndPrint() ) {
			echo '<tr><td></td>';
			print_fail( 'Total error(s): '. $t_file->countErrors() );
			echo '</tr>' . PHP_EOL;
			return;
		}
		unset( $t_lang_files[$t_key] );
	}

	# Check foreign language files
	$t_errors = 0;
	foreach( $t_lang_files as $t_lang ) {
		$t_file = new LangCheckFile( $t_path, $t_lang, $p_group );
		$t_file->checkAndPrint();
		$t_errors += $t_file->countErrors();
	}

	if( $t_errors ) {
		echo '<tr><td></td>';
		print_fail( 'Total error(s): ' . $t_errors );
		echo '</tr>' . PHP_EOL;
	}
}

function print_info( $p_message ) {
	echo '<td class="alert-info">', ( $p_message ), '</td>';
}

function print_fail( $p_message ) {
	echo '<td class="alert-danger">', string_attribute( $p_message ), '</td>';
}
