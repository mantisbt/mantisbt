<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# This file adds a new profile and redirects to account_proj_menu_page.php3
?>
<?php include( 'core_API.php' ) ?>
<?php login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( REPORTER );

	# get protected state
	$t_protected = get_current_user_field( 'protected' );

	# protected account check
	if ( ON == $t_protected ) {
		print_mantis_error( ERROR_PROTECTED_ACCOUNT );
	}

	# validating input
	$check_failed = false;
	if ( ( empty( $f_platform ) ) ||
		 ( empty( $f_os ) ) ||
		 ( empty( $f_os_build ) ) ||
		 ( empty( $f_description ) ) ) {
		$check_failed = true;
	}

	$result = 0;
	if ( $check_failed ) {
		print_mantis_error( ERROR_EMPTY_FIELD );
	} else {
		# required fields ok, proceeding
		# " character poses problem when editting so let's just convert them
		$c_platform		= string_prepare_text( $f_platform );
		$c_os			= string_prepare_text( $f_os );
		$c_os_build		= string_prepare_text( $f_os_build );
		$c_description	= string_prepare_textarea( $f_description );

		# get user id
		$c_user_id = (integer)get_current_user_field( 'id' );

		# Add profile
		$query = "INSERT
				INTO $g_mantis_user_profile_table
	    		( id, user_id, platform, os, os_build, description )
				VALUES
				( null, '$c_user_id', '$c_platform', '$c_os', '$c_os_build', '$c_description' )";
	    $result = db_query( $query );
	}

    $t_redirect_url = $g_account_profile_menu_page;
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>