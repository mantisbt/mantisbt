<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_report.php,v 1.5 2002-12-04 08:05:45 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php
	# This page stores the reported bug
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	# this page is invalid for the 'All Project' selection
	if ( 0 == helper_get_current_project() ) {
		print_header_redirect( 'login_select_proj_page.php?ref=' . string_get_bug_report_url() );
	}

	check_access( REPORTER );

	$f_build				= gpc_get_string( 'f_build', '' );
	$f_platform				= gpc_get_string( 'f_platform', '' );
	$f_os					= gpc_get_string( 'f_os', '' );
	$f_os_build				= gpc_get_string( 'f_os_build', '' );
	$f_product_version		= gpc_get_string( 'f_product_version', '' );
	$f_profile_id			= gpc_get_int( 'f_profile_id', 0 );
	$f_handler_id			= gpc_get_int( 'f_handler_id', 0 );
	$f_view_state			= gpc_get_int( 'f_view_state', 0 );

	$f_category				= gpc_get_string( 'f_category', '' );
	$f_reproducibility		= gpc_get_int( 'f_reproducibility' );
	$f_severity				= gpc_get_int( 'f_severity' );
	$f_priority				= gpc_get_int( 'f_priority', NORMAL );
	$f_summary				= gpc_get_string( 'f_summary' );
	$f_description			= gpc_get_string( 'f_description' );
	$f_steps_to_reproduce	= gpc_get_string( 'f_steps_to_reproduce', '' );
	$f_additional_info		= gpc_get_string( 'f_additional_info', '' );

	$f_file					= gpc_get_file( 'f_file' );
	$f_report_stay			= gpc_get_bool( 'f_report_stay' );

	$t_reporter_id		= auth_get_current_user_id();
	$t_project_id		= helper_get_current_project();
	$t_upload_method	= config_get( 'file_upload_method' );

	if ( 0 != $f_file['size'] &&
		 ( DISK == $t_upload_method || FTP == $t_upload_method ) &&
		 is_uploaded_file( $f_file ) ) {
		$t_file_path = project_get_field( $t_project_id, 'file_path' );

		if ( !file_exists( $t_file_path ) ) {
			trigger_error( ERROR_NO_DIRECTORY, ERROR );
		}
	}

	# if a profile was selected then let's use that information
	if ( 0 != $f_profile_id ) {
		$row = user_get_profile_row( $t_reporter_id, $f_profile_id );
		extract( $row, EXTR_PREFIX_ALL, 'v' );

		if ( '' == $f_platform ) {
			$f_platform = $v_platform;
		}
		if ( '' == $f_os ) {
			$f_os = $v_os;
		}
		if ( '' == $f_os_build ) {
			$f_os_build = $v_os_build;
		}
	}

	$t_bug_id = bug_create( $t_project_id,
					$t_reporter_id, $f_handler_id,
					$f_priority,
					$f_severity, $f_reproducibility,
					$f_category,
					$f_os, $f_os_build,
					$f_platform, $f_product_version,
					$f_build,
					$f_profile_id, $f_summary, $f_view_state,
					$f_description, $f_steps_to_reproduce, $f_additional_info );

	# File Uploaded
	if ( is_uploaded_file( $f_file['tmp_name'] ) && 0 != $f_file['size'] ) {
		file_add( $t_bug_id, $f_file['tmp_name'], $f_file['name'], $f_file['type'] );
	}

if( ON == config_get( 'use_experimental_custom_fields' ) ) {
	$t_related_custom_field_ids = custom_field_get_ids( helper_get_current_project() );
	foreach( $t_related_custom_field_ids as $id ) {
		$t_def = custom_field_get_definition($id);
		if( !custom_field_set_value( $id, $t_bug_id, gpc_get_string( "f_custom_field_$id", $t_def['default_value'] ) ) ) {
			trigger_error( ERROR_CUSTOM_FIELD_WRONG_VALUE, ERROR );
		}
	}
} // ON = config_get( 'use_experimental_custom_fields' )

	print_page_top1();

	if ( ! $f_report_stay ) {
		print_meta_redirect( 'view_all_bug_page.php' );
	}

	print_page_top2();
?>
<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ) . '<br />';

	if ( $f_report_stay ) {
?>
			<form method="post" action="<?php echo string_get_bug_report_url() ?>">
				<input type="hidden" name="f_category" 			value="<?php echo $f_category ?>" />
				<input type="hidden" name="f_severity" 			value="<?php echo $f_severity ?>" />
				<input type="hidden" name="f_reproducibility" 	value="<?php echo $f_reproducibility ?>" />
				<input type="hidden" name="f_profile_id" 		value="<?php echo $f_profile_id ?>" />
				<input type="hidden" name="f_platform" 			value="<?php echo $f_platform ?>" />
				<input type="hidden" name="f_os" 				value="<?php echo $f_os ?>" />
				<input type="hidden" name="f_os_build" 			value="<?php echo $f_os_build ?>" />
				<input type="hidden" name="f_product_version" 	value="<?php echo $f_product_version ?>" />
				<input type="hidden" name="f_build" 			value="<?php echo $f_build ?>" />
				<input type="hidden" name="f_report_stay" 		value="<?php echo $f_report_stay ?>" />
				<input type="submit" 							value="<?php echo lang_get( 'report_more_bugs' ) ?>" />
			</form>
<?php
	} else {
		print_bracket_link( string_get_bug_view_url( $t_bug_id, 1 ), lang_get( 'view_submitted_bug_link' ) . " $t_bug_id" );
		print_bracket_link( 'view_all_bug_page.php', lang_get( 'view_bugs_link' ) );
	}
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>