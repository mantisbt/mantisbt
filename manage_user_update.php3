<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

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

	### update action
	### administrator is not allowed to change access level or enabled
	### this is to prevent screwing your own account
	if ( $f_protected==1 ) {
	    $query = "UPDATE $g_mantis_user_table
	    		SET username='$f_username', email='$f_email',
	    			protected='$f_protected'
	    		WHERE id='$f_id'";
	} else {
	    $query = "UPDATE $g_mantis_user_table
	    		SET username='$f_username', email='$f_email',
	    			access_level='$f_access_level', enabled='$f_enabled',
	    			protected='$f_protected'
	    		WHERE id='$f_id'";
	}

    $result = db_query( $query );
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<?
	if ( $result ) {
		print_meta_redirect( $g_manage_page, $g_wait_time );
	}
?>
<? include( $g_meta_include_file ) ?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>
<? print_top_page( $g_top_include_page ) ?>
<? print_menu( $g_menu_include_file ) ?>

<p>
<div align="center">
<?
	if ( $f_protected==1 ) {				### PROTECTED
		PRINT "$s_manage_user_protected_msg<p>";
	} else if ( $result ) {					### SUCCESS
		PRINT "$s_manage_user_updated_msg<p>";
	} else {								### FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $g_manage_page, $s_proceed );
?>
</div>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>