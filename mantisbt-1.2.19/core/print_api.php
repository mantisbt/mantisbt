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
 * this file handles printing functions
 * @package CoreAPI
 * @subpackage PrintAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * requires current_user_api
 */
require_once( 'current_user_api.php' );
/**
 * requires string_api
 */
require_once( 'string_api.php' );
/**
 * requires prepare_api
 */
require_once( 'prepare_api.php' );
/**
 * requires profile_api
 */
require_once( 'profile_api.php' );
/**
 * requires last_visited_api
 */
require_once( 'last_visited_api.php' );
/**
 * requires file_api
 */
require_once( 'file_api.php' );

require_once( 'bug_group_action_api.php' );

# --------------------
# Print the headers to cause the page to redirect to $p_url
# If $p_die is true (default), terminate the execution of the script
#  immediately
# If we have handled any errors on this page and the 'stop_on_errors' config
#  option is turned on, return false and don't redirect.
# $p_sanitize - true/false - true in the case where the URL is extracted from GET/POST or untrusted source.
# This would be false if the URL is trusted (e.g. read from config_inc.php).
#
# @param string The page to redirect: has to be a relative path
# @param boolean if true, stop the script after redirecting
# @param boolean apply string_sanitize_url to passed url
# @return boolean
function print_header_redirect( $p_url, $p_die = true, $p_sanitize = false, $p_absolute = false ) {
	$t_use_iis = config_get( 'use_iis' );

	if( ON == config_get_global( 'stop_on_errors' ) && error_handled() ) {
		return false;
	}

	# validate the url as part of this site before continuing
	if( $p_absolute ) {
		if( $p_sanitize ) {
			$t_url = string_sanitize_url( $p_url );
		} else {
			$t_url = $p_url;
		}
	} else {
		if( $p_sanitize ) {
			$t_url = string_sanitize_url( $p_url, true );
		} else {
			$t_url = config_get( 'path' ) . $p_url;
		}
	}

	$t_url = string_prepare_header( $t_url );

	# don't send more headers if they have already been sent (guideweb)
	if( !headers_sent() ) {
		header( 'Content-Type: text/html; charset=utf-8' );

		if( ON == $t_use_iis ) {
			header( "Refresh: 0;url=$t_url" );
		} else {
			header( "Location: $t_url" );
		}
	} else {
		trigger_error( ERROR_PAGE_REDIRECTION, ERROR );
		return false;
	}

	if( $p_die ) {
		die;

		# additional output can cause problems so let's just stop output here
	}

	return true;
}

# --------------------
# Print a redirect header to view a bug
function print_header_redirect_view( $p_bug_id ) {
	print_header_redirect( string_get_bug_view_url( $p_bug_id ) );
}

# --------------------
# Get a view URL for the bug id based on the user's preference and
#  call print_successful_redirect() with that URL
function print_successful_redirect_to_bug( $p_bug_id ) {
	$t_url = string_get_bug_view_url( $p_bug_id, auth_get_current_user_id() );

	print_successful_redirect( $t_url );
}

# --------------------
# If the show query count is ON, print success and redirect after the
#  configured system wait time.
# If the show query count is OFF, redirect right away.
function print_successful_redirect( $p_redirect_to ) {
	if( helper_show_queries() ) {
		html_page_top( null, $p_redirect_to );
		echo '<br /><div class="center">';
		echo lang_get( 'operation_successful' ) . '<br />';
		print_bracket_link( $p_redirect_to, lang_get( 'proceed' ) );
		echo '</div>';
		html_page_bottom();
	} else {
		print_header_redirect( $p_redirect_to );
	}
}

# Print avatar image for the given user ID
function print_avatar( $p_user_id, $p_size = 80 ) {
	if ( OFF === config_get( 'show_avatar' ) ) {
		return;
	}

	if( !user_exists( $p_user_id ) ) {
		return;
	}

	if( access_has_project_level( config_get( 'show_avatar_threshold' ), null, $p_user_id ) ) {
		$t_avatar = user_get_avatar( $p_user_id, $p_size );
		if( false !== $t_avatar ) {
			$t_avatar_url = htmlspecialchars( $t_avatar[0] );
			$t_width = $t_avatar[1];
			$t_height = $t_avatar[2];
			echo '<a rel="nofollow" href="http://site.gravatar.com"><img class="avatar" src="' . $t_avatar_url . '" alt="User avatar" width="' . $t_width . '" height="' . $t_height . '" /></a>';
		}
	}
}

# --------------------
# prints the name of the user given the id.  also makes it an email link.
function print_user( $p_user_id ) {
	echo prepare_user_name( $p_user_id );
}

# --------------------
# same as print_user() but fills in the subject with the bug summary
function print_user_with_subject( $p_user_id, $p_bug_id ) {
	$c_user_id = db_prepare_int( $p_user_id );

	if( NO_USER == $p_user_id ) {
		return;
	}

	$t_username = user_get_name( $p_user_id );
	if( user_exists( $p_user_id ) && user_get_field( $p_user_id, 'enabled' ) ) {
		$t_email = user_get_email( $p_user_id );
		print_email_link_with_subject( $t_email, $t_username, $p_bug_id );
	} else {
		echo '<font STYLE="text-decoration: line-through">';
		echo $t_username;
		echo '</font>';
	}
}

# --------------------
# print out an email editing input
function print_email_input( $p_field_name, $p_email ) {
	$t_limit_email_domain = config_get( 'limit_email_domain' );
	if( $t_limit_email_domain ) {

		# remove the domain part
		$p_email = preg_replace( '/@' . preg_quote( $t_limit_email_domain, '/' ) . '$/i', '', $p_email );
		echo '<input type="text" name="' . string_attribute( $p_field_name ) . '" size="20" maxlength="64" value="' . string_attribute( $p_email ) . '" />@' . string_display_line( $t_limit_email_domain );
	} else {
		echo '<input type="text" name="' . string_attribute( $p_field_name ) . '" size="32" maxlength="64" value="' . string_attribute( $p_email ) . '" />';
	}
}

# --------------------
# print out an email editing input
function print_captcha_input( $p_field_name ) {
	echo '<input type="text" name="' . $p_field_name . '" size="5" maxlength="5" value="" />';
}

# ##########################################################################
# Option List Printing API
# ##########################################################################


# --------------------
# This populates an option list with the appropriate users by access level
#
# @todo from print_reporter_option_list
function print_user_option_list( $p_user_id, $p_project_id = null, $p_access = ANYBODY ) {
	$t_current_user = auth_get_current_user_id();

	if( null === $p_project_id ) {
		$p_project_id = helper_get_current_project();
	}

	if( $p_project_id === ALL_PROJECTS ) {
		$t_projects = user_get_accessible_projects( $t_current_user );

		# Get list of users having access level for all accessible projects
		$t_users = array();
		foreach( $t_projects as $t_project_id ) {
			$t_project_users_list = project_get_all_user_rows( $t_project_id, $p_access );
			# Do a 'smart' merge of the project's user list, into an
			# associative array (to remove duplicates)
			# Use a while loop for better performance
			$i = 0;
			while( isset( $t_project_users_list[$i] ) ) {
				$t_users[ $t_project_users_list[$i]['id'] ] = $t_project_users_list[$i];
				$i++;
			}
			unset( $t_project_users_list );
		}
		unset( $t_projects );
	} else {
		$t_users = project_get_all_user_rows( $p_project_id, $p_access );
	}

	$t_display = array();
	$t_sort = array();
	$t_show_realname = ( ON == config_get( 'show_realname' ) );
	$t_sort_by_last_name = ( ON == config_get( 'sort_by_last_name' ) );
	foreach( $t_users as $t_key => $t_user ) {
		$t_user_name = string_attribute( $t_user['username'] );
		$t_sort_name = utf8_strtolower( $t_user_name );
		if( $t_show_realname && ( $t_user['realname'] <> '' ) ) {
			$t_user_name = string_attribute( $t_user['realname'] );
			if( $t_sort_by_last_name ) {
				$t_sort_name_bits = explode( ' ', utf8_strtolower( $t_user_name ), 2 );
				$t_sort_name = ( isset( $t_sort_name_bits[1] ) ? $t_sort_name_bits[1] . ', ' : '' ) . $t_sort_name_bits[0];
			} else {
				$t_sort_name = utf8_strtolower( $t_user_name );
			}
		}
		$t_display[] = $t_user_name;
		$t_sort[] = $t_sort_name;
	}
	array_multisort( $t_sort, SORT_ASC, SORT_STRING, $t_users, $t_display );
	unset( $t_sort );
	$t_count = count( $t_users );
	for( $i = 0;$i < $t_count;$i++ ) {
		$t_row = $t_users[$i];
		echo '<option value="' . $t_row['id'] . '" ';
		check_selected( $p_user_id, $t_row['id'] );
		echo '>' . $t_display[$i] . '</option>';
	}
}

