<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: system_utils.php,v 1.4 2004-09-23 21:22:12 thraxisp Exp $
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
	<tr bgcolor="#ffffff"><td>Move attachments stored in database schema to disk files.</td><td><center>
	<?php html_button( 'move_db2disk.php', 'Move Attachments to Disk', array( 'doc' => 'attachment') ); ?>
	</center></td></tr>

	<!-- move database project files to disk -->
	<tr bgcolor="#ffffff"><td>Move project files stored in database schema to disk.</td><td><center>
	<?php html_button( 'move_db2disk.php', 'Move Project Files to Disk', array( 'doc' => 'project') ); ?>
	</center></td></tr>

	<!-- move custom field content to standard field -->
	<tr bgcolor="#ffffff"><td>Copy Custom Field to Standard Field.</td><td><center>
	<form method="post" action="copy_field.php">
	   From
		<SELECT name="source_id">
			<?php 
				$t_custom_ids = custom_field_get_ids();
				foreach ( $t_custom_ids as $t_id ) {
					printf("<OPTION VALUE=\"%d\">%s", $t_id, custom_field_get_field($t_id, 'name' ) );
				}
			?>
		</SELECT> to
		<SELECT name="dest_id">
			<?php
				# @@@ should be expanded and configurable
				# list matches exact field name from database
				$t_dest_ids = array('fixed_in_version');
				foreach ( $t_dest_ids as $t_id ) {
					printf("<OPTION VALUE=\"%s\">%s", $t_id, $t_id );
				}
			?>
		</SELECT>
	<input type="submit" class="button" value="Copy" >
	</form>
	</center></td></tr>

	<!-- move database project files to disk -->
	<tr bgcolor="#ffffff"><td>Analyse Workflow</td><td><center>
	<?php html_button( 'workflow.php', 'Analyse' ); ?>
	</center></td></tr>


</table>
</body>
</html>