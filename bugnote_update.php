<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
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

	$f_bugnote_text = $f_bugnote_text."\n\n";
	$f_bugnote_text = $f_bugnote_text.$s_edited_on.date( $g_normal_date_format );
	$f_bugnote_text = string_prepare_textarea( $f_bugnote_text );
    $query = "UPDATE $g_mantis_bugnote_text_table
    		SET note='$f_bugnote_text'
    		WHERE id='$f_bugnote_text_id'";
   	$result = db_query( $query );

	# updated the last_updated date
	bugnote_date_update( $f_bugnote_id );

	# Determine which view page to redirect back to.
	$t_redirect_url = get_view_redirect_url( $f_id );
	if ( ( ON == $g_quick_proceed )&&( $result ) ) {
		print_header_redirect( $t_redirect_url );
	}
?>
<?php print_page_top1() ?>
<?php
	if ( $result ) {
		print_meta_redirect( $t_redirect_url );
	}
?>
<?php print_page_top2() ?>

<?php print_proceed( $result, $query, $t_redirect_url ) ?>

<?php print_page_bot1( __FILE__ ) ?>