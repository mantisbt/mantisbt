<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# Changes applied to 0.13 database to give us 0.14

	# --------------------------------------------------------
	# $Id: 0_13_inc.php,v 1.9 2005-07-14 21:38:01 thraxisp Exp $
	# --------------------------------------------------------
?>
<?php
	require( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'db_table_names_inc.php' );

	$upgrades = array();

	$upgrades[] = new SQLUpgrade(
			'0.13-1',
			'Add mantis_project_table',
			"CREATE TABLE IF NOT EXISTS $t_project_table (
			  id int(7) unsigned zerofill NOT NULL auto_increment,
			  name varchar(128) NOT NULL,
			  status enum('development','release','stable','obsolete') DEFAULT 'development' NOT NULL,
			  enabled char(3) NOT NULL,
			  view_state set('public','private') DEFAULT 'public' NOT NULL,
			  description text NOT NULL,
			  PRIMARY KEY (id),
			  KEY id (id),
			  UNIQUE name (name) )"
		);

	# @@@ Only add this record if the table has 0 rows, i.e. just added now.
	$upgrades[] = new SQLUpgrade(
			'0.13-2',
			'Insert default project into mantis_project_table',
			"INSERT INTO $t_project_table
			  ( id, name, status, enabled, view_state, description )
			  VALUES ( '0000001', 'mantis', 'development', 'on', 'public', 'Mantis.  Report problems with the actual bug tracker here. (Do not remove this account.  You can set it to be disabled or private if you do not wish to see it)' )"
		);

	$upgrades[] = new SQLUpgrade(
			'0.13-3',
			'Add mantis_project_category_table',
			"CREATE TABLE IF NOT EXISTS $t_project_category_table (
			  project_id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
			  category varchar(32) NOT NULL )"
		);

	$upgrades[] = new SQLUpgrade(
			'0.13-4',
			'Add mantis_project_version_table',
			"CREATE TABLE IF NOT EXISTS $t_project_version_table (
			  project_id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
			  version varchar(32) NOT NULL )"
		);

	$upgrades[] = new SQLUpgrade(
			'0.13-5',
			'Add project_id column to mantis_bug_table',
			"ALTER TABLE $t_bug_table ADD project_id INT (7) UNSIGNED ZEROFILL not null AFTER id"
		);

	$upgrades[] = new SQLUpgrade(
			'0.13-6',
			'Change category column in mantis_bug_table to varchar',
			"ALTER TABLE $t_bug_table CHANGE category category VARCHAR (32) not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.13-7',
			'Change version column in mantis_bug_table to varchar',
			"ALTER TABLE $t_bug_table CHANGE version version VARCHAR (32) DEFAULT 'none' not null"
		);

	$upgrades[] = new SQLUpgrade(
			'0.13-8',
			'Set project_id to "0000001" for all bugs',
			"UPDATE $t_bug_table
			  SET project_id='0000001'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.13-9',
			'Add project_id column news table',
			"ALTER TABLE $t_news_table ADD project_id INT (7) UNSIGNED ZEROFILL not null AFTER id"
		);

	$upgrades[] = new SQLUpgrade(
			'0.13-10',
			'Set project_id to "0000001" for all news postings',
			"UPDATE $t_news_table
			  SET project_id='0000001'"
		);

	$upgrades[] = new SQLUpgrade(
			'0.13-11',
			'Added login count to user table',
			"ALTER TABLE $t_user_table ADD login_count INT not null DEFAULT '0' AFTER access_level"
		);

	$upgrades[] = new SQLUpgrade(
			'0.13-12',
			'Add manager to access_levels',
			"ALTER TABLE $t_user_table
			  CHANGE access_level access_level
			  ENUM ('viewer','reporter','updater','developer','manager','administrator') DEFAULT 'viewer'
			  NOT NULL"
		);

	$upgrades[] = new SQLUpgrade(
			'0.13-13',
			'Make username unique',
			"ALTER IGNORE TABLE $t_user_table ADD UNIQUE(username)"
		);


	return $upgrades;
?>
