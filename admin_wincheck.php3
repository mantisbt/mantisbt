<? include( "core_API.php" ) ?>
<h2>This file will try to identify the possible problems that windows users are having with not being able to login.</h2>
<p>
Trying to connect to database <b><? echo $g_database_name ?></b>
on host <b><? echo  $g_hostname ?></b>
with username <b><? echo $g_db_username ?></b>
and password **(hidden)**.
<p>
If you don't see any errors below then your connection works.
<p>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
?>
<p>
<h3>...Connection success</h3>
<hr>
<p>
Now, we'll do a simple select to see if your tables are accessible...
<p>
<?
	$query = "SELECT username FROM $g_mantis_user_table";
	$result = db_query( $query );
	$user_count = mysql_num_rows( $result );
?>
<p>
		...OK, you have <? echo $user_count ?> user accounts.  They are:
<b>
	<?
		for ($i=0;$i<$user_count;$i++) {
			$row = db_fetch_array( $result );
			$username = $row["username"];
			echo $username." ";
		}
	?>
</b>
		The tables seem to be setup correctly.
<p>
We will now run the crypt() function and see what the results are.  This is the result of crypt on "root".  It will be different everytime you run the crypt() command.  Should look like "sDJeuusPdptvI
" or "OhynONwj7o2Do", etc.
<p>
<?
	$root_crypt = crypt("root");
	echo "<b>".$root_crypt."</b> is <b>".strlen($root_crypt)."</b> chars long (should be 13)";
?>
<p>
<hr>
Now We are selecting the administrator account and pulling out the crypted password and hash string.
<p>
<?
	$query = "SELECT * FROM $g_mantis_user_table WHERE username='administrator'";
	$result = db_query( $query );
	$row = mysql_fetch_array( $result );
	extract( $row, EXTR_PREFIX_ALL, "v" );
?>
<p>
The crypted administrator account password is: <b><? echo $v_password ?></b><br>
The hash string is: <b><? echo $v_cookie_string ?></b><br>
The hash string should be 101 characters in length.  It is <b><? echo strlen( $v_cookie_string ) ?></b> characters long.
<p>
<hr>
Now we will run the proper check on the password to see if it properly re-creates the administrator password.
<p>
<?
	$salt = substr( $v_password, 0, 2 );
	$test_pass = crypt( "root", $salt );
?>
<p>
<b><? echo $test_pass ?></b> should equal <b><? echo $v_password ?></b>.  We use a salt string of: <b><? echo $salt ?></b>
<p>
If you see true below then crypt() is working fine.
<h3>
<?
	if ($test_pass==$v_password) {
		PRINT "true";
	} else {
		PRINT "false";
	}
?>
</h3>
<p>
That's it for now.  If there are further problem it is in the variable passing between forms or in setting the cookie(s) or something else.