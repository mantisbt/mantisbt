<?php
# MantisBT - a php based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

	/**
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	require_once( 'email_api.php' );

	auth_reauthenticate();

	html_page_top( lang_get( 'manage_threshold_config' ) );

	print_manage_menu( 'adm_permissions_report.php' );
	print_manage_config_menu( 'manage_config_work_threshold_page.php' );

    $t_user = auth_get_current_user_id();
	$t_project_id = helper_get_current_project();
	$t_access = user_get_access_level( $t_user, $t_project_id );
	$t_show_submit = false;

	$t_access_levels = MantisEnum::getAssocArrayIndexedByValues( config_get( 'access_levels_enum_string' ) );

	$t_overrides = array();
	function set_overrides( $p_config ) {
	   global $t_overrides;
	   if ( !in_array( $p_config, $t_overrides ) ) {
	       $t_overrides[] = $p_config;
	   }
	}

	function get_section_begin_mcwt( $p_section_name ) {
		global $t_access_levels;

		echo '<table class="width100">';
		echo '<tr><td class="form-title" colspan="' . ( count( $t_access_levels ) + 2 ) . '">' . $p_section_name . '</td></tr>' . "\n";
		echo '<tr><td class="form-title" width="40%" rowspan="2">' . lang_get( 'perm_rpt_capability' ) . '</td>';
		echo '<td class="form-title"style="text-align:center"  width="40%" colspan="' . count( $t_access_levels ) . '">' . lang_get( 'access_levels' ) . '</td>';
		echo '<td class="form-title" style="text-align:center" rowspan="2">&#160;' . lang_get( 'alter_level' ) . '&#160;</td></tr><tr>';
		foreach( $t_access_levels as $t_access_level => $t_access_label ) {
			echo '<td class="form-title" style="text-align:center">&#160;' . MantisEnum::getLabel( lang_get( 'access_levels_enum_string' ), $t_access_level ) . '&#160;</td>';
		}
		echo '</tr>' . "\n";
	}

	function get_capability_row( $p_caption, $p_threshold, $p_all_projects_only=false ) {
	    global $t_user, $t_project_id, $t_show_submit, $t_access_levels, $t_colour_project, $t_colour_global;

        $t_file = config_get_global( $p_threshold );
        if ( !is_array( $t_file ) ) {
            $t_file_exp = array();
		    foreach( $t_access_levels as $t_access_level => $t_label ) {
		        if ( $t_access_level >= $t_file ) {
		            $t_file_exp[] = $t_access_level;
		        }
		    }
		} else {
		    $t_file_exp = $t_file;
		}

        $t_global = config_get( $p_threshold, null, null, ALL_PROJECTS );
        if ( !is_array( $t_global ) ) {
            $t_global_exp = array();
		    foreach( $t_access_levels as $t_access_level => $t_label ) {
		        if ( $t_access_level >= $t_global ) {
		            $t_global_exp[] = $t_access_level;
		        }
		    }
		} else {
		    $t_global_exp = $t_global;
		}

        $t_project = config_get( $p_threshold );
        if ( !is_array( $t_project ) ) {
            $t_project_exp = array();
		    foreach( $t_access_levels as $t_access_level => $t_label ) {
		        if ( $t_access_level >= $t_project ) {
		            $t_project_exp[] = $t_access_level;
		        }
		    }
		} else {
		    $t_project_exp = $t_project;
		}

		$t_can_change = access_has_project_level( config_get_access( $p_threshold ), $t_project_id, $t_user )
		          && ( ( ALL_PROJECTS == $t_project_id ) || !$p_all_projects_only );

		echo '<tr ' . helper_alternate_class() . '><td>' . string_display( $p_caption ) . '</td>';
		foreach( $t_access_levels as $t_access_level => $t_access_label ) {
            $t_file = in_array( $t_access_level, $t_file_exp );
            $t_global = in_array( $t_access_level, $t_global_exp );
            $t_project = in_array( $t_access_level, $t_project_exp ) ;

            $t_colour = '';
            if ( $t_global != $t_file ) {
                $t_colour = ' bgcolor="' . $t_colour_global . '" '; # all projects override
                if ( $t_can_change ) {
                    set_overrides( $p_threshold );
                }
            }
            if ( $t_project != $t_global ) {
                $t_colour = ' bgcolor="' . $t_colour_project . '" '; # project overrides
                if ( $t_can_change ) {
                    set_overrides( $p_threshold );
                }
            }

			if ( $t_can_change ) {
			    $t_checked = $t_project ? "checked=\"checked\"" : "";
			    $t_value = "<input type=\"checkbox\" name=\"flag_thres_" . $p_threshold . "[]\" value=\"$t_access_level\" $t_checked />";
			    $t_show_submit = true;
			} else {
			    if ( $t_project ) {
				    $t_value = '<img src="images/ok.gif" width="20" height="15" alt="X" title="X" />';
			    } else {
				    $t_value = '&#160;';
			    }
            }
			echo '<td class="center"' . $t_colour . '>' . $t_value . '</td>';
		}
		if ( $t_can_change ) {
			echo '<td> <select name="access_' . $p_threshold . '">';
			print_enum_string_option_list( 'access_levels', config_get_access( $p_threshold ) );
			echo '</select> </td>';
		} else {
			echo '<td>' . MantisEnum::getLabel( lang_get( 'access_levels_enum_string' ), config_get_access( $p_threshold ) ) . '&#160;</td>';
		}

		echo '</tr>' . "\n";
	}

	function get_capability_boolean( $p_caption, $p_threshold, $p_all_projects_only=false ) {
	    global $t_user, $t_project_id, $t_show_submit, $t_access_levels, $t_colour_project, $t_colour_global;

        $t_file = config_get_global( $p_threshold );
        $t_global = config_get( $p_threshold, null, null, ALL_PROJECTS );
        $t_project = config_get( $p_threshold );

		$t_can_change = access_has_project_level( config_get_access( $p_threshold ), $t_project_id, $t_user )
		          && ( ( ALL_PROJECTS == $t_project_id ) || !$p_all_projects_only );

        $t_colour = '';
        if ( $t_global != $t_file ) {
            $t_colour = ' bgcolor="' . $t_colour_global . '" '; # all projects override
            if ( $t_can_change ) {
                set_overrides( $p_threshold );
            }
        }
        if ( $t_project != $t_global ) {
            $t_colour = ' bgcolor="' . $t_colour_project . '" '; # project overrides
            if ( $t_can_change ) {
                set_overrides( $p_threshold );
            }
        }

		echo '<tr ' . helper_alternate_class() . '><td>' . string_display( $p_caption ) . '</td>';
		if ( $t_can_change ) {
		    $t_checked = ( ON == config_get( $p_threshold ) ) ? "checked=\"checked\"" : "";
		    $t_value = "<input type=\"checkbox\" name=\"flag_" . $p_threshold . "\" value=\"1\" $t_checked />";
		    $t_show_submit = true;
		} else {
		    if ( ON == config_get( $p_threshold ) ) {
			    $t_value = '<img src="images/ok.gif" width="20" height="15" title="X" alt="X" />';
		    } else {
			    $t_value = '&#160;';
		    }
        }
		echo '<td' . $t_colour . '>' . $t_value . '</td><td class="left" colspan="' . ( count( $t_access_levels ) - 1 ). '"></td>';

		if ( $t_can_change ) {
			echo '<td><select name="access_' . $p_threshold . '">';
			print_enum_string_option_list( 'access_levels', config_get_access( $p_threshold ) );
			echo '</select> </td>';
		} else {
			echo '<td>' . MantisEnum::getLabel( lang_get( 'access_levels_enum_string' ), config_get_access( $p_threshold ) ) . '&#160;</td>';
		}

		echo '</tr>' . "\n";
	}

	function get_capability_enum( $p_caption, $p_threshold, $p_enum, $p_all_projects_only=false ) {
	    global $t_user, $t_project_id, $t_show_submit, $t_access_levels, $t_colour_project, $t_colour_global;

        $t_file = config_get_global( $p_threshold );
        $t_global = config_get( $p_threshold, null, null, ALL_PROJECTS );
        $t_project = config_get( $p_threshold );

		$t_can_change = access_has_project_level( config_get_access( $p_threshold ), $t_project_id, $t_user )
		          && ( ( ALL_PROJECTS == $t_project_id ) || !$p_all_projects_only );

        $t_colour = '';
        if ( $t_global != $t_file ) {
            $t_colour = ' bgcolor="' . $t_colour_global . '" '; # all projects override
            if ( $t_can_change ) {
                set_overrides( $p_threshold );
            }
        }
        if ( $t_project != $t_global ) {
            $t_colour = ' bgcolor="' . $t_colour_project . '" '; # project overrides
            if ( $t_can_change ) {
                set_overrides( $p_threshold );
            }
        }

		echo '<tr ' . helper_alternate_class() . '><td>' . string_display( $p_caption ) . '</td>';
		if ( $t_can_change ) {
			echo '<td class="left" colspan="3"' . $t_colour . '><select name="flag_' . $p_threshold . '">';
			print_enum_string_option_list( $p_enum, config_get( $p_threshold ) );
			echo '</select></td><td colspan="' . ( count( $t_access_levels ) - 3 ) . '"></td>';
		    $t_show_submit = true;
		} else {
			$t_value = MantisEnum::getLabel( lang_get( $p_enum . '_enum_string' ), config_get( $p_threshold ) ) . '&#160;';
		    echo '<td class="left" colspan="3"' . $t_colour . '>' . $t_value . '</td><td colspan="' . ( count( $t_access_levels ) - 3 ) . '"></td>';
        }

		if ( $t_can_change ) {
			echo '<td><select name="access_' . $p_threshold . '">';
			print_enum_string_option_list( 'access_levels', config_get_access( $p_threshold ) );
			echo '</select> </td>';
		} else {
			echo '<td>' . MantisEnum::getLabel( lang_get( 'access_levels_enum_string' ), config_get_access( $p_threshold ) ) . '&#160;</td>';
		}

		echo '</tr>' . "\n";
	}

	function get_section_end() {
		echo '</table><br />' . "\n";
	}

	$t_colour_project = config_get( 'colour_project');
	$t_colour_global = config_get( 'colour_global');

    echo "<br /><br />\n";

	if ( ALL_PROJECTS == $t_project_id ) {
	    $t_project_title = lang_get( 'config_all_projects' );
	} else {
	    $t_project_title = sprintf( lang_get( 'config_project' ) , string_display( project_get_name( $t_project_id ) ) );
	}
	echo '<p class="bold">' . $t_project_title . '</p>' . "\n";
	echo '<p>' . lang_get( 'colour_coding' ) . '<br />';
	if ( ALL_PROJECTS <> $t_project_id ) {
	    echo '<span style="background-color:' . $t_colour_project . '">' . lang_get( 'colour_project' ) .'</span><br />';
	}
	echo '<span style="background-color:' . $t_colour_global . '">' . lang_get( 'colour_global' ) . '</span></p>';

	echo "<form name=\"mail_config_action\" method=\"post\" action=\"manage_config_work_threshold_set.php\">\n";
	echo form_security_field( 'manage_config_work_threshold_set' );

	# Issues
	get_section_begin_mcwt( lang_get( 'issues' ) );
	get_capability_row( lang_get( 'report_issue' ), 'report_bug_threshold' );
    get_capability_enum( lang_get( 'submit_status' ), 'bug_submit_status', 'status' );
	get_capability_row( lang_get( 'update_issue' ), 'update_bug_threshold' );
	get_capability_boolean( lang_get( 'allow_close_immediate' ), 'allow_close_immediately' );
    get_capability_boolean( lang_get( 'allow_reporter_close' ), 'allow_reporter_close' );
	get_capability_row( lang_get( 'monitor_issue' ), 'monitor_bug_threshold' );
	get_capability_row( lang_get( 'handle_issue' ), 'handle_bug_threshold' );
 	get_capability_row( lang_get( 'assign_issue' ), 'update_bug_assign_threshold' );
	get_capability_row( lang_get( 'move_issue' ), 'move_bug_threshold', true );
	get_capability_row( lang_get( 'delete_issue' ), 'delete_bug_threshold' );
	get_capability_row( lang_get( 'reopen_issue' ), 'reopen_bug_threshold' );
    get_capability_boolean( lang_get( 'allow_reporter_reopen' ), 'allow_reporter_reopen' );
    get_capability_enum( lang_get( 'reopen_status' ), 'bug_reopen_status', 'status' );
    get_capability_enum( lang_get( 'reopen_resolution' ), 'bug_reopen_resolution', 'resolution' );
    get_capability_enum( lang_get( 'resolved_status' ), 'bug_resolved_status_threshold', 'status' );
    get_capability_enum( lang_get( 'readonly_status' ), 'bug_readonly_status_threshold', 'status' );
	get_capability_row( lang_get( 'update_readonly_issues' ), 'update_readonly_bug_threshold' );
	get_capability_row( lang_get( 'update_issue_status' ), 'update_bug_status_threshold' );
	get_capability_row( lang_get( 'view_private_issues' ), 'private_bug_threshold' );
	get_capability_row( lang_get( 'set_view_status' ), 'set_view_status_threshold' );
	get_capability_row( lang_get( 'update_view_status' ), 'change_view_status_threshold' );
	get_capability_row( lang_get( 'show_list_of_users_monitoring_issue' ), 'show_monitor_list_threshold' );
    get_capability_boolean( lang_get( 'set_status_assigned' ), 'auto_set_status_to_assigned' );
    get_capability_enum( lang_get( 'assigned_status' ), 'bug_assigned_status', 'status' );
    get_capability_boolean( lang_get( 'limit_access' ), 'limit_reporters', true );
	get_section_end();

	# Notes
	get_section_begin_mcwt( lang_get( 'notes' ) );
	get_capability_row( lang_get( 'add_notes' ), 'add_bugnote_threshold' );
	get_capability_row( lang_get( 'update_notes' ), 'update_bugnote_threshold' );
    get_capability_boolean( lang_get( 'allow_user_edit' ), 'bugnote_allow_user_edit_delete' );
	get_capability_row( lang_get( 'delete_note' ), 'delete_bugnote_threshold' );
	get_capability_row( lang_get( 'view_private_notes' ), 'private_bugnote_threshold' );
	get_section_end();

	# Others
	get_section_begin_mcwt( lang_get('others' ) );
	get_capability_row( lang_get( 'view' ) . ' ' . lang_get( 'changelog_link' ), 'view_changelog_threshold' );
	get_capability_row( lang_get( 'view' ) . ' ' . lang_get( 'assigned_to' ), 'view_handler_threshold' );
	get_capability_row( lang_get( 'view' ) . ' ' . lang_get( 'bug_history' ), 'view_history_threshold' );
	get_capability_row( lang_get( 'send_reminders' ), 'bug_reminder_threshold' );
	get_capability_row( lang_get( 'receive_reminders' ), 'reminder_receive_threshold' );
	get_section_end();


    if ( $t_show_submit ) {
        echo "<input type=\"submit\" class=\"button\" value=\"" . lang_get( 'change_configuration' ) . "\" />\n";
    }

	echo "</form>\n";

	if ( $t_show_submit && ( 0 < count( $t_overrides ) ) ) {
        echo "<div class=\"right\"><form name=\"threshold_config_action\" method=\"post\" action=\"manage_config_revert.php\">\n";
		echo form_security_field( 'manage_config_revert' );
        echo "<input name=\"revert\" type=\"hidden\" value=\"" . implode( ',', $t_overrides ) . "\"></input>";
        echo "<input name=\"project\" type=\"hidden\" value=\"$t_project_id\"></input>";
        echo "<input name=\"return\" type=\"hidden\" value=\"" . string_attribute( form_action_self() ) ."\"></input>";
        echo "<input type=\"submit\" class=\"button\" value=\"";
        if ( ALL_PROJECTS == $t_project_id ) {
            echo lang_get( 'revert_to_system' );
        } else {
        echo lang_get( 'revert_to_all_project' );
        }
        echo "\" />\n";
        echo "</form></div>\n";
    }

	html_page_bottom();
