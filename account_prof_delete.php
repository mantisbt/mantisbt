<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# The specified profile is deleted and the user is redirected to
	# account_prof_menu_page.php3
?>
<?php include( 'core_API.php' ) ?>
<?php login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	# get protected state
	$t_protected = get_current_user_field( 'protected' );

	# protected account check
	if ( ON == $t_protected ) {
		print_mantis_error( ERROR_PROTECTED_ACCOUNT );
	}

	$c_user_id = (integer)get_current_user_field( 'id' );
	$c_id = (integer)$f_id;

	# Delete the profile
	$query = "DELETE
			FROM $g_mantis_user_profile_table
    		WHERE id='$c_id' AND user_id='$c_user_id'";
    $result = db_query( $query );

    $t_redirect_url = $g_account_profile_menu_page;
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>