<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	check_access( ADMINISTRATOR );

	# Check to make sure that the access is legal
	# NOTE: enabling this could be a bad idea
	if ( ON == $g_show_source ) {
		check_access( ADMINISTRATOR );
	} else {
		# need to replace with access error page
		print_header_redirect( 'logout_page.php' );
		exit;
	}
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<p />
<div align="left">
<?php
	PRINT "$s_show_source_for_msg: $f_url<p />";

	# Print source
	show_source( $f_url );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
