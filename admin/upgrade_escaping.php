<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: upgrade_escaping.php,v 1.6 2005-07-22 23:46:23 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	$g_skip_open_db = true;  # don't open the database in database_api.php
	require_once ( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core.php' );
	$g_error_send_page_header = false; # suppress page headers in the error handler

    # @@@ upgrade list moved to the bottom of upgrade_inc.php

	$f_advanced = gpc_get_bool( 'advanced', false );

	$result = @db_connect( config_get_global( 'dsn', false ), config_get_global( 'hostname' ), config_get_global( 'db_username' ), config_get_global( 'db_password' ), config_get_global( 'database_name' ) );
	if ( false == $result ) {
?>
<p>Opening connection to database [<?php echo config_get_global( 'database_name' ) ?>] on host [<?php echo config_get_global( 'hostname' ) ?>] with username [<?php echo config_get_global( 'db_username' ) ?>] failed.</p>
</body>
<?php
        exit();
	}

	# check to see if the new installer was used
    if ( -1 != config_get( 'database_version', -1 ) ) {
		if ( OFF == $g_use_iis ) {
			header( 'Status: 302' );
		}
		header( 'Content-Type: text/html' );

		if ( ON == $g_use_iis ) {
			header( "Refresh: 0;url=install.php" );
		} else {
			header( "Location: install.php" );
		}
		exit; # additional output can cause problems so let's just stop output here
	}
?>
<html>
<head>
<title> Mantis Administration - String Escaping Database Fixes </title>
<link rel="stylesheet" type="text/css" href="admin.css" />
</head>
<body>
<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
	<tr class="top-bar">
		<td class="links">
			[ <a href="upgrade_list.php">Back to Upgrade List</a> ]
			[ <a href="upgrade_escaping.php">Refresh view</a> ]
		</td>
		<td class="title">
			String Escaping Database Fixes
		</td>
	</tr>
</table>
<br /><br />
<?php
	if ( ! db_table_exists( config_get( 'mantis_upgrade_table' ) ) ) {
        # Create the upgrade table if it does not exist
        $query = "CREATE TABLE " . config_get( 'mantis_upgrade_table' ) .
				  "(upgrade_id char(20) NOT NULL,
				  description char(255) NOT NULL,
				  PRIMARY KEY (upgrade_id))";

        $result = db_query( $query );
    }


	# link the data structures and upgrade list
	require_once ( 'upgrade_inc.php' );

	# drop the database upgrades and load the escaping ones
	$upgrade_set = new UpgradeSet();

	$upgrade_set->add_items( include( 'upgrades/0_17_escaping_fixes_inc.php' ) );

	$upgrade_set->process_post_data();
?>
</body>
</html>
