<?php
# MantisBT - A PHP based bugtracking system

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
 * Print API
 *
 * @package CoreAPI
 * @subpackage PrintAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_group_action_api.php
 * @uses category_api.php
 * @uses config_api.php
 * @uses collapse_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses custom_field_api.php
 * @uses database_api.php
 * @uses email_api.php
 * @uses error_api.php
 * @uses file_api.php
 * @uses form_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses last_visited_api.php
 * @uses news_api.php
 * @uses prepare_api.php
 * @uses profile_api.php
 * @uses project_api.php
 * @uses project_hierarchy_api.php
 * @uses string_api.php
 * @uses tag_api.php
 * @uses user_api.php
 * @uses utility_api.php
 * @uses version_api.php
 */

require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_group_action_api.php' );
require_api( 'category_api.php' );
require_api( 'config_api.php' );
require_api( 'collapse_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'custom_field_api.php' );
require_api( 'database_api.php' );
require_api( 'email_api.php' );
require_api( 'error_api.php' );
require_api( 'file_api.php' );
require_api( 'form_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'last_visited_api.php' );
require_api( 'news_api.php' );
require_api( 'prepare_api.php' );
require_api( 'profile_api.php' );
require_api( 'project_api.php' );
require_api( 'project_hierarchy_api.php' );
require_api( 'string_api.php' );
require_api( 'tag_api.php' );
require_api( 'user_api.php' );
require_api( 'utility_api.php' );
require_api( 'version_api.php' );

/**
 * Print the headers to cause the page to redirect to $p_url
 * If $p_die is true (default), terminate the execution of the script immediately
 * If we have handled any errors on this page return false and don't redirect.
 * $p_sanitize - true/false - true in the case where the URL is extracted from GET/POST or untrusted source.
 * This would be false if the URL is trusted (e.g. read from config_inc.php).
 *
 * @param string  $p_url      The page to redirect: has to be a relative path.
 * @param boolean $p_die      If true, stop the script after redirecting.
 * @param boolean $p_sanitize Apply string_sanitize_url to passed URL.
 * @param boolean $p_absolute Indicate if URL is absolute.
 * @return boolean
 */
function print_header_redirect( $p_url, $p_die = true, $p_sanitize = false, $p_absolute = false ) {
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
			$t_url = config_get_global( 'path' ) . $p_url;
		}
	}

	$t_url = string_prepare_header( $t_url );

	# don't send more headers if they have already been sent
	if( !headers_sent() ) {
		header( 'Content-Type: text/html; charset=utf-8' );
		header( 'Location: ' . $t_url );
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

/**
 * Print a redirect header to view a bug
 *
 * @param integer $p_bug_id A bug identifier.
 * @return void
 */
function print_header_redirect_view( $p_bug_id ) {
	print_header_redirect( string_get_bug_view_url( $p_bug_id ) );
}

/**
 * Get a view URL for the bug id based on the user's preference and
 * call print_successful_redirect() with that URL
 *
 * @param integer $p_bug_id A bug identifier.
 * @return void
 */
function print_successful_redirect_to_bug( $p_bug_id ) {
	$t_url = string_get_bug_view_url( $p_bug_id );

	print_successful_redirect( $t_url );
}

/**
 * If the show query count is ON, print success and redirect after the configured system wait time.
 * If the show query count is OFF, redirect right away.
 *
 * @param string $p_redirect_to URI to redirect to.
 * @return void
 */
function print_successful_redirect( $p_redirect_to ) {
	if( helper_log_to_page() ) {
		layout_page_header( null, $p_redirect_to );
		layout_page_begin();
		echo '<br /><div class="center">';
		echo lang_get( 'operation_successful' ) . '<br />';
		print_link_button( $p_redirect_to, lang_get( 'proceed' ) );
		echo '</div>';
		layout_page_end();
	} else {
		print_header_redirect( $p_redirect_to );
	}
}

/**
 * Print avatar image for the given user ID
 *
 * @param integer $p_user_id 		A user identifier.
 * @param string $p_class_prefix	CSS class prefix.
 * @param integer $p_size    		Image pixel size.
 * @return void
 */
function print_avatar( $p_user_id, $p_class_prefix, $p_size = 80 ) {
	$t_avatar = Avatar::get( $p_user_id, $p_size );

	echo prepare_avatar( $t_avatar, $p_class_prefix, $p_size );
}

/**
 * prints the name of the user given the id.
 *
 * By default, the username will become a hyperlink to View User page,
 * but caller can decide to just print the username.
 *
 * @param integer $p_user_id A user identifier.
 * @param boolean $p_link    Whether to add an html link (defaults to true)
 *
 * @return void
 */
function print_user( $p_user_id, $p_link = true ) {
	echo prepare_user_name( $p_user_id, $p_link );
}

/**
 * same as echo get_user_name() but fills in the subject with the bug summary
 *
 * @param integer $p_user_id A user identifier.
 * @param integer $p_bug_id  A bug identifier.
 * @return void
 */
function print_user_with_subject( $p_user_id, $p_bug_id ) {
	if( NO_USER == $p_user_id ) {
		return;
	}

	print_user( $p_user_id );

	if( user_exists( $p_user_id ) && user_is_enabled( $p_user_id ) ) {
		$t_email = user_get_email( $p_user_id );

		echo '&nbsp;';
		print_email_link_with_subject( $t_email, '', '', $p_bug_id );
	}
}

/**
 * print out an email editing input
 *
 * @param string $p_field_name Name of input tag.
 * @param string $p_email      Email address.
 * @return void
 */
function print_email_input( $p_field_name, $p_email ) {
	echo '<input class="input-sm" id="email-field" type="text" name="' . string_attribute( $p_field_name ) . '" size="32" maxlength="64" value="' . string_attribute( $p_email ) . '" />';
}

/**
 * print out an email editing input
 *
 * @param string $p_field_name Name of input tag.
 * @return void
 */
function print_captcha_input( $p_field_name ) {
	echo '<input class="input-sm" id="captcha-field" type="text" name="' . $p_field_name . '" size="6" maxlength="6" value="" />';
}

/**
 * This populates an option list with the appropriate users by access level
 * @todo from print_reporter_option_list
 * @param integer|array $p_user_id    A user identifier or a list of them.
 * @param integer       $p_project_id A project identifier.
 * @param integer       $p_access     An access level.
 * @return void
 */
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
			foreach( $t_project_users_list as $t_id => $t_user ) {
				$t_users[$t_id] = $t_user;
			}
			# Clear the array to release memory
			unset( $t_project_users_list );
		}
		unset( $t_projects );
	} else {
		$t_users = project_get_all_user_rows( $p_project_id, $p_access );
	}

	# Add the specified user ID to the list
	# If we have an array of user IDs, then we've been called from a filter
	# so don't add anything
	if( !is_array( $p_user_id ) &&
		$p_user_id != NO_USER &&
		!array_key_exists( $p_user_id, $t_users )
	) {
		$t_row = user_cache_row( $p_user_id, /* trigger_error */ false );
		if( $t_row === false ) {
			# User doesn't exist - create a dummy record for display purposes
			$t_name = user_get_name( $p_user_id );
			$t_row = array(
				'id' => $p_user_id,
				'username' => $t_name,
				'realname' => $t_name,
			);
		}
		$t_users[$p_user_id] = $t_row;
	}

	$t_display = array();
	$t_sort = array();

	foreach( $t_users as $t_key => $t_user ) {
		$t_display[] = user_get_expanded_name_from_row( $t_user );
		$t_sort[] = user_get_name_for_sorting_from_row( $t_user );
	}

	array_multisort( $t_sort, SORT_ASC, SORT_STRING, $t_users, $t_display );
	unset( $t_sort );

	$t_count = count( $t_users );
	for( $i = 0;$i < $t_count;$i++ ) {
		$t_row = $t_users[$i];
		echo '<option value="' . $t_row['id'] . '" ';
		check_selected( $p_user_id, (int)$t_row['id'] );
		echo '>' . string_attribute( $t_display[$i] ) . '</option>';
	}
}

/**
 * This populates the reporter option list with the appropriate users
 *
 * @todo ugly functions  need to be refactored
 * @todo This function really ought to print out all the users, I think.
 *  I just encountered a situation where a project used to be public and
 *  was made private, so now I can't filter on any of the reporters who
 *  actually reported the bugs at the time. Maybe we could get all user
 *  who are listed as the reporter in any bug?  It would probably be a
 *  faster query actually.
 * @param integer $p_user_id    A user identifier.
 * @param integer $p_project_id A project identifier.
 * @return void
 */
function print_reporter_option_list( $p_user_id, $p_project_id = null ) {
	print_user_option_list( $p_user_id, $p_project_id, config_get( 'report_bug_threshold' ) );
}

/**
 * Print the entire form for attaching a tag to a bug.
 * @param integer $p_bug_id A bug identifier.
 * @param string  $p_string Default contents of the input box.
 * @return boolean
 */
function print_tag_attach_form( $p_bug_id, $p_string = '' ) {
?>
	<form method="post" action="tag_attach.php" class="form-inline">
	<?php echo form_security_field( 'tag_attach' )?>
	<input type="hidden" name="bug_id" value="<?php echo $p_bug_id?>" class="input-sm" />
	<?php print_tag_input( $p_bug_id, $p_string ); ?>
	<input type="submit" value="<?php echo lang_get( 'tag_attach' )?>" class="btn btn-primary btn-sm btn-white btn-round" />
	</form>
<?php
	return true;
}

/**
 * Print the separator comment, input box, and existing tag dropdown menu.
 *
 * @param integer $p_bug_id A bug identifier. If not specified or 0, the
 *                          dropdown list will include all available tags;
 *                          otherwise tags attached to the given bug will
 *                          be excluded.
 * @param string  $p_string Default contents of the input box.
 *
 * @return void
 */
