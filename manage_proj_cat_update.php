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
	check_access( MANAGER );

	if ( empty( $f_category ) ) {
		print_mantis_error( ERROR_EMPTY_FIELD );
	}

	$f_category = urldecode( $f_category );
	$f_orig_category = urldecode( stripslashes( $f_orig_category ) );

	$result = 0;
	$query = '';
	if ( strcmp ( $f_category, $f_orig_category ) != 0 ) {
		# check for duplicate
		if ( !is_duplicate_category( $f_project_id, $f_category ) ) {
			$result = category_update( $f_project_id, $f_category, $f_orig_category );
			if ( !$result ) {
				break;
			}

			$c_category			= addslashes($f_category);
			$c_orig_category	= addslashes($f_orig_category);

			$query = "UPDATE $g_mantis_bug_table
					SET category='$c_category'
					WHERE category='$c_orig_category'";
		   	$result = db_query( $query );
		}
	} else {
	  $result = true;
	}

	$t_redirect_url = $g_manage_project_edit_page.'?f_project_id='.$f_project_id;
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
	} else if ( is_duplicate_category( $f_project_id, $f_category )) {
		PRINT $MANTIS_ERROR[ERROR_DUPLICATE_CATEGORY].'<p>';
	} else {							# FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $t_redirect_url, $s_proceed );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>