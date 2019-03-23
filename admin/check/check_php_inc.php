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
 * This file contains configuration checks for php issues
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses check_api.php
 * @uses config_api.php
 * @uses utility_api.php
 */

if( !defined( 'CHECK_PHP_INC_ALLOW' ) ) {
	return;
}

# MantisBT Check API
require_once( 'check_api.php' );
require_api( 'config_api.php' );
require_api( 'utility_api.php' );

check_print_section_header_row( 'PHP' );

check_print_test_row(
	'Version of <a href="http://en.wikipedia.org/wiki/PHP">PHP</a> installed is at least ' . PHP_MIN_VERSION,
	version_compare( phpversion(), PHP_MIN_VERSION, '>=' ),
	'PHP version ' . phpversion() . ' is currently installed on this server.'
);

# $t_extensions_required lists the extensions required to run Mantis in general
$t_extensions_required = array(
	'date',
	'hash',
	'pcre',
	'Reflection',
	'session',
	'mbstring'
);

foreach( $t_extensions_required as $t_extension ) {
	check_print_test_row(
		$t_extension . ' PHP extension is available',
		extension_loaded( $t_extension ),
		array( false => 'MantisBT requires the ' . $t_extension . ' extension to either be compiled into PHP or loaded as an extension.' )
	);
}

$t_fileinfo_loaded = extension_loaded( 'fileinfo' );

if( config_get_global( 'allow_file_upload' ) ) {
	check_print_test_row(
		'Fileinfo PHP extension is available to support file uploads',
		$t_fileinfo_loaded,
		array( false => 'Ensure that the fileinfo extension is installed and enabled' )
	);
} else {
	# most of the plugins need this extensions as they use functions plugin_file /  plugin_file_include
	check_print_test_warn_row(
		'Fileinfo PHP extension is available to support plugins',
		$t_fileinfo_loaded,
		array( false => 'Ensure that the fileinfo extension is installed and enabled' )
	);
}

if ( $t_fileinfo_loaded ) {
	$t_fileinfo_magic_db_file = config_get_global( 'fileinfo_magic_db_file' );
	if( $t_fileinfo_magic_db_file ) {
		check_print_info_row(
			'Name of magic.db file set with the fileinfo_magic_db_file configuration value',
			config_get_global( 'fileinfo_magic_db_file' ) );
		check_print_test_row(
			'fileinfo_magic_db_file configuration value points to an existing magic.db file',
			file_exists( $t_fileinfo_magic_db_file ) );
		$t_finfo = new finfo( FILEINFO_MIME, $t_fileinfo_magic_db_file );
	} else {
		$t_finfo = new finfo( FILEINFO_MIME );
	}
	check_print_test_row(
		'Fileinfo extension can find and load a valid magic.db file',
		$t_finfo !== false,
		array( false => 'Ensure that the fileinfo_magic_db_file configuration value points to a valid magic.db file.' )
	);
}

check_print_test_warn_row(
	'<a href="http://en.wikipedia.org/wiki/Xdebug">Xdebug</a> extension is not loaded',
	!extension_loaded( 'xdebug' ),
	array( false => 'For security reasons this extension should not be loaded on production and Internet facing servers.' )
);

$t_variables_order = ini_get( 'variables_order' );
check_print_test_row(
	'variables_order php.ini directive contains GPCS',
	stripos( $t_variables_order, 'G' ) !== false &&
		stripos( $t_variables_order, 'P' ) !== false &&
		stripos( $t_variables_order, 'C' ) !== false &&
		stripos( $t_variables_order, 'S' ) !== false,
	array( false => 'The value of this directive is currently: ' . $t_variables_order )
);

check_print_test_row(
	'magic_quotes_gpc php.ini directive is disabled',
	!( function_exists( 'get_magic_quotes_gpc' ) && @get_magic_quotes_gpc() ),
	array( false => 'PHP\'s magic quotes feature is <a href="http://www.php.net/manual/en/security.magicquotes.whynot.php">deprecated in PHP 5.3.0</a> and should not be used.' )
);

check_print_test_row(
	'magic_quotes_runtime php.ini directive is disabled',
	!( function_exists( 'get_magic_quotes_runtime' ) && @get_magic_quotes_runtime() ),
	array( false => 'PHP\'s magic quotes feature is <a href="http://www.php.net/manual/en/security.magicquotes.whynot.php">deprecated in PHP 5.3.0</a> and should not be used.' )
);

check_print_test_warn_row(
	'register_argc_argv php.ini directive is disabled',
	!ini_get_bool( 'register_argc_argv' ),
	array( false => 'This directive should be disabled to increase performance (it only affects PHP in CLI mode).' )
);

