<?php
	require_once ( 'admin_upgrade_inc.php' );
	
	check_applied('0.18.0', 'mantis_news_table', 'announcement');

	$upgrade_obj = new UpgradeItem();
	$upgrade_obj->SetUpgradeName ( 'Upgrade from 0.17.x to 0.18.x', 'admin_upgrade_0_18_0' );

	# START OF UPGRADE SQL STATEMENTS
	# --- LATEST CHANGES SHOULD GO AT THE BOTTOM ---

	$upgrade_obj->AddItem( "# Mantis Project Customization" );
	$upgrade_obj->AddItem( "CREATE TABLE mantis_project_customization_table ".
	                       "(project_id int(7) unsigned zerofill NOT NULL default '0000000', ".
						   "priorities varchar(200) NOT NULL default '', ".
						   "severities varchar(200) NOT NULL default '', ".
						   "reproducibilities varchar(200) NOT NULL default '', ".
						   "states varchar(200) NOT NULL default '', ".
						   "resolutions varchar(200) NOT NULL default '', ".
						   "projections varchar(200) NOT NULL default '', ".
						   "etas varchar(200) NOT NULL default '', ".
						   "colors varchar(160) NOT NULL default '', ".
						   "KEY project_id (project_id))" );
	$upgrade_obj->AddItem();

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

	if ( !isset ( $f_action ) ) {
		$upgrade_obj->PrintActions();
	} else {
		$upgrade_obj->Execute( $f_action );	
	}
?>
