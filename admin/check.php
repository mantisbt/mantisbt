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
 * @copyright Copyright (C) 2002 - 2009  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

error_reporting( E_ALL );

$g_skip_open_db = true;  # don't open the database in database_api.php

/**
 * MantisBT Core API's
 */
require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core.php' );

$t_core_path = config_get_global( 'core_path' );

require_once( $t_core_path . 'email_api.php' );
require_once( $t_core_path . 'database_api.php' );

$f_password = gpc_get_string( 'password', null );

define( 'BAD', 0 );
define( 'GOOD', 1 );

function print_test_result( $p_result ) {
	if( BAD == $p_result ) {
		echo '<td bgcolor="#ff0088">BAD</td>';
	}

	if( GOOD == $p_result ) {
		echo '<td bgcolor="#00ff88">GOOD</td>';
	}
}

function print_yes_no( $p_result ) {
	if(( 0 === $p_result ) || ( "no" === strtolower( $p_result ) ) ) {
		echo 'No';
	}

	if(( 1 === $p_result ) || ( "yes" === strtolower( $p_result ) ) ) {
		echo 'Yes';
	}
}

function print_test_row( $p_description, $p_pass, $p_info = null ) {
	echo '<tr>';
	echo '<td bgcolor="#ffffff">';
	echo $p_description;
	if( $p_info != null) {
		echo '<br />';
		echo '<i>' . $p_info . '</i>';
	}
	echo '</td>';

	if( $p_pass ) {
		print_test_result( GOOD );
	} else {
		print_test_result( BAD );
	}

	echo '</tr>';
}

function test_bug_download_threshold() {
	$t_pass = true;

	$t_view_threshold = config_get_global( 'view_attachments_threshold' );
	$t_download_threshold = config_get_global( 'download_attachments_threshold' );
	$t_delete_threshold = config_get_global( 'delete_attachments_threshold' );

	if( $t_view_threshold > $t_download_threshold ) {
		$t_pass = false;
	} else {
		if( $t_download_threshold > $t_delete_threshold ) {
			$t_pass = false;
		}
	}

	print_test_row( 'Bug attachments download thresholds (view_attachments_threshold, ' .
		'download_attachments_threshold, delete_attachments_threshold)', $t_pass );

	return $t_pass;
}

function test_bug_attachments_allow_flags() {
	$t_pass = true;

	$t_own_view = config_get_global( 'allow_view_own_attachments' );
	$t_own_download = config_get_global( 'allow_download_own_attachments' );
	$t_own_delete = config_get_global( 'allow_delete_own_attachments' );

	if(( $t_own_delete == ON ) && ( $t_own_download == FALSE ) ) {
		$t_pass = false;
	} else {
		if(( $t_own_download == ON ) && ( $t_own_view == OFF ) ) {
			$t_pass = false;
		}
	}

	print_test_row( 'Bug attachments allow own flags (allow_view_own_attachments, ' .
		'allow_download_own_attachments, allow_delete_own_attachments)', $t_pass );

	return $t_pass;
}

