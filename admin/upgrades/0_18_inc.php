<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# Changes applied to 0.18 database

	# --------------------------------------------------------
	# $Id: 0_18_inc.php,v 1.3 2004-04-01 18:42:10 narcissus Exp $
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
			  id int(7) unsigned zerofill DEFAULT '0000001' NOT NULL auto_increment,
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

	return $upgrades;
?>