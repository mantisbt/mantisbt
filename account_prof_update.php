<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# This page updates the users profile information then redirects to
	# account_prof_menu_page.php3
?>
<?php include( 'core_API.php' ) ?>
<?php login_cookie_check() ?>
<?php
	# get protected state
	$t_protected = get_current_user_field( 'protected' );

	# protected account check
	if ( ON == $t_protected ) {
		print_mantis_error( ERROR_PROTECTED_ACCOUNT );
	}

	$c_user_id	= (integer)get_current_user_field( 'id' );
	$c_id		= (integer)$f_id;

	# " character poses problem when editting so let's just convert them
	$c_platform		= string_prepare_text( $f_platform );
	$c_os			= string_prepare_text( $f_os );
	$c_os_build		= string_prepare_text( $f_os_build );
	$c_description	= string_prepare_textarea( $f_description );

	# Add item
	$query = "UPDATE $g_mantis_user_profile_table
    		SET platform='$c_platform', os='$c_os',
    			os_build='$c_os_build', description='$c_description'
    		WHERE id='$c_id' AND user_id='$c_user_id'";
    $result = db_query( $query );

    $t_redirect_url = 'account_prof_menu_page.php';
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
