<?php
	require( "../constant_inc.php" );
	require( "../config_inc.php" );
	require( "../core_database_API.php" );
?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	class UpgradeItem {
		var $item_count;
		var $query_arr;

		function UpgradeItem() {
			$this->item_count = 0;
			$this->query_arr = array();
		}

		function AddItem( $p_string ) {
			$this->query_arr[$this->item_count] = $p_string;
			$this->item_count++;
		}

		function PrintAll() {
			for ( $i=0; $i<$this->item_count; $i++ ) {
				echo "ONLY PRINTING: ".$this->query_arr[$i]."<br />";
			}
		}

		function PerformAll() {
			for ( $i=0; $i<$this->item_count; $i++ ) {
				PRINT "Executing upgrade #".$i.": ".$this->query_arr[$i]."<br />";
				$result = db_query( $this->query_arr[$i] );
			}
		}
	}
?>
<?php
	$upgrade_obj = new UpgradeItem();
	$upgrade_obj->AddItem( "ALTER TABLE mantis_bug_table CHANGE last_updated last_updated DATETIME DEFAULT '1970-01-01 00:00:01' NOT NULL" );
	$upgrade_obj->AddItem( "ALTER TABLE mantis_bugnote_table CHANGE last_modified last_modified DATETIME DEFAULT '1970-01-01 00:00:01' NOT NULL" );
	$upgrade_obj->AddItem( "ALTER TABLE mantis_news_table CHANGE last_modified last_modified DATETIME DEFAULT '1970-01-01 00:00:01' NOT NULL" );
	$upgrade_obj->AddItem( "ALTER TABLE mantis_user_table CHANGE last_visit last_visit DATETIME DEFAULT '1970-01-01 00:00:01' NOT NULL" );

	$upgrade_obj->AddItem( "ALTER TABLE mantis_bug_file_table CHANGE content content LONGBLOB NOT NULL" );
	$upgrade_obj->AddItem( "ALTER TABLE mantis_project_file_table CHANGE content content LONGBLOB NOT NULL" );

	$upgrade_obj->AddItem( "ALTER TABLE mantis_bug_table ADD view_state INT(2) DEFAULT '10'  NOT NULL AFTER profile_id" );
	$upgrade_obj->AddItem( "ALTER TABLE mantis_bugnote_table ADD view_state INT(2) DEFAULT '10' NOT NULL AFTER bugnote_text_id" );

	$upgrade_obj->AddItem( "ALTER TABLE mantis_project_version_table CHANGE version version VARCHAR(64) NOT NULL" );
	$upgrade_obj->AddItem( "ALTER TABLE mantis_project_category_table CHANGE category category VARCHAR(64) NOT NULL" );
	$upgrade_obj->AddItem( "ALTER TABLE mantis_bug_table CHANGE category category VARCHAR(64) NOT NULL" );
	$upgrade_obj->AddItem( "ALTER TABLE mantis_bug_table CHANGE version version VARCHAR(64) NOT NULL" );

	$upgrade_obj->AddItem( "ALTER TABLE mantis_user_pref_table ADD project_id INT(7) UNSIGNED ZEROFILL NOT NULL AFTER user_id" );

	$upgrade_obj->AddItem( "CREATE TABLE mantis_bug_relationship_table (
							source_bug_id int(7) unsigned zerofill NOT NULL default '0000000',
							destination_bug_id int(7) unsigned zerofill NOT NULL default '0000000',
							relationship_type int(2) NOT NULL default '0')" );

	$upgrade_obj->AddItem( "CREATE TABLE mantis_bug_monitor_table (
							user_id int(7) unsigned zerofill NOT NULL default '0000000',
							bug_id int(7) unsigned NOT NULL default '0')" );

	$upgrade_obj->PerformAll();
?>
<p>Finished