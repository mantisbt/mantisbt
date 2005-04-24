<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: issues_rss.php,v 1.1 2005-04-24 13:22:21 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path . 'class.RSSBuilder.inc.php' );
	require_once( $t_core_path . 'user_api.php' );
	require_once( $t_core_path . 'filter_api.php' );

	# only allow RSS generation of anonymous login is enabled.
	# @@@ consider adding an explicit option to enable/disable RSS syndication.
	if ( OFF == config_get( 'allow_anonymous_login' ) ) {
		access_denied();
	}

	$f_project_id = gpc_get_int( 'project_id', 0 );

	$t_path = config_get( 'path' );

	# construct rss file

	$encoding = lang_get( 'charset' );
	$about = $t_path;
	$title = config_get( 'window_title' ) . ' - ' . lang_get( 'issues' );
	$description = '';  // @@@ add a description
	$image_link = $t_path . 'images/mantis_logo_button.gif';

	# only rss 2.0
	$category = project_get_name( $f_project_id );
	if ( $f_project_id !== 0 ) {
		$title .= ' - ' . $category;
	}

	# in minutes (only rss 2.0)
	$cache = '10';

	$rssfile = new RSSBuilder(	$encoding, $about, $title, $description,
					$image_link, $category, $cache);

	# person, an organization, or a service
	$publisher = '';

	# person, an organization, or a service
	$creator = '';

	$date = (string) date('Y-m-d\TH:i:sO');
	$language = lang_get( 'phpmailer_language' );
	$rights = '';

	# spatial location , temporal period or jurisdiction
	$coverage = (string) '';

	# person, an organization, or a service
	$contributor = (string) '';

	$rssfile->addDCdata( $publisher, $creator, $date, $language, $rights, $coverage, $contributor );

	# hourly / daily / weekly / ...
	$period = (string) 'hourly';

	# every X hours/days/...
	$frequency = (int) 1;

	$base = (string) date('Y-m-d\TH:i:sO');

	$rssfile->addSYdata( $period, $frequency, $base );

	$t_page_number = 1;
	$t_issues_per_page = 25;
	$t_page_count = 0;
	$t_issues_count = 0;
	$t_custom_filter = null;
	$t_project_id = $f_project_id;
	$t_user_id = user_get_id_by_name( config_get( 'anonymous_account' ) );
	$t_show_sticky = null;

	$t_issues = filter_get_bug_rows( $t_page_number, $t_issues_per_page, $t_page_count, $t_issues_count,
									 $t_custom_filter, $t_project_id, $t_user_id, $t_show_sticky );

	# Loop through results
	for ( $i = 0; $i < count( $t_issues ); $i++ ) {
		$row = $t_issues[$i];

		$t_bug = bug_get( $row['id'], true );

		$about = $link = $t_path . "view.php?id=" . $row['id'];
		$title = string_display_links( $t_bug->summary );
		$description = string_display_links( $t_bug->description );

		# optional DC value
		$subject = $title;

		# optional DC value
		$date = date( 'Y-m-d\TH:i:sO', $t_bug->last_updated );

		# author of item
		$author = user_get_name( $t_bug->reporter_id );

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