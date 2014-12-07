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
 * Mantis Database installation process
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

error_reporting( E_ALL );
@set_time_limit( 0 );

# Load the MantisDB core in maintenance mode. This mode will assume that
# config/config_inc.php hasn't been specified. Thus the database will not be opened
# and plugins will not be loaded.
define( 'MANTIS_MAINTENANCE_MODE', true );

require_once( dirname( dirname( __FILE__ ) ) . '/core.php' );
require_api( 'install_helper_functions_api.php' );
require_api( 'crypto_api.php' );
$g_error_send_page_header = false; # bypass page headers in error handler

$g_failed = false;
$g_database_upgrade = false;

/**
 * Print Test result
 *
 * @param integer $p_result    Result - BAD|GOOD.
 * @param boolean $p_hard_fail Fail installation or soft warning.
 * @param string  $p_message   Message to display to user.
 * @return void
 */
function print_test_result( $p_result, $p_hard_fail = true, $p_message = '' ) {
	global $g_failed;
	echo '<td ';
	if( BAD == $p_result ) {
		if( $p_hard_fail ) {
			$g_failed = true;
			echo 'bgcolor="red">BAD';
		} else {
			echo 'bgcolor="pink">POSSIBLE PROBLEM';
		}
		if( '' != $p_message ) {
			echo '<br />' . $p_message;
		}
	}

	if( GOOD == $p_result ) {
		echo 'bgcolor="green">GOOD';
	}
	echo '</td>';
}

/**
 * Print Test result
 *
 * @param string  $p_test_description Test Description.
 * @param integer $p_result           Result - BAD|GOOD.
 * @param boolean $p_hard_fail        Fail installation or soft warning.
 * @param string  $p_message          Message to display to user.
 * @return void
 */
function print_test( $p_test_description, $p_result, $p_hard_fail = true, $p_message = '' ) {
	echo '<tr><td bgcolor="#ffffff">' . $p_test_description . '</td>';
	print_test_result( $p_result, $p_hard_fail, $p_message );
	echo '</tr>' . "\n";
}

# install_state
#   0 = no checks done
#   1 = server ok, get database information
#   2 = check the database information
#   3 = install the database
#   4 = get additional config file information
#   5 = write the config file
#	6 = post install checks
#	7 = done, link to login or db updater
$t_install_state = gpc_get_int( 'install', 0 );

html_begin();
html_head_begin();
html_css_link( 'admin.css' );
html_content_type();
html_title( 'Administration - Installation' );
html_javascript_link( 'jquery-1.11.1.min.js' );
html_javascript_link( 'install.js' );
html_head_end();
?>

<body>
<table width="100%" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
	<tr class="top-bar">
		<td class="links">
			[ <a href="index.php">Back to Administration</a> ]
		</td>
		<td class="title">
		<?php
switch( $t_install_state ) {
	case 6:
		echo 'Post Installation Checks';
		break;
	case 5:
		echo 'Install Configuration File';
		break;
	case 4:
		echo 'Additional Configuration Information';
		break;
	case 3:
		echo 'Install Database';
		break;
	case 2:
		echo 'Check and Install Database';
		break;
	case 1:
		echo 'Database Parameters';
		break;
	case 0:
	default:
		echo 'Pre-Installation Check';
		break;
}
?>
		</td>
	</tr>
</table>
<br /><br />

<?php
if( 0 == $t_install_state ) {
	?>
<table width="100%" bgcolor="#222222" cellpadding="10" cellspacing="1">
<tr>
	<td bgcolor="#e8e8e8" colspan="2">
		<span class="title">Checking Installation...</span>
	</td>
</tr>
<?php
}

$t_config_filename = $g_config_path . 'config_inc.php';
$t_config_exists = file_exists( $t_config_filename );

# Initialize Oracle-specific values for prefix and suffix, and set
# values for other db's as per config defaults
$t_prefix_defaults = array(
	'oci8' => array(
		'db_table_prefix'        => 'm',
		'db_table_plugin_prefix' => 'plg',
		'db_table_suffix'        => '',
	) ,
);
foreach( $t_prefix_defaults['oci8'] as $t_key => $t_value ) {
	$t_prefix_defaults['other'][$t_key] = config_get( $t_key, '' );
}

if( $t_config_exists && $t_install_state <= 1 ) {
	# config already exists - probably an upgrade
	$f_dsn                    = config_get( 'dsn', '' );
	$f_hostname               = config_get( 'hostname', '' );
	$f_db_type                = config_get( 'db_type', '' );
	$f_database_name          = config_get( 'database_name', '' );
	$f_db_schema              = config_get( 'db_schema', '' );
	$f_db_username            = config_get( 'db_username', '' );
	$f_db_password            = config_get( 'db_password', '' );
	$f_timezone               = config_get( 'default_timezone', '' );

	# Set default prefix/suffix form variables ($f_db_table_XXX)
	foreach( $t_prefix_defaults['other'] as $t_key => $t_value ) {
		${'f_' . $t_key} = $t_value;
	}
} else {
	# read control variables with defaults
	$f_dsn                = gpc_get( 'dsn', config_get( 'dsn', '' ) );
	$f_hostname           = gpc_get( 'hostname', config_get( 'hostname', 'localhost' ) );
	$f_db_type            = gpc_get( 'db_type', config_get( 'db_type', '' ) );
	$f_database_name      = gpc_get( 'database_name', config_get( 'database_name', 'bugtracker' ) );
	$f_db_schema          = gpc_get( 'db_schema', config_get( 'db_schema', '' ) );
	$f_db_username        = gpc_get( 'db_username', config_get( 'db_username', '' ) );
	$f_db_password        = gpc_get( 'db_password', config_get( 'db_password', '' ) );
	if( CONFIGURED_PASSWORD == $f_db_password ) {
		$f_db_password = config_get( 'db_password' );
	}
	$f_timezone           = gpc_get( 'timezone', config_get( 'default_timezone' ) );

	# Set default prefix/suffix form variables ($f_db_table_XXX)
	$t_prefix_type = $f_db_type == 'oci8' ? $f_db_type : 'other';
	foreach( $t_prefix_defaults[$t_prefix_type] as $t_key => $t_value ) {
		${'f_' . $t_key} = gpc_get( $t_key, $t_value );
	}
}
$f_admin_username = gpc_get( 'admin_username', '' );
$f_admin_password = gpc_get( 'admin_password', '' );
if( CONFIGURED_PASSWORD == $f_admin_password ) {
	$f_admin_password = '';
}
$f_log_queries    = gpc_get_bool( 'log_queries', false );
$f_db_exists      = gpc_get_bool( 'db_exists', false );

