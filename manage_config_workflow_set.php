<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: manage_config_workflow_set.php,v 1.2 2005-02-27 15:33:01 jlatour Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );
	require_once( $t_core_path.'email_api.php' );

	$t_can_change_level = min( config_get_access( 'notify_flags' ), config_get_access( 'default_notify_flags' ) );
	access_ensure_global_level( $t_can_change_level );

	$t_redirect_url = 'manage_config_workflow_page.php';
	$t_project = helper_get_current_project();
	$t_access = current_user_get_access_level();

	html_page_top1( lang_get( 'manage_workflow_config' ) );
	html_meta_redirect( $t_redirect_url );
	html_page_top2();

	# process the changes to threshold values
	$t_valid_thresholds = array( 'bug_submit_status', 'bug_resolved_status_threshold', 'bug_reopen_status' );

	foreach( $t_valid_thresholds as $t_threshold ) {
		if( config_get_access( $t_threshold ) <= $t_access ) {
			$f_value = gpc_get( 'threshold_' . $t_threshold );
			$f_access = gpc_get( 'access_' . $t_threshold );
			config_set( $t_threshold, $f_value, NO_USER, $t_project, $f_access );
		}
	}

	# process the workflow by reversing the flags to a matrix and creating the appropriate string
	if( config_get_access( 'status_enum_workflow' ) <= $t_access ) {
		$f_value = gpc_get( 'flag' );
		$f_access = gpc_get( 'workflow_access' );
		$t_matrix = array();

		foreach( $f_value as $t_transition ) {
			list( $t_from, $t_to ) = split( ':', $t_transition );
			$t_matrix[$t_from][$t_to] = '';
		}
		$t_statuses = explode_enum_string( config_get( 'status_enum_string' ) );
		foreach( $t_statuses as $t_status ) {
			list( $t_state, $t_label ) = explode_enum_arr( $t_status );
			$t_workflow_row = '';
			$t_first = true;
			if ( isset( $t_matrix[$t_state] ) ) {
				foreach ( $t_matrix[$t_state] as $t_next_state => $t_junk ) {
					if ( false == $t_first ) {
						$t_workflow_row .= ',';
					}
					$t_workflow_row .= $t_next_state . ':' . get_enum_element( 'status', $t_next_state );
					$t_first = false;
				}
			}
			if ( '' <> $t_workflow_row ) {
				$t_workflow[$t_state] = $t_workflow_row;
			}
		}
		config_set( 'status_enum_workflow', $t_workflow, NO_USER, $t_project, $f_access );
	}

	# process the access level changes
	if( config_get_access( 'status_enum_workflow' ) <= $t_access ) {
		# get changes to access level to change these values
		$f_access = gpc_get( 'status_access' );

		# walk through the status labels to set the status threshold
		$t_enum_status = explode_enum_string( config_get( 'status_enum_string' ) );
		$t_set_status = array();
		foreach ( $t_enum_status as $t_status) {
			list( $t_status_id, $t_status_label ) = explode_enum_arr( $t_status );
			$f_level = gpc_get( 'access_change_' . $t_status_id );
			if ( NEW_ == $t_status_id ) {
				config_set( 'report_bug_threshold', (int)$f_level, ALL_USERS, $t_project, $f_access );
			}else{
				$t_set_status[$t_status_id] = (int)$f_level;
			}
		}

		config_set( 'set_status_threshold', $t_set_status, ALL_USERS, $t_project, $f_access );
	}
?>

<br />
<div align="center">
<?php
	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
