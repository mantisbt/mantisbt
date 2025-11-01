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
 * This file contains configuration checks for database issues
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses check_api.php
 * @uses config_api.php
 * @uses database_api.php
 * @uses helper_api.php
 * @uses utility_api.php
 */

use Mantis\admin\check\EndOfLifeCheck;

if( !defined( 'CHECK_DATABASE_INC_ALLOW' ) ) {
	return;
}

# MantisBT Check API
require_once( 'check_api.php' );
require_api( 'config_api.php' );
require_api( 'database_api.php' );
require_api( 'helper_api.php' );
require_api( 'utility_api.php' );

check_print_section_header_row( 'Database' );

$t_adodb_version_check_ok = false;
$t_adodb_version_info = 'No version of ADOdb could be found. This is a compulsory dependency of MantisBT.';
if( isset( $ADODB_vers ) ) {
	# ADOConnection::Version() is broken as it treats v5.1 the same as v5.10
	# Therefore we must extract the correct version ourselves
	# Upstream bug report: http://phplens.com/lens/lensforum/msgs.php?id=18320
	# This bug has been fixed in ADOdb 5.11 (May 5, 2010) but we still
	# need to use the backwards compatible approach to detect ADOdb <5.11.
	if( preg_match( '/^[Vv]([0-9.]+)/', $ADODB_vers, $t_matches ) == 1 ) {
		$t_adodb_version_check_ok = version_compare( $t_matches[1], DB_MIN_VERSION_ADODB, '>=' );
		$t_adodb_version_info = 'ADOdb version ' . htmlentities( $t_matches[1] ) . ' was found.';
	}

	# Making sure we're not using the ADOdb extension (see #14552)
	check_print_test_row(
		'Making sure the legacy ADOdb extension is not being used',
		!extension_loaded( 'ADOdb' ),
		'The ADOdb extension is not supported and must be disabled'
	);
}
check_print_test_row(
	'Version of <a href="https://adodb.org">ADOdb</a> available is at least ' . DB_MIN_VERSION_ADODB,
	$t_adodb_version_check_ok,
	$t_adodb_version_info
);

if( !$t_adodb_version_check_ok ) {
	return;
}

$t_database_dsn = config_get_global( 'dsn' );
check_print_info_row(
	'Using a custom <a href="https://en.wikipedia.org/wiki/Database_Source_Name">Database Source Name</a> (DSN) for connecting to the database',
	$t_database_dsn ? 'Yes' : 'No'
);

$t_database_type = config_get_global( 'db_type' );
check_print_info_row(
	'Database type (ADOdb driver)',
	htmlentities( $t_database_type )
);


$t_db_support = db_check_database_support( $t_database_type );
check_print_test_row(
	'Database type is supported by the version of PHP installed on this server',
	$t_db_support,
	array( false => 'The current database type is set to ' . htmlentities( $t_database_type )
		. '. The version of PHP installed on this server does not have support for this database type.' )
);
if( !$t_db_support ) {
	# Can't continue the checks without PHP support
	return;
}

if( db_is_mysql() ) {
	check_print_test_warn_row(
		'PHP support for legacy MySQL driver',
		'mysql' != $t_database_type,
		array( false => "'mysql' driver is deprecated as of PHP 5.5.0 and has been removed as of PHP 7.0.0, please use 'mysqli' instead" )
	);

	check_print_test_row( 'PHP support for MySQL Native Driver',
		function_exists( 'mysqli_stmt_get_result' ),
		array( false => 'Check that the MySQL Native Driver (mysqlnd) has been compiled into your server.' )
	);

	check_print_test_warn_row(
		'mysqli.allow_local_infile php.ini directive is set to 0',
		!ini_get_bool( 'mysqli.allow_local_infile' ),
		array( false => 'mysqli.allow_local_infile should be disabled to prevent remote attackers to access local files '
			. '(see issue <a href="https://mantisbt.org/bugs/view.php?id=23173">#23173</a>).' )
	);

}