function check_zend_optimiser_version() {
	$t_pass = true;

	ob_start();
	phpinfo(INFO_GENERAL);
	$t_output = ob_get_contents();
	ob_end_clean();

	$t_output = str_replace(array("&gt;", "&lt;", "&quot;", "&amp;", "&#039;", "&nbsp;"), array(">", "<", "\"", "&", "'", " "), $t_output);

	define ( 'ZEND_OPTIMIZER_VERSION', '3.3');
	define ( 'ZEND_OPTIMIZER_SUBVERSION', 3);

	if (strstr($t_output, "Zend Optimizer")) {
		$t_version = split("Zend Optimizer",$t_output);
		$t_version = split(",",$t_version[1]);
		$t_version = trim($t_version[0]);

		if (!strstr($t_version,"v")) {
			$t_info = 'Zend Optimizer Detected - Unknown Version.';
			$t_pass = false;
  		} else {
			$t_version = str_replace("v","",$t_version);
			$t_version = explode(".",$t_version);
			$t_subVersion = $t_version[2];
			$t_dummy = array_pop($t_version);
			$t_version = implode(".",$t_version);

			if (!($t_version > ZEND_OPTIMIZER_VERSION) || ($t_version==ZEND_OPTIMIZER_VERSION && $t_subVersion>=ZEND_OPTIMIZER_SUBVERSION)) {
				$t_pass = false;
				$t_info = 'Fail - Installed Version: ' . $t_version . '.' . $t_subVersion . '.';
			}
		}
	} else {
		$t_info = 'Zend Optimiser not detected';
	}

	if (strstr($t_output, 'has been disabled')) {
		$t_info = 'Unable to determine Zend Optimizer version - phpinfo() is disabled.';
		$t_pass = false;
	}

	if( $t_pass == false ) {
		$t_info .= ' Zend Optimizer should be version be ' . ZEND_OPTIMIZER_VERSION . '.' . ZEND_OPTIMIZER_SUBVERSION  . ' or greater! Some old versions cause the view issues page not to display completely. The latest version of Zend Optimizer can be found at www.zend.com';
	}

	print_test_row( 'Checking Zend Optimiser version (if installed)...', $t_pass, $t_info );

	return $t_pass;
}

function test_database_utf8() {
	if ( !db_is_mysql() ) {
		return;
	}

	// table collation/character set check
	$result = db_query_bound( 'SHOW TABLE STATUS' );
	while( $row = db_fetch_array( $result ) ) {
		print_test_row( 'Checking Table Collation is utf8 for ' . $row['Name'] . '....', substr( $row['Collation'], 0, 5 ) === 'utf8_', $row['Collation'] );
	}

	foreach( db_get_table_list() as $t_table ) {
		if( db_table_exists( $t_table ) ) {
			$result = db_query_bound( 'SHOW FULL FIELDS FROM ' . $t_table );
			while( $row = db_fetch_array( $result ) ) {
				if ( $row['Collation'] === null ) {
					continue;
				}
				print_test_row( 'Checking Non-null Column Collation in ' . $t_table . ' is utf8 for ' . $row['Field'] . '....', substr( $row['Collation'], 0, 5 ) === 'utf8_', $row['Collation'] . ' ( ' . $row['Type'] . ')' );
			}
		}
	}
}

	$version = phpversion();

	require_once( $t_core_path . 'obsolete.php' );

	html_page_top( 'MantisBT Administration - Check Installation' );

?>
<table class="width75" align="center" cellspacing="1">
<tr>
<td class="form-title" width="30%" colspan="2"><?php echo 'Checking your installation' ?></td>
</tr>




<!-- Test PHP Version -->
<tr>
	<td bgcolor="#ffffff">
		MantisBT requires at least <b>PHP <?php echo PHP_MIN_VERSION?></b>. You are running <b>PHP <?php echo $version?>
	</td>
	<?php
		$result = version_compare( phpversion(), PHP_MIN_VERSION, '>=' );
if( false == $result ) {
	print_test_result( BAD );
}
else {
	print_test_result( GOOD );
}
?>
</tr>

<!-- Test DATABASE part 1 -->
<tr>
	<td bgcolor="#ffffff">
		Opening connection to database [<?php echo config_get_global( 'database_name' )?>] on host [<?php echo config_get_global( 'hostname' )?>] with username [<?php echo config_get_global( 'db_username' )?>]
	</td>
	<?php
		$result = @db_connect( config_get_global( 'dsn', false ), config_get_global( 'hostname' ), config_get_global( 'db_username' ), config_get_global( 'db_password' ), config_get_global( 'database_name' ) );
if( false == $result ) {
	print_test_result( BAD );
}
else {
	print_test_result( GOOD );
}
?>
</tr>

