<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( 'core_API.php' ) ?>
<?php login_cookie_check() ?>
<?php
	check_access( MANAGER );

	if ( empty( $f_category ) ) {
		print_mantis_error( ERROR_EMPTY_FIELD );
	}
	
	check_varset( $f_assigned_to, '0' );

	$f_category = urldecode( $f_category );
	$f_orig_category = urldecode( stripslashes( $f_orig_category ) );

	$result = 0;
	$query = '';

	# check for duplicate
	if ( !is_duplicate_category( $f_project_id, $f_category, $f_orig_category ) ) {
		$result = category_update( $f_project_id, $f_category, $f_orig_category, $f_assigned_to );
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

	$t_redirect_url = 'manage_proj_edit_page.php?f_project_id='.$f_project_id;
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
	} else if ( is_duplicate_category( $f_project_id, $f_category, $f_orig_category )) {
		PRINT $MANTIS_ERROR[ERROR_DUPLICATE_CATEGORY].'<p>';
	} else {							# FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $t_redirect_url, $s_proceed );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
