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
	
	require_once( $t_core_path . 'version_api.php' );
?>
<?php login_cookie_check() ?>
<?php
	check_access( config_get( 'manage_project_threshold' ) );

	$f_project_id	= gpc_get_int( 'project_id' );
	$f_version		= gpc_get_string( 'version' );
	$f_date_order	= gpc_get_string( 'date_order' );
	$f_orig_version	= gpc_get_string( 'orig_version' );

	$result = 0;
	$query = '';

	# check for duplicate (don't care for date_order at this stage, because no two versions should
	# have the same name even if they have different time stamps.
	if ( !version_is_duplicate( $f_project_id, $f_version, '0', $f_orig_version ) ) {
		$result = version_update( $f_project_id, $f_version, $f_date_order, $f_orig_version );
		if ( !$result ) {
			break;
		}

		$c_version		= db_prepare_string( $f_version );
		$c_orig_version	= db_prepare_string( $f_orig_version );

		$query = "UPDATE $g_mantis_bug_table
				SET version='$f_version'
				WHERE version='$f_orig_version'";
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
	if ( $result ) {				# SUCCESS
		echo lang_get( 'operation_successful' ).'<br />';
	} else if ( version_is_duplicate( $f_project_id, $f_version, '0', $f_orig_version )) {
		echo $MANTIS_ERROR[ERROR_DUPLICATE_VERSION].'<br />';
	} else {						# FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
