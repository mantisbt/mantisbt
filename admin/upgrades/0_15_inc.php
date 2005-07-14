<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# Changes applied to 0.15 database to give us 0.16

	# --------------------------------------------------------
	# $Id: 0_15_inc.php,v 1.9 2005-07-14 21:38:01 thraxisp Exp $
	# --------------------------------------------------------
?>
<?php
	require( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'db_table_names_inc.php' );

	$upgrades = array();

	$upgrades[] = new FunctionUpgrade(
			'0.15-1',
			'Add file type column to bug file table',
			'upgrade_0_15_1' );

	function upgrade_0_15_1() {
		global $t_bug_file_table;

		if ( !db_field_exists( 'file_type', $t_bug_file_table ) ) {
			$query = "ALTER TABLE $t_bug_file_table ADD file_type VARCHAR(250) NOT NULL AFTER filesize";

			$result = @db_query( $query );

			if ( false == $result ) {
				return false;
			}
		}

		return true;
	}

	$upgrades[] = new FunctionUpgrade(
			'0.15-2',
			'Add file type column to project file table',
			'upgrade_0_15_2' );

	function upgrade_0_15_2() {
		global $t_project_file_table;

		if ( !db_field_exists( 'file_type', $t_project_file_table ) ) {
			$query = "ALTER TABLE $t_project_file_table ADD file_type VARCHAR(250) NOT NULL AFTER filesize";

			$result = @db_query( $query );

			if ( false == $result ) {
				return false;
			}
		}

		return true;
	}

	$upgrades[] = new SQLUpgrade(
			'0.15-3',
			'',
			"ALTER TABLE $t_bug_table CHANGE os_build os_build VARCHAR(32) NOT NULL"
		);

	$upgrades[] = new SQLUpgrade(
			'0.15-4',
			'',
			"ALTER TABLE $t_bug_table CHANGE build build VARCHAR(32) NOT NULL"
		);

	$upgrades[] = new SQLUpgrade(
			'0.15-5',
			'',
			"ALTER TABLE $t_bug_table CHANGE votes votes INT(4) NOT NULL"
		);

	$upgrades[] = new SQLUpgrade(
			'0.15-6',
			'',
			"ALTER TABLE $t_user_profile_table CHANGE os_build os_build VARCHAR(32) NOT NULL"
		);

	$upgrades[] = new SQLUpgrade(
			'0.15-7',
			'',
			"ALTER TABLE $t_user_pref_table CHANGE language language VARCHAR(32) DEFAULT 'english' NOT NULL"
		);

	$upgrades[] = new SQLUpgrade(
			'0.15-8',
			'Create bug history table',
			"CREATE TABLE IF NOT EXISTS $t_bug_history_table (
			  user_id int(7) unsigned zerofill NOT NULL default '0000000',
			  bug_id int(7) unsigned zerofill NOT NULL default '0000000',
			  date_modified datetime NOT NULL default '1970-01-01 00:00:01',
			  field_name varchar(32) NOT NULL default '',
			  old_value varchar(128) NOT NULL default '',
			  new_value varchar(128) NOT NULL default '',
			  KEY bug_id (bug_id),
			  KEY user_id (user_id))"
		);


	$upgrades[] = new FunctionUpgrade(
			'0.15-9',
			'Add order field to project version table',
			'upgrade_0_15_9' );

	function upgrade_0_15_9() {
		global $t_project_version_table;

		if ( !db_field_exists( 'date_order', $t_project_version_table ) ) {
			$query = "ALTER TABLE $t_project_version_table ADD date_order DATETIME DEFAULT '1970-01-01 00:00:01' NOT NULL";

			$result = @db_query( $query );

			if ( false == $result ) {
				return false;
			}
		}

		return true;
	}

	return $upgrades;
?>
