<?php
	require( 'admin_inc.php' );
?>
<?php
	class UpgradeItem {
		var $item_count;
		var $query_arr;
		var $upgrade_name;
		var $upgrade_file;

		function UpgradeItem() {
			$this->item_count = 0;
			$this->query_arr = array();
			$this->upgrade_name = 'upgrade_name_not_set_call_SetUpgradeName';
			$this->upgrade_file = 'script_name_not_set_call_SetUpgradeName';
		}

		# For example, Upgrade Name = 'Upgrade from 0.17.x to 0.18.x'
		#              Upgrade File = 'admin_upgrade_0_18_0'
		# The upgrade file name will also be used for the name of the generated
		# SQL file, it should also be the same as the php script file that has the
		# SQL statements and their execution (without extension).
		function SetUpgradeName( $p_upgrade_name, $p_upgrade_file ) {
			$this->upgrade_name = $p_upgrade_name;
			$this->upgrade_file = $p_upgrade_file;
		}

		# Valid comments are empty lines or lines that start with a #
		function IsValidComment( $p_comment ) {
			return ( ( strlen ( $p_comment ) == 0 ) || ( $p_comment[0] == '#' ) );
		}

		function AddComment( $p_comment ) {
			if ( !$this->IsValidComment( $p_comment ) ) {
				PRINT "Error: Comments must start with # ( $p_comment )";
				return;
			}

			$this->AddItem ( $p_comment );
		}

		# Empty lines or lines start with a # are considered comments
		function AddItem( $p_string = '' ) {
			$this->query_arr[$this->item_count] = $p_string;
			$this->item_count++;
		}

		# Prints the upgrade title + the execution links, hence SetUpgradeName()) must be called first.
		function PrintActions() {
			global $g_php;

			PRINT "<tr><td nowrap>$this->upgrade_name</td><td>
			       [ <a href='$this->upgrade_file$g_php?f_action=print'>Print</a> ]
			       [ <a href='$this->upgrade_file$g_php?f_action=sql'>Download SQL</a> ]
			       [ <a href='$this->upgrade_file$g_php?f_action=upgrade'>Upgrade Now</a> ]</td></tr>";
		}

		function Execute( $p_action ) {
			if ( strcmp($p_action, 'print' ) == 0 ) {
				$t_message = "PRINTING ONLY";
			} else if ( strcmp ( $p_action, 'upgrade' ) == 0 ) {
				$t_message = "PRINTING AND EXECUTING";
			} else if ( strcmp ( $p_action, 'sql' ) == 0) {
				# @@@ The generated file is in UNIX format, should it be in Windows format?
				$t_filename = $this->upgrade_file . '.sql';
				header( "Content-Type: text/plain; name=$t_filename" );
				header( 'Content-Transfer-Encoding: BASE64;' );
				header( "Content-Disposition: attachment; filename=$t_filename" );

				for ( $i=0; $i<$this->item_count; $i++ ) {
					if ( $this->IsValidComment( $this->query_arr[$i] ) ) {
						PRINT $this->query_arr[$i] . "\r\n";
					} else {
						PRINT $this->query_arr[$i] . ";\r\n";
					}
				}

				die;  # how to mark the eof with terminating the script.
			} else {
				PRINT "Unknown action < $p_action >.";
				return;
			}

			PRINT "<hr /><big>$this->upgrade_name - $t_message</big><br /><hr /><code>";

			$t_query_number = 0;
			for ( $i=0; $i<$this->item_count; $i++ ) {
				if ( $this->IsValidComment( $this->query_arr[$i] ) ) {
					PRINT '<b>' . $this->query_arr[$i] . '</b><br />';
				} else {
					$t_query_number++;
					PRINT $t_query_number  . ': '.$this->query_arr[$i].'<br />';
					flush();
					if ( strcmp( $p_action, 'upgrade' ) == 0 ) {
						$result = db_query( $this->query_arr[$i] );
					}
				}
			}

			PRINT "</code><hr />";
		}
	}
?>
