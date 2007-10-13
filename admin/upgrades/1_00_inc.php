<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

	# Changes applied to 0.18 database

	# --------------------------------------------------------
	# $Id: 1_00_inc.php,v 1.12.2.1 2007-10-13 22:35:10 giallu Exp $
	# --------------------------------------------------------
?>
<?php
	require( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'db_table_names_inc.php' );

	$upgrades = array();

	$upgrades[] = new SQLUpgrade(
			'config-key1',
			'make mantis_config_table keys not null',
			"ALTER TABLE $t_config_table CHANGE project_id project_id INT NOT NULL DEFAULT '0'"
		);

	$upgrades[] = new SQLUpgrade(
			'config-key2',
			'make mantis_config_table keys not null',
			"ALTER TABLE $t_config_table CHANGE user_id user_id INT NOT NULL DEFAULT '0'"
		);

	$upgrades[] = new SQLUpgrade(
			'configdb-pk',
			'Add mantis_config_table primary key',
			"ALTER TABLE $t_config_table
			    ADD PRIMARY KEY (config_id, project_id, user_id)"
		);

	$upgrades[] = new SQLUpgrade(
			'note_bug_id_index',
			'Add index on bug_id in bugnotes table',
			"ALTER TABLE $t_bugnote_table ADD INDEX ( bug_id )"
		);

	$upgrades[] = new SQLUpgrade(
			'project_child_index',
			'Add index on child_id in project hierarchy table',
			"ALTER TABLE $t_project_hierarchy_table ADD INDEX ( child_id )"
		);

	$upgrades[] = new SQLUpgrade(
			'bug_status_index',
			'Add index on status in bug table',
			"ALTER TABLE $t_bug_table ADD INDEX ( status )"
		);

	$upgrades[] = new SQLUpgrade(
			'bug_project_index',
			'Add index on project_id in bug table',
			"ALTER TABLE $t_bug_table ADD INDEX ( project_id )"
		);

	$upgrades[] = new SQLUpgrade(
			'note_updated_index',
			'Add index on last_modified in bugnotes table',
			"ALTER TABLE $t_bugnote_table ADD INDEX ( last_modified )"
		);

	$upgrades[] = new SQLUpgrade(
			'project_vs_index',
			'Add index on view_state in project table',
			"ALTER TABLE $t_project_table ADD INDEX ( view_state )"
		);

	$upgrades[] = new SQLUpgrade(
			'project_uid_index',
			'Add index on user_id in project_user table',
			"ALTER TABLE $t_project_user_list_table ADD INDEX ( user_id )"
		);

	$upgrades[] = new SQLUpgrade(
			'user_enabled_index',
			'Add index on enabled in user table',
			"ALTER TABLE $t_user_table ADD INDEX ( enabled )"
		);

	$upgrades[] = new SQLUpgrade(
			'user_access_index',
			'Add index on access_level in user table',
			"ALTER TABLE $t_user_table ADD INDEX ( access_level )"
		);
		
	$upgrades[] = new SQLUpgrade(
			'cf_string_bug_index',
			'Add index on bug_id in custom_field_string table',
			"ALTER TABLE $t_custom_field_string_table ADD INDEX ( bug_id )"
		);
	
	$upgrades[] = new SQLUpgrade(
			'pref_new_min',
			'change pref email_on_new_minimum_severity for database compabilility',
			"ALTER TABLE $t_user_pref_table CHANGE email_on_new_minimum_severity email_on_new_min_severity INT(2) DEFAULT '10' NOT NULL"
		);
	
	$upgrades[] = new SQLUpgrade(
			'pref_assigned_min',
			'change pref email_on_assigned_minimum_severity for database compabilility',
			"ALTER TABLE $t_user_pref_table CHANGE email_on_assigned_minimum_severity email_on_assigned_min_severity INT(2) DEFAULT '10' NOT NULL"
		);
	
	$upgrades[] = new SQLUpgrade(
			'pref_feedback_min',
			'change pref email_on_feedback_minimum_severity for database compabilility',
			"ALTER TABLE $t_user_pref_table CHANGE email_on_status_minimum_severity email_on_status_min_severity INT(2) DEFAULT '10' NOT NULL"
		);
	
	$upgrades[] = new SQLUpgrade(
			'pref_status_min',
			'change pref email_on_minimum_severity for database compabilility',
			"ALTER TABLE $t_user_pref_table CHANGE email_on_feedback_minimum_severity email_on_feedback_min_severity INT(2) DEFAULT '10' NOT NULL"
		);
	
	$upgrades[] = new SQLUpgrade(
			'pref_resolved_min',
			'change pref email_on_resolved_minimum_severity for database compabilility',
			"ALTER TABLE $t_user_pref_table CHANGE email_on_resolved_minimum_severity email_on_resolved_min_severity INT(2) DEFAULT '10' NOT NULL"
		);
	
	$upgrades[] = new SQLUpgrade(
			'pref_closed_min',
			'change pref email_on_closed_minimum_severity for database compabilility',
			"ALTER TABLE $t_user_pref_table CHANGE email_on_closed_minimum_severity email_on_closed_min_severity INT(2) DEFAULT '10' NOT NULL"
		);
	
	$upgrades[] = new SQLUpgrade(
			'pref_reopened_min',
			'change pref email_on_reopened_minimum_severity for database compabilility',
			"ALTER TABLE $t_user_pref_table CHANGE email_on_reopened_minimum_severity email_on_reopened_min_severity INT(2) DEFAULT '10' NOT NULL"
		);
	
	$upgrades[] = new SQLUpgrade(
			'pref_bugnote_min',
			'change pref email_on_bugnote_minimum_severity for database compabilility',
			"ALTER TABLE $t_user_pref_table CHANGE email_on_bugnote_minimum_severity email_on_bugnote_min_severity INT(2) DEFAULT '10' NOT NULL"
		);
	
	$upgrades[] = new SQLUpgrade(
			'pref_status_min',
			'change pref email_on_status_minimum_severity for database compabilility',
			"ALTER TABLE $t_user_pref_table CHANGE email_on_status_minimum_severity email_on_status_min_severity INT(2) DEFAULT '10' NOT NULL"
		);
	
	$upgrades[] = new SQLUpgrade(
			'pref_priority_min',
			'change pref email_on_priority_minimum_severity for database compabilility',
			"ALTER TABLE $t_user_pref_table CHANGE email_on_priority_minimum_severity email_on_priority_min_severity INT(2) DEFAULT '10' NOT NULL"
		);
	
	# this line should be the last upgrade in a version. When it is set, the upgrader
	# assumed that all updates in this file have been applied
	
	# uncomment the following line before the final release when the installer ( schema.php )is 
	# sync'd with these incremantal updates
	$upgrades[] = new ReleaseUpgrade( '51' );

	$upgrades[] = new SQLUpgrade(
			'def_fixed_in_v',
			'add default for fixed in version',
			"ALTER TABLE $t_bug_table CHANGE fixed_in_version fixed_in_version VARCHAR(64) DEFAULT '' NOT NULL"
		);
	
	return $upgrades;
?>
