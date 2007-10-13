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
	# $Id: news_rss.php,v 1.11.2.1 2007-10-13 22:34:08 giallu Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path . 'class.RSSBuilder.inc.php' );
	require_once( $t_core_path . 'news_api.php' );
	require_once( $t_core_path . 'project_api.php' );
	require_once( $t_core_path . 'print_api.php' );
	require_once( $t_core_path . 'rss_api.php' );

	$f_username = gpc_get_string( 'username', null );
	$f_key = gpc_get_string( 'key', null );
	$f_project_id = gpc_get_int( 'project_id', ALL_PROJECTS );

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

	# construct rss file

	$encoding = lang_get( 'charset' );
	$about = config_get( 'path' );
	$title = string_rss_links( config_get( 'window_title' ) . ' - ' . lang_get( 'news' ) );

	if ( $f_username !== null ) {
		$title .= " - ($f_username)";
	}

	$description = $title;
	$image_link = config_get( 'path' ) . 'images/mantis_logo_button.gif';

	# only rss 2.0
	$category = string_rss_links( project_get_name( $f_project_id ) );

	# in minutes (only rss 2.0)
	$cache = '60';

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
	$period = (string) 'daily';

	# every X hours/days/...
	$frequency = (int) 1;

	$base = (string) date('Y-m-d\TH:i:sO');

	# add missing : in the O part of the date.  PHP 5 supports a 'c' format which will output the format
	# exactly as we want it.
	# // 2002-10-02T10:00:00-0500 -> // 2002-10-02T10:00:00-05:00
	$base = substr( $base, 0, 22 ) . ':' . substr( $base, -2 );

	$rssfile->addSYdata( $period, $frequency, $base );

	$news_rows = news_get_limited_rows( 0 /* offset */, $f_project_id );

	# Loop through results
	for ( $i = 0; $i < count( $news_rows ); $i++ ) {
		$row = $news_rows[$i];
		extract( $row, EXTR_PREFIX_ALL, 'v' );

		# skip news item if private, or
		# belongs to a private project (will only happen
		if ( VS_PRIVATE == $v_view_state ) {
			continue;
		}

		$v_headline 	= string_rss_links( $v_headline );
		$v_body 	= string_rss_links( $v_body );
		$v_date_posted 	= date( 'Y-m-d\TH:i:sO', $v_date_posted );

		$about = $link = config_get( 'path' ) . "news_view_page.php?news_id=$v_id";
		$title = $v_headline;
		$description = $v_body;

		# optional DC value
		$subject = $title;

		# optional DC value
		$date = $v_date_posted;

		# author of item
		$author = string_rss_links( user_get_name( $v_poster_id ) );
		if ( access_has_global_level( config_get( 'show_user_email_threshold' ) ) ) {
			$t_author_email = user_get_field( $v_poster_id, 'email' );
			if ( is_blank( $t_author_email ) ) {
				$t_author_email = $author . '@example.com';
			}
		} else {
			$t_author_email = $author . '@example.com';
		}
		$author .= ' &lt;' . $t_author_email . '&gt;';

		# $comments = 'http://www.example.com/sometext.php?somevariable=somevalue&comments=1';	# url to comment page rss 2.0 value
		$comments = '';

		# optional mod_im value for dispaying a different pic for every item
		$image = '';

		$rssfile->addItem(	$about, $title, $link, $description, $subject, $date,
					$author, $comments, $image);
	}

	# @@@ consider making this a configuration option.
	# 0.91 / 1.0 / 2.0
	$version = '2.0';

	$rssfile->outputRSS( $version );
?>
