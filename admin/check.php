<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: check.php,v 1.23 2005-07-17 10:06:00 prichards Exp $
	# --------------------------------------------------------
?>
<?php
	error_reporting( E_ALL );

	$g_skip_open_db = true;  # don't open the database in database_api.php
	require_once ( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core.php' );
	require_once ( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core/email_api.php' );

	$t_core_path = config_get_global( 'core_path' );

	$f_mail_test	= gpc_get_bool( 'mail_test' );
	$f_password		= gpc_get_string( 'password', null );

	define( 'BAD', 0 );
	define( 'GOOD', 1 );

	function print_test_result( $p_result ) {
		if ( BAD == $p_result ) {
			echo '<td bgcolor="#ff0088">BAD</td>';
		}

		if ( GOOD == $p_result ) {
			echo '<td bgcolor="#00ff88">GOOD</td>';
		}
	}

	function print_yes_no( $p_result ) {
		if ( ( 0 === $p_result ) || ( "no" === strtolower( $p_result ) ) ) {
			echo 'No';
		}

		if ( ( 1 === $p_result ) || ( "yes" === strtolower( $p_result ) ) ) {
			echo 'Yes';
		}
	}

	function print_test_row( $p_description, $p_pass )
	{
		echo '<tr>';
		echo '<td bgcolor="#ffffff">';
		echo $p_description;
		echo '</td>';

		if ( $p_pass ) {
			print_test_result( GOOD );
		} else {
			print_test_result( BAD );
		}

		echo '</tr>';
	}

	function test_bug_download_threshold()
	{
		$t_pass = true;

		$t_view_threshold = config_get_global( 'view_attachments_threshold' );
		$t_download_threshold = config_get_global( 'download_attachments_threshold' );
		$t_delete_threshold = config_get_global( 'delete_attachments_threshold' );

		if ( $t_view_threshold > $t_download_threshold ) {
			$t_pass = false;
		} else {
			if ( $t_download_threshold > $t_delete_threshold ) {
				$t_pass = false;
			}
		}

		print_test_row( 'Bug attachments download thresholds (view_attachments_threshold, ' .
				'download_attachments_threshold, delete_attachments_threshold)', $t_pass );

		return $t_pass;
	}

	function test_bug_attachments_allow_flags()
	{
		$t_pass = true;

		$t_own_view = config_get_global( 'allow_view_own_attachments' );
		$t_own_download = config_get_global( 'allow_download_own_attachments' );
		$t_own_delete = config_get_global( 'allow_delete_own_attachments' );

		if ( ( $t_own_delete == ON ) && ( $t_own_download == FALSE ) ) {
			$t_pass = false;
		} else {
			if ( ( $t_own_download == ON ) && ( $t_own_view == OFF ) ) {
				$t_pass = false;
			}
		}

		print_test_row( 'Bug attachments allow own flags (allow_view_own_attachments, ' .
				'allow_download_own_attachments, allow_delete_own_attachments)', $t_pass );

		return $t_pass;
	}

	$version = phpversion();
?>
<html>
<head>
<title> Mantis Administration - Check Installation </title>
<link rel="stylesheet" type="text/css" href="admin.css" />
</head>
<body>
<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
	<tr class="top-bar">
		<td class="links">
			[ <a href="index.php">Back to Administration</a> ]
		</td>
		<td class="title">
			Check Installation
		</td>
	</tr>
</table>
<br /><br />

<?php
	require_once( $g_core_path . 'obsolete.php' );
?>

<!-- Version Check -->
<table width="100%" bgcolor="#0000aa" border="0" cellpadding="20" cellspacing="1">
<tr>
	<td bgcolor="#f0f0ff">
		<span class="title">Version</span>
		<p>Mantis requires at least <b>PHP <?php echo PHP_MIN_VERSION ?></b>.  If you are not running this version or above, you or your administrator will need to upgrade your build of PHP.</p>
		<p>You are running <b>PHP <?php echo $version ?></b></p>
	</td>
</tr>
</table>

<br />

<table width="100%" bgcolor="#222222" border="0" cellpadding="10" cellspacing="1">
<tr>
	<td bgcolor="#e8e8e8" colspan="2">
		<span class="title">Checking your installation</span>
	</td>
</tr>

<!-- Test DATABASE part 1 -->
<tr>
	<td bgcolor="#ffffff">
		Opening connection to database [<?php echo config_get_global( 'database_name' ) ?>] on host [<?php echo config_get_global( 'hostname' ) ?>] with username [<?php echo config_get_global( 'db_username' ) ?>]
	</td>
	<?php
		$result = @db_connect( config_get_global( 'dsn', false ), config_get_global( 'hostname' ), config_get_global( 'db_username' ), config_get_global( 'db_password' ), config_get_global( 'database_name' ) );
		if ( false == $result ) {
			print_test_result( BAD );
		} else {
			print_test_result( GOOD );
		}
	?>
</tr>

<!-- Absolute path check -->
<tr>
	<td bgcolor="#ffffff">
		Checking to see if your absolute_path config option has a trailing slash: "<?php echo config_get_global( 'absolute_path' ) ?>"
	</td>
	<?php
		$t_absolute_path = config_get_global( 'absolute_path' );

		if ( ( "\\" == substr( $t_absolute_path, -1, 1 ) ) ||
			 ( "/"  == substr( $t_absolute_path, -1, 1 ) ) ) {
			print_test_result( GOOD );
		} else {
			print_test_result( BAD );
		}
	?>
</tr>


<?php
# Windows-only checks
if ( substr( php_uname(), 0, 7 ) == 'Windows' ) {
?>
<!-- Email Validation -->
<tr>
	<td bgcolor="#ffffff">
		validate_email = OFF?
	</td>
	<?php
		if ( ON != config_get_global( 'validate_email' ) ) {
			print_test_result( GOOD );
		} else {
			print_test_result( BAD );
		}
	?>
</tr>

<!-- MX Record Checking -->
<tr>
	<td bgcolor="#ffffff">
		check_mx_record = OFF?
	</td>
	<?php
		if ( ON != config_get_global( 'check_mx_record' ) ) {
			print_test_result( GOOD );
		} else {
			print_test_result( BAD );
		}
	?>
</tr>
<?php } # windows-only check ?>



<!-- PHP Setup check -->
<?php
	$t_vars = array( 'magic_quotes_gpc',
					'gpc_order',
					'variables_order',
					'include_path',
					'short_open_tag');

	while ( list( $t_foo, $t_var ) = each( $t_vars ) ) {
?>
<tr>
	<td bgcolor="#ffffff">
		<?php echo $t_var ?>
	</td>
	<td bgcolor="#ffffff">
		<?php echo ini_get( $t_var ) ?>
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

		print_test_row( '$g_bug_link_tag is not empty ("' . config_get_global( 'bug_link_tag' ) . '")',
				'' <> config_get_global( 'bug_link_tag' ) );
		print_test_row( '$g_bugnote_link_tag is not empty ("' . config_get_global( 'bugnote_link_tag' ) . '")',
				'' <> config_get_global( 'bugnote_link_tag' ) );

		print_test_row( 'filters: dhtml_filters = ON requires use_javascript = ON',
				( OFF == config_get_global( 'dhtml_filters' ) ) || ( ON == config_get_global( 'use_javascript' ) ) );
?>
</table>

<!-- register_globals check -->
<?php
	if ( ini_get_bool( 'register_globals' ) ) { ?>
		<br />

		<table width="100%" bgcolor="#222222" border="0" cellpadding="20" cellspacing="1">
		<tr>
			<td bgcolor="#ffcc22">
				<span class="title">WARNING - register_globals - WARNING</span><br /><br />

				You have register_globals enabled in PHP, which is considered a security risk.  Since version 0.18, Mantis has no longer relied on register_globals being enabled.  PHP versions later that 4.2.0 have this option disabled by default.  For more information on the security issues associated with enabling register_globals, see <a href="http://www.php.net/manual/en/security.globals.php">this page</a>.

				If you have no other PHP applications that rely on register_globals, you should add the line <pre>register_globals = Off</pre> to your php.ini file;  if you do have other applications that require register_globals, you could consider disabling it for your Mantis installation by adding the line <pre>php_value register_globals off</pre> to a <tt>.htaccess</tt> file or a <tt>&lt;Directory&gt;</tt> or <tt>&lt;Location&gt;</tt> block in your apache configuration file.  See the apache documentation if you require more information.
			</td>
		</tr>
		</table>

		<br /><?php
	}
?>

<!-- login_method check -->
<?php
	if ( CRYPT_FULL_SALT == config_get_global( 'login_method' ) ) { ?>
		<br />

		<table width="100%" bgcolor="#222222" border="0" cellpadding="20" cellspacing="1">
		<tr>
			<td bgcolor="#ff0088">
				<span class="title">WARNING - login_method - WARNING</span><br /><br />

				You are using CRYPT_FULL_SALT as your login method. This login method is deprecated and you should change the login method to either CRYPT (which is compatible) or MD5 (which is more secure). CRYPT_FULL_SALT will be removed in the next major release.

				You can simply change the login_method in your configuration file. You don't need to do anything else, even if you migrate to MD5 (which produces incompatible hashes). This is because Mantis will automatically convert the passwords as users log in.
			</td>
		</tr>
		</table>

		<br /><?php
	} else if ( MD5 != config_get_global( 'login_method' ) ) { ?>
		<br />

		<table width="100%" bgcolor="#222222" border="0" cellpadding="20" cellspacing="1">
		<tr>
			<td bgcolor="#ffcc22">
				<span class="title">NOTICE - login_method - NOTICE</span><br /><br />

				You are not using MD5 as your login_method. The other login methods are mostly provided for backwards compatibility, but we recommend migrating to the more secure MD5.

				You can simply change the login_method in your configuration file to MD5. Mantis will automatically convert the passwords as users log in.
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
			if ( ini_get_bool( 'file_uploads' ) && config_get_global( 'allow_file_upload' ) ) {
		?>
				<p>File uploads are ENABLED.</p>
				<p>File uploads will be stored <?php
								switch ( config_get_global( 'file_upload_method' ) ) {
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
								} ?>	</p>

				<p>The following size settings are in effect.  Maximum upload size will be whichever of these is SMALLEST. </p>
				<p>PHP variable 'upload_max_filesize': <?php echo ini_get_number( 'upload_max_filesize' ) ?> bytes<br />
				PHP variable 'post_max_size': <?php echo ini_get_number( 'post_max_size' ) ?> bytes<br />
				Mantis variable 'max_file_size': <?php echo config_get_global( 'max_file_size' ) ?> bytes</p>

		<?php
				if ( DATABASE == config_get_global( 'file_upload_method' ) ) {
					echo '<p>There may also be settings in your web server and database that prevent you from  uploading files or limit the maximum file size.  See the documentation for those packages if you need more information. ';
					if ( 500 < min( ini_get_number( 'upload_max_filesize' ), ini_get_number( 'post_max_size' ), config_get_global( 'max_file_size' ) ) ) {
						echo '<span class="error">Your current settings will most likely need adjustments to the PHP max_execution_time or memory_limit settings, the MySQL max_allowed_packet setting, or equivalent.</span>';
					}
				} else {
					echo '<p>There may also be settings in your web server that prevent you from  uploading files or limit the maximum file size.  See the documentation for those packages if you need more information.';
				}
				echo '</p>';
			} else {
		?>
				<p>File uploads are DISABLED.  To enable them, make sure <tt>$g_file_uploads = on</tt> is in your php.ini file and <tt>allow_file_upload = ON</tt> is in your mantis config file.</p>
		<?php
			}
		?>
	</td>
</tr>
</table>

<br />


<!-- Email testing -->
<a name="email" id="email" />
<table width="100%" bgcolor="#222222" border="0" cellpadding="20" cellspacing="1">
<tr>
	<td bgcolor="#f4f4f4">
		<span class="title">Testing Email</span>
		<p>You can test the mail() function with this form.  Just check the recipient and click submit.  If the page takes a very long time to reappear or results in an error then you will need to investigate your php/mail server settings.  Note that errors can also appear in the server error log.  More help can be found at the <a href="http://www.php.net/manual/en/ref.mail.php">PHP website</a>.</p>
		<?php
		if ( $f_mail_test ) {
			echo '<b><font color="#ff0000">Testing Mail</font></b> - ';
 # @@@ thraxisp - workaround to ensure a language is set without authenticating
 #  will disappear when this is properly localized
			lang_push( 'english' );
			$result = email_send( config_get_global( 'administrator_email' ), 'Testing PHP mail() function',	'Your PHP mail settings appear to be correctly set.');

			if ( !$result ) {
				echo ' PROBLEMS SENDING MAIL TO: ' . config_get_global( 'administrator_email' ) . '. Please check your php/mail server settings.<br />';
			} else {
				echo ' mail() send successful.<br />';
			}
		}
		?>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>#email">
		Email Address: <?php echo config_get_global( 'administrator_email' ); ?><br />
		<input type="submit" value="Send Mail" name="mail_test" />
		</form>
	</td>
</tr>
</table>

<br />


<!-- CRYPT CHECKS -->
<a name="crypt" id="crypt" />
<table width="100%" bgcolor="#aa0000" border="0" cellpadding="20" cellspacing="1">
<tr>
	<td bgcolor="#fff0f0">
		<span class="title">Which types of Crypt() does your installation support:</span>
		<p>
		Standard DES:
		<?php print_yes_no( CRYPT_STD_DES  ) ?>
		<br />
		Extended DES:
		<?php print_yes_no( CRYPT_EXT_DES ) ?>
		<br />
		MD5:
		<?php print_yes_no( CRYPT_MD5 ) ?>
		<br />
		Blowfish:
		<?php print_yes_no( CRYPT_BLOWFISH ) ?>
		</p>
	</td>
</tr>
</table>

</body>
</html>
