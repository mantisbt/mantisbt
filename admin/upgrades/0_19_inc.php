<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# Changes applied to 0.18 database

	# --------------------------------------------------------
	# $Id: 0_19_inc.php,v 1.1 2004-10-05 17:20:33 thraxisp Exp $
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

	return $upgrades;
?>
