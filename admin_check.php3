<? include( "core_API.php" ) ?>
<b>Database and config_inc.php test file</b>: If you see "Everything should be working." at the bottom and no errors then everything should have gone well.  Otherwise you need to configure your settings correctly.
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
<b>Connection success</b>.
<p>
Now, we'll do a simple select to see if your tables are accessible.
<p>
<?
	$query = "SELECT COUNT(*) FROM $g_mantis_user_table";
	$result = db_query( $query );
	if ( $result ) {
		$user_count = mysql_result( $result, 0, 0 );
?>
<p>
		OK, you have <? echo $user_count ?> user accounts.  The tables seem to be setup correctly.
<p>
<?
	}
?>
<p>Now, we'll check if your PHP Version is up-to-date enough (=>3.0.13).
<?
$ver=explode(".",phpversion());

if($ver[0]==3){
    if($ver[2]>=13){
        echo "<br>PHP version OK (".phpversion().")";
    }else{
        // Error (pl, rc) or smaller then13
        if(sub_str($ver[2],1)>=13){
            echo "<br>PHP version OK (".phpversion().")";
        }else{
            echo "<br>you must upgrade your php version to at least 3.0.13 (Current ".phpversion().")";
        }
    }
}else{
    echo "<br>PHP version OK (".phpversion().")";
}
?>
<p>
<b>Everything should be working</b>.