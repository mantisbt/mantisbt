<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	require_once ( 'admin_upgrade_inc.php' );
	
	check_applied('0.18.0', 'mantis_news_table', 'announcement');

	$upgrade_obj = new UpgradeItem();
	$upgrade_obj->SetUpgradeName ( 'Upgrade from 0.17.x to 0.18.x', 'admin_upgrade_0_18_0' );

	# START OF UPGRADE SQL STATEMENTS
	# --- LATEST CHANGES SHOULD GO AT THE BOTTOM ---

	$upgrade_obj->AddItem( "# Printing Preference Table" );
	$upgrade_obj->AddItem( "CREATE TABLE mantis_user_print_pref_table (user_id int(7) unsigned zerofill NOT ".
	                       "NULL default '0000000', print_pref varchar(27) NOT NULL default '', PRIMARY KEY ".
						   "(user_id))" );
	$upgrade_obj->AddItem();

	$upgrade_obj->AddItem( "# Bug history" );
	$upgrade_obj->AddItem( "ALTER TABLE mantis_bug_history_table ADD type INT(2) NOT NULL" );
	$upgrade_obj->AddItem();

	$upgrade_obj->AddItem( "# Auto-assigning of bugs for a default user per category" );
	$upgrade_obj->AddItem( "ALTER TABLE mantis_project_category_table ADD user_id INT(7) NOT NULL" );
	$upgrade_obj->AddItem();

	$upgrade_obj->AddItem( "# Private news support" );
	$upgrade_obj->AddItem( "ALTER TABLE mantis_news_table ADD view_state INT(2) DEFAULT '10' NOT NULL ".
							"AFTER last_modified" );
	$upgrade_obj->AddItem();

	$upgrade_obj->AddItem( "# Allow news items to stay at the top" );
	$upgrade_obj->AddItem( "ALTER TABLE mantis_news_table ADD announcement INT(1) NOT NULL AFTER view_state" );
	$upgrade_obj->AddItem();

	$upgrade_obj->AddItem( "# Bug relationship support" );
	$upgrade_obj->AddItem( "ALTER TABLE mantis_bug_relationship_table ADD id INT(7) UNSIGNED ZEROFILL NOT ".
							"NULL AUTO_INCREMENT PRIMARY KEY FIRST" );
	$upgrade_obj->AddItem();

	# @@@ this was added in db_upgrade.sql going from 0.14 to 0.15 but not added to db_generate.sql
	#  this means that users who installed from versions after 0.15 don't have it but those from before
	#  do... so users from before upgrading with this will get an error about the key already existing...
	$upgrade_obj->AddItem( "# Bug relationship support" );
	$upgrade_obj->AddItem( "ALTER TABLE mantis_user_table ADD UNIQUE cookie_string (cookie_string)" );
	$upgrade_obj->AddItem();


	# END OF UPGRADE SQL STATEMENTS

	$f_action = $_REQUEST['action'];

	if ( !isset ( $f_action ) ) {
		$upgrade_obj->PrintActions();
	} else {
		$upgrade_obj->Execute( $f_action );	
	}
?>
