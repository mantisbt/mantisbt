<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php die('Not in use.'); ?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	project_access_check( $f_id );
	check_access( REPORTER );
	$c_id	= (integer)$f_id;
	$c_vote	= (integer)$f_vote;

	# increase vote count and update in table
	$f_vote++;
    $query = "UPDATE $g_mantis_bug_table
    		SET votes=$c_vote
    		WHERE id='$c_id'";
   	$result = db_query($query);

	$t_redirect_url = string_get_bug_view_url( $f_id );
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