<!-- Test DATABASE part 2 -->
<?php if( db_is_connected() ) {
	$t_serverinfo = $g_db->ServerInfo()?>
<tr>
	<td bgcolor="#ffffff">
		Database Type (adodb)
	</td>
	<td bgcolor="#ffffff">
			<?php echo $g_db->databaseType?>
	</td>
</tr><tr>
	<td bgcolor="#ffffff">
			Database Provider (adodb)
	</td>
	<td bgcolor="#ffffff">
				<?php echo $g_db->dataProvider?>
	</td>
</tr><tr>
	<td bgcolor="#ffffff">
		Database Server Description (adodb)
	</td>
	<td bgcolor="#ffffff">
			<?php echo $t_serverinfo['description']?>
	</td>
</tr><tr>
	<td bgcolor="#ffffff">
		Database Server Description (version)
	</td>
	<td bgcolor="#ffffff">
			<?php echo $t_serverinfo['version']?>
	</td>
</tr>
<?php
}?>

<!-- Absolute path check -->
<tr>
	<td bgcolor="#ffffff">
		Checking to see if your absolute_path config option has a trailing slash: "<?php echo config_get_global( 'absolute_path' )?>"
	</td>
	<?php
		$t_absolute_path = config_get_global( 'absolute_path' );

if(( "\\" == substr( $t_absolute_path, -1, 1 ) ) || ( "/" == substr( $t_absolute_path, -1, 1 ) ) ) {
	print_test_result( GOOD );
}
else {
	print_test_result( BAD );
}
?>
</tr>

<?php
# Windows-only checks
if( substr( php_uname(), 0, 7 ) == 'Windows' ) {
	print_test_row( 'validate_email (if ON) requires php 5.3 on windows...',
		OFF == config_get_global( 'validate_email' ) || ON == config_get_global( 'validate_email' ) && version_compare( phpversion(), '5.3.0', '>=' ) );
	print_test_row( 'check_mx_record (if ON) requires php 5.3 on windows...',
		OFF == config_get_global( 'check_mx_record' ) || ON == config_get_global( 'check_mx_record' ) && version_compare( phpversion(), '5.3.0', '>=' ) );
}

$t_vars = array(
	'magic_quotes_gpc',
	'gpc_order',
	'variables_order',
	'include_path',
	'short_open_tag',
	'mssql.textsize',
	'mssql.textlimit',
);

while( list( $t_foo, $t_var ) = each( $t_vars ) ) {
	?>
<tr>
	<td bgcolor="#ffffff">
		<?php echo $t_var?>
	</td>
	<td bgcolor="#ffffff">
		<?php echo ini_get( $t_var )?>
	</td>
</tr>
<?php
}

test_bug_download_threshold();
test_bug_attachments_allow_flags();

print_test_row( 'check mail configuration: send_reset_password = ON requires allow_blank_email = OFF',
	( ( OFF == config_get_global( 'send_reset_password' ) ) || ( OFF == config_get_global( 'allow_blank_email' ) ) ) );
print_test_row( 'check mail configuration: send_reset_password = ON requires enable_email_notification = ON',
	( OFF == config_get_global( 'send_reset_password' ) ) || ( ON == config_get_global( 'enable_email_notification' ) ) );
print_test_row( 'check mail configuration: allow_signup = ON requires enable_email_notification = ON',
	( OFF == config_get_global( 'allow_signup' ) ) || ( ON == config_get_global( 'enable_email_notification' ) ) );
print_test_row( 'check mail configuration: allow_signup = ON requires send_reset_password = ON',
	( OFF == config_get_global( 'allow_signup' ) ) || ( ON == config_get_global( 'send_reset_password' ) ) );
print_test_row( 'check language configuration: fallback_language is not \'auto\'',
	'auto' <> config_get_global( 'fallback_language' ) );
print_test_row( 'check configuration: allow_anonymous_login = ON requires anonymous_account to be set',
	( OFF == config_get_global( 'allow_anonymous_login' ) ) || ( strlen( config_get_global( 'anonymous_account') ) > 0 ) );

$t_anon_user = false;

print_test_row( 'check configuration: anonymous_account is a valid username if set',
	( (strlen( config_get_global( 'anonymous_account') ) > 0 ) ? ( ($t_anon_user = user_get_id_by_name( config_get_global( 'anonymous_account') ) ) !== false ) : TRUE ) );
