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
	check_access( ADMINISTRATOR );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php print_manage_doc_menu( $g_documentation_page ) ?>

<?php phpinfo() ?>

<?php print_page_bot1( __FILE__ ) ?>