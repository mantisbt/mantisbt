<?php
	require_once ( 'admin_upgrade_inc.php' );

	$upgrade_obj = new UpgradeItem();
	$upgrade_obj->SetUpgradeName ( 'Upgrade from 0.17.x to 0.18.x', 'admin_upgrade_0_18_0' );

	# START OF UPGRADE SQL STATEMENTS
	# --- LATEST CHANGES SHOULD GO AT THE BOTTOM ---
	
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

	# END OF UPGRADE SQL STATEMENTS

	if ( !isset ( $f_action ) ) {
		$upgrade_obj->PrintActions();
	} else {
		$upgrade_obj->Execute( $f_action );	
	}
?>