if( db_is_mssql() ) {

	check_print_test_warn_row(
		'PHP support for Microsoft SQL Server driver',
		'mssql' != $t_database_type,
		array( false => "'mssql' driver is no longer supported in PHP >= 5.3, please use 'mssqlnative' instead" )
	);

	$t_mssql_textsize = ini_get_number( 'mssql.textsize' );
	check_print_info_row(
		'php.ini directive: mssql.textsize',
		htmlentities( $t_mssql_textsize )
	);

	check_print_test_warn_row(
		'mssql.textsize php.ini directive is set to -1',
		$t_mssql_textsize == -1,
		array( false => 'The value of the mssql.textsize directive is currently '
			. htmlentities( $t_mssql_textsize )
			. '. You should set this value to -1 to prevent large text fields being truncated upon being read from the database.' )
	);

	$t_mssql_textlimit = ini_get_number( 'mssql.textlimit' );
	check_print_info_row(
		'php.ini directive: mssql.textlimit',
		htmlentities( $t_mssql_textlimit )
	);

	check_print_test_warn_row(
		'mssql.textlimit php.ini directive is set to -1',
		$t_mssql_textlimit == -1,
		array( false => 'The value of the mssql.textlimit directive is currently '
			. htmlentities( $t_mssql_textlimit )
			. '. You should set this value to -1 to prevent large text fields being truncated upon being read from the database.' )
	);
}

$t_database_hostname = config_get_global( 'hostname' );
check_print_info_row(
	'Database hostname',
	htmlentities( $t_database_hostname )
);

$t_database_username = config_get_global( 'db_username' );
check_print_info_row(
	'Database username',
	htmlentities( $t_database_username )
);

$t_database_password = config_get_global( 'db_password' );

$t_database_name = config_get_global( 'database_name' );
check_print_info_row(
	'Database name',
	htmlentities( $t_database_name )
);

db_connect( $t_database_dsn, $t_database_hostname, $t_database_username, $t_database_password, $t_database_name );
check_print_test_row(
	'Can open connection to database <em>' . htmlentities( $t_database_name )
	. '</em> on host <em>' . htmlentities( $t_database_hostname )
	. '</em> with username <em>' . htmlentities( $t_database_username ) . '</em>',
	db_is_connected()
);

if( !db_is_connected() ) {
	return;
}
global $g_db;
$t_database_server_info = $g_db->ServerInfo();
$t_db_version = $t_database_server_info['version'];

# MantisBT minimum version
check_print_info_row(
	'Database server version',
	htmlentities( $t_db_version ),
	htmlentities( $t_database_server_info['description'] )
);

global $g_db_functional_type;
switch( $g_db_functional_type ) {
	case DB_TYPE_MYSQL:
		$t_eol_db_type = stripos( $t_database_server_info['description'], 'MariaDB' )
			? EndOfLifeCheck::PRODUCT_MARIADB
			: EndOfLifeCheck::PRODUCT_MYSQL;
		$t_db_min_version = DB_MIN_VERSION_MYSQL;

		check_print_info_row(
			'Database server type',
			$t_eol_db_type
		);
		break;
	case DB_TYPE_PGSQL:
		$t_eol_db_type = EndOfLifeCheck::PRODUCT_POSTGRESQL;
		$t_db_min_version = DB_MIN_VERSION_PGSQL;
		break;
	case DB_TYPE_MSSQL:
		$t_eol_db_type = EndOfLifeCheck::PRODUCT_SQLSERVER;
		$t_db_min_version = DB_MIN_VERSION_MSSQL;
		break;
	case DB_TYPE_ORACLE:
		$t_eol_db_type = EndOfLifeCheck::PRODUCT_ORACLE;
		$t_db_min_version = DB_MIN_VERSION_ORACLE;
		break;
	default:
		$t_eol_db_type = '';
		$t_db_min_version = 0;
}
check_print_test_row(
	'Minimum database version required for MantisBT',
	version_compare( $t_db_version, $t_db_min_version, '>=' ),
	"The minimum requirement for your database platform is $t_db_min_version."
);

# Database version End-of-life and Support checks
# Get info from https://endoflife.date/

try {
	/** @noinspection HtmlUnknownTarget */
	$t_url_link = '<a href="%1$s">%1$s</a>';

	$t_release = new EndOfLifeCheck( $t_eol_db_type, $t_db_version );
	$t_message = 'Release information retrieved from '
		. sprintf( $t_url_link, $t_release->getUrl() );
}
catch( Exception $e ) {
	$t_message = 'Failed to retrieve release information from '
		. sprintf( $t_url_link, EndOfLifeCheck::URL ) . ': '
		. $e->getMessage() . '<br>'
		. $e->getPrevious()->getMessage();
	$t_release = false;
}
check_print_test_warn_row(
	'Database End-of-Life support check',
	$t_release !== false,
	$t_message
	);

