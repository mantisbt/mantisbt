<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	check_access( config_get( 'manage_project_threshold' ) );

	$f_project_id = gpc_get_int( 'project_id' );
	$f_version = gpc_get_string( 'version' );

	$result = 0;
	# check for empty case or duplicate
	if ( !is_blank( $f_version )&&( !version_is_duplicate( $f_project_id, $f_version ) ) ) {
		$result = version_add( $f_project_id, $f_version );
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
	} else if ( version_is_duplicate( $f_project_id, $f_version )) {
		echo $MANTIS_ERROR[ERROR_DUPLICATE_VERSION] . '<br />';
	} else {							# FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
