<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: jump_to_bug.php,v 1.18 2003-02-15 10:25:16 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php
	# Redirect to the appropriate viewing page for the bug
?>
<?php
	require_once( 'core.php' );
?>
<?php auth_ensure_user_authenticated() ?>
<?php
	# No need for access checking.  We're just redirecting to another page.

	# Determine which view page to redirect back to.
	print_header_redirect_view( $f_bug_id );
?>
