<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
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
			Upgrade Installation
		</td>
	</tr>
</table>
<br /><br />

<p><b>WARNING:</b> - Always backup your database data before upgrading.  From the command line you can do this with the mysqldump command.</p>
<p>eg:</p>
<p><tt>mysqldump -u[username] -p[password] [database_name] > [filename]</tt></p>
<p>This will dump the contents of the specified database into the specified filename.</p>
<p>If an error occurs you can re-create your previous database by just importing your backed up database data.  You'll need to drop and recreate your database (or remove each table).</p>
<p><tt>mysql -u[username] -p[password] [database_name] < [filename]</tt></p>

<p>Upgrades may take several minutes depending on the size of your database.</p>

<div align="center">
	<table width="80%" bgcolor="#222222" border="0" cellpadding="10" cellspacing="1">
		<tr bgcolor="#ffffff">
			<td align="center" nowrap="nowrap"><p>When you have backed up your database click the link below to continue</p>[ <a href="upgrade_list.php">Upgrade Now</a> ]</td>
		</tr>
	</table>
</div>
