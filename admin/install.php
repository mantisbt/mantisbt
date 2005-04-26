<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: install.php,v 1.1 2005-04-26 17:35:08 thraxisp Exp $
	# --------------------------------------------------------
?>
<?php
	error_reporting( E_ALL );

	//@@@ put this somewhere
	set_time_limit ( 0 ) ;
	$g_skip_open_db = true;  # don't open the database in database_api.php
	@require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core.php' );

	define( 'BAD', 0 );
	define( 'GOOD', 1 );
	$g_failed = false;

	function print_test_result( $p_result, $p_hard_fail=true, $p_message='' ) {
		global $g_failed;
		echo '<td ';
		if ( BAD == $p_result ) {
			if ( $p_hard_fail ) {
				$g_failed = true;
				echo 'bgcolor="red">BAD';
			} else {
				echo 'bgcolor="pink">BAD';
			}
		}

		if ( GOOD == $p_result ) {
			echo 'bgcolor="green">GOOD';
		}
		if ( '' !== $p_message ) {
			echo '<br />' . $p_message;
		}
		echo '<td>';
	}

	//temporary default values
	$t_hostname = 'localhost';
	$t_database_name = 'bugtrack';
	$t_db_username = '';
	$t_db_password = '';
	
	# install_state
	#   0 = no checks done
	#   1 = server ok, get database information
	#   2 = check the database information
	#   3 = install the database
	#   4 = get additional config file information
	#   5 = write the config file
	$t_install_state = gpc_get_int( 'install', 0 );

