<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: upgrade.php,v 1.8 2005-07-02 00:56:04 thraxisp Exp $
	# --------------------------------------------------------
?>
<?php
	require_once ( 'upgrade_inc.php' );
	
	if ( ! db_table_exists( $t_upgrade_table ) ) {
        # Create the upgrade table if it does not exist
        $query = "CREATE TABLE IF NOT EXISTS $t_upgrade_table
				  (upgrade_id char(20) NOT NULL,
				  description char(255) NOT NULL,
				  PRIMARY KEY (upgrade_id))";

        $result = db_query( $query );
    }

	$upgrade_set = new UpgradeSet();

	$upgrade_set->add_items_with_check( 'upgrades/0_13_inc.php', $t_project_table );
	$upgrade_set->add_items_with_check( 'upgrades/0_14_inc.php', $t_bug_file_table );
	$upgrade_set->add_items_with_check( 'upgrades/0_15_inc.php', $t_bug_history_table );
	$upgrade_set->add_items_with_check( 'upgrades/0_16_inc.php', $t_bug_monitor_table );

    # this upgrade process was introduced in 0.17.x, so beyond here, the 
    #  process of checking the upgrade_table to see if updates are applied should work
	$upgrade_set->add_items_with_check( 'upgrades/0_17_inc.php', '', '0.17.0' );
	$upgrade_set->add_items_with_check( 'upgrades/0_18_inc.php', '', '0.18.0' );
	$upgrade_set->add_items_with_check( 'upgrades/0_19_inc.php', '', '0.19.0' );
	$upgrade_set->add_items_with_check( 'upgrades/1_00_inc.php', '', '1.0.0' );
	
	$f_advanced = gpc_get_bool( 'advanced', false );
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
			[ <a href="upgrade.php">Refresh view</a> ]
			[ <a href="upgrade.php?advanced=<?php echo ( $f_advanced ? 0 : 1 ) ?>"><?php echo ( $f_advanced ? 'Simple' : 'Advanced' ) ?></a> ]
		</td>
		<td class="title">
			Upgrade Installation
		</td>
	</tr>
</table>
<br /><br />

<?php
	$upgrade_set->process_post_data( $f_advanced );
?>
</body>
</html>
