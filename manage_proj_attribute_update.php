<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	check_access( MANAGER );

	if ( empty( $f_attribute ) ) {
		print_mantis_error( ERROR_EMPTY_FIELD );
	}

	check_varset( $f_assigned_to, '0' );

	$f_attribute = urldecode( $f_attribute );
	$f_orig_attribute = urldecode( stripslashes( $f_orig_attribute ) );

	$result = 0;
	$result2 = 0;
	$query = '';
	$t_status_flag = 0 ; #for colors

	check_varset( $f_color, ''  ) ;

	# check for duplicate
	if ( !attribute_is_duplicate( $f_parameter, $f_project_id, $f_attribute, $f_orig_attribute ) ) {
		$result = attribute_update( $f_parameter, $f_project_id, $f_attribute, $f_orig_attribute, $f_color);
		if ( !$result ) {
			break;
		}
	}

	$t_redirect_url = 'manage_proj_menu_page.php';
	//$t_redirect_url = 'manage_proj_edit_page.php?f_project_id='.$f_project_id;
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
	} else if ( attribute_is_duplicate( $f_parameter, $f_project_id, $f_attribute, $f_orig_attribute )) {
		echo $MANTIS_ERROR[ERROR_DUPLICATE_CATEGORY].'<br />';
	} else {							# FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
