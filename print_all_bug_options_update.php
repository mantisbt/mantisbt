<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Updates printing prefs then redirect to print_all_bug_page_page.php
?>
<?php require_once( 'core.php' ) ?>
<?php require( 'print_all_bug_options_inc.php' ) ?>

<?php login_cookie_check() ?>
<?php
	$f_user_id		= gpc_get_int( 'user_id' );
	$f_redirect_url	= gpc_get_int( 'redirect_url' );

	# the check for the protected state is already done in the form, there is
	# no need to duplicate it here.

	# get the fields list
	$t_field_name_arr = get_field_names();
	$field_name_count = count($t_field_name_arr);

	# check the checkboxes
	for ($i=0 ; $i <$field_name_count ; $i++) {
		$t_name='print_'.strtolower(str_replace(' ','_',$t_field_name_arr[$i]));
		if ( !isset( $$t_name ) || ( 1 == ($$t_name) ) ) {
			$t_prefs_arr[$i] = 0;
		}
		else {
			$t_prefs_arr[$i] = 1;
		}
	}

	# get user id
	$t_user_id = $f_user_id;

	$c_export = implode('',$t_prefs_arr);

	# update preferences
	$query = "UPDATE $g_mantis_user_print_pref_table
			SET print_pref='$c_export'
			WHERE user_id='$t_user_id'";

	$result = db_query( $query );

	print_page_top1();
	print_meta_redirect( $f_redirect_url );
	print_page_top2();
	PRINT '<br /><div align="center">';

	if ( $result ) {
		PRINT $s_operation_successful;
	} else {
		PRINT $MANTIS_ERROR[ERROR_GENERIC];
	}

	PRINT '<br />';
	print_bracket_link( $f_redirect_url, $s_proceed );
	PRINT '<br /></div>';
	print_page_bot1( __FILE__ );
?>