if( $t_release !== false ) {
	# Has reached End Of Life ?
	check_print_test_warn_row(
		'Database version is supported',
		!$t_release->isEOL( $t_message ),
		$t_message
	);

	# Is it an LTS release ?
	# Note: only report this for RDBMS have a concept of LTS releases.
	if( db_is_mysql() || db_is_oracle() ) {
		check_print_test_warn_row(
			'Database version is a Long-Term Support (LTS) release',
			$t_release->isLTS(),
			[false => "Non-LTS releases are not recommended for Production use."
			]
		);
	}

	# Is there a newer release available ?
	check_print_test_warn_row(
		'Database is the latest available maintenance release',
		$t_release->isLatest( $t_message ),
		$t_message
	);
}

$t_table_prefix = config_get_global( 'db_table_prefix' );
check_print_info_row(
	'Prefix added to each MantisBT table name',
	htmlentities( $t_table_prefix )
);

$t_table_plugin_prefix = config_get_global( 'db_table_plugin_prefix' );
check_print_info_row(
	'Prefix added to each Plugin table name',
	htmlentities( $t_table_plugin_prefix )
);

$t_table_suffix = config_get_global( 'db_table_suffix' );
check_print_info_row(
	'Suffix added to each MantisBT table name',
	htmlentities( $t_table_suffix )
);

check_print_test_warn_row(
	'Plugin table prefix should not be empty',
	!empty( $t_table_plugin_prefix ),
	array(
		false => 'Defining $g_db_table_plugin_prefix allows easy identification of plugin-specific vs MantisBT core tables',
	)
);

if( db_is_mysql() ) {
	global $g_database_name;
	# Check DB's default collation
	$t_query = 'SELECT default_collation_name
		FROM information_schema.schemata
		WHERE schema_name = ' . db_param();
	$t_collation = db_result( db_query( $t_query, array( $g_database_name ) ) );
	check_print_test_row(
		'Database default collation is UTF-8',
		check_is_collation_utf8( $t_collation ),
		array( false => 'Database is using '
			. htmlentities( $t_collation )
			. ' collation where UTF-8 collation is required.' )
	);

	$t_table_regex = '/^'
		. preg_quote( $t_table_prefix, '/' ) . '.+?'
		. preg_quote( $t_table_suffix, '/' ) . '$/';

	$t_result = db_query( 'SHOW TABLE STATUS' );
	while( $t_row = db_fetch_array( $t_result ) ) {
		if( $t_row['comment'] !== 'VIEW' &&
			preg_match( $t_table_regex, $t_row['name'] )
		) {
			check_print_test_row(
				'Table <em>' . htmlentities( $t_row['name'] ) . '</em> is using UTF-8 collation',
				check_is_collation_utf8( $t_row['collation'] ),
				array( false => 'Table ' . htmlentities( $t_row['name'] )
					. ' is using ' . htmlentities( $t_row['collation'] )
					. ' collation where UTF-8 collation is required.' )
			);
		}
	}

	foreach( db_get_table_list() as $t_table ) {
		if( preg_match( $t_table_regex, $t_table ) ) {
			$t_result = db_query( 'SHOW FULL FIELDS FROM ' . $t_table );
			while( $t_row = db_fetch_array( $t_result ) ) {
				if( $t_row['collation'] === null ) {
					continue;
				}
				check_print_test_row(
					'Text column <em>' . htmlentities( $t_row['field'] )
					. '</em> of type <em>' . $t_row['type']
					. '</em> on table <em>' . htmlentities( $t_table )
					. '</em> is using UTF-8 collation',
					check_is_collation_utf8( $t_row['collation'] ),
					array( false => 'Text column ' . htmlentities( $t_row['field'] )
						. ' of type ' . $t_row['type']
						. ' on table ' . htmlentities( $t_table )
						. ' is using ' . htmlentities( $t_row['collation'] )
						. ' collation where UTF-8 collation is required.' )
				);
			}
		}
	}
}
