<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: check.php,v 1.6 2004-03-05 01:26:17 jlatour Exp $
	# --------------------------------------------------------
?>
<?php
	error_reporting( E_ALL );

	$g_skip_open_db = true;  # don't open the database in database_api.php
	@require_once( '../core.php' );

	$t_core_path = config_get( 'core_path' );

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
		<p>Mantis requires at least <b>PHP <?php echo PHP_MIN_VERSION ?></b>.  If you are not running this version you or your administrator will need to upgrade your build of PHP.  We recommend 4.0.6 or 4.1.2 at the moment.</p>
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
		Opening connection to database on host [<?php echo config_get( 'hostname' ) . ':' . config_get( 'port' ) ?>] with username [<?php echo config_get( 'db_username' ) ?>]
	</td>
	<?php
		$result = @db_connect( config_get( 'hostname' ), config_get( 'db_username' ), config_get( 'db_password' ), config_get( 'port' ) );
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
		Checking to see if your absolute_path config option has a trailing slash: "<?php echo config_get( 'absolute_path' ) ?>"
	</td>
	<?php
		$t_absolute_path = config_get( 'absolute_path' );

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
		if ( ON != config_get( 'validate_email' ) ) {
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
		if ( ON != config_get( 'check_mx_record' ) ) {
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

				You have register_globals enabled in PHP, which is considered a security risk.  Since version 0.18, Mantis has no longer relied on register_globals being enabled.  PHP versions later that 4.2.0 have this option disabled by default.  For more information on the security issues associated with enabling register_globals, see <a href="http://www.php.net/manual/en/security.registerglobals.php">this page</a>.

				If you have no other PHP applications that rely on register_globals, you should add the line <pre>register_globals = Off</pre> to your php.ini file;  if you do have other applications that require register_globals, you could consider disabling it for your Mantis installation by adding the line <pre>php_value register_globals off</pre> to a <tt>.htaccess</tt> file or a <tt>&lt;Directory&gt;</tt> or <tt>&lt;Location&gt;</tt> block in your apache configuration file.  See the apache documentation if you require more information.
			</td>
		</tr>
		</table>

		<br /><?php
	}
?>

<!-- login_method check -->
<?php
	if ( CRYPT_FULL_SALT == config_get( 'login_method' ) ) { ?>
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
	} else if ( MD5 != config_get( 'login_method' ) ) { ?>
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
			if ( ini_get_bool( 'file_uploads' ) && config_get( 'allow_file_upload' ) ) {
		?>
				<p>File uploads are ENABLED</p>

				<p>The following size settings are in effect.  Maximum upload size will be whichever of these is SMALLEST. </p>
				<p>PHP variable 'upload_max_filesize': <?php echo ini_get( 'upload_max_filesize' ) ?><br />
				PHP variable 'post_max_size': <?php echo ini_get( 'post_max_size' ) ?><br />
				Mantis variable 'max_file_size': <?php echo config_get( 'max_file_size' ) ?> bytes</p>

				<p>There may also be settings in Apache (or MySQL if using the SQL upload method) that prevent you from  uploading files or limit the maximum file size.  See the documentation for those packages if you need more information.</p>
		<?php
			} else {
		?>
				<p>File uploads are DISABLED.  To enable them, make sure <tt>file_uploads = on</tt> is in your php.ini file and <tt>allow_file_upload = ON</tt> is in your mantis config file.</p>
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
			echo '<b><font color="#ff0000">Mail sent</font></b> - ';
			
			$result = mail( config_get( 'administrator_email' ), 'Testing PHP mail() function', 'Your PHP mail settings appear to be correctly set.', 'From: ' . config_get( 'administrator_email' ) . "\n" );

			if ( !$result ) {
				echo ' PROBLEMS SENDING MAIL TO: ' . config_get( 'administrator_email' ) . '. Please check your php/mail server settings.<br />';
			} else {
				echo ' mail() send successful.<br />';
			}
		}
		?>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>#email">
		Email Address: <?php echo config_get( 'administrator_email' ); ?><br />
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