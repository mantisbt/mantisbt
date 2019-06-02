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
 * Tests configuration
 * @package Tests
 * @subpackage UnitTests
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

# Start output buffering
ob_start();

# Include PHPUnit dependencies ; ensure compatibility with 3.5 and 3.6
@include_once 'PHPUnit/Framework.php';

/**
 * Parse file and retrieve distinct T_VARIABLE tokens with 'g_' prefix
 *
 * @param string $p_file      Configuration filename.
 * @param array  &$p_var_list An array of variables to update.
 * @return boolean false if file can't be parsed
 */
function parse_config_global_vars( $p_file, array &$p_var_list ) {
	# Parse the file
	$t_contents = file_get_contents( $p_file, true );
	if( false === $t_contents ) {
		return false;
	}
	$t_tokens = token_get_all( $t_contents );

	if( !is_array( $p_var_list ) ) {
		$p_var_list = array();
	}

	# Store all distinct T_VARIABLE tokens with 'g_' prefix
	foreach( $t_tokens as $t ) {
		if( is_array( $t ) && $t[0] == T_VARIABLE ) {
			$t_var = ltrim( $t[1], '$' );
			if( substr( $t_var, 0, 2 ) == 'g_' ) {
				$p_var_list[$t_var] = $t_var;
			}
		}
	}

	return true;
}

/**
 * Initializes MantisBT core and bypasses the http headers for PHPUnit tests
 *
 * When the Mantis Core is needed for Unit Tests, this function should
 * be called instead of a standard "require_once( 'core.php' );"
 *
 * This is required because when running PHPUnit, config_defaults_inc.php is
 * not in the global scope, therefore 'global' variables are not properly
 * initialized.
 *
 * @return void
 */
function require_mantis_core() {
	$t_var_list = array();
	parse_config_global_vars( 'config_defaults_inc.php', $t_var_list );
	parse_config_global_vars( 'config/config_inc.php', $t_var_list );

	# HTTP headers bypass
	$t_bypass_headers = 'g_bypass_headers';
	$t_var_list[] = $t_bypass_headers;

	# Global declaration for all variables
	$t_decl = '';
	foreach( $t_var_list as $t_var ) {
		global $$t_var;
		$t_decl .= 'global ' . $t_var . ";\n";
	}

	$$t_bypass_headers = true;
	require_once( 'core.php' );

	# We need to disable MantisBT's error handler to allow PHPUnit to convert
	# errors to exceptions, allowing us to capture and test them.
	restore_error_handler();
}


# Set error reporting to the level to which Zend Framework code must comply.
error_reporting( E_ALL | E_STRICT );

# Determine the root, library, and tests directories of the framework
# distribution.
$g_mantisRoot = dirname( dirname( __FILE__ ) );
$g_mantisCore = $g_mantisRoot . '/core';
$g_mantisLibrary = $g_mantisRoot . '/library';
$g_mantisClasses = $g_mantisRoot . '/core/classes';
$g_mantisTests = $g_mantisRoot . '/tests';


# Prepend the application/ and tests/ directories to the include_path.
$g_path = array(
	$g_mantisRoot,
	$g_mantisCore,
	$g_mantisLibrary,
	$g_mantisClasses,
	get_include_path()
);
set_include_path( implode( PATH_SEPARATOR, $g_path ) );

# Unset global variables that are no longer needed.
unset($g_mantisRoot, $g_mantisLibrary, $g_mantisTests, $g_path);