# --------------------
# ugly functions  need to be refactored
# This populates the reporter option list with the appropriate users
#
# @todo This function really ought to print out all the users, I think.
#  I just encountered a situation where a project used to be public and
#  was made private, so now I can't filter on any of the reporters who
#  actually reported the bugs at the time. Maybe we could get all user
#  who are listed as the reporter in any bug?  It would probably be a
#  faster query actually.
function print_reporter_option_list( $p_user_id, $p_project_id = null ) {
	print_user_option_list( $p_user_id, $p_project_id, config_get( 'report_bug_threshold' ) );
}

/**
 * Print the entire form for attaching a tag to a bug.
 * @param integer Bug ID
 * @param string Default contents of the input box
 */
function print_tag_attach_form( $p_bug_id, $p_string = '' ) {
	?>
		<small><?php echo sprintf( lang_get( 'tag_separate_by' ), config_get( 'tag_separator' ) )?></small>
		<form method="post" action="tag_attach.php">
		<?php echo form_security_field( 'tag_attach' )?>
		<input type="hidden" name="bug_id" value="<?php echo $p_bug_id?>" />
		<?php
			print_tag_input( $p_bug_id, $p_string );
	?>
		<input type="submit" value="<?php echo lang_get( 'tag_attach' )?>" class="button" />
		</form>
		<?php
		return true;
}

/**
 * Print the separator comment, input box, and existing tag dropdown menu.
 * @param integer Bug ID
 * @param string Default contents of the input box
 */
function print_tag_input( $p_bug_id = 0, $p_string = '' ) {
	?>
		<input type="hidden" id="tag_separator" value="<?php echo config_get( 'tag_separator' )?>" />
		<input type="text" name="tag_string" id="tag_string" size="40" value="<?php echo string_attribute( $p_string )?>" />
		<select <?php echo helper_get_tab_index()?> name="tag_select" id="tag_select" onchange="tag_string_append( this.options[ this.selectedIndex ].title );">
			<?php print_tag_option_list( $p_bug_id );?>
		</select>
		<?php

		return true;
}

/**
 * Print the dropdown combo-box of existing tags.
 * When passed a bug ID, the option list will not contain any tags attached to the given bug.
 * @param integer Bug ID
 */
function print_tag_option_list( $p_bug_id = 0 ) {
	$t_rows = tag_get_candidates_for_bug( $p_bug_id );

	echo '<option value="0">', string_html_specialchars( lang_get( 'tag_existing' ) ), '</option>';
	foreach ( $t_rows as $row ) {
		$t_string = $row['name'];
		if ( !empty( $row['description'] ) ) {
			$t_string .= ' - ' . utf8_substr( $row['description'], 0, 20 );
		}
		echo '<option value="', $row['id'], '" title="', string_attribute( $row['name'] ), '">', string_attribute( $t_string ), '</option>';
	}
}

# --------------------
# Get current headlines and id  prefix with v_
function print_news_item_option_list() {
	$t_mantis_news_table = db_get_table( 'mantis_news_table' );

	$t_project_id = helper_get_current_project();

	$t_global = access_has_global_level( config_get_global( 'admin_site_threshold' ) );
	if( $t_global ) {
		$query = "SELECT id, headline, announcement, view_state
				FROM $t_mantis_news_table
				ORDER BY date_posted DESC";
	} else {
		$query = "SELECT id, headline, announcement, view_state
				FROM $t_mantis_news_table
				WHERE project_id=" . db_param() . "
				ORDER BY date_posted DESC";
	}
	$result = db_query_bound( $query, ($t_global == true ? Array() : Array( $t_project_id ) ) );
	$news_count = db_num_rows( $result );

	for( $i = 0;$i < $news_count;$i++ ) {
		$row = db_fetch_array( $result );

		$t_headline = string_display( $row['headline'] );
		$t_announcement = $row['announcement'];
		$t_view_state = $row['view_state'];
		$t_id = $row['id'];

		$t_notes = array();
		$t_note_string = '';

		if ( 1 == $t_announcement ) {
			array_push( $t_notes, lang_get( 'announcement' ) );
		}

		if ( VS_PRIVATE == $t_view_state ) {
			array_push( $t_notes, lang_get( 'private' ) );
		}

		if ( count( $t_notes ) > 0 ) {
			$t_note_string = ' [' . implode( ' ', $t_notes ) . ']';
		}

		echo "<option value=\"$t_id\">$t_headline$t_note_string</option>";
	}
}

# ---------------
# Constructs the string for one news entry given the row retrieved from the news table.
function print_news_entry( $p_headline, $p_body, $p_poster_id, $p_view_state, $p_announcement, $p_date_posted ) {
	$t_headline = string_display_links( $p_headline );
	$t_body = string_display_links( $p_body );
	$t_date_posted = date( config_get( 'normal_date_format' ), $p_date_posted );

	if( VS_PRIVATE == $p_view_state ) {
		$t_news_css = 'news-heading-private';
	} else {
		$t_news_css = 'news-heading-public';
	}

	$output = '<div align="center">';
	$output .= '<table class="width75" cellspacing="0">';
	$output .= '<tr>';
	$output .= "<td class=\"$t_news_css\">";
	$output .= "<span class=\"bold\">$t_headline</span> - ";
	$output .= "<span class=\"italic-small\">$t_date_posted</span> - ";
	echo $output;

	/** @todo eventually we should replace print's with methods to construct the strings. */
	print_user( $p_poster_id );
	$output = '';

	if( 1 == $p_announcement ) {
		$output .= ' <span class="small">';
		$output .= '[' . lang_get( 'announcement' ) . ']';
		$output .= '</span>';
	}
	if( VS_PRIVATE == $p_view_state ) {
		$output .= ' <span class="small">';
		$output .= '[' . lang_get( 'private' ) . ']';
		$output .= '</span>';
	}

	$output .= '</td>';
	$output .= '</tr>';
	$output .= '<tr>';
	$output .= "<td class=\"news-body\">$t_body</td>";
	$output .= '</tr>';
	$output .= '</table>';
	$output .= '</div>';

	echo $output;
}

# --------------------
# print a news item given a row in the news table.
function print_news_entry_from_row( $p_news_row ) {
	$t_headline = $p_news_row['headline'];
	$t_body = $p_news_row['body'];
	$t_poster_id = $p_news_row['poster_id'];
	$t_view_state = $p_news_row['view_state'];
	$t_announcement = $p_news_row['announcement'];
	$t_date_posted = $p_news_row['date_posted'];

	print_news_entry( $t_headline, $t_body, $t_poster_id, $t_view_state, $t_announcement, $t_date_posted );
}

# --------------------
# print a news item
function print_news_string_by_news_id( $p_news_id ) {
	$row = news_get_row( $p_news_id );

	# only show VS_PRIVATE posts to configured threshold and above
	if(( VS_PRIVATE == $row['view_state'] ) && !access_has_project_level( config_get( 'private_news_threshold' ) ) ) {
		return;
	}

	print_news_entry_from_row( $row );
}

# --------------------
function print_assign_to_option_list( $p_user_id = '', $p_project_id = null, $p_threshold = null ) {

	if( null === $p_threshold ) {
		$p_threshold = config_get( 'handle_bug_threshold' );
	}

	print_user_option_list( $p_user_id, $p_project_id, $p_threshold );
}


function print_note_option_list( $p_user_id = '', $p_project_id = null, $p_threshold = null ) {
	if ( null === $p_threshold ) {
		$p_threshold = config_get( 'add_bugnote_threshold' );
	}

	print_user_option_list( $p_user_id, $p_project_id, $p_threshold );
}

/**
 * List projects that the current user has access to.
 *
 * @param integer $p_project_id 	The current project id or null to use cookie.
 * @param bool $p_include_all_projects  true: include "All Projects", otherwise false.
 * @param mixed $p_filter_project_id  The id of a project to exclude or null.
 * @param string $p_trace  The current project trace, identifies the sub-project via a path from top to bottom.
 * @return void
 */
function print_project_option_list( $p_project_id = null, $p_include_all_projects = true, $p_filter_project_id = null, $p_trace = false ) {
	$t_project_ids = current_user_get_accessible_projects();
	project_cache_array_rows( $t_project_ids );

	if( $p_include_all_projects ) {
		echo '<option value="' . ALL_PROJECTS . '"';
		check_selected( $p_project_id, ALL_PROJECTS );
		echo '>' . lang_get( 'all_projects' ) . '</option>' . "\n";
	}

	$t_project_count = count( $t_project_ids );
	for( $i = 0;$i < $t_project_count;$i++ ) {
		$t_id = $t_project_ids[$i];
		if( $t_id != $p_filter_project_id ) {
			echo '<option value="' . $t_id . '"';
			check_selected( $p_project_id, $t_id );
			echo '>' . string_attribute( project_get_field( $t_id, 'name' ) ) . '</option>' . "\n";
			print_subproject_option_list( $t_id, $p_project_id, $p_filter_project_id, $p_trace, Array() );
		}
	}
}

