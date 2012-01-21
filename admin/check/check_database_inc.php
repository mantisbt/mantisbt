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
 * @package MantisBT
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses check_api.php
 * @uses config_api.php
 * @uses database_api.php
 * @uses utility_api.php
 */

if ( !defined( 'CHECK_DATABASE_INC_ALLOW' ) ) {
	return;
}

/**
 * MantisBT Check API
 */
require_once( 'check_api.php' );
require_api( 'config_api.php' );
require_api( 'database_api.php' );
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
	if( preg_match( '/^[Vv]([0-9\.]+)/', $ADODB_vers, $t_matches ) == 1 ) {
		$t_adodb_version_check_ok = version_compare( $t_matches[1], '5.10', '>=' );
		$t_adodb_version_info = 'ADOdb version ' . htmlentities( $t_matches[1] ) . ' was found.';
	}
}
check_print_test_row(
	'Version of <a href="http://en.wikipedia.org/wiki/ADOdb">ADOdb</a> available is at least 5.11',
	$t_adodb_version_check_ok,
	$t_adodb_version_info
);

if( !$t_adodb_version_check_ok ) {
	return;
}

$t_database_dsn = config_get_global( 'dsn' );
check_print_info_row(
	'Using a custom <a href="http://en.wikipedia.org/wiki/Database_Source_Name">Database Source Name</a> (DSN) for connecting to the database',
	$t_database_dsn ? 'Yes' : 'No'
);

$t_database_type = config_get_global( 'db_type' );
check_print_info_row(
	'Database type',
	htmlentities( $t_database_type )
);

check_print_test_row(
	'Database type is supported by the version of PHP installed on this server',
	db_check_database_support( $t_database_type ),
	array( false => 'The current database type is set to ' . htmlentities( $t_database_type ) . '. The version of PHP installed on this server does not have support for this database type.' )
);

if ( db_is_mssql() ) {

	check_print_test_warn_row(
		'PHP support for Microsoft SQL Server driver',
		'mssql' != config_get_global( 'db_type' ),
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
		array( false => 'The value of the mssql.textsize directive is currently ' . htmlentities( $t_mssql_textsize ) . '. You should set this value to -1 to prevent large text fields being truncated upon being read from the database.' )
	);

	$t_mssql_textlimit = ini_get_number( 'mssql.textlimit' );
	check_print_info_row(
		'php.ini directive: mssql.textlimit',
		htmlentities( $t_mssql_textlimit )
	);

	check_print_test_warn_row(
		'mssql.textlimit php.ini directive is set to -1',
		$t_mssql_textlimit == -1,
		array( false => 'The value of the mssql.textlimit directive is currently ' . htmlentities( $t_mssql_textlimit ) . '. You should set this value to -1 to prevent large text fields being truncated upon being read from the database.' )
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
	'Can open connection to database <em>' . htmlentities( $t_database_name ) . '</em> on host <em>' . htmlentities( $t_database_hostname ) . '</em> with username <em>' . htmlentities( $t_database_username ) . '</em>',
	db_is_connected()
);

if( !db_is_connected() ) {
	return;
}

$t_database_server_info = $g_db->ServerInfo();
check_print_info_row(
	'Database server version',
	htmlentities( $t_database_server_info['version'] )
);

if( db_is_mysql() ) {

	check_print_test_row(
		'Version of MySQL being used is within the <a href="http://www.mysql.com/about/legal/lifecycle/">MySQL extended lifecycle period</a>',
		version_compare( $t_database_server_info['version'], '5.0', '>=' ),
		array(
			true => 'Extended lifecycle support ends on 2011-12-31 for MySQL 5.0 and on 2013-12-31 for MySQL 5.1.',
			false => 'The version of MySQL you are using is ' . htmlentities( $t_database_server_info['version'] ) . '. This version is no longer supported and should not be used as security flaws discovered in this version will not be fixed.'
		)
	);

	check_print_test_warn_row(
		'Version of MySQL being used is within the <a href="http://www.mysql.com/about/legal/lifecycle/">MySQL active lifecycle period</a>',
		version_compare( $t_database_server_info['version'], '5.1', '>=' ),
		array(
			true => 'Active lifecycle support ends on 2010-12-31 for MySQL 5.1.',
			false => 'The version of MySQL you are using is ' . htmlentities( $t_database_server_info['version'] ) . '. It is recommended you use a newer version of MySQL still within the active lifecycle period.'
		)
	);

}

if( db_is_pgsql() ) {

	check_print_test_row(
		'Version of PostgreSQL being used still has <a href="http://wiki.postgresql.org/wiki/PostgreSQL_Release_Support_Policy">release support</a>',
		version_compare( $t_database_server_info['version'], '7.4', '>=' ),
		array( false => 'The version of PostgreSQL you are using is '. htmlentities( $t_database_server_info['version'] ). '. This version is no longer supported and should not be used as security flaws discovered in this version will not be fixed.' )
	);

}

$t_table_prefix = config_get_global( 'db_table_prefix' );
check_print_info_row(
	'Prefix added to each MantisBT table name',
	htmlentities( $t_table_prefix )
);

$t_table_suffix = config_get_global( 'db_table_suffix' );
check_print_info_row(
	'Suffix added to each MantisBT table name',
	htmlentities( $t_table_suffix )
);

if( db_is_mysql() ) {

	$t_table_prefix_regex_safe = preg_quote( $t_table_prefix, '/' );
	$t_table_suffix_regex_safe = preg_quote( $t_table_suffix, '/' );

	$t_result = db_query_bound( 'SHOW TABLE STATUS' );
	while( $t_row = db_fetch_array( $t_result ) ) {
		if( $t_row['Comment'] !== 'VIEW' &&
		    preg_match( "/^$t_table_prefix_regex_safe.+?$t_table_suffix_regex_safe\$/", $t_row['Name'] ) ) {
			check_print_test_row(
				'Table <em>' . htmlentities( $t_row['Name'] ) . '</em> is using UTF-8 collation',
				substr( $t_row['Collation'], 0, 5 ) === 'utf8_',
				array( false => 'Table ' . htmlentities( $t_row['Name'] ) . ' is using ' . htmlentities( $t_row['Collation'] ) . ' collation where UTF-8 collation is required.' )
			);
		}
	}

	foreach( db_get_table_list() as $t_table ) {
		if( preg_match( "/^$t_table_prefix_regex_safe.+?$t_table_suffix_regex_safe\$/", $t_table ) ) {
			$t_result = db_query_bound( 'SHOW FULL FIELDS FROM ' . $t_table );
			while( $t_row = db_fetch_array( $t_result ) ) {
				if ( $t_row['Collation'] === null ) {
					continue;
				}
				check_print_test_row(
					'Text column <em>' . htmlentities( $t_row['Field'] ) . '</em> of type <em>' . $t_row['Type'] . '</em> on table <em>' . htmlentities( $t_table ) . '</em> is is using UTF-8 collation',
					substr( $t_row['Collation'], 0, 5 ) === 'utf8_',
					array( false => 'Text column ' . htmlentities( $t_row['Field'] ) . ' of type ' . $t_row['Type'] . ' on table ' . htmlentities( $t_table ) . ' is using ' . htmlentities( $t_row['Collation'] ) . ' collation where UTF-8 collation is required.' )
				);
			}
		}
	}

}
