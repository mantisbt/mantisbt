<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# Changes applied to 0.18 database

	# --------------------------------------------------------
	# $Id: 1_00_inc.php,v 1.1 2005-04-19 13:49:05 thraxisp Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'db_table_names_inc.php' );

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


	return $upgrades;
?>
