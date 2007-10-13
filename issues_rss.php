<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

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
	# $Id: issues_rss.php,v 1.8.2.1 2007-10-13 22:33:16 giallu Exp $
	# --------------------------------------------------------
?>
<?php
	#
	# GET PARAMETERS FOR THIS PAGE
	#
	# project_id: 0 - all projects, otherwise project id.
	# filter_id: The filter id to use for generating the rss.
	# sort: This parameter is ignore if filter_id is supplied and is not equal to 0.
	#		"update": issues ordered descending by last updated date.
	#       "submit": issues ordered descending by submit date (default).

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path . 'class.RSSBuilder.inc.php' );
	require_once( $t_core_path . 'user_api.php' );
	require_once( $t_core_path . 'filter_api.php' );
	require_once( $t_core_path . 'rss_api.php' );

	$f_project_id = gpc_get_int( 'project_id', ALL_PROJECTS );
	$f_filter_id = gpc_get_int( 'filter_id', 0 );
	$f_sort = gpc_get_string( 'sort', 'submit' );
	$f_username = gpc_get_string( 'username', null );
	$f_key = gpc_get_string( 'key', null );

	# make sure RSS syndication is enabled.
	if ( OFF == config_get( 'rss_enabled' ) ) {
		access_denied();
	}

	# authenticate the user
	if ( $f_username !== null ) {
		if ( !rss_login( $f_username, $f_key ) ) {
			access_denied();
		}
	} else {
		if ( OFF == config_get( 'allow_anonymous_login' ) ) {
			access_denied();
		}
	}

	# Make sure that the current user has access to the selected project (if not ALL PROJECTS).
	if ( $f_project_id != ALL_PROJECTS ) {
		access_ensure_project_level( VIEWER, $f_project_id );
	}

	if ( $f_sort === 'update' ) {
		$c_sort_field = 'last_updated';
	} else {
		$c_sort_field = 'date_submitted';
	}

	$t_path = config_get( 'path' );

	# construct rss file

	$encoding = lang_get( 'charset' );
	$about = $t_path;
	$title = string_rss_links( config_get( 'window_title' ) );
	$image_link = $t_path . 'images/mantis_logo_button.gif';

	# only rss 2.0
	$category = string_rss_links( project_get_name( $f_project_id ) );
	if ( $f_project_id !== 0 ) {
		$title .= ' - ' . $category;
	}

	$title .= ' - ' . lang_get( 'issues' );

 	if ( $f_username !== null ) {
		$title .= " - ($f_username)";
	}

	if ( $f_filter_id !== 0 ) {
		$title .= ' (' . filter_get_field( $f_filter_id, 'name' ) . ')';
	}

	$description = $title;

	# in minutes (only rss 2.0)
	$cache = '10';

	$rssfile = new RSSBuilder(	$encoding, $about, $title, $description,
					$image_link, $category, $cache);

	# person, an organization, or a service
	$publisher = '';

	# person, an organization, or a service
	$creator = '';

	$date = (string) date( 'r' );
	$language = lang_get( 'phpmailer_language' );
	$rights = '';

	# spatial location , temporal period or jurisdiction
	$coverage = (string) '';

	# person, an organization, or a service
	$contributor = (string) '';

	$rssfile->setPublisher( $publisher );
	$rssfile->setCreator( $creator );
	$rssfile->setRights( $rights );
	$rssfile->setCoverage( $coverage );
	$rssfile->setContributor( $contributor );

	# hourly / daily / weekly / ...
	$period = (string) 'hourly';

	# every X hours/days/...
	$frequency = (int) 1;

	$base = (string) date( 'Y-m-d\TH:i:sO' );

	# add missing : in the O part of the date.  PHP 5 supports a 'c' format which will output the format
	# exactly as we want it.
	# // 2002-10-02T10:00:00-0500 -> // 2002-10-02T10:00:00-05:00
	$base = substr( $base, 0, 22 ) . ':' . substr( $base, -2 );

	$rssfile->addSYdata( $period, $frequency, $base );

	$t_page_number = 1;
	$t_issues_per_page = 25;
	$t_page_count = 0;
	$t_issues_count = 0;
	$t_project_id = $f_project_id;
	if ( $f_username !== null ) {
		$t_user_id = user_get_id_by_name( $f_username );
	} else {
		$t_user_id = user_get_id_by_name( config_get( 'anonymous_account' ) );
	}
	$t_show_sticky = null;

	if ( $f_filter_id == 0 ) {
		$t_custom_filter = filter_get_default();
		$t_custom_filter['sort'] = $c_sort_field;
	} else {
		# null will be returned if the user doesn't have access right to access the filter.
		$t_custom_filter = filter_db_get_filter( $f_filter_id, $t_user_id );
		if ( null === $t_custom_filter ) {
			access_denied();
		}

		$t_custom_filter = filter_deserialize( $t_custom_filter );
	}

	$t_issues = filter_get_bug_rows( $t_page_number, $t_issues_per_page, $t_page_count, $t_issues_count,
									 $t_custom_filter, $t_project_id, $t_user_id, $t_show_sticky );

	# Loop through results
	for ( $i = 0; $i < count( $t_issues ); $i++ ) {
		$row = $t_issues[$i];

		$t_bug = bug_get( $row['id'], true );

		$about = $link = $t_path . "view.php?id=" . $row['id'];
		$title = string_rss_links( bug_format_id( $row['id'] ) . ': ' . $t_bug->summary );
		
		if ( $row['view_state'] == VS_PRIVATE ) {
			$title .= ' [' . lang_get( 'private' ) . ']';
		}
                
		$description = string_rss_links( $t_bug->description );

		# subject is category.
		$subject = string_rss_links( $t_bug->category );

		# optional DC value
		$date = date( 'Y-m-d\TH:i:sO', $t_bug->last_updated );

		# author of item
		$author = string_rss_links( user_get_name( $t_bug->reporter_id ) );
		if ( access_has_global_level( config_get( 'show_user_email_threshold' ) ) ) {
			$t_author_email = user_get_field( $t_bug->reporter_id, 'email' );
			if ( is_blank( $t_author_email ) ) {
				$t_author_email = $author . '@example.com';
			}
		} else {
			$t_author_email = $author . '@example.com';
		}
		$author .= ' &lt;' . $t_author_email . '&gt;';

		# $comments = 'http://www.example.com/sometext.php?somevariable=somevalue&comments=1';	# url to comment page rss 2.0 value
		$comments = $t_path . 'view.php?id=' . $row['id'] . '#bugnotes';

		# optional mod_im value for dispaying a different pic for every item
		$image = '';

		$rssfile->addItem( $about, $title, $link, $description, $subject, $date,
							$author, $comments, $image );
	}

	# @@@ consider making this a configuration option.
	# 0.91 / 1.0 / 2.0
	$version = '2.0';

	$rssfile->outputRSS( $version );
?>
