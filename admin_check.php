<?
	# mail test
	if ( isset( $f_mail_test ) ) {
		$result = mail( $f_to_email, "Testing PHP mail() function", $f_message, "From: $f_to_email\n" );
	}
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
</style>
</head>
<body>
<?php include( "core_API.php" ) ?>
<?php
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
?>
<b>Checking your installation</b>
<table width="100%" bgcolor="#888888" border="0" cellpadding="2" cellspacing="1">
<!-- PHP version -->
<tr>
	<td bgcolor="#ffffff" colspan="2">
		You are using PHP version <?php echo phpversion() ?>
	</td>
</tr>
<!-- Test DATABASE part 1 -->
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
		if ( "/" == $g_absolute_path[strlen($g_absolute_path)-1] ) {
			print_test_result( GOOD );
		} else {
			print_test_result( BAD );
		}
	?>
</tr>
<!-- Function check -->
<tr>
	<td bgcolor="#ffffff">
		Checking to see if function crypt() exists
	</td>
	<?php
		if ( function_exists( "crypt" ) ) {
			print_test_result( GOOD );
		} else {
			print_test_result( BAD );
		}
	?>
</tr>
<!-- Function check -->
<tr>
	<td bgcolor="#ffffff">
		Checking to see if function getmxrr() exists
	</td>
	<?php
		if ( function_exists( "getmxrr" ) ) {
			print_test_result( GOOD );
		} else {
			print_test_result( BAD );
		}
	?>
</tr>
<!-- Function check -->
<tr>
	<td bgcolor="#ffffff">
		Checking to see if function checkdnsrr() exists
	</td>
	<?php
		if ( function_exists( "checkdnsrr" ) ) {
			print_test_result( GOOD );
		} else {
			print_test_result( BAD );
		}
	?>
</tr>
<!-- Function check -->
<tr>
	<td bgcolor="#ffffff">
		Checking to see if function wordwrap() exists
	</td>
	<?php
		if ( function_exists( "wordwrap" ) ) {
			print_test_result( GOOD );
		} else {
			print_test_result( BAD );
		}
	?>
</tr>
<!-- Function check -->
<tr>
	<td bgcolor="#ffffff">
		Checking to see if function str_pad() exists
	</td>
	<?php
		if ( function_exists( "str_pad" ) ) {
			print_test_result( GOOD );
		} else {
			print_test_result( BAD );
		}
	?>
</tr>
<!-- Function check -->
<tr>
	<td bgcolor="#ffffff">
		Checking to see if function is_uploaded_file() exists
	</td>
	<?php
		if ( function_exists( "is_uploaded_file" ) ) {
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
		$test_val = get_cfg_var( "register_globals" );
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
		<?php echo get_cfg_var( "magic_quotes_gpc" ) ?>
	</td>
</tr>
<tr>
	<td bgcolor="#ffffff">
		gpc_order
	</td>
	<td bgcolor="#ffffff">
		<?php echo get_cfg_var( "gpc_order" ) ?>
	</td>
</tr>
<tr>
	<td bgcolor="#ffffff">
		variables_order
	</td>
	<td bgcolor="#ffffff">
		<?php echo get_cfg_var( "variables_order" ) ?>
	</td>
</tr>
<tr>
	<td bgcolor="#ffffff">
		include_path
	</td>
	<td bgcolor="#ffffff">
		<?php echo get_cfg_var( "include_path" ) ?>
	</td>
</tr>
<tr>
	<td bgcolor="#ffffff">
		short_open_tag
	</td>
	<td bgcolor="#ffffff">
		<?php echo get_cfg_var( "short_open_tag" ) ?>
	</td>
</tr>
</table>

<p>
<hr size=1 width="100%">
<p>
<b>Testing Email</b><br>
You can test the mail() function with this form.  Just fill in the adddress and submit.  If the page takes a very long time to reappear or results in an error then you will need to investigate your php/mail server settings.  More help can be found at the <a href="http://www.php.net">PHP website</a>.
<p>
<? if ( isset( $f_mail_test ) ) { ?>
<b><font color="#ff0000">Mail sent</font></b> -
<?
		if ( !$result ) {
			PRINT " PROBLEMS SENDING MAIL TO: $f_to_email. Please check your php/mail server settings.<p>";
		} else {
			PRINT " mail() send successful.<p>";
		}
	}
?>
<form method="post" action="<? echo $PHP_SELF ?>">
Email Address: <input type="text" size="32" name="f_to_email" value="<? if ( isset( $f_to_email ) ) echo $f_to_email ?>"><br>
<textarea name="f_message" cols="60" rows="5">Test message text</textarea><br>
<input type="submit" name="f_mail_test">
</form>

</body>
</html>