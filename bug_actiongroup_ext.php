<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2008  Mantis Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

	# --------------------------------------------------------
	# $Id: bug_actiongroup_ext.php,v 1.1.2.1 2007-10-13 22:32:32 giallu Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path . 'bug_api.php' );
	require_once( $t_core_path . 'bug_group_action_api.php' );

	# helper_ensure_post();

	auth_ensure_user_authenticated();

	helper_begin_long_process();

	$f_action = gpc_get_string( 'action' );
	$f_bug_arr	= gpc_get_int_array( 'bug_arr', array() );

	$t_action_include_file = 'bug_actiongroup_' . $f_action . '_inc.php';

	require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . $t_action_include_file );

	# group bugs by project
	$t_projects_bugs = array();
	foreach( $f_bug_arr as $t_bug_id ) {
		bug_ensure_exists( $t_bug_id );
		$t_bug = bug_get( $t_bug_id, true );
		
		if ( isset( $t_projects_bugs[$t_bug->project_id] ) ) {
		  $t_projects_bugs[$t_bug->project_id][] = $t_bug_id;
        } else {
		  $t_projects_bugs[$t_bug->project_id] = array( $t_bug_id );
        }
    }
  
    $t_failed_ids = array();
    
    # validate all bugs before we start the processing, we may fail the whole action
    # group, or some of the bugs.
    foreach( $t_projects_bugs as $t_project_id => $t_bug_ids ) {
        if ( $t_bug->project_id != helper_get_current_project() ) {
            # in case the current project is not the same project of the bug we are viewing...
            # ... override the current project. This to avoid problems with categories and handlers lists etc.
            $g_project_override = $t_bug->project_id;
            # @@@ (thraxisp) the next line goes away if the cache was smarter and used project
            config_flush_cache(); # flush the config cache so that configs are refetched
        }

        foreach( $t_bug_ids as $t_bug_id ) {
            $t_result = bug_group_action_validate( $f_action, $t_bug_id );
            if ( $t_result !== true ) {
                foreach( $t_result as $t_key => $t_value ) {
                    $t_failed_ids[$t_key] = $t_value;
                }
            }
        }
    }

    # process bugs that are not already failed by validation.
    foreach( $t_projects_bugs as $t_project_id => $t_bug_ids ) {
		if ( $t_bug->project_id != helper_get_current_project() ) {
			# in case the current project is not the same project of the bug we are viewing...
			# ... override the current project. This to avoid problems with categories and handlers lists etc.
			$g_project_override = $t_bug->project_id;
			# @@@ (thraxisp) the next line goes away if the cache was smarter and used project
			config_flush_cache(); # flush the config cache so that configs are refetched
		}

        foreach( $t_bug_ids as $t_bug_id ) {
            # do not process this bug if validation failed for it.
            if ( !isset( $t_failed_ids[$t_bug_id] ) ) {
                $t_result = bug_group_action_process( $f_action, $t_bug_id );
                if ( $t_result !== true ) {
                    $t_failed_ids[] = $t_result;
                }
            }
        }
    }

	form_security_purge( $t_form_name );

	$t_redirect_url = 'view_all_bug_page.php';

	if ( count( $t_failed_ids ) > 0 ) {
		html_page_top1();
		html_page_top2();

		echo '<div align="center">';
		foreach( $t_failed_ids as $t_id => $t_reason ) {
			printf("<p>%s: %s</p>\n", string_get_bug_view_link( $t_id ), $t_reason );
		}

		print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
		echo '</div>';

		html_page_bottom1( __FILE__ );
	} else {
		print_header_redirect( $t_redirect_url );
	}
?>
