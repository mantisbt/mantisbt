<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# These upgrades fix the double escaped data that was put into the database
	#  in every version up 0.17.x.  We pull out the data, unescape it, remove
	#  entities and then insert the data back in.

	# --------------------------------------------------------
	# $Id: 0_17_escaping_fixes_inc.php,v 1.5 2005-07-14 21:38:01 thraxisp Exp $
	# --------------------------------------------------------
?>
<?php
	require( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'db_table_names_inc.php' );

	$upgrades = array();

	function upgrade_decode_entities( $p_string ) {
		$p_string = strtr( $p_string, array_flip( get_html_translation_table( HTML_ENTITIES ) ) );
		$p_string = preg_replace( "/&#([0-9]+);/me", "chr('\\1')", $p_string );
		return $p_string;
	}

	function upgrade_fix_strings( $p_table_name, $p_primary_key, $p_fields ) {
		$c_table_name = db_prepare_string( $p_table_name );
		$c_primary_key = db_prepare_string( $p_primary_key );
		$t_field_string = db_prepare_string( implode( ',', $p_fields ) );
		$query = "SELECT $c_primary_key, $t_field_string FROM $c_table_name";

		$result = @db_query( $query );

		if ( false == $result ) {
			return false;
		}

		$count = db_num_rows( $result );
		$t_failures = 0;

		for ( $i=0 ; $i < $count ; $i++ ) {
			$row = db_fetch_array( $result );

			$query2 = "UPDATE $c_table_name SET ";
			$t_updates = array();
			foreach( $p_fields as $t_field ) {
				$t_new_value = stripslashes( upgrade_decode_entities( $row[$t_field] ) );
				$t_updates[] = db_prepare_string( $t_field ) . "='" . db_prepare_string( $t_new_value ) . "'";
			}

			$query2 .= implode( ',', $t_updates );

			$query2 .= "WHERE $c_primary_key=" . $row[$p_primary_key];

			$result2 = @db_query( $query2 );

			if ( false == $result2 ) {
				$t_failures++;
			}
		}

		# If every query failed, something must be wrong so let's fail
		# If fewer failed, we don't want to fail because unescaping the
		#  successful ones again is bad.
		if ( $count > 0 && $t_failures == $count ) {
			return false;
		} else {
			return true;
		}
	}

	$upgrades[] = new FunctionUpgrade(
		'escaping-fix-1',
		'Fix double escaped data in mantis_bug_file_table',
		'upgrade_escaping_fix_1' );

	function upgrade_escaping_fix_1() {
		global $t_bug_file_table;
		return upgrade_fix_strings( $t_bug_file_table, 'id',
			array( 'title', 'description', 'filename' ) );
	}

	$upgrades[] = new FunctionUpgrade(
		'escaping-fix-2',
		'Fix double escaped data in mantis_bug_table',
		'upgrade_escaping_fix_2' );

	function upgrade_escaping_fix_2() {
		global $t_bug_table;
		return upgrade_fix_strings( $t_bug_table, 'id',
			array( 'os', 'os_build', 'platform', 'version', 'build', 'summary' ) );
	}

	$upgrades[] = new FunctionUpgrade(
		'escaping-fix-3',
		'Fix double escaped data in mantis_bug_text_table',
		'upgrade_escaping_fix_3' );

	function upgrade_escaping_fix_3() {
		global $t_bug_text_table;
		return upgrade_fix_strings( $t_bug_text_table, 'id',
			array( 'description', 'steps_to_reproduce', 'additional_information' ) );
	}

	$upgrades[] = new FunctionUpgrade(
		'escaping-fix-4',
		'Fix double escaped data in mantis_bugnote_text_table',
		'upgrade_escaping_fix_4' );

	function upgrade_escaping_fix_4() {
		global $t_bugnote_text_table;
		return upgrade_fix_strings( $t_bugnote_text_table, 'id',
			array( 'note' ) );
	}

	$upgrades[] = new FunctionUpgrade(
		'escaping-fix-5',
		'Fix double escaped data in mantis_news_table',
		'upgrade_escaping_fix_5' );

	function upgrade_escaping_fix_5() {
		global $t_news_table;
		return upgrade_fix_strings( $t_news_table, 'id',
			array( 'headline', 'body' ) );
	}

	$upgrades[] = new FunctionUpgrade(
		'escaping-fix-6',
		'Fix double escaped data in mantis_project_file_table',
		'upgrade_escaping_fix_6' );

	function upgrade_escaping_fix_6() {
		global $t_project_file_table;
		return upgrade_fix_strings( $t_project_file_table, 'id',
			array( 'title', 'description', 'filename' ) );
	}

	$upgrades[] = new FunctionUpgrade(
		'escaping-fix-7',
		'Fix double escaped data in mantis_project_table',
		'upgrade_escaping_fix_7' );

	function upgrade_escaping_fix_7() {
		global $t_project_table;
		return upgrade_fix_strings( $t_project_table, 'id',
			array( 'name', 'file_path', 'description' ) );
	}

	$upgrades[] = new FunctionUpgrade(
		'escaping-fix-8',
		'Fix double escaped data in mantis_user_profile_table',
		'upgrade_escaping_fix_8' );

	function upgrade_escaping_fix_8() {
		global $t_user_profile_table;
		return upgrade_fix_strings( $t_user_profile_table, 'id',
			array( 'platform', 'os', 'os_build', 'description' ) );
	}

	$upgrades[] = new FunctionUpgrade(
		'escaping-fix-9',
		'Fix double escaped data in mantis_bug_history_table',
		'upgrade_escaping_fix_9' );

	function upgrade_escaping_fix_9() {
		global $t_bug_history_table;

		if ( db_field_exists( 'id', $t_bug_history_table ) ) {
			return upgrade_fix_strings( $t_bug_history_table, 'id',
				array( 'field_name', 'old_value', 'new_value' ) );
		}

		return false;
	}

	$upgrades[] = new SQLUpgrade(
		'escaping-fix-10',
		'Remove history entries where type=0 and the old value = new value.  These existed because of escaping errors',
		"DELETE FROM $t_bug_history_table
		  WHERE (type = 0) AND (old_value = new_value)");

	return $upgrades;
?>