function print_tag_input( $p_bug_id = 0, $p_string = '' ) {
?>
	<label class="inline small"><?php printf( lang_get( 'tag_separate_by' ), config_get( 'tag_separator' ) )?></label>
	<input type="hidden" id="tag_separator" value="<?php echo config_get( 'tag_separator' )?>" />
	<input type="text" name="tag_string" id="tag_string" class="input-sm" size="40" value="<?php echo string_attribute( $p_string )?>" />
	<select class="input-sm" <?php echo helper_get_tab_index()?> name="tag_select" id="tag_select" class="input-sm">
		<?php print_tag_option_list( $p_bug_id );?>
	</select>
<?php
}

/**
 * Print out a list of errors for tags that failed validation or access check.
 *
 * @param array $p_tags_failed The array of failed tags.
 * @return void
 */
function print_tagging_errors_table( $p_tags_failed ) {
	?>
	<div id="manage-user-div" class="form-container">
		<h2><?php echo lang_get( 'tag_attach_failed' ) ?></h2>
		<table><tbody>
		<?php
		foreach( $p_tags_failed as $t_tag_row ) {
			echo '<tr>';

			echo '<td>', string_html_specialchars( $t_tag_row['name'] ), '</td>';

			if( -1 == $t_tag_row['id'] ) {
				$t_error = lang_get( 'tag_create_denied' );
			} else if( -2 == $t_tag_row['id'] ) {
				$t_error = lang_get( 'tag_invalid_name' );
			}

			echo '<td>', $t_error, '</td>';
			echo '</tr>';
		}
		?>
		</tbody></table>
	</div>
	<?php
}

/**
 * Print the drop-down combo-box of existing tags.
 * When passed a bug ID, the option list will not contain any tags attached to the given bug.
 * @param integer $p_bug_id A bug identifier.
 * @return void
 */
function print_tag_option_list( $p_bug_id = 0 ) {
	$t_rows = tag_get_candidates_for_bug( $p_bug_id );

	echo '<option value="0">', string_html_specialchars( lang_get( 'tag_existing' ) ), '</option>';
	foreach ( $t_rows as $t_row ) {
		echo '<option value="', $t_row['id'], '" title="', string_attribute( $t_row['description'] );
		echo '">', string_attribute( $t_row['name'] ), '</option>';
	}
}

/**
 * Get current headlines and id prefix with v_
 * @return void
 */
function print_news_item_option_list() {
	$t_project_id = helper_get_current_project();

	$t_global = access_has_global_level( config_get_global( 'admin_site_threshold' ) );
	db_param_push();
	if( $t_global ) {
		$t_query = 'SELECT id, headline, announcement, view_state FROM {news} ORDER BY date_posted DESC';
	} else {
		$t_query = 'SELECT id, headline, announcement, view_state FROM {news}
				WHERE project_id=' . db_param() . '
				ORDER BY date_posted DESC';
	}

	$t_result = db_query( $t_query, ($t_global == true ? array() : array( $t_project_id ) ) );

	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_headline = string_display_line( $t_row['headline'] );
		$t_announcement = $t_row['announcement'];
		$t_view_state = $t_row['view_state'];
		$t_id = $t_row['id'];

		$t_notes = array();
		$t_note_string = '';

		if( 1 == $t_announcement ) {
			array_push( $t_notes, lang_get( 'announcement' ) );
		}

		if( VS_PRIVATE == $t_view_state ) {
			array_push( $t_notes, lang_get( 'private' ) );
		}

		if( count( $t_notes ) > 0 ) {
			$t_note_string = ' [' . implode( ' ', $t_notes ) . ']';
		}

		echo '<option value="' . $t_id . '">' . $t_headline . $t_note_string . '</option>';
	}
}

/**
 * Constructs the string for one news entry given the row retrieved from the news table.
 *
 * @param string  $p_headline     Headline of news article.
 * @param string  $p_body         Body text of news article.
 * @param integer $p_poster_id    User ID of author.
 * @param integer $p_view_state   View State - either VS_PRIVATE or VS_PUBLIC.
 * @param boolean $p_announcement Flagged if news should be an announcement.
 * @param integer $p_date_posted  Date associated with news entry.
 * @return void
 */
function print_news_entry( $p_headline, $p_body, $p_poster_id, $p_view_state, $p_announcement, $p_date_posted ) {
	$t_headline = string_display_line_links( $p_headline );
	$t_body = string_display_links( $p_body );
	$t_date_posted = date( config_get( 'normal_date_format' ), $p_date_posted );

	$t_news_css = VS_PRIVATE == $p_view_state ? 'widget-color-red' : 'widget-color-blue2';
	?>

	<div class="space-10"></div>
	<div class="widget-box <?php echo $t_news_css ?>">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<?php print_icon( 'fa-edit', 'ace-icon' ); ?>
				<?php echo $t_headline ?>
			</h4>
			<div class="widget-toolbar">
				<a data-action="collapse" href="#">
					<?php print_icon( 'fa-chevron-up', 'ace-icon bigger-125' ); ?>
				</a>
			</div>
		</div>

		<div class="widget-body">
			<div class="widget-toolbox padding-8 clearfix">
				<?php print_icon( 'fa-user' ); ?>
				<?php echo prepare_user_name( $p_poster_id ); ?>
				&#160;&#160;&#160;&#160;
				<?php print_icon( 'fa-clock-o' ); ?>
				<?php echo $t_date_posted; ?>
			</div>
			<div class="widget-main">
				<?php
			if( 1 == $p_announcement ) { ?>
				<span class="news-announcement"><?php echo lang_get( 'announcement' ); ?></span><?php
			}
			if( VS_PRIVATE == $p_view_state ) { ?>
				<span class="news-private"><?php echo lang_get( 'private' ); ?></span><?php
			} ?>
				<p class="news-body"><?php echo $t_body; ?></p>
			</div>
		</div>
	</div><?php
}

/**
 * print a news item given a row in the news table.
 * @param array $p_news_row A news database result.
 * @return void
 */
function print_news_entry_from_row( array $p_news_row ) {
	$t_headline = $p_news_row['headline'];
	$t_body = $p_news_row['body'];
	$t_poster_id = $p_news_row['poster_id'];
	$t_view_state = $p_news_row['view_state'];
	$t_announcement = $p_news_row['announcement'];
	$t_date_posted = $p_news_row['date_posted'];

	print_news_entry( $t_headline, $t_body, $t_poster_id, $t_view_state, $t_announcement, $t_date_posted );
}

/**
 * print a news item
 *
 * @param integer $p_news_id A news article identifier.
 * @return void
 */
function print_news_string_by_news_id( $p_news_id ) {
	$t_row = news_get_row( $p_news_id );

	# only show VS_PRIVATE posts to configured threshold and above
	if( ( VS_PRIVATE == $t_row['view_state'] ) && !access_has_project_level( config_get( 'private_news_threshold' ) ) ) {
		return;
	}

	print_news_entry_from_row( $t_row );
}

/**
 * Print User option list for assigned to field
 * @param integer|string $p_user_id    A user identifier.
 * @param integer        $p_project_id A project identifier.
 * @param integer        $p_threshold  An access level.
 * @return void
 */
function print_assign_to_option_list( $p_user_id = '', $p_project_id = null, $p_threshold = null ) {
	if( null === $p_threshold ) {
		$p_threshold = config_get( 'handle_bug_threshold' );
	}

	print_user_option_list( $p_user_id, $p_project_id, $p_threshold );
}

/**
 * Print User option list for bugnote filter field
 * @param integer|string $p_user_id    A user identifier.
 * @param integer        $p_project_id A project identifier.
 * @param integer        $p_threshold  An access level.
 * @return void
 */
function print_note_option_list( $p_user_id = '', $p_project_id = null, $p_threshold = null ) {
	if( null === $p_threshold ) {
		$p_threshold = config_get( 'add_bugnote_threshold' );
	}

	print_user_option_list( $p_user_id, $p_project_id, $p_threshold );
}

/**
 * List projects that the current user has access to.
 *
 * @param integer        $p_project_id           The current project id or null to use cookie.
 * @param boolean        $p_include_all_projects True: include "All Projects", otherwise false.
 * @param integer|null   $p_filter_project_id    The id of a project to exclude or null.
 * @param string|boolean $p_trace                The current project trace, identifies the sub-project via a path from top to bottom.
 * @param boolean        $p_can_report_only      If true, disables projects in which user can't report issues; defaults to false (all projects enabled).
 * @return void
 */
function print_project_option_list( $p_project_id = null, $p_include_all_projects = true, $p_filter_project_id = null, $p_trace = false, $p_can_report_only = false ) {
	$t_user_id = auth_get_current_user_id();
	$t_project_ids = user_get_accessible_projects( $t_user_id );
	$t_can_report = true;
	project_cache_array_rows( $t_project_ids );

	if( $p_include_all_projects && $p_filter_project_id !== ALL_PROJECTS ) {
		echo '<option value="' . ALL_PROJECTS . '"';
		if( $p_project_id !== null ) {
			check_selected( $p_project_id, ALL_PROJECTS, false );
		}
		echo '>' . lang_get( 'all_projects' ) . '</option>' . "\n";
	}

	foreach( $t_project_ids as $t_id ) {
		if( $p_can_report_only ) {
			$t_report_bug_threshold = config_get( 'report_bug_threshold', null, $t_user_id, $t_id );
			$t_can_report = access_has_project_level( $t_report_bug_threshold, $t_id, $t_user_id );
		}

		echo '<option value="' . $t_id . '"';
		check_selected( $p_project_id, $t_id, false );
		check_disabled( $t_id == $p_filter_project_id || !$t_can_report );
		echo '>' . string_attribute( project_get_field( $t_id, 'name' ) ) . '</option>' . "\n";
		print_subproject_option_list( $t_id, $p_project_id, $p_filter_project_id, $p_trace, $p_can_report_only );
	}
}

/**
 * List projects that the current user has access to
 * @param integer $p_parent_id         A parent project identifier.
 * @param integer $p_project_id        A project identifier.
 * @param integer $p_filter_project_id A filter project identifier.
 * @param boolean $p_trace             Whether to trace parent projects.
 * @param boolean $p_can_report_only   If true, disables projects in which user can't report issues; defaults to false (all projects enabled).
 * @param array   $p_parents           Array of parent projects.
 * @return void
 */
