<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# The specified profile is deleted and the user is redirected to
	# account_prof_menu_page.php3
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	# protected account check
	if ( current_user_is_protected() ) {
		trigger_error( ERROR_PROTECTED_ACCOUNT, ERROR );
	}

	$f_id	= gpc_get_int( 'f_id' );

	$c_id = db_prepare_int( $f_id );

	$t_user_id = auth_get_current_user_id();

	$t_user_profile_table = config_get( 'mantis_user_profile_table' );

	# Delete the profile
	$query = "DELETE
			FROM $t_user_profile_table
			WHERE id='$c_id' AND user_id='$t_user_id'";
	$result = db_query( $query );

	if ( $result ) {
		print_header_redirect( 'account_prof_menu_page.php' );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
