<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# Changes applied to 0.18 database

	# --------------------------------------------------------
	# $Id: 0_19_inc.php,v 1.3 2004-12-12 20:35:00 bpfennigschmidt Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'db_table_names_inc.php' );

	$upgrades = array();

	$upgrades[] = new SQLUpgrade(
			'bugnote-type',
			'Add note type column to bugnote',
			"ALTER TABLE $t_bugnote_table ADD note_type INT(7) default '0'" );

	$upgrades[] = new SQLUpgrade(
			'bugnote-attr',
			'Add note_attr column to bugnote',
			"ALTER TABLE $t_bugnote_table ADD note_attr VARCHAR(250) default ''" );

	$upgrades[] = new SQLUpgrade( 
			'tokensdb-1',
			'Add mantis_tokens_table',
			"CREATE TABLE $t_tokens_table (
			  id int NOT NULL auto_increment,
			  owner int NOT NULL,
			  type int NOT NULL,
			  timestamp datetime NOT NULL,
			  expiry datetime NOT NULL,
			  value text NOT NULL,
			  PRIMARY KEY (id))"
		);

	$upgrades[] = new SQLUpgrade(
			'sticky-issues',
			'Add sticky column to bug table',
			"ALTER TABLE $t_bug_table ADD sticky TINYINT(1) default '0' NOT NULL" );

	return $upgrades;
?>