if( $t_config_exists ) {
	if( 0 == $t_install_state ) {
		print_test( 'Config File Exists - Upgrade', true );

		print_test( 'Setting Database Type', '' !== $f_db_type, true, 'database type is blank?' );

		# @TODO: dsn config seems to be undefined, remove ?
		$t_db_conn_exists = ( $f_dsn !== '' || ( $f_database_name !== '' && $f_db_username !== '' && $f_hostname !== '' ) );
		# Oracle supports binding in two ways:
		#  - hostname, username/password and database name
		#  - tns name (insert into hostname field) and username/password, database name is still empty
		if( $f_db_type == 'oci8' ) {
			$t_db_conn_exists = $t_db_conn_exists || ( $f_database_name == '' && $f_db_username !== '' && $f_hostname !== '' );
		}
		print_test( 'Checking Database connection settings exist',
			$t_db_conn_exists,
			true,
			'database connection settings do not exist?' );

		print_test( 'Checking PHP support for database type',
			db_check_database_support( $f_db_type ), true,
			'database is not supported by PHP. Check that it has been compiled into your server.' );

		if( $f_db_type == 'mssql' ) {
			print_test( 'Checking PHP support for Microsoft SQL Server driver',
				version_compare( phpversion(), '5.3' ) < 0, true,
				'mssql driver is no longer supported in PHP >= 5.3, please use mssqlnative instead' );
		}
	}

	$g_db = ADONewConnection( $f_db_type );
	$t_result = @$g_db->Connect( $f_hostname, $f_db_username, $f_db_password, $f_database_name );
	if( $g_db->IsConnected() ) {
		$g_db_connected = true;
	}

	$t_cur_version = config_get( 'database_version', -1 );

	if( $t_cur_version > 1 ) {
		$g_database_upgrade = true;
		$f_db_exists = true;
	} else {
		if( 0 == $t_install_state ) {
			print_test( 'Config File Exists but Database does not', false, false, 'Bad config_inc.php?' );
		}
	}
}

if( $f_db_type == 'db2' ) {

	# If schema name is supplied, then separate it from database name.
	if( strpos( $f_database_name, '/' ) != false ) {
		$f_db2AS400 = $f_database_name;
		list( $f_database_name, $f_db_schema ) = explode( '/', $f_db2AS400, 2 );
	}
}

if( 0 == $t_install_state ) {
	?>

<!-- Check PHP Version -->
<?php print_test( ' Checking PHP version (your version is ' . phpversion() . ')', check_php_version( phpversion() ), true, 'Upgrade to a more recent version of PHP' );?>

<!-- Check Safe Mode -->
<?php
print_test( 'Checking if safe mode is enabled for install script',
	!ini_get( 'SAFE_MODE' ),
	true,
	'Disable safe_mode in php.ini before proceeding' ) ?>

<?php
	print_test( 'Checking there is no config_inc.php in 1.2.x location.', !file_exists( dirname( dirname( __FILE__ ) ) . '/config_inc.php' ), true, 'Move config_inc.php to config/config_inc.php.' );
	print_test( 'Checking there is no custom_constants_inc.php in 1.2.x location.', !file_exists( dirname( dirname( __FILE__ ) ) . '/custom_constants_inc.php' ), true, 'Move custom_constants_inc.php to config/custom_constants_inc.php.' );
	print_test( 'Checking there is no custom_strings_inc.php in 1.2.x location.', !file_exists( dirname( dirname( __FILE__ ) ) . '/custom_strings_inc.php' ), true, 'Move custom_strings_inc.php to config/custom_strings_inc.php.' );
	print_test( 'Checking there is no custom_functions_inc.php in 1.2.x location.', !file_exists( dirname( dirname( __FILE__ ) ) . '/custom_functions_inc.php' ), true, 'Move custom_functions_inc.php to config/custom_functions_inc.php.' );
	print_test( 'Checking there is no custom_relationships_inc.php in 1.2.x location.', !file_exists( dirname( dirname( __FILE__ ) ) . '/custom_relationships_inc.php' ), true, 'Move custom_relationships_inc.php to config/custom_relationships_inc.php.' );
	print_test( 'Checking there is no mc_config_defaults_inc.php in 1.2.x location.', !file_exists( dirname( dirname( __FILE__ ) ) . '/api/soap/mc_config_defaults_inc.php' ), true, 'Delete this file.' );
	print_test( 'Checking there is no mc_config_inc.php in 1.2.x location.', !file_exists( dirname( dirname( __FILE__ ) ) . '/api/soap/mc_config_inc.php' ), true, 'Move contents to config_inc.php file.' );
?>

</table>
<?php
	if( false == $g_failed ) {
		$t_install_state++;
	}
} # end install_state == 0

