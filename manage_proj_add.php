<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( 'core_API.php' ) ?>
<?php login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( ADMINISTRATOR );

	$c_name 		= string_prepare_textarea( $f_name );
	$c_description 	= string_prepare_textarea( $f_description );
	$c_view_state	= (integer)$f_view_state;
	$c_status		= (integer)$f_status;
	$c_file_path	= addslashes($f_file_path);

	$result = 0;
	$duplicate = is_duplicate_project( $f_name );
	if ( !empty( $f_name ) && !$duplicate ) {
		# Add item
		$query = "INSERT
				INTO $g_mantis_project_table
				( id, name, status, enabled, view_state, file_path, description )
				VALUES
				( null, '$c_name', '$c_status', '1', '$c_view_state', '$c_file_path', '$c_description' )";
	    $result = db_query( $query );
	}

	$t_redirect_url = 'manage_proj_menu_page.php';
?>
<?php print_page_top1() ?>
<?php
	if ( $result ) {
		print_meta_redirect( $t_redirect_url );
	}
?>
<?php print_page_top2() ?>

<p>
<div align="center">
<?php
	if ( $result ) {					# SUCCESS
		PRINT $s_operation_successful.'<p>';
	} else if ( $duplicate ) {			# DUPLICATE
		PRINT $MANTIS_ERROR[ERROR_DUPLICATE_PROJECT].'<p>';
	} else {							# FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $t_redirect_url, $s_proceed );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>