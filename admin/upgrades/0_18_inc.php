<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# Changes applied to 0.18 database

	# --------------------------------------------------------
	# $Id: 0_18_inc.php,v 1.1 2004-01-31 15:10:34 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'db_table_names_inc.php' );

	$upgrades = array();

	$upgrades[] = new FunctionUpgrade( 
		'0.18-vb-1',
		'Add index on bug_id field in mantis_bug_file_table.',
		'upgrade_0_18_vb_1' );

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

	return $upgrades;
?>