# got database information, check and install
if( 2 == $t_install_state ) {
	?>

<table width="100%" cellpadding="10" cellspacing="1">

<!-- Checking DB support-->
<?php
	print_test( 'Setting Database Type', '' !== $f_db_type, true, 'database type is blank?' );

	print_test( 'Checking PHP support for database type', db_check_database_support( $f_db_type ), true, 'database is not supported by PHP. Check that it has been compiled into your server.' );

	# ADOdb library version check
	# PostgreSQL, Oracle and MSSQL require at least 5.19. MySQL should be fine
	# with 5.10 but to simplify we align to the requirement of the others.
	$t_adodb_version = substr( $ADODB_vers, 1, strpos( $ADODB_vers, ' ' ) - 1 );
	print_test( 'Checking ADOdb Library version is at least ' . DB_MIN_VERSION_ADODB,
		version_compare( $t_adodb_version, DB_MIN_VERSION_ADODB, '>=' ),
		true,
		'Current version: ' . $ADODB_vers
	);

	print_test( 'Setting Database Hostname', '' !== $f_hostname, true, 'host name is blank' );
	print_test( 'Setting Database Username', '' !== $f_db_username, true, 'database username is blank' );
	print_test( 'Setting Database Password', '' !== $f_db_password, false, 'database password is blank' );
	print_test( 'Setting Database Name', '' !== $f_database_name || $f_db_type == 'oci8', true, 'database name is blank' );

	if( $f_db_type == 'db2' ) {
		print_test( 'Setting Database Schema', !is_blank( $f_db_schema ), true, 'must have a schema name for AS400 in the form of DBNAME/SCHEMA' );
	}
?>
<tr>
	<td bgcolor="#ffffff">
		Setting Admin Username
	</td>
	<?php
		if( '' !== $f_admin_username ) {
		print_test_result( GOOD );
	} else {
		print_test_result( BAD, false, 'admin user name is blank, using database user instead' );
		$f_admin_username = $f_db_username;
	}
	?>
</tr>
<tr>
	<td bgcolor="#ffffff">
		Setting Admin Password
	</td>
	<?php
		if( '' !== $f_admin_password ) {
			print_test_result( GOOD );
		} else {
			print_test_result( BAD, false, 'admin user password is blank, using database user password instead' );
			$f_admin_password = $f_db_password;
		}
	?>
</tr>

<!-- connect to db -->
<tr>
	<td bgcolor="#ffffff">
		Attempting to connect to database as admin
	</td>
	<?php
		$t_db_open = false;
	$g_db = ADONewConnection( $f_db_type );
	$t_result = @$g_db->Connect( $f_hostname, $f_admin_username, $f_admin_password );

	if( $t_result ) {

		# check if db exists for the admin
		$t_result = @$g_db->Connect( $f_hostname, $f_admin_username, $f_admin_password, $f_database_name );
		if( $t_result ) {
			$t_db_open = true;
			$f_db_exists = true;
		}
		if( $f_db_type == 'db2' ) {
			$t_result = $g_db->execute( 'set schema ' . $f_db_schema );
			if( $t_result === false ) {
				print_test_result( BAD, true, 'set schema failed: ' . $g_db->errorMsg() );
			}
		} else {
			print_test_result( GOOD );
		}

		# due to a bug in ADODB, this call prompts warnings, hence the @
		# the check only works on mysql if the database is open
		$t_version_info = @$g_db->ServerInfo();
	} else {
		print_test_result( BAD, true, 'Does administrative user have access to the database? ( ' . db_error_msg() . ' )' );
	}
	?>
</tr>
<?php
	if( $f_db_exists ) {
		?>
<tr>
	<td bgcolor="#ffffff">
		Attempting to connect to database as user
	</td>
	<?php
		$g_db = ADONewConnection( $f_db_type );
		$t_result = @$g_db->Connect( $f_hostname, $f_db_username, $f_db_password, $f_database_name );

		if( $t_result == true ) {
			$t_db_open = true;
			if( $f_db_type == 'db2' ) {
				$t_result = $g_db->execute( 'set schema ' . $f_db_schema );
				if( $t_result === false ) {
					print_test_result( BAD, true, 'set schema failed: ' . $g_db->errorMsg() );
				}
			} else {
				print_test_result( GOOD );
			}
		} else {
			print_test_result( BAD, false, 'Database user doesn\'t have access to the database ( ' . db_error_msg() . ' )' );
		}
		?>
</tr>

<?php
	}
	if( $t_db_open ) {
		?>
<!-- display database version -->
<tr>
	<td bgcolor="#ffffff">
		Checking Database Server Version
		<?php
		echo '<br /> Running ' . $f_db_type . ' version ' . nl2br( $t_version_info['description'] );
		?>
	</td>
	<?php
		$t_warning = '';
		$t_error = '';
		switch( $f_db_type ) {
			case 'mysql':
			case 'mysqli':
				if( version_compare( $t_version_info['version'], DB_MIN_VERSION_MYSQL, '<' ) ) {
					$t_error = 'MySQL ' . DB_MIN_VERSION_MYSQL . ' or later is required for installation.';
				}
				break;
			case 'mssql':
			case 'mssqlnative':
				if( version_compare( $t_version_info['version'], DB_MIN_VERSION_MSSQL, '<' ) ) {
					$t_error = 'SQL Server 2005 (' . DB_MIN_VERSION_MSSQL . ') or later is required for installation.';
				}
				break;
			case 'pgsql':
			case 'db2':
			default:
				break;
		}

		print_test_result( ( '' == $t_error ) && ( '' == $t_warning ), ( '' != $t_error ), $t_error . ' ' . $t_warning );
		?>
</tr>
<?php
	}?>
</table>
<?php
	if( false == $g_failed ) {
		$t_install_state++;
	} else {
		$t_install_state--; # a check failed, redisplay the questions
	}
} # end 2 == $t_install_state

