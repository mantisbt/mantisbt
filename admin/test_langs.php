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
$t_mantis_dir = dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR;

require_once( $t_mantis_dir . 'core.php' );

access_ensure_global_level( config_get_global( 'admin_site_threshold' ) );

if( function_exists( 'xdebug_disable' ) ) {
    xdebug_disable();
}

if( !defined( 'T_ML_COMMENT' ) ) {
    define( 'T_ML_COMMENT', T_COMMENT );
}
else {
    define( 'T_DOC_COMMENT', T_ML_COMMENT );
}

if( !checkfile( $t_mantis_dir . 'lang' . DIRECTORY_SEPARATOR, STRINGS_ENGLISH, true ) ) {
    print_error( "Language file '" . STRINGS_ENGLISH . "' failed.", 'FAILED' );
    die;
}

lang_push( 'english' );

set_time_limit( 0 );

layout_page_header();

layout_admin_page_begin();

print_admin_menu_bar( 'test_langs.php' );
?>

    <div class="col-md-12 col-xs-12">
        <div class="space-10"></div>

        <div class="widget-box widget-color-blue2">
            <div class="widget-header widget-header-small">
                <h4 class="widget-title lighter">
                    <i class="ace-icon fa fa-text-width"></i>
                    Test Langs
                </h4>
            </div>

            <div class="widget-body">
                <div class="widget-main no-padding">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-condensed table-hover">
                            <?php
                            # check core language files
                            if( function_exists( 'scandir' ) ) {
                                checklangdir( $t_mantis_dir );
                            }
                            else {
                                $t_lang_files = array();
                                foreach( $g_language_choices_arr as $t_lang ) {
                                    if( $t_lang == 'auto' ) {
                                        continue;
                                    }
                                    $t_lang_files[] = $t_lang;
                                }
                                asort( $t_lang_files );
                                checklangdir( $t_mantis_dir, $t_lang_files );
                            }


                            # attempt to find plugin language files
                            echo '<tr><td>';
                            echo "Trying to find+check plugin language files...<br />";
                            if( function_exists( 'scandir' ) ) {
                                checkplugins( config_get( 'plugin_path' ) );
                            } else {
                                echo 'php scandir is disabled - skipping<br />';
                            }
                            echo '</td></tr>';
                            ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
/**
 * Check plugin language files
 * @param string $p_path Plugin path.
 * @return void
 */