function print_subproject_option_list( $p_parent_id, $p_project_id = null, $p_filter_project_id = null, $p_trace = false, $p_can_report_only = false, array $p_parents = array() ) {
	if ( config_get_global( 'subprojects_enabled' ) == OFF ) {
		return;
	}

	array_push( $p_parents, $p_parent_id );
	$t_user_id = auth_get_current_user_id();
	$t_project_ids = user_get_accessible_subprojects( $t_user_id, $p_parent_id );
	project_cache_array_rows( $t_project_ids );
	$t_can_report = true;

	foreach( $t_project_ids as $t_id ) {
		if( $p_can_report_only ) {
			$t_report_bug_threshold = config_get( 'report_bug_threshold', null, $t_user_id, $t_id );
			$t_can_report = access_has_project_level( $t_report_bug_threshold, $t_id, $t_user_id );
		}

		if( $p_trace ) {
			$t_full_id = implode( ';', $p_parents ) . ';' . $t_id;
		} else {
			$t_full_id = $t_id;
		}

		echo '<option value="' . $t_full_id . '"';
		check_selected( $p_project_id, $t_full_id, false );
		check_disabled( $t_id == $p_filter_project_id || !$t_can_report );
		echo '>'
			. str_repeat( '&#160;', count( $p_parents ) )
			. str_repeat( '&raquo;', count( $p_parents ) ) . ' '
			. string_attribute( project_get_field( $t_id, 'name' ) )
			. '</option>' . "\n";
		print_subproject_option_list( $t_id, $p_project_id, $p_filter_project_id, $p_trace, $p_can_report_only, $p_parents );
	}
}

/**
 * prints the profiles given the user id
 * @param integer $p_user_id   A user identifier.
 * @param integer $p_select_id ID to mark as selected; if 0, gets the user's default profile.
 * @param array   $p_profiles  Array of profiles.
 * @return void
 */
function print_profile_option_list( $p_user_id, $p_select_id = 0, array $p_profiles = null ) {
	if( 0 == $p_select_id ) {
		$p_select_id = profile_get_default( $p_user_id );
	}
	if( $p_profiles != null ) {
		$t_profiles = $p_profiles;
	} else {
		$t_profiles = profile_get_all_for_user( $p_user_id );
	}
	print_profile_option_list_from_profiles( $t_profiles, $p_select_id );
}

/**
 * prints the profiles used in a certain project
 * @param integer $p_project_id A project identifier.
 * @param integer $p_select_id  ID to mark as selected; if 0, gets the user's default profile.
 * @param array   $p_profiles   Array of profiles.
 * @return void
 */
function print_profile_option_list_for_project( $p_project_id, $p_select_id = 0, array $p_profiles = null ) {
	if( 0 == $p_select_id ) {
		$p_select_id = profile_get_default( auth_get_current_user_id() );
	}
	if( $p_profiles != null ) {
		$t_profiles = $p_profiles;
	} else {
		$t_profiles = profile_get_all_for_project( $p_project_id );
	}
	print_profile_option_list_from_profiles( $t_profiles, $p_select_id );
}

/**
 * print the profile option list from profiles array
 *
 * @param array   $p_profiles  Array of Operating System Profiles (ID, platform, os, os_build).
 * @param integer $p_select_id ID to mark as selected.
 * @return void
 */
function print_profile_option_list_from_profiles( array $p_profiles, $p_select_id ) {
	echo '<option value="">' . lang_get( 'select_option' ) . '</option>';
	foreach( $p_profiles as $t_profile ) {
		extract( $t_profile, EXTR_PREFIX_ALL, 'v' );

		$t_platform = string_attribute( $t_profile['platform'] );
		$t_os = string_attribute( $t_profile['os'] );
		$t_os_build = string_attribute( $t_profile['os_build'] );

		echo '<option value="' . $t_profile['id'] . '"';
		if( $p_select_id !== false ) {
			check_selected( $p_select_id, (int)$t_profile['id'] );
		}
		echo '>' . $t_platform . ' ' . $t_os . ' ' . $t_os_build . '</option>';
	}
}

/**
 * Since categories can be orphaned we need to grab all unique instances of category
 * We check in the project category table and in the bug table
 * We put them all in one array and make sure the entries are unique
 *
 * @param integer $p_category_id A category identifier.
 * @param integer $p_project_id  A project identifier.
 * @return void
 */
function print_category_option_list( $p_category_id = 0, $p_project_id = null ) {
	if( null === $p_project_id ) {
		$t_project_id = helper_get_current_project();
	} else {
		$t_project_id = $p_project_id;
	}

	$t_cat_arr = category_get_all_rows( $t_project_id, null, true );

	if( config_get( 'allow_no_category' ) ) {
		echo '<option value="0"';
		check_selected( $p_category_id, 0 );
		echo '>';
		echo category_full_name( 0, false );
		echo '</option>', PHP_EOL;
	} else {
		if( 0 == $p_category_id ) {
			if( count( $t_cat_arr ) == 1 ) {
				$p_category_id = (int) $t_cat_arr[0]['id'];
			} else {
				echo '<option value="0" disabled hidden';
				check_selected( $p_category_id, 0 );
				echo '>';
				echo string_attribute( lang_get( 'select_option' ) );
				echo '</option>', PHP_EOL;
			}
		}
	}

	foreach( $t_cat_arr as $t_category_row ) {
		$t_category_id = (int)$t_category_row['id'];
		$t_category_name = category_full_name(
			$t_category_id,
			$t_category_row['project_id'] != $t_project_id
		);
		echo '<option value="' . $t_category_id . '"';
		check_selected( $p_category_id, $t_category_id );
		echo '>';
		echo string_attribute( $t_category_name ), '</option>', PHP_EOL;
	}
}

/**
 * Now that categories are identified by numerical ID, we need an old-style name
 * based option list to keep existing filter functionality.
 * @param string       $p_category_name The selected category.
 * @param integer|null $p_project_id    A specific project or null.
 * @return void
 */
function print_category_filter_option_list( $p_category_name = '', $p_project_id = null ) {
	$t_cat_arr = category_get_filter_list( $p_project_id );

	natcasesort( $t_cat_arr );
	foreach( $t_cat_arr as $t_cat ) {
		$t_name = string_attribute( $t_cat );
		echo '<option value="' . $t_name . '"';
		check_selected( $p_category_name, $t_cat );
		echo '>' . $t_name . '</option>';
	}
}

/**
 * Print the option list for platforms accessible for the specified user.
 * @param string  $p_platform The current platform value.
 * @param integer $p_user_id  A user identifier.
 * @return void
 */
function print_platform_option_list( $p_platform, $p_user_id = null ) {
	$t_platforms_array = profile_get_field_all_for_user( 'platform', $p_user_id );

	foreach( $t_platforms_array as $t_platform_unescaped ) {
		$t_platform = string_attribute( $t_platform_unescaped );
		echo '<option value="' . $t_platform . '"';
		check_selected( $p_platform, $t_platform_unescaped );
		echo '>' . $t_platform . '</option>';
	}
}

/**
 * Print the option list for OSes accessible for the specified user.
 * @param string  $p_os      The current operating system value.
 * @param integer $p_user_id A user identifier.
 * @return void
 */
function print_os_option_list( $p_os, $p_user_id = null ) {
	$t_os_array = profile_get_field_all_for_user( 'os', $p_user_id );

	foreach( $t_os_array as $t_os_unescaped ) {
		$t_os = string_attribute( $t_os_unescaped );
		echo '<option value="' . $t_os . '"';
		check_selected( $p_os, $t_os_unescaped );
		echo '>' . $t_os . '</option>';
	}
}

/**
 * Print the option list for os_build accessible for the specified user.
 * @param string  $p_os_build The current operating system build value.
 * @param integer $p_user_id  A user identifier.
 * @return void
 */
function print_os_build_option_list( $p_os_build, $p_user_id = null ) {
	$t_os_build_array = profile_get_field_all_for_user( 'os_build', $p_user_id );

	foreach( $t_os_build_array as $t_os_build_unescaped ) {
		$t_os_build = string_attribute( $t_os_build_unescaped );
		echo '<option value="' . $t_os_build . '"';
		check_selected( $p_os_build, $t_os_build_unescaped );
		echo '>' . $t_os_build . '</option>';
	}
}

/**
 * Print the option list for versions.
 * All versions related for each project will be printed. Those include, for each
 * project, the directly linked versions and the inherited versions if applicable.
 *
 * @param string              $p_version       The currently selected version.
 * @param integer|array|null  $p_project_ids   A project id, or array of ids, or null to use current project.
 * @param integer             $p_released      One of VERSION_ALL, VERSION_FUTURE or VERSION_RELEASED
 *                                             to define which versions to include in the list (defaults to ALL).
 * @param boolean $p_leading_blank Allow selection of no version.
 *
 * @return void
 */
function print_version_option_list( $p_version = '', $p_project_ids = null, $p_released = VERSION_ALL, $p_leading_blank = true ) {
	if( null === $p_project_ids ) {
		$p_project_ids = helper_get_current_project();
	}
	$t_project_ids = is_array( $p_project_ids ) ? $p_project_ids : array( $p_project_ids );

	$t_versions = version_get_all_rows( $t_project_ids, $p_released, true );

	# Ensure the selected version (if specified) is included in the list
	# Note: Filter API specifies selected versions as an array
	if( !is_array( $p_version ) ) {
		if( !empty( $p_version ) ) {
			foreach( $t_project_ids as $t_project_id ) {
				$t_version_id = version_get_id( $p_version, $t_project_id );
				if( $t_version_id !== false ) {
					$t_versions[] = version_cache_row( $t_version_id );
					break;
				}
			}
		}
	}

	if( $p_leading_blank ) {
		echo '<option value=""></option>';
	}

	$t_listed = array();
	$t_max_length = config_get( 'max_dropdown_length' );

	$t_show_project_name = count( $t_project_ids ) > 1;

	foreach( $t_versions as $t_version ) {
		# If the current version is obsolete, and current version not equal to $p_version,
		# then skip it.
		if( ( (int)$t_version['obsolete'] ) == 1 ) {
			if( $t_version['version'] != $p_version ) {
				continue;
			}
		}

		$t_version_version = string_attribute( $t_version['version'] );

		if( !in_array( $t_version_version, $t_listed, true ) ) {
			$t_listed[] = $t_version_version;
			echo '<option value="' . $t_version_version . '"';
			check_selected( $p_version, $t_version['version'] );
			$t_version_string = string_attribute( prepare_version_string( $t_version['project_id'], $t_version['id'], $t_show_project_name ) );

			echo '>', string_shorten( $t_version_string, $t_max_length ), '</option>';
		}
	}
}