# system checks have passed, get the database information
if( 1 == $t_install_state ) {
	?>

<form method='POST'>

<input name="install" type="hidden" value="2">

<table width="100%" cellpadding="10" cellspacing="1">

<tr>
	<td bgcolor="#e8e8e8" colspan="2">
		<span class="title">
			<?php echo
				( $g_database_upgrade ? 'Upgrade Options' : 'Installation Options' ),
				( $g_failed ? ': Checks Failed... ' : '' )
			?>
		</span>
	</td>
</tr>

<?php
# install-only fields: when upgrading, only display admin username and password
if( !$g_database_upgrade ) {
?>

<!-- Database type selection list -->
<tr>
	<td>
		Type of Database
	</td>
	<td>
		<!-- Default values for table prefix/suffix -->
		<div>
<?php
	# These elements are referenced by the db selection list's on change event
	# to populate the corresponding fields as appropriate
	foreach( $t_prefix_defaults as $t_db_type => $t_defaults ) {
		echo '<div id="default_' . $t_db_type . '" class="hidden">';
		foreach( $t_defaults as $t_key => $t_value ) {
			echo "\n\t" . '<span name="' . $t_key . '">' . $t_value . '</span>';
		}
		echo "\n" . '</div>' . "\n";
	}
?>
		</div>

		<select id="db_type" name="db_type">
<?php
			# Build selection list of available DB types
			$t_db_list = array(
				'mysqli'      => 'MySQL Improved',
				'mysql'       => 'MySQL',
				'mssql'       => 'Microsoft SQL Server',
				'mssqlnative' => 'Microsoft SQL Server Native Driver',
				'pgsql'       => 'PostgreSQL',
				'oci8'        => 'Oracle',
				'db2'         => 'IBM DB2',
			);
			# mysql is deprecated as of PHP 5.5.0
			if( version_compare( phpversion(), '5.5.0' ) >= 0 ) {
				unset( $t_db_list['mysql']);
			}
			# mssql is not supported with PHP >= 5.3
			if( version_compare( phpversion(), '5.3' ) >= 0 ) {
				unset( $t_db_list['mssql']);
			}

			foreach( $t_db_list as $t_db => $t_db_descr ) {
				echo '<option value="' . $t_db . '"' .
					( $t_db == $f_db_type ? ' selected="selected"' : '' ) . '>' .
					$t_db_descr . "</option>\n";
			}
?>
		</select>
	</td>
</tr>

<!-- Database server hostname -->
<tr>
	<td>
		Hostname (for Database Server)
	</td>
	<td>
		<input name="hostname" type="textbox" value="<?php echo $f_hostname?>">
	</td>
</tr>

<!-- Database username and password -->
<tr>
	<td>
		Username (for Database)
	</td>
	<td>
		<input name="db_username" type="textbox" value="<?php echo $f_db_username?>">
	</td>
</tr>

<tr>
	<td>
		Password (for Database)
	</td>
	<td>
		<input name="db_password" type="password" value="<?php
			echo !is_blank( $f_db_password ) && $t_config_exists
				? CONFIGURED_PASSWORD
				: $f_db_password;
		?>">
	</td>
</tr>

<!-- Database name -->
<tr>
	<td>
		Database name (for Database)
	</td>
	<td>
		<input name="database_name" type="textbox" value="<?php echo $f_database_name?>">
	</td>
</tr>
<?php
} # end install-only fields
?>

<!-- Admin user and password -->
<tr>
	<td>
		Admin Username (to <?php echo( !$g_database_upgrade ) ? 'create Database' : 'update Database'?> if required)
	</td>
	<td>
		<input name="admin_username" type="textbox" value="<?php echo $f_admin_username?>">
	</td>
</tr>

<tr>
	<td>
		Admin Password (to <?php echo( !$g_database_upgrade ) ? 'create Database' : 'update Database'?> if required)
	</td>
	<td>
		<input name="admin_password" type="password" value="<?php
			echo !is_blank( $f_admin_password ) && $f_admin_password == $f_db_password
				? CONFIGURED_PASSWORD
				: $f_admin_password;
		?>">
	</td>
</tr>

<?php
# install-only fields: when upgrading, only display admin username and password
if( !$g_database_upgrade ) {
	$t_prefix_labels = array(
		'db_table_prefix'        => 'Database Table Prefix',
		'db_table_plugin_prefix' => 'Database Plugin Table Prefix',
		'db_table_suffix'        => 'Database Table Suffix',
	);
	foreach( $t_prefix_defaults[$t_prefix_type] as $t_key => $t_value ) {
		echo "<tr>\n\t<td>\n";
		echo "\t\t" . $t_prefix_labels[$t_key] . "\n";
		echo "\t</td>\n\t<td>\n\t\t";
		echo '<input id="' . $t_key . '" name="' . $t_key . '" type="textbox" value="' . $f_db_table_prefix . '">';
		echo "\n\t</td>\n</tr>\n\n";
	}

	# Default timezone, get PHP setting if not defined in Mantis
	$t_tz = config_get_global( 'default_timezone' );
	if( is_blank( $t_tz ) ) {
		$t_tz = @date_default_timezone_get();
	}
?>
<!-- Timezone -->
<tr>
	<td>
		Default Time Zone
	</td>
	<td>
		<select id="timezone" name="timezone">
			<?php print_timezone_option_list( $t_tz ) ?>
		</select>
	</td>
</tr>
<?php
} # end install-only fields
?>

<!-- Printing SQL queries -->
<tr>
	<td>
		Print SQL Queries instead of Writing to the Database
	</td>
	<td>
		<input name="log_queries" type="checkbox" value="1" <?php echo( $f_log_queries ? 'checked="checked"' : '' )?>>
	</td>
</tr>

<!-- Submit button -->
<tr>
	<td>
		<?php echo ( $g_failed
			? 'Please correct failed checks and try again'
			: 'Attempt Installation' );
		?>
	</td>
	<td>
		<input name="go" type="submit" class="button" value="Install/Upgrade Database">
	</td>
</tr>

</table>
</form>

<?php
}  # end install_state == 1

