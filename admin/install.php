<?php
# MantisBT - a php based bugtracking system

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
 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

error_reporting( E_ALL );

/** @todo put this somewhere */
@set_time_limit( 0 );
$g_skip_open_db = true;  # don't open the database in database_api.php
define( 'MANTIS_INSTALLER', true );
define( 'PLUGINS_DISABLED', true );
require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core.php' );
require_once( 'install_functions.php' );
require_once( 'install_helper_functions.php' );
$g_error_send_page_header = false; # bypass page headers in error handler

$g_failed = false;
$g_database_upgrade = false;

# -------
# print test result
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

# -------
# print test header and result
function print_test( $p_test_description, $p_result, $p_hard_fail = true, $p_message = '' ) {

	echo "\n<tr><td bgcolor=\"#ffffff\">$p_test_description</td>";
	print_test_result( $p_result, $p_hard_fail, $p_message );
	echo "</tr>\n";
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
?>
<html>
<head>
<title> MantisBT Administration - Installation  </title>
<link rel="stylesheet" type="text/css" href="admin.css" />
</head>
<body>
<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
	<tr class="top-bar">
		<td class="links">
			[ <a href="index.php">Back to Administration</a> ]
		</td>
		<td class="title">
		<?php
switch( $t_install_state ) {
	case 6:
		echo "Post Installation Checks";
		break;
	case 5:
		echo "Install Configuration File";
		break;
	case 4:
		echo "Additional Configuration Information";
		break;
	case 3:
		echo "Install Database";
		break;
	case 2:
		echo "Check and Install Database";
		break;
	case 1:
		echo "Database Parameters";
		break;
	case 0:
	default:
		echo "Pre-Installation Check";
		break;
}
?>
		</td>
	</tr>
</table>
<br /><br />

<form method='POST'>
<?php
if( 0 == $t_install_state ) {
	?>
<table width="100%" bgcolor="#222222" border="0" cellpadding="10" cellspacing="1">
<tr>
	<td bgcolor="#e8e8e8" colspan="2">
		<span class="title">Checking Installation...</span>
	</td>
</tr>
<?php
}

$t_config_filename = $g_absolute_path . 'config_inc.php';
$t_config_exists = file_exists( $t_config_filename );
$f_hostname = null;
$f_db_type = null;
$f_database_name = null;
$f_db_username = null;
$f_db_password = null;
if( $t_config_exists ) {
	if( 0 == $t_install_state ) {
		print_test( "Config File Exists - Upgrade", true );
	}

	# config already exists - probably an upgrade

	$f_dsn = config_get( 'dsn', '' );
	$f_hostname = config_get( 'hostname', '' );
	$f_db_type = config_get( 'db_type', '' );
	$f_database_name = config_get( 'database_name', '' );
	$f_db_username = config_get( 'db_username', '' );
	$f_db_password = config_get( 'db_password', '' );

	if( 0 == $t_install_state ) {
		print_test( 'Setting Database Type', '' !== $f_db_type, true, 'database type is blank?' );
		print_test( 'Checking Database connection settings exist', ( $f_dsn !== '' || ( $f_database_name !== '' && $f_db_username !== '' && $f_hostname !== '' ) ), true, 'database connection settings do not exist?' );
		print_test( 'Checking PHP support for database type',
			db_check_database_support( $f_db_type ), true,
			'database is not supported by PHP. Check that it has been compiled into your server.'
		);
		if( $f_db_type == 'mssql' ) {
			print_test( 'Checking PHP support for Microsoft SQL Server driver',
				version_compare( phpversion(), '5.3' ) < 0, true,
				'mssql driver is no longer supported in PHP >= 5.3, please use mssqlnative instead'
			);
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
} else {
	# read control variables with defaults
	$f_hostname = gpc_get( 'hostname', config_get( 'hostname', 'localhost' ) );
	$f_db_type = gpc_get( 'db_type', config_get( 'db_type', '' ) );
	$f_database_name = gpc_get( 'database_name', config_get( 'database_name', 'bugtrack' ) );
	$f_db_username = gpc_get( 'db_username', config_get( 'db_username', '' ) );
	$f_db_password = gpc_get( 'db_password', config_get( 'db_password', '' ) );
	if( CONFIGURED_PASSWORD == $f_db_password ) {
		$f_db_password = config_get( 'db_password' );
	}
}
$f_admin_username = gpc_get( 'admin_username', '' );
$f_admin_password = gpc_get( 'admin_password', '' );
$f_log_queries = gpc_get_bool( 'log_queries', false );
$f_db_exists = gpc_get_bool( 'db_exists', false );

$f_db_schema = '';
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
	! ini_get ( 'SAFE_MODE' ),
	true,
	'Disable safe_mode in php.ini before proceeding' ) ?>

</table>
<?php
	if( false == $g_failed ) {
		$t_install_state++;
	}
} # end install_state == 0

# got database information, check and install
if( 2 == $t_install_state ) {
	?>

<table width="100%" border="0" cellpadding="10" cellspacing="1">
<!-- Setting config variables -->
<?php print_test( 'Setting Database Hostname', '' !== $f_hostname, true, 'host name is blank' )?>

<!-- Setting config variables -->
<?php print_test( 'Setting Database Type', '' !== $f_db_type, true, 'database type is blank?' )?>

<!-- Checking DB support-->
<?php
	print_test( 'Checking PHP support for database type', db_check_database_support( $f_db_type ), true, 'database is not supported by PHP. Check that it has been compiled into your server.' );

	print_test( 'Setting Database Username', '' !== $f_db_username, true, 'database username is blank' );
	print_test( 'Setting Database Password', '' !== $f_db_password, false, 'database password is blank' );
	print_test( 'Setting Database Name', '' !== $f_database_name, true, 'database name is blank' );

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
		if( '' != $f_db_password ) {
			print_test_result( BAD, false, 'admin user password is blank, using database user password instead' );
			$f_admin_password = $f_db_password;
		} else {
			print_test_result( GOOD );
		}
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
			$result = &$g_db->execute( 'set schema ' . $f_db_schema );
			if( $result === false ) {
				print_test_result( BAD, true, 'set schema failed: ' . $g_db->errorMsg() );
			}
		} else {
			print_test_result( GOOD );
		}
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
				$result = &$g_db->execute( 'set schema ' . $f_db_schema );
				if( $result === false ) {
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
		# due to a bug in ADODB, this call prompts warnings, hence the @
		# the check only works on mysql if the database is open
		$t_version_info = @$g_db->ServerInfo();
		echo '<br /> Running ' . $f_db_type . ' version ' . $t_version_info['description'];
		?>
	</td>
	<?php
		$t_warning = '';
		$t_error = '';
		switch( $f_db_type ) {
			case 'mysql':
			case 'mysqli':
				if( version_compare( $t_version_info['version'], '4.1.0', '<' ) ) {
					$t_error = 'MySQL 4.1.0 or later is required for installation.';
				}
				break;
			case 'mssql':
			case 'mssqlnative':
				if( version_compare( $t_version_info['version'], '9.0.0', '<' ) ) {
					$t_error = 'SQL Server 2005 or later is required for installation.';
				}
				break;
			case 'pgsql':
			case 'db2':
			default:
				break;
		}

		print_test_result(( '' == $t_error ) && ( '' == $t_warning ), ( '' != $t_error ), $t_error . ' ' . $t_warning );
		?>
</tr>
<?php
	}
	if( false == $g_failed ) {
		$t_install_state++;
	} else {
		$t_install_state--; # a check failed, redisplay the questions
	}
} # end 2 == $t_install_state

