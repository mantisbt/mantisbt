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
	$upgrade_obj->AddItem( "ALTER TABLE mantis_bug_history_table ADD type INT(2) NOT NULL" );

	# END OF UPGRADE SQL STATEMENTS

	if ( !isset ( $f_action ) ) {
		$upgrade_obj->PrintActions();
	} else {
		$upgrade_obj->Execute( $f_action );	
	}
?>