# all checks have passed, install the database
if( 3 == $t_install_state ) {
	?>
<table width="100%" cellpadding="10" cellspacing="1">
<tr>
	<td bgcolor="#e8e8e8" colspan="2">
		<span class="title">Installing Database</span>
	</td>
</tr>
<?php if( !$f_log_queries ) {?>
<tr>
	<td bgcolor="#ffffff">
		Create database if it does not exist
	</td>
	<?php
		$t_result = @$g_db->Connect( $f_hostname, $f_admin_username, $f_admin_password, $f_database_name );

		if( $f_db_type == 'db2' ) {
			$t_rs = $g_db->Execute( "select * from SYSIBM.SCHEMATA WHERE SCHEMA_NAME = '" . $f_db_schema . "' AND SCHEMA_OWNER = '" . $f_db_username . "'" );
			if( $t_rs === false ) {
				echo '<br />false';
			}

			if( $t_rs->EOF ) {
				$t_result = false;
				echo $g_db->errorMsg();
			} else {
				$t_result = $g_db->execute( 'set schema ' . $f_db_schema );
			}
		}

		$t_db_open = false;

		if( $t_result == true ) {
			print_test_result( GOOD );
			$t_db_open = true;
		} else {
			# create db
			$g_db = ADONewConnection( $f_db_type );
			$t_result = $g_db->Connect( $f_hostname, $f_admin_username, $f_admin_password );

			$t_dict = NewDataDictionary( $g_db );

			if( $f_db_type == 'db2' ) {
				$t_rs = $g_db->Execute( 'CREATE SCHEMA ' . $f_db_schema );

				if( !$t_rs ) {
					$t_result = false;
					print_test_result( BAD, true, 'Does administrative user have access to create the database? ( ' . db_error_msg() . ' )' );
					$t_install_state--; # db creation failed, allow user to re-enter user/password info
				} else {
					print_test_result( GOOD );
					$t_db_open = true;
				}
			} else {
				$t_sqlarray = $t_dict->CreateDatabase( $f_database_name, array( 'mysql' => 'DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci' ) );
				$t_ret = $t_dict->ExecuteSQLArray( $t_sqlarray, false );
				if( $t_ret == 2 ) {
					print_test_result( GOOD );
					$t_db_open = true;
				} else {
					$t_error = db_error_msg();
					if( $f_db_type == 'oci8' ) {
						$t_db_exists = preg_match( '/ORA-01920/', $t_error );
					} else {
						$t_db_exists = strstr( $t_error, 'atabase exists' );
					}

					if( $t_db_exists ) {
						print_test_result( BAD, false, 'Database already exists? ( ' . db_error_msg() . ' )' );
					} else {
						print_test_result( BAD, true, 'Does administrative user have access to create the database? ( ' . db_error_msg() . ' )' );
						$t_install_state--; # db creation failed, allow user to re-enter user/password info
					}
				}
			}
		}
		?>
</tr>
<?php
	# Close the connection and clear the ADOdb object to free memory
	$g_db->Close();
	$g_db = null;
?>
<tr>
	<td bgcolor="#ffffff">
		Attempting to connect to database as user
	</td>
	<?php
		$g_db = ADONewConnection( $f_db_type );
		$t_result = @$g_db->Connect( $f_hostname, $f_db_username, $f_db_password, $f_database_name );

		if( $f_db_type == 'db2' ) {
			$t_result = $g_db->execute( 'set schema ' . $f_db_schema );
			if( $t_result === false ) {
				echo $g_db->errorMsg();
			}
		}

		if( $t_result == true ) {
			print_test_result( GOOD );
		} else {
			print_test_result( BAD, false, 'Database user doesn\'t have access to the database ( ' . db_error_msg() . ' )' );
		}
		$g_db->Close();
	?>
</tr>
<?php
	}

	# install the tables
	if( false == $g_failed ) {
		$g_db_connected = false;

		# fake out database access routines used by config_get
		config_set_global( 'db_type', $f_db_type );

		# Initialize table prefixes as specified by user
		config_set_global( 'db_table_prefix', $f_db_table_prefix );
		config_set_global( 'db_table_plugin_prefix', $f_db_table_plugin_prefix );
		config_set_global( 'db_table_suffix', $f_db_table_suffix );
		# database_api references this
		require_once( dirname( __FILE__ ) . '/schema.php' );
		$g_db = ADONewConnection( $f_db_type );
		$t_result = @$g_db->Connect( $f_hostname, $f_admin_username, $f_admin_password, $f_database_name );
		if( !$f_log_queries ) {
			$g_db_connected = true;

			# fake out database access routines used by config_get
		}
		$t_last_update = config_get( 'database_version', -1, ALL_USERS, ALL_PROJECTS );
		$t_last_id = count( $g_upgrade ) - 1;
		$i = $t_last_update + 1;
		if( $f_log_queries ) {
			echo '<tr><td bgcolor="#ffffff" col_span="2"> Database Creation Suppressed, SQL Queries follow <pre>';
		}

		# Make sure we do the upgrades using UTF-8 if needed
		if( $f_db_type === 'mysql' || $f_db_type === 'mysqli' ) {
			$g_db->execute( 'SET NAMES UTF8' );
		}

		if( $f_db_type == 'db2' ) {
			$t_result = $g_db->execute( 'set schema ' . $f_db_schema );
			if( $t_result === false ) {
				echo $g_db->errorMsg();
			}
		}

		$t_dict = NewDataDictionary( $g_db );

		# Special processing for specific schema versions
		# This allows execution of additional install steps, which are
		# not a Mantis schema upgrade but nevertheless required due to
		# changes in the code

		if( $t_last_update > 51 && $t_last_update < 189 ) {
			# Since MantisBT 1.1.0 / ADOdb 4.96 (corresponding to schema 51)
			# 'L' columns are BOOLEAN instead of SMALLINT
			# Check for any DB discrepancies and update columns if needed
			$t_bool_columns = check_pgsql_bool_columns();
			if( $t_bool_columns !== true ) {
				# Some columns need converting
				$t_msg = "PostgreSQL: check Boolean columns' actual type";
				if( is_array( $t_bool_columns ) ) {
					print_test(
						$t_msg,
						count( $t_bool_columns ) == 0,
						false,
						count( $t_bool_columns ) . ' columns must be converted to BOOLEAN' );
				} else {
					# We did not get an array => error occured
					print_test( $t_msg, false, true, $t_bool_columns );
				}

				# Convert the columns
				foreach( $t_bool_columns as $t_row ) {
					extract( $t_row, EXTR_PREFIX_ALL, 'v' );
					$t_null = $v_is_nullable ? 'NULL' : 'NOT NULL';
					$t_default = is_null( $v_column_default ) ? 'NULL' : $v_column_default;
					$t_sqlarray = $t_dict->AlterColumnSQL(
						$v_table_name,
						$v_column_name . ' L ' . $t_null . ' DEFAULT ' . $t_default );
					print_test(
						'Converting column ' . $v_table_name . '.' . $v_column_name . ' to BOOLEAN',
						2 == $t_dict->ExecuteSQLArray( $t_sqlarray, false ),
						true,
						print_r( $t_sqlarray, true ) );
					if( $g_failed ) {
						# Error occured, bail out
						break;
					}
				}
			}
		}
		# End of special processing for specific schema versions

		while( ( $i <= $t_last_id ) && !$g_failed ) {
			if( !$f_log_queries ) {
				echo '<tr><td bgcolor="#ffffff">';
			}

			$t_sql = true;
			$t_target = $g_upgrade[$i][1][0];

			switch( $g_upgrade[$i][0] ) {
				case 'InsertData':
					$t_sqlarray = call_user_func_array( $g_upgrade[$i][0], $g_upgrade[$i][1] );
					break;

				case 'UpdateSQL':
					$t_sqlarray = array(
						$g_upgrade[$i][1],
					);
					$t_target = $g_upgrade[$i][1];
					break;

				case 'UpdateFunction':
					$t_sqlarray = array(
						$g_upgrade[$i][1],
					);
					if( isset( $g_upgrade[$i][2] ) ) {
						$t_sqlarray[] = $g_upgrade[$i][2];
					}
					$t_sql = false;
					$t_target = $g_upgrade[$i][1];
					break;

				case null:
					# No-op upgrade step - required for oci8
					break;

				default:
					$t_sqlarray = call_user_func_array( array( $t_dict, $g_upgrade[$i][0] ), $g_upgrade[$i][1] );

					# 0: function to call, 1: function params, 2: function to evaluate before calling upgrade, if false, skip upgrade.
					if( isset( $g_upgrade[$i][2] ) ) {
						if( call_user_func_array( $g_upgrade[$i][2][0], $g_upgrade[$i][2][1] ) ) {
							$t_sqlarray = call_user_func_array( array( $t_dict, $g_upgrade[$i][0] ), $g_upgrade[$i][1] );
						} else {
							$t_sqlarray = array();
						}
					} else {
						$t_sqlarray = call_user_func_array( array( $t_dict, $g_upgrade[$i][0] ), $g_upgrade[$i][1] );
					}
					break;
			}
			if( $f_log_queries ) {
				if( $t_sql ) {
					foreach( $t_sqlarray as $t_sql ) {
						# "CREATE OR REPLACE TRIGGER" statements must end with "END;\n/" for Oracle sqlplus
						if( $f_db_type == 'oci8' && stripos( $t_sql, 'CREATE OR REPLACE TRIGGER' ) === 0 ) {
							$t_sql_end = PHP_EOL . '/';
						} else {
							$t_sql_end = ';';
						}
						echo htmlentities( $t_sql ) . $t_sql_end . PHP_EOL . PHP_EOL;
					}
				}
			} else {
				echo 'Schema step ' . $i . ': ';
				if( is_null( $g_upgrade[$i][0] ) ) {
					echo 'No operation';
					$t_ret = 2;
				} else {
					echo $g_upgrade[$i][0] . ' ( ' . $t_target . ' )';
					if( $t_sql ) {
						$t_ret = $t_dict->ExecuteSQLArray( $t_sqlarray, false );
					} else {
						if( isset( $t_sqlarray[1] ) ) {
							$t_ret = call_user_func( 'install_' . $t_sqlarray[0], $t_sqlarray[1] );
						} else {
							$t_ret = call_user_func( 'install_' . $t_sqlarray[0] );
						}
					}
				}
				echo '</td>';
				if( $t_ret == 2 ) {
					print_test_result( GOOD );
					config_set( 'database_version', $i );
				} else {
					$t_all_sql = '';
					foreach ( $t_sqlarray as $t_single_sql ) {
						$t_all_sql .= $t_single_sql . '<br />';
					}
					print_test_result( BAD, true, $t_all_sql  . $g_db->ErrorMsg() );
				}
				echo '</tr>';
			}
			$i++;
		}
		if( $f_log_queries ) {
			# add a query to set the database version
			echo 'INSERT INTO ' . db_get_table( 'config' ) . ' ( value, type, access_reqd, config_id, project_id, user_id ) VALUES (\'' . $t_last_id . '\', 1, 90, \'database_version\', 0, 0 );' . PHP_EOL;
			echo '</pre><br /><p style="color:red">Your database has not been created yet. Please create the database, then install the tables and data using the information above before proceeding.</p></td></tr>';
		}
	}
	if( false == $g_failed ) {
		$t_install_state++;
	} else {
		$t_install_state--;
	}

	?>
</table>
<?php
}  # end install_state == 3

