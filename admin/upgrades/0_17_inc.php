<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# Changes applied to 0.17 database to give us ____
?>
<?php
	$upgrades = array();

	$upgrades[] = new SQLUpgrade( 
		'0.17-jf-1',
		'Printing Preference Table',
		"CREATE TABLE mantis_user_print_pref_table (user_id int(7) unsigned zerofill NOT ".
		  "NULL default '0000000', print_pref varchar(27) NOT NULL default '', PRIMARY KEY ".
		  "(user_id))" );

	$upgrades[] = new SQLUpgrade( 
		'0.17-jf-2',
		'Bug history',
		"ALTER TABLE mantis_bug_history_table ADD type INT(2) NOT NULL" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-3',
		'Auto-assigning of bugs for a default user per category',
		"ALTER TABLE mantis_project_category_table ADD user_id INT(7) NOT NULL" );

	$upgrades[] = new SQLUpgrade( 
		'0.17-jf-4',
		'Private news support',
		"ALTER TABLE mantis_news_table ADD view_state INT(2) DEFAULT '10' NOT NULL ".
		  "AFTER last_modified" );

	$upgrades[] = new SQLUpgrade(
		'0.17-jf-5',
		'Allow news items to stay at the top',
		"ALTER TABLE mantis_news_table ADD announcement INT(1) NOT NULL AFTER view_state" );


	$upgrades[] = new SQLUpgrade( 
		'0.17-jf-6',
		'relationship support',
		"ALTER TABLE mantis_bug_relationship_table ADD id INT(7) UNSIGNED ZEROFILL NOT ".
		  "NULL AUTO_INCREMENT PRIMARY KEY FIRST" );


	# The following are all changes that were goofed somewhere between 0.13 and here
	# In some cases, changes were made in db_upgrade scripts but not applied to the
	#  database that was used to generate db_generate.  These upgrades all attempt to
	#  make sure that all users end up with identical databases from this point on,
	#  no matter what version they originally installed.

	$upgrades[] = new SQLUpgrade( 
		'0.17-compat-1',
		'Set default for mantis_bug_file_table.date_added (incorrect for 0.15 installs)',
		"ALTER TABLE mantis_bug_file_table
		  MODIFY date_added datetime
		  DEFAULT '1970-01-01 00:00:01'
		  NOT NULL" );

	$upgrades[] = new SQLUpgrade( 
		'0.17-compat-2',
		'Correct values for mantis_bug_file_table.date_added (incorrect for 0.15 installs)',
		"UPDATE mantis_bug_file_table
		  SET date_added='1970-01-01 00:00:01'
		  WHERE date_added='0000-00-00 00:00:00'" );

	$upgrades[] = new SQLUpgrade( 
		'0.17-compat-3',
		'Set default for mantis_project_file_table.date_added (incorrect for 0.15 installs)',
		"ALTER TABLE mantis_project_file_table
		  MODIFY date_added datetime
		  DEFAULT '1970-01-01 00:00:01'
		  NOT NULL" );

	$upgrades[] = new SQLUpgrade( 
		'0.17-compat-4',
		'Correct values for mantis_project_file_table.date_added (incorrect for 0.15 installs)',
		"UPDATE mantis_project_file_table
		  SET date_added='1970-01-01 00:00:01'
		  WHERE date_added='0000-00-00 00:00:00'" );

	$upgrades[] = new SQLUpgrade( 
		'0.17-compat-5',
		'Set default for mantis_bug_table.build (incorrect for 0.16 installs)',
		"ALTER TABLE mantis_bug_table
		  MODIFY build varchar(32) 
		  DEFAULT '' 
		  NOT NULL" );

	$upgrades[] = new SQLUpgrade( 
		'0.17-compat-6',
		'Correct values for mantis_bug_table.build (incorrect for 0.16 installs)',
		"UPDATE mantis_bug_table
		  SET build=''
		  WHERE build='0'" );

	$upgrades[] = new SQLUpgrade( 
		'0.17-compat-7',
		'Set default for mantis_user_table.date_created (incorrect for < 0.17 installs)',
		"ALTER TABLE mantis_user_table
		  MODIFY date_created datetime
		  DEFAULT '1970-01-01 00:00:01'
		  NOT NULL" );

	$upgrades[] = new SQLUpgrade( 
		'0.17-compat-8',
		'Correct values for mantis_user_table.date_created (incorrect for < 0.17 installs)',
		"UPDATE mantis_user_table
		  SET date_created='1970-01-01 00:00:01'
		  WHERE date_created='0000-00-00 00:00:00'" );

	$upgrades[] = new SQLUpgrade( 
		'0.17-compat-9',
		'Set default for mantis_project_table.enabled to 1 (incorrect for < 0.17 installs)',
		"ALTER TABLE mantis_project_table
		  MODIFY enabled INT(1)
		  NOT NULL
		  DEFAULT '1'" );

	$upgrades[] = new SQLUpgrade( 
		'0.17-compat-10',
		'Set default for mantis_news_table.date_posted (incorrect for < 0.17 installs)',
		"ALTER TABLE mantis_news_table
		  MODIFY date_posted datetime
		  NOT NULL
		  DEFAULT '1970-01-01 00:00:01'" );

	$upgrades[] = new SQLUpgrade( 
		'0.17-compat-11',
		'Correct values for mantis_news_table.date_posted (incorrect for < 0.17 installs)',
		"UPDATE mantis_news_table
		  SET date_posted='1970-01-01 00:00:01'
		  WHERE date_posted='0000-00-00 00:00:00'" );

	$upgrades[] = new SQLUpgrade( 
		'0.17-compat-12',
		'Set default for mantis_bug_table.date_submitted (incorrect for < 0.17 installs)',
		"ALTER TABLE mantis_bug_table
		  MODIFY date_submitted datetime
		  NOT NULL
		  DEFAULT '1970-01-01 00:00:01'" );

	$upgrades[] = new SQLUpgrade( 
		'0.17-compat-13',
		'Correct values for mantis_bug_table.date_submitted (incorrect for < 0.17 installs)',
		"UPDATE mantis_bug_table
		  SET date_submitted='1970-01-01 00:00:01'
		  WHERE date_submitted='0000-00-00 00:00:00'" );

	$upgrades[] = new SQLUpgrade( 
		'0.17-compat-14',
		'Set default for mantis_bugnote_table.date_submitted (incorrect for < 0.17 installs)',
		"ALTER TABLE mantis_bugnote_table
		  MODIFY date_submitted datetime
		  NOT NULL
		  DEFAULT '1970-01-01 00:00:01'" );

	$upgrades[] = new SQLUpgrade( 
		'0.17-compat-15',
		'Correct values for mantis_bugnote_table.date_submitted (incorrect for < 0.17 installs)',
		"UPDATE mantis_bugnote_table
		  SET date_submitted='1970-01-01 00:00:01'
		  WHERE date_submitted='0000-00-00 00:00:00'" );

	# @@@ this was added in db_upgrade.sql going from 0.14 to 0.15 but not added to db_generate.sql
	#  this means that users who installed from versions after 0.15 don't have it but those from before
	#  do... so users from before upgrading with this will get an error about the key already existing...
	$upgrades[] = new FunctionUpgrade( 
		'0.17-compat-16',
		'Add unique index to cookie_string if it is not already there (incorrect for > 0.14)',
		'upgrade_0_17_compat_16' );
	
	function upgrade_0_17_compat_16() {
		$query = 'DESCRIBE mantis_user_table cookie_string';

		$result = db_query( $query );

		$t_row = db_fetch_array( $result );

		if ( 'UNI' != $t_row['Key'] ) {
			$query = "ALTER TABLE mantis_user_table
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
		$query = 'DESCRIBE mantis_project_version_table';

		$result = db_query( $query );

		$count = db_num_rows( $result );

		for ( $i=0 ; $i < $count ; $i++ ) {
			$row = db_fetch_array( $result );

			if ( $row['Field'] == 'ver_order' ) {
				$query = "ALTER TABLE mantis_project_version_table
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
		"DELETE FROM mantis_project_user_list_table
		  WHERE project_id=0" );

	return $upgrades;
?>