<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'category_api.php' );
?>
<?php login_cookie_check() ?>
<?php
	check_access( config_get( 'manage_project_threshold' ) );

	$f_project_id		= gpc_get_int( 'project_id' );
	$f_category			= gpc_get_string( 'category' );
	$f_orig_category	= gpc_get_string( 'orig_category' );
	$f_assigned_to		= gpc_get_int( 'assigned_to', 0 );

	$result = 0;
	$query = '';

	# check for duplicate
	if ( !is_duplicate_category( $f_project_id, $f_category, $f_orig_category ) ) {
		$result = category_update( $f_project_id, $f_category, $f_orig_category, $f_assigned_to );
		if ( !$result ) {
			break;
		}

		$c_category			= db_prepare_string( $f_category );
		$c_orig_category	= db_prepare_string( $f_orig_category );
		$c_project_id		= db_prepare_int( $f_project_id );

		$query = "UPDATE $g_mantis_bug_table
				SET category='$c_category'
				WHERE category='$c_orig_category'
				  AND project_id='$c_project_id'";
	   	$result = db_query( $query );
	}

	$t_redirect_url = 'manage_proj_edit_page.php?project_id='.$f_project_id;
?>
<?php print_page_top1() ?>
<?php
	if ( $result ) {
		print_meta_redirect( $t_redirect_url );
	}
?>
<?php print_page_top2() ?>

<br />
<div align="center">
<?php
	if ( $result ) {					# SUCCESS
		echo lang_get( 'operation_successful' ).'<br />';
	} else if ( is_duplicate_category( $f_project_id, $f_category, $f_orig_category )) {
		echo $MANTIS_ERROR[ERROR_DUPLICATE_CATEGORY].'<br />';
	} else {							# FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