function checkplugins( $p_path ) {
    $t_path = rtrim( $p_path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;

    $t_plugins = @scandir( $t_path );
    if( false == $t_plugins ) {
        print_error( "plugin path $t_path not found or not accessible" );
    }
    else {
        foreach( $t_plugins as $t_plugin ) {
            if( $t_plugin[0] == '.' ) {
                continue;
            }
            echo '<tr><td>';
            echo "Checking language files for plugin $t_plugin:<br />";
            checklangdir( $t_path . $t_plugin );
            echo '</td></tr>';
        }
    }
}

/**
 * Check directory of language files
 *
 * @param string $p_path       Path.
 * @param string $p_lang_files Sub path.
 * @return void
 */
function checklangdir( $p_path, $p_lang_files = null ) {
    $t_path = rtrim( $p_path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR;

    if( is_array( $p_lang_files ) ) {
        $t_lang_files = $p_lang_files;
    }
    else {
        $t_lang_files = @scandir( $t_path );
    }
    if( false == $t_lang_files ) {
        print_error( "language dir $t_path not found or not accessible" );
    }
    else {
        if( in_array( STRINGS_ENGLISH, $t_lang_files ) ) {
            echo '<tr><td>';
            echo "Testing English language file...<br />";
            flush();
            echo '</td></tr>';
            echo '<tr><td>';
            checkfile( $t_path, STRINGS_ENGLISH );
            echo '</td></tr>';
        }
        # Skipping english language, readme and hidden files
        foreach( $t_lang_files as $key => $t_lang ) {
            if( $t_lang[0] == '.'
                || $t_lang == 'langreadme.txt'
                || $t_lang == 'README'
                || $t_lang == STRINGS_ENGLISH
            ) {
                unset( $t_lang_files[$key] );
            }
        }
        if( !empty( $t_lang_files ) ) {
            echo '<tr><td>';
            echo 'Retrieved ', count( $t_lang_files ), ' languages<br />';
            echo '</td></tr>';
            foreach( $t_lang_files as $t_lang ) {
                echo '<tr><td>';
                checkfile( $t_path, $t_lang );
                echo '</td></tr>';
            }
        }
    }
}

/**
 * Check Language File
 *
 * @param string  $p_path  Path.
 * @param string  $p_file  File.
 * @param boolean $p_quiet Quiet output.
 * @return boolean
 */
function checkfile( $p_path, $p_file, $p_quiet = false ) {
    if( !$p_quiet ) {
        echo "Testing language file '$p_file' (phase 1)...<br />";
        flush();
    }

    $file = $p_path . $p_file;

    set_error_handler( 'lang_error_handler' );
    $t_result = checktoken( $file, ($p_file == STRINGS_ENGLISH ) );
    restore_error_handler();

    if( !$t_result ) {
        print_error( "Language file '$p_file' failed at phase 1.", 'FAILED' );
        if( $p_quiet ) {
            return false;
        }
    }

    if( !$p_quiet ) {
        echo "Testing language file '$p_file' (phase 2)...<br />";
        flush();
    } else {
        return true;
    }

    set_error_handler( 'lang_error_handler' );
    ob_start();
    $t_result = eval( "require_once( '$file' );" );
    $t_data = ob_get_contents();
    ob_end_clean();
    restore_error_handler();

    if( $t_result === false ) {
        print_error( "Language file '$p_file' failed at eval", 'FAILED' );
        if( $p_quiet ) {
            return false;
        }
    }

    if( !empty( $t_data ) ) {
        print_error( "Language file '$p_file' failed at require_once (data output: " . var_export( $t_data, true ) . ")", 'FAILED' );
        if( $p_quiet ) {
            return false;
        }
    }
    return true;
}

/**
 * Check Language File Tokens
 *
 * @param string  $file Language file to tokenize.
 * @param boolean $base Whether language file is default (aka english).
 * @return boolean
 */
function checktoken( $file, $base = false ) {
    $in_php_code = false;
    $variables = array();
    static $s_basevariables;
    $t_current_var = null;
    $last_token = 0;
    $set_variable = false;
    $variablearr = false;
    $twopartstring = false;
    $need_end_variable = false;
    $source = file_get_contents( $file );
    $tokens = @token_get_all( $source );
    $expectendarr = false;
    $settingvariable = false;
    $t_pass = true;
    $t_fatal = false;
    foreach( $tokens as $token ) {
        $last_token2 = 0;
        if( is_string( $token ) ) {
            switch( $token ) {
                case '=':
                    if( $last_token != T_VARIABLE ) {
                        print_error( "'=' sign without variable (line $t_line)" );
                        $t_pass = false;
                    }
                    $set_variable = true;
                    break;
                case '[':
                    if( $last_token != T_VARIABLE ) {
                        $t_pass = false;
                    }
                    $variablearr = true;
                    break;
                case ']':
                    if( !$expectendarr ) {
                        $t_pass = false;
                    }
                    $expectendarr = false;
                    $variablearr = false;
                    break;
                case ';':
                    if( !$need_end_variable ) {
                        print_error( "function separator found at unexpected location (line $t_line)" );
                        $t_pass = false;
                    }
                    $need_end_variable = false;
                    break;
                case '.':
                    if( $last_token == T_CONSTANT_ENCAPSED_STRING ) {
                        $twopartstring = true;
                    } else {
                        print_error( "string concatenation found at unexpected location (line $t_line)" );
                        $t_pass = false;
                    }
                    break;
                default:
                    print_error( "unknown token $token" );
                    $t_pass = false;
                    break;
            }
        } else {
            # token array
            list( $id, $text, $t_line ) = $token;

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

                print_error( "token# $id: " . token_name( $id ) . " = $text (line $t_line)" );
                $t_pass = false;
            }

            switch( $id ) {
                case T_OPEN_TAG:
                    $in_php_code = true;
                    break;
                case T_CLOSE_TAG:
                    $in_php_code = false;
                    break;
                case T_INLINE_HTML:
                    print_error( "Whitespace in language file outside of PHP code block (line $t_line)" );
                    $t_pass = false;
                    break;
                case T_VARIABLE:
                    if( $set_variable && $t_current_var != null ) {
                        $need_end_variable = true;
                        $settingvariable = true;
                        $t_current_var = null;
                        break;
                    }
                    $t_current_var = $text;
                    break;
                case T_STRING:
                    if( $variablearr ) {
                        $t_current_var .= $text;
                        if( !defined( $text ) ) {
                            print_error( "undefined constant: $text (line $t_line)" );
                        }
                    } else {
                        print_error( "T_STRING found at unexpected location (line $t_line)" );
                        $t_pass = false;
                    }
                    if( strpos( $t_current_var, "\n" ) !== false ) {
                        print_error( "NEW LINE in string: $id " . token_name( $id ) . " = $text (line $t_line)", 'PARSER' );
                        $t_pass = false;
                        $t_fatal = true;
                    }
                    $last_token2 = T_VARIABLE;
                    $expectendarr = true;
                    break;
                case T_CONSTANT_ENCAPSED_STRING:
                    if( $token[1][0] != '\'' ) {
                        print_error( "Language strings should be single-quoted (line $t_line)" );
                    }
                    if( $variablearr ) {
                        $t_current_var .= $text;
                        $last_token2 = T_VARIABLE;
                        $expectendarr = true;
                        break;
                    }

                    if( $last_token == T_VARIABLE && $set_variable && $t_current_var != null ) {
                        if( isset( $variables[$t_current_var] ) ) {
                            print_error( "duplicate language string ($t_current_var ) (line $t_line)" );
                        } else {
                            $variables[$t_current_var] = $text;
                        }

                        if( $base ) {
                            # english
                            #if( isset( $s_basevariables[$t_current_var] ) ) {
                            #	print_error( "WARN: english string redefined - plugin? $t_current_var" );
                            #}
                            $s_basevariables[$t_current_var] = true;
                        } else {
                            if( !isset( $s_basevariables[$t_current_var] ) ) {
                                print_error( "'$t_current_var' is not defined in the English language file", 'WARNING' );
                                #} else {
                                #  missing translation
                            }
                        }

                    }
                    if( strpos( $t_current_var, "\n" ) !== false ) {
                        print_error( "NEW LINE in string: $id " . token_name( $id ) . " = $text (line $t_line)", 'PARSER' );
                        $t_pass = false;
                        $t_fatal = true;
                    }
                    $t_current_var = null;
                    $need_end_variable = true;
                    break;
                default:
                    print_error( "$id " . token_name( $id ) . " = $text (line $t_line)", 'PARSER' );
                    $t_pass = false;
                    break;
            }

            $last_token = $id;
            if( $last_token2 > 0 ) {
                $last_token = $last_token2;
            }
        }

        if( $t_fatal ) {
            break;
        }
    }

    return $t_pass;
}

/**
 * Error handler for language file checks
 * @param integer $p_type    Error type.
 * @param string  $p_error   Error code.
 * @param string  $p_file    File error occurred in.
 * @param integer $p_line    Line number error occurred on.
 * @param string  $p_context Context of error.
 * @return void
 */
function lang_error_handler( $p_type, $p_error, $p_file, $p_line, $p_context ) {
    print_error( "error handler thrown: " . $p_type . '<br />' . $p_error . '<br />' . $p_file . '<br />' . $p_line . '<br />' . $p_context );
}

/**
 * Print Language File error
 *
 * @param string $p_string Error string.
 * @param string $p_type   Message type to display (default ERROR).
 * @return void
 */
function print_error( $p_string, $p_type = 'ERROR' ) {
    echo '<p class="error-msg">', "$p_type: $p_string", '</p>';
}

layout_admin_page_end();
