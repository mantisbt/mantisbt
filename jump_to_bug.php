<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Update bug data then redirect to the appropriate viewing page
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	$f_id = gpc_get_int( 'f_id' );
	project_access_check( $f_id );
	bug_ensure_exists( $f_id );

	# Determine which view page to redirect back to.
	print_header_redirect_view( $f_id );
?>
