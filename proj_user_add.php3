<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( MANAGER );

	$query = "SELECT id
			FROM $g_mantis_user_table
			WHERE username='$f_username'";
	$result = db_query( $query );
	if ( db_num_rows( $result ) > 0 ) {
		$t_user_id = db_result( $result, 0, 0 );

		# Add user to project
		$query = "INSERT
				INTO $g_mantis_project_user_list_table
				(project_id, user_id, access_level)
				VALUES
				('$g_project_cookie_val', '$t_user_id', '$f_access_level' )";
		$result = db_query($query);
	} else {
		$result = 0;
	}

	$t_redirect_url = $g_proj_user_menu_page;
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