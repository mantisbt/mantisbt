<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: upgrade_advanced.php,v 1.5 2004-10-05 17:20:33 thraxisp Exp $
	# --------------------------------------------------------
?>
<?php
	require_once ( 'upgrade_inc.php' );

	$upgrade_set = new UpgradeSet();

	$upgrade_set->add_items( include( 'upgrades/0_13_inc.php' ) );
	$upgrade_set->add_items( include( 'upgrades/0_14_inc.php' ) );
	$upgrade_set->add_items( include( 'upgrades/0_15_inc.php' ) );
	$upgrade_set->add_items( include( 'upgrades/0_16_inc.php' ) );
	$upgrade_set->add_items( include( 'upgrades/0_17_inc.php' ) );
	$upgrade_set->add_items( include( 'upgrades/0_18_inc.php' ) );
	$upgrade_set->add_items( include( 'upgrades/0_19_inc.php' ) );
?>
<html>
<head>
<title> Mantis Administration - Database Upgrade </title>
<link rel="stylesheet" type="text/css" href="admin.css" />
</head>
<body>

<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
	<tr class="top-bar">
		<td class="links">
			[ <a href="upgrade_list.php">Back to Upgrade List</a> ]
			[ <a href="upgrade_advanced.php">Refresh view</a> ]
			[ <a href="upgrade.php">Simple</a> ]
		</td>
		<td class="title">
			Upgrade Installation
		</td>
	</tr>
</table>
<br /><br />

<?php
	$upgrade_set->process_post_data( true );
?>
</body>
</html>