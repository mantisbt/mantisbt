<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( MANAGER );

	$result = 0;
	# check for empty case or duplicate
	if ( !empty( $f_version )&&( !is_duplicate_version( $f_version, $f_project_id ) ) ) {
		# insert version
		$query = "INSERT
				INTO $g_mantis_project_version_table
				( project_id, version, date_order )
				VALUES
				( '$f_project_id', '$f_version', NOW() )";
		$result = db_query( $query );
	}

	$t_redirect_url = $g_manage_project_edit_page."?f_project_id=".$f_project_id;
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
		PRINT "$s_operation_successful<p>";
	} else if ( is_duplicate_version( $f_version, $f_project_id )) {
		PRINT "$s_duplicate_version";
	} else {							# FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $t_redirect_url, $s_proceed );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>