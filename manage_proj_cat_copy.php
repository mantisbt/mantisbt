<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( MANAGER );

	$result = category_get_all( $f_project_id );
	$category_count = db_num_rows( $result );
	for ($i=0;$i<$category_count;$i++) {
		$row = db_fetch_array( $result );
		$t_category = $row["category"];
		$t_category = addslashes( $t_category );

		if ( !is_duplicate_category( $t_category, $f_new_project_id ) ) {
			category_add( $f_new_project_id, $t_category );
		}
	}

	$t_redirect_url = $g_manage_project_edit_page."?f_project_id=".$f_project_id;

	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>