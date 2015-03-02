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
 * GET PARAMETERS FOR THIS PAGE
 *
 * project_id: 0 - all projects, otherwise project id.
 * filter_id: The filter id to use for generating the rss.
 * sort: This parameter is ignore if filter_id is supplied and is not equal to 0.
 *		"update": issues ordered descending by last updated date.
 *       "submit": issues ordered descending by submit date (default).
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses bug_api.php
 * @uses category_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses filter_api.php
 * @uses gpc_api.php
 * @uses lang_api.php
 * @uses project_api.php
 * @uses rss_api.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'bug_api.php' );
require_api( 'category_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'filter_api.php' );
require_api( 'gpc_api.php' );
require_api( 'lang_api.php' );
require_api( 'project_api.php' );
require_api( 'rss_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );
require_api( 'utility_api.php' );

$f_project_id = gpc_get_int( 'project_id', ALL_PROJECTS );
$f_filter_id = gpc_get_int( 'filter_id', 0 );
$f_sort = gpc_get_string( 'sort', 'submit' );
$f_username = gpc_get_string( 'username', null );
$f_key = gpc_get_string( 'key', null );

# make sure RSS syndication is enabled.
if( OFF == config_get( 'rss_enabled' ) ) {
	access_denied();
}

# authenticate the user
if( $f_username !== null ) {
	if( !rss_login( $f_username, $f_key ) ) {
		access_denied();
	}
} else {
	if( OFF == config_get( 'allow_anonymous_login' ) ) {
		access_denied();
	}
}

# Make sure that the current user has access to the selected project (if not ALL PROJECTS).
if( $f_project_id != ALL_PROJECTS ) {
	access_ensure_project_level( config_get( 'view_bug_threshold', null, null, $f_project_id ), $f_project_id );
}

if( $f_sort === 'update' ) {
	$c_sort_field = 'last_updated';
} else {
	$c_sort_field = 'date_submitted';
}

$t_path = config_get( 'path' );

# construct rss file

$t_encoding = 'utf-8';
$t_about = $t_path;
$t_title = config_get( 'window_title' );
$t_image_link = $t_path . 'images/mantis_logo_button.gif';

# only rss 2.0
$t_category = project_get_name( $f_project_id );
if( $f_project_id !== 0 ) {
	$t_title .= ' - ' . $t_category;
}

$t_title .= ' - ' . lang_get( 'issues' );

if( $f_username !== null ) {
	$t_title .= ' - (' . $f_username . ')';
}

if( $f_filter_id !== 0 ) {
	$t_title .= ' (' . filter_get_field( $f_filter_id, 'name' ) . ')';
}

$t_description = $t_title;

# in minutes (only rss 2.0)
$t_cache = '10';

$t_rssfile = new RSSBuilder(	$t_encoding, $t_about, $t_title, $t_description, $t_image_link, $t_category, $t_cache );

# person, an organization, or a service
$t_publisher = '';

# person, an organization, or a service
$t_creator = '';

$t_date = date( 'r' );
$t_language = lang_get( 'phpmailer_language' );
$t_rights = '';

# spatial location , temporal period or jurisdiction
$t_coverage = '';

# person, an organization, or a service
$t_contributor = '';

$t_rssfile->addDCdata( $t_publisher, $t_creator, $t_date, $t_language, $t_rights, $t_coverage, $t_contributor );

# hourly / daily / weekly / ...
$t_period = 'hourly';

# every X hours/days/...
$t_frequency = 1;

$t_base = date( 'Y-m-d\TH:i:sO' );

# add missing : in the O part of the date.  PHP 5 supports a 'c' format which will output the format
# exactly as we want it.
# 2002-10-02T10:00:00-0500 -> 2002-10-02T10:00:00-05:00
$t_base = utf8_substr( $t_base, 0, 22 ) . ':' . utf8_substr( $t_base, -2 );

$t_rssfile->addSYdata( $t_period, $t_frequency, $t_base );

$t_page_number = 1;
$t_issues_per_page = 25;
$t_page_count = 0;
$t_issues_count = 0;
$t_project_id = $f_project_id;
if( $f_username !== null ) {
	$t_user_id = user_get_id_by_name( $f_username );
} else {
	$t_user_id = user_get_id_by_name( config_get( 'anonymous_account' ) );
}
$t_show_sticky = null;

if( $f_filter_id == 0 ) {
	$t_custom_filter = filter_get_default();
	$t_custom_filter['sort'] = $c_sort_field;
} else {
	# null will be returned if the user doesn't have access right to access the filter.
	$t_custom_filter = filter_db_get_filter( $f_filter_id, $t_user_id );
	if( null === $t_custom_filter ) {
		access_denied();
	}

	$t_custom_filter = filter_deserialize( $t_custom_filter );
}

$t_issues = filter_get_bug_rows( $t_page_number, $t_issues_per_page, $t_page_count, $t_issues_count,
								 $t_custom_filter, $t_project_id, $t_user_id, $t_show_sticky );
$t_issues_count = count( $t_issues );

# Loop through results
for( $i = 0; $i < $t_issues_count; $i++ ) {
	$t_bug = $t_issues[$i];

	$t_about = $t_link = $t_path . 'view.php?id=' . $t_bug->id;
	$t_title = bug_format_id( $t_bug->id ) . ': ' . $t_bug->summary;

	if( $t_bug->view_state == VS_PRIVATE ) {
		$t_title .= ' [' . lang_get( 'private' ) . ']';
	}

	$t_description = string_rss_links( $t_bug->description );

	# subject is category.
	$t_subject = category_full_name( $t_bug->category_id, false );

	# optional DC value
	$t_date = $t_bug->last_updated;

	# author of item
	$t_author = '';
	if( access_has_global_level( config_get( 'show_user_email_threshold' ) ) ) {
		$t_author_name = user_get_name( $t_bug->reporter_id );
		$t_author_email = user_get_field( $t_bug->reporter_id, 'email' );

		if( !is_blank( $t_author_email ) ) {
			if( !is_blank( $t_author_name ) ) {
				$t_author = $t_author_name . ' <' . $t_author_email . '>';
			} else {
				$t_author = $t_author_email;
			}
		}
	}

	# $comments = 'http://www.example.com/sometext.php?somevariable=somevalue&comments=1';	# url to comment page rss 2.0 value
	$t_comments = $t_path . 'view.php?id=' . $t_bug->id . '#bugnotes';

	# optional mod_im value for dispaying a different pic for every item
	$t_image = '';

	$t_rssfile->addRSSItem( $t_about, $t_title, $t_link, $t_description, $t_subject, $t_date,
						$t_author, $t_comments, $t_image );
}

# @todo consider making this a configuration option - 0.91 / 1.0 / 2.0
$t_version = '2.0';

$t_rssfile->outputRSS( $t_version );

