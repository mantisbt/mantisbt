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
 * @package CoreAPI
 * @subpackage TwitterAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * requires config api
 */
require_once( 'config_api.php' );

$g_twitter_enabled = null;

/**
 * Checks if twitter is used for the current installation.
 * returns true for enabled, false otherwise.
 *
 * @return true: twitter enabled, false: otherwise.
 * @access public
 */
function twitter_enabled() {
	global $g_twitter_enabled;

	if( null === $g_twitter_enabled ) {
		$g_twitter_enabled = !is_blank( config_get( 'twitter_username' ) );
	}

	if( $g_twitter_enabled && !function_exists( 'curl_init' ) ) {
		trigger_error( ERROR_TWITTER_NO_CURL_EXT, ERROR );
	}

	return $g_twitter_enabled;
}

/**
 * Posts a twitter update when a bug is resolved.
 *
 * @param $p_bug_id The bug id that was resolved.
 * @access public
 */
function twitter_issue_resolved( $p_bug_id ) {
	if( !twitter_enabled() ) {
		return true;
	}

	$t_bug = bug_get( $p_bug_id, false );

	# Do not twitter except fixed issues
	if( $t_bug->resolution < config_get( 'bug_resolution_fixed_threshold' ) ||
		$t_bug->resolution >= config_get( 'bug_resolution_not_fixed_threshold' ) ) {
		return true;
	}

	# Do not twitter private bugs.
	if( $t_bug->view_state != VS_PUBLIC ) {
		return true;
	}

	# Do not twitter bugs belonging to private projects.
	if( VS_PRIVATE == project_get_field( $t_bug->project_id, 'view_state' ) ) {
		return true;
	}

	$c_bug_id = db_prepare_int( $p_bug_id );

	if( is_blank( $t_bug->fixed_in_version ) ) {
		$t_message = sprintf( lang_get( 'twitter_resolved_no_version' ), $c_bug_id, category_full_name( $t_bug->category_id,

		/* include project */
		false ), $t_bug->summary, user_get_name( $t_bug->handler_id ) );
	} else {
		$t_message = sprintf( lang_get( 'twitter_resolved' ), $c_bug_id, category_full_name( $t_bug->category_id,

		/* include project */
		false ), $t_bug->summary, user_get_name( $t_bug->handler_id ), $t_bug->fixed_in_version );
	}

	return twitter_update( $t_message );
}

/**
 * Posts a twitter update when a news entry is submitted.
 *
 * @param $p_news_id The newly posted news id.
 * @access public
 */
function twitter_news( $p_news_id ) {
	if( !twitter_enabled() ) {
		return true;
	}

	$t_news_view_state = news_get_field( $p_news_id, 'view_state' );
	if( VS_PUBLIC != $t_news_view_state ) {
		return true;
	}

	$t_news_project_id = news_get_field( $p_news_id, 'project_id' );
	if( $t_news_project_id != ALL_PROJECTS ) {
		$t_project_view_state = project_get_field( $t_news_project_id, 'view_state' );
		if( VS_PUBLIC != $t_project_view_state ) {
			return true;
		}
	}

	$t_news_headline = news_get_field( $p_news_id, 'headline' );

	return twitter_update( $t_news_headline );
}

/**
 * Posts an update to twitter
 *
 * @param $p_message  The message to post.
 * @access private
 */
function twitter_update( $p_message ) {
	if( !twitter_enabled() ) {
		return true;
	}

	if( is_blank( $p_message ) ) {
		return true;
	}

	# don't prepare the string, otherwise it will be escaped twice once by MantisBT and once by Twitter
	$c_message = $p_message;

	// Set username and password
	$t_username = config_get( 'twitter_username' );
	$t_password = config_get( 'twitter_password' );

	// The twitter API address
	$t_url = 'http://twitter.com/statuses/update.xml';

	// Set up and execute the curl process
	$t_curl = curl_init();

	curl_setopt( $t_curl, CURLOPT_URL, $t_url );
	curl_setopt( $t_curl, CURLOPT_CONNECTTIMEOUT, 2 );
	curl_setopt( $t_curl, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $t_curl, CURLOPT_POST, 1 );
	curl_setopt( $t_curl, CURLOPT_POSTFIELDS, 'status=' . $c_message );
	curl_setopt( $t_curl, CURLOPT_USERPWD, $t_username . ':' . $t_password );

	$t_buffer = curl_exec( $t_curl );

	curl_close( $t_curl );

	return !is_blank( $t_buffer );
}