# system checks have passed, get the database information
if( 1 == $t_install_state ) {
	?>

<table width="100%" border="0" cellpadding="10" cellspacing="1">
<tr>
	<td bgcolor="#e8e8e8" colspan="2">
		<span class="title"><?php echo $g_database_upgrade ? 'Upgrade Options' : 'Installation Options'?></span>
	</td>
</tr>
<?php if( !$g_database_upgrade ) {?>
<tr>
	<td>
		Type of Database
	</td>
	<td>
		<select name="db_type">
		<?php
			// Build selection list of available DB types
			$t_db_list = array(
				'mysql'       => 'MySQL (default)',
				'mysqli'      => 'MySQLi',
				'mssql'       => 'Microsoft SQL Server',
				'mssqlnative' => 'Microsoft SQL Server Native Driver',
				'pgsql'       => 'PostgreSQL',
				'oci8'        => 'Oracle',
				'db2'         => 'IBM DB2',
			);

			// mssql is not supported with PHP >= 5.3
			if( version_compare( phpversion(), '5.3' ) >= 0 ) {
				unset( $t_db_list['mssql']);
			}

			foreach( $t_db_list as $t_db => $t_db_descr ) {
				echo '<option value="' . $t_db . '"' .
					( $t_db == $f_db_type ? ' selected="selected"' : '' ) . '>' .
					$t_db_descr . '</option>';
			}
		?>
		</select>
	</td>
</tr>
<?php
}

if( !$g_database_upgrade ) {?>
<tr>
	<td>
		Hostname (for Database Server)
	</td>
	<td>
		<input name="hostname" type="textbox" value="<?php echo $f_hostname?>"></input>
	</td>
</tr>
<?php
}

if( !$g_database_upgrade ) {?>
<tr>
	<td>
		Username (for Database)
	</td>
	<td>
		<input name="db_username" type="textbox" value="<?php echo $f_db_username?>"></input>
	</td>
</tr>
<?php
}

if( !$g_database_upgrade ) {?>
<tr>
	<td>
		Password (for Database)
	</td>
	<td>
		<input name="db_password" type="password" value="<?php echo( !is_blank( $f_db_password ) ? CONFIGURED_PASSWORD : "" )?>"></input>
	</td>
</tr>
<?php
}

if( !$g_database_upgrade ) {?>
<tr>
	<td>
		Database name (for Database)
	</td>
	<td>
		<input name="database_name" type="textbox" value="<?php echo $f_database_name?>"></input>
	</td>
</tr>
<?php
}?>

<tr>
	<td>
		Admin Username (to <?php echo( !$g_database_upgrade ) ? 'create Database' : 'update Database'?> if required)
	</td>
	<td>
		<input name="admin_username" type="textbox" value="<?php echo $f_admin_username?>"></input>
	</td>
</tr>

<tr>
	<td>
		Admin Password (to <?php echo( !$g_database_upgrade ) ? 'create Database' : 'update Database'?> if required)
	</td>
	<td>
		<input name="admin_password" type="password" value="<?php echo $f_admin_password?>"></input>
	</td>
</tr>

<tr>
	<td>
		Print SQL Queries instead of Writing to the Database
	</td>
	<td>
		<input name="log_queries" type="checkbox" value="1" <?php echo( $f_log_queries ? 'checked="checked"' : '' )?>></input>
	</td>
</tr>

<tr>
	<td>
		Attempt Installation
	</td>
	<td>
		<input name="go" type="submit" class="button" value="Install/Upgrade Database"></input>
	</td>
</tr>
<input name="install" type="hidden" value="2"></input>

</table>
<?php
}  # end install_state == 1

