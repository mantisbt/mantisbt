<?php
	require( "../constant_inc.php" );
	require( "../config_inc.php" );
	require( "../core_database_API.php" );
?>
<h1>Mantis Database Upgrade</h1>
<b>WARNING:</b> - Always backup your database data before upgrading.  From the command line you can do this with the mysqldump command.
<p>
eg:
<p>
<font face="courier new">mysqldump -u[username] -p[password] [database_name] > [filename]</font>
<p>
This will dump the contents of the specified database into the specified filename.
<p>
If an error occurs you can re-create your previous database by just importing your backed up database data.  You'll need to drop and recreate your database (or remove each table).
<p>
<font face="courier new">mysql -u[username] -p[password] [database_name] < [filename]</font>
<p>
<hr>
If you are more than one minor version behind then you will need to run upgrades sequentially.  So to jump from 0.15.1 to 0.17.0 you would run 0.15.x to 0.16.x then 0.16.x to 0.17.x
<hr>
<p>
<table border=0 width=50%>
<?php require_once ( 'admin_upgrade_0_18_0.php' ) ?>
<tr><td>Upgrade from 0.16.x to 0.17.x</td><td>[ <a href="admin_upgrade_0_17_0<?php echo $g_php ?>">Upgrade Now</a> ]</td></tr>
<tr><td>Upgrade from 0.15.x to 0.16.x</td><td>[ <a href="admin_upgrade_0_16_0<?php echo $g_php ?>">Upgrade Now</a> ]</td></tr>
<tr><td>Upgrade from 0.14.x to 0.15.x</td><td>[ <a href="admin_upgrade_0_15_0<?php echo $g_php ?>">Upgrade Now</a> ]</td></tr>
<tr><td>Upgrade to 0.14.x</td><td>[ <a href="admin_upgrade_0_14_0<?php echo $g_php ?>">Upgrade Now</a> ]</td></tr>
</table>
<p>
<hr>
<p>
Upgrades may take several minutes depending on the size of your database.