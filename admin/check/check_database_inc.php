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
 * @uses utility_api.php
 */

if( !defined( 'CHECK_DATABASE_INC_ALLOW' ) ) {
	return;
}

# MantisBT Check API
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
		$t_adodb_version_check_ok = version_compare( $t_matches[1], DB_MIN_VERSION_ADODB, '>=' );
		$t_adodb_version_info = 'ADOdb version ' . htmlentities( $t_matches[1] ) . ' was found.';
	}

	# Making sure we're not using the ADOdb extension (see #14552)
	check_print_test_row(
		'Checking use of the <a href="http://adodb.sourceforge.net/#extension">ADOdb extension</a>',
		!extension_loaded( 'ADOdb' ),
		'The ADOdb extension is not supported and must be disabled'
	);
}
check_print_test_row(
	'Version of <a href="http://en.wikipedia.org/wiki/ADOdb">ADOdb</a> available is at least ' . DB_MIN_VERSION_ADODB,
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
	array( false => 'The current database type is set to ' . htmlentities( $t_database_type )
		. '. The version of PHP installed on this server does not have support for this database type.' )
);

if( db_is_mysql() ) {
	check_print_test_warn_row(
		'PHP support for MySQL driver',
		'mysql' != $t_database_type,
		array( false => "'mysql' driver is deprecated as of PHP 5.5.0, please use 'mysqli' instead" )
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

$t_database_server_info = $g_db->ServerInfo();
$t_db_version = $t_database_server_info['version'];
preg_match( '/^[0-9]+\.[0-9+]/', $t_db_version, $t_matches );
$t_db_major_version = $t_matches[0];

# MantisBT minimum version
check_print_info_row(
	'Database server version',
	htmlentities( $t_database_server_info['version'] )
);

if( db_is_mysql() ) {
	$t_db_min_version = DB_MIN_VERSION_MYSQL;
} elseif( db_is_pgsql() ) {
	$t_db_min_version = DB_MIN_VERSION_PGSQL;
} elseif( db_is_mssql() ) {
	$t_db_min_version = DB_MIN_VERSION_MSSQL;
} else {
	$t_db_min_version = 0;
}
check_print_test_row(
	'Minimum database version required for MantisBT',
	version_compare( $t_db_version, $t_db_min_version, '>=' ),
	array(
		true => 'You are using version ' . htmlentities( $t_db_version ) . '.',
		false => 'The database version you are using is ' . htmlentities( $t_db_version )
			. '. The minimum requirement for MantisBT on your database platform is ' . $t_db_min_version . '.'
	)
);

$t_date_format = config_get( 'short_date_format' );

# MySQL support checking
if( db_is_mysql() ) {
	# Note: the MySQL lifecycle page [1] is no longer available.
	# The list below was built based on information found in [2].
	# [1] http://www.mysql.com/about/legal/lifecycle/
	# [2] http://dev.mysql.com/doc/refman/5.7/en/faqs-general.html#qandaitem-B-1-1-1
	$t_versions = array(
		# Series >= Type, GA status, GA date
		'5.0' => array( 'GA', '5.0.15', '2005-10-19' ),
		'5.1' => array( 'GA', '5.1.30', '2008-11-14' ),
		'5.4' => array( 'Discontinued' ),
		'5.5' => array( 'GA', '5.5.8', '2010-12-03' ),
		'5.6' => array( 'GA', '5.6.10', '2013-02-05' ),
		'5.7' => array( 'GA', '5.7.9', '2015-10-21' ),
		'6.0' => array( 'Discontinued' ),
	);
	$t_support_url = 'http://www.mysql.com/support/';

	# Is it a GA release
	$t_mysql_ga_release = false;
	$t_date_premier_end = $t_date_extended_end = null;
	if( !array_key_exists( $t_db_major_version, $t_versions ) ) {
		check_print_test_warn_row(
			'MySQL Lifecycle and Release Support data availability',
			false,
			array(
				false => 'Release information for MySQL ' . $t_db_major_version
					. ' series is not available, unable to perform the lifecycle checks.'
			) );
	} else {
		if( 'GA' == $t_versions[$t_db_major_version][0] ) {
			$t_mysql_ga_release = version_compare( $t_database_server_info['version'], $t_versions[$t_db_major_version][1], '>=' );
			# Support end-dates as per http://www.mysql.com/support/
			$t_date_ga = new DateTime( $t_versions[$t_db_major_version][2] );
			$t_date_premier_end = $t_date_ga->add( new DateInterval( 'P5Y' ) )->format( $t_date_format );
			$t_date_extended_end = $t_date_ga->add( new DateInterval( 'P3Y' ) )->format( $t_date_format );
		} else {
			$t_mysql_ga_release = false;
			$t_date_premier_end = $t_date_extended_end = null;
		}
		check_print_test_row(
			'MySQL version is a General Availability (GA) release',
			$t_mysql_ga_release,
			array(
				true => 'You are using MySQL version ' . htmlentities( $t_db_version ) . '.',
				false => 'The version of MySQL you are using is '
					. htmlentities( $t_db_version )
					. '. This is a development or pre-GA version which '
					. ( $t_versions[$t_db_major_version][0] == 'Discontinued' ? 'has been discontinued and ' : '' )
					. 'is not recommended for Production use. You should upgrade to a supported GA release.'
			) );

		# Within lifecycle 'Extended' support
		check_print_test_row(
			'MySQL version is within the <a href="' . $t_support_url . '">Extended Support</a> period (GA + 8 years)',
			date_create( $t_date_extended_end ) > date_create( 'now' ),
			array(
				true => 'Extended support for MySQL ' . $t_db_major_version . ' series ends on ' . $t_date_extended_end,
				false => 'Support for the release of MySQL you are using ('
					. htmlentities( $t_db_version )
					. ') ended on ' . $t_date_extended_end
					. '. It should not be used, as security flaws discovered in this version will not be fixed.'
			) );

		# Within lifecycle 'Premier' support
		check_print_test_warn_row(
			'Version of MySQL being used is within the <a href="' . $t_support_url . '">Premier Support</a> period (GA + 5 years)',
			date_create( $t_date_premier_end ) > date_create( 'now' ),
			array(
				true => 'Premier support for MySQL ' . $t_db_major_version . ' series ends on ' . $t_date_premier_end,
				false => 'Premier Support for the release of MySQL you are using ('
					. htmlentities( $t_db_version )
					. ') ended on ' . $t_date_premier_end
					. '. The release is in its Extended support period, which ends on '
					. $t_date_extended_end
					. '. You should upgrade to a newer version of MySQL which is still within its Premier support period to benefit from bug fixes and security patches.'
			) );
	}
} else if( db_is_pgsql() ) {
	# PostgreSQL support checking

	# Version support information
	$t_versions = array(
		# Version => EOL date
		'9.4' => '2019-12-31',
		'9.3' => '2018-09-30',
		'9.2' => '2017-09-30',
		'9.1' => '2016-09-30',
		'9.0' => '2015-09-30',
	);
	$t_support_url = 'http://www.postgresql.org/support/versioning/';

	# Determine EOL date
	if( array_key_exists( $t_db_major_version, $t_versions ) ) {
		$t_date_eol = $t_versions[$t_db_major_version];
	} else {
		$t_version = key( $t_versions );
		if( version_compare( $t_db_major_version, $t_version, '>' ) ) {
			# Major version is higher than the most recent in array - assume we're supported
			$t_date_eol = new DateTime;
			$t_date_eol = $t_date_eol->add( new DateInterval( 'P1Y' ) )->format( $t_date_format );
			$t_assume = array( 'more recent', $t_version, 'supported' );
		} else {
			# Assume EOL
			$t_date_eol = null;
			end( $t_versions );
			$t_assume = array( 'older', key( $t_versions ), 'at end of life' );
		}

		check_print_test_warn_row(
			'PostgreSQL version support information availability',
			false,
			array(
				false => 'Release information for version ' . $t_db_major_version . ' is not available. '
					. vsprintf( 'Since it is %s than %s, we assume it is %s. ', $t_assume )
					. 'Please refer to the <a href="' . $t_support_url
					. '">PostgreSQL release support policy</a> to make sure.'
			) );
	}

	check_print_test_row(
		'Version of PostgreSQL is <a href="' . $t_support_url . '">supported</a>',
		date_create( $t_date_eol ) > date_create( 'now' ),
		array(
			false => 'PostgreSQL version ' . htmlentities( $t_db_version )
				. ' is no longer supported and should not be used, as security flaws discovered in this version will not be fixed.'
		) );
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
