<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( MANAGER );

	$f_title 		= string_prepare_text( $f_title );
	$f_description 	= string_prepare_textarea( $f_description );

	$query = "UPDATE $g_mantis_project_file_table
			SET title='$f_title', description='$f_description'
			WHERE id='$f_id'";
	$result = db_query( $query );

	$t_redirect_url = $g_proj_doc_page;
?>
<?php print_page_top1() ?>
<?php
	if ( $result ) {
		print_meta_redirect( $t_redirect_url );
	}
?>
<?php print_page_top2() ?>

<?php print_proceed( $result, $query, $t_redirect_url ) ?>

<?php print_page_bot1( __FILE__ ) ?>