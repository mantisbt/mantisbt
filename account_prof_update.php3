<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	# This page updates the users profile information then redirects to
	# account_prof_menu_page.php3
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	$f_user_id = get_current_user_field( "id" );

	# " character poses problem when editting so let's just convert them
	$f_platform		= string_prepare_text( $f_platform );
	$f_os			= string_prepare_text( $f_os );
	$f_os_build		= string_prepare_text( $f_os_build );
	$f_description	= string_prepare_textarea( $f_description );

	# Add item
	$query = "UPDATE $g_mantis_user_profile_table
    		SET platform='$f_platform', os='$f_os',
    			os_build='$f_os_build', description='$f_description'
    		WHERE id='$f_id' AND user_id='$f_user_id'";
    $result = db_query( $query );

    $t_redirect_url = $g_account_profile_menu_page;
?>
<?php print_page_top1() ?>
<?
	if ( $result ) {
		print_meta_redirect( $t_redirect_url );
	}
?>
<?php print_page_top2() ?>

<?php print_proceed( $result, $query, $t_redirect_url ) ?>

<?php print_page_bot1( __FILE__ ) ?>