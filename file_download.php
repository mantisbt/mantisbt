<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Add file and redirect to the referring page
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
  $f_id = (integer)$f_id;
	#check_access( DEVELOPER );

	# we handle the case where the file is attached to a bug
	# or attached to a project as a project doc.
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

	# dump file content to the connection.
	if ( DISK == $g_file_upload_method ) {
		readfile( $v_diskfile );
	} else {
		echo $v_content;
	}
?>