check_print_test_warn_row(
	'register_long_arrays php.ini directive is disabled',
	!ini_get_bool( 'register_long_arrays' ),
	array( false => 'This directive is deprecated in PHP 5.3.0 and should be disabled for performance reasons.' )
);

check_print_test_warn_row(
	'auto_globals_jit php.ini directive is enabled',
	ini_get_bool( 'auto_globals_jit' ),
	array( false => 'This directive is currently disabled: enable it for a performance gain.' )
);

check_print_test_warn_row(
	'display_errors php.ini directive is disabled',
	!ini_get_bool( 'display_errors' ),
	array( false => 'For security reasons this directive should be disabled on all production and Internet facing servers.' )
);

check_print_test_warn_row(
	'display_startup_errors php.ini directive is disabled',
	!ini_get_bool( 'display_startup_errors' ),
	array( false => 'For security reasons this directive should be disabled on all production and Internet facing servers.' )
);

check_print_test_warn_row(
	'PHP errors are being logged or reported',
	ini_get_bool( 'display_errors' ) || ini_get_bool( 'log_errors' ),
	array( false => 'PHP is not currently set to log or report errors and thus you may be unaware of PHP errors that occur.' )
);

check_print_info_row(
	'php.ini directive: memory_limit',
	check_format_number( ini_get_number( 'memory_limit' ) )
);

check_print_info_row(
	'php.ini directive: post_max_size',
	check_format_number( ini_get_number( 'post_max_size' ) )
);

$t_memory_limit = ini_get_number( 'memory_limit' );
check_print_test_row(
	'memory_limit php.ini directive is at least equal to the post_max_size directive',
	$t_memory_limit >= ini_get_number( 'post_max_size' ) || $t_memory_limit == -1,
	array( false => 'The current value of the memory_limit directive is ' . htmlentities( ini_get_number( 'memory_limit' ) ) . ' bytes. This value needs to be at least equal to the post_max_size directive value of ' . htmlentities( ini_get_number( 'post_max_size' ) ) . ' bytes.' )
);

check_print_info_row(
	'File uploads are enabled (php.ini directive: file_uploads)',
	ini_get_bool( 'file_uploads' ) ? 'Yes' : 'No'
);

check_print_info_row(
	'php.ini directive: upload_max_filesize',
	check_format_number( ini_get_number( 'upload_max_filesize' ) )
);

check_print_test_row(
	'post_max_size php.ini directive is at least equal to the upload_max_size directive',
	ini_get_number( 'post_max_size' ) >= ini_get_number( 'upload_max_filesize' ),
	array( false => 'The current value of the post_max_size directive is ' . htmlentities( ini_get_number( 'post_max_size' ) ) . ' bytes. This value needs to be at least equal to the upload_max_size directive value of ' . htmlentities( ini_get_number( 'upload_max_filesize' ) ) . ' bytes.' )
);

$t_disabled_functions = explode( ',', ini_get( 'disable_functions' ) );
foreach( $t_disabled_functions as $t_disabled_function ) {
	$t_disabled_function = trim( $t_disabled_function );
	if( $t_disabled_function
		&& substr( $t_disabled_function, 0, 6 ) != 'pcntl_'
	) {
		check_print_test_warn_row(
			'<em>' . $t_disabled_function . '</em> function is enabled',
			false,
			'This function has been disabled by the disable_functions php.ini directive. MantisBT may not operate correctly with this function disabled.' );
	}
}

$t_disabled_classes = explode( ',', ini_get( 'disable_classes' ) );
foreach( $t_disabled_classes as $t_disabled_class ) {
	$t_disabled_class = trim( $t_disabled_class );
	if( $t_disabled_class ) {
		check_print_test_warn_row(
			'<em>' . $t_disabled_class . '</em> class is enabled',
			false,
			'This class has been disabled by the disable_classes php.ini directive. MantisBT may not operate correctly with this class disabled.' );
	}
}

# Print additional information from php.ini to assist debugging (see http://www.php.net/manual/en/ini.list.php)
$t_vars = array(
	'open_basedir',
	'extension',
	'upload_tmp_dir',
	'max_file_uploads',
	'date.timezone'
);

foreach( $t_vars as $t_var ) {
	$t_value = ini_get( $t_var );
	if( $t_value != '' ) {
		check_print_info_row( 'php.ini directive: ' . $t_var, htmlentities( $t_value ) );
	}
}

check_print_test_warn_row(
	'webserver: check SCRIPT_NAME is returned to PHP by web server',
	isset( $_SERVER['SCRIPT_NAME'] ),
	array( false => 'Please ensure web server configuration sets SCRIPT_NAME' )
);