# database installed, get any additional information
if( 4 == $t_install_state ) {

	# @todo to be written
	# must post data gathered to preserve it
	?>
		<input name="hostname" type="hidden" value="<?php echo $f_hostname?>">
		<input name="db_type" type="hidden" value="<?php echo $f_db_type?>">
		<input name="database_name" type="hidden" value="<?php echo $f_database_name?>">
		<input name="db_username" type="hidden" value="<?php echo $f_db_username?>">
		<input name="db_password" type="hidden" value="<?php echo $f_db_password?>">
		<input name="admin_username" type="hidden" value="<?php echo $f_admin_username?>">
		<input name="admin_password" type="hidden" value="<?php echo $f_admin_password?>">
		<input name="log_queries" type="hidden" value="<?php echo( $f_log_queries ? 1 : 0 )?>">
		<input name="db_exists" type="hidden" value="<?php echo( $f_db_exists ? 1 : 0 )?>">
<?php
	# must post <input name="install" type="hidden" value="5">
	# rather than the following line
	$t_install_state++;
}  # end install_state == 4

# all checks have passed, install the database
if( 5 == $t_install_state ) {
	$t_config_filename = $g_config_path . 'config_inc.php';
	$t_config_exists = file_exists( $t_config_filename );
	?>
<table width="100%" cellpadding="10" cellspacing="1">
<tr>
	<td bgcolor="#e8e8e8" colspan="2">
		<span class="title">Write Configuration File(s)</span>
	</td>
</tr>

<tr>
	<td bgcolor="#ffffff">
<?php
	if( !$t_config_exists ) {
?>
		Creating Configuration File (config/config_inc.php)<br />
		<span class="error-msg">
			(if this file is not created, create it manually with the contents below)
		</span>
<?php
	} else {
?>
		Updating Configuration File (config/config_inc.php)<br />
<?php
	}
?>
	</td>
<?php
	# Generating the config_inc.php file

	# Automatically generate a strong master salt/nonce for MantisBT
	# cryptographic purposes. If a strong source of randomness is not
	# available the user will have to manually set this value post
	# installation.
	$t_crypto_master_salt = crypto_generate_random_string( 32 );
	if( $t_crypto_master_salt !== null ) {
		$t_crypto_master_salt = base64_encode( $t_crypto_master_salt );
	}

	$t_config = '<?php' . PHP_EOL
		. '$g_hostname               = \'' . $f_hostname . '\';' . PHP_EOL
		. '$g_db_type                = \'' . $f_db_type . '\';' . PHP_EOL
		. '$g_database_name          = \'' . addslashes( $f_database_name ) . '\';' . PHP_EOL
		. '$g_db_username            = \'' . addslashes( $f_db_username ) . '\';' . PHP_EOL
		. '$g_db_password            = \'' . addslashes( $f_db_password ) . '\';' . PHP_EOL;

	switch( $f_db_type ) {
		case 'db2':
			$t_config .=  '$g_db_schema              = \'' . $f_db_schema . '\';' . PHP_EOL;
			break;
		default:
			break;
	}
	$t_config .= PHP_EOL;

	# Add lines for table prefix/suffix if different from default
	$t_insert_line = false;
	foreach( $t_prefix_defaults['other'] as $t_key => $t_value ) {
		$t_new_value = ${'f_' . $t_key};
		if( $t_new_value != $t_value ) {
			$t_config .= '$g_' . str_pad( $t_key, 25 ) . '= \'' . ${'f_' . $t_key} . '\';' . PHP_EOL;
			$t_insert_line = true;
		}
	}
	if( $t_insert_line ) {
		$t_config .= PHP_EOL;
	}

	$t_config .=
		  '$g_default_timezone       = \'' . $f_timezone . '\';' . PHP_EOL
		. PHP_EOL
		. "\$g_crypto_master_salt     = '" . addslashes( $t_crypto_master_salt ) . "';" . PHP_EOL;

	$t_write_failed = true;

	if( !$t_config_exists ) {
		if( $t_fd = @fopen( $t_config_filename, 'w' ) ) {
			fwrite( $t_fd, $t_config );
			fclose( $t_fd );
		}

		if( file_exists( $t_config_filename ) ) {
			print_test_result( GOOD );
			$t_write_failed = false;
		} else {
			print_test_result( BAD, false, 'cannot write ' . $t_config_filename );
		}
	} else {
		# already exists, see if the information is the same
		if( ( $f_hostname != config_get( 'hostname', '' ) ) ||
			( $f_db_type != config_get( 'db_type', '' ) ) ||
			( $f_database_name != config_get( 'database_name', '' ) ) ||
			( $f_db_schema != config_get( 'db_schema', '' ) ) ||
			( $f_db_username != config_get( 'db_username', '' ) ) ||
			( $f_db_password != config_get( 'db_password', '' ) ) ) {
			print_test_result( BAD, false, 'file ' . $g_config_path . 'config_inc.php' . ' already exists and has different settings' );
		} else {
			print_test_result( GOOD, false );
			$t_write_failed = false;
		}
	}
	?>
</tr>
<?php
	if( $t_crypto_master_salt === null ) {
		print_test( 'Setting Cryptographic salt in config file', false, false,
					'Unable to find a random number source for cryptographic purposes. You will need to edit ' .
					$g_config_path . 'config_inc.php' . ' and set a value for $g_crypto_master_salt manually' );
	}

	if( true == $t_write_failed ) {
?>
<tr>
	<td colspan="2">
		<table width="50%" cellpadding="10" cellspacing="1">
			<tr>
				<td>
					Please add the following lines to
					'<?php echo $g_absolute_path; ?>config_inc.php'
					before continuing to the database upgrade check:
				</td>
			</tr>
			<tr>
				<td>
					<pre><?php echo htmlentities( $t_config ); ?></pre>
				</td>
			</tr>
		</table>
	</td>
</tr>
<?php
	}
?>

</table>

<?php
	if( false == $g_failed ) {
		$t_install_state++;
	}
}

