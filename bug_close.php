<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_close.php,v 1.38 2004-06-26 14:05:41 prichards Exp $
	# --------------------------------------------------------
?>
<?php
	# This file sets the bug to the chosen resolved state then gives the
	# user the opportunity to enter a reason for the closure
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'bug_api.php' );
?>
<?php
	$f_bug_id		= gpc_get_int( 'bug_id' );
	$f_bugnote_text	= gpc_get_string( 'bugnote_text', '' );

	access_ensure_can_close_bug( $f_bug_id );

	# Validate the custom fields before closing the bug.
	$t_bug_data = bug_get( $f_bug_id, true );
	$t_related_custom_field_ids = custom_field_get_linked_ids( $t_bug_data->project_id );
	foreach( $t_related_custom_field_ids as $t_id ) {
		$t_def = custom_field_get_definition( $t_id );
		if ( $t_def['require_close'] && ( gpc_get_string( "custom_field_$t_id", '' ) == '' ) ) {
			error_parameters( lang_get_defaulted( custom_field_get_field( $t_id, 'name' ) ) );
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		}
		if ( !custom_field_validate( $t_id, gpc_get_string( "custom_field_$t_id", $t_def['default_value'] ) ) ) {
			error_parameters( lang_get_defaulted( custom_field_get_field( $t_id, 'name' ) ) );
			trigger_error( ERROR_CUSTOM_FIELD_INVALID_VALUE, ERROR );
		}
	}

	# Handle custom field submission
	foreach( $t_related_custom_field_ids as $t_id ) {
		# Do not set custom field value if user has no write access.
		if( !custom_field_has_write_access( $t_id, $f_bug_id ) ) {
			continue;
		}

		$t_def = custom_field_get_definition( $t_id );
		if( !custom_field_set_value( $t_id, $f_bug_id, gpc_get_string( "custom_field_$t_id", $t_def['default_value'] ) ) ) {
			error_parameters( lang_get_defaulted( custom_field_get_field( $t_id, 'name' ) ) );
			trigger_error( ERROR_CUSTOM_FIELD_INVALID_VALUE, ERROR );
		}
	}

	bug_close( $f_bug_id, $f_bugnote_text );

	print_successful_redirect( 'view_all_bug_page.php' );
?>
