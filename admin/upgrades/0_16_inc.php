<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# Changes applied to 0.16 database to give us 0.17
?>
<?php
	$upgrades = array();

	$upgrades[] = new SQLUpgrade(
			'0.16-1',
			'',
			"ALTER TABLE mantis_bug_table CHANGE last_updated last_updated DATETIME DEFAULT '1970-01-01 00:00:01' NOT NULL"
		);

	$upgrades[] = new SQLUpgrade(
			'0.16-2',
			'',
			"ALTER TABLE mantis_bugnote_table CHANGE last_modified last_modified DATETIME DEFAULT '1970-01-01 00:00:01' NOT NULL"
		);

	$upgrades[] = new SQLUpgrade(
			'0.16-3',
			'',
			"ALTER TABLE mantis_news_table CHANGE last_modified last_modified DATETIME DEFAULT '1970-01-01 00:00:01' NOT NULL"
		);

	$upgrades[] = new SQLUpgrade(
			'0.16-4',
			'',
			"ALTER TABLE mantis_user_table CHANGE last_visit last_visit DATETIME DEFAULT '1970-01-01 00:00:01' NOT NULL"
		);


	$upgrades[] = new SQLUpgrade(
			'0.16-5',
			'',
			"ALTER TABLE mantis_bug_file_table CHANGE content content LONGBLOB NOT NULL"
		);

	$upgrades[] = new SQLUpgrade(
			'0.16-6',
			'',
			"ALTER TABLE mantis_project_file_table CHANGE content content LONGBLOB NOT NULL"
		);


	$upgrades[] = new SQLUpgrade(
			'0.16-7',
			'Add view_state to bug table',
			"ALTER TABLE mantis_bug_table ADD view_state INT(2) DEFAULT '10'  NOT NULL AFTER profile_id"
		);

	$upgrades[] = new SQLUpgrade(
			'0.16-8',
			'Add view_state to bugnote table',
			"ALTER TABLE mantis_bugnote_table ADD view_state INT(2) DEFAULT '10' NOT NULL AFTER bugnote_text_id"
		);


	$upgrades[] = new SQLUpgrade(
			'0.16-9',
			'',
			"ALTER TABLE mantis_project_version_table CHANGE version version VARCHAR(64) NOT NULL"
		);

	$upgrades[] = new SQLUpgrade(
			'0.16-10',
			'',
			"ALTER TABLE mantis_project_category_table CHANGE category category VARCHAR(64) NOT NULL"
		);

	$upgrades[] = new SQLUpgrade(
			'0.16-11',
			'',
			"ALTER TABLE mantis_bug_table CHANGE category category VARCHAR(64) NOT NULL"
		);

	$upgrades[] = new SQLUpgrade(
			'0.16-12',
			'',
			"ALTER TABLE mantis_bug_table CHANGE version version VARCHAR(64) NOT NULL"
		);

	$upgrades[] = new SQLUpgrade(
			'0.16-13',
			'Add project_id to user pref table',
			"ALTER TABLE mantis_user_pref_table ADD project_id INT(7) UNSIGNED ZEROFILL NOT NULL AFTER user_id"
		);

	$upgrades[] = new SQLUpgrade(
			'0.16-14',
			'Create bug relationship table',
			"CREATE TABLE mantis_bug_relationship_table (
			  source_bug_id int(7) unsigned zerofill NOT NULL default '0000000',
			  destination_bug_id int(7) unsigned zerofill NOT NULL default '0000000',
			  relationship_type int(2) NOT NULL default '0')"
		);

	$upgrades[] = new SQLUpgrade(
			'0.16-15',
			'Create bug monitor table',
			"CREATE TABLE mantis_bug_monitor_table (
			  user_id int(7) unsigned zerofill NOT NULL default '0000000',
			  bug_id int(7) unsigned NOT NULL default '0')"
		);
	
	return $upgrades;
?>