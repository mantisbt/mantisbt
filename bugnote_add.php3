<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_mysql_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	### get user information
    $query = "SELECT id
    		FROM $g_mantis_user_table
			WHERE cookie_string='$g_string_cookie_val'";
    $result = mysql_query($query);
    if ( $result ) {
		$u_id = mysql_result( $result, "id" );
	}

	$f_bugnote_text = string_safe( $f_bugnote_text );
	### insert bugnote text
	$query = "INSERT
			INTO $g_mantis_bugnote_text_table
			( id, note )
			VALUES
			( null, '$f_bugnote_text' )";
	$result = mysql_query( $query );

	### retrieve bugnote text id number
	$query = "SELECT id
			FROM $g_mantis_bugnote_text_table
			ORDER BY id DESC
			LIMIT 1";
	$result = mysql_query( $query );
	if ( $result ) {
		$t_bugnote_text_id = mysql_result( $result, "id" );
	}

	### insert bugnote info
	$query = "INSERT
			INTO $g_mantis_bugnote_table
			( id, bug_id, reporter_id, bugnote_text_id, date_submitted, last_modified )
			VALUES
			( null, '$f_bug_id', '$u_id','$t_bugnote_text_id',NOW(), NOW() )";
	$result = mysql_query( $query );

	### set last updated
	$query = "UPDATE $g_mantis_bug_table
    		SET last_updated=NOW()
    		WHERE id='$f_bug_id'";
   	$result = mysql_query($query);
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<?
	if ( $result ) {
		print_meta_redirect( "$g_bug_view_page?f_id=$f_bug_id", $g_wait_time );
	}
?>
<? include( $g_meta_include_file ) ?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>

<p>
<? print_menu( $g_menu_include_file ) ?>

<p>
<div align=center>
<?
	if ( $result ) {
		PRINT "Bugnote added...<p>";
	}
	### OK!!!
	else {
		PRINT "ERROR DETECTED: Report this sql statement to <a href=\"<? echo $g_administrator_email ?>\">administrator</a><p>";
		PRINT $query;
	}
?>
<p>
<a href="<? echo $g_bug_view_page ?>?f_id=<? echo $f_bug_id ?>">Click here to proceed</a>
</div>

<? print_footer() ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>