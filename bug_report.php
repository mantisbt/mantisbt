<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_report.php,v 1.31 2004-05-17 13:02:33 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	# This page stores the reported bug
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'string_api.php' );
	require_once( $t_core_path.'file_api.php' );
	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'custom_field_api.php' );
?>
<?php
	access_ensure_project_level( config_get('report_bug_threshold' ) );

	$f_build				= gpc_get_string( 'build', '' );
	$f_platform				= gpc_get_string( 'platform', '' );
	$f_os					= gpc_get_string( 'os', '' );
	$f_os_build				= gpc_get_string( 'os_build', '' );
	$f_product_version		= gpc_get_string( 'product_version', '' );
	$f_profile_id			= gpc_get_int( 'profile_id', 0 );
	$f_handler_id			= gpc_get_int( 'handler_id', 0 );
	$f_view_state			= gpc_get_int( 'view_state', config_get( 'default_bug_view_status' ) );

	$f_category				= gpc_get_string( 'category', '' );
	$f_reproducibility		= gpc_get_int( 'reproducibility' );
	$f_severity				= gpc_get_int( 'severity' );
	$f_priority				= gpc_get_int( 'priority', NORMAL );
	$f_summary				= gpc_get_string( 'summary' );
	$f_description			= gpc_get_string( 'description' );
	$f_steps_to_reproduce	= gpc_get_string( 'steps_to_reproduce', '' );
	$f_additional_info		= gpc_get_string( 'additional_info', '' );

	$f_file					= gpc_get_file( 'file', null );
	$f_report_stay			= gpc_get_bool( 'report_stay' );
	$f_project_id			= gpc_get_int( 'project_id' );

	$t_reporter_id		= auth_get_current_user_id();
	$t_upload_method	= config_get( 'file_upload_method' );

	$f_summary			= trim( $f_summary );

	# If a file was uploaded, and we need to store it on disk, let's make
	#  sure that the file path for this project exists
	if ( is_uploaded_file( $f_file['tmp_name'] ) &&
		  file_allow_bug_upload() &&
		  ( DISK == $t_upload_method || FTP == $t_upload_method ) ) {
		$t_file_path = project_get_field( $f_project_id, 'file_path' );

		if ( !file_exists( $t_file_path ) ) {
			trigger_error( ERROR_NO_DIRECTORY, ERROR );
		}
	}


	# if a profile was selected then let's use that information
	if ( 0 != $f_profile_id ) {
		$row = user_get_profile_row( $t_reporter_id, $f_profile_id );

		if ( is_blank( $f_platform ) ) {
			$f_platform = $row['platform'];
		}
		if ( is_blank( $f_os ) ) {
			$f_os = $row['os'];
		}
		if ( is_blank( $f_os_build ) ) {
			$f_os_build = $row['os_build'];
		}
	}

	# Validate the custom fields before adding the bug.
	$t_related_custom_field_ids = custom_field_get_linked_ids( $f_project_id );
	foreach( $t_related_custom_field_ids as $t_id ) {
		$t_def = custom_field_get_definition( $t_id );
		if ( !custom_field_validate( $t_id, gpc_get_string( "custom_field_$t_id", $t_def['default_value'] ) ) ) {
			trigger_error( ERROR_CUSTOM_FIELD_INVALID_VALUE, ERROR );
		}
	}

	# Create the bug
	$t_bug_id = bug_create( $f_project_id,
					$t_reporter_id, $f_handler_id,
					$f_priority,
					$f_severity, $f_reproducibility,
					$f_category,
					$f_os, $f_os_build,
					$f_platform, $f_product_version,
					$f_build,
					$f_profile_id, $f_summary, $f_view_state,
					$f_description, $f_steps_to_reproduce, $f_additional_info );


	# Handle the file upload
	if ( is_uploaded_file( $f_file['tmp_name'] ) &&
		  0 != $f_file['size'] &&
		  file_allow_bug_upload() ) {
		file_add( $t_bug_id, $f_file['tmp_name'], $f_file['name'], $f_file['type'] );
	}


	# Handle custom field submission
	foreach( $t_related_custom_field_ids as $t_id ) {
		# Do not set custom field value if user has no write access.
		if( !custom_field_has_write_access( $t_id, $t_bug_id ) ) {
			continue;
		}

		$t_def = custom_field_get_definition( $t_id );
		if( !custom_field_set_value( $t_id, $t_bug_id, gpc_get_string( "custom_field_$t_id", $t_def['default_value'] ) ) ) {
			trigger_error( ERROR_CUSTOM_FIELD_INVALID_VALUE, ERROR );
		}
	}

	email_new_bug( $t_bug_id );

	html_page_top1();

	if ( ! $f_report_stay ) {
		html_meta_redirect( 'view_all_bug_page.php' );
	}

	html_page_top2();
?>
<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ) . '<br />';

	if ( $f_report_stay ) {
?>
	<form method="post" action="<?php echo string_get_bug_report_url() ?>">
		<input type="hidden" name="category" 		value="<?php echo $f_category ?>" />
		<input type="hidden" name="severity" 		value="<?php echo $f_severity ?>" />
		<input type="hidden" name="reproducibility" 	value="<?php echo $f_reproducibility ?>" />
		<input type="hidden" name="profile_id" 		value="<?php echo $f_profile_id ?>" />
		<input type="hidden" name="platform" 		value="<?php echo $f_platform ?>" />
		<input type="hidden" name="os" 			value="<?php echo $f_os ?>" />
		<input type="hidden" name="os_build" 		value="<?php echo $f_os_build ?>" />
		<input type="hidden" name="product_version" 	value="<?php echo $f_product_version ?>" />
		<input type="hidden" name="build" 		value="<?php echo $f_build ?>" />
		<input type="hidden" name="report_stay" 	value="1" />
		<input type="hidden" name="view_state"		value="<?php echo $f_view_state ?>" />
		<input type="submit" class="button" 		value="<?php echo lang_get( 'report_more_bugs' ) ?>" />
	</form>
<?php
	} else {
		print_bracket_link( string_get_bug_view_url( $t_bug_id ), lang_get( 'view_submitted_bug_link' ) . " $t_bug_id" );
		print_bracket_link( 'view_all_bug_page.php', lang_get( 'view_bugs_link' ) );
	}
?>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
