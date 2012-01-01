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
	 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	require_once( 'email_api.php' );

	auth_reauthenticate();

	html_page_top( lang_get( 'manage_workflow_config' ) );

	print_manage_menu( 'adm_permissions_report.php' );
	print_manage_config_menu( 'manage_config_workflow_page.php' );

	$t_access = current_user_get_access_level();
	$t_project = helper_get_current_project();
	$t_can_change_workflow = $t_access >= config_get_access( 'status_enum_workflow' );
	$t_can_change_flags = $t_can_change_workflow;
	$t_overrides = array();

	function set_overrides( $p_config ) {
	   global $t_overrides;
	   if ( !in_array( $p_config, $t_overrides ) ) {
	       $t_overrides[] = $p_config;
	   }
	}

	function parse_workflow( $p_enum_workflow ) {
        $t_status_arr  = MantisEnum::getAssocArrayIndexedByValues( config_get( 'status_enum_string' ) );
        if ( 0 == count( $p_enum_workflow ) ) {
            # workflow is not set, default it to all transitions
	        foreach ( $t_status_arr as $t_status => $t_label ) {
		        $t_temp_workflow = array();
			    foreach ( $t_status_arr as $t_next => $t_next_label ) {
				    if ( $t_status <> $t_next ) {
				       $t_temp_workflow[] = $t_next . ':' . $t_next_label;
				    }
			    }
		        $p_enum_workflow[$t_status] = implode( ',', $t_temp_workflow );
		    }
	    }

    	$t_entry = array();
    	$t_exit = array();

    	# prepopulate new bug state (bugs go from nothing to here)
    	$t_submit_status_array = config_get( 'bug_submit_status' );
		$t_new_label = MantisEnum::getLabel( lang_get( 'status_enum_string' ), config_get( 'bug_submit_status' ) );
    	if ( is_array( $t_submit_status_array ) ) {
    		# @@@ (thraxisp) this is not implemented in bug_api.php
    		foreach ($t_submit_status_array as $t_access => $t_status ) {
    			$t_entry[$t_status][0] = $t_new_label;
    			$t_exit[0][$t_status] = $t_new_label;
    		}
    	} else {
			$t_status = $t_submit_status_array;
			$t_entry[$t_status][0] = $t_new_label;
			$t_exit[0][$t_status] = $t_new_label;
    	}

        # add user defined arcs and implicit reopen arcs
    	$t_reopen = config_get( 'bug_reopen_status' );
		$t_reopen_label = MantisEnum::getLabel( lang_get( 'resolution_enum_string' ), config_get( 'bug_reopen_resolution' ) );
    	$t_resolved_status = config_get( 'bug_resolved_status_threshold' );
    	$t_default = array();
    	foreach ( $t_status_arr as $t_status => $t_status_label ) {
    		if ( isset( $p_enum_workflow[$t_status] ) ) {
    			$t_next_arr = MantisEnum::getAssocArrayIndexedByValues( $p_enum_workflow[$t_status] );
    			foreach ( $t_next_arr as $t_next => $t_next_label) {
                    if ( !isset( $t_default[$t_status] ) ) {
    	                $t_default[$t_status] = $t_next;
    	            }
					$t_exit[$t_status][$t_next] = '';
					$t_entry[$t_next][$t_status] = '';
				}
    		} else {
    			$t_exit[$t_status] = array();
    		}
    		if ( $t_status >= $t_resolved_status ) {
    			$t_exit[$t_status][$t_reopen] = $t_reopen_label;
    			$t_entry[$t_reopen][$t_status] = $t_reopen_label;
    		}
    		if ( !isset( $t_entry[$t_status] ) ) {
    			$t_entry[$t_status] = array();
    		}
    	}
        return array( 'entry' => $t_entry, 'exit' => $t_exit, 'default' => $t_default );
    }


	# Get the value associated with the specific action and flag.
	function show_flag( $p_from_status_id, $p_to_status_id ) {
		global $t_can_change_workflow, $t_overrides, $t_file_workflow, $t_global_workflow, $t_project_workflow, $t_colour_global, $t_colour_project;
		if ( $p_from_status_id <> $p_to_status_id ) {
            $t_file = isset( $t_file_workflow['exit'][$p_from_status_id][$p_to_status_id] ) ? 1 : 0 ;
            $t_global = isset( $t_global_workflow['exit'][$p_from_status_id][$p_to_status_id] ) ? 1 : 0 ;
            $t_project = isset( $t_project_workflow['exit'][$p_from_status_id][$p_to_status_id] ) ? 1 : 0;

            $t_colour = '';
            if ( $t_global != $t_file ) {
                $t_colour = ' bgcolor="' . $t_colour_global . '" '; # all projects override
                if ( $t_can_change_workflow ) {
                    set_overrides( 'status_enum_workflow' );
                }
            }
            if ( $t_project != $t_global ) {
                $t_colour = ' bgcolor="' . $t_colour_project . '" '; # project overrides
                if ( $t_can_change_workflow ) {
                    set_overrides( 'status_enum_workflow' );
                }
            }
            $t_value = '<td class="center"' . $t_colour . '>';

			$t_flag = ( 1 == $t_project );
			$t_label = $t_flag ? $t_project_workflow['exit'][$p_from_status_id][$p_to_status_id] : '';

			if ( $t_can_change_workflow ) {
				$t_flag_name = $p_from_status_id . ':' . $p_to_status_id;
				$t_set = $t_flag ? "checked=\"checked\"" : "";
				$t_value .= "<input type=\"checkbox\" name=\"flag[]\" value=\"$t_flag_name\" $t_set />";
			} else {
				$t_value .= $t_flag ? '<img src="images/ok.gif" width="20" height="15" title="X" alt="X" />' : '&#160;';
			}

			if ( $t_flag && ( '' != $t_label ) ) {
				$t_value .= '<br />(' . $t_label . ')';
			}
		} else {
            $t_value = '<td>&#160;';
		}

		$t_value .= '</td>';

		return $t_value;
	}

	function section_begin( $p_section_name ) {
		$t_enum_statuses = MantisEnum::getValues( config_get( 'status_enum_string' ) );
		echo '<table class="width100">';
		echo '<tr><td class="form-title-caps" colspan="' . ( count( $t_enum_statuses ) + 2 ) . '">'
			. $p_section_name . '</td></tr>' . "\n";
		echo '<tr><td class="form-title" width="30%" rowspan="2">' . lang_get( 'current_status' ) . '</td>';
		echo '<td class="form-title" style="text-align:center" colspan="' . ( count( $t_enum_statuses ) + 1 ) . '">'
			. lang_get( 'next_status' ) . '</td></tr>';
		echo "\n<tr>";

		foreach( $t_enum_statuses as $t_status ) {
			echo '<td class="form-title" style="text-align:center">&#160;' . string_no_break( MantisEnum::getLabel( lang_get( 'status_enum_string' ), $t_status ) ) . '&#160;</td>';
		}

		echo '<td class="form-title" style="text-align:center">' . lang_get( 'custom_field_default_value' ) . '</td>';
		echo '</tr>' . "\n";
	}

	function capability_row( $p_from_status ) {
		global $t_file_workflow, $t_global_workflow, $t_project_workflow, $t_colour_global, $t_colour_project, $t_can_change_workflow;
		$t_enum_status = MantisEnum::getAssocArrayIndexedByValues( config_get( 'status_enum_string' ) );
		echo '<tr ' . helper_alternate_class() . '><td>' . string_no_break( MantisEnum::getLabel( lang_get( 'status_enum_string' ), $p_from_status ) ) . '</td>';
		foreach ( $t_enum_status as $t_to_status_id => $t_to_status_label ) {
			echo show_flag( $p_from_status, $t_to_status_id );
		}

        $t_file = isset( $t_file_workflow['default'][$p_from_status] ) ? $t_file_workflow['default'][$p_from_status] : 0 ;
        $t_global = isset( $t_global_workflow['default'][$p_from_status] ) ? $t_global_workflow['default'][$p_from_status] : 0 ;
        $t_project = isset( $t_project_workflow['default'][$p_from_status] ) ? $t_project_workflow['default'][$p_from_status] : 0;

        $t_colour = '';
        if ( $t_global != $t_file ) {
            $t_colour = ' bgcolor="' . $t_colour_global . '" '; # all projects override
            if ( $t_can_change_workflow ) {
                set_overrides( 'status_enum_workflow' );
            }
        }
        if ( $t_project != $t_global ) {
            $t_colour = ' bgcolor="' . $t_colour_project . '" '; # project overrides
            if ( $t_can_change_workflow ) {
                set_overrides( 'status_enum_workflow' );
            }
        }
		echo '<td class="center"' . $t_colour . '>';
		if ( $t_can_change_workflow ) {
            echo '<select name="default_' . $p_from_status . '">';
            print_enum_string_option_list( 'status', $t_project );
            echo '</select>';
        } else {
            echo MantisEnum::getLabel( lang_get( 'status_enum_string' ), $t_project );
        }
        echo ' </td>';
		echo '</tr>' . "\n";
	}

	function section_end() {
		echo '</table><br />' . "\n";
	}

	function threshold_begin( $p_section_name ) {
		echo '<table class="width100">';
		echo '<tr><td class="form-title" colspan="3">' . $p_section_name . '</td></tr>' . "\n";
		echo '<tr><td class="form-title" width="30%">' . lang_get( 'threshold' ) . '</td>';
		echo '<td class="form-title" >' . lang_get( 'status_level' ) . '</td>';
		echo '<td class="form-title" >' . lang_get( 'alter_level' ) . '</td></tr>';
		echo "\n";
	}

	function threshold_row( $p_threshold ) {
		global $t_access, $t_can_change_flags, $t_colour_project, $t_colour_global;

        $t_file = config_get_global( $p_threshold );
        $t_global = config_get( $p_threshold, null, null, ALL_PROJECTS );
        $t_project = config_get( $p_threshold );
		$t_can_change_threshold = $t_access >= config_get_access( $p_threshold );
        $t_colour = '';
        if ( $t_global != $t_file ) {
            $t_colour = ' bgcolor="' . $t_colour_global . '" '; # all projects override
            if ( $t_can_change_threshold ) {
                set_overrides( $p_threshold );
            }
        }
        if ( $t_project != $t_global ) {
            $t_colour = ' bgcolor="' . $t_colour_project . '" '; # project overrides
            if ( $t_can_change_threshold ) {
                set_overrides( $p_threshold );
            }
        }

		echo '<tr ' . helper_alternate_class() . '><td>' . lang_get( 'desc_' . $p_threshold ) . '</td>';
		if ( $t_can_change_threshold ) {
			echo '<td' . $t_colour . '><select name="threshold_' . $p_threshold . '">';
			print_enum_string_option_list( 'status', $t_project );
			echo '</select> </td>';
			echo '<td><select name="access_' . $p_threshold . '">';
			print_enum_string_option_list( 'access_levels', config_get_access( $p_threshold ) );
			echo '</select> </td>';
			$t_can_change_flags = true;
		} else {
			echo '<td' . $t_colour . '>' . MantisEnum::getLabel( lang_get( 'status_enum_string' ), $t_project ) . '&#160;</td>';
			echo '<td>' . MantisEnum::getLabel( lang_get( 'access_levels_enum_string' ), config_get_access( $p_threshold ) ) . '&#160;</td>';
		}

		echo '</tr>' . "\n";
	}

	function threshold_end() {
		echo '</table><br />' . "\n";
	}

	function access_begin( $p_section_name ) {
		echo '<table class="width100">';
		echo '<tr><td class="form-title" colspan="2">'
			. $p_section_name . '</td></tr>' . "\n";
		echo '<tr><td class="form-title" colspan="2">' . lang_get( 'access_change' ) . '</td></tr>';
	}

	function access_row() {
		global $t_access, $t_can_change_flags, $t_colour_project, $t_colour_global;

		$t_enum_status = MantisEnum::getAssocArrayIndexedByValues( config_get( 'status_enum_string' ) );

		$t_file_new = config_get_global( 'report_bug_threshold' );
		$t_global_new = config_get( 'report_bug_threshold', null, null, ALL_PROJECTS );
		$t_project_new = config_get( 'report_bug_threshold' );

		$t_file_set = config_get_global( 'set_status_threshold' );
		foreach ( $t_enum_status as $t_status => $t_status_label) {
		    if ( !isset( $t_file_set[$t_status] ) ) {
		        $t_file_set[$t_status] = config_get_global('update_bug_status_threshold');
		    }
		}

		$t_global_set = config_get( 'set_status_threshold', null, null, ALL_PROJECTS );
		foreach ( $t_enum_status as $t_status => $t_status_label) {
		    if ( !isset( $t_file_set[$t_status] ) ) {
		        $t_file_set[$t_status] = config_get('update_bug_status_threshold', null, null, ALL_PROJECTS );
		    }
		}

		$t_project_set = config_get( 'set_status_threshold' );
		foreach ( $t_enum_status as $t_status => $t_status_label) {
		    if ( !isset( $t_file_set[$t_status] ) ) {
		        $t_file_set[$t_status] = config_get('update_bug_status_threshold' );
		    }
		}

		foreach ( $t_enum_status as $t_status => $t_status_label) {
			echo '<tr ' . helper_alternate_class() . '><td width="30%">' . string_no_break( MantisEnum::getLabel( lang_get( 'status_enum_string' ), $t_status ) ) . '</td>';
			if ( config_get( 'bug_submit_status' ) == $t_status ) {
				$t_level = $t_project_new;
				$t_can_change = ( $t_access >= config_get_access( 'report_bug_threshold' ) );
                $t_colour = '';
                if ( $t_global_new != $t_file_new ) {
                    $t_colour = ' bgcolor="' . $t_colour_global . '" '; # all projects override
                    if ( $t_can_change ) {
                        set_overrides( 'report_bug_threshold' );
                    }
                }
                if ( $t_project_new != $t_global_new ) {
                    $t_colour = ' bgcolor="' . $t_colour_project . '" '; # project overrides
                    if ( $t_can_change ) {
                        set_overrides( 'report_bug_threshold' );
                    }
                }
			} else {
				$t_level = ( isset( $t_project_set[$t_status] ) ? $t_project_set[$t_status] : false );
				$t_level_global = ( isset( $t_global_set[$t_status] ) ? $t_global_set[$t_status] : false );
				$t_level_file = ( isset( $t_file_set[$t_status] ) ? $t_file_set[$t_status] : false );
				$t_can_change = ( $t_access >= config_get_access( 'set_status_threshold' ) );
                $t_colour = '';
                if ( $t_level_global != $t_level_file ) {
                    $t_colour = ' bgcolor="' . $t_colour_global . '" '; # all projects override
                    if ( $t_can_change ) {
                        set_overrides( 'set_status_threshold' );
                    }
                }
                if ( $t_level != $t_level_global ) {
                    $t_colour = ' bgcolor="' . $t_colour_project . '" '; # project overrides
                    if ( $t_can_change ) {
                        set_overrides( 'set_status_threshold' );
                    }
                }
			}
			if ( $t_can_change ) {
				echo '<td' . $t_colour . '><select name="access_change_' . $t_status . '">';
				print_enum_string_option_list( 'access_levels', $t_level );
				echo '</select> </td>';
			    $t_can_change_flags = true;
			} else {
				echo '<td class="center"' . $t_colour . '>' . MantisEnum::getLabel( lang_get( 'access_levels_enum_string' ), $t_level ) . '</td>';
			}
			echo '</tr>' . "\n";
		}
	}

	echo '<br /><br />';

	# count arcs in and out of each status
	$t_enum_status = config_get( 'status_enum_string' );
	$t_status_arr  = MantisEnum::getAssocArrayIndexedByValues( $t_enum_status );

	$t_extra_enum_status = '0:non-existent,' . $t_enum_status;
	$t_lang_enum_status = '0:' . lang_get( 'non_existent' ) . ',' . lang_get( 'status_enum_string' );
	$t_all_status = explode( ',', $t_extra_enum_status);

	# gather all versions of the workflow
	$t_file_workflow = parse_workflow( config_get_global( 'status_enum_workflow' ) );
	$t_global_workflow = parse_workflow( config_get( 'status_enum_workflow', null, null, ALL_PROJECTS ) );
	$t_project_workflow = parse_workflow( config_get( 'status_enum_workflow' ) );

    # validate the project workflow
	$t_validation_result = '';
	foreach ( $t_status_arr as $t_status => $t_label ) {
		if ( isset( $t_project_workflow['exit'][$t_status][$t_status] ) ) {
			$t_validation_result .= '<tr ' . helper_alternate_class() . '><td>'
							. MantisEnum::getLabel( $t_lang_enum_status, $t_status )
							. '</td><td bgcolor="#FFED4F">' . lang_get( 'superfluous' ) . '</td>';
		}
	}

	# check for entry == 0 without exit == 0, unreachable state
	foreach ( $t_status_arr as $t_status => $t_status_label) {
		if ( ( 0 == count( $t_project_workflow['entry'][$t_status] ) ) && ( 0 < count( $t_project_workflow['exit'][$t_status] ) ) ){
			$t_validation_result .= '<tr ' . helper_alternate_class() . '><td>'
							. MantisEnum::getLabel( $t_lang_enum_status, $t_status )
							. '</td><td bgcolor="#FF0088">' . lang_get( 'unreachable' ) . '</td>';
		}
	}

	# check for exit == 0 without entry == 0, unleaveable state
	foreach ( $t_status_arr as $t_status => $t_status_label ) {
		if ( ( 0 == count( $t_project_workflow['exit'][$t_status] ) ) && ( 0 < count( $t_project_workflow['entry'][$t_status] ) ) ){
			$t_validation_result .= '<tr ' . helper_alternate_class() . '><td>'
							. MantisEnum::getLabel( $t_lang_enum_status, $t_status )
							. '</td><td bgcolor="#FF0088">' . lang_get( 'no_exit' ) . '</td>';
		}
	}

	# check for exit == 0 and entry == 0, isolated state
	foreach ( $t_status_arr as $t_status => $t_status_label ) {
		if ( ( 0 == count( $t_project_workflow['exit'][$t_status] ) ) && ( 0 == count( $t_project_workflow['entry'][$t_status] ) ) ){
			$t_validation_result .= '<tr ' . helper_alternate_class() . '><td>'
							. MantisEnum::getLabel( $t_lang_enum_status, $t_status )
							. '</td><td bgcolor="#FF0088">' . lang_get( 'unreachable' ) . '<br />' . lang_get( 'no_exit' ) . '</td>';
		}
	}

	$t_colour_project = config_get( 'colour_project');
	$t_colour_global = config_get( 'colour_global');

	echo "<form name=\"workflow_config_action\" method=\"post\" action=\"manage_config_workflow_set.php\">\n";
	echo form_security_field( 'manage_config_workflow_set' );

	if ( ALL_PROJECTS == $t_project ) {
	    $t_project_title = lang_get( 'config_all_projects' );
	} else {
	    $t_project_title = sprintf( lang_get( 'config_project' ) , string_display( project_get_name( $t_project ) ) );
	}
	echo '<p class="bold">' . $t_project_title . '</p>' . "\n";
	echo '<p>' . lang_get( 'colour_coding' ) . '<br />';
	if ( ALL_PROJECTS <> $t_project ) {
	    echo '<span style="background-color:' . $t_colour_project . '">' . lang_get( 'colour_project' ) .'</span><br />';
	}
	echo '<span style="background-color:' . $t_colour_global . '">' . lang_get( 'colour_global' ) . '</span></p>';

	# show the settings used to derive the table
	threshold_begin( lang_get( 'workflow_thresholds' ) );
	if ( !is_array( config_get( 'bug_submit_status' ) ) ) {
		threshold_row( 'bug_submit_status' );
	}
	threshold_row( 'bug_resolved_status_threshold' );
	threshold_row( 'bug_reopen_status' );
	threshold_end();
	echo '<br />';

	if ( '' <> $t_validation_result ) {
		echo '<table class="width100">';
		echo '<tr><td class="form-title" colspan="3">' . lang_get( 'validation' ) . '</td></tr>' . "\n";
		echo '<tr><td class="form-title" width="30%">' . lang_get( 'status' ) . '</td>';
		echo '<td class="form-title" >' . lang_get( 'comment' ) . '</td></tr>';
		echo "\n";
		echo $t_validation_result;
		echo '</table><br /><br />';
	}

	# display the graph as a matrix
	section_begin( lang_get( 'workflow' ) );
	foreach ( $t_status_arr as $t_from_status => $t_from_label) {
		capability_row( $t_from_status );
	}
	section_end();

	if ( $t_can_change_workflow ) {
		echo '<p>' . lang_get( 'workflow_change_access' ) . ':';
		echo '<select name="workflow_access">';
		print_enum_string_option_list( 'access_levels', config_get_access( 'status_enum_workflow' ) );
		echo '</select> </p><br />';
	}

	# display the access levels required to move an issue
	access_begin( lang_get( 'access_levels' ) );
	access_row();
	section_end();

	if ( $t_access >= config_get_access( 'set_status_threshold' ) ) {
		echo '<p>' . lang_get( 'access_change_access' ) . ':';
		echo '<select name="status_access">';
		print_enum_string_option_list( 'access_levels', config_get_access( 'set_status_threshold' ) );
		echo '</select> </p><br />';
	}

	if ( $t_can_change_flags ) {
		echo "<input type=\"submit\" class=\"button\" value=\"" . lang_get( 'change_configuration' ) . "\" />\n";
		echo "</form>\n";

        if ( 0 < count( $t_overrides ) ) {
            echo "<div class=\"right\"><form name=\"mail_config_action\" method=\"post\" action=\"manage_config_revert.php\">\n";
			echo form_security_field( 'manage_config_revert' );
            echo "<input name=\"revert\" type=\"hidden\" value=\"" . implode( ',', $t_overrides ) . "\"></input>";
            echo "<input name=\"project\" type=\"hidden\" value=\"$t_project\"></input>";
            echo "<input name=\"return\" type=\"hidden\" value=\"" . string_attribute( form_action_self() ) ."\"></input>";
            echo "<input type=\"submit\" class=\"button\" value=\"";
            if ( ALL_PROJECTS == $t_project ) {
                echo lang_get( 'revert_to_system' );
            } else {
                echo lang_get( 'revert_to_all_project' );
            }
            echo "\" />\n";
            echo "</form></div>\n";
        }

	} else {
        echo "</form>\n";
    }

	html_page_bottom();
