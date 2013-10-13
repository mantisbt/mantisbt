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
 * Mantis Tests
 *
 * @package Tests
 * @subpackage UnitTests
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/*
 * Start output buffering
 */
ob_start();

/**
 * Include PHPUnit dependencies ; ensure compatibility with 3.5 and 3.6
 */
@include_once 'PHPUnit/Framework.php';


/**
 * Parse file and retrieve distinct T_VARIABLE tokens with 'g_' prefix
 * @param string $p_file
 * @param array $p_var_list
 * @return bool false if file can't be parsed
 */
function parse_config_global_vars( $p_file, &$p_var_list ) {
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
		if( is_array($t) && $t[0] == T_VARIABLE ) {
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
 */
function require_mantis_core() {
	parse_config_global_vars( 'config_defaults_inc.php', $t_var_list );
	parse_config_global_vars( 'config_inc.php', $t_var_list );

	# HTTP headers bypass
	$t_bypass_headers = 'g_bypass_headers';
	$t_var_list[] = $t_bypass_headers;

	# Global declaration for all variables
	$t_decl = '';
	foreach( $t_var_list as $v ) {
		global $$v;
		$t_decl .= "global $v;\n";
	}

	$$t_bypass_headers = true;
	require_once( 'core.php' );
}


# Set error reporting to the level to which Zend Framework code must comply.
error_reporting( E_ALL | E_STRICT );

# Determine the root, library, and tests directories of the framework
# distribution.
$mantisRoot = dirname( dirname(__FILE__) );
$mantisCore = "$mantisRoot/core";
$mantisLibrary = "$mantisRoot/library";
$mantisClasses = "$mantisRoot/core/classes";
$mantisTests = "$mantisRoot/tests";


# Prepend the application/ and tests/ directories to the include_path.
$path = array(
	$mantisRoot,
	$mantisCore,
	$mantisLibrary,
	$mantisClasses,
	get_include_path()
);
set_include_path( implode( PATH_SEPARATOR, $path ) );

# Unset global variables that are no longer needed.
unset($mantisRoot, $mantisLibrary, $mantisTests, $path);
