<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	check_access( ADMINISTRATOR );
?>
<?php 
	print_page_top1();
	print_page_top2();

	print_manage_menu( 'documentation_page.php' );

	print_manage_doc_menu( 'documentation_page.php' );

	echo '<br />';

	phpinfo();
?>
<?php print_page_bot1( __FILE__ ) ?>