/**
 * print build option list
 * @param string $p_build The current build value.
 * @return void
 */
function print_build_option_list( $p_build = '' ) {
	$t_overall_build_arr = array();

	$t_project_id = helper_get_current_project();

	$t_project_where = helper_project_specific_where( $t_project_id );

	# Get the "found in" build list
	$t_query = 'SELECT DISTINCT build
				FROM {bug}
				WHERE ' . $t_project_where . '
				ORDER BY build DESC';
	$t_result = db_query( $t_query );

	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_overall_build_arr[] = $t_row['build'];
	}

	$t_max_length = config_get( 'max_dropdown_length' );

	foreach( $t_overall_build_arr as $t_build_unescaped ) {
		$t_build = string_attribute( $t_build_unescaped );
		echo '<option value="' . $t_build . '"';
		check_selected( $p_build, $t_build_unescaped );
		echo '>' . string_shorten( $t_build, $t_max_length ) . '</option>';
	}
}

/**
 * select the proper enumeration values based on the input parameter
 * Current value may be an integer, or an array of integers.
 * @param string  $p_enum_name Name of enumeration (eg: status).
 * @param integer|array $p_val	The current value(s)
 * @return void
 */
function print_enum_string_option_list( $p_enum_name, $p_val = 0 ) {
	$t_config_var_name = $p_enum_name . '_enum_string';
	$t_config_var_value = config_get( $t_config_var_name );
	$t_string_var = lang_get( $t_config_var_name );

	if( is_array( $p_val ) ) {
		$t_val = $p_val;
	} else {
		$t_val = (int)$p_val;
	}

	$t_enum_values = MantisEnum::getValues( $t_config_var_value );

	foreach ( $t_enum_values as $t_key ) {
		$t_label = MantisEnum::getLocalizedLabel( $t_config_var_value, $t_string_var, $t_key );

		echo '<option value="' . $t_key . '"';
		check_selected( $t_val, $t_key );
		echo '>' . string_html_specialchars( $t_label ) . '</option>';
	}
}

/**
 * Select the proper enumeration values for status based on workflow
 * or the input parameter if workflows are not used
 * @param integer $p_user_auth     A user identifier.
 * @param integer $p_current_value The current value.
 * @param boolean $p_show_current  Whether to show the current status.
 * @param boolean $p_add_close     Whether to add close option.
 * @param integer $p_project_id    A project identifier.
 * @return array
 */
function get_status_option_list( $p_user_auth = 0, $p_current_value = 0, $p_show_current = true, $p_add_close = false, $p_project_id = ALL_PROJECTS ) {
	$t_config_var_value = config_get( 'status_enum_string', null, null, $p_project_id );
	$t_enum_workflow = config_get( 'status_enum_workflow', null, null, $p_project_id );

	if( count( $t_enum_workflow ) < 1 || !MantisEnum::hasValue( $t_config_var_value, $p_current_value ) ) {
		# workflow not defined, use default enumeration
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
		if( ( $p_show_current || $p_current_value != $t_enum_value )
			&& access_compare_level( $p_user_auth, access_get_status_threshold( $t_enum_value, $p_project_id ) )
		) {
			$t_enum_list[$t_enum_value] = get_enum_element( 'status', $t_enum_value );
		}
	}

	if( $p_show_current ) {
		$t_enum_list[$p_current_value] = get_enum_element( 'status', $p_current_value );
	}

	if( $p_add_close && access_compare_level( $p_current_value, config_get( 'bug_resolved_status_threshold', null, null, $p_project_id ) ) ) {
		$t_closed = config_get( 'bug_closed_status_threshold', null, null, $p_project_id );
		if( $p_show_current || $p_current_value != $t_closed ) {
			$t_enum_list[$t_closed] = get_enum_element( 'status', $t_closed );
		}
	}

	return $t_enum_list;
}

/**
 * print the status option list for the bug_update pages
 * @param string  $p_select_label  The id/name html attribute of the select box.
 * @param integer $p_current_value The current value.
 * @param boolean $p_allow_close   Whether to allow close.
 * @param integer $p_project_id    A project identifier.
 * @return void
 */
function print_status_option_list( $p_select_label, $p_current_value = 0, $p_allow_close = false, $p_project_id = ALL_PROJECTS ) {
	$t_current_auth = access_get_project_level( $p_project_id );

	$t_enum_list = get_status_option_list( $t_current_auth, $p_current_value, true, $p_allow_close, $p_project_id );

	if( count( $t_enum_list ) > 1 ) {
		# resort the list into ascending order
		ksort( $t_enum_list );
		reset( $t_enum_list );
		echo '<select class="input-sm" ' . helper_get_tab_index() . ' id="' . $p_select_label . '" name="' . $p_select_label . '">';
		foreach( $t_enum_list as $t_key => $t_val ) {
			echo '<option value="' . $t_key . '"';
			check_selected( $t_key, $p_current_value );
			echo '>' . string_html_specialchars( $t_val ) . '</option>';
		}
		echo '</select>';
	} else if( count( $t_enum_list ) == 1 ) {
		echo array_pop( $t_enum_list );
	} else {
		echo MantisEnum::getLabel( lang_get( 'status_enum_string' ), $p_current_value );
	}
}

/**
 * prints the list of a project's users
 * if no project is specified uses the current project
 * @param integer $p_project_id A project identifier.
 * @return void
 */
function print_project_user_option_list( $p_project_id = null ) {
	print_user_option_list( 0, $p_project_id );
}

/**
 * prints the list of access levels that are less than or equal to the access level of the
 * logged in user.  This is used when adding users to projects
 * @param integer $p_val        The current value.
 * @param integer $p_project_id A project identifier.
 * @return void
 */
function print_project_access_levels_option_list( $p_val, $p_project_id = null ) {
	$t_current_user_access_level = access_get_project_level( $p_project_id );
	$t_access_levels_enum_string = config_get( 'access_levels_enum_string' );
	$t_enum_values = MantisEnum::getValues( $t_access_levels_enum_string );
	foreach ( $t_enum_values as $t_enum_value ) {
		# a user must not be able to assign another user an access level that is higher than theirs.
		if( $t_enum_value > $t_current_user_access_level ) {
			continue;
		}
		$t_access_level = get_enum_element( 'access_levels', $t_enum_value );
		echo '<option value="' . $t_enum_value . '"';
		check_selected( $p_val, $t_enum_value );
		echo '>' . string_html_specialchars( $t_access_level ) . '</option>';
	}
}

/**
 * Print option list of available language choices
 * @param string $p_language The current language.
 * @return void
 */
function print_language_option_list( $p_language ) {
	$t_arr = config_get( 'language_choices_arr' );
	$t_enum_count = count( $t_arr );
	for( $i = 0;$i < $t_enum_count;$i++ ) {
		$t_language = string_attribute( $t_arr[$i] );
		echo '<option value="' . $t_language . '"';
		check_selected( $t_language, $p_language );
		echo '>' . $t_language . '</option>';
	}
}

/**
 * Print option list of available font choices
 * @param string $p_font The current font.
 * @return void
 */
function print_font_option_list( $p_font ) {
	if ( config_get_global( 'cdn_enabled' ) == ON ) {
		$t_arr = config_get( 'font_family_choices' );
	} else {
		$t_arr = config_get( 'font_family_choices_local' );
	}
	$t_enum_count = count( $t_arr );
	for( $i = 0;$i < $t_enum_count;$i++ ) {
		$t_font = string_attribute( $t_arr[$i] );
		echo '<option value="' . $t_font . '"';
		check_selected( $t_font, $p_font );
		echo '>' . $t_font . '</option>';
	}
}

/**
 * Print a dropdown list of all bug actions available to a user for a specified
 * set of projects.
 * @param array $p_project_ids An array containing one or more project IDs.
 * @return void
 */
function print_all_bug_action_option_list( array $p_project_ids = null ) {
	$t_commands = bug_group_action_get_commands( $p_project_ids );
	foreach ( $t_commands as $t_action_id => $t_action_label) {
		echo '<option value="' . $t_action_id . '">' . $t_action_label . '</option>';
	}
}

/**
 * list of users that are NOT in the specified project and that are enabled
 * if no project is specified use the current project
 * also exclude any administrators
 * @param integer $p_project_id A project identifier.
 * @return void
 */
function print_project_user_list_option_list( $p_project_id = null ) {
	$t_users = user_get_unassigned_by_project_id( $p_project_id );
	foreach( $t_users as $t_id=>$t_name ) {
		echo '<option value="' . $t_id . '">' . string_attribute( $t_name ) . '</option>';
	}
}

/**
 * list of projects that a user is NOT in
 * @param integer $p_user_id An user identifier.
 * @return void
 */
function print_project_user_list_option_list2( $p_user_id ) {
	db_param_push();
	$t_query = 'SELECT DISTINCT p.id, p.name
				FROM {project} p
				LEFT JOIN {project_user_list} u
				ON p.id=u.project_id AND u.user_id=' . db_param() . '
				WHERE p.enabled = ' . db_param() . ' AND
					u.user_id IS NULL
				ORDER BY p.name';
	$t_result = db_query( $t_query, array( (int)$p_user_id, true ) );
	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_project_name = string_attribute( $t_row['name'] );
		$t_user_id = $t_row['id'];
		echo '<option value="' . $t_user_id . '">' . $t_project_name . '</option>';
	}
}

/**
 * list of projects that a user is in
 * @param integer $p_user_id             An user identifier.
 * @param boolean $p_include_remove_link Whether to display remove link.
 * @return void
 */
