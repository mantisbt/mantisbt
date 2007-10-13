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

	# Changes applied to 0.16 database to give us 0.17

	# --------------------------------------------------------
	# $Id: 0_16_inc.php,v 1.9.16.1 2007-10-13 22:35:07 giallu Exp $
	# --------------------------------------------------------
?>
<?php
	require( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'db_table_names_inc.php' );

	$upgrades = array();

	$upgrades[] = new SQLUpgrade(
			'0.16-1',
			'',
			"ALTER TABLE $t_bug_table CHANGE last_updated last_updated DATETIME DEFAULT '1970-01-01 00:00:01' NOT NULL"
		);

	$upgrades[] = new SQLUpgrade(
			'0.16-2',
			'',
			"ALTER TABLE $t_bugnote_table CHANGE last_modified last_modified DATETIME DEFAULT '1970-01-01 00:00:01' NOT NULL"
		);

	$upgrades[] = new SQLUpgrade(
			'0.16-3',
			'',
			"ALTER TABLE $t_news_table CHANGE last_modified last_modified DATETIME DEFAULT '1970-01-01 00:00:01' NOT NULL"
		);

	$upgrades[] = new SQLUpgrade(
			'0.16-4',
			'',
			"ALTER TABLE $t_user_table CHANGE last_visit last_visit DATETIME DEFAULT '1970-01-01 00:00:01' NOT NULL"
		);


	$upgrades[] = new SQLUpgrade(
			'0.16-5',
			'',
			"ALTER TABLE $t_bug_file_table CHANGE content content LONGBLOB NOT NULL"
		);

	$upgrades[] = new SQLUpgrade(
			'0.16-6',
			'',
			"ALTER TABLE $t_project_file_table CHANGE content content LONGBLOB NOT NULL"
		);


	$upgrades[] = new FunctionUpgrade(
			'0.16-7',
			'Add view_state to bug table',
			'upgrade_0_16_7' );

	function upgrade_0_16_7() {
		global $t_bug_table;

		if ( !db_field_exists( 'view_state', $t_bug_table ) ) {
			$query = "ALTER TABLE $t_bug_table ADD view_state INT(2) DEFAULT '10'  NOT NULL AFTER profile_id";

			$result = @db_query( $query );

			if ( false == $result ) {
				return false;
			}
		}

		return true;
	}

	$upgrades[] = new FunctionUpgrade(
			'0.16-8',
			'Add view_state to bugnote table',
			'upgrade_0_16_8' );

	function upgrade_0_16_8() {
		global $t_bugnote_table;

		if ( !db_field_exists( 'view_state', $t_bugnote_table ) ) {
			$query = "ALTER TABLE $t_bugnote_table ADD view_state INT(2) DEFAULT '10' NOT NULL AFTER bugnote_text_id";

			$result = @db_query( $query );

			if ( false == $result ) {
				return false;
			}
		}

		return true;
	}

	$upgrades[] = new SQLUpgrade(
			'0.16-9',
			'',
			"ALTER TABLE $t_project_version_table CHANGE version version VARCHAR(64) NOT NULL"
		);

	$upgrades[] = new SQLUpgrade(
			'0.16-10',
			'',
			"ALTER TABLE $t_project_category_table CHANGE category category VARCHAR(64) NOT NULL"
		);

	$upgrades[] = new SQLUpgrade(
			'0.16-11',
			'',
			"ALTER TABLE $t_bug_table CHANGE category category VARCHAR(64) NOT NULL"
		);

	$upgrades[] = new SQLUpgrade(
			'0.16-12',
			'',
			"ALTER TABLE $t_bug_table CHANGE version version VARCHAR(64) NOT NULL"
		);

	$upgrades[] = new FunctionUpgrade(
			'0.16-13',
			'Add project_id to user pref table',
			'upgrade_0_16_13' );

	function upgrade_0_16_13() {
		global $t_user_pref_table;

		if ( !db_field_exists( 'project_id', $t_user_pref_table ) ) {
			$query = "ALTER TABLE $t_user_pref_table ADD project_id INT(7) UNSIGNED ZEROFILL NOT NULL AFTER user_id";

			$result = @db_query( $query );

			if ( false == $result ) {
				return false;
			}
		}

		return true;
	}

	$upgrades[] = new SQLUpgrade(
			'0.16-14',
			'Create bug relationship table',
			"CREATE TABLE IF NOT EXISTS $t_bug_relationship_table (
			  source_bug_id int(7) unsigned zerofill NOT NULL default '0000000',
			  destination_bug_id int(7) unsigned zerofill NOT NULL default '0000000',
			  relationship_type int(2) NOT NULL default '0')"
		);

	$upgrades[] = new SQLUpgrade(
			'0.16-15',
			'Create bug monitor table',
			"CREATE TABLE IF NOT EXISTS $t_bug_monitor_table (
			  user_id int(7) unsigned zerofill NOT NULL default '0000000',
			  bug_id int(7) unsigned NOT NULL default '0')"
		);

	return $upgrades;
?>
