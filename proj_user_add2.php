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
	check_access( MANAGER );
	$f_project_id		= gpc_get_int( 'project_id' );
	$f_user_id			= gpc_get_int( 'user_id' );
	$f_access_level		= gpc_get_int( 'access_level' );

	$c_user_id		= (integer)$f_user_id;
	$c_access_level	= (integer)$f_access_level;

	# Add user to project(s)
	$result = 0;
	$count = count( $f_project_id );
	for ($i=0;$i<$count;$i++) {
		$t_project_id = (integer)$f_project_id[$i];
		$query = "INSERT
				INTO $g_mantis_project_user_list_table
				(project_id, user_id, access_level)
				VALUES
				('$t_project_id', '$c_user_id', '$c_access_level')";
		$result = db_query( $query );
	}

	$t_redirect_url = 'manage_user_page.php?user_id='.$f_user_id;
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