# --------------------
# List projects that the current user has access to
function print_subproject_option_list( $p_parent_id, $p_project_id = null, $p_filter_project_id = null, $p_trace = false, $p_parents = Array() ) {
	array_push( $p_parents, $p_parent_id );
	$t_project_ids = current_user_get_accessible_subprojects( $p_parent_id );
	$t_project_count = count( $t_project_ids );
	for( $i = 0;$i < $t_project_count;$i++ ) {
		$t_full_id = $t_id = $t_project_ids[$i];
		if( $t_id != $p_filter_project_id ) {
			echo "<option value=\"";
			if( $p_trace ) {
				$t_full_id = join( $p_parents, ";" ) . ';' . $t_id;
			}
			echo $t_full_id . '"';
			check_selected( $p_project_id, $t_full_id );
			echo '>' . str_repeat( '&#160;', count( $p_parents ) ) . str_repeat( '&raquo;', count( $p_parents ) ) . ' ' . string_attribute( project_get_field( $t_id, 'name' ) ) . '</option>' . "\n";
			print_subproject_option_list( $t_id, $p_project_id, $p_filter_project_id, $p_trace, $p_parents );
		}
	}
}

# --------------------
# Print extended project browser
function print_extended_project_browser( $p_trace = Array(), $p_project_id = null ) {
	project_cache_all();
	$t_project_ids = current_user_get_accessible_projects();

	echo '<script type="text/javascript" language="JavaScript">' . "\n";
	echo "<!--\n";
	echo "var subprojects = new Object();\n";

	echo 'function unescapeHTML(html) {' . "\n";
	echo '	var htmlNode = document.createElement("DIV");' . "\n";
	echo '	htmlNode.innerHTML = html;' . "\n";
	echo '	if(htmlNode.innerText)' . "\n";
	echo '		return htmlNode.innerText; // IE' . "\n";
	echo '	return htmlNode.textContent; // FF' . "\n";
	echo '} ' . "\n";

	$t_projects = Array();

	$t_project_count = count( $t_project_ids );
	for( $i = 0;$i < $t_project_count;$i++ ) {
		$t_id = $t_project_ids[$i];
		echo 'subprojects[\'' . $t_id . '\'] = new Object();' . "\n";

		$t_name = project_get_field( $t_id, 'name' );
		$c_name = addslashes( str_replace( array("\r", "\n"), '', $t_name ) );
		echo 'subprojects[\'' . $t_id . '\'][\'' . $t_id . '\'] = \'' . $c_name . '\';' . "\n";

		$t_projects[$t_id] = $t_name;

		print_extended_project_browser_subproject_javascript( $t_id );
	}

	echo "\n";
	echo 'function setProject(projectVal) {' . "\n";
	echo "\t" . 'var spInput = document.form_set_project.project_id;' . "\n";
	echo "\t" . 'spInput.options.length = 0' . "\n";
	echo "\t" . 'if (projectVal == "' . ALL_PROJECTS . '") {' . "\n";
	echo "\t\t" . 'spInput.options[0] = new Option(\'' . lang_get( 'all_projects' ) . '\', \'' . ALL_PROJECTS . '\');' . "\n";
	echo "\t" . '} else {' . "\n";
	echo "\t\t" . 'var i = 0;' . "\n";
	echo "\t\t" . 'var project = subprojects[ projectVal ];' . "\n";
	echo "\t\t" . 'for ( var sp in project ) {' . "\n";
	echo "\t\t\t" . 'spInput.options[ i++ ] = new Option( unescapeHTML(project[sp]), sp );' . "\n";
	echo "\t\t" . '}' . "\n";
	echo "\t" . '}' . "\n";
	echo '}' . "\n";

	echo '// --></script>' . "\n";
	echo '<select name="top_id" onChange="setProject(this.value); document.form_set_project.submit()" class="small">' . "\n";
	echo '<option value="' . ALL_PROJECTS . '"';
	echo check_selected( $p_project_id, ALL_PROJECTS );
	echo '>' . lang_get( 'all_projects' ) . '</option>' . "\n";

	foreach( $t_projects as $t_id => $t_name ) {
		$c_name = string_attribute( $t_name );
		echo '<option value="' . $t_id . '"';
		echo check_selected( $p_project_id, $t_id );
		echo '>' . $c_name . '</option>' . "\n";
	}

	echo '</select>' . "\n";

	if( 0 === count( $p_trace ) ) {
		$t_top_id = ALL_PROJECTS;
	} else {
		$t_top_id = $p_trace[0];
		$t_trace_str = join( ';', $p_trace );
	}

	echo '<select name="project_id" onChange="document.form_set_project.submit()" class="small-subprojects"></select>' . "\n";
	echo '<script type="text/javascript" language="JavaScript">' . "\n";
	echo '<!--' . "\n";
	echo 'document.form_set_project.top_id.value = \'' . $t_top_id . '\';' . "\n";
	echo 'setProject(' . $t_top_id . ');' . "\n";
	echo 'document.form_set_project.project_id.value = \'' . $t_trace_str . '\';' . "\n";
	echo '// --></script>' . "\n";
}

# --------------------
# print the subproject javascript for the extended project browser
function print_extended_project_browser_subproject_javascript( $p_trace ) {
	$t_trace_projects = explode( ';', $p_trace );
	$t_top_id = $t_trace_projects[0];
	$t_level = count( $t_trace_projects );
	$t_parent_id = $t_trace_projects[$t_level - 1];

	$t_project_ids = current_user_get_accessible_subprojects( $t_parent_id );
	$t_project_count = count( $t_project_ids );

	for( $i = 0;$i < $t_project_count;$i++ ) {
		$t_id = $t_project_ids[$i];
		$t_name = addslashes( str_repeat(( '&#160;' ), $t_level ) . str_repeat(( '&raquo;' ), $t_level ) . ' ' . project_get_field( $t_id, 'name' ) );
		echo 'subprojects[\'' . $t_top_id . '\'][\'' . $p_trace . ';' . $t_id . '\'] = \'' . $t_name . '\';' . "\n";

		print_extended_project_browser_subproject_javascript( $p_trace . ';' . $t_id );
	}
}

# --------------------
# prints the profiles given the user id
function print_profile_option_list( $p_user_id, $p_select_id = 0, $p_profiles = null ) {
	if( 0 === $p_select_id ) {
		$p_select_id = profile_get_default( $p_user_id );
	}
	if( $p_profiles != null ) {
		$t_profiles = $p_profiles;
	} else {
		$t_profiles = profile_get_all_for_user( $p_user_id );
	}
	print_profile_option_list_from_profiles( $t_profiles, $p_select_id );
}

# --------------------
# prints the profiles used in a certain project
function print_profile_option_list_for_project( $p_project_id, $p_select_id = 0, $p_profiles = null ) {
	if( 0 === $p_select_id ) {
		$p_select_id = profile_get_default( auth_get_current_user_id() );
	}
	if( $p_profiles != null ) {
		$t_profiles = $p_profiles;
	} else {
		$t_profiles = profile_get_all_for_project( $p_project_id );
	}
	print_profile_option_list_from_profiles( $t_profiles, $p_select_id );
}

# --------------------
# print the profile option list from profiles array
function print_profile_option_list_from_profiles( $p_profiles, $p_select_id ) {
	echo '<option value=""></option>';
	foreach( $p_profiles as $t_profile ) {
		extract( $t_profile, EXTR_PREFIX_ALL, 'v' );

		$t_platform = string_attribute( $t_profile['platform'] );
		$t_os = string_attribute( $t_profile['os'] );
		$t_os_build = string_attribute( $t_profile['os_build'] );

		echo '<option value="' . $t_profile['id'] . '"';
		check_selected( $p_select_id, $t_profile['id'] );
		echo '>' . $t_platform . ' ' . $t_os . ' ' . $t_os_build . '</option>';
	}
}

# --------------------
# Since categories can be orphaned we need to grab all unique instances of category
# We check in the project category table and in the bug table
# We put them all in one array and make sure the entries are unique
function print_category_option_list( $p_category_id = 0, $p_project_id = null ) {
	$t_category_table = db_get_table( 'mantis_category_table' );
	$t_project_table = db_get_table( 'mantis_project_table' );

	if( null === $p_project_id ) {
		$t_project_id = helper_get_current_project();
	} else {
		$t_project_id = $p_project_id;
	}

	if( config_get( 'allow_no_category' ) ) {
		echo "<option value=\"0\"", check_selected( $p_category_id, 0 ), '>';
		echo category_full_name( 0, /* show project */ false ), '</option>';
	} else {
		if( 0 == $p_category_id ) {
			echo "<option value=\"0\"", check_selected( $p_category_id, 0 ), '>';
			echo string_attribute( lang_get( 'select_option' ) ), '</option>';
		}
	}

	$cat_arr = category_get_all_rows( $t_project_id, /* inherit */ null, /* sortByProject */ true );

	foreach( $cat_arr as $t_category_row ) {
		$t_category_id = $t_category_row['id'];
		echo "<option value=\"$t_category_id\"";
		check_selected( $p_category_id, $t_category_id );
		echo '>' . string_attribute( category_full_name( $t_category_id, $t_category_row['project_id'] != $t_project_id ) ) . '</option>';
	}
}

