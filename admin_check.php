<?php
	error_reporting( E_ALL );
	# mail test
	if ( isset( $f_mail_test ) ) {
		$result = mail( $f_to_email, "Testing PHP mail() function", $f_message, "From: $f_to_email\n" );
	}

	define( "CRYPT2", 10 );
	define( "CRYPT3", 20 );

	function get_password( $p_test_password, $p_type ) {
		switch( $p_type ) {
		case CRYPT:	$salt = substr( $p_test_password, 0, 2 );
					return crypt( $p_test_password, $salt );
		case CRYPT2:$salt = $p_test_password;
					return crypt( $p_test_password, $salt );
		case CRYPT3:return crypt( $p_test_password);
		case PLAIN:	return $p_test_password;
		case MD5:	return md5( $p_test_password );
		#case LDAP:	if ( ldap_uid_pass( $f_username, $p_test_password ) ) {
		}
	}

	define( "BAD", 0 );
	define( "GOOD", 1 );

	function print_test_result( $p_result ) {
		if ( BAD == $p_result ) {
			PRINT "<td bgcolor=#ff0088>BAD</td>";
		}

		if ( GOOD == $p_result ) {
			PRINT "<td bgcolor=#00ff88>GOOD</td>";
		}
	}

	function print_yes_no( $p_result ) {
		if (( 0 === $p_result ) || ( "no" === strtolower( $p_result ) )) {
			PRINT "<font bgcolor=#ff0088>No</font>";
		}

		if (( 1 === $p_result ) || ( "yes" === strtolower( $p_result ) )) {
			PRINT "<font bgcolor=#00ff88>Yes</font>";
		}
	}

	$version = phpversion();