?>
<html>
<head>
<title> Mantis Administration - Installation  </title>
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
			switch ( $t_install_state ) {
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
if ( 0 == $t_install_state ) {
?>
<table width="100%" bgcolor="#222222" border="0" cellpadding="10" cellspacing="1">
<tr>
	<td bgcolor="#e8e8e8" colspan="2">
		<span class="title">Checking Installation...</span>
	</td>
</tr>

<!-- Check Php Version -->
<tr>
	<td bgcolor="#ffffff">
		Checking  PHP Version (Your version is <?php echo phpversion(); ?>)
	</td>
	<?php
		if (phpversion() == '4.0.6') {
			print_test_result( GOOD );
		} else {
			if ( function_exists ( 'version_compare' ) ) {
				if ( version_compare ( phpversion() , '4.0.6', '>=' ) ) {
					print_test_result( GOOD );
				} else {
					print_test_result( BAD );
				}
			} else {
			 	print_test_result( BAD );
			}
		}
	?>
</tr>

<!-- Check Safe Mode -->
<tr>
	<td bgcolor="#ffffff">
		Checking If Safe mode is enabled for install script
	</td>
	<?php
		if ( ! ini_get ( 'SAFE_MODE' ) ) {
			print_test_result( GOOD );
		} else {
			print_test_result( BAD );
		}
	?>
</tr>

<!-- Checking MD5 -->
<tr>
	<td bgcolor="#ffffff">
		Checking for MD5 Crypt() support
	</td>
	<?php
		if ( 1 === CRYPT_MD5 ) {
			print_test_result( GOOD );
		} else {
			print_test_result( BAD, false );
		}
	?>
</tr>

<!-- Checking register_globals are off -->
<tr>
	<td bgcolor="#ffffff">
		Checking for register_globals are off for mantis
	</td>
	<?php
		if ( ! ini_get_bool( 'register_globals' ) ) {
			print_test_result( GOOD );
		} else {
			print_test_result( BAD );
		}
	?>
</tr>

<!-- Checking config file is writable -->
<tr>
	<td bgcolor="#ffffff">
		Checking that configuration file can be created
	</td>
	<?php
		$t_fd = @fopen( $g_absolute_path . DIRECTORY_SEPARATOR . 'config_inc.php', 'x' );
		if ( false !== $t_fd ) {
			print_test_result( GOOD );
			fclose( $t_fd );
			@unlink( $g_absolute_path . DIRECTORY_SEPARATOR . 'config_inc.php' );
		} else {
			print_test_result( BAD );
		}
	?>
</tr>
</table>
<?php
	if ( false == $g_failed ) {
		$t_install_state++;
	}
} # end install_state == 0

# got database information, check and install
if ( 2 == $t_install_state ) {
?>

<table width="100%" border="0" cellpadding="10" cellspacing="1">
<!-- Setting config variables -->
<tr>
	<td bgcolor="#ffffff">
		Setting Database Hostname
	</td>
	<?php
			$f_hostname = gpc_get('hostname', '');
			$g_config_entry['hostname'] = '\''.$f_hostname.'\'';
			if ( '' !== $f_hostname ) {
				print_test_result( GOOD );
			} else {
				print_test_result( BAD );
			}
	?>
</tr>

<!-- Setting config variables -->
<tr>
	<td bgcolor="#ffffff">
		Setting Database Type
	</td>
	<?php
			$f_db_type = gpc_get('db_type', '');
			$g_config_entry['db_type'] = '\''.$f_db_type.'\'';
			if ( '' !== $f_db_type ) {
				print_test_result( GOOD );
			} else {
				print_test_result( BAD );
			}
	?>
</tr>

<!-- Checking DB support-->
<tr>
	<td bgcolor="#ffffff">
		Checking PHP support for database type
	</td>
	<?php
			$t_support = false;
			switch ($f_db_type) {
				case 'mysql':
					$t_support = function_exists('mysql_connect');
					break;
				case 'pgsql':
					$t_support = function_exists('pg_connect');
					break;
				case 'mssql':
					$t_support = function_exists('mssql_connect');
					break;
				default:
					$t_support = false;
			}

			if ( $t_support ) {
				print_test_result( GOOD );
			} else {
				print_test_result( BAD );
			}
	?>
</tr>

<!-- Setting config variables -->
<tr>
	<td bgcolor="#ffffff">
		Setting Database Username
	</td>
	<?php
			$f_db_username = gpc_get('db_username', '');
			$g_config_entry['db_username'] = '\''.$f_db_username.'\'';
			if ( '' !== $f_db_username ) {
				print_test_result( GOOD );
			} else {
				print_test_result( BAD );
			}
	?>
</tr>

<!-- Setting config variables -->
<tr>
	<td bgcolor="#ffffff">
		Setting Database Password
	</td>
	<?php
			$f_db_password = gpc_get('db_password', '');
			$g_config_entry['db_password'] = '\''.$f_db_password.'\'';
			if ( '' !== $f_db_password ) {
				print_test_result( GOOD );
			} else {
				print_test_result( BAD, false );
			}
	?>
</tr>

<!-- Setting config variables -->
<tr>
	<td bgcolor="#ffffff">
		Setting Database Name
	</td>
	<?php
			$f_database_name = gpc_get('database_name', '');
			$g_config_entry['database_name'] = '\''.$f_database_name.'\'';
			if ( '' !== $f_database_name ) {
				print_test_result( GOOD );
			} else {
				print_test_result( BAD );
			}
	?>
</tr>

<!-- Setting config variables -->
<tr>
	<td bgcolor="#ffffff">
		Setting Admin Username
	</td>
	<?php
			$f_adm_username = gpc_get( 'admin_username', '' );
			if ( '' !== $f_adm_username ) {
				print_test_result( GOOD );
			} else {
				print_test_result( BAD );
			}
	?>
</tr>

<!-- Setting config variables -->
<tr>
	<td bgcolor="#ffffff">
		Setting Admin Password
	</td>
	<?php
			$f_adm_password = gpc_get( 'admin_password', '');
			if ( '' !== $f_adm_password ) {
				print_test_result( GOOD );
			} else {
				print_test_result( BAD );
			}
	?>
</tr>

<!-- Setting config variables -->
<tr>
	<td bgcolor="#ffffff">
		Attempting to connect to database as admin
	</td>
	<?php
		$g_db = ADONewConnection($f_db_type);
		$t_result = @$g_db->Connect($f_hostname, $f_adm_username, $f_adm_password);

		if ( $t_result == true ) {
			print_test_result( GOOD );
		} else {
			print_test_result( BAD );
		}
	?>
</tr>
<tr>
	<td bgcolor="#ffffff">
		Attempting to connect to database as user
	</td>
	<?php
		$g_db = ADONewConnection($f_db_type);
		$t_result = @$g_db->Connect($f_hostname, $f_db_username, $f_db_password, $f_database_name);

		if ( $t_result == true ) {
			print_test_result( GOOD );
		} else {
			print_test_result( BAD, false ); # may fail if db doesn't exist, will recheck later
		}
	?>
</tr>

<?php
	if ( false == $g_failed ) {
		$t_install_state++;
	}
} # end 2 == $t_install_state

# system checks have passed, get the database information
if ( 1 == $t_install_state ) {
?>

<table width="100%" border="0" cellpadding="10" cellspacing="1">
<tr>
	<td bgcolor="#e8e8e8" colspan="2">
		<span class="title">Installation Options</span>
	</td>
</tr>

<tr>
	<td>
		Type of Database
	</td>
	<td>
		<select name="db_type">
		<option value="mysql">MySql (default)</option>
		<option value="odbc_mssql">Microsoft SQL Server ODBC (experimental)</option>
		<option value="ado_mssql">Microsoft SQL Server ADO (experimental)</option>
		<option value="pgsql">PGSQL (experimental)</option>
	</td>
</tr>

<tr>
	<td>
		Hostname (for Database Server)
	</td>
	<td>
		<input name="hostname" type="textbox" value="<?php echo ( ( '' != $f_hostname ) ? $f_hostname : $t_hostname ); ?>"></input>
	</td>
</tr>

<tr>
	<td>
		Username (for Database)
	</td>
	<td>
		<input name="db_username" type="textbox" value="<?php echo ( ( '' != $f_db_username ) ? $f_db_username : $t_db_username ); ?>"></input>
	</td>
</tr>

<tr>
	<td>
		Password (for Database)
	</td>
	<td>
		<input name="db_password" type="password" value="<?php echo ( ( '' != $f_db_password ) ? $f_db_password : $t_db_password ); ?>"></input>
	</td>
</tr>

<tr>
	<td>
		Database name (for Database)
	</td>
	<td>
		<input name="database_name" type="textbox" value="<?php echo ( ( '' != $f_database_name ) ? $f_database_name : $t_database_name ); ?>"></input>
	</td>
</tr>
<tr>
	<td>
		Admin Username (to create Database)
	</td>
	<td>
		<input name="admin_username" type="textbox" value="<?php echo ( ( '' != $f_adm_username ) ? $f_adm_username : $t_db_username ); ?>"></input>
	</td>
</tr>

<tr>
	<td>
		Admin Password (to create Database)
	</td>
	<td>
		<input name="admin_password" type="password" value="<?php echo ( ( '' != $f_adm_password ) ? $f_adm_password : $t_db_password ); ?>"></input>
	</td>
</tr>

<tr>
<tr>
	<td>
		Attempt Installation
	</td>
	<td>
		<input name="go" type="submit" value="Install Database"></input>
	</td>
</tr>
<input name="install" type="hidden" value="2"></input>

</table>
<?php
}  # end install_state == 1

# all checks have passed, install the database
if ( 3 == $t_install_state ) {
?>

<table width="100%" border="0" cellpadding="10" cellspacing="1">
<tr>
	<td bgcolor="#ffffff">
		Create database if it does not exist
	</td>
	<?php
		$t_result = @$g_db->Connect( $f_hostname, $f_adm_username, $f_adm_password, $f_database_name );

		if ( $t_result == true ) {
			print_test_result( GOOD );
		} else {
			// create db
			$g_db = ADONewConnection( $f_db_type );
			$t_result = $g_db->Connect( $f_hostname, $f_adm_username, $f_adm_password );
			$dict = NewDataDictionary( $g_db );
			$sqlarray = $dict->CreateDatabase( $f_database_name );
			$ret = $dict->ExecuteSQLArray( $sqlarray );
			if( $ret == 2) {
				print_test_result( GOOD );
			} else {
				print_test_result( BAD );
			}
	}
	?>
</tr>
<tr>
	<td bgcolor="#ffffff">
		Attempting to connect to database as user
	</td>
	<?php
		$g_db = ADONewConnection($f_db_type);
		$t_result = @$g_db->Connect($f_hostname, $f_db_username, $f_db_password, $f_database_name);

		if ( $t_result == true ) {
			print_test_result( GOOD );
		} else {
			print_test_result( BAD ); 
		}
	?>
</tr>
<?php
	if ( false == $g_failed ) {
		require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'schema.php' );
		$g_db = ADONewConnection( $f_db_type );
		$t_result = @$g_db->Connect( $f_hostname, $f_db_username, $f_db_password, $f_database_name );
		$lastid = sizeof( $upgrade );
		for($i = 0; $i < $lastid; $i++) {
?>
<tr>
	<td bgcolor="#ffffff">
		Create Schema ( Adding Schema Version <?php echo $i?> )
	</td>
<?php
			$dict = NewDataDictionary($g_db);
			$sqlarray = call_user_func_array(Array($dict,$upgrade[$i][1]),$upgrade[$i][2]);
			$ret = $dict->ExecuteSQLArray($sqlarray);
			if ( $ret == 2 ) {
				print_test_result( GOOD );
			} else {
				print_test_result( BAD, true, $sqlarray[0] );
			}
			echo '</tr>';
		}
	}
	if ( false == $g_failed ) {
		$t_install_state++;
	}
?>
</table>
<?php
}  # end install_state == 3

