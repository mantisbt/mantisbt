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
# config_inc.php hasn't been specified. Thus the database will not be opened
# and plugins will not be loaded.
define( 'MANTIS_MAINTENANCE_MODE', true );

require_once( dirname( dirname( __FILE__ ) ) . '/core.php' );
require_api( 'install_helper_functions_api.php' );
require_api( 'crypto_api.php' );
require_once( 'check/check_api.php');

$g_show_all = true;
$g_failed = false;
$g_database_upgrade = false;

/**
 * Print Info result
 *
 * @param string $p_description Description Message to display to user
 * @param string $p_info Info Message to display to user
 */
function print_info_row( $p_description, $p_info = null ) {
	echo "\t<tr>\n\t\t<td bgcolor=\"#ffffff\">$p_description</td>\n";
	echo "\t\t<td bgcolor=\"#ffffff\">$p_info</td>\n\t</tr>\n";
}

/**
 * Print Test result
 *
 * @param int $p_result Result - BAD|GOOD
 * @param bool $p_hard_fail Fail installation or soft warning
 * @param string $p_message Message to display to user
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
 * @param string $p_test_description Test Description
 * @param int $p_result Result - BAD|GOOD
 * @param bool $p_hard_fail Fail installation or soft warning
 * @param string $p_message Message to display to user
 */
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

http_content_headers();
html_begin();
html_head_begin();
html_css_link( 'admin.css' );
html_content_type();
html_title( 'MantisBT Administration - Installation' );
html_head_end();
?>
<body>
<?php html_top_banner(); ?>
<br/>
<form method='POST'>
<table id="check-results">
	<thead>
		<tr>
			<th colspan="2" class="thead1">
				<strong>MantisBT installation -
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
							echo "Installation Options";
							break;
					}
				?>
				</strong>
			</th>
		</tr>
	</thead>
<?php

$t_config_filename = $g_absolute_path . 'config_inc.php';
$t_config_exists = file_exists( $t_config_filename );

if( $t_config_exists && $t_install_state <= 1 ) {
	# config already exists - probably an upgrade
	$f_dsn                    = config_get( 'dsn', '' );
	$f_hostname               = config_get( 'hostname', '' );
	$f_db_type                = config_get( 'db_type', '' );
	$f_database_name          = config_get( 'database_name', '' );
	$f_db_username            = config_get( 'db_username', '' );
	$f_db_password            = config_get( 'db_password', '' );

	# Set default prefix/suffix form variables ($f_db_table_XXX)
	foreach( $t_prefix_defaults['other'] as $t_key => $t_value ) {
		${'f_' . $t_key} = $t_value;
	}
} else {
	# read control variables with defaults
	$f_dsn                = gpc_get( 'dsn', config_get( 'dsn', '' ) );
	$f_hostname           = gpc_get( 'hostname', config_get( 'hostname', '' ) );
	$f_db_type            = gpc_get( 'db_type', config_get( 'db_type', '' ) );
	$f_database_name      = gpc_get( 'database_name', config_get( 'database_name', '' ) );
	$f_db_username        = gpc_get( 'db_username', config_get( 'db_username', '' ) );
	$f_db_password        = gpc_get( 'db_password', config_get( 'db_password', '' ) );
	if( CONFIGURED_PASSWORD == $f_db_password ) {
		$f_db_password = config_get( 'db_password' );
	}
}
$f_admin_username = gpc_get( 'admin_username', '' );
$f_admin_password = gpc_get( 'admin_password', '' );
if( CONFIGURED_PASSWORD == $f_admin_password ) {
	$f_admin_password = '';
}
$f_db_exists      = gpc_get_bool( 'db_exists', false );

if( $t_config_exists ) {
	if( 0 == $t_install_state ) {
		print_test( "Config File Exists - Upgrade", true );
		print_test( 'Setting Database Type', '' !== $f_db_type, true, 'database type is blank?' );
		print_test( 'Checking Database connection settings exist', ( $f_dsn !== '' || ( $f_database_name !== '' && $f_db_username !== '' && $f_hostname !== '' ) ), true, 'database connection settings do not exist?' );
		print_test( 'Checking PHP support for database type', extension_loaded( $f_db_type ), true, 'database is not supported by PHP. Check that it has been compiled into your server.' );
	}

	$g_db = MantisDatabase::GetInstance($f_db_type);
	try {
		$t_result = $g_db->connect( null, $f_hostname, $f_db_username, $f_db_password, $f_database_name, null );
		$t_prefix = config_get_global( 'db_table_prefix' );
		$t_suffix = config_get_global( 'db_table_suffix' );

		$g_db->SetPrefixes( $t_prefix, $t_suffix );
	} catch (Exception $ex) {
		$t_result = false;
	}

	if( $g_db->IsConnected() ) {
		$g_db_connected = true;
	}

	$t_cur_version = config_get( 'database_version', -1 );

	if( $t_cur_version > 1 ) {
		$g_database_upgrade = true;
		$f_db_exists = true;
		print_info_row( "Current Schema Version", $t_cur_version );
	} else {
		if( 0 == $t_install_state ) {
			print_test( 'Config File Exists but Database does not', false, false, 'Bad config_inc.php?' );
		}
	}
}

if( 0 == $t_install_state ) {
	if( $g_database_upgrade == false ) {
	}
?>

<?php
	if( false == $g_failed ) {
		$t_install_state++;
	}
} # end install_state == 0

# got database information, check and install
if( 2 == $t_install_state ) {
	?>

<!-- Checking DB support-->
<?php
	check_print_test_row( 'Setting Database Type', '' !== $f_db_type, array( false => 'Database type is blank?', true => 'Database type set to ' . htmlentities( $f_db_type )));
	check_print_test_row( 'Checking PHP support for database type', extension_loaded( $f_db_type ), array( false => 'database is not supported by PHP. Check that it has been compiled into your server.' ));
	check_print_test_row( 'Setting Database Hostname', '' !== $f_hostname, array( false => 'host name is blank' ));
	check_print_test_row( 'Setting Database Username', '' !== $f_db_username, array( false => 'database username is blank' ));
	check_print_test_row( 'Setting Database Password', '' !== $f_db_password, array( false => 'database password is blank' ));
	check_print_test_row( 'Setting Database Name', '' !== $f_database_name, array( false => 'database name is blank' ));
	check_print_test_warn_row( 'Setting Admin Username', '' !== $f_admin_username, array( false => 'admin user name is blank, using database user instead'));
	if( '' == $f_admin_username ) {
		$f_admin_username = $f_db_username;
	}

	check_print_test_warn_row( 'Setting Admin Password', '' !== $f_admin_password, array( false => 'admin user password is blank, using database user password instead'));
	if( '' == $f_admin_password ) {
		$f_admin_password = $f_db_password;
	}

	$t_db_open = false;

	$g_db = MantisDatabase::GetInstance($f_db_type);
	try {
		$t_result = $g_db->connect( null, $f_hostname, $f_admin_username, $f_admin_password, null, null );
	} catch (Exception $ex) {
		$t_result = false;
	}
	check_print_test_row( 'Attempting to connect to database server as admin', $t_result, array( false => 'Does administrative user have access to the database? ( ' . db_last_error() . ' )'  ) );

	if( $t_result ) {
		# check if db exists for the admin
		try {
			$t_result = @$g_db->Connect( null, $f_hostname, $f_admin_username, $f_admin_password, $f_database_name, null );
		} catch (Exception $ex) {
			$t_result = false;
		}
		if( $t_result ) {
			$t_db_open = true;
			$f_db_exists = true;
		}
		check_print_test_row( 'Attempting to connect to open database as admin', $t_result, array( false => 'Does administrative user have access to the database? ( ' . db_last_error() . ' )'  ));

	}

	if( $f_db_exists ) {
		$g_db = MantisDatabase::GetInstance($f_db_type);
		try {
			$t_result = $g_db->connect( null, $f_hostname, $f_db_username, $f_db_password, $f_database_name, null );
		} catch (Exception $ex) {
			$t_result = false;
		}

		if( $t_result == true ) {
			$t_db_open = true;
		}
		check_print_test_row( 'Attempting to connect to database as user', $t_result, array( false => 'Database user doesn\'t have access to the database ( ' . db_last_error() . ' )'  ));
	}

	if( $t_db_open ) {
		$t_version_info = $g_db->GetServerInfo();
		check_print_info_row( 'Database Server Type', $f_db_type );
		check_print_info_row( 'Database Server Version', $t_version_info['version']);
		foreach( $g_db->diagnose() as $t_result ) {
			check_print_test_row( $t_result[0], $t_result[1], $t_result[2] );
		}
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

<tr>
	<td bgcolor="#e8e8e8" colspan="2">
		<input name="install" type="hidden" value="2">
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
		<select id="db_type" name="db_type">
<?php
		$t_db_types = explode(',',check_get_database_extensions(true));
		foreach( $t_db_types as $t_type ) {
			if( $f_db_type == $t_type ) {
				echo '<option value="' . $t_type . '" selected="selected">' . $t_type . '</option>';
			} else {
				echo '<option value="' . $t_type . '">' . $t_type . '</option>';
			}
		}
?>
		</select>
	</td>
</tr>
<tr>
	<td>Hostname (for Database Server)</td>
	<td><input name="hostname" type="textbox" value="<?php echo $f_hostname?>"></td>
</tr>
<tr>
	<td>Username (for Database)</td>
	<td><input name="db_username" type="textbox" value="<?php echo $f_db_username?>"></td>
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
			echo !is_blank( $f_admin_password) && $f_admin_password == $f_db_password
				? CONFIGURED_PASSWORD
				: $f_admin_password;
		?>">
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

<?php
}  # end install_state == 1

# all checks have passed, install the database
if( 3 == $t_install_state ) {
	check_print_section_header_row( 'Installing Database' );
	?>
<tr>
	<td bgcolor="#ffffff">
		Create database if it does not exist
	</td>
	<?php
		try {
			$t_result = $g_db->connect( null, $f_hostname, $f_admin_username, $f_admin_password, null, null );
		} catch (Exception $ex) {
			$t_result = false;
		}
		$t_db_open = false;

		if( $g_db->DatabaseExists( $f_database_name ) === true ) {
			print_test_result( GOOD );
			$t_db_open = true;
		} else {
			$dict = MantisDatabaseDict::GetDriverInstance($f_db_type);

			$sqlarray = $dict->CreateDatabase( $f_database_name );
			$ret = $dict->ExecuteSQLarray( $sqlarray );
			if( $ret == DB_QUERY_SUCCESS ) {
				print_test_result( GOOD );
				$t_db_open = true;
			} else {
					$t_error_msg = $g_db->GetLastError();
					if( strstr( $t_error_msg, 'atabase exists' ) ) {
						print_test_result( BAD, false, 'Database already exists? ( ' . $t_error_msg . ' )' );
				} else {
						print_test_result( BAD, true, 'Does administrative user have access to create the database? ( ' . $t_error_msg . ' )' );
					$t_install_state--; # db creation failed, allow user to re-enter user/password info
				}
			}
		}
		?>
</tr>
<tr>
	<td bgcolor="#ffffff">
		Attempting to connect to database as user
	</td>
	<?php
		$g_db = MantisDatabase::GetInstance($f_db_type);
		try {
			$t_result = $g_db->connect( null, $f_hostname, $f_db_username, $f_db_password, $f_database_name, null );
		} catch (Exception $ex) {
			$t_result = false;
		}

		if( $t_result == true ) {
			print_test_result( GOOD );
		} else {
			print_test_result( BAD, false, 'Database user doesn\'t have access to the database ( ' . $g_db->GetLastError() . ' )' );
		}
	?>
</tr>
<?php

	# install the tables
	if( false == $g_failed ) {
		$g_db_connected = false;

		# fake out database access routines used by config_get
		config_set_global( 'db_type', $f_db_type );

		# database_api references this
		require_once( dirname( __FILE__ ) . '/schema.php' );

		$g_db = MantisDatabase::GetInstance($f_db_type);
		try {
			$t_result = $g_db->connect( null, $f_hostname, $f_admin_username, $f_admin_password, $f_database_name, null );
		} catch (Exception $ex) {
			$t_result = false;
		}

		$t_last_update = config_get( 'database_version', -1, ALL_USERS, ALL_PROJECTS );
		$lastid = count( $upgrade ) - 1;
		$i = $t_last_update + 1;

		while(( $i <= $lastid ) && !$g_failed ) {
			$g_db->SetPrefixes( 'mantis_', '_table' );
			$dict = MantisDatabaseDict::GetDriverInstance($f_db_type);
			$t_sql = true;
			$t_target = $upgrade[$i][1][0];

			switch ($upgrade[$i][0]) {
				case 'InsertData':
					$sqlarray = call_user_func_array( $upgrade[$i][0], $upgrade[$i][1] );
					break;
				case 'UpdateSQL':
					$sqlarray = array(
						$upgrade[$i][1],
					);
					$t_target = $upgrade[$i][1];
					break;
				case 'UpdateFunction':
					$sqlarray = array(
						$upgrade[$i][1],
					);
					if( isset( $upgrade[$i][2] ) ) {
						$sqlarray[] = $upgrade[$i][2];
					}
					$t_sql = false;
					$t_target = $upgrade[$i][1];
					break;
				default:
					$sqlarray = call_user_func_array( array( $dict, $upgrade[$i][0] ), $upgrade[$i][1] );

					/* 0: function to call, 1: function params, 2: function to evaluate before calling upgrade, if false, skip upgrade. */
					if( isset( $upgrade[$i][2] ) ) {
						if( call_user_func_array( $upgrade[$i][2][0], $upgrade[$i][2][1] ) ) {
							$sqlarray = call_user_func_array( array( $dict, $upgrade[$i][0] ), $upgrade[$i][1] );
						} else {
							$sqlarray = array();
						}
					} else {
						$sqlarray = call_user_func_array( array( $dict, $upgrade[$i][0] ), $upgrade[$i][1] );
					}
					break;
			}

			if( $t_sql ) {
				$ret = $dict->ExecuteSQLarray( $sqlarray );
			} else {
				if( isset( $sqlarray[1] ) ) {
					$ret = call_user_func( 'install_' . $sqlarray[0], $sqlarray[1] );
				} else {
					$ret = call_user_func( 'install_' . $sqlarray[0] );
				}
			}
			if( $ret == DB_QUERY_SUCCESS ) {
				check_print_test_row( 'Schema Step ' . $i .': ' . $upgrade[$i][0] . ' ( ' . $t_target . ' )', true);
				config_set( 'database_version', $i );
			} else {
				$t_error = 'Database Error: ' . db_last_error() . '<br/><br/>Queries:<br/>';
				foreach ( $sqlarray as $single_sql )
					$t_error .= $single_sql . '<br /><br/>';
				check_print_test_row( 'Schema Step ' . $i .': ' . $upgrade[$i][0] . ' ( ' . $t_target . ' )', false, $t_error);
				$g_failed = true;
			}
			echo '</tr>';
			$i++;
		}

		if ( $t_last_update === -1 && !$g_failed) {
			$ret = call_user_func( 'install_create_admin_if_not_exist', array( 'administrator', 'root') );
			if( $ret == DB_QUERY_SUCCESS ) {
				print_test_result( GOOD );
			} else {
				print_test_result( BAD, true, $g_db->GetLastError() );
			}
		}
	}
	if( false == $g_failed ) {
		$t_install_state++;
	} else {
		$t_install_state--;
	}

	?>
<?php
}  # end install_state == 3

# database installed, get any additional information
if( 4 == $t_install_state ) {

	/** @todo to be written */
	// must post data gathered to preserve it
	?>
		<input name="hostname" type="hidden" value="<?php echo $f_hostname?>">
		<input name="db_type" type="hidden" value="<?php echo $f_db_type?>">
		<input name="database_name" type="hidden" value="<?php echo $f_database_name?>">
		<input name="db_username" type="hidden" value="<?php echo $f_db_username?>">
		<input name="db_password" type="hidden" value="<?php echo $f_db_password?>">
		<input name="admin_username" type="hidden" value="<?php echo $f_admin_username?>">
		<input name="admin_password" type="hidden" value="<?php echo $f_admin_password?>">
		<input name="db_exists" type="hidden" value="<?php echo( $f_db_exists ? 1 : 0 )?>">
<?php
	# must post <input name="install" type="hidden" value="5">
	# rather than the following line
	$t_install_state++;
}  # end install_state == 4

# all checks have passed, install the database
if( 5 == $t_install_state ) {
	$t_config_filename = $g_absolute_path . 'config_inc.php';
	$t_config_exists = file_exists( $t_config_filename );
	?>
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
		Creating Configuration File (config_inc.php)<br />
		<span class="error-msg">
			(if this file is not created, create it manually with the contents below)
		</span>
<?php
	} else {
?>
		Updating Configuration File (config_inc.php)<br />
<?php
	}
?>
	</td>
<?php
	# Generating the config_inc.php file

	$t_config = '<?php' . PHP_EOL
		. "\$g_hostname               = '$f_hostname';" . PHP_EOL
		. "\$g_db_type                = '$f_db_type';" . PHP_EOL
		. "\$g_database_name          = '" . addslashes( $f_database_name ) . "';" . PHP_EOL
		. "\$g_db_username            = '" . addslashes( $f_db_username ) . "';" . PHP_EOL
		. "\$g_db_password            = '" . addslashes( $f_db_password ) . "';" . PHP_EOL
		. "\$g_crypto_master_salt     = '" . base64_encode( crypto_generate_random_string( 32, false ) ) . "';" . PHP_EOL;

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


<?php
	if( false == $g_failed ) {
		$t_install_state++;
	}
}

