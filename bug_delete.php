<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Delete the bug, bugtext, bugnote, and bugtexts selected
	# Redirects to view_all_bug_page.php3
?>
<?php include( 'core_API.php' ) ?>
<?php login_cookie_check() ?>
<?php
	project_access_check( $f_id );
	check_access( $g_handle_bug_threshold );
	check_bug_exists( $f_id );

	delete_bug($f_id, $f_bug_text_id);

	$t_redirect_url = 'view_all_bug_page.php';
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
