<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

	# --------------------------------------------------------
	# $Id: upgrade_advanced.php,v 1.7.2.1 2007-10-13 22:34:58 giallu Exp $
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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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