# end install_state == 5

if( 6 == $t_install_state ) {

	# post install checks
	?>
<table width="100%" bgcolor="#222222" cellpadding="10" cellspacing="1">
<tr>
	<td bgcolor="#e8e8e8" colspan="2">
		<span class="title">Checking Installation...</span>
	</td>
</tr>

<!-- Checking register_globals are off -->
<?php print_test( 'Checking for register_globals are off for mantis', !ini_get_bool( 'register_globals' ), false, 'change php.ini to disable register_globals setting' )?>

<tr>
	<td bgcolor="#ffffff">
		Attempting to connect to database as user
	</td>
	<?php
		$g_db = ADONewConnection( $f_db_type );
	$t_result = @$g_db->Connect( $f_hostname, $f_db_username, $f_db_password, $f_database_name );

	if( $t_result == true ) {
		print_test_result( GOOD );
	} else {
		print_test_result( BAD, false, 'Database user does not have access to the database ( ' . db_error_msg() . ' )' );
	}

	if( $f_db_type == 'db2' ) {
		$t_result = $g_db->execute( 'set schema ' . $f_db_schema );
		if( $t_result === false ) {
			echo $g_db->errorMsg();
		}
	}
	?>
</tr>
<tr>
	<td bgcolor="#ffffff">
		checking ability to SELECT records
	</td>
	<?php
	$t_query = 'SELECT COUNT(*) FROM ' . db_get_table( 'config' );
	$t_result = @$g_db->Execute( $t_query );

	if( $t_result != false ) {
		print_test_result( GOOD );
	} else {
		print_test_result( BAD, true, 'Database user does not have SELECT access to the database ( ' . db_error_msg() . ' )' );
	}
	?>
</tr>
<tr>
	<td bgcolor="#ffffff">
		checking ability to INSERT records
	</td>
	<?php
		$t_query = 'INSERT INTO ' . db_get_table( 'config' ) . ' ( value, type, access_reqd, config_id, project_id, user_id ) VALUES (\'test\', 1, 90, \'database_test\', 20, 0 )';
	$t_result = @$g_db->Execute( $t_query );

	if( $t_result != false ) {
		print_test_result( GOOD );
	} else {
		print_test_result( BAD, true, 'Database user does not have INSERT access to the database ( ' . db_error_msg() . ' )' );
	}
	?>
</tr>
<tr>
	<td bgcolor="#ffffff">
		checking ability to UPDATE records
	</td>
	<?php
		$t_query = 'UPDATE ' . db_get_table( 'config' ) . ' SET value=\'test_update\' WHERE config_id=\'database_test\'';
	$t_result = @$g_db->Execute( $t_query );

	if( $t_result != false ) {
		print_test_result( GOOD );
	} else {
		print_test_result( BAD, true, 'Database user does not have UPDATE access to the database ( ' . db_error_msg() . ' )' );
	}
	?>
</tr>
<tr>
	<td bgcolor="#ffffff">
		checking ability to DELETE records
	</td>
	<?php
		$t_query = 'DELETE FROM ' . db_get_table( 'config' ) . ' WHERE config_id=\'database_test\'';
	$t_result = @$g_db->Execute( $t_query );

	if( $t_result != false ) {
		print_test_result( GOOD );
	} else {
		print_test_result( BAD, true, 'Database user does not have DELETE access to the database ( ' . db_error_msg() . ' )' );
	}
	?>
</tr>
</table>
<?php
	if( false == $g_failed ) {
		$t_install_state++;
	}
}

