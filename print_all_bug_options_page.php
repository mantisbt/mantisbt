<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# This page allows the user to set the fields of the bugs he wants to print
	# Update is POSTed to acount_prefs_update.php3
	# Reset is POSTed to acount_prefs_reset.php3
?>
<?php include( 'core_API.php' ) ?>
<?php require( 'print_all_bug_options_inc.php' ) ?>
<?php login_cookie_check() ?>
<?php
	print_page_top1();
	print_page_top2();
	edit_printing_prefs();
	print_page_bot1( __FILE__ );
?>
