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
	check_access( ADMINISTRATOR );

	# Check to make sure that the access is legal
	# NOTE: enabling this could be a bad idea
	if ( ON == $g_show_source ) {
		check_access( ADMINISTRATOR );
	} else {
		# need to replace with access error page
		print_header_redirect( $g_logout_page );
		exit;
	}
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<p>
<div align="left">
<?
	PRINT "$s_show_source_for_msg: $f_url<p>";

	# Print source
	show_source( $f_url );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>