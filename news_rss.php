<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: news_rss.php,v 1.9 2005-08-21 21:07:29 ryandesign Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path . 'class.RSSBuilder.inc.php' );
	require_once( $t_core_path . 'news_api.php' );
	require_once( $t_core_path . 'project_api.php' );
	require_once( $t_core_path . 'print_api.php' );

	# only allow RSS generation of anonymous login is enabled.
	# @@@ consider adding an explicit option to enable/disable RSS syndication.
	if ( OFF == config_get( 'allow_anonymous_login' ) ) {
		access_denied();
	}

	# Make sure that the user selected either all projects or a public one.
	$f_project_id = gpc_get_int( 'project_id', 0 );
	if ( ( $f_project_id != 0 ) && ( VS_PRIVATE == project_get_field( $f_project_id, 'view_state' ) ) ) {
		access_denied();
	}

	# construct rss file

	$encoding = lang_get( 'charset' );
	$about = config_get( 'path' );
	$title = string_rss_links( config_get( 'window_title' ) . ' - ' . lang_get( 'news' ) );
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
