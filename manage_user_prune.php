<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: manage_user_prune.php,v 1.7 2005-02-12 20:01:06 jlatour Exp $
	# --------------------------------------------------------
?>
<?php require_once( 'core.php' ) ?>
<?php
	access_ensure_global_level( config_get( 'manage_user_threshold' ) );

	$t_user_table = config_get( 'mantis_user_table' );

	# Delete the users who have never logged in and are older than 1 week
	$days_old = 7;
	$days_old = (integer)$days_old;
	$query = "SELECT id
			FROM $t_user_table
			WHERE login_count=0 AND TO_DAYS(".db_now().") - '$days_old' > TO_DAYS(date_created)";
	$result = db_query($query);

	$count = db_num_rows( $result );

	if ( $count > 0 ) {
		helper_ensure_confirmed( lang_get( 'confirm_account_pruning' ),
								 lang_get( 'prune_accounts_button' ) );
	}

	for ($i=0; $i < $count; $i++) {
		$row = db_fetch_array( $result );
		user_delete($row['id']);
	}

	$t_redirect_url = 'manage_user_page.php';
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