print_test_row( 'check configuration: anonymous_account should not be an administrator',
	( $t_anon_user ? ( !access_compare_level( user_get_field( $t_anon_user, 'access_level' ), ADMINISTRATOR) ) : TRUE ) );
print_test_row( '$g_bug_link_tag is not empty ("' . config_get_global( 'bug_link_tag' ) . '")',
	'' <> config_get_global( 'bug_link_tag' ) );
print_test_row( '$g_bugnote_link_tag is not empty ("' . config_get_global( 'bugnote_link_tag' ) . '")',
	'' <> config_get_global( 'bugnote_link_tag' ) );
print_test_row( 'filters: dhtml_filters = ON requires use_javascript = ON',
	( OFF == config_get_global( 'dhtml_filters' ) ) || ( ON == config_get_global( 'use_javascript' ) ) );
print_test_row( 'Phpmailer sendmail configuration requires escapeshellcmd. Please use a different phpmailer method if this is blocked.',
	( PHPMAILER_METHOD_SENDMAIL != config_get( 'phpMailer_method' ) || ( PHPMAILER_METHOD_SENDMAIL == config_get( 'phpMailer_method' ) ) && function_exists( 'escapeshellcmd' ) ) );
print_test_row( 'Phpmailer sendmail configuration requires escapeshellarg. Please use a different phpmailer method if this is blocked.',
	( PHPMAILER_METHOD_SENDMAIL != config_get( 'phpMailer_method' ) || ( PHPMAILER_METHOD_SENDMAIL == config_get( 'phpMailer_method' ) ) && function_exists( 'escapeshellarg' ) ) );

check_zend_optimiser_version();

if( ON == config_get_global( 'use_jpgraph' ) ) {
	$t_jpgraph_path = config_get_global( 'jpgraph_path' );

	if( !file_exists( $t_jpgraph_path ) ) {
		$t_jpgraph_path = '..' . DIRECTORY_SEPARATOR . $t_jpgraph_path;
	}

	if( !file_exists( $t_jpgraph_path . 'jpgraph.php') ) {
		print_test_row( 'checking we can find jpgraph class files...', false );
	} else {
		require_once( $t_jpgraph_path . 'jpgraph.php' );

		print_test_row( 'Checking Jpgraph version (if installed)...', version_compare(JPG_VERSION, '2.3.0') ? true : false, JPG_VERSION );
	}

	print_test_row( 'check configuration: jpgraph (if used) requires php bundled gd for antialiasing support',
		( config_get_global( 'jpgraph_antialias' ) == OFF || function_exists('imageantialias') ) );

}

print_test_row( 'Checking if ctype is enabled in php (required for rss feeds)....', extension_loaded('ctype') );

print_test_row( 'Checking for mysql is at least version 4.1...', !(db_is_mysql() && version_compare( $t_serverinfo['version'], '4.1.0', '<' ) ) );
print_test_row( 'Checking for broken mysql version ( bug 10250)...', !(db_is_mysql() && $t_serverinfo['version'] == '4.1.21') );

test_database_utf8();


?>
</table>

<!-- register_globals check -->
<?php
	if( ini_get_bool( 'register_globals' ) ) {?>
		<br />

		<table width="100%" bgcolor="#222222" border="0" cellpadding="20" cellspacing="1">
		<tr>
			<td bgcolor="#ffcc22">
				<span class="title">WARNING - register_globals - WARNING</span><br /><br />

				You have register_globals enabled in PHP, which is considered a security risk.  Since version 0.18, MantisBT has no longer relied on register_globals being enabled.  PHP versions later that 4.2.0 have this option disabled by default.  For more information on the security issues associated with enabling register_globals, see <a href="http://www.php.net/manual/en/security.globals.php">this page</a>.

				If you have no other PHP applications that rely on register_globals, you should add the line <pre>register_globals = Off</pre> to your php.ini file;  if you do have other applications that require register_globals, you could consider disabling it for your MantisBT installation by adding the line <pre>php_value register_globals off</pre> to a <tt>.htaccess</tt> file or a <tt>&lt;Directory&gt;</tt> or <tt>&lt;Location&gt;</tt> block in your apache configuration file.  See the apache documentation if you require more information.
			</td>
		</tr>
		</table>

		<br /><?php
}
?>

