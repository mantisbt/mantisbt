<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

    # This module is based on bug_update.php3 and provides a quick method
    # for assigning a call to the currently signed on user.
    # Copyright (C) 2001  Steve Davies - steved@ihug.co.nz

?>
<?
	### Assign bug to user then redirect to viewing page
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( DEVELOPER );
	check_bug_exists( $f_id );

    $t_ass_val = ASSIGNED;

    ### get user id
    $t_handler_id = get_current_user_field( "id" );
    $query = "UPDATE $g_mantis_bug_table
            SET handler_id='$t_handler_id', status='$t_ass_val'
			WHERE id='$f_id'";
    $result = db_query($query);

	### send assigned to email
	email_assign( $f_id );

	# Determine which view page to redirect back to.
	$t_redirect_url = get_view_redirect_url( $f_id );
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<?
	if ( $result ) {
		print_meta_redirect( $t_redirect_url, $g_wait_time );
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
	if ( $result ) {					### SUCCESS
        PRINT "$s_bug_assign_msg<p>";
	} else {							### FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $t_redirect_url, $s_proceed );
?>
</div>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>