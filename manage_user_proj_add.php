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
	check_access( config_get( 'manage_project_threshold' ) );

	$f_user_id		= gpc_get_int( 'f_user_id' );
	$f_access_level	= gpc_get_int( 'f_access_level' );
	# check for no projects
	check_varset( $f_project_id, array() );

	# Add a user to project(s)
	$count = count( $f_project_id );
	$result = ( $count == 0 );
	for ($i=0;$i<$count;$i++) {
		$t_project_id = $f_project_id[$i];
		$result = project_add_user( $t_project_id, $f_user_id, $f_access_level );
	}

	$t_redirect_url = 'manage_user_page.php?f_user_id='.$f_user_id;
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
