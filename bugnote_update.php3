<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	### Update bug data then redirect to the appropriate viewing page
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( REPORTER );

	$f_bugnote_text = $f_bugnote_text."\n\n";
	$f_bugnote_text = $f_bugnote_text.$s_edited_on.date( $g_normal_date_format );
	$f_bugnote_text = string_prepare_textarea( $f_bugnote_text );
    $query = "UPDATE $g_mantis_bugnote_text_table
    		SET note='$f_bugnote_text'
    		WHERE id='$f_bugnote_text_id'";
   	$result = db_query( $query );

	switch ( $g_show_view ) {
		case 0:	if ( get_current_user_pref_field( "advanced_view" )==1 ) {
					$t_redirect_url = $g_view_bug_page;
				} else {
					$t_redirect_url = $g_view_bug_advanced_page;
				}
				break;
		case 1:	$t_redirect_url = $g_view_bug_page;
				break;
		case 2:	$t_redirect_url = $g_view_bug_advanced_page;
				break;
	}
	$t_redirect_url = $t_redirect_url."?f_id=".$f_id;

	if (( $g_quick_proceed == 1 )&&( $result )) {
		print_header_redirect( $t_redirect_url );
	}
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
		PRINT "$s_bugnote_updated_msg<p>";
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