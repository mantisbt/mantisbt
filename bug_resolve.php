<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_resolve.php,v 1.43 2004-08-02 19:40:38 prichards Exp $
	# --------------------------------------------------------
?>
<?php
	# This file sets the bug to the chosen resolved state and adds a
	#  bugnote giving a reason for the resolution
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'bug_api.php' );
?>
<?php
	$f_bug_id		= gpc_get_int( 'bug_id' );
	$f_bugnote_text	= gpc_get_string( 'bugnote_text', '' );
	$f_resolution	= gpc_get_int( 'resolution', FIXED );
	$f_duplicate_id	= gpc_get_int( 'duplicate_id', null );
	$f_fixed_in_version = gpc_get_string( 'fixed_in_version', '' );
	$f_close_now	= gpc_get_bool( 'close_now' );

	access_ensure_bug_level( config_get( 'update_bug_threshold' ), $f_bug_id );
	access_ensure_bug_level( config_get( 'handle_bug_threshold' ), $f_bug_id );

	# make sure the bug is not being marked as a duplicate of itself
	if ( $f_duplicate_id === $f_bug_id ) {
		trigger_error( ERROR_BUG_DUPLICATE_SELF, ERROR );
	}

	# Validate the custom fields before resolving the bug.
	$t_bug_data = bug_get( $f_bug_id, true );
	$t_related_custom_field_ids = custom_field_get_linked_ids( $t_bug_data->project_id );
	foreach( $t_related_custom_field_ids as $t_id ) {
		$t_def = custom_field_get_definition( $t_id );
		if ( $t_def['require_resolve'] && ( gpc_get_custom_field( "custom_field_$t_id", $t_def['type'], '' ) == '' ) ) {
			error_parameters( lang_get_defaulted( custom_field_get_field( $t_id, 'name' ) ) );
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		}
		if ( !custom_field_validate( $t_id, gpc_get_custom_field( "custom_field_$t_id", $t_def['type'], $t_def['default_value'] ) ) ) {
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
		if( !custom_field_set_value( $t_id, $f_bug_id, gpc_get_custom_field( "custom_field_$t_id", $t_def['type'],$t_def['default_value'] ) ) ) {
			error_parameters( lang_get_defaulted( custom_field_get_field( $t_id, 'name' ) ) );
			trigger_error( ERROR_CUSTOM_FIELD_INVALID_VALUE, ERROR );
		}
	}

	bug_resolve( $f_bug_id, $f_resolution, $f_fixed_in_version, $f_bugnote_text, $f_duplicate_id );

	if ( $f_close_now ) {
		bug_set_field( $f_bug_id, 'status', CLOSED );
	}

	print_successful_redirect_to_bug( $f_bug_id );
?>