/**
 *	Now that categories are identified by numerical ID, we need an old-style name
 *	based option list to keep existing filter functionality.
 *	@param string $p_category_name The selected category
 *	@param mixed $p_project_id A specific project or null
 */
function print_category_filter_option_list( $p_category_name = '', $p_project_id = null ) {
	$t_cat_arr = category_get_filter_list( $p_project_id );

	natcasesort( $t_cat_arr );
	foreach( $t_cat_arr as $t_cat ) {
		$t_name = string_attribute( $t_cat );
		echo '<option value="' . $t_name . '"';
		check_selected( string_attribute( $p_category_name ), $t_name );
		echo '>' . $t_name . '</option>';
	}
}

# --------------------
# Print the option list for platforms accessible for the specified user.
function print_platform_option_list( $p_platform, $p_user_id = null ) {
	$t_platforms_array = profile_get_field_all_for_user( 'platform', $p_user_id );

	foreach( $t_platforms_array as $t_platform ) {
		$t_platform = string_attribute( $t_platform );
		echo '<option value="' . $t_platform . '"';
		check_selected( string_attribute( $p_platform ), $t_platform );
		echo '>' . $t_platform . '</option>';
	}
}

# --------------------
# Print the option list for OSes accessible for the specified user.
function print_os_option_list( $p_os, $p_user_id = null ) {
	$t_os_array = profile_get_field_all_for_user( 'os', $p_user_id );

	foreach( $t_os_array as $t_os ) {
		$t_os = string_attribute( $t_os );
		echo '<option value="' . $t_os . '"';
		check_selected( string_attribute( $p_os ), $t_os );
		echo '>' . $t_os . '</option>';
	}
}

# Print the option list for os_build accessible for the specified user.
function print_os_build_option_list( $p_os_build, $p_user_id = null ) {
	$t_os_build_array = profile_get_field_all_for_user( 'os_build', $p_user_id );

	foreach( $t_os_build_array as $t_os_build ) {
		$t_os_build = string_attribute( $t_os_build );
		echo '<option value="' . $t_os_build . '"';
		check_selected( string_attribute( $p_os_build ), $t_os_build );
		echo '>' . $t_os_build . '</option>';
	}
}

# Print the option list for versions
# $p_version = currently selected version.
# $p_project_id = project id, otherwise current project will be used.
# $p_released = null to get all, 1: only released, 0: only future versions
# $p_leading_black = allow selection of no version
# $p_with_subs = include subprojects
function print_version_option_list( $p_version = '', $p_project_id = null, $p_released = null, $p_leading_blank = true, $p_with_subs = false ) {
	if( null === $p_project_id ) {
		$c_project_id = helper_get_current_project();
	} else {
		$c_project_id = db_prepare_int( $p_project_id );
	}

	if( $p_with_subs ) {
		$versions = version_get_all_rows_with_subs( $c_project_id, $p_released,
		/* obsolete */
		null );
	} else {
		$versions = version_get_all_rows( $c_project_id, $p_released,
		/* obsolete */
		null );
	}

	# Ensure the selected version (if specified) is included in the list
	# Note: Filter API specifies selected versions as an array
	if( !is_array( $p_version ) ) {
		if( !empty( $p_version ) ) {
			$t_version_id = version_get_id( $p_version, $c_project_id );
			if( $t_version_id !== false ) {
				$versions[] = version_cache_row( $t_version_id );
			}
		}
	}

	if( $p_leading_blank ) {
		echo '<option value=""></option>';
	}

	$t_listed = array();
	$t_max_length = config_get( 'max_dropdown_length' );
	$t_show_version_dates = access_has_project_level( config_get( 'show_version_dates_threshold' ) );
	$t_short_date_format = config_get( 'short_date_format' );

	foreach( $versions as $version ) {
		# If the current version is obsolete, and current version not equal to $p_version,
		# then skip it.
		if(( (int) $version['obsolete'] ) == 1 ) {
			if( $version['version'] != $p_version ) {
				continue;
			}
		}

		$t_version = string_attribute( $version['version'] );

		if ( !in_array( $t_version, $t_listed, true ) ) {
			$t_listed[] = $t_version;
			echo '<option value="' . $t_version . '"';
			check_selected( string_attribute( $p_version ), $t_version );

			$t_version_string = string_attribute( prepare_version_string( $c_project_id, $version['id'] ) );

			echo '>', string_shorten( $t_version_string , $t_max_length ), '</option>';
		}
	}
}

function print_build_option_list( $p_build = '' ) {
	$t_bug_table = db_get_table( 'mantis_bug_table' );
	$t_overall_build_arr = array();

	$t_project_id = helper_get_current_project();

	$t_project_where = helper_project_specific_where( $t_project_id );

	# Get the "found in" build list
	$query = "SELECT DISTINCT build
				FROM $t_bug_table
				WHERE $t_project_where
				ORDER BY build DESC";
	$result = db_query_bound( $query );
	$option_count = db_num_rows( $result );

	for( $i = 0;$i < $option_count;$i++ ) {
		$row = db_fetch_array( $result );
		$t_overall_build_arr[] = $row['build'];
	}

	$t_max_length = config_get( 'max_dropdown_length' );

	foreach( $t_overall_build_arr as $t_build ) {
		$t_build = string_attribute( $t_build );
		echo "<option value=\"$t_build\"";
		check_selected( string_attribute( $p_build ), $t_build );
		echo ">" . string_shorten( $t_build, $t_max_length ) . "</option>";
	}
}

# select the proper enum values based on the input parameter
# $p_enum_name - name of enumeration (eg: status)
# $p_val: current value
function print_enum_string_option_list( $p_enum_name, $p_val = 0 ) {
	$t_config_var_name = $p_enum_name . '_enum_string';
	$t_config_var_value = config_get( $t_config_var_name );

	$t_enum_values = MantisEnum::getValues( $t_config_var_value );

	foreach ( $t_enum_values as $t_key ) {
		$t_elem2 = get_enum_element( $p_enum_name, $t_key );

		echo '<option value="' . $t_key . '"';
		check_selected( $p_val, $t_key );
		echo '>' . $t_elem2 . '</option>';
	}
}

/**
 * Returns a list of valid status options based on workflow
 * @param int $p_user_auth User's access level
 * @param int $p_current_value Current issue's status
 * @param bool $p_show_current Add current status to return list
 * @param bool $p_add_close Add 'closed' to return list
 * @param int $p_project_id
 * @return array
 */
function get_status_option_list( $p_user_auth = 0, $p_current_value = 0, $p_show_current = true, $p_add_close = false, $p_project_id = ALL_PROJECTS ) {
	$t_config_var_value = config_get( 'status_enum_string', null, null, $p_project_id );
	$t_enum_workflow = config_get( 'status_enum_workflow', null, null, $p_project_id );

	if( count( $t_enum_workflow ) < 1 ) {
		# workflow not defined, use default enum
		$t_enum_values = MantisEnum::getValues( $t_config_var_value );
	} else {
		# workflow defined - find allowed states
		if( isset( $t_enum_workflow[$p_current_value] ) ) {
			$t_enum_values = MantisEnum::getValues( $t_enum_workflow[$p_current_value] );
		} else {
			# workflow was not set for this status, this shouldn't happen
			# caller should be able to handle empty list
			$t_enum_values = array();
		}
	}
	$t_enum_list = array();

	foreach ( $t_enum_values as $t_enum_value ) {
		if (   ( $p_show_current || $p_current_value != $t_enum_value )
			&& access_compare_level( $p_user_auth, access_get_status_threshold( $t_enum_value, $p_project_id ) )
		) {
			$t_enum_list[$t_enum_value] = get_enum_element( 'status', $t_enum_value );
		}
	}

	if ( $p_show_current ) {
		$t_enum_list[$p_current_value] = get_enum_element( 'status', $p_current_value );
	}

	if ( $p_add_close && access_compare_level( $p_current_value, config_get( 'bug_resolved_status_threshold', null, null, $p_project_id ) ) ) {
		$t_closed = config_get( 'bug_closed_status_threshold', null, null, $p_project_id );
		if( $p_show_current || $p_current_value != $t_closed ) {
			$t_enum_list[$t_closed] = get_enum_element( 'status', $t_closed );
		}
	}

	return $t_enum_list;
}

