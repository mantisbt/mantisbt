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

	if ( $f_password != $f_password_verify ) {
		echo "ERROR: passwords do not match";
		exit;
	}

	if ( !isset( $f_protected ) ) {
		$f_protected = 0;
	} else {
		$f_protected = 1;
	}

	if ( !isset( $f_enabled ) ) {
		$f_enabled = 0;
	} else {
		$f_enabled = 1;
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
    $result = db_query( $query );

   	### Use this for MS SQL: SELECT @@IDENTITY AS 'id'
	$t_user_id = db_insert_id();

	### Create preferences
    $query = "INSERT
    		INTO $g_mantis_user_pref_table
    		(id, user_id, advanced_report, advanced_view)
    		VALUES
    		(null, '$t_user_id',
    		'$g_default_advanced_report', '$g_default_advanced_view')";
    $result = db_query($query);

	### Add profile
	/*$query = "INSERT
			INTO $g_mantis_user_profile_table
    		( id, user_id, platform, os, os_build, description, default_profile )
			VALUES
			( null, '$f_user_id', '$f_platform', '$f_os', '$f_os_build', '$f_description', '' )";
    $result = db_query( $query );*/
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
	if ( $result ) {				### SUCCESS
		$f_access_level = get_enum_element( $g_access_levels_enum_string, $f_access_level );
		PRINT "$s_created_user_part1 <b>$f_username</b> $s_created_user_part2 <b>$f_access_level</b><p>";
	} else {						### FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $g_manage_page, $s_proceed );
?>
</div>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>