<!-- login_method check -->
<?php
	if( CRYPT_FULL_SALT == config_get_global( 'login_method' ) ) {?>
		<br />

		<table width="100%" bgcolor="#222222" border="0" cellpadding="20" cellspacing="1">
		<tr>
			<td bgcolor="#ff0088">
				<span class="title">WARNING - login_method - WARNING</span><br /><br />

				You are using CRYPT_FULL_SALT as your login method. This login method is deprecated and you should change the login method to either CRYPT (which is compatible) or MD5 (which is more secure). CRYPT_FULL_SALT will be removed in the next major release.

				You can simply change the login_method in your configuration file. You don't need to do anything else, even if you migrate to MD5 (which produces incompatible hashes). This is because MantisBT will automatically convert the passwords as users log in.
			</td>
		</tr>
		</table>

		<br /><?php
	} else if( MD5 != config_get_global( 'login_method' ) ) {?>
		<br />

		<table width="100%" bgcolor="#222222" border="0" cellpadding="20" cellspacing="1">
		<tr>
			<td bgcolor="#ffcc22">
				<span class="title">NOTICE - login_method - NOTICE</span><br /><br />

				You are not using MD5 as your login_method. The other login methods are mostly provided for backwards compatibility, but we recommend migrating to the more secure MD5.

				You can simply change the login_method in your configuration file to MD5. MantisBT will automatically convert the passwords as users log in.
			</td>
		</tr>
		</table>

		<br /><?php
	}
?>
<br />

<!-- Uploads -->
<table width="100%" bgcolor="#222222" border="0" cellpadding="20" cellspacing="1">
<tr>
	<td bgcolor="#f4f4f4">
		<span class="title">File Uploads</span><br />
		<?php
			if( ini_get_bool( 'file_uploads' ) && config_get_global( 'allow_file_upload' ) ) {
	?>
				<p>File uploads are ENABLED.</p>
				<p>File uploads will be stored <?php
				switch( config_get_global( 'file_upload_method' ) ) {
					case DATABASE:
						echo 'in the DATABASE.';
						break;
					case DISK:
						echo 'on DISK in the directory specified by the project.';
						break;
					case FTP:
						echo 'on an FTP server (' . config_get_global( 'file_upload_ftp_server' ) . '), and cached locally.';
						break;
					default:
						echo 'in an illegal place.';
				}?>	</p>

				<p>The following size settings are in effect.  Maximum upload size will be whichever of these is SMALLEST. </p>
				<p>PHP variable 'upload_max_filesize': <?php echo ini_get_number( 'upload_max_filesize' )?> bytes<br />
				PHP variable 'post_max_size': <?php echo ini_get_number( 'post_max_size' )?> bytes<br />
				MantisBT variable 'max_file_size': <?php echo config_get_global( 'max_file_size' )?> bytes</p>

		<?php
				if( DATABASE == config_get_global( 'file_upload_method' ) ) {
					echo '<p>There may also be settings in your web server and database that prevent you from  uploading files or limit the maximum file size.  See the documentation for those packages if you need more information. ';
					if( 500 < min( ini_get_number( 'upload_max_filesize' ), ini_get_number( 'post_max_size' ), config_get_global( 'max_file_size' ) ) ) {
						echo '<span class="error">Your current settings will most likely need adjustments to the PHP max_execution_time or memory_limit settings, the MySQL max_allowed_packet setting, or equivalent.</span>';
					}
				} else {
					echo '<p>There may also be settings in your web server that prevent you from  uploading files or limit the maximum file size.  See the documentation for those packages if you need more information.';
				}
				echo '</p>';
			} else {
	?>
				<p>File uploads are DISABLED.  To enable them, make sure <tt>$g_file_uploads = on</tt> is in your php.ini file and <tt>allow_file_upload = ON</tt> is in your MantisBT config file.</p>
		<?php
			}
?>
	</td>
</tr>
</table>
<br />
</body>
</html>
