<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	###########################################################################
	### INCLUDES                                                            ###
	###########################################################################

	# prevent caching
  	require( "constant_inc.php" );
	require( "config_inc.php" );

	require( "core_database_API.php" );

	if ( !empty( $g_string_cookie_val ) ) {
		$t_language = "";
		db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
		$query = "SELECT language
				FROM $g_mantis_user_pref_table pref, $g_mantis_user_table user
				WHERE user.cookie_string='$g_string_cookie_val' AND
						user.id=pref.user_id";
		$result = db_query( $query );
		$t_language = db_result( $result, 0 , 0 );
		if (!empty( $t_language )) {
			include( "strings_".$t_language.".txt" );
		} else {
			include( "strings_".$g_default_language.".txt" );
		}
		db_close();
	} else {
		include( "strings_".$g_default_language.".txt" );
	}

	require( "core_html_API.php" );
	require( "core_print_API.php" );
	require( "core_helper_API.php" );
	require( "core_summary_API.php" );
	require( "core_date_API.php" );
	require( "core_user_API.php" );
	require( "core_email_API.php" );
	require( "core_news_API.php" );
	require( "core_icon_API.php" );

	### --------------------
	### --------------------
	function get_enum_string( $p_table_name, $p_field_name ) {
		$query = "SHOW FIELDS
				FROM $p_table_name";
		$result = db_query( $query );
		$entry_count = db_num_rows( $result );
		for ($i=0;$i<$entry_count;$i++) {
			$row = db_fetch_array( $result );
	    	$t_type = stripslashes($row["Type"]);
	    	$t_field = $row["Field"];
	    	if ( $t_field==$p_field_name ) {
		    	return substr( $t_type, 5, strlen($t_type)-6);
		    }
	    } ### end for
	}
	### --------------------
	# returns the number of items in a list
	# default delimiter is a ,
	function get_list_item_count( $p_delim_char, $t_enum_string ) {
		return count( explode( $p_delim_char,$t_enum_string ) );
	}
	### Used in summary reports

	function print_bug_enum_summary2( $p_enum, $p_status="" ) {
		global $g_mantis_bug_table, $g_primary_color_light, $g_primary_color_dark,
			$g_project_cookie_val;

		$t_enum_string = get_enum_string( $g_mantis_bug_table, $p_enum );
		$t_arr = explode( ",", $t_enum_string );
		$enum_count = count( $t_arr );
		for ($i=0;$i<$enum_count;$i++) {
			$t_s = str_replace( "'", "", $t_arr[$i] );

			$query = "SELECT COUNT(*)
					FROM $g_mantis_bug_table
					WHERE $p_enum='$t_s' AND
						project_id='$g_project_cookie_val'";
			if ( !empty( $p_status ) ) {
				if ( $p_status=="open" ) {
					$query = $query." AND status<>'resolved'";
				}
				else if ( $p_status=="open" ) {
					$query = $query." AND status='resolved'";
				}
				else {
					$query = $query." AND status='$p_status'";
				}
			}
			$result = db_query( $query );
			$t_enum_count = db_result( $result, 0 );

			### alternate row colors
			if ( $i % 2 == 1) {
				$bgcolor=$g_primary_color_light;
			}
			else {
				$bgcolor=$g_primary_color_dark;
			}

			PRINT "<tr align=center bgcolor=$bgcolor>";
				PRINT "<td width=50%>";
					echo $t_s;
				PRINT "</td>";
				PRINT "<td width=50%>";
					echo $t_enum_count;
				PRINT "</td>";
			PRINT "</tr>";
		} ### end for
	}

	###########################################################################
	### END                                                                 ###
	###########################################################################
?>