# all checks have passed, install the database
if( 3 == $t_install_state ) {
	?>
<table width="100%" border="0" cellpadding="10" cellspacing="1">
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
			$rs = $g_db->Execute( "select * from SYSIBM.SCHEMATA WHERE SCHEMA_NAME = '" . $f_db_schema . "' AND SCHEMA_OWNER = '" . $f_db_username . "'" );
			if( $rs === false ) {
				echo "<br />false";
			}

			if( $rs->EOF ) {
				$t_result = false;
				echo $g_db->errorMsg();
			} else {
				$t_result = &$g_db->execute( 'set schema ' . $f_db_schema );
			}
		}

		$t_db_open = false;

		if( $t_result == true ) {
			print_test_result( GOOD );
			$t_db_open = true;
		} else {
			// create db
			$g_db = ADONewConnection( $f_db_type );
			$t_result = $g_db->Connect( $f_hostname, $f_admin_username, $f_admin_password );

			$dict = NewDataDictionary( $g_db );

			if( $f_db_type == 'db2' ) {
				$rs = &$g_db->Execute( "CREATE SCHEMA " . $f_db_schema );

				if( !$rs ) {
					$t_result = false;
					print_test_result( BAD, true, 'Does administrative user have access to create the database? ( ' . db_error_msg() . ' )' );
					$t_install_state--; # db creation failed, allow user to re-enter user/password info
				} else {
					print_test_result( GOOD );
					$t_db_open = true;
				}
			} else {
				$sqlarray = $dict->CreateDatabase( $f_database_name, Array( 'mysql' => 'DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci' ) );
				$ret = $dict->ExecuteSQLArray( $sqlarray, false );
				if( $ret == 2 ) {
					print_test_result( GOOD );
					$t_db_open = true;
				} else {
					$t_error = db_error_msg();
					if( strstr( $t_error, 'atabase exists' ) ) {
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
	$g_db->Close();
?>
<tr>
	<td bgcolor="#ffffff">
		Attempting to connect to database as user
	</td>
	<?php
		$g_db = ADONewConnection( $f_db_type );
		$t_result = @$g_db->Connect( $f_hostname, $f_db_username, $f_db_password, $f_database_name );

		if( $f_db_type == 'db2' ) {
			$result = &$g_db->execute( 'set schema ' . $f_db_schema );
			if( $result === false ) {
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
		$GLOBALS['g_db_type'] = $f_db_type;

		# database_api references this
		require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'schema.php' );
		$g_db = ADONewConnection( $f_db_type );
		$t_result = @$g_db->Connect( $f_hostname, $f_admin_username, $f_admin_password, $f_database_name );
		if( !$f_log_queries ) {
			$g_db_connected = true;

			# fake out database access routines used by config_get
		}
		$t_last_update = config_get( 'database_version', -1, ALL_USERS, ALL_PROJECTS );
		$lastid = count( $upgrade ) - 1;
		$i = $t_last_update + 1;
		if( $f_log_queries ) {
			echo '<tr><td bgcolor="#ffffff" col_span="2"> Database Creation Suppressed, SQL Queries follow <pre>';
		}

		# Make sure we do the upgrades using UTF-8 if needed
		if ( $f_db_type === 'mysql' || $f_db_type === 'mysqli' ) {
			$g_db->execute( 'SET NAMES UTF8' );
		}

		if( $f_db_type == 'db2' ) {
			$result = &$g_db->execute( 'set schema ' . $f_db_schema );
			if( $result === false ) {
				echo $g_db->errorMsg();
			}
		}

		while(( $i <= $lastid ) && !$g_failed ) {
			if( !$f_log_queries ) {
				echo '<tr><td bgcolor="#ffffff">';
			}

			$dict = NewDataDictionary( $g_db );
			$t_sql = true;
			$t_target = $upgrade[$i][1][0];
			if( $upgrade[$i][0] == 'InsertData' ) {
				$sqlarray = call_user_func_array( $upgrade[$i][0], $upgrade[$i][1] );
			}
			else if( $upgrade[$i][0] == 'UpdateSQL' ) {
				$sqlarray = array(
					$upgrade[$i][1],
				);
				$t_target = $upgrade[$i][1];
			} else if( $upgrade[$i][0] == 'UpdateFunction' ) {
				$sqlarray = array(
					$upgrade[$i][1],
				);
				if( isset( $upgrade[$i][2] ) ) {
					$sqlarray[] = $upgrade[$i][2];
				}
				$t_sql = false;
				$t_target = $upgrade[$i][1];
			} else {
				/* 0: function to call, 1: function params, 2: function to evaluate before calling upgrade, if false, skip upgrade. */
				if( isset( $upgrade[$i][2] ) ) {
					if( call_user_func_array( $upgrade[$i][2][0], $upgrade[$i][2][1] ) ) {
						$sqlarray = call_user_func_array( Array( $dict, $upgrade[$i][0] ), $upgrade[$i][1] );
					} else {
						$sqlarray = array();
					}
				} else {
					$sqlarray = call_user_func_array( Array( $dict, $upgrade[$i][0] ), $upgrade[$i][1] );
				}
			}
			if( $f_log_queries ) {
				if( $t_sql ) {
					foreach( $sqlarray as $sql ) {
						echo htmlentities( $sql ) . ";\r\n\r\n";
					}
				}
			} else {
				echo 'Schema ' . $upgrade[$i][0] . ' ( ' . $t_target . ' )</td>';
				if( $t_sql ) {
					$ret = $dict->ExecuteSQLArray( $sqlarray, false );
				} else {
					if( isset( $sqlarray[1] ) ) {
						$ret = call_user_func( 'install_' . $sqlarray[0], $sqlarray[1] );
					} else {
						$ret = call_user_func( 'install_' . $sqlarray[0] );
					}
				}
				if( $ret == 2 ) {
					print_test_result( GOOD );
					config_set( 'database_version', $i );
				} else {
					$all_sql = '';
					foreach ( $sqlarray as $single_sql )
						$all_sql .= $single_sql . '<br />';
					print_test_result( BAD, true, $all_sql  . $g_db->ErrorMsg() );
				}
				echo '</tr>';
			}
			$i++;
		}
		if( $f_log_queries ) {
			# add a query to set the database version
			echo 'INSERT INTO ' . db_get_table( 'mantis_config_table' ) . ' ( value, type, access_reqd, config_id, project_id, user_id ) VALUES (\'' . $lastid . '\', 1, 90, \'database_version\', 0, 0 );' . "\r\n";
			echo '</pre></br /><p style="color:red">Your database has not been created yet. Please create the database, then install the tables and data using the information above before proceeding.</td></tr>';
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

	/** @todo to be written */
	// must post data gathered to preserve it
	?>
		<input name="hostname" type="hidden" value="<?php echo $f_hostname?>"></input>
		<input name="db_type" type="hidden" value="<?php echo $f_db_type?>"></input>
		<input name="database_name" type="hidden" value="<?php echo $f_database_name?>"></input>
		<input name="db_username" type="hidden" value="<?php echo $f_db_username?>"></input>
		<input name="db_password" type="hidden" value="<?php echo $f_db_password?>"></input>
		<input name="admin_username" type="hidden" value="<?php echo $f_admin_username?>"></input>
		<input name="admin_password" type="hidden" value="<?php echo $f_admin_password?>"></input>
		<input name="log_queries" type="hidden" value="<?php echo( $f_log_queries ? 1 : 0 )?>"></input>
		<input name="db_exists" type="hidden" value="<?php echo( $f_db_exists ? 1 : 0 )?>"></input>
<?php
	# must post <input name="install" type="hidden" value="5"></input>
	# rather than the following line
	$t_install_state++;
}  # end install_state == 4

# all checks have passed, install the database
if( 5 == $t_install_state ) {
	$t_config_filename = $g_absolute_path . 'config_inc.php';
	$t_config_exists = file_exists( $t_config_filename );
	?>
<table width="100%" border="0" cellpadding="10" cellspacing="1">
<tr>
	<td bgcolor="#e8e8e8" colspan="2">
		<span class="title">Write Configuration File(s)</span>
	</td>
</tr>

<tr>
	<td bgcolor="#ffffff">
		<?php
			if( !$t_config_exists ) {
		echo 'Creating Configuration File (config_inc.php)<br />';
		echo '<font color="red">(if this file is not created, create it manually with the contents below)</font>';
	} else {
		echo 'Updating Configuration File (config_inc.php)<br />';
	}
	?>
	</td>
	<?php
		$t_config = '<?php' . "\r\n";
	$t_config .= "\t\$g_hostname = '$f_hostname';\r\n";
	$t_config .= "\t\$g_db_type = '$f_db_type';\r\n";
	$t_config .= "\t\$g_database_name = '$f_database_name';\r\n";
	$t_config .= "\t\$g_db_username = '$f_db_username';\r\n";
	$t_config .= "\t\$g_db_password = '$f_db_password';\r\n";

	if( $f_db_type == 'db2' ) {
		$t_config .= "\t\$g_db_schema = '$f_db_schema';\r\n";
	}

	$t_config .= '?>' . "\r\n";
	$t_write_failed = true;

	if( !$t_config_exists ) {
		if( $fd = @fopen( $t_config_filename, 'w' ) ) {
			fwrite( $fd, $t_config );
			fclose( $fd );
		}

		if( file_exists( $t_config_filename ) ) {
			print_test_result( GOOD );
			$t_write_failed = false;
		} else {
			print_test_result( BAD, false, 'cannot write ' . $t_config_filename );
		}
	} else {
		# already exists, see if the information is the same
		if ( ( $f_hostname != config_get( 'hostname', '' ) ) ||
			( $f_db_type != config_get( 'db_type', '' ) ) ||
			( $f_database_name != config_get( 'database_name', '') ) ||
			( $f_db_schema != config_get( 'db_schema', '') ) ||
			( $f_db_username != config_get( 'db_username', '' ) ) ||
			( $f_db_password != config_get( 'db_password', '' ) ) ) {
			print_test_result( BAD, false, 'file ' . $g_absolute_path . 'config_inc.php' . ' already exists and has different settings' );
		} else {
			print_test_result( GOOD, false );
			$t_write_failed = false;
		}
	}
	?>
</tr>
<?php
	if( true == $t_write_failed ) {
		echo '<tr><table width="50%" border="0" cellpadding="10" cellspacing="1" align="center">';
		echo '<tr><td>Please add the following lines to ' . $g_absolute_path . 'config_inc.php before continuing to the database upgrade check:</td></tr>';
		echo '<tr><td><pre>' . htmlentities( $t_config ) . '</pre></td></tr></table></tr>';
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
<table width="100%" bgcolor="#222222" border="0" cellpadding="10" cellspacing="1">
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
		print_test_result( BAD, false, 'Database user doesn\'t have access to the database ( ' . db_error_msg() . ' )' );
	}

	if( $f_db_type == 'db2' ) {
		$result = &$g_db->execute( 'set schema ' . $f_db_schema );
		if( $result === false ) {
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
		$t_mantis_config_table = db_get_table( 'mantis_config_table' );
	$t_query = "SELECT COUNT(*) FROM $t_mantis_config_table";
	$t_result = @$g_db->Execute( $t_query );

	if( $t_result != false ) {
		print_test_result( GOOD );
	} else {
		print_test_result( BAD, true, 'Database user doesn\'t have SELECT access to the database ( ' . db_error_msg() . ' )' );
	}
	?>
</tr>
<tr>
	<td bgcolor="#ffffff">
		checking ability to INSERT records
	</td>
	<?php
		$t_query = "INSERT INTO $t_mantis_config_table ( value, type, access_reqd, config_id, project_id, user_id ) VALUES ('test', 1, 90, 'database_test', 20, 0 )";
	$t_result = @$g_db->Execute( $t_query );

	if( $t_result != false ) {
		print_test_result( GOOD );
	} else {
		print_test_result( BAD, true, 'Database user doesn\'t have INSERT access to the database ( ' . db_error_msg() . ' )' );
	}
	?>
</tr>
<tr>
	<td bgcolor="#ffffff">
		checking ability to UPDATE records
	</td>
	<?php
		$t_query = "UPDATE $t_mantis_config_table SET value='test_update' WHERE config_id='database_test'";
	$t_result = @$g_db->Execute( $t_query );

	if( $t_result != false ) {
		print_test_result( GOOD );
	} else {
		print_test_result( BAD, true, 'Database user doesn\'t have UPDATE access to the database ( ' . db_error_msg() . ' )' );
	}
	?>
</tr>
<tr>
	<td bgcolor="#ffffff">
		checking ability to DELETE records
	</td>
	<?php
		$t_query = "DELETE FROM $t_mantis_config_table WHERE config_id='database_test'";
	$t_result = @$g_db->Execute( $t_query );

	if( $t_result != false ) {
		print_test_result( GOOD );
	} else {
		print_test_result( BAD, true, 'Database user doesn\'t have DELETE access to the database ( ' . db_error_msg() . ' )' );
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
<p>Install was successful.</p>
<?php if( $f_db_exists ) {?>
<p><a href="../login_page.php">Continue</a> to log into Mantis</p>
<?php
	} else {?>
<p>Please log in as the administrator and <a href="../login_page.php">create</a> your first project.

<?php
	}
}

# end install_state == 7

if( $g_failed ) {
	?>
<table width="100%" bgcolor="#222222" border="0" cellpadding="10" cellspacing="1">
<tr>
	<td bgcolor="#e8e8e8" colspan="2">
		<span class="title">Checks Failed...</span>
	</td>
</tr>
<tr>
	<td bgcolor="#ffffff">Please correct failed checks</td>
	<td bgcolor="#ffffff">
		<input name="install" type="hidden" value="<?php echo $t_install_state?>"></input>
		<input name="hostname" type="hidden" value="<?php echo $f_hostname?>"></input>
		<input name="db_type" type="hidden" value="<?php echo $f_db_type?>"></input>
		<input name="database_name" type="hidden" value="<?php echo $f_database_name?>"></input>
		<input name="db_username" type="hidden" value="<?php echo $f_db_username?>"></input>
		<input name="db_password" type="hidden" value="<?php echo $f_db_password?>"></input>
		<input name="admin_username" type="hidden" value="<?php echo $f_admin_username?>"></input>
		<input name="admin_password" type="hidden" value="<?php echo $f_admin_password?>"></input>
		<input name="log_queries" type="hidden" value="<?php echo( $f_log_queries ? 1 : 0 )?>"></input>
		<input name="db_exists" type="hidden" value="<?php echo( $f_db_exists ? 1 : 0 )?>"></input>
		<input name="retry" type="submit" class="button" value="Retry"></input>
	</td>
</tr>
</table>
<?php
}
?>
</form>
</body>
</html>
