<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: main_page.php,v 1.55 2004-10-24 02:40:00 vboctor Exp $
	# --------------------------------------------------------

	# This is the first page a user sees when they login to the bugtracker
	# News is displayed which can notify users of any important changes

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'current_user_api.php' );
	require_once( $t_core_path.'news_api.php' );
	require_once( $t_core_path.'date_api.php' );
	require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'news_inc.php' );

	access_ensure_project_level( VIEWER );

	$f_offset = gpc_get_int( 'offset', 0 );

	$t_project_id = helper_get_current_project();

	if ( ( $t_project_id == ALL_PROJECTS ) || ( VS_PRIVATE != project_get_field( $t_project_id, 'view_state' ) ) ) {
		html_set_rss_link( "news_rss.php?project_id=$t_project_id" );
	}

	html_page_top1();
	html_page_top2();

	if ( !current_user_is_anonymous() ) {
		echo '<div class="quick-summary-left">';
		echo lang_get( 'open_and_assigned_to_me' ) . ': ';
		echo '<a class="subtle" href="view_all_set.php?type=1&amp;handler_id=' .  auth_get_current_user_id() . '&amp;hide_status=' . RESOLVED . '">' . current_user_get_assigned_open_bug_count() . '</a>';
		echo '</div>';

		echo '<div class="quick-summary-right">';
		echo lang_get( 'open_and_reported_to_me' ) . ': ';
		echo '<a class="subtle" href="view_all_set.php?type=1&amp;reporter_id=' . auth_get_current_user_id() . '&amp;hide_status=' . RESOLVED . '">' . current_user_get_reported_open_bug_count() . '</a>';
		echo '</div>';

		echo '<div class="quick-summary-left">';
		echo lang_get( 'last_visit' ) . ': ';
		echo print_date( config_get( 'normal_date_format' ), db_unixtimestamp( current_user_get_field( 'last_visit' ) ) );
		echo '</div>';
	}

	echo '<br />';
	echo '<br />';
	echo '<br />';

	$t_news_rows = news_get_limited_rows( $f_offset, $t_project_id );
	$t_news_count = count( $t_news_rows );

	# Loop through results
	for ( $i = 0; $i < $t_news_count; $i++ ) {
		$t_row = $t_news_rows[$i];

		# only show VS_PRIVATE posts to configured threshold and above
		if ( ( VS_PRIVATE == $t_row[ 'view_state' ] ) &&
			 !access_has_project_level( config_get( 'private_news_threshold' ) ) ) {
			continue;
		}

		print_news_entry_from_row( $t_row );
		echo '<br />';
	}  # end for loop

	echo '<div align="center">';

	print_bracket_link( 'news_list_page.php', lang_get( 'archives' ) );
	$t_news_view_limit = config_get( 'news_view_limit' );
	$f_offset_next = $f_offset + $t_news_view_limit;
	$f_offset_prev = $f_offset - $t_news_view_limit;

	if ( $f_offset_prev >= 0) {
		print_bracket_link( 'main_page.php?offset=' . $f_offset_prev, lang_get( 'newer_news_link' ) );
	}

	if ( $t_news_count == $t_news_view_limit ) {
		print_bracket_link( 'main_page.php?offset=' . $f_offset_next, lang_get( 'older_news_link' ) );
	}

	print_bracket_link( "news_rss.php?project_id=$t_project_id", lang_get( 'rss' ) );

	echo '</div>';

	html_page_bottom1( __FILE__ );
?>