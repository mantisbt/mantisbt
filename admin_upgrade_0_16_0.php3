<?
	require( "constant_inc.php" );
	require( "config_inc.php" );
	require( "core_database_API.php" );
?>
<?
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
				echo $this->query_arr[$i]."<br>";
			}
		}

		function PerformItem( $p_index ) {
			PRINT "Executing upgrade #".$p_index."<br>";
			#$result = db_query( $this->query_arr[$p_index] );
		}

		function PerformAll() {
			for ( $i=0; $i<$this->item_count; $i++ ) {
				PRINT "Executing upgrade #".$i.": ".$this->query_arr[$i]."<br>";
				#$result = db_query( $this->query_arr[$i] );
			}
		}
	}
?>
<?
	$upgrade_obj = new UpgradeItem();
	$upgrade_obj->AddItem( "ALTER TABLE mantis_bug_file_table ADD file_type VARCHAR(250) NOT NULL AFTER filesize" );
	$upgrade_obj->AddItem( "ALTER TABLE mantis_project_file_table ADD file_type VARCHAR(250) NOT NULL AFTER filesize" );
	$upgrade_obj->AddItem( "ALTER TABLE mantis_bug_table CHANGE os_build os_build VARCHAR(32) NOT NULL" );
	$upgrade_obj->AddItem( "ALTER TABLE mantis_bug_table CHANGE build build VARCHAR(32) NOT NULL" );
	$upgrade_obj->AddItem( "ALTER TABLE mantis_bug_table CHANGE votes votes INT(4) NOT NULL" );
	$upgrade_obj->AddItem( "ALTER TABLE mantis_user_profile_table CHANGE os_build os_build VARCHAR(32) NOT NULL" );
	$upgrade_obj->AddItem( "ALTER TABLE mantis_user_pref_table CHANGE language language VARCHAR(32) DEFAULT 'english' NOT NULL" );
	$upgrade_obj->AddItem( "CREATE TABLE mantis_bug_history_table (
							user_id int(7) unsigned zerofill NOT NULL default '0000000',
							bug_id int(7) unsigned zerofill NOT NULL default '0000000',
							date_modified datetime NOT NULL default '1970-01-01 00:00:01',
							field_name varchar(32) NOT NULL default '',
							old_value varchar(128) NOT NULL default '',
							new_value varchar(128) NOT NULL default '',
							KEY bug_id (bug_id),
							KEY user_id (user_id))" );

	$upgrade_obj->AddItem( "ALTER TABLE mantis_project_version_table ADD date_order DATETIME DEFAULT '1970-01-01 00:00:01' NOT NULL" );

	#$upgrade_obj->PrintAll();
	$upgrade_obj->PerformAll();
?>
<p>Finished