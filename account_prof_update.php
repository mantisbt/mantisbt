<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# This page updates the users profile information then redirects to
	# account_prof_menu_page.php3
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	# protected account check
	if ( current_user_is_protected() ) {
		trigger_error( ERROR_PROTECTED_ACCOUNT, ERROR );
	}

	$f_id			= gpc_get_int( 'f_id' );
	$f_platform		= gpc_get_string( 'f_platform' );
	$f_os			= gpc_get_string( 'f_os' );
	$f_os_build		= gpc_get_string( 'f_os_build' );
	$f_description	= gpc_get_string( 'f_description' );

	$c_id			= db_prepare_int( $f_id );
	$c_platform		= db_prepare_string( $f_platform );
	$c_os			= db_prepare_string( $f_os );
	$c_os_build		= db_prepare_string( $f_os_build );
	$c_description	= db_prepare_string( $f_description );

	$t_user_id	= auth_get_current_user_id();

	$t_user_profile_table = config_get( 'mantis_user_profile_table' );

	# Add item
	$query = "UPDATE $t_user_profile_table
    		SET platform='$c_platform', os='$c_os',
    			os_build='$c_os_build', description='$c_description'
    		WHERE id='$c_id' AND user_id='$t_user_id'";
    $result = db_query( $query );

	if ( $result ) {
		print_header_redirect( 'account_prof_menu_page.php' );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