function print_project_user_list( $p_user_id, $p_include_remove_link = true ) {
	$t_projects = user_get_assigned_projects( $p_user_id );

	foreach( $t_projects as $t_project_id=>$t_project ) {
		$t_project_name = string_attribute( $t_project['name'] );
		$t_view_state = $t_project['view_state'];
		$t_access_level = $t_project['access_level'];
		$t_access_level = get_enum_element( 'access_levels', $t_access_level );
		$t_view_state = get_enum_element( 'project_view_state', $t_view_state );

		echo $t_project_name . ' [' . $t_access_level . '] (' . $t_view_state . ')';
		if( $p_include_remove_link && access_has_project_level( config_get( 'project_user_threshold' ), $t_project_id ) ) {
			html_button( 'manage_user_proj_delete.php', lang_get( 'remove_link' ), array( 'project_id' => $t_project_id, 'user_id' => $p_user_id ) );
		}
		echo '<br />';
	}
}

/**
 * List of projects with which the specified field id is linked.
 * For every project, the project name is listed and then the list of custom
 * fields linked in order with their sequence numbers.  The specified field
 * is always highlighted in italics and project names in bold.
 *
 * @param integer $p_field_id The field to list the projects associated with.
 * @return void
 */
function print_custom_field_projects_list( $p_field_id ) {
	$c_field_id = (integer)$p_field_id;
	$t_project_ids = custom_field_get_project_ids( $p_field_id );

	$t_security_token = form_security_param( 'manage_proj_custom_field_remove' );

	foreach( $t_project_ids as $t_project_id ) {
		$t_project_name = project_get_field( $t_project_id, 'name' );
		echo '<strong>', string_display_line( $t_project_name ), '</strong>: ';
		print_extra_small_button( 'manage_proj_custom_field_remove.php?field_id=' . $c_field_id . '&project_id=' . $t_project_id . '&return=custom_field' . $t_security_token, lang_get( 'remove_link' ) );
		echo '<br />- ';

		$t_linked_field_ids = custom_field_get_linked_ids( $t_project_id );

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
 * @param integer $p_priority Current priority.
 * @return void
 */
function print_plugin_priority_list( $p_priority ) {
	if( $p_priority < PLUGIN_PRIORITY_LOW || $p_priority > PLUGIN_PRIORITY_HIGH ) {
		echo '<option value="', $p_priority, '" selected="selected">', $p_priority, '</option>';
	}

	for( $i = PLUGIN_PRIORITY_HIGH; $i >= PLUGIN_PRIORITY_LOW; $i-- ) {
		echo '<option value="', $i, '"';
		check_selected( $p_priority, $i );
		echo '>', $i, '</option>';
	}
}

/**
 * prints a link to VIEW a bug given an ID
 *  account for the user preference and site override
 * @param integer $p_bug_id      A bug identifier.
 * @param boolean $p_detail_info Detail info to display with the link.
 * @return void
 */
function print_bug_link( $p_bug_id, $p_detail_info = true ) {
	echo string_get_bug_view_link( $p_bug_id, $p_detail_info );
}

/**
 * formats the priority given the status
 * shows the priority in BOLD if the bug is NOT closed and is of significant priority
 * @param BugData $p_bug Bug Object.
 * @return void
 */
function print_formatted_priority_string( BugData $p_bug ) {
	$t_pri_str = get_enum_element( 'priority', $p_bug->priority, auth_get_current_user_id(), $p_bug->project_id );
	$t_priority_threshold = config_get( 'priority_significant_threshold' );

	if( $t_priority_threshold >= 0 &&
		$p_bug->priority >= $t_priority_threshold &&
		$p_bug->status < config_get( 'bug_closed_status_threshold' ) ) {
		echo '<span class="bold">' . $t_pri_str . '</span>';
	} else {
		echo $t_pri_str;
	}
}

/**
 * formats the severity given the status
 * shows the severity in BOLD if the bug is NOT closed and is of significant severity
 * @param BugData $p_bug Bug Object.
 * @return void
 */
function print_formatted_severity_string( BugData $p_bug ) {
	$t_sev_str = get_enum_element( 'severity', $p_bug->severity, auth_get_current_user_id(), $p_bug->project_id );
	$t_severity_threshold = config_get( 'severity_significant_threshold' );

	if( $t_severity_threshold >= 0 &&
		$p_bug->severity >= $t_severity_threshold &&
		$p_bug->status < config_get( 'bug_closed_status_threshold' ) ) {
		echo '<span class="bold">' . $t_sev_str . '</span>';
	} else {
		echo $t_sev_str;
	}
}

/**
 * Print view bug sort link
 * @todo params should be in same order as print_manage_user_sort_link
 * @param string  $p_string         The displayed text of the link.
 * @param string  $p_sort_field     The field to sort.
 * @param string  $p_sort           The field to sort by.
 * @param string  $p_dir            The sort direction - either ASC or DESC.
 * @param integer $p_columns_target See COLUMNS_TARGET_* in constant_inc.php.
 * @return void
 */
function print_view_bug_sort_link( $p_string, $p_sort_field, $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	# @TODO cproensa, $g_filter is needed to get the temporary id, since the
	# actual filter is not providede as parameter. Ideally, we should not
	# rely in this global variable, but at the moment is not possible without
	# a rewrite of these print functions.
	global $g_filter;

	switch( $p_columns_target ) {
		case COLUMNS_TARGET_PRINT_PAGE:
		case COLUMNS_TARGET_VIEW_PAGE:
			if( $p_sort_field == $p_sort ) {
				# We toggle between ASC and DESC if the user clicks the same sort order
				if( 'ASC' == $p_dir ) {
					$p_dir = 'DESC';
				} else {
					$p_dir = 'ASC';
				}
			} else {
				# Otherwise always start with ascending
				$p_dir = 'ASC';
			}
			$t_sort_field = rawurlencode( $p_sort_field );
			$t_print_parameter = ( $p_columns_target == COLUMNS_TARGET_PRINT_PAGE ) ? '&print=1' : '';
			$t_filter_parameter = filter_is_temporary( $g_filter ) ? filter_get_temporary_key_param( $g_filter ) . '&' : '';
			print_link( 'view_all_set.php?' . $t_filter_parameter . 'sort_add=' . $t_sort_field . '&dir_add=' . $p_dir . '&type=' . FILTER_ACTION_PARSE_ADD . $t_print_parameter, $p_string );
			break;
		default:
			echo $p_string;
	}
}

/**
 * Print manage user sort link
 * @param string  $p_page          The page within mantis to link to.
 * @param string  $p_string        The displayed text of the link.
 * @param string  $p_field         The field to sort.
 * @param string  $p_dir           The sort direction - either ASC or DESC.
 * @param string  $p_sort_by       The field to sort by.
 * @param integer $p_hide_inactive Whether to hide inactive users.
 * @param integer $p_filter        The filter to use.
 * @param string  $p_search        The search term to use.
 * @param integer $p_show_disabled Whether to show disabled users.
 * @param string  $p_class         The CSS class of the link.
 * @return void
 */
function print_manage_user_sort_link( $p_page, $p_string, $p_field, $p_dir, $p_sort_by, $p_hide_inactive = 0, $p_filter = 'ALL', $p_search = '', $p_show_disabled = 0, $p_class = '' ) {
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
	print_link( $p_page . '?sort=' . $t_field . '&dir=' . $t_dir . '&save=1&hideinactive=' . $p_hide_inactive . '&showdisabled=' . $p_show_disabled . '&filter=' . $p_filter . '&search=' . $p_search,
        $p_string, false, $p_class );
}

/**
 * Print manage project sort link
 * @param string $p_page    The page within mantis to link to.
 * @param string $p_string  The displayed text of the link.
 * @param string $p_field   The field to sort.
 * @param string $p_dir     The sort direction - either ASC or DESC.
 * @param string $p_sort_by The field to sort by.
 * @return void
 */
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
	print_link( $p_page . '?sort=' . $t_field . '&dir=' . $t_dir, $p_string );
}

/**
 * Print a button which presents a standalone form.
 * If $p_security_token is OFF, the button's form will not contain a security
 * field; this is useful when form does not result in modifications (CSRF is not
 * needed). If otherwise specified (i.e. not null), the parameter must contain
 * a valid security token, previously generated by form_security_token().
 * Use this to avoid performance issues when loading pages having many calls to
 * this function, such as adm_config_report.php.
 * @param string $p_action_page    The action page.
 * @param string $p_label          The button label.
 * @param array  $p_args_to_post   Associative array of arguments to be posted, with
 *                                 arg name => value, defaults to null (no args).
 * @param mixed  $p_security_token Optional; null (default), OFF or security token string.
 * @param string $p_class          The CSS class of the button.
 * @see form_security_token()
 * @return void
 */
function print_form_button( $p_action_page, $p_label, array $p_args_to_post = null, $p_security_token = null, $p_class = '' ) {
	$t_form_name = explode( '.php', $p_action_page, 2 );
	# TODO: ensure all uses of print_button supply arguments via $p_args_to_post (POST)
	# instead of via $p_action_page (GET). Then only add the CSRF form token if
	# arguments are being sent via the POST method.
	echo '<form method="post" action="', htmlspecialchars( $p_action_page ), '" class="form-inline inline single-button-form">';
	if( $p_security_token !== OFF ) {
		echo form_security_field( $t_form_name[0], $p_security_token );
	}
	if( $p_class !== '') {
		$t_class = $p_class;
	} else {
		$t_class = 'btn btn-primary btn-xs btn-white btn-round';
	}
	echo '<button type="submit" class="' . $t_class . '">' . $p_label . '</button>';
	if( $p_args_to_post ) {
		print_hidden_inputs( $p_args_to_post );
	}
	echo '</form>';
}

/**
 * print brackets around a pre-prepared link (i.e. '<a href' html tag).
 * @param string $p_link The URL to link to.
 * @return void
 * @deprecated 2.21.0
 */
function print_bracket_link_prepared( $p_link ) {
	error_parameters(
		__FUNCTION__,
		'print button functions or, in the context of an EVENT_MENU_ITEM hook, return link as associative array'
	);
	trigger_error( ERROR_DEPRECATED_SUPERSEDED, DEPRECATED );

	echo '<span class="bracket-link">[&#160;' . $p_link . '&#160;]</span> ';
}

/**
 * print a HTML link
 * @param string  $p_link       The page URL.
 * @param string  $p_url_text   The displayed text for the link.
 * @param boolean $p_new_window Whether to open in a new window.
 * @param string  $p_class      The CSS class of the link.
 * @return void
 */
function print_link( $p_link, $p_url_text, $p_new_window = false, $p_class = '' ) {
	if( is_blank( $p_link ) ) {
		echo $p_url_text;
	} else {
		$t_link = htmlspecialchars( $p_link );
		if( $p_new_window === true ) {
			echo '<a class="new-window ' . $p_class . '" href="' . $t_link . '" target="_blank">' . $p_url_text . '</a>';
		} else {
			if( $p_class !== '' ) {
				echo '<a class="' . $p_class . '" href="' . $t_link . '">' . $p_url_text . '</a>';
			} else {
				echo '<a href="' . $t_link . '">' . $p_url_text . '</a>';
			}
		}
	}
}

/**
 * print a HTML link with a button look
 * @param string  $p_link       The page URL.
 * @param string  $p_url_text   The displayed text for the link.
 * @param string  $p_class      The CSS class of the link.
 * @param boolean $p_new_window Whether to open in a new window.
 * @return void
 */
function print_link_button( $p_link, $p_url_text, $p_class = '', $p_new_window = false ) {
	if( is_blank( $p_link ) ) {
		echo $p_url_text;
	} else {
		$t_link = htmlspecialchars( $p_link );
		if( $p_new_window === true ) {
			echo "<a class=\"btn btn-primary btn-white btn-round $p_class\" href=\"$t_link\" target=\"_blank\">$p_url_text</a>";
		} else {
			echo "<a class=\"btn btn-primary btn-white btn-round $p_class\" href=\"$t_link\">$p_url_text</a>";
		}
	}
}

/**
 * shortcut for printing a HTML link with a small button look
 * @param string  $p_link       The page URL.
 * @param string  $p_url_text   The displayed text for the link.
 * @param boolean $p_new_window Whether to open in a new window.
 * @return void
 */
function print_extra_small_button( $p_link, $p_url_text, $p_new_window = false ) {
	print_link_button( $p_link, $p_url_text, 'btn-xs', $p_new_window );
}

function print_small_button( $p_link, $p_url_text, $p_new_window = false ) {
	print_link_button( $p_link, $p_url_text, 'btn-sm', $p_new_window );
}

/**
 * print a HTML page link
 * @param string  $p_page_url       The Page URL.
 * @param string  $p_text           The displayed text for the link.
 * @param integer $p_page_no        The page number to link to.
 * @param integer $p_page_cur       The current page number.
 * @param integer $p_temp_filter_key Temporary filter key.
 * @return void
 */
function print_page_link( $p_page_url, $p_text = '', $p_page_no = 0, $p_page_cur = 0, $p_temp_filter_key = null ) {
	if( is_blank( $p_text ) ) {
		$p_text = $p_page_no;
	}

	if( ( 0 < $p_page_no ) && ( $p_page_no != $p_page_cur ) ) {
		echo '<li class="pull-right"> ';
		$t_delimiter = ( strpos( $p_page_url, '?' ) ? '&' : '?' );
		if( $p_temp_filter_key ) {
			print_link( $p_page_url . $t_delimiter . 'filter=' . $p_temp_filter_key . '&page_number=' . $p_page_no, $p_text );
		} else {
			print_link( $p_page_url . $t_delimiter . 'page_number=' . $p_page_no, $p_text );
		}
		echo ' </li>';
	} else {
		echo '<li class="disabled pull-right"><a>' . $p_text . '</a></li>';
	}
}

/**
 * print a list of page number links (eg [1 2 3])
 * @param string  $p_page           The Page URL.
 * @param integer $p_start          The first page number.
 * @param integer $p_end            The last page number.
 * @param integer $p_current        The current page number.
 * @param integer $p_temp_filter_key Temporary filter key.
 * @return void
 */
function print_page_links( $p_page, $p_start, $p_end, $p_current, $p_temp_filter_key = null ) {
	$t_items = array();

	# @TODO cproensa
	# passing the temporary filter id to build ad-hoc url parameter is weak
	# ideally, we should pass a parameters array which is appended to all generated links
	# those parameters are provided as needed by the main page calling this functions

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

	print( '<ul class="pagination small no-margin"> ' );

	# Next and Last links
	print_page_link( $p_page, $t_last, $p_end, $p_current, $p_temp_filter_key );
	if( $p_current < $p_end ) {
		print_page_link( $p_page, $t_next, $p_current + 1, $p_current, $p_temp_filter_key );
	} else {
		print_page_link( $p_page, $t_next, null, null, $p_temp_filter_key );
	}

	# Page numbers ...

	$t_first_page = max( $p_start, $p_current - $t_page_links / 2 );
	$t_first_page = min( $t_first_page, $p_end - $t_page_links );
	$t_first_page = max( $t_first_page, $p_start );

	$t_last_page = $t_first_page + $t_page_links;
	$t_last_page = min( $t_last_page, $p_end );

	if( $t_last_page < $p_end ) {
		print( '<li class="pull-right"><a> ... </a></li>' );
	}

	for( $i = $t_last_page;$i >= $t_first_page;$i-- ) {
		if( $i == $p_current ) {
			array_push( $t_items, '<li class="active pull-right"><a>' . $i . '</a></li>' );
		} else {
			$t_delimiter = ( strpos( $p_page, '?' ) ? '&' : '?' ) ;
			$t_filter_param = filter_get_temporary_key_param( $p_temp_filter_key );
			$t_filter_param .= $t_filter_param === null ? '' : '&amp;';
			array_push( $t_items, '<li class="pull-right"><a href="' . $p_page . $t_delimiter . $t_filter_param . 'page_number=' . $i . '">' . $i . '</a></li>' );
		}
	}
	echo implode( '&#160;', $t_items );

	if( $t_first_page > 1 ) {
		print( '<li class="pull-right"><a> ... </a></li>' );
	}


	# First and previous links
	print_page_link( $p_page, $t_prev, $p_current - 1, $p_current, $p_temp_filter_key );
	print_page_link( $p_page, $t_first, 1, $p_current, $p_temp_filter_key );

	print( ' </ul>' );
}

/**
 * print a mailto: href link
 *
 * @param string $p_email Email Address.
 * @param string $p_text  Link text to display to user.
 * @return void
 */
function print_email_link( $p_email, $p_text ) {
	echo prepare_email_link( $p_email, $p_text );
}

/**
 * print a mailto: href link with subject
 *
 * @param string  $p_email  Email Address.
 * @param string  $p_text   Link text to display to user.
 * @param string  $p_tooltip The tooltip to show.
 * @param string  $p_bug_id The bug identifier.
 * @param boolean $p_show_as_button If true, show link as button with envelope
 *                                  icon, otherwise display a plain-text link.
 * @return void
 */
function print_email_link_with_subject( $p_email, $p_text, $p_tooltip, $p_bug_id, $p_show_as_button = true )
{
	global $g_project_override;
	$t_bug = bug_get( $p_bug_id, true );

	$g_project_override = $t_bug->project_id;

	echo prepare_email_link(
			$p_email,
			$p_text,
			email_build_subject( $p_bug_id ),
			$p_tooltip,
			$p_show_as_button
		);
}

/**
 * Print a hidden input for each name=>value pair in the array
 *
 * If a value is an array an input will be created for each item with a name
 *  that ends with []
 * The names and values are passed through htmlspecialchars() before being displayed
 *
 * @param array $p_assoc_array Array of Name/Value pairs for html input tags.
 * @return void
 */
function print_hidden_inputs( array $p_assoc_array ) {
	foreach( $p_assoc_array as $t_key => $t_val ) {
		print_hidden_input( $t_key, $t_val );
	}
}

/**
 * Print hidden html input tag <input type=hidden>
 *
 * @param string $p_field_key Name parameter.
 * @param string $p_field_val Value parameter.
 * @return void
 */
function print_hidden_input( $p_field_key, $p_field_val ) {
	if( is_array( $p_field_val ) ) {
		foreach( $p_field_val as $t_key => $t_value ) {
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
		echo '<input type="hidden" name="' . $t_key . '" value="' . $t_val . '" />' . "\n";
	}
}

/**
 * This prints the little [?] link for user help
 * @param string $p_a_name The anchor to use when accessing the documentation.
 * @return void
 */
function print_documentation_link( $p_a_name = '' ) {
	echo lang_get( $p_a_name );
	# @todo Disable documentation links for now.  May be re-enabled if linked to new manual.
	# echo "<a href=\"doc/documentation.html#$p_a_name\" target=\"_info\">[?]</a>";
}

/**
 * prints the sign up link
 * @return void
 */
function print_signup_link() {
	if( auth_signup_enabled() &&
		 ( LDAP != config_get_global( 'login_method' ) ) &&
		 ( ON == config_get( 'enable_email_notification' ) )
	   ) {
		print_link_button( 'signup_page.php', lang_get( 'signup_link' ) );
	}
}

/**
 * prints the login link
 * @return void
 */
function print_login_link() {
	print_link_button( auth_login_page(), lang_get( 'login' ) );
}

/**
 * prints the lost password link
 * @return void
 */
function print_lost_password_link() {
	# lost password feature disabled or reset password via email disabled -> stop here!
	if( ( LDAP != config_get_global( 'login_method' ) ) &&
		 ( ON == config_get( 'lost_password_feature' ) ) &&
		 ( ON == config_get( 'send_reset_password' ) ) &&
		 ( ON == config_get( 'enable_email_notification' ) ) ) {
		print_link_button( 'lost_pwd_page.php', lang_get( 'lost_password_link' ) );
	}
}

/**
 * Get icon corresponding to the specified filename
 *
 * @param string $p_filename Filename for which to retrieve icon link.
 * @return void
 */
function print_file_icon( $p_filename ) {
	$t_icon = file_get_icon_url( $p_filename );
	print_icon(
		string_attribute( $t_icon['url'] ),
		'',
		sprintf( lang_get( 'file_icon_description' ), string_attribute( $t_icon['alt'] ) )
	);
}

/**
 * Prints an RSS image that is hyperlinked to an RSS feed.
 *
 * @param string $p_feed_url URI to an RSS feed.
 * @param string $p_title    Title to use for hyperlink.
 * @return void
 */
function print_rss( $p_feed_url, $p_title = '' ) {
	echo '<a class="rss" rel="alternate" href="', htmlspecialchars( $p_feed_url ), '" title="', $p_title, '">';
	print_icon( 'fa-rss', 'fa-lg orange', $p_title );
	echo '</a>';
}

/**
 * Prints the recently visited issues.
 * @return void
 */
function print_recently_visited() {
	$t_ids = last_visited_get_array();

	if( count( $t_ids ) == 0 ) {
		return;
	}

	echo '<div class="recently-visited">' . lang_get( 'recently_visited' ) . ': ';
	$t_first = true;

	foreach( $t_ids as $t_id ) {
		if( !$t_first ) {
			echo ', ';
		} else {
			$t_first = false;
		}

		echo string_get_bug_view_link( $t_id );
	}
	echo '</div>';
}

/**
 * print a drop down box from input array
 * @param array        $p_control_array Array of elements in drop down list (name, description).
 * @param string       $p_control_name  Name attribute of <select> box.
 * @param string|array $p_match	        Either a string or an array of selected values.
 * @param boolean      $p_add_any       Whether to display an '[any]' option in the drop down.
 * @param boolean      $p_multiple      Whether drop down list allows multiple values to be selected.
 * @return string
 */
function get_dropdown( array $p_control_array, $p_control_name, $p_match = '', $p_add_any = false, $p_multiple = false ) {
	if( $p_multiple ) {
		$t_size = ' size="5"';
		$t_multiple = ' multiple="multiple"';
	} else {
		$t_size = '';
		$t_multiple = '';
	}
	$t_info = sprintf( '<select class="input-sm" %s name="%s" id="%s"%s>', $t_multiple, $p_control_name, $p_control_name, $t_size );
	if( $p_add_any ) {
		array_unshift_assoc( $p_control_array, META_FILTER_ANY, lang_trans( '[any]' ) );
	}
	foreach ( $p_control_array as $t_name => $t_desc ) {
		$t_sel = '';
		if( is_array( $p_match ) ) {
			if( in_array( $t_name, array_values( $p_match ) ) || in_array( $t_desc, array_values( $p_match ) ) ) {
				$t_sel = ' selected="selected"';
			}
		} else {
			if( ( $t_name === $p_match ) || ( $t_desc === $p_match ) ) {
				$t_sel = ' selected="selected"';
			}
		}
		$t_info .= sprintf( '<option%s value="%s">%s</option>', $t_sel, $t_name, $t_desc );
	}
	$t_info .= "</select>\n";
	return $t_info;
}

/**
 * Prints the list of visible attachments belonging to a given bug.
 * @param integer $p_bug_id ID of the bug to print attachments list for.
 * @param string $p_security_token The security token to use for deleting attachments.
 * @return void
 */
function print_bug_attachments_list( $p_bug_id, $p_security_token ) {
	$t_attachments = file_get_visible_attachments( $p_bug_id );
	echo "\n<ul>";
	foreach ( $t_attachments as $t_attachment ) {
		echo "\n<li>";
		print_bug_attachment( $t_attachment, $p_security_token );
		echo "\n</li>";
	}
	echo "\n</ul>";
}

/**
 * Prints information about a single attachment including download link, file
 * size, upload timestamp and an expandable preview for text and image file
 * types.
 * If $p_security_token is null, a token will be generated with form_security_token().
 * If otherwise specified (i.e. not null), the parameter must contain
 * a valid security token, previously generated by form_security_token().
 * Use this to avoid performance issues when loading pages having many calls to
 * this function, such as print_bug_attachments_list().
 * @param array $p_attachment An attachment array from within the array returned
 *                            by the file_get_visible_attachments() function.
 * @param string $p_security_token The security token to use for deleting attachments.
 * @return void
 */
function print_bug_attachment( array $p_attachment, $p_security_token ) {
	echo '<div class="well well-xs">';

	if( $p_attachment['preview'] || $p_attachment['type'] === 'audio' || $p_attachment['type'] === 'video' ) {
		$t_collapse_id = 'attachment_preview_' . $p_attachment['id'];
		global $g_collapse_cache_token;
		$g_collapse_cache_token[$t_collapse_id] = 
			$p_attachment['type'] == 'image' ||
			$p_attachment['type'] == 'audio' ||
			$p_attachment['type'] == 'video';

		collapse_open( $t_collapse_id, '');
	}

	print_bug_attachment_header( $p_attachment, $p_security_token );

	if( $p_attachment['preview'] ) {
		echo lang_get( 'word_separator' );
		collapse_icon( $t_collapse_id );

		switch( $p_attachment['type'] ) {
			case 'text':
				print_bug_attachment_preview_text( $p_attachment );
				break;
			case 'image':
				print_bug_attachment_preview_image( $p_attachment );
				break;
			case 'audio':
			case 'video':
				print_bug_attachment_preview_audio_video(
					$p_attachment, $p_attachment['file_type'], $p_attachment['preview'] );
				break;
		}

		collapse_closed( $t_collapse_id, '' );

		print_bug_attachment_header( $p_attachment, $p_security_token );
		echo lang_get( 'word_separator' );
		collapse_icon( $t_collapse_id );
		collapse_end( $t_collapse_id );
	} else {
		# Audio / Video support showing control without preloading.
		if( $p_attachment['type'] === 'audio' || $p_attachment['type'] === 'video' ) {
			echo lang_get( 'word_separator' );
			collapse_icon( $t_collapse_id );
	
			print_bug_attachment_preview_audio_video(
				$p_attachment,
				$p_attachment['file_type'],
				$p_attachment['preview'] );
	
			collapse_closed( $t_collapse_id );
			print_bug_attachment_header( $p_attachment, $p_security_token );
			echo lang_get( 'word_separator' );
			collapse_icon( $t_collapse_id );
			collapse_end( $t_collapse_id );	
		} else {
			echo '<br />';
		}
	}

	echo '</div>';
}

/**
 * Prints a single textual line of information about an attachment including download link, file
 * size and upload timestamp.
 * If $p_security_token is null, a token will be generated with form_security_token().
 * If otherwise specified (i.e. not null), the parameter must contain
 * a valid security token, previously generated by form_security_token().
 * Use this to avoid performance issues when loading pages having many calls to
 * this function, such as print_bug_attachments_list().
 * @param array $p_attachment An attachment array from within the array returned by
 *              the file_get_visible_attachments() function.
 * @param string $p_security_token The security token to use for deleting attachments.
 * @return void
 */
function print_bug_attachment_header( array $p_attachment, $p_security_token ) {
	if( $p_attachment['exists'] ) {
		if( $p_attachment['can_download'] ) {
			echo '<a href="' . string_attribute( $p_attachment['download_url'] ) . '">';
		}
		print_file_icon( $p_attachment['display_name'] );
		if( $p_attachment['can_download'] ) {
			echo '</a>';
		}
		echo lang_get( 'word_separator' );
		if( $p_attachment['can_download'] ) {
			echo '<a href="' . string_attribute( $p_attachment['download_url'] ) . '">';
		}
		echo string_display_line( $p_attachment['display_name'] );
		if( $p_attachment['can_download'] ) {
			echo '</a>';
		}

		echo lang_get( 'word_separator' ) . '(' . number_format( $p_attachment['size'] ) . lang_get( 'word_separator' ) . lang_get( 'bytes' ) . ')';
		event_signal( 'EVENT_VIEW_BUG_ATTACHMENT', array( $p_attachment ) );
	} else {
		print_file_icon( $p_attachment['display_name'] );
		echo lang_get( 'word_separator' ) . '<s>' . string_display_line( $p_attachment['display_name'] ) . '</s>' . lang_get( 'word_separator' ) . '(' . lang_get( 'attachment_missing' ) . ')';
	}

	if( $p_attachment['can_delete'] ) {
		echo '<a class="noprint red zoom-130 pull-right" '
			. 'href="bug_file_delete.php?file_id=' . $p_attachment['id']
			. form_security_param( 'bug_file_delete', $p_security_token ) . '">';
		print_icon( 'fa-trash-o', '1 ace-icon bigger-115' );
		echo '</a>';
	}
}

/**
 * Prints the preview of a text file attachment.
 * @param array $p_attachment An attachment array from within the array returned by
 *              the file_get_visible_attachments() function.
 * @return void
 */
function print_bug_attachment_preview_text( array $p_attachment ) {
	if( !$p_attachment['exists'] ) {
		return;
	}
	echo "\n<pre class=\"bug-attachment-preview-text\">";
	switch( config_get( 'file_upload_method' ) ) {
		case DISK:
			if( file_exists( $p_attachment['diskfile'] ) ) {
				$t_content = file_get_contents( $p_attachment['diskfile'] );
			}
			break;
		case DATABASE:
			db_param_push();
			$t_query = 'SELECT * FROM {bug_file} WHERE id=' . db_param();
			$t_result = db_query( $t_query, array( (int)$p_attachment['id'] ) );
			$t_row = db_fetch_array( $t_result );
			$t_content = $t_row['content'];
			break;
		default:
			trigger_error( ERROR_GENERIC, ERROR );
	}
	echo htmlspecialchars( $t_content, ENT_SUBSTITUTE, 'UTF-8' );
	echo '</pre>';
}

/**
 * Prints the preview of an image file attachment.
 * @param array $p_attachment An attachment array from within the array returned by
 *              the file_get_visible_attachments() function.
 * @return void
 */
function print_bug_attachment_preview_image( array $p_attachment ) {
	$t_preview_style = 'border: 0;';
	$t_max_width = config_get( 'preview_max_width' );
	if( $t_max_width > 0 ) {
		$t_preview_style .= ' max-width:' . $t_max_width . 'px;';
	}

	$t_max_height = config_get( 'preview_max_height' );
	if( $t_max_height > 0 ) {
		$t_preview_style .= ' max-height:' . $t_max_height . 'px;';
	}

	$t_title = file_get_field( $p_attachment['id'], 'title' );
	$t_image_url = $p_attachment['download_url'] . '&show_inline=1' . form_security_param( 'file_show_inline' );

	echo "\n<div class=\"bug-attachment-preview-image\">";
	echo '<a href="' . string_attribute( $p_attachment['download_url'] ) . '">';
	echo '<img src="' . string_attribute( $t_image_url ) . '" alt="' . string_attribute( $t_title ) . '" loading="lazy" style="' . string_attribute( $t_preview_style ) . '" />';
	echo '</a></div>';
}

/**
 * Prints the preview of an audio/video file attachment.
 * @param array $p_attachment An attachment array from within the array returned by
 *              the file_get_visible_attachments() function.
 * @param string $p_file_type mime type
 * @param boolean $p_preload true to preload audio/video, false otherwise.
 * @return void
 */
function print_bug_attachment_preview_audio_video( array $p_attachment, $p_file_type, $p_preload ) {
	$t_file_url = $p_attachment['download_url'] . '&show_inline=1' . form_security_param( 'file_show_inline' );
	$t_preload = $p_preload ? '' : ' preload="none"';

	$t_type = $p_attachment['type'];

	echo "\n<div class=\"bug-attachment-preview-" . $t_type . "\">";
	echo '<a href="' . string_attribute( $p_attachment['download_url'] ) . '">';
	echo '<' . $t_type . ' controls="controls"' . $t_preload . '>';
	echo '<source src="' . string_attribute( $t_file_url ) . '" type="' . string_attribute( $p_file_type ) . '">';
  	echo lang_get( 'browser_does_not_support_' . $t_type );
	echo '</' . $t_type . '>';
	echo "</a></div>";
}

/**
 * Print the option list for time zones
 * @param string $p_timezone Selected time zone.
 * @return void
 */
function print_timezone_option_list( $p_timezone ) {
	$t_identifiers = timezone_identifiers_list( DateTimeZone::ALL );

	foreach( $t_identifiers as $t_identifier ) {
		$t_zone = explode( '/', $t_identifier, 2 );
		if( isset( $t_zone[1] ) ) {
			$t_id = $t_zone[1];
		} else {
			$t_id = $t_identifier;
		}
		$t_locations[$t_zone[0]][$t_identifier] = array(
			str_replace( '_', ' ', $t_id ),
			$t_identifier
		);
	}

	foreach( $t_locations as $t_continent => $t_locations ) {
		echo "\t" . '<optgroup label="' . $t_continent . '">' . "\n";
		foreach ( $t_locations as $t_location ) {
			echo "\t\t" . '<option value="' . $t_location[1] . '"';
			check_selected( $p_timezone, $t_location[1] );
			echo '>' . $t_location[0] . '</option>' . "\n";
		}
		echo "\t" . '</optgroup>' . "\n";
	}
}

/**
 * Return file size information
 * @param integer $p_size File size.
 * @param string  $p_unit File size unit.
 * @return string
 */
function get_filesize_info( $p_size, $p_unit ) {
	return sprintf( lang_get( 'max_file_size_info' ), number_format( $p_size ), $p_unit );
}

/**
 * Print maximum file size information.
 *
 * @param integer $p_size    Size in bytes.
 * @param integer $p_divider Optional divider, defaults to 1024.
 * @param string  $p_unit    Optional language string of unit, defaults to KiB.
 * @return void
 */
function print_max_filesize( $p_size, $p_divider = 1024, $p_unit = 'kib' ) {
	echo '<span class="small" title="' . get_filesize_info( $p_size, lang_get( 'bytes' ) ) . '">';
	echo get_filesize_info( $p_size / $p_divider, lang_get( $p_unit ) );
	echo '</span>';
}

/**
 * Populate form element with dropzone data attributes
 * @return void
 */
function print_dropzone_form_data() {
	//$t_max_file_size = ceil( file_get_max_file_size() / ( 1024*1024 ) );
	echo 'data-force-fallback="' . ( config_get( 'dropzone_enabled' ) ? 'false' : 'true' ) . '"' . "\n";
	echo "\t" . 'data-max-filesize-bytes="'. file_get_max_file_size() . '"' . "\n";
	$t_allowed_files = config_get( 'allowed_files' );
	if ( !empty ( $t_allowed_files ) ) {
		$t_allowed_files = '.' . implode ( ',.', explode ( ',', config_get( 'allowed_files' ) ) );
	}
	echo "\t" . 'data-accepted-files="' . $t_allowed_files . '"' . "\n";
	echo "\t" . 'data-default-message="' . htmlspecialchars( lang_get( 'dropzone_default_message' ) ) . '"' . "\n";
	echo "\t" . 'data-fallback-message="' . htmlspecialchars( lang_get( 'dropzone_fallback_message' ) ) . '"' . "\n";
	echo "\t" . 'data-fallback-text="' . htmlspecialchars( lang_get( 'dropzone_fallback_text' ) ) . '"' . "\n";
	echo "\t" . 'data-file-too-big="' . htmlspecialchars( lang_get( 'dropzone_file_too_big' ) ) . '"' . "\n";
	echo "\t" . 'data-invalid-file-type="' . htmlspecialchars( lang_get( 'dropzone_invalid_file_type' ) ) . '"' . "\n";
	echo "\t" . 'data-response-error="' . htmlspecialchars( lang_get( 'dropzone_response_error' ) ) . '"' . "\n";
	echo "\t" . 'data-cancel-upload="' . htmlspecialchars( lang_get( 'dropzone_cancel_upload' ) ) . '"' . "\n";
	echo "\t" . 'data-cancel-upload-confirmation="' . htmlspecialchars( lang_get( 'dropzone_cancel_upload_confirmation' ) ) . '"' . "\n";
	echo "\t" . 'data-remove-file="'. htmlspecialchars( lang_get( 'dropzone_remove_file' ) ) . '"' . "\n";
	echo "\t" . 'data-remove-file-confirmation="' . htmlspecialchars( lang_get( 'dropzone_remove_file_confirmation' ) ) . '"' . "\n";
	echo "\t" . 'data-max-files-exceeded="' . htmlspecialchars( lang_get( 'dropzone_max_files_exceeded' ) ) . '"' . "\n";
	echo "\t" . 'data-dropzone-not-supported="' . htmlspecialchars( lang_get( 'dropzone_not_supported' ) ) . '"';
	echo "\t" . 'data-dropzone_multiple_files_too_big="' . htmlspecialchars( lang_get( 'dropzone_multiple_files_too_big' ) ) . '"';
}

/**
 * Populate a hidden div where its inner html will be used as preview template
 * for dropzone attached files
 * @return void
 */
function print_dropzone_template(){
	?>
	<div id="dropzone-preview-template" class="hidden">
		<div class="dz-preview dz-file-preview">
			<div class="dz-filename"><span data-dz-name></span></div>
			<div><img data-dz-thumbnail /></div>
			<div class="dz-size" data-dz-size></div>
			<div class="progress progress-small progress-striped active">
				<div class="progress-bar progress-bar-success" data-dz-uploadprogress></div>
			</div>
			<div class="dz-success-mark"><span></span></div>
			<div class="dz-error-mark"><span></span></div>
			<div class="dz-error-message"><span data-dz-errormessage></span></div>
		</div>
	</div>
	<?php
}

/**
 * Print a button which presents a standalone form.
 * This function remains for compatibility with v1.3
 * @deprecated use print_form_button() instead
 * @param string $p_action_page    The action page.
 * @param string $p_label          The button label.
 * @param array  $p_args_to_post   Associative array of arguments to be posted
 * @param mixed  $p_security_token Optional; null (default), OFF or security token string.
 * @see form_security_token()
 * @see print_form_button()
 * @return void
 */
function print_button( $p_action_page, $p_label, array $p_args_to_post = null, $p_security_token = null ) {
	error_parameters( __FUNCTION__, 'print_form_button' );
	trigger_error( ERROR_DEPRECATED_SUPERSEDED, DEPRECATED );
	print_form_button( $p_action_page, $p_label, $p_args_to_post, $p_security_token );
}

/**
 * Generate an html option list for the given array
 * @param array  $p_array        Array.
 * @param string $p_filter_value The selected value.
 * @return void
 */
function print_option_list_from_array( array $p_array, $p_filter_value ) {
	foreach( $p_array as $t_key => $t_value ) {
		echo '<option value="' . $t_key . '"';
		check_selected( (string)$p_filter_value, (string)$t_key );
		echo '>' . string_attribute( $t_value ) . '</option>' . "\n";
	}
}

/**
 * Print HTML relationship listbox
 *
 * @param integer $p_default_rel_type Relationship Type (default -1).
 * @param string  $p_select_name      List box name (default "rel_type").
 * @param boolean $p_include_any      Include an ANY option in list box (default false).
 * @param boolean $p_include_none     Include a NONE option in list box (default false).
 * @param string  $p_input_css        CSS classes to use with input fields
 * @return void
 */
function print_relationship_list_box( $p_default_rel_type = BUG_REL_ANY, $p_select_name = 'rel_type', $p_include_any = false, $p_include_none = false, $p_input_css = "input-sm" ) {
	global $g_relationships;
	?>
<select class="<?php echo $p_input_css ?>" name="<?php echo $p_select_name?>">
<?php if( $p_include_any ) {?>
<option value="<?php echo BUG_REL_ANY ?>" <?php echo( $p_default_rel_type == BUG_REL_ANY ? ' selected="selected"' : '' )?>>[<?php echo lang_get( 'any' )?>]</option>
<?php
	}

	if( $p_include_none ) {?>
<option value="<?php echo BUG_REL_NONE ?>" <?php echo( $p_default_rel_type == BUG_REL_NONE ? ' selected="selected"' : '' )?>>[<?php echo lang_get( 'none' )?>]</option>
<?php
	}

	foreach( $g_relationships as $t_type => $t_relationship ) {
		?>
<option value="<?php echo $t_type?>"<?php echo( $p_default_rel_type == $t_type ? ' selected="selected"' : '' )?>><?php echo lang_get( $t_relationship['#description'] )?></option>
<?php
	}?>
</select>
<?php
}

