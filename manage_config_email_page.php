<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: manage_config_email_page.php,v 1.2 2005-02-27 15:33:01 jlatour Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );
	require_once( $t_core_path . 'email_api.php' );

	html_page_top1( lang_get( 'manage_email_config' ) );
	html_page_top2();

	print_manage_menu( 'adm_permissions_report.php' );
	print_manage_config_menu( 'manage_config_email_page.php' );

	$t_access = current_user_get_access_level();
	$t_project = helper_get_current_project();
	$t_can_change_flags = $t_access >= config_get_access( 'notify_flags' );
	$t_can_change_defaults = $t_access >= config_get_access( 'default_notify_flags' );

	# Get the value associated with the specific action and flag.
	function show_notify_flag( $p_action, $p_flag ) {
		global $t_can_change_flags , $t_can_change_defaults;
		$t_flag = email_notify_flag( $p_action, $p_flag );
		if ( $t_can_change_flags || $t_can_change_defaults ) {
			$t_flag_name = $p_action . ':' . $p_flag;
			$t_set = $t_flag ? "CHECKED" : "";
			return "<input type=\"checkbox\" name=\"flag[]\" value=\"$t_flag_name\" $t_set />";
		} else {
			return ( $t_flag ? '<img src="images/ok.gif" width="20" height="15" title="X" alt="X" />' : '&nbsp;' );
		}
	}

	function show_notify_threshold( $p_access, $p_action ) {
		global $t_can_change_flags , $t_can_change_defaults;
		$t_flag = ( $p_access >= email_notify_flag( $p_action, 'threshold_min' ) )
			&& ( $p_access <= email_notify_flag( $p_action, 'threshold_max' ) );
		if ( $t_can_change_flags  || $t_can_change_defaults ) {
			$t_flag_name = $p_action . ':' . $p_access;
			$t_set = $t_flag ? "CHECKED" : "";
			return "<input type=\"checkbox\" name=\"flag_threshold[]\" value=\"$t_flag_name\" $t_set />";
		} else {
			return $t_flag ? '<img src="images/ok.gif" width="20" height="15" title="X" alt="X" />' : '&nbsp;';
		}
	}

	function get_section_begin_for_email( $p_section_name ) {
		global $t_project;
		$t_access_levels = explode_enum_string( config_get( 'access_levels_enum_string' ) );
		echo '<table class="width100">';
		echo '<tr><td class="form-title" colspan=' . ( count( $t_access_levels ) + 7 ) . '>' . strtoupper( $p_section_name )
			. '<br />' . lang_get( 'project_name' ) . ': ' . project_get_name( $t_project ) . '</td></tr>' . "\n";
		echo '<tr><td class="form-title" width="30%">Message</td>';
		echo'<td class="form-title" style="text-align:center">&nbsp;' . lang_get( 'reporter' ) . '&nbsp;</td>';
		echo '<td class="form-title" style="text-align:center">&nbsp;' . lang_get( 'assigned_to' ) . '&nbsp;</td>';
		echo '<td class="form-title" style="text-align:center">&nbsp;' . lang_get( 'users_monitoring_bug' ) . '&nbsp;</td>';
		echo '<td class="form-title" style="text-align:center">&nbsp;' . lang_get( 'users_added_bugnote' ) . '&nbsp;</td>';
		foreach( $t_access_levels as $t_access_level ) {
			$t_entry_array = explode_enum_arr( $t_access_level );
			echo '<td class="form-title" style="text-align:center">&nbsp;' . get_enum_to_string( lang_get( 'access_levels_enum_string' ), $t_entry_array[0] ) . '&nbsp;</td>';
		}
		echo '</tr>' . "\n";
	}

	function get_capability_row_for_email( $p_caption, $p_message_type ) {
		$t_access_levels = explode_enum_string( config_get( 'access_levels_enum_string' ) );

		echo '<tr ' . helper_alternate_class() . '><td>' . string_display( $p_caption ) . '</td>';
		echo '<td class="center">' . show_notify_flag( $p_message_type, 'reporter' )  . '</td>';
		echo '<td class="center">' . show_notify_flag( $p_message_type, 'handler' ) . '</td>';
		echo '<td class="center">' . show_notify_flag( $p_message_type, 'monitor' ) . '</td>';
		echo '<td class="center">' . show_notify_flag( $p_message_type, 'bugnotes' ) . '</td>';

		foreach( $t_access_levels as $t_access_level ) {
			$t_entry_array = explode_enum_arr( $t_access_level );
			echo '<td class="center">' . show_notify_threshold( (int)$t_entry_array[0], $p_message_type ) . '</td>';
		}
		echo '</tr>' . "\n";
	}

	function get_section_end_for_email() {
		echo '</table><br />' . "\n";
	}

	echo '<br /><br />';

	# Email notifications
	if( config_get( 'enable_email_notification' ) == ON ) {

		if ( $t_can_change_flags  || $t_can_change_defaults ) {
			echo "<form name=\"mail_config_action\" method=\"post\" action=\"manage_config_email_set.php\">\n";
		}

		get_section_begin_for_email( lang_get( 'email_notification' ) );
		get_capability_row_for_email( lang_get( 'email_on_new' ), 'new' );
		get_capability_row_for_email( lang_get( 'email_on_assigned' ), 'owner' );
		get_capability_row_for_email( lang_get( 'email_on_reopened' ), 'reopened' );
		get_capability_row_for_email( lang_get( 'email_on_deleted' ), 'deleted' );
		get_capability_row_for_email( lang_get( 'email_on_bugnote_added' ), 'bugnote' );
		if( config_get( 'enable_sponsorship' ) == ON ) {
			get_capability_row_for_email( lang_get( 'email_on_sponsorship_changed' ), 'sponsor' );
		}
		if( config_get( 'enable_relationship' ) == ON ) {
			get_capability_row_for_email( lang_get( 'email_on_relationship_changed' ), 'relationship' );
		}
		$t_statuses = explode_enum_string( config_get( 'status_enum_string' ) );
		foreach( $t_statuses as $t_status ) {
			list( $t_state, $t_label ) = explode_enum_arr( $t_status );
			get_capability_row_for_email( lang_get( 'status_changed_to' ) . ' \'' . get_enum_element( 'status', $t_state ) . '\'', $t_label );
		}

		get_section_end_for_email();

		if ( $t_can_change_flags  || $t_can_change_defaults ) {
			if ( $t_can_change_defaults ) {
				echo '<p>' . lang_get( 'notify_defaults_change_access' ) . ':';
				echo '<select name="notify_defaults_access">';
				print_enum_string_option_list( 'access_levels', config_get_access( 'default_notify_flags' ) );
				echo '</select> </p>';
			}

			if ( $t_can_change_flags ) {
				echo '<p>' . lang_get( 'notify_actions_change_access' ) . ':';
				echo '<select name="notify_actions_access">';
				print_enum_string_option_list( 'access_levels', config_get_access( 'notify_flags' ) );
				echo '</select> </p>';
			}

			echo "<input type=\"submit\" class=\"button\" value=\"" . lang_get( 'change_configuration' ) . "\" />\n";

			echo "</form>\n";
		}

	}

	html_page_bottom1( __FILE__ );
?>
