<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# Changes applied to 0.18 database

	# --------------------------------------------------------
	# $Id: 0_18_inc.php,v 1.9 2004-06-30 17:54:52 prichards Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'db_table_names_inc.php' );

	$upgrades = array();

	$upgrades[] = new FunctionUpgrade( 
		'0.18-vb-1',
		'Add index on bug_id field in mantis_bug_file_table.',
		'upgrade_0_18_vb_1' );

	$upgrades[] = new SQLUpgrade( 
			'filtersdb-1',
			'Add mantis_filters_table',
			"CREATE TABLE $t_filters_table (
			  id int(7) unsigned DEFAULT '1' NOT NULL auto_increment,
			  user_id int(7) NOT NULL,
			  project_id int(7) NOT NULL,
			  is_public BOOL,
			  name char(64) NOT NULL,
			  filter_string text NOT NULL,
			  PRIMARY KEY (id))"
		);

	function upgrade_0_18_vb_1() {
		global $t_bug_file_table;

		if ( !db_key_exists_on_field( $t_bug_file_table, 'bug_id', 'MUL' ) ) {
			$query = "ALTER TABLE $t_bug_file_table ADD INDEX bug_id ( bug_id )";
			$result = @db_query( $query );

			if ( false == $result ) {
				return false;
			}
		}

		return true;
	}

	$upgrades[] = new FunctionUpgrade( 
		'emailsevs-1',
		'Add the necessary columns for email severity filtering',
		'emailseverities_fix_1' );
	
	function emailseverities_fix_1() {
		global $t_user_pref_table;
		
		if ( !db_field_exists( 'email_on_priority_minimum_severity', $t_user_pref_table ) ) {
			$query = "ALTER TABLE $t_user_pref_table ADD email_on_priority_minimum_severity INT(2) DEFAULT '10' NOT NULL
						AFTER email_on_priority";
	
			$result = @db_query( $query );
	
			if ( false == $result ) {
				return false;
			}
		}
	
		if ( !db_field_exists( 'email_on_status_minimum_severity', $t_user_pref_table ) ) {
			$query = "ALTER TABLE $t_user_pref_table ADD email_on_status_minimum_severity INT(2) DEFAULT '10' NOT NULL
						AFTER email_on_priority_minimum_severity";
	
			$result = @db_query( $query );
			
			if ( false == $result ) {
				return false;
			}
		}

		if ( !db_field_exists( 'email_on_bugnote_minimum_severity', $t_user_pref_table ) ) {
			$query = "ALTER TABLE $t_user_pref_table ADD email_on_bugnote_minimum_severity INT(2) DEFAULT '10' NOT NULL
						AFTER email_on_status_minimum_severity";
	
			$result = @db_query( $query );
			
			if ( false == $result ) {
				return false;
			}
		}

		if ( !db_field_exists( 'email_on_reopened_minimum_severity', $t_user_pref_table ) ) {
			$query = "ALTER TABLE $t_user_pref_table ADD email_on_reopened_minimum_severity INT(2) DEFAULT '10' NOT NULL
						AFTER email_on_bugnote_minimum_severity";
	
			$result = @db_query( $query );
			
			if ( false == $result ) {
				return false;
			}
		}

		if ( !db_field_exists( 'email_on_closed_minimum_severity', $t_user_pref_table ) ) {
			$query = "ALTER TABLE $t_user_pref_table ADD email_on_closed_minimum_severity INT(2) DEFAULT '10' NOT NULL
						AFTER email_on_reopened_minimum_severity";
	
			$result = @db_query( $query );
			
			if ( false == $result ) {
				return false;
			}
		}

		if ( !db_field_exists( 'email_on_resolved_minimum_severity', $t_user_pref_table ) ) {
			$query = "ALTER TABLE $t_user_pref_table ADD email_on_resolved_minimum_severity INT(2) DEFAULT '10' NOT NULL
						AFTER email_on_closed_minimum_severity";
	
			$result = @db_query( $query );
			
			if ( false == $result ) {
				return false;
			}
		}

		if ( !db_field_exists( 'email_on_feedback_minimum_severity', $t_user_pref_table ) ) {
			$query = "ALTER TABLE $t_user_pref_table ADD email_on_feedback_minimum_severity INT(2) DEFAULT '10' NOT NULL
						AFTER email_on_resolved_minimum_severity";
	
			$result = @db_query( $query );
			
			if ( false == $result ) {
				return false;
			}
		}

		if ( !db_field_exists( 'email_on_assigned_minimum_severity', $t_user_pref_table ) ) {
			$query = "ALTER TABLE $t_user_pref_table ADD email_on_assigned_minimum_severity INT(2) DEFAULT '10' NOT NULL
						AFTER email_on_feedback_minimum_severity";
	
			$result = @db_query( $query );
			
			if ( false == $result ) {
				return false;
			}
		}

		if ( !db_field_exists( 'email_on_new_minimum_severity', $t_user_pref_table ) ) {
			$query = "ALTER TABLE $t_user_pref_table ADD email_on_new_minimum_severity INT(2) DEFAULT '10' NOT NULL
						AFTER email_on_assigned_minimum_severity";
	
			$result = @db_query( $query );
			
			if ( false == $result ) {
				return false;
			}
		}

		return true;
	}


	$upgrades[] = new SQLUpgrade( 
			'sponsorship-1',
			'Add sponsorships table',
			"CREATE TABLE IF NOT EXISTS mantis_sponsorship_table (
				id int(7) NOT NULL auto_increment,
				bug_id int(7) NOT NULL default '0',
				user_id int(7) NOT NULL default '0',
				amount int(7) NOT NULL default '0',
				logo varchar(128) NOT NULL default '',
				url varchar(128) NOT NULL default '',
				paid int(1) NOT NULL default '0',
				date_submitted datetime NOT NULL default '1970-01-01 00:00:01',
				last_updated datetime NOT NULL default '1970-01-01 00:00:01',
				PRIMARY KEY  (id),
				KEY bug_id (bug_id),
				KEY user_id (user_id)
				) TYPE=MyISAM COMMENT='A table for sponsorships' AUTO_INCREMENT=1" );

	$upgrades[] = new SQLUpgrade( 
			'sponsorship-2',
			'Add sponsorship_total to bug table',
			"ALTER TABLE mantis_bug_table ADD sponsorship_total INT( 7 ) DEFAULT '0' NOT NULL" );

	$upgrades[] = new SQLUpgrade( 
			'sponsorship-3',
			'Add an index on sponsorship_total in bug table',
			"ALTER TABLE mantis_bug_table ADD INDEX sponsorship_total ( sponsorship_total )" );

	$upgrades[] = new SQLUpgrade( 
			'fixed_in_version-1',
			'Add fixed_in_version field to bug table.',
			"ALTER TABLE mantis_bug_table ADD fixed_in_version VARCHAR( 64 ) NOT NULL AFTER version" );

	$upgrades[] = new SQLUpgrade( 
			'fixed_in_version-2',
			'Add index on fixed_in_version field in bug table.',
			"ALTER TABLE mantis_bug_table ADD INDEX ( fixed_in_version )" );

	$upgrades[] = new SQLUpgrade( 
			'user_realname',
			'Add real name to user information.',
			"ALTER TABLE mantis_user_table ADD realname VARCHAR( 64 ) NOT NULL AFTER username" );

	$upgrades[] = new SQLUpgrade(
			'custom_fields-1',
			'Allow custom fields to be set/required for resolve/close/report/update',
			"ALTER TABLE mantis_custom_field_table ADD display_resolve BOOL NOT NULL default '0'" );

	$upgrades[] = new SQLUpgrade(
			'custom_fields-2',
			'Allow custom fields to be set/required for resolve/close/report/update',
			"ALTER TABLE mantis_custom_field_table ADD display_close BOOL NOT NULL default '0'" );

	$upgrades[] = new SQLUpgrade(
			'custom_fields-3',
			'Allow custom fields to be set/required for resolve/close/report/update',
			"ALTER TABLE mantis_custom_field_table ADD require_report BOOL NOT NULL default '0'" );

	$upgrades[] = new SQLUpgrade(
			'custom_fields-4',
			'Allow custom fields to be set/required for resolve/close/report/update',
			"ALTER TABLE mantis_custom_field_table ADD require_update BOOL NOT NULL default '0'" );

	$upgrades[] = new SQLUpgrade(
			'custom_fields-5',
			'Allow custom fields to be set/required for resolve/close/report/update',
			"ALTER TABLE mantis_custom_field_table ADD require_resolve BOOL NOT NULL default '0'" );

	$upgrades[] = new SQLUpgrade(
			'custom_fields-6',
			'Allow custom fields to be set/required for resolve/close/report/update',
			"ALTER TABLE mantis_custom_field_table ADD require_close BOOL NOT NULL default '0'" );

	return $upgrades;
?>