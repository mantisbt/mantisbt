<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Deletes the bug and re-directs to view_all_bug_page.php 
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	project_access_check( $f_id );
	check_access( $g_allow_bug_delete_access_level );
	bug_ensure_exists( $f_id );

	if ( bug_delete( $f_id, $f_bug_text_id ) ) {
		print_header_redirect( 'view_all_bug_page.php' );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
