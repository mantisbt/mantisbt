<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: system_utils.php,v 1.1 2004-07-23 23:20:16 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	require_once ( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core.php' );
?>
<html>
<head>
<title>Mantis Administration - System Utilities</title>
<link rel="stylesheet" type="text/css" href="admin.css" />
</head>
<body>

<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
	<tr class="top-bar">
		<td class="links">
			[ <a href="index.php">Back to Mantis Administration</a> ]
			[ <a href="upgrade_escaping.php">Refresh view</a> ]
		</td>
		<td class="title">
			System Utilities
		</td>
	</tr>
</table>
<br /><br />

<table width="80%" bgcolor="#222222" border="0" cellpadding="10" cellspacing="1">
	<tr><td bgcolor=\"#e8e8e8\" colspan=\"2\"><span class=\"title\">Upgrade Utilities</span></td></tr>

	<!-- # Headings -->
	<tr bgcolor="#ffffff"><th width="70%">Description</th><th width="30%">Execute</th></tr>

	<!-- each row links to an upgrade
		move database bug attachments to disk -->
	<tr bgcolor="#ffffff"><td>Move attachments stored in database schema to disk files.</td><td>
	<?php html_button( 'move_db2disk.php', 'Move Attachments to Disk', array( 'doc' => 'attachment') ); ?>
	</td></tr>

	<!-- move database project files to disk -->
	<tr bgcolor="#ffffff"><td>Move project files stored in database schema to disk.</td><td>
	<?php html_button( 'move_db2disk.php', 'Move Project Files to Disk', array( 'doc' => 'project') ); ?>
	</td></tr>

</table>
</body>
</html>