<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_mysql_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	if ( !access_level_check_greater_or_equal( "administrator" ) ) {
		### need to replace with access error page
		header( "Location: $g_logout_page" );
		exit;
	}

	### create the almost unique string for each user then insert into the table
	$t_cookie_string = create_cookie_string( $f_email );
	$t_password = crypt( $f_password );
    $query = "INSERT
    		INTO $g_mantis_user_table
    		( id, username, email, password, date_created, last_visit,
    		access_level, enabled, protected, cookie_string )
			VALUES
			( null, '$f_username', '$f_email', '$t_password', NOW(), NOW(),
			'$f_access_level', '$f_enabled', '$f_protected', '$t_cookie_string')";
    $result = mysql_query( $query );
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

<p>
<? print_menu( $g_menu_include_file ) ?>

<p>
<div align=center>
<?
	if ( $result ) {
		PRINT "$s_created_user_part1 <b>$f_username</b> $s_created_user_part2 <b>$f_access_level</b><p>";
	}
	else {
		PRINT "ERROR DETECTED: Report this sql statement to <a href=\"<? echo $g_administrator_email ?>\">administrator</a><p>";
		echo $query;
	}
?>
<p>
<a href="<? echo $g_manage_page ?>"><? $s_proceed ?></a>
</div>

<? print_footer() ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>