# end install_state == 5

if( 6 == $t_install_state ) {

	# post install checks
	?>
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
	$g_db = MantisDatabase::GetInstance($f_db_type);
	$g_db->SetPrefixes( 'mantis_', '_table' );
	try {
		$t_result = $g_db->connect( null, $f_hostname, $f_db_username, $f_db_password, $f_database_name, null );
	} catch (Exception $ex) {
		$t_result = false;
	}

	if( $t_result == true ) {
		print_test_result( GOOD );
	} else {
		print_test_result( BAD, false, 'Database user doesn\'t have access to the database ( ' . $g_db->GetLastError() . ' )' );
	}

	?>
</tr>
<tr>
	<td bgcolor="#ffffff">
		checking ability to SELECT records
	</td>
	<?php
	$t_query = 'SELECT COUNT(*) FROM {config}';
	$t_result = @$g_db->Execute( $t_query );

	if( $t_result != false ) {
		print_test_result( GOOD );
	} else {
		print_test_result( BAD, true, 'Database user doesn\'t have SELECT access to the database ( ' . $g_db->GetLastError() . ' )' );
	}
	?>
</tr>
<tr>
	<td bgcolor="#ffffff">
		checking ability to INSERT records
	</td>
	<?php
		$t_query = "INSERT INTO {config} ( value, type, access_reqd, config_id, project_id, user_id ) VALUES ('test', 1, 90, 'database_test', 20, 0 )";
	$t_result = @$g_db->Execute( $t_query );

	if( $t_result != false ) {
		print_test_result( GOOD );
	} else {
		print_test_result( BAD, true, 'Database user doesn\'t have INSERT access to the database ( ' . $g_db->GetLastError() . ' )' );
	}
	?>
</tr>
<tr>
	<td bgcolor="#ffffff">
		checking ability to UPDATE records
	</td>
	<?php
		$t_query = "UPDATE {config} SET value='test_update' WHERE config_id='database_test'";
	$t_result = @$g_db->Execute( $t_query );

	if( $t_result != false ) {
		print_test_result( GOOD );
	} else {
		print_test_result( BAD, true, 'Database user doesn\'t have UPDATE access to the database ( ' . $g_db->GetLastError() . ' )' );
	}
	?>
</tr>
<tr>
	<td bgcolor="#ffffff">
		checking ability to DELETE records
	</td>
	<?php
		$t_query = "DELETE FROM {config} WHERE config_id='database_test'";
	$t_result = @$g_db->Execute( $t_query );

	if( $t_result != false ) {
		print_test_result( GOOD );
	} else {
		print_test_result( BAD, true, 'Database user doesn\'t have DELETE access to the database ( ' . db_error_msg() . ' )' );
	}
	?>
</tr>
<?php
	if( false == $g_failed ) {
		$t_install_state++;
	}
}

# end install_state == 6

if( 7 == $t_install_state ) {
	# cleanup and launch upgrade
	?>
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

<?php
}

# end install_state == 7

if( $g_failed && $t_install_state != 1 ) {
	?>
<tr>
	<td bgcolor="#e8e8e8" colspan="2">
		<span class="title">Installation Failed...</span>
	</td>
</tr>
<tr>
	<td bgcolor="#ffffff">Please correct failed checks</td>
	<td bgcolor="#ffffff">
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
		<input name="db_exists" type="hidden" value="<?php echo( $f_db_exists ? 1 : 0 )?>">
		<input name="retry" type="submit" class="button" value="Retry">
	</td>
</tr>
<?php
}
?>
</table>
</form>
</body>
</html>
