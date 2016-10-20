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
 * This is the first page a user sees when they login to the bugtracker
 * News is displayed which can notify users of any important changes
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses news_api.php
 * @uses print_api.php
 * @uses rss_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'news_api.php' );
require_api( 'print_api.php' );
require_api( 'rss_api.php' );

access_ensure_project_level( config_get( 'view_bug_threshold' ) );

$f_offset = gpc_get_int( 'offset', 0 );

$t_project_id = helper_get_current_project();

$t_rss_enabled = config_get( 'rss_enabled' );

if( OFF != $t_rss_enabled && news_is_enabled() ) {
	$t_rss_link = rss_get_news_feed_url( $t_project_id );
	html_set_rss_link( $t_rss_link );
}

layout_page_header( lang_get( 'main_link' ) );

layout_page_begin();

echo '<div class="col-md-6 col-xs-12">';

if( !current_user_is_anonymous() ) {
	$t_current_user_id = auth_get_current_user_id();
	$t_hide_status = config_get( 'bug_resolved_status_threshold' );
	echo '<span class="bigger-120">';
	echo lang_get( 'open_and_assigned_to_me_label' ) . lang_get( 'word_separator' );
	print_link( "view_all_set.php?type=1&handler_id=$t_current_user_id&hide_status=$t_hide_status", current_user_get_assigned_open_bug_count() );

	echo '<br />';

	echo lang_get( 'open_and_reported_to_me_label' ) . lang_get( 'word_separator' );
	print_link( "view_all_set.php?type=1&reporter_id=$t_current_user_id&hide_status=$t_hide_status", current_user_get_reported_open_bug_count() );

	echo '<br />';

	echo lang_get( 'last_visit_label' ) . lang_get( 'word_separator' );
	echo date( config_get( 'normal_date_format' ), current_user_get_field( 'last_visit' ) );
	echo '</span>';
}

echo '</div>';
echo '<div class="col-md-6 col-xs-12">';

if( news_is_enabled() && access_has_project_level( config_get( 'manage_news_threshold' ) ) ) {
	# Admin can edit news for All Projects (site-wide)
	if( ALL_PROJECTS != helper_get_current_project() || current_user_is_administrator() ) {
		print_link_button( 'news_menu_page.php', lang_get( 'edit_news_link' ), 'pull-right');
	} else {
		print_link_button( 'login_select_proj_page.php', lang_get( 'edit_news_link' ), 'pull-right');
	}
}
echo '</div>';

echo '<div class="col-md-12 col-xs-12">';

if( news_is_enabled() ) {
	$t_news_rows = news_get_limited_rows( $f_offset, $t_project_id );
	$t_news_count = count( $t_news_rows );

	if( $t_news_count ) {
		echo '<div id="news-items">';
		# Loop through results
		for( $i = 0; $i < $t_news_count; $i++ ) {
			$t_row = $t_news_rows[$i];

			# only show VS_PRIVATE posts to configured threshold and above
			if( ( VS_PRIVATE == $t_row['view_state'] ) &&
				 !access_has_project_level( config_get( 'private_news_threshold' ) ) ) {
				continue;
			}

			print_news_entry_from_row( $t_row );
		}  # end for loop
		echo '</div>';
	}

	echo '<div class="space-10"></div>';
	echo '<div class="btn-group">';

	print_link_button( 'news_list_page.php', lang_get( 'archives' ) );
	$t_news_view_limit = config_get( 'news_view_limit' );
	$f_offset_next = $f_offset + $t_news_view_limit;
	$f_offset_prev = $f_offset - $t_news_view_limit;

	if( $f_offset_prev >= 0 ) {
		print_link_button( 'main_page.php?offset=' . $f_offset_prev, lang_get( 'newer_news_link' ) );
	}

	if( $t_news_count == $t_news_view_limit ) {
		print_link_button( 'main_page.php?offset=' . $f_offset_next, lang_get( 'older_news_link' ) );
	}

	if( OFF != $t_rss_enabled ) {
		print_link_button( $t_rss_link, lang_get( 'rss' ) );
	}

	echo '</div>';
}
echo '</div>';
layout_page_end();
