<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: news_rss.php,v 1.1 2004-02-10 11:37:43 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path . 'class.RSSBuilder.inc.php' );
	require_once( $t_core_path . 'news_api.php' );
	require_once( $t_core_path . 'project_api.php' );
	require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'news_inc.php' );

	if ( OFF == config_get( 'allow_anonymous_login' ) ) {
		access_denied();
	}

	// Make sure that the user selected either all projects or a public one.
	$f_project_id = gpc_get_int( 'project_id', 0 );
	if ( ( $f_project_id != 0 ) && ( VS_PRIVATE == project_get_field( $f_project_id, 'view_state' ) ) ) {
		access_denied();
	}

	// construct rss file

	$encoding = lang_get( 'charset' );
	$about = config_get( 'path' );
	$title = config_get( 'window_title' );
	$description = '';
	$image_link = '';
	$category = project_get_name( $f_project_id ); // only rss 2.0
	$cache = '60'; // in minutes (only rss 2.0)

	$rssfile = new RSSBuilder(	$encoding, $about, $title, $description, $image_link, 
					$category, $cache);

	/* if you want you can add additional Dublic Core data to the basic rss file (if rss version supports it) */
	$publisher = ''; // person, an organization, or a service
	$creator = ''; // person, an organization, or a service
	$date = (string) date('Y-m-d\TH:i:sO');
	$language = lang_get( 'phpmailer_language' );
	$rights = '';
	$coverage = (string) ''; // spatial location , temporal period or jurisdiction
	$contributor = (string) ''; // person, an organization, or a service
	$rssfile->addDCdata(	$publisher, $creator, $date, $language, $rights, $coverage, 
				$contributor);

	/* if you want you can add additional Syndication data to the basic rss file (if rss version supports it) */
	$period = (string) 'daily'; // hourly / daily / weekly / ...
	$frequency = (int) 1; // every X hours/days/...
	$base = (string) date('Y-m-d\TH:i:sO');
	$rssfile->addSYdata($period, $frequency, $base);

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

		$v_headline 	= string_display_links( $v_headline );
		$v_body 	= string_display_links( $v_body );
		$v_date_posted 	= date( 'Y-m-d\TH:i:sO', $v_date_posted );

		$about = $link = config_get( 'path' ) . "news.php?news_id=$v_id";
		$title = $v_headline;
		$description = $v_body;
		$subject = $title; // optional DC value
		$date = $v_date_posted; // optional DC value
		$author = user_get_name( $v_poster_id ); // author of item
		$comments = ''; // $comments = 'http://flaimo.com/sometext.php?somevariable=somevalue&comments=1'; // url to comment page rss 2.0 value
		$image = ''; // optional mod_im value for dispaying a different pic for every item
		$rssfile->addItem(	$about, $title, $link, $description, $subject, $date,	
					$author, $comments, $image);
	}

	# @@@ should be a configuration option
	$version = '2.0'; // 0.91 / 1.0 / 2.0

	$rssfile->outputRSS($version);
?>