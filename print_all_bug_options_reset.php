<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Reset prefs to defaults then redirect to account_prefs_page.php3
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'current_user_api.php' );
?>
<?php require( 'print_all_bug_options_inc.php' ) ?>
<?php login_cookie_check() ?>
<?php
	# protected account check
	current_user_ensure_unprotected();

	# get user id
	$t_user_id = current_user_get_field( 'id' );

	# get the fields list
	$t_field_name_arr = get_field_names();
	$field_name_count = count($t_field_name_arr);
	
	# create a default array, same size than $t_field_name
	for ($i=0 ; $i<$field_name_count ; $i++) { 
		$t_default_arr[$i] = 0 ;
	}
	$t_default = implode('',$t_default_arr) ;

	# reset to defaults
	$query = "UPDATE $g_mantis_user_print_pref_table
			SET print_pref='$t_default'
			WHERE user_id='$t_user_id'";

	$result = db_query( $query );

	$t_redirect_url = 'print_all_bug_options_page.php';
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