?>
<html>
<head>
<title>Administrator checks</title>
<style type="text/css">
body { background-color: #ffffff; font-family:Verdana, Arial; font-size: 10pt }
td { font-family:Verdana, Arial; font-size: 10pt }
p { font-family:Verdana, Arial; font-size: 10pt }
address { font-family:Verdana, Arial; font-size: 8pt }
span.required { font-family:Verdana, Arial; font-size: 10pt; color: #aa0000 }
span.title    { font-family:Verdana, Arial; font-size: 12pt; color: #000000; font-weight: bold; }
</style>
</head>
<body>
<?php include( "core_API.php" ) ?>

<h2>Admin Check</h2>

<?php # ---- Version Check ---- ?>
<table width="100%" bgcolor="#0000aa" border="0" cellpadding="20" cellspacing="1">
<tr>
	<td bgcolor="#f0f0ff">
		<span class="title">Version</span>
		<p>
		Mantis requires at least <b>PHP 4.0.3</b>.  If you are not running this version you or you radministrator will need to upgrade your build of PHP.  I recommend 4.0.6 or 4.1.2 at the moment.
		<p />
		You are running <b>PHP <?php echo $version ?></b>
	</td>
</tr>
</table>

<p>

<table width="100%" bgcolor="#222222" border="0" cellpadding="10" cellspacing="1">
<!-- Test DATABASE part 1 -->
<tr>
	<td bgcolor="#e8e8e8" colspan="2">
		<span class="title">Checking your installation</span>
	</td>
</tr>
<tr>
	<td bgcolor="#ffffff">
		Opening connection to database on host [<?php echo $g_hostname ?>] with username [<?php echo $g_db_username ?>]
	</td>
	<?php
		$result = mysql_connect( $g_hostname, $g_db_username, $g_db_password );
		if ( false == $result ) {
			print_test_result( BAD );
		} else {
			print_test_result( GOOD );
		}
	?>
</tr>
<!-- Test DATABASE part 2 -->
<tr>
	<td bgcolor="#ffffff">
		Selecting database [<?php echo $g_database_name ?>]
	</td>
	<?php
		$result = mysql_select_db( $g_database_name );
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
		Checking to see if your $g_absolute_path variable has a trailing /
	</td>
	<?php
		if (( "\\" == $g_absolute_path[strlen($g_absolute_path)-1] ) ||
			( "/"  == $g_absolute_path[strlen($g_absolute_path)-1] )) {
			print_test_result( GOOD );
		} else {
			print_test_result( BAD );
		}
	?>
</tr>
<!-- PHP Setup check -->
<tr>
	<td bgcolor="#ffffff">
		register_globals should be set to On or 1
	</td>
	<?php
		$test_val = ini_get( "register_globals" );
		if ( ( 1 == $test_val ) || ( "On" == $test_val ) ) {
			print_test_result( GOOD );
		} else {
			print_test_result( BAD );
		}
	?>
</tr>
<!-- PHP Setup check -->
<tr>
	<td bgcolor="#ffffff">
		magic_quotes_gpc
	</td>
	<td bgcolor="#ffffff">
		<?php echo ini_get( "magic_quotes_gpc" ) ?>
	</td>
</tr>
<tr>
	<td bgcolor="#ffffff">
		gpc_order
	</td>
	<td bgcolor="#ffffff">
		<?php echo ini_get( "gpc_order" ) ?>
	</td>
</tr>
<tr>
	<td bgcolor="#ffffff">
		variables_order
	</td>
	<td bgcolor="#ffffff">
		<?php echo ini_get( "variables_order" ) ?>
	</td>
</tr>
<tr>
	<td bgcolor="#ffffff">
		include_path
	</td>
	<td bgcolor="#ffffff">
		<?php echo ini_get( "include_path" ) ?>
	</td>
</tr>
<tr>
	<td bgcolor="#ffffff">
		short_open_tag
	</td>
	<td bgcolor="#ffffff">
		<?php echo ini_get("short_open_tag") ?>
	</td>
</tr>
</table>

<p>
<?php # ---- Windows ? ---- ?>
<table width="100%" bgcolor="#00aa00" border="0" cellpadding="20" cellspacing="1">
<tr>
	<td bgcolor="#f4fff4">
		<span class="title">Using Windows?</span>
		In your config_inc.php set the following:<br />
		<pre>
		$g_validate_email  = OFF;
		$g_check_mx_record = OFF;
		</pre>
		getmxrr() and checkdnsrr() are not available under Windows.  You need to turn these off the configuration variables above to avoid warnings and errors.
	</td>
</tr>
</table>


<p>
<?php # ---- Email testing ---- ?>
<table width="100%" bgcolor="#222222" border="0" cellpadding="20" cellspacing="1">
<tr>
	<td bgcolor="#f4f4f4">
		<span class="title">Testing Email</span>
		<p>
		You can test the mail() function with this form.  Just fill in the adddress and submit.  If the page takes a very long time to reappear or results in an error then you will need to investigate your php/mail server settings.  More help can be found at the <a href="http://www.php.net/manual/en/ref.mail.php">PHP website</a>.
		<p>
		<?php if ( isset( $f_mail_test ) ) { ?>
		<b><font color="#ff0000">Mail sent</font></b> -
		<?php
				if ( !$result ) {
					PRINT " PROBLEMS SENDING MAIL TO: $f_to_email. Please check your php/mail server settings.<p>";
				} else {
					PRINT " mail() send successful.<p>";
				}
			}
		?>
		<form method="post" action="<?php echo $PHP_SELF ?>">
		Email Address: <input type="text" size="32" name="f_to_email" value="<?php if ( isset( $f_to_email ) ) echo $f_to_email ?>"><br>
		<textarea name="f_message" cols="60" rows="5">Test message text</textarea><br>
		<input type="submit" value="Send Mail" name="f_mail_test">
		</form>
	</td>
</tr>
</table>

<a name="password"><p></a>
<?php # ---- Password ---- ?>
<table width="100%" bgcolor="#00bb00" border="0" cellpadding="20" cellspacing="1">
<tr>
	<td bgcolor="#f4fff4">

		<span class="title">Password Check:</span>
		<p>
		<form method="post" action="<?php echo $PHP_SELF ?>#password">
		Password: <input type="text" size="32" name="f_password" value="<?php if ( isset( $f_password ) ) echo $f_password ?>"><br>
		<input type="submit" name="f_password_test">
		</form>
		<?php
				if ( isset( $f_password_test ) ) {
					echo get_password( $f_password, PLAIN )."<br />";

					$crypt_pass = get_password( $f_password, CRYPT );
					echo $crypt_pass."<br />";
					echo get_password( $crypt_pass, CRYPT )."<br />";
					echo get_password( $f_password, CRYPT2 )."<br />";
					echo get_password( $f_password, CRYPT3 )."<br />";
					echo get_password( $f_password, MD5 )."<br />";
					echo create_random_password( $f_password )."<br />";
				}
		?>
	</td>
</tr>
</table>

<p>
<?php # ---- CRYPT CHECKS ---- ?>
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
	</td>
</tr>
</table>

</body>
</html>