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
<p>
<a href="admin_upgrade_0_16_0.php3">Upgrade to 0.16.x</a> - NOTE: Please run this again if you are upgrading to 0.16.1.  This script was improperly set and didn't actually upgrade your database.
<p>
<a href="admin_upgrade_0_15_0.php3">Upgrade to 0.15.x</a>
<p>
<a href="admin_upgrade_0_14_0.php3">Upgrade to 0.14.x</a>
<p>
<hr>
<p>
Upgrades may take several minutes depending on the size of your database.