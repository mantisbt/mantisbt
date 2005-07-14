<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# Changes applied to 0.17 database to give us ____

	# --------------------------------------------------------
	# $Id: 0_17_inc.php,v 1.14 2005-07-14 21:38:01 thraxisp Exp $
	# --------------------------------------------------------
?>
<?php
	require( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'db_table_names_inc.php' );

	$upgrades = array();

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-1',
		'Printing Preference Table',
		"CREATE TABLE IF NOT EXISTS $t_user_print_pref_table (user_id int(7) unsigned zerofill NOT ".
		  "NULL default '0000000', print_pref varchar(27) NOT NULL default '', PRIMARY KEY ".
		  "(user_id))" );

	$upgrades[] = new FunctionUpgrade(
		'0.17-jf-2',
		'Bug history',
		'upgrade_0_17_jf_2' );

	function upgrade_0_17_jf_2() {
		global $t_bug_history_table;

		if ( !db_field_exists( 'type', $t_bug_history_table ) ) {
			$query = "ALTER TABLE $t_bug_history_table ADD type INT(2) NOT NULL";

			$result = @db_query( $query );

			if ( false == $result ) {
				return false;
			}
		}

		return true;
	}

	$upgrades[] = new FunctionUpgrade(
		'0.17-jf-3',
		'Auto-assigning of bugs for a default user per category',
		'upgrade_0_17_jf_3' );

	function upgrade_0_17_jf_3() {
		global $t_project_category_table;

		if ( !db_field_exists( 'user_id', $t_project_category_table ) ) {
			$query = "ALTER TABLE $t_project_category_table ADD user_id INT(7) NOT NULL";

			$result = @db_query( $query );

			if ( false == $result ) {
				return false;
			}
		}

		return true;
	}

	$upgrades[] = new FunctionUpgrade(
		'0.17-jf-4',
		'Private news support',
		'upgrade_0_17_jf_4' );

	function upgrade_0_17_jf_4() {
		global $t_news_table;

		if ( !db_field_exists( 'view_state', $t_news_table ) ) {
			$query = "ALTER TABLE $t_news_table ADD view_state INT(2) DEFAULT '10' NOT NULL
						AFTER last_modified";

			$result = @db_query( $query );

			if ( false == $result ) {
				return false;
			}
		}

		return true;
	}

	$upgrades[] = new FunctionUpgrade(
		'0.17-jf-5',
		'Allow news items to stay at the top',
		'upgrade_0_17_jf_5' );

	function upgrade_0_17_jf_5() {
		global $t_news_table;

		if ( !db_field_exists( 'announcement', $t_news_table ) ) {
			$query = "ALTER TABLE $t_news_table ADD announcement INT(1) NOT NULL AFTER view_state";

			$result = @db_query( $query );

			if ( false == $result ) {
				return false;
			}
		}

		return true;
	}

	$upgrades[] = new FunctionUpgrade(
		'0.17-jf-6',
		'relationship support',
		'upgrade_0_17_jf_6' );

	function upgrade_0_17_jf_6() {
		global $t_bug_relationship_table;

		if ( !db_field_exists( 'id', $t_bug_relationship_table ) ) {
			$query = "ALTER TABLE $t_bug_relationship_table ADD id INT(7) UNSIGNED ZEROFILL NOT
						NULL AUTO_INCREMENT PRIMARY KEY FIRST";

			$result = @db_query( $query );

			if ( false == $result ) {
				return false;
			}
		}

		return true;
	}

	$upgrades[] = new SQLUpgrade(
		'0.17-custom-field-1',
		'Add mantis_custom_field_table',
		"CREATE TABLE IF NOT EXISTS $t_custom_field_table (
		  id int(3) NOT NULL auto_increment,
		  name varchar(64) NOT NULL default '',
		  type int(2) NOT NULL default '0',
		  possible_values varchar(255) NOT NULL default '',
		  default_value varchar(255) NOT NULL default '',
		  valid_regexp varchar(255) NOT NULL default '',
		  access_level_r int(2) NOT NULL default '0',
		  access_level_rw int(2) NOT NULL default '0',
		  length_min int(3) NOT NULL default '0',
		  length_max int(3) NOT NULL default '0',
		  advanced int(1) NOT NULL default '0',
		  PRIMARY KEY (id),
		  KEY name (name))" );

	$upgrades[] = new SQLUpgrade(
		'0.17-custom-field-2',
		'Add mantis_custom_field_string_table',
		"CREATE TABLE IF NOT EXISTS $t_custom_field_string_table (
		  field_id int(3) NOT NULL,
		  bug_id int(7) NOT NULL,
		  value varchar(255) NOT NULL default '',
		  PRIMARY KEY (field_id,bug_id))" );

	$upgrades[] = new SQLUpgrade(
		'0.17-custom-field-3',
		'Add mantis_custom_field_project_table',
		"CREATE TABLE IF NOT EXISTS $t_custom_field_project_table (
		  field_id int(3) NOT NULL,
		  project_id int(7) unsigned NOT NULL,
		  sequence int(2) NOT NULL default '0',
		  PRIMARY KEY (field_id,project_id))" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-7',
		'Drop mantis_project_customization_table',
		"DROP TABLE IF EXISTS mantis_project_customization_table" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-8',
		'Drop votes column of mantis_bug_table',
		"ALTER TABLE $t_bug_table DROP COLUMN votes" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-9',
		'Add primary key on mantis_project_version_table',
		"ALTER IGNORE TABLE $t_project_version_table ADD PRIMARY KEY (project_id,version)" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-10',
		'Add primary key on mantis_project_user_list_table',
		"ALTER IGNORE TABLE $t_project_user_list_table ADD PRIMARY KEY (project_id,user_id)" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-11',
		'Add primary key on mantis_project_category_table',
		"ALTER IGNORE TABLE $t_project_category_table ADD PRIMARY KEY (project_id,category)" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-12',
		'Add primary key on mantis_bug_monitor_table',
		"ALTER IGNORE TABLE $t_bug_monitor_table ADD PRIMARY KEY (user_id,bug_id)" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-13',
		'Remove zerofill on mantis_bug_file_table.id',
		"ALTER TABLE $t_bug_file_table
			MODIFY id int(7) unsigned NOT NULL auto_increment" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-14',
		'Remove zerofill on mantis_bug_file_table.bug_id',
		"ALTER TABLE $t_bug_file_table
			MODIFY bug_id int(7) unsigned NOT NULL" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-15',
		'Remove zerofill on mantis_bug_history_table.user_id',
		"ALTER TABLE $t_bug_history_table
			MODIFY user_id int(7) unsigned NOT NULL" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-16',
		'Remove zerofill on mantis_bug_history_table.bug_id',
		"ALTER TABLE $t_bug_history_table
			MODIFY bug_id int(7) unsigned NOT NULL" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-17',
		'Remove zerofill on mantis_bug_monitor_table.user_id',
		"ALTER TABLE $t_bug_monitor_table
			MODIFY user_id int(7) unsigned NOT NULL" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-18',
		'Remove zerofill on mantis_bug_relationship_table.id',
		"ALTER TABLE $t_bug_relationship_table
			MODIFY id int(7) unsigned NOT NULL auto_increment" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-19',
		'Remove zerofill on mantis_bug_relationship_table.source_bug_id',
		"ALTER TABLE $t_bug_relationship_table
			MODIFY source_bug_id int(7) unsigned NOT NULL default 0" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-20',
		'Remove zerofill on mantis_bug_relationship_table.destination_bug_id',
		"ALTER TABLE $t_bug_relationship_table
			MODIFY destination_bug_id int(7) unsigned NOT NULL default 0" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-21',
		'Remove zerofill on mantis_bug_table.id',
		"ALTER TABLE $t_bug_table
			MODIFY id int(7) unsigned NOT NULL auto_increment" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-22',
		'Remove zerofill on mantis_bug_table.project_id',
		"ALTER TABLE $t_bug_table
			MODIFY project_id int(7) unsigned NOT NULL" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-23',
		'Remove zerofill on mantis_bug_table.reporter_id',
		"ALTER TABLE $t_bug_table
			MODIFY reporter_id int(7) unsigned NOT NULL default 0" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-24',
		'Remove zerofill on mantis_bug_table.handler_id',
		"ALTER TABLE $t_bug_table
			MODIFY handler_id int(7) unsigned NOT NULL default 0" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-25',
		'Remove zerofill on mantis_bug_table.duplicate_id',
		"ALTER TABLE $t_bug_table
			MODIFY duplicate_id int(7) unsigned NOT NULL default 0" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-26',
		'Remove zerofill on mantis_bug_table.bug_text_id',
		"ALTER TABLE $t_bug_table
			MODIFY bug_text_id int(7) unsigned NOT NULL" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-27',
		'Remove zerofill on mantis_bug_table.profile_id',
		"ALTER TABLE $t_bug_table
			MODIFY profile_id int(7) unsigned NOT NULL default 0" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-28',
		'Remove zerofill on mantis_bug_text_table.id',
		"ALTER TABLE $t_bug_text_table
			MODIFY id int(7) unsigned NOT NULL auto_increment" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-29',
		'Remove zerofill on mantis_bugnote_table.id',
		"ALTER TABLE $t_bugnote_table
			MODIFY id int(7) unsigned NOT NULL auto_increment" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-30',
		'Remove zerofill on mantis_bugnote_table.bug_id',
		"ALTER TABLE $t_bugnote_table
			MODIFY bug_id int(7) unsigned NOT NULL" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-31',
		'Remove zerofill on mantis_bugnote_table.reporter_id',
		"ALTER TABLE $t_bugnote_table
			MODIFY reporter_id int(7) unsigned NOT NULL" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-32',
		'Remove zerofill on mantis_bugnote_table.bugnote_text_id',
		"ALTER TABLE $t_bugnote_table
			MODIFY bugnote_text_id int(7) unsigned NOT NULL" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-33',
		'Remove zerofill on mantis_bugnote_text_table.id',
		"ALTER TABLE $t_bugnote_text_table
			MODIFY id int(7) unsigned NOT NULL auto_increment" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-34',
		'Remove zerofill on mantis_news_table.id',
		"ALTER TABLE $t_news_table
			MODIFY id int(7) unsigned NOT NULL auto_increment" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-35',
		'Remove zerofill on mantis_news_table.project_id',
		"ALTER TABLE $t_news_table
			MODIFY project_id int(7) unsigned NOT NULL default 0" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-36',
		'Remove zerofill on mantis_news_table.poster_id',
		"ALTER TABLE $t_news_table
			MODIFY poster_id int(7) unsigned zerofill NOT NULL" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-37',
		'Remove zerofill on mantis_project_category_table.project_id',
		"ALTER TABLE $t_project_category_table
			MODIFY project_id int(7) unsigned NOT NULL" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-38',
		'Remove zerofill on mantis_project_file_table.id',
		"ALTER TABLE $t_project_file_table
			MODIFY id int(7) unsigned NOT NULL auto_increment" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-39',
		'Remove zerofill on mantis_project_file_table.project_id',
		"ALTER TABLE $t_project_file_table
			MODIFY project_id int(7) unsigned NOT NULL" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-40',
		'Remove zerofill on mantis_project_table.id',
		"ALTER TABLE $t_project_table
			MODIFY id int(7) unsigned NOT NULL auto_increment" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-41',
		'Remove zerofill on mantis_project_user_list_table.project_id',
		"ALTER TABLE $t_project_user_list_table
			MODIFY project_id int(7) unsigned NOT NULL" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-42',
		'Remove zerofill on mantis_project_user_list_table.user_id',
		"ALTER TABLE $t_project_user_list_table
			MODIFY user_id int(7) unsigned NOT NULL" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-43',
		'Remove zerofill on mantis_project_version_table.project_id',
		"ALTER TABLE $t_project_version_table
			MODIFY project_id int(7) unsigned NOT NULL" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-44',
		'Remove zerofill on mantis_user_pref_table.id',
		"ALTER TABLE $t_user_pref_table
			MODIFY id int(7) unsigned NOT NULL auto_increment" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-45',
		'Remove zerofill on mantis_user_pref_table.user_id',
		"ALTER TABLE $t_user_pref_table
			MODIFY user_id int(7) unsigned NOT NULL" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-46',
		'Remove zerofill on mantis_user_pref_table.project_id',
		"ALTER TABLE $t_user_pref_table
			MODIFY project_id int(7) unsigned NOT NULL default 0" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-47',
		'Remove zerofill on mantis_user_pref_table.default_profile',
		"ALTER TABLE $t_user_pref_table
			MODIFY default_profile int(7) unsigned NOT NULL default 0" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-48',
		'Remove zerofill on mantis_user_pref_table.default_project',
		"ALTER TABLE $t_user_pref_table
			MODIFY default_project int(7) unsigned NOT NULL default 0" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-49',
		'Remove zerofill on mantis_user_print_pref_table.user_id',
		"ALTER TABLE $t_user_print_pref_table
			MODIFY user_id int(7) unsigned NOT NULL" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-50',
		'Remove zerofill on mantis_user_profile_table.id',
		"ALTER TABLE $t_user_profile_table
			MODIFY id int(7) unsigned NOT NULL auto_increment" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-51',
		'Remove zerofill on mantis_user_profile_table.user_id',
		"ALTER TABLE $t_user_profile_table
			MODIFY user_id int(7) unsigned NOT NULL" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-52',
		'Remove zerofill on mantis_user_table.id',
		"ALTER TABLE $t_user_table
			MODIFY id int(7) unsigned NOT NULL auto_increment" );

	# The following are all changes that were goofed somewhere between 0.13 and here
	# In some cases, changes were made in db_upgrade scripts but not applied to the
	#  database that was used to generate db_generate.  These upgrades all attempt to
	#  make sure that all users end up with identical databases from this point on,
	#  no matter what version they originally installed.

	$upgrades[] = new SQLUpgrade(
		'0.17-compat-1',
		'Set default for mantis_bug_file_table.date_added (incorrect for 0.15 installs)',
		"ALTER TABLE $t_bug_file_table
		  MODIFY date_added datetime
		  DEFAULT '1970-01-01 00:00:01'
		  NOT NULL" );

	$upgrades[] = new SQLUpgrade(
		'0.17-compat-2',
		'Correct values for mantis_bug_file_table.date_added (incorrect for 0.15 installs)',
		"UPDATE $t_bug_file_table
		  SET date_added='1970-01-01 00:00:01'
		  WHERE date_added='0000-00-00 00:00:00'" );

	$upgrades[] = new SQLUpgrade(
		'0.17-compat-3',
		'Set default for mantis_project_file_table.date_added (incorrect for 0.15 installs)',
		"ALTER TABLE $t_project_file_table
		  MODIFY date_added datetime
		  DEFAULT '1970-01-01 00:00:01'
		  NOT NULL" );

	$upgrades[] = new SQLUpgrade(
		'0.17-compat-4',
		'Correct values for mantis_project_file_table.date_added (incorrect for 0.15 installs)',
		"UPDATE $t_project_file_table
		  SET date_added='1970-01-01 00:00:01'
		  WHERE date_added='0000-00-00 00:00:00'" );

	$upgrades[] = new SQLUpgrade(
		'0.17-compat-5',
		'Set default for mantis_bug_table.build (incorrect for 0.16 installs)',
		"ALTER TABLE $t_bug_table
		  MODIFY build varchar(32)
		  DEFAULT ''
		  NOT NULL" );

	$upgrades[] = new SQLUpgrade(
		'0.17-compat-6',
		'Correct values for mantis_bug_table.build (incorrect for 0.16 installs)',
		"UPDATE $t_bug_table
		  SET build=''
		  WHERE build='0'" );

	$upgrades[] = new SQLUpgrade(
		'0.17-compat-7',
		'Set default for mantis_user_table.date_created (incorrect for < 0.17 installs)',
		"ALTER TABLE $t_user_table
		  MODIFY date_created datetime
		  DEFAULT '1970-01-01 00:00:01'
		  NOT NULL" );

	$upgrades[] = new SQLUpgrade(
		'0.17-compat-8',
		'Correct values for mantis_user_table.date_created (incorrect for < 0.17 installs)',
		"UPDATE $t_user_table
		  SET date_created='1970-01-01 00:00:01'
		  WHERE date_created='0000-00-00 00:00:00'" );

	$upgrades[] = new SQLUpgrade(
		'0.17-compat-9',
		'Set default for mantis_project_table.enabled to 1 (incorrect for < 0.17 installs)',
		"ALTER TABLE $t_project_table
		  MODIFY enabled INT(1)
		  NOT NULL
		  DEFAULT '1'" );

	$upgrades[] = new SQLUpgrade(
		'0.17-compat-10',
		'Set default for mantis_news_table.date_posted (incorrect for < 0.17 installs)',
		"ALTER TABLE $t_news_table
		  MODIFY date_posted datetime
		  NOT NULL
		  DEFAULT '1970-01-01 00:00:01'" );

	$upgrades[] = new SQLUpgrade(
		'0.17-compat-11',
		'Correct values for mantis_news_table.date_posted (incorrect for < 0.17 installs)',
		"UPDATE $t_news_table
		  SET date_posted='1970-01-01 00:00:01'
		  WHERE date_posted='0000-00-00 00:00:00'" );

	$upgrades[] = new SQLUpgrade(
		'0.17-compat-12',
		'Set default for mantis_bug_table.date_submitted (incorrect for < 0.17 installs)',
		"ALTER TABLE $t_bug_table
		  MODIFY date_submitted datetime
		  NOT NULL
		  DEFAULT '1970-01-01 00:00:01'" );

	$upgrades[] = new SQLUpgrade(
		'0.17-compat-13',
		'Correct values for mantis_bug_table.date_submitted (incorrect for < 0.17 installs)',
		"UPDATE $t_bug_table
		  SET date_submitted='1970-01-01 00:00:01'
		  WHERE date_submitted='0000-00-00 00:00:00'" );

	$upgrades[] = new SQLUpgrade(
		'0.17-compat-14',
		'Set default for mantis_bugnote_table.date_submitted (incorrect for < 0.17 installs)',
		"ALTER TABLE $t_bugnote_table
		  MODIFY date_submitted datetime
		  NOT NULL
		  DEFAULT '1970-01-01 00:00:01'" );

	$upgrades[] = new SQLUpgrade(
		'0.17-compat-15',
		'Correct values for mantis_bugnote_table.date_submitted (incorrect for < 0.17 installs)',
		"UPDATE $t_bugnote_table
		  SET date_submitted='1970-01-01 00:00:01'
		  WHERE date_submitted='0000-00-00 00:00:00'" );

	# This was added in db_upgrade.sql going from 0.14 to 0.15 but not
	# added to db_generate.sql.  This means that users who installed from
	# versions after 0.15 don't have it but those from before do.
	# Because of this, we need a function instead of an SQL statement so
	# that users from before upgrading with this will not get an error
	# about the key already existing.
	$upgrades[] = new FunctionUpgrade(
		'0.17-compat-16',
		'Add unique index to cookie_string if it is not already there (incorrect for > 0.14)',
		'upgrade_0_17_compat_16' );

	function upgrade_0_17_compat_16() {
		global $t_user_table;
		$query = "DESCRIBE $t_user_table cookie_string";

		$result = db_query( $query );

		$t_row = db_fetch_array( $result );

		$t_key = isset( $t_row['Key'] ) ? $t_row['Key'] : $t_row['key'];

		if ( 'UNI' != $t_key ) {
			$query = "ALTER IGNORE TABLE $t_user_table
						ADD UNIQUE cookie_string (cookie_string)";

			$result = @db_query( $query );

			if ( false == $result ) {
				return false;
			}
		}

		return true;
	}

	$upgrades[] = new FunctionUpgrade(
		'0.17-compat-17',
		'Remove mantis_project_version_table.ver_order (incorrect for < 0.15)',
		'upgrade_0_17_compat_17' );

	function upgrade_0_17_compat_17() {
		global $t_project_version_table;
		$query = "DESCRIBE $t_project_version_table";

		$result = db_query( $query );

		$count = db_num_rows( $result );

		for ( $i=0 ; $i < $count ; $i++ ) {
			$t_row = db_fetch_array( $result );

			$t_field = isset( $t_row['Field'] ) ? $t_row['Field'] : $t_row['field'];
			if ( $t_field == 'ver_order' ) {
				$query = "ALTER TABLE $t_project_version_table
							DROP ver_order";

				$result = @db_query( $query );

				if ( false == $result ) {
					return false;
				}

				break;
			}
		}

		return true;
	}

	$upgrades[] = new SQLUpgrade(
		'0.17-compat-18',
		'Remove users from project 0',
		"DELETE FROM $t_project_user_list_table
		  WHERE project_id=0" );

	$upgrades[] = new FunctionUpgrade(
		'0.17-vb-19',
		'Add id field to bug history table',
		'upgrade_0_17_vb_19' );

	function upgrade_0_17_vb_19() {
		global $t_bug_history_table;

		if ( !db_field_exists( 'id', $t_bug_history_table ) ) {
			$query = "ALTER TABLE $t_bug_history_table ADD id INT(7) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";

			$result = @db_query( $query );

			if ( false == $result ) {
				return false;
			}
		}

		return true;
	}

	return $upgrades;
?>
