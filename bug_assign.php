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
	# $Revision: 1.19 $
	# $Author: prescience $
	# $Date: 2002-08-19 02:04:46 $
	#
	# $Id: bug_assign.php,v 1.19 2002-08-19 02:04:46 prescience Exp $
	# --------------------------------------------------------

	# Assign bug to user then redirect to viewing page
?>
<?php include( 'core_API.php' ) ?>
<?php login_cookie_check() ?>
<?php
	project_access_check( $f_id );
	check_access( $g_handle_bug_threshold );

	$result = bug_assign( $f_id );

	# Determine which view page to redirect back to.
	$t_redirect_url = get_view_redirect_url( $f_id, 1 );
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>