<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Update bug data then redirect to the appropriate viewing page
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	project_access_check( $f_id );
	check_access( REPORTER );

	$f_bugnote_text		= $f_bugnote_text."\n\n";
	$f_bugnote_text		= $f_bugnote_text.$s_edited_on.date( $g_normal_date_format );
	$c_bugnote_text		= string_prepare_textarea( $f_bugnote_text );
	$c_bugnote_text_id	= (integer)$f_bugnote_text_id;

    $query = "UPDATE $g_mantis_bugnote_text_table
    		SET note='$c_bugnote_text'
    		WHERE id='$c_bugnote_text_id'";
   	$result = db_query( $query );

	# updated the last_updated date
	bugnote_date_update( $f_bugnote_id );

	# Determine which view page to redirect back to.
	$t_redirect_url = get_bug_link_plain( $f_id );
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>