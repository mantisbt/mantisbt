<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: manage_config_workflow_page.php,v 1.2 2005-02-27 15:33:01 jlatour Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );
	require_once( $t_core_path . 'email_api.php' );

	html_page_top1( lang_get( 'manage_workflow_config' ) );
	html_page_top2();

	print_manage_menu( 'adm_permissions_report.php' );
	print_manage_config_menu( 'manage_config_workflow_page.php' );

	$t_access = current_user_get_access_level();
	$t_project = helper_get_current_project();
	$t_can_change_flags = $t_access >= config_get_access( 'status_enum_workflow' );

	# Get the value associated with the specific action and flag.
	function show_flag( $p_from_status_id, $p_to_status_id ) {
		global $t_can_change_flags, $t_exit;
		if ( $p_from_status_id <> $p_to_status_id ) {
			$t_flag = isset( $t_exit[$p_from_status_id][$p_to_status_id] );
			$t_label = $t_flag ? $t_exit[$p_from_status_id][$p_to_status_id] : '';
			if ( $t_can_change_flags ) {
				$t_flag_name = $p_from_status_id . ':' . $p_to_status_id;
				$t_set = $t_flag ? "CHECKED" : "";
				$t_value = "<input type=\"checkbox\" name=\"flag[]\" value=\"$t_flag_name\" $t_set />";
			} else {
				$t_value = $t_flag ? '<img src="images/ok.gif" width="20" height="15" title="X" alt="X" />' : '&nbsp;';
			}

			if ( $t_flag && ( '' != $t_label ) ) {
				$t_value .= '<br />(' . $t_label . ')';
			}
		} else {
			$t_value = '';
		}

		return $t_value;
	}

	function section_begin( $p_section_name ) {
		$t_enum_status = explode_enum_string( config_get( 'status_enum_string' ) );
		echo '<table class="width100">';
		echo '<tr><td class="form-title" colspan=' . ( count( $t_enum_status ) + 1 ) . '>'
			. strtoupper( $p_section_name ) . '</td></tr>' . "\n";
		echo '<tr><td class="form-title" width="30%">' . lang_get( 'current_status' ) . '</td>';
		echo '<td class="form-title" colspan="' . ( count( $t_enum_status ) ) . '">'
			. lang_get( 'next_status' ) . '</td></tr>';
		echo "\n<tr><td>&nbsp;</td>";
		foreach( $t_enum_status as $t_status ) {
			$t_entry_array = explode_enum_arr( $t_status );
			echo '<td class="form-title" style="text-align:center">&nbsp;' . get_enum_to_string( lang_get( 'status_enum_string' ), $t_entry_array[0] ) . '&nbsp;</td>';
		}
		echo '</tr>' . "\n";
	}

	function capability_row( $p_from_status ) {
		$t_enum_status = explode_enum_string( config_get( 'status_enum_string' ) );
		list( $t_from_status_id, $t_from_status_label ) = explode_enum_arr( $p_from_status );
		echo '<tr ' . helper_alternate_class() . '><td>' . get_enum_to_string( lang_get( 'status_enum_string' ), $t_from_status_id ) . '</td>';
		foreach ( $t_enum_status as $t_to_status) {
			list( $t_to_status_id, $t_to_status_label ) = explode_enum_arr( $t_to_status );
			echo '<td class="center">' . show_flag( $t_from_status_id, $t_to_status_id ) . '</td>';
		}
		echo '</tr>' . "\n";
	}

	function section_end() {
		echo '</table><br />' . "\n";
	}

	function threshold_begin( $p_section_name ) {
		echo '<table class="width100">';
		echo '<tr><td class="form-title" colspan="3">' . strtoupper( $p_section_name ) . '</td></tr>' . "\n";
		echo '<tr><td class="form-title" width="30%">' . lang_get( 'threshold' ) . '</td>';
		echo '<td class="form-title" >' . lang_get( 'status_level' ) . '</td>';
		echo '<td class="form-title" >' . lang_get( 'alter_level' ) . '</td></tr>';
		echo "\n";
	}

	function threshold_row( $p_threshold ) {
		global $t_access;
		echo '<tr ' . helper_alternate_class() . '><td>' . lang_get( 'desc_' . $p_threshold ) . '</td>';
		$t_change_threshold = config_get_access( $p_threshold );
		if ( $t_access >= $t_change_threshold ) {
			echo '<td><select name="threshold_' . $p_threshold . '">';
			print_enum_string_option_list( 'status', config_get( $p_threshold ) );
			echo '</select> </td>';
			echo '<td><select name="access_' . $p_threshold . '">';
			print_enum_string_option_list( 'access_levels', $t_change_threshold );
			echo '</select> </td>';
		} else {
			echo '<td>' . get_enum_to_string( lang_get( 'status_enum_string' ), config_get( $p_threshold ) ) . '&nbsp;</td>';
			echo '<td>' . get_enum_to_string( lang_get( 'access_levels_enum_string' ), $t_change_threshold ) . '&nbsp;</td>';
		}

		echo '</tr>' . "\n";
	}

	function threshold_end() {
		echo '</table><br />' . "\n";
	}

	function access_begin( $p_section_name ) {
		$t_enum_status = explode_enum_string( config_get( 'status_enum_string' ) );
		echo '<table class="width100">';
		echo '<tr><td class="form-title" colspan=' . ( count( $t_enum_status ) + 1 ) . '>'
			. strtoupper( $p_section_name ) . '</td></tr>' . "\n";
		echo "\n<tr><td>&nbsp;</td>";
		foreach( $t_enum_status as $t_status ) {
			$t_entry_array = explode_enum_arr( $t_status );
			echo '<td class="form-title" style="text-align:center">&nbsp;' . get_enum_to_string( lang_get( 'status_enum_string' ), $t_entry_array[0] ) . '&nbsp;</td>';
		}
		echo '</tr>' . "\n";
	}

	function access_row( ) {
		global $t_access;
		$t_enum_status = explode_enum_string( config_get( 'status_enum_string' ) );
		echo '<tr ' . helper_alternate_class() . '><td>' . lang_get( 'access_change' ) . '</td>';
		foreach ( $t_enum_status as $t_status) {
			list( $t_status_id, $t_status_label ) = explode_enum_arr( $t_status );
			if ( NEW_ == $t_status_id ) {
				$t_level = config_get( 'report_bug_threshold' );
				$t_can_change = ( $t_access <= config_get_access( 'report_bug_threshold' ) );
			}else{
				$t_level = access_get_status_threshold( $t_status_id );
				$t_can_change = ( $t_access <= config_get_access( 'set_status_threshold' ) );
			}

			if ( $t_can_change ) {
				echo '<td><select name="access_change_' . $t_status_id . '">';
				print_enum_string_option_list( 'access_levels', $t_level );
				echo '</select> </td>';
			} else {
				echo '<td class="center">' . get_enum_to_string( config_get( 'access_levels_enum_string' ), $t_access ) . '</td>';
			}
		}
		echo '</tr>' . "\n";
	}

	echo '<br /><br />';

	# count arcs in and out of each status
	$t_enum_status = config_get( 'status_enum_string' );
	$t_enum_workflow = config_get( 'status_enum_workflow' );

	$t_extra_enum_status = '0:non-existent,' . $t_enum_status;
	$t_lang_enum_status = '0:' . lang_get( 'non_existent' ) . ',' . lang_get( 'status_enum_string' );
	$t_all_status = explode( ',', $t_extra_enum_status);

	$t_status_arr  = explode_enum_string( $t_enum_status );
	$t_entry = array();
	$t_exit = array();
	$t_validation_result = '';

	# prepopulate new bug state (bugs go from nothing to here)
	$t_submit_status_array = config_get( 'bug_submit_status' );
	$t_new_label = get_enum_to_string( lang_get( 'status_enum_string' ), NEW_ );
	if ( true == is_array( $t_submit_status_array ) ) {
		# @@@ (thraxisp) this is not implemented in bug_api.php
		foreach ($t_submit_status_array as $t_access => $t_status ) {
			$t_entry[$t_status][0] = $t_new_label;
			$t_exit[0][$t_status] = $t_new_label;
		}
	}else{
			$t_status = $t_submit_status_array;
			$t_entry[$t_status][0] = $t_new_label;
			$t_exit[0][$t_status] = $t_new_label;
	}

  # add user defined arcs and implicit reopen arcs
	$t_reopen = config_get( 'bug_reopen_status' );
	$t_reopen_label = get_enum_to_string( lang_get( 'resolution_enum_string' ), REOPENED );
	$t_resolved_status = config_get( 'bug_resolved_status_threshold' );
	foreach ( $t_status_arr as $t_status ) {
		list( $t_status_id, $t_status_label ) = explode_enum_arr( $t_status );
		if ( isset( $t_enum_workflow[$t_status_id] ) ) {
			$t_next_arr = explode_enum_string( $t_enum_workflow[$t_status_id] );
			foreach ( $t_next_arr as $t_next ) {
				if ( !is_blank( $t_next ) ) {
					list( $t_next_id, $t_next_label ) = explode_enum_arr( $t_next );
					$t_exit[$t_status_id][$t_next_id] = '';
					$t_entry[$t_next_id][$t_status_id] = '';
					if ( $t_status_id == $t_next_id ) {
						$t_validation_result .= '<tr ' . helper_alternate_class() . '><td>'
							. get_enum_to_string( $t_lang_enum_status, $t_next_id )
							. '</td><td bgcolor="#FFED4F">' . lang_get( 'superfluous' ) . '</td>';
					}
				}
			}
		}else{
			$t_exit[$t_status_id] = array();
		}
		if ( $t_status_id >= $t_resolved_status ) {
			$t_exit[$t_status_id][$t_reopen] = $t_reopen_label;
			$t_entry[$t_reopen][$t_status_id] = $t_reopen_label;
		}
		if ( ! isset( $t_entry[$t_status_id] ) ) {
			$t_entry[$t_status_id] = array();
		}
	}

	# check for entry == 0 without exit == 0, unreachable state
	foreach ( $t_status_arr as $t_status ) {
		list( $t_status_id, $t_status_label ) = explode_enum_arr( $t_status );
		if ( ( 0 == count( $t_entry[$t_status_id] ) ) && ( 0 < count( $t_exit[$t_status_id] ) ) ){
			$t_validation_result .= '<tr ' . helper_alternate_class() . '><td>'
							. get_enum_to_string( $t_lang_enum_status, $t_status_id )
							. '</td><td bgcolor="#FF0088">' . lang_get( 'unreachable' ) . '</td>';
		}
	}

	# check for exit == 0 without entry == 0, unleaveable state
	foreach ( $t_status_arr as $t_status ) {
		list( $t_status_id, $t_status_label ) = explode_enum_arr( $t_status );
		if ( ( 0 == count( $t_exit[$t_status_id] ) ) && ( 0 < count( $t_entry[$t_status_id] ) ) ){
			$t_validation_result .= '<tr ' . helper_alternate_class() . '><td>'
							. get_enum_to_string( $t_lang_enum_status, $t_status_id )
							. '</td><td bgcolor="#FF0088">' . lang_get( 'no_exit' ) . '</td>';
		}
	}

	if ( $t_can_change_flags ) {
		echo "<form name=\"workflow_config_action\" method=\"post\" action=\"manage_config_workflow_set.php\">\n";
	}
	echo '<p class="form-title">' . lang_get( 'project_name' ) . ': ' . project_get_name( $t_project ) . '</p>' . "\n";


	# show the settings used to derive the table
	threshold_begin( lang_get( 'workflow_thresholds' ) );
	if ( ! is_array( config_get( 'bug_submit_status' ) ) ) {
		threshold_row( 'bug_submit_status' );
	}
	threshold_row( 'bug_resolved_status_threshold' );
	threshold_row( 'bug_reopen_status' );
	threshold_end();

	if ( '' <> $t_validation_result ) {
		echo '<table class="width100">';
		echo '<tr><td class="form-title" colspan="3">' . strtoupper( lang_get( 'validation' ) ) . '</td></tr>' . "\n";
		echo '<tr><td class="form-title" width="30%">' . lang_get( 'status' ) . '</td>';
		echo '<td class="form-title" >' . lang_get( 'comment' ) . '</td></tr>';
		echo "\n";
		echo $t_validation_result;
		echo '</table><br /><br />';
	}

	# display the graph as a matrix
	section_begin( lang_get( 'workflow' ) );
	foreach ( $t_status_arr as $t_from_status ) {
		capability_row( $t_from_status );
	}
	section_end();

	if ( $t_can_change_flags ) {
		echo '<p>' . lang_get( 'workflow_change_access' ) . ':';
		echo '<select name="workflow_access">';
		print_enum_string_option_list( 'access_levels', config_get_access( 'status_enum_workflow' ) );
		echo '</select> </p>';
	}

	# display the access levels required to move an issue
	access_begin( lang_get( 'access_levels' ) );
	access_row( );
	section_end();

	if ( $t_access <= config_get_access( 'set_status_threshold' ) ) {
		echo '<p>' . lang_get( 'access_change_access' ) . ':';
		echo '<select name="status_access">';
		print_enum_string_option_list( 'access_levels', config_get_access( 'status_enum_workflow' ) );
		echo '</select> </p>';
	}

	if ( $t_can_change_flags ) {
		echo "<input type=\"submit\" class=\"button\" value=\"" . lang_get( 'change_configuration' ) . "\" />\n";

		echo "</form>\n";
	}

	html_page_bottom1( __FILE__ );
?>
