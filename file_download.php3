<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	### Add file and redirect to the referring page
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	#check_access( DEVELOPER );

	switch ( $f_type ) {
		case "bug":	$query = "SELECT *
							FROM $g_mantis_bug_file_table
							WHERE id='$f_id'";
					break;
		case "doc":	$query = "SELECT *
							FROM $g_mantis_project_file_table
							WHERE id='$f_id'";
					break;
	}

	$result = db_query( $query );
	$row = db_fetch_array( $result );
	extract( $row, EXTR_PREFIX_ALL, "v" );

	header( "Content-type: $v_file_type" );
	header( "Content-Length: ".$v_filesize );
	header( "Content-Disposition: filename=$v_filename" );
	header( "Content-Description: Download Data" );

	echo $v_content;
?>