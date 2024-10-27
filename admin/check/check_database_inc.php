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
	if( preg_match( '/^[Vv]([0-9.]+)/', $ADODB_vers, $t_matches ) == 1 ) {
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
preg_match( '/^([0-9]+)\.[0-9+]/', $t_db_version, $t_matches );
$t_db_major_version = $t_matches[0];

# MantisBT minimum version
check_print_info_row(
	'Database server version',
	htmlentities( $t_db_version )
);

if( db_is_mysql() ) {
	$t_db_min_version = DB_MIN_VERSION_MYSQL;
} elseif( db_is_pgsql() ) {
	$t_db_min_version = DB_MIN_VERSION_PGSQL;

	# Starting with PostgreSQL 10, a major version is indicated by increasing
	# the first part of the version;  before that a major version was indicated
	# by increasing either the first or second part of the version number.
	if( version_compare( $t_db_version, '10', '>=' ) ) {
		$t_db_major_version = $t_matches[1];
	}
} elseif( db_is_mssql() ) {
	$t_db_min_version = DB_MIN_VERSION_MSSQL;
} elseif( db_is_oracle() ) {
	$t_db_min_version = DB_MIN_VERSION_ORACLE;
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
	# The list below was built based on information found in the FAQ [1]
	# [1]: https://dev.mysql.com/doc/refman/8.4/en/faqs-general.html
	# @TODO consider using https://endoflife.date/mysql to retrieve this data
	$t_versions = array(
		# Series => Type, Version when GA status was achieved, GA date
		'5.0' => array( 'GA', '5.0.15', '2005-10-19' ),
		'5.1' => array( 'GA', '5.1.30', '2008-11-14' ),
		'5.4' => array( 'Discontinued' ),
		'5.5' => array( 'GA', '5.5.8', '2010-12-03' ),
		'5.6' => array( 'GA', '5.6.10', '2013-02-05' ),
		'5.7' => array( 'GA', '5.7.9', '2015-10-21' ),
		'6.0' => array( 'Discontinued' ),
		'8.0' => array( 'GA', '8.0.11', '2018-04-19' ),
		'8.1' => array( 'Innovation', '8.1.0', '2023-07-18' ),
		'8.2' => array( 'Innovation', '8.2.0', '2023-10-25' ),
		'8.3' => array( 'Innovation', '8.3.0', '2024-01-16' ),
		'8.4' => array( 'LTS', '8.4.0', '2024-04-30' ),
		'9.0' => array( 'Innovation', '9.0.0', '2024-06-07' ),
		'9.1' => array( 'Innovation', '9.1.0', '2024-09-24' ),
	);

	$t_support_url = 'https://www.mysql.com/support/supportedplatforms/';
	$t_supported_release = '<a href="' . $t_support_url . '">supported release</a>';

	# Is it a GA or LTS release
	$t_mysql_ga_release = false;
	$t_date_end_active_support = $t_date_end_of_life = null;
	if( !array_key_exists( $t_db_major_version, $t_versions ) ) {
		$t_param = [
			'category_id' => 12, # db mysql
			'product_version' => MANTIS_VERSION,
			'reproducibility' => 10, # always
			'priority' => 20, # low
			'summary' => "MySQL version $t_db_major_version is not defined in Admin Checks",
			'description' => "Please add the missing version to " . basename( __FILE__ ) . ".",
		];
		$t_report_bug_url = 'https://mantisbt.org/bugs/bug_report_page.php?' . http_build_query( $t_param );
		check_print_test_warn_row(
			'MySQL Lifecycle and Release Support data availability',
			false,
			array(
				false => 'Release information for MySQL ' . $t_db_major_version
					. ' series is not available, unable to perform the lifecycle checks.'
					. ' Please <a href="' . $t_report_bug_url . '">report the issue</a>.'
			) );
	} else {
		$t_version = $t_versions[$t_db_major_version];
		$t_version_type = $t_version[0];
		$t_mysql_ga_release = version_compare( $t_db_version, $t_version[1], '>=' );
		if( in_array( $t_version_type, ['GA', 'LTS'] )) {
			# Support end-dates as per https://www.mysql.com/support/
			/** @noinspection PhpUnhandledExceptionInspection */
			$t_date_ga = new DateTimeImmutable( $t_version[2] );
			$t_date_end_active_support = $t_date_ga->add( new DateInterval( 'P5Y' ) )
												   ->modify( 'last day of this month' );
			$t_date_end_of_life = $t_date_ga->add( new DateInterval( 'P8Y' ) )
											->modify( 'last day of this month' );
		} elseif( 'Innovation' === $t_version_type ) {
			# Innovation releases are supported until the next one comes out.
			# If not defined, we default to 3 months as Oracle aims for quarterly releases.

			# Get the next version
			# Skip the first entries (Innovation versions started with MySQL 8.1)
			$t_innovation_versions = array_slice( $t_versions, 8 );
			foreach( $t_innovation_versions as $t_key => $t_unused ) {
				$t_next_version = next( $t_innovation_versions );
				if( $t_key == $t_db_major_version) {
					break;
				}
			}

			/** @noinspection PhpUndefinedVariableInspection */
			if( $t_next_version !== false ) {
				/** @noinspection PhpUnhandledExceptionInspection */
				$t_date_end_of_life = new DateTimeImmutable( $t_next_version[2] );
			} else {
				/** @noinspection PhpUnhandledExceptionInspection */
				$t_date_ga = new DateTimeImmutable( $t_version[2] );
				$t_date_end_of_life = $t_date_ga->add( new DateInterval( 'P3M' ) )
												->modify( 'last day of this month' );
			}
			$t_date_end_active_support = $t_date_end_of_life;
		}

		check_print_test_row(
			'MySQL version is a General Availability (GA) release',
			$t_mysql_ga_release,
			array(
				false => "MySQL $t_db_version is a development or pre-GA release, which "
					. ( $t_version_type == 'Discontinued' ? 'has been discontinued and ' : '' )
					. "is not recommended for Production use. You should upgrade to a $t_supported_release."
			)
		);

		# Has not reached End Of Life
		$t_end_of_life = $t_date_end_of_life > date_create_immutable();
		$t_eol_date_formatted = $t_date_end_of_life ? $t_date_end_of_life->format( $t_date_format ) : 'N/A (Discontinued release)';
		check_print_test_warn_row(
			'MySQL version is supported',
			$t_end_of_life,
			array(
				true => 'Support for MySQL ' . $t_db_major_version . ' series ends on ' . $t_eol_date_formatted,
				false => 'Support for MySQL ' . htmlspecialchars( $t_db_version )
					. " ended on $t_eol_date_formatted. You should upgrade to a $t_supported_release,"
					. ' as bugs and security flaws discovered in this version will not be fixed.'
			)
		);

		if( $t_end_of_life && $t_date_end_active_support < $t_date_end_of_life ) {
			# Within active support period
			$t_eas_date_formatted = $t_date_end_active_support->format( $t_date_format );
			check_print_test_warn_row(
				'MySQL version is within the active support period',
				$t_date_end_active_support > date_create_immutable(),
				array(
					true => 'Active support for MySQL ' . $t_db_major_version . ' series ends on ' . $t_eas_date_formatted,
					false => 'Active support for MySQL ' . htmlspecialchars( $t_db_version )
						. ' ended on ' . $t_eas_date_formatted
						. '. The release is in its Extended support period, which will end on ' . $t_eol_date_formatted
						. ". You should upgrade to an actively $t_supported_release,"
						. '  to benefit from bug fixes and security patches.'
				) );
		}
	}
} else if( db_is_pgsql() ) {
	# PostgreSQL support checking

	# Version support information
	$t_versions = array(
		# Version => Final release (EOL) date
		'17'  => '2029-11-08',
		'16'  => '2028-11-09',
		'15'  => '2027-11-11',
		'14'  => '2026-11-12',
		'13'  => '2025-11-13',
		'12'  => '2024-11-14',
		'11'  => '2023-11-09',
		'10'  => '2022-11-10',
		'9.6' => '2021-11-11',
		'9.5' => '2021-02-11',
		'9.4' => '2020-02-13',
		'9.3' => '2018-11-08',
		'9.2' => '2017-11-09',
		'9.1' => '2016-10-27',
		'9.0' => '2015-10-15',
	);
	$t_support_url = 'https://www.postgresql.org/support/versioning/';

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
					. '">PostgreSQL Versioning Policy</a> to make sure.'
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