# print the status option list for the bug_update pages
function print_status_option_list( $p_select_label, $p_current_value = 0, $p_allow_close = false, $p_project_id = ALL_PROJECTS ) {
	$t_current_auth = access_get_project_level( $p_project_id );

	$t_enum_list = get_status_option_list( $t_current_auth, $p_current_value, true, $p_allow_close, $p_project_id );

	if( count( $t_enum_list ) > 1 ) {

		# resort the list into ascending order
		ksort( $t_enum_list );
		reset( $t_enum_list );
		echo '<select ', helper_get_tab_index(), ' name="' . $p_select_label . '">';
		foreach( $t_enum_list as $key => $val ) {
			echo '<option value="' . $key . '"';
			check_selected( $key, $p_current_value );
			echo '>' . $val . '</option>';
		}
		echo '</select>';
	} else if ( count( $t_enum_list ) == 1 ) {
		echo array_pop( $t_enum_list );
	} else {
		echo MantisEnum::getLabel( lang_get( 'status_enum_string' ), $p_current_value );
	}
}

# prints the list of a project's users
# if no project is specified uses the current project
function print_project_user_option_list( $p_project_id = null ) {
	print_user_option_list( 0, $p_project_id );
}

# prints the list of access levels that are less than or equal to the access level of the
# logged in user.  This is used when adding users to projects
function print_project_access_levels_option_list( $p_val, $p_project_id = null ) {
	$t_current_user_access_level = access_get_project_level( $p_project_id );
	$t_access_levels_enum_string = config_get( 'access_levels_enum_string' );
	$t_enum_values = MantisEnum::getValues( $t_access_levels_enum_string );
	foreach ( $t_enum_values as $t_enum_value ) {
		# a user must not be able to assign another user an access level that is higher than theirs.
		if ( $t_enum_value > $t_current_user_access_level ) {
			continue;
		}
		$t_access_level = get_enum_element( 'access_levels', $t_enum_value );
		echo '<option value="' . $t_enum_value . '"';
		check_selected( $p_val, $t_enum_value );
		echo '>' . $t_access_level . '</option>';
	}
}

function print_language_option_list( $p_language ) {
	$t_arr = config_get( 'language_choices_arr' );
	$enum_count = count( $t_arr );
	for( $i = 0;$i < $enum_count;$i++ ) {
		$t_language = string_attribute( $t_arr[$i] );
		echo '<option value="' . $t_language . '"';
		check_selected( $t_language, $p_language );
		echo '>' . $t_language . '</option>';
	}
}

/**
 * Print a dropdown list of all bug actions available to a user for a specified
 * set of projects.
 * @param array $p_projects An array containing one or more project IDs
 * @return null
 */
function print_all_bug_action_option_list( $p_project_ids = null ) {
	$t_commands = bug_group_action_get_commands( $p_project_ids);
	while( list( $t_action_id, $t_action_label ) = each( $t_commands ) ) {
		echo '<option value="' . $t_action_id . '">' . $t_action_label . '</option>';
	}
}

# --------------------
# list of users that are NOT in the specified project and that are enabled
# if no project is specified use the current project
# also exclude any administrators
function print_project_user_list_option_list( $p_project_id = null ) {
	$t_mantis_project_user_list_table = db_get_table( 'mantis_project_user_list_table' );
	$t_mantis_user_table = db_get_table( 'mantis_user_table' );

	if( null === $p_project_id ) {
		$p_project_id = helper_get_current_project();
	}
	$c_project_id = (int) $p_project_id;

	$t_adm = config_get_global( 'admin_site_threshold' );
	$query = "SELECT DISTINCT u.id, u.username, u.realname
				FROM $t_mantis_user_table u
				LEFT JOIN $t_mantis_project_user_list_table p
				ON p.user_id=u.id AND p.project_id=" . db_param() . "
				WHERE u.access_level<" . db_param() . " AND
					u.enabled = " . db_param() . " AND
					p.user_id IS NULL
				ORDER BY u.realname, u.username";
	$result = db_query_bound( $query, Array( $c_project_id, $t_adm, true ) );
	$t_display = array();
	$t_sort = array();
	$t_users = array();
	$t_show_realname = ( ON == config_get( 'show_realname' ) );
	$t_sort_by_last_name = ( ON == config_get( 'sort_by_last_name' ) );
	$category_count = db_num_rows( $result );
	for( $i = 0;$i < $category_count;$i++ ) {
		$row = db_fetch_array( $result );
		$t_users[] = $row['id'];
		$t_user_name = string_attribute( $row['username'] );
		$t_sort_name = $t_user_name;
		if(( isset( $row['realname'] ) ) && ( $row['realname'] <> '' ) && $t_show_realname ) {
			$t_user_name = string_attribute( $row['realname'] );
			if( $t_sort_by_last_name ) {
				$t_sort_name_bits = explode( ' ', utf8_strtolower( $t_user_name ), 2 );
				$t_sort_name = ( isset( $t_sort_name_bits[1] ) ? $t_sort_name_bits[1] . ', ' : '' ) . $t_sort_name_bits[0];
			} else {
				$t_sort_name = utf8_strtolower( $t_user_name );
			}
		}
		$t_display[] = $t_user_name;
		$t_sort[] = $t_sort_name;
	}
	array_multisort( $t_sort, SORT_ASC, SORT_STRING, $t_users, $t_display );
	$t_count = count( $t_sort );
	for( $i = 0;$i < $t_count; $i++ ) {
		echo '<option value="' . $t_users[$i] . '">' . $t_display[$i] . '</option>';
	}
}

# list of projects that a user is NOT in
function print_project_user_list_option_list2( $p_user_id ) {
	$t_mantis_project_user_list_table = db_get_table( 'mantis_project_user_list_table' );
	$t_mantis_project_table = db_get_table( 'mantis_project_table' );

	$c_user_id = db_prepare_int( $p_user_id );

	$query = "SELECT DISTINCT p.id, p.name
				FROM $t_mantis_project_table p
				LEFT JOIN $t_mantis_project_user_list_table u
				ON p.id=u.project_id AND u.user_id=" . db_param() . "
				WHERE p.enabled = " . db_param() . " AND
					u.user_id IS NULL
				ORDER BY p.name";
	$result = db_query_bound( $query, Array( $c_user_id, true ) );
	$category_count = db_num_rows( $result );
	for( $i = 0;$i < $category_count;$i++ ) {
		$row = db_fetch_array( $result );
		$t_project_name = string_attribute( $row['name'] );
		$t_user_id = $row['id'];
		echo "<option value=\"$t_user_id\">$t_project_name</option>";
	}
}

# list of projects that a user is in
function print_project_user_list( $p_user_id, $p_include_remove_link = true ) {
	$t_mantis_project_user_list_table = db_get_table( 'mantis_project_user_list_table' );
	$t_mantis_project_table = db_get_table( 'mantis_project_table' );

	$c_user_id = db_prepare_int( $p_user_id );

	$query = "SELECT DISTINCT p.id, p.name, p.view_state, u.access_level
				FROM $t_mantis_project_table p
				LEFT JOIN $t_mantis_project_user_list_table u
				ON p.id=u.project_id
				WHERE p.enabled = '1' AND
					u.user_id=" . db_param() . "
				ORDER BY p.name";
	$result = db_query_bound( $query, Array( $c_user_id ) );
	$category_count = db_num_rows( $result );
	for( $i = 0;$i < $category_count;$i++ ) {
		$row = db_fetch_array( $result );
		$t_project_id = $row['id'];
		$t_project_name = string_attribute( $row['name'] );
		$t_view_state = $row['view_state'];
		$t_access_level = $row['access_level'];
		$t_access_level = get_enum_element( 'access_levels', $t_access_level );
		$t_view_state = get_enum_element( 'project_view_state', $t_view_state );

		echo $t_project_name . ' [' . $t_access_level . '] (' . $t_view_state . ')';
		if( $p_include_remove_link && access_has_project_level( config_get( 'project_user_threshold' ), $t_project_id ) ) {
			html_button( 'manage_user_proj_delete.php', lang_get( 'remove_link' ), array( 'project_id' => $t_project_id, 'user_id' => $p_user_id ) );
		}
		echo '<br />';
	}
}

# List of projects with which the specified field id is linked.
# For every project, the project name is listed and then the list of custom
# fields linked in order with their sequence numbers.  The specified field
# is always highlighted in italics and project names in bold.
#
# $p_field_id - The field to list the projects associated with.
function print_custom_field_projects_list( $p_field_id ) {
	$c_field_id = (integer) $p_field_id;
	$t_project_ids = custom_field_get_project_ids( $p_field_id );

	$t_security_token = form_security_param( 'manage_proj_custom_field_remove' );

	foreach( $t_project_ids as $t_project_id ) {
		$t_project_name = project_get_field( $t_project_id, 'name' );
		$t_sequence = custom_field_get_sequence( $p_field_id, $t_project_id );
		echo '<b>', string_display_line( $t_project_name ), '</b>: ';
		print_bracket_link( "manage_proj_custom_field_remove.php?field_id=$c_field_id&project_id=$t_project_id&return=custom_field$t_security_token", lang_get( 'remove_link' ) );
		echo '<br />- ';

		$t_linked_field_ids = custom_field_get_linked_ids( $t_project_id );

		$t_current_project_fields = array();

		$t_first = true;
		foreach( $t_linked_field_ids as $t_current_field_id ) {
			if( $t_first ) {
				$t_first = false;
			} else {
				echo ', ';
			}

			if( $t_current_field_id == $p_field_id ) {
				echo '<em>';
			}

			echo string_display_line( custom_field_get_field( $t_current_field_id, 'name' ) );
			echo ' (', custom_field_get_sequence( $t_current_field_id, $t_project_id ), ')';

			if( $t_current_field_id == $p_field_id ) {
				echo '</em>';
			}
		}

		echo '<br /><br />';
	}
}

/**
 * List of priorities that can be assigned to a plugin.
 * @param int current priority
 */
function print_plugin_priority_list( $p_priority ) {
	if( $p_priority < 1 && $p_priority > 5 ) {
		echo '<option value="', $p_priority, '" selected="selected">', $p_priority, '</option>';
	}

	for( $i = 5;$i >= 1;$i-- ) {
		echo '<option value="', $i, '" ', check_selected( $p_priority, $i ), ' >', $i, '</option>';
	}
}

# ##########################################################################
# String printing API
# ##########################################################################

# prints a link to VIEW a bug given an ID
#  account for the user preference and site override
function print_bug_link( $p_bug_id, $p_detail_info = true ) {
	echo string_get_bug_view_link( $p_bug_id, null, $p_detail_info );
}

# formats the priority given the status
# shows the priority in BOLD if the bug is NOT closed and is of significant priority
function print_formatted_priority_string( $p_bug ) {
	$t_pri_str = get_enum_element( 'priority', $p_bug->priority, auth_get_current_user_id(), $p_bug->project_id );
	$t_priority_threshold = config_get( 'priority_significant_threshold' );

	if( $t_priority_threshold >= 0 &&
		$p_bug->priority >= $t_priority_threshold &&
		$p_bug->status < config_get( 'bug_closed_status_threshold' ) ) {
		echo "<span class=\"bold\">$t_pri_str</span>";
	} else {
		echo $t_pri_str;
	}
}

# formats the severity given the status
# shows the severity in BOLD if the bug is NOT closed and is of significant severity
function print_formatted_severity_string( $p_bug ) {
	$t_sev_str = get_enum_element( 'severity', $p_bug->severity, auth_get_current_user_id(), $p_bug->project_id );
	$t_severity_threshold = config_get( 'severity_significant_threshold' );

	if( $t_severity_threshold >= 0 &&
		$p_bug->severity >= $t_severity_threshold &&
		$p_bug->status < config_get( 'bug_closed_status_threshold' ) ) {
		echo "<span class=\"bold\">$t_sev_str</span>";
	} else {
		echo $t_sev_str;
	}
}

# ##########################################################################
# Link Printing API
# ##########################################################################

# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
function print_view_bug_sort_link( $p_string, $p_sort_field, $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	if( $p_columns_target == COLUMNS_TARGET_PRINT_PAGE ) {
		if( $p_sort_field == $p_sort ) {
			# We toggle between ASC and DESC if the user clicks the same sort order
			if( 'ASC' == $p_dir ) {
				$p_dir = 'DESC';
			} else {
				$p_dir = 'ASC';
			}
		} else {
			# Otherwise always start with ASCending
			$t_dir = 'ASC';
		}

		$t_sort_field = rawurlencode( $p_sort_field );
		print_link( "view_all_set.php?sort=$t_sort_field&dir=$p_dir&type=2&print=1", $p_string );
	}
	else if( $p_columns_target == COLUMNS_TARGET_VIEW_PAGE ) {
		if( $p_sort_field == $p_sort ) {

			# we toggle between ASC and DESC if the user clicks the same sort order
			if( 'ASC' == $p_dir ) {
				$p_dir = 'DESC';
			} else {
				$p_dir = 'ASC';
			}
		} else {
			# Otherwise always start with ASCending
			$t_dir = 'ASC';
		}

		$t_sort_field = rawurlencode( $p_sort_field );
		print_link( "view_all_set.php?sort=$t_sort_field&dir=$p_dir&type=2", $p_string );
	} else {
		echo $p_string;
	}
}

function print_manage_user_sort_link( $p_page, $p_string, $p_field, $p_dir, $p_sort_by, $p_hide_inactive = 0, $p_filter = ALL, $p_show_disabled = 0 ) {
	if( $p_sort_by == $p_field ) {

		# If this is the selected field flip the order
		if( 'ASC' == $p_dir || ASCENDING == $p_dir ) {
			$t_dir = 'DESC';
		} else {
			$t_dir = 'ASC';
		}
	} else {
		# Otherwise always start with ASCending
		$t_dir = 'ASC';
	}

	$t_field = rawurlencode( $p_field );
	print_link( "$p_page?sort=$t_field&dir=$t_dir&save=1&hideinactive=$p_hide_inactive&showdisabled=$p_show_disabled&filter=$p_filter", $p_string );
}

function print_manage_project_sort_link( $p_page, $p_string, $p_field, $p_dir, $p_sort_by ) {
	if( $p_sort_by == $p_field ) {

		# If this is the selected field flip the order
		if( 'ASC' == $p_dir || ASCENDING == $p_dir ) {
			$t_dir = 'DESC';
		} else {
			$t_dir = 'ASC';
		}
	} else {
		# Otherwise always start with ASCending
		$t_dir = 'ASC';
	}

	$t_field = rawurlencode( $p_field );
	print_link( "$p_page?sort=$t_field&dir=$t_dir", $p_string );
}

/**
 * Print a button which presents a standalone form.
 * If $p_security_token is OFF, the button's form will not contain a security
 * field; this is useful when form does not result in modifications (CSRF is not
 * needed). If otherwise specified (i.e. not null), the parameter must contain
 * a valid security token, previously generated by form_security_token().
 * Use this to avoid performance issues when loading pages having many calls to
 * this function, such as adm_config_report.php.
 * @param string $p_action_page The action page
 * @param string $p_label The button label
 * @param array $p_args_to_post Associative array of arguments to be posted, with
 *                              arg name => value, defaults to null (no args)
 * @param mixed $p_security_token Optional; null (default), OFF or security token string
 * @see form_security_token()
 */
function print_button( $p_action_page, $p_label, $p_args_to_post = null, $p_security_token = null ) {
	$t_form_name = explode( '.php', $p_action_page, 2 );
	# TODO: ensure all uses of print_button supply arguments via $p_args_to_post (POST)
	# instead of via $p_action_page (GET). Then only add the CSRF form token if
	# arguments are being sent via the POST method.
	echo '<form method="post" action="', htmlspecialchars( $p_action_page ), '">';
	if( $p_security_token !== OFF ) {
		echo form_security_field( $t_form_name[0], $p_security_token );
	}
	echo '<input type="submit" class="button-small" value="', $p_label, '" />';

	if( $p_args_to_post !== null ) {
		foreach( $p_args_to_post as $t_var => $t_value ) {
			echo '<input type="hidden" name="' . $t_var .
				'" value="' . htmlentities( $t_value ) . '" />';
		}
	}

	echo '</form>';
}

# print brackets around a pre-prepared link (i.e. '<a href' html tag).
function print_bracket_link_prepared( $p_link ) {
	echo '<span class="bracket-link">[&#160;' . $p_link . '&#160;]</span> ';
}

# print the bracketed links used near the top
# if the $p_link is blank then the text is printed but no link is created
# if $p_new_window is true, link will open in a new window, default false.
function print_bracket_link( $p_link, $p_url_text, $p_new_window = false, $p_class = '' ) {
	echo '<span class="bracket-link">[&#160;';
	print_link( $p_link, $p_url_text, $p_new_window, $p_class );
	echo '&#160;]</span> ';
}

# print a HTML link
function print_link( $p_link, $p_url_text, $p_new_window = false, $p_class = '' ) {
	if( is_blank( $p_link ) ) {
		echo $p_url_text;
	} else {
		$t_link = htmlspecialchars( $p_link );
		if( $p_new_window === true ) {
			if( $p_class !== '') {
				echo "<a class=\"$p_class\" href=\"$t_link\" target=\"_blank\">$p_url_text</a>";
			} else {
				echo "<a href=\"$t_link\" target=\"_blank\">$p_url_text</a>";
			}
		} else {
			if( $p_class !== '') {
				echo "<a class=\"$p_class\" href=\"$t_link\">$p_url_text</a>";
			} else {
				echo "<a href=\"$t_link\">$p_url_text</a>";
			}
		}
	}
}

# print a HTML page link
function print_page_link( $p_page_url, $p_text = '', $p_page_no = 0, $p_page_cur = 0, $p_temp_filter_id = 0 ) {
	if( is_blank( $p_text ) ) {
		$p_text = $p_page_no;
	}

	if( ( 0 < $p_page_no ) && ( $p_page_no != $p_page_cur ) ) {
		$t_delimiter = ( strpos( $p_page_url, "?" ) ? "&" : "?" );
		if( $p_temp_filter_id !== 0 ) {
			print_link( "$p_page_url${t_delimiter}filter=$p_temp_filter_id&page_number=$p_page_no", $p_text );
		} else {
			print_link( "$p_page_url${t_delimiter}page_number=$p_page_no", $p_text );
		}
	} else {
		echo $p_text;
	}
}

# print a list of page number links (eg [1 2 3])
function print_page_links( $p_page, $p_start, $p_end, $p_current, $p_temp_filter_id = 0 ) {
	$t_items = array();
	$t_link = '';

	# Check if we have more than one page,
	#  otherwise return without doing anything.

	if( $p_end - $p_start < 1 ) {
		return;
	}

	# Get localized strings
	$t_first = lang_get( 'first' );
	$t_last = lang_get( 'last' );
	$t_prev = lang_get( 'prev' );
	$t_next = lang_get( 'next' );

	$t_page_links = 10;

	print( "[ " );

	# First and previous links
	print_page_link( $p_page, $t_first, 1, $p_current, $p_temp_filter_id );
	echo '&#160;';
	print_page_link( $p_page, $t_prev, $p_current - 1, $p_current, $p_temp_filter_id );
	echo '&#160;';

	# Page numbers ...

	$t_first_page = max( $p_start, $p_current - $t_page_links / 2 );
	$t_first_page = min( $t_first_page, $p_end - $t_page_links );
	$t_first_page = max( $t_first_page, $p_start );

	if( $t_first_page > 1 ) {
		print( " ... " );
	}

	$t_last_page = $t_first_page + $t_page_links;
	$t_last_page = min( $t_last_page, $p_end );

	for( $i = $t_first_page;$i <= $t_last_page;$i++ ) {
		if( $i == $p_current ) {
			array_push( $t_items, $i );
		} else {
			$t_delimiter = ( strpos( $p_page, "?" ) ? "&" : "?" ) ;
			if( $p_temp_filter_id !== 0 ) {
				array_push( $t_items, "<a href=\"$p_page${t_delimiter}filter=$p_temp_filter_id&page_number=$i\">$i</a>" );
			} else {
				array_push( $t_items, "<a href=\"$p_page${t_delimiter}page_number=$i\">$i</a>" );
			}
		}
	}
	echo implode( '&#160;', $t_items );

	if( $t_last_page < $p_end ) {
		print( ' ... ' );
	}

	# Next and Last links
	echo '&#160;';
	if( $p_current < $p_end ) {
		print_page_link( $p_page, $t_next, $p_current + 1, $p_current, $p_temp_filter_id );
	} else {
		print_page_link( $p_page, $t_next, null, null, $p_temp_filter_id );
	}
	echo '&#160;';
	print_page_link( $p_page, $t_last, $p_end, $p_current, $p_temp_filter_id );

	print( ' ]' );
}

# print a mailto: href link
function print_email_link( $p_email, $p_text ) {
	echo get_email_link( $p_email, $p_text );
}

# return the mailto: href string link instead of printing it
function get_email_link( $p_email, $p_text ) {
	return prepare_email_link( $p_email, $p_text );
}

# print a mailto: href link with subject
function print_email_link_with_subject( $p_email, $p_text, $p_bug_id ) {
	$t_subject = email_build_subject( $p_bug_id );
	echo get_email_link_with_subject( $p_email, $p_text, $t_subject );
}

# return the mailto: href string link instead of printing it
# add subject line
function get_email_link_with_subject( $p_email, $p_text, $p_summary ) {
	if( !access_has_project_level( config_get( 'show_user_email_threshold' ) ) ) {
		return $p_text;
	}

	# If we apply string_url() to the whole mailto: link then the @
	#  gets turned into a %40 and you can't right click in browsers to
	#  do Copy Email Address.  If we don't apply string_url() to the
	#  summary text then an ampersand (for example) will truncate the text
	$t_summary = string_url( $p_summary );
	$t_email = string_url( $p_email );
	$t_mailto = string_attribute( "mailto:$t_email?subject=$t_summary" );
	$t_text = string_display( $p_text );

	return "<a href=\"$t_mailto\">$t_text</a>";
}

# Print a hidden input for each name=>value pair in the array
#
# If a value is an array an input will be created for each item with a name
#  that ends with []
# The names and values are passed through htmlspecialchars() before being displayed
function print_hidden_inputs( $p_assoc_array ) {
	foreach( $p_assoc_array as $t_key => $t_val ) {
		print_hidden_input( $t_key, $t_val );
	}
}

function print_hidden_input( $p_field_key, $p_field_val ) {
	if( is_array( $p_field_val ) ) {
		foreach( $p_field_val AS $t_key => $t_value ) {
			if( is_array( $t_value ) ) {
				$t_key = string_html_entities( $t_key );
				$t_field_key = $p_field_key . '[' . $t_key . ']';
				print_hidden_input( $t_field_key, $t_value );
			} else {
				$t_field_key = $p_field_key . '[' . $t_key . ']';
				print_hidden_input( $t_field_key, $t_value );
			}
		}
	} else {
		$t_key = string_html_entities( $p_field_key );
		$t_val = string_html_entities( $p_field_val );
		echo "<input type=\"hidden\" name=\"$t_key\" value=\"$t_val\" />\n";
	}
}

# =============================
# Functions that used to be in html_api
# =============================

# This prints the little [?] link for user help
# The $p_a_name is a link into the documentation.html file
function print_documentation_link( $p_a_name = '' ) {
	echo lang_get( $p_a_name ) . "\n";
	# @@@ Disable documentation links for now.  May be re-enabled if linked to new manual.
	# echo "<a href=\"doc/documentation.html#$p_a_name\" target=\"_info\">[?]</a>";
}

# print the hr
function print_hr( $p_hr_size = null, $p_hr_width = null ) {
	if( null === $p_hr_size ) {
		$p_hr_size = config_get( 'hr_size' );
	}
	if( null === $p_hr_width ) {
		$p_hr_width = config_get( 'hr_width' );
	}
	echo "<hr size=\"$p_hr_size\" width=\"$p_hr_width%\" />";
}

# prints the signup link
function print_signup_link() {
	if ( ( ON == config_get_global( 'allow_signup' ) ) &&
	     ( LDAP != config_get_global( 'login_method' ) ) &&
	     ( ON == config_get( 'enable_email_notification' ) )
	   ) {
		print_bracket_link( 'signup_page.php', lang_get( 'signup_link' ) );
	}
}

# prints the login link
function print_login_link() {
	print_bracket_link( 'login_page.php', lang_get( 'login_title' ) );
}

# prints the lost pwd link
function print_lost_password_link() {
	# lost password feature disabled or reset password via email disabled -> stop here!
	if ( ( LDAP != config_get_global( 'login_method' ) ) &&
	     ( ON == config_get( 'lost_password_feature' ) ) &&
	     ( ON == config_get( 'send_reset_password' ) ) &&
	     ( ON == config_get( 'enable_email_notification' ) ) ) {
		print_bracket_link( 'lost_pwd_page.php', lang_get( 'lost_password_link' ) );
	}
}

# ===============================
# Deprecated Functions
# ===============================

# print our standard mysql query error
# this function should rarely (if ever) be reached.  instead the db_()
# functions should trap (although inelegantly).
function print_sql_error( $p_query ) {
	global $g_administrator_email;

	$error = error_string( ERROR_SQL );
	$error .= lang_get( 'word_separator' );
	$error .= sprintf( lang_get( 'please_report' ), prepare_email_link( $g_administrator_email, lang_get( 'administrator' ) ) );
	$error .= "<br />$p_query;<br />";
	echo $error;
}

# Get icon corresponding to the specified filename
function print_file_icon( $p_filename ) {
	$t_icon = file_get_icon_url( $p_filename );
	echo '<img src="' . $t_icon['url'] . '" alt="' . $t_icon['alt'] . ' file icon" width="16" height="16" border="0" />';
}

# Prints an RSS image that is hyperlinked to an RSS feed.
function print_rss( $p_feed_url, $p_title = '' ) {
	$t_path = config_get( 'path' );
	echo '<a href="', htmlspecialchars( $p_feed_url ), '" title="', $p_title, '"><img src="', $t_path, '/images/', 'rss.png" width="16" height="16" border="0" alt="', $p_title, '" /></a>';
}

# Prints the recently visited issues.
function print_recently_visited() {
	if( !last_visited_enabled() ) {
		return;
	}

	$t_ids = last_visited_get_array();

	if( count( $t_ids ) == 0 ) {
		return;
	}

	echo '<div align="right"><small>' . lang_get( 'recently_visited' ) . ': ';
	$t_first = true;

	foreach( $t_ids as $t_id ) {
		if( !$t_first ) {
			echo ', ';
		} else {
			$t_first = false;
		}

		echo string_get_bug_view_link( $t_id );
	}
	echo '</small></div>';
}

# print a dropdown box from input array
function get_dropdown( $p_control_array, $p_control_name, $p_match = '', $p_add_any = false, $p_multiple = false, $p_change_script = '' ) {
	$t_control_array = $p_control_array;
	if( $p_multiple ) {
		$t_size = ' size="5"';
		$t_multiple = ' multiple="multiple"';
	} else {
		$t_size = '';
		$t_multiple = '';
	}
	$t_script = ( $p_change_script == '' ? '' : ' onchange="' . $p_change_script . '"' );
	$t_info = sprintf( "<select %s name=\"%s\" id=\"%s\"%s%s>", $t_multiple, $p_control_name, $p_control_name, $t_size, $t_script );
	if( $p_add_any ) {
		array_unshift_assoc( $t_control_array, META_FILTER_ANY, lang_trans( '[any]' ) );
	}
	while( list( $t_name, $t_desc ) = each( $t_control_array ) ) {
		$t_sel = '';
		if( is_array( $p_match ) ) {
			if( in_array( $t_name, array_values( $p_match ) ) || in_array( $t_desc, array_values( $p_match ) ) ) {
				$t_sel = ' selected="selected"';
			}
		} else {
			if(( $t_name === $p_match ) || ( $t_desc === $p_match ) ) {
				$t_sel = ' selected="selected"';
			}
		}
		$t_info .= sprintf( "<option%s value=\"%s\">%s</option>", $t_sel, $t_name, $t_desc );
	}
	$t_info .= "</select>\n";
	return $t_info;
}

# List the attachments belonging to the specified bug.  This is used from within
# bug_view_inc.php
function print_bug_attachments_list( $p_bug_id ) {
	$t_attachments = file_get_visible_attachments( $p_bug_id );
	$t_attachments_count = count( $t_attachments );

	$i = 0;
	$image_previewed = false;

	foreach ( $t_attachments as $t_attachment ) {
		$t_file_display_name = string_display_line( $t_attachment['display_name'] );
		$t_filesize = number_format( $t_attachment['size'] );
		$t_date_added = date( config_get( 'normal_date_format' ), $t_attachment['date_added'] );

		if ( $image_previewed ) {
			$image_previewed = false;
			echo '<br />';
		}

		if ( $t_attachment['can_download'] ) {
			$t_href_start = '<a href="' . string_attribute( $t_attachment['download_url'] ) . '">';
			$t_href_end = '</a>';

			$t_href_clicket = " [<a href=\"file_download.php?file_id={$t_attachment['id']}&amp;type=bug\" target=\"_blank\">^</a>]";
		} else {
			$t_href_start = '';
			$t_href_end = '';
			$t_href_clicket = '';
		}

		if ( !$t_attachment['exists'] ) {
			print_file_icon( $t_file_display_name );
			echo '&#160;<span class="strike">' . $t_file_display_name . '</span>' . lang_get( 'word_separator' ) . '(' . lang_get( 'attachment_missing' ) . ')';
		} else {
			echo $t_href_start;
			print_file_icon( $t_file_display_name );
			echo $t_href_end . '&#160;' . $t_href_start . $t_file_display_name . $t_href_end . $t_href_clicket . ' (' . $t_filesize . ' ' . lang_get( 'bytes' ) . ') ' . '<span class="italic">' . $t_date_added . '</span>';
		}

		if ( $t_attachment['can_delete'] ) {
			echo '&#160;[';
			print_link( 'bug_file_delete.php?file_id=' . $t_attachment['id'] . form_security_param( 'bug_file_delete' ), lang_get( 'delete_link' ), false, 'small' );
			echo ']';
		}

		if ( $t_attachment['exists'] ) {
			if ( ( FTP == config_get( 'file_upload_method' ) ) && $t_attachment['exists'] ) {
				echo ' (' . lang_get( 'cached' ) . ')';
			}

			if ( $t_attachment['preview'] && ( $t_attachment['type'] == 'text' ) ) {
				 $c_id = db_prepare_int( $t_attachment['id'] );
				 $t_bug_file_table = db_get_table( 'mantis_bug_file_table' );

				echo "<script type=\"text/javascript\" language=\"JavaScript\">
<!--
function swap_content( span ) {
displayType = ( document.getElementById( span ).style.display == 'none' ) ? '' : 'none';
document.getElementById( span ).style.display = displayType;
}

 -->
 </script>";
				echo " <span id=\"hideSection_$c_id\">[<a class=\"small\" href='#' id='attmlink_" . $c_id . "' onclick='swap_content(\"hideSection_" . $c_id . "\");swap_content(\"showSection_" . $c_id . "\");return false;'>" . lang_get( 'show_content' ) . "</a>]</span>";
				echo " <span style='display:none' id=\"showSection_$c_id\">[<a class=\"small\" href='#' id='attmlink_" . $c_id . "' onclick='swap_content(\"hideSection_" . $c_id . "\");swap_content(\"showSection_" . $c_id . "\");return false;'>" . lang_get( 'hide_content' ) . "</a>]";

				echo "<pre>";

				/** @todo Refactor into a method that gets contents for download / preview. */
				switch( config_get( 'file_upload_method' ) ) {
					case DISK:
						if ( $t_attachment['exists'] ) {
							$v_content = file_get_contents( $t_attachment['diskfile'] );
						}
						break;
					case FTP:
						if( file_exists( $t_attachment['exists'] ) ) {
							file_get_contents( $t_attachment['diskfile'] );
						} else {
							$ftp = file_ftp_connect();
							file_ftp_get( $ftp, $t_attachment['diskfile'], $t_attachment['diskfile'] );
							file_ftp_disconnect( $ftp );
							$v_content = file_get_contents( $t_attachment['diskfile'] );
						}
						break;
					default:
						$query = "SELECT *
	                  					FROM $t_bug_file_table
				            			WHERE id=" . db_param();
						$result = db_query_bound( $query, Array( $c_id ) );
						$row = db_fetch_array( $result );
						$v_content = $row['content'];
				}

				echo htmlspecialchars( $v_content );
				echo "</pre></span>\n";
			}

			if ( $t_attachment['can_download'] && $t_attachment['preview'] && $t_attachment['type'] == 'image' ) {
				$t_preview_style = 'border: 0;';
				$t_max_width = config_get( 'preview_max_width' );
				if( $t_max_width > 0 ) {
					$t_preview_style .= ' max-width:' . $t_max_width . 'px;';
				}

				$t_max_height = config_get( 'preview_max_height' );
				if( $t_max_height > 0 ) {
					$t_preview_style .= ' max-height:' . $t_max_height . 'px;';
				}

				$t_preview_style = 'style="' . $t_preview_style . '"';
				$t_title = file_get_field( $t_attachment['id'], 'title' );

				$t_image_url = $t_attachment['download_url'] . '&amp;show_inline=1' . form_security_param( 'file_show_inline' );

				echo "\n<br />$t_href_start<img alt=\"$t_title\" $t_preview_style src=\"$t_image_url\" />$t_href_end";
				$image_previewed = true;
			}
		}

		if ( $i != ( $t_attachments_count - 1 ) ) {
			echo "<br />\n";
			$i++;
		}
	}
}

# --------------------
# Print the option list for timezones
function print_timezone_option_list( $p_timezone ) {
	if ( !function_exists( 'timezone_identifiers_list' ) ) {
		echo '<option value="', $p_timezone, '" selected="selected">', $p_timezone, '</option>';
		return;
	}

	$t_identifiers = timezone_identifiers_list();

	foreach ( $t_identifiers as $t_identifier )
	{
	    $t_zone = explode( '/', $t_identifier );

	    // Only use "friendly" continent names - http://us.php.net/manual/en/timezones.others.php
		if ($t_zone[0] == 'Africa' ||
			$t_zone[0] == 'America' ||
			$t_zone[0] == 'Antarctica' ||
			$t_zone[0] == 'Arctic' ||
			$t_zone[0] == 'Asia' ||
			$t_zone[0] == 'Atlantic' ||
			$t_zone[0] == 'Australia' ||
			$t_zone[0] == 'Europe' ||
			$t_zone[0] == 'Indian' ||
			$t_zone[0] == 'Pacific' )
		{
	        if ( isset( $t_zone[1] ) != '' )
	        {
	            $t_locations[$t_zone[0]][$t_zone[0] . '/' . $t_zone[1]] = array( str_replace( '_', ' ', $t_zone[1] ), $t_identifier );
	        }
		}
	}

	foreach( $t_locations as $t_continent => $t_locations ) {
		echo '<optgroup label="'.$t_continent.'">';
		foreach ( $t_locations as $t_location ) {
			echo '<option value="' . $t_location[1] . '"';
			check_selected( $p_timezone, $t_location[1] );
			echo '>' . $t_location[0] . '</option>';
		}
	}
}