# database installed, get any additional information
if ( 4 == $t_install_state ) {
	# @@@ to be written
	#  must post $g_config_entry to preserve it
	# must post <input name="install" type="hidden" value="5"></input>
	# rather than the following line
		$t_install_state++;
	}  # end install_state == 4
	
# all checks have passed, install the database
if ( 5 == $t_install_state ) {
?>
<table width="100%" border="0" cellpadding="10" cellspacing="1">
<tr>
	<td bgcolor="#ffffff">
		Creating Default Config File
	</td>
	<?php
		if ( /*!$g_failed &&*/ ! file_exists ( $g_absolute_path .DIRECTORY_SEPARATOR.'config_inc.php' ) ) {
			$fd = fopen($g_absolute_path.DIRECTORY_SEPARATOR.'config_inc.php','x');
			fwrite($fd, '<?php'."\r\n");
			foreach ( $g_config_entry as $key => $value) {
				fwrite($fd,'$g_'.$key.'='.$value.";\r\n");
			}
			fwrite($fd, '?>'."\r\n");

			fclose($fd);
			if ( file_exists ( $g_absolute_path .DIRECTORY_SEPARATOR.'config_inc.php' ) ) {
				print_test_result( GOOD );
			} else {
				print_test_result( BAD );
			}
		} else {
			// already exists
			print_test_result( BAD );

		}
	?>
</tr>
</table>

<?php
	if ( false == $g_failed ) {
		$t_install_state++;
	}
}  # end install_state == 5

if ( 6 == $t_install_state ) {
?>
<p>Install was successful.</p>
<p>Log in <a href="<?php echo dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR; ?>">here</a></p>

<?php
} # end install_state == 6


if( $g_failed ) { 
?>
	<p>Please correct failed checks</p>
<?php 
} 
?>

</form>

</body>
</html>