# end install_state == 6

if( 7 == $t_install_state ) {
	# cleanup and launch upgrade
	?>
<table width="100%" bgcolor="#222222" cellpadding="10" cellspacing="1">
<tr>
	<td bgcolor="#e8e8e8" colspan="2">
		<span class="title">Installation Complete...</span>
	</td>
</tr>
<tr bgcolor="#ffffff">
	<td>
		MantisBT was installed successfully.
<?php if( $f_db_exists ) {?>
		<a href="../login_page.php">Continue</a> to log in.
<?php } else { ?>
		Please log in as the administrator and <a href="../login_page.php">create</a> your first project.
<?php } ?>
	</td>
	<?php print_test_result( GOOD ); ?>
</tr>
</table>

<?php
}

# end install_state == 7

if( $g_failed && $t_install_state != 1 ) {
	?>
<table width="100%" bgcolor="#222222" cellpadding="10" cellspacing="1">
<tr>
	<td bgcolor="#e8e8e8" colspan="2">
		<span class="title">Installation Failed...</span>
	</td>
</tr>
<tr>
	<td bgcolor="#ffffff">Please correct failed checks</td>
	<td bgcolor="#ffffff">
<form method='POST'>
		<input name="install" type="hidden" value="<?php echo $t_install_state?>">
		<input name="hostname" type="hidden" value="<?php echo $f_hostname?>">
		<input name="db_type" type="hidden" value="<?php echo $f_db_type?>">
		<input name="database_name" type="hidden" value="<?php echo $f_database_name?>">
		<input name="db_username" type="hidden" value="<?php echo $f_db_username?>">
		<input name="db_password" type="hidden" value="<?php
			echo !is_blank( $f_db_password ) && $t_config_exists
				? CONFIGURED_PASSWORD
				: $f_db_password;
		?>">
		<input name="admin_username" type="hidden" value="<?php echo $f_admin_username?>">
		<input name="admin_password" type="hidden" value="<?php
			echo !is_blank( $f_admin_password ) && $f_admin_password == $f_db_password
				? CONFIGURED_PASSWORD
				: $f_admin_password;
		?>">
		<input name="log_queries" type="hidden" value="<?php echo( $f_log_queries ? 1 : 0 )?>">
		<input name="db_exists" type="hidden" value="<?php echo( $f_db_exists ? 1 : 0 )?>">
		<input name="retry" type="submit" class="button" value="Retry">
</form>
	</td>
</tr>
</table>
<?php
}
?>
</body>
</html>
