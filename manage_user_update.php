<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( 'core_API.php' ) ?>
<?php login_cookie_check() ?>
<?php
	check_access( ADMINISTRATOR );

	if ( isset( $f_protected ) ) {
		$f_protected = 1;
	} else {
		$f_protected = 0;
	}

	if ( isset( $f_enabled ) ) {
		$f_enabled = 1;
	} else {
		$f_enabled = 0;
	}

	$c_username 	= addslashes($f_username);
	$c_email		= addslashes($f_email);
	$c_protected	= (integer)$f_protected;
	$c_enabled		= (integer)$f_enabled;
	$c_id			= (integer)$f_id;
	$c_access_level	= (integer)$f_access_level;

	# update action
	# administrator is not allowed to change access level or enabled
	# this is to prevent screwing your own account
	if ( ON == $f_protected ) {
	    $query = "UPDATE $g_mantis_user_table
	    		SET username='$c_username', email='$c_email',
	    			protected='$c_protected'
	    		WHERE id='$c_id'";
	} else {
	    $query = "UPDATE $g_mantis_user_table
	    		SET username='$c_username', email='$c_email',
	    			access_level='$c_access_level', enabled='$c_enabled',
	    			protected='$c_protected'
	    		WHERE id='$c_id'";
	}

    $result = db_query( $query );
    $t_redirect_url = 'manage_page.php';
?>
<?php print_page_top1() ?>
<?php
	if ( $result ) {
		print_meta_redirect( $t_redirect_url );
	}
?>
<?php print_page_top2() ?>

<p>
<div align="center">
<?php
	if ( ON == $f_protected ) {				# PROTECTED
		PRINT $s_manage_user_protected_msg.'<p>';
	} else if ( $result ) {					# SUCCESS
		PRINT $s_operation_successful.'<p>';
	} else {								# FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $t_redirect_url, $s_proceed );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
