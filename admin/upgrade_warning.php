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
 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */
/**
 * MantisBT Core API's
 */
require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core.php' );

access_ensure_global_level( config_get_global( 'admin_site_threshold' ) );

$g_error_send_page_header = false; # suppress page headers in the error handler

# @@@ upgrade list moved to the bottom of upgrade_inc.php

$f_advanced = gpc_get_bool( 'advanced', false );

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>MantisBT Administration - Check Installation </title>
<link rel="stylesheet" type="text/css" href="admin.css" />
</head>
<body>

<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
	<tr class="top-bar">
		<td class="links">
			[ <a href="index.php">Back to Administration</a> ]
		</td>
		<td class="title">
			Upgrade Installation
		</td>
	</tr>
</table>
<br /><br />

<p><b>WARNING:</b> - Always backup your database data before upgrading.  For example, if you use a mysql database, From the command line you can do this with the mysqldump command.</p>
<p>eg:</p>
<p><tt>mysqldump -u[username] -p[password] [database_name] > [filename]</tt></p>
<p>This will dump the contents of the specified database into the specified filename.</p>
<p>If an error occurs you can re-create your previous database by just importing your backed up database data.  You'll need to drop and recreate your database (or remove each table).</p>
<p><tt>mysql -u[username] -p[password] [database_name] < [filename]</tt></p>

<p>Upgrades may take several minutes depending on the size of your database.</p>

<div align="center">
	<table width="80%" bgcolor="#222222" border="0" cellpadding="10" cellspacing="1">
		<tr bgcolor="#ffffff">
				<?php
# check to see if the new installer was used
if( -1 != config_get( 'database_version', -1 ) ) {
	?>
				<td align="center" nowrap="nowrap"><p>When you have backed up your database click the link below to continue</p>[ <a href="install.php">Upgrade Now</a> ]</td>
				<?php
}
else {?>
				<td align="center" nowrap="nowrap"><p>You aware to be running an old (pre 1.1.0) release of MantisBT. To update to this release of mantis, you must first update your installation to 1.1</td>
				<?php
}?>
		</tr>
	</table>
</div>
</body>
</html>
