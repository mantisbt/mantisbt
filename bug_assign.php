<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# This module is based on bug_update.php and provides a quick method
	# for assigning a call to the currently signed on user.
	# Copyright (C) 2001  Steve Davies - steved@ihug.co.nz

	# --------------------------------------------------------
	# $Revision: 1.23 $
	# $Author: jfitzell $
	# $Date: 2002-10-20 23:59:48 $
	#
	# $Id: bug_assign.php,v 1.23 2002-10-20 23:59:48 jfitzell Exp $
	# --------------------------------------------------------

	# Assign bug to user then redirect to viewing page
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	$f_bug_id = gpc_get_int( 'f_bug_id' );

	project_access_check( $f_bug_id );
	check_access( config_get( 'handle_bug_threshold' ) );

	bug_ensure_exists( $f_bug_id );

	bug_assign( $f_bug_id, auth_get_current_user_id() );

	print_header_redirect_view( $f_bug_id );
?>