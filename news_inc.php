<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: news_inc.php,v 1.2 2004-02-10 11:37:43 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	#---------------
	# Constructs the string for one news entry given the row retrieved from the news table.
	function print_news_entry( $p_headline, $p_body, $p_poster_id, $p_view_state, $p_announcement, $p_date_posted ) {
		$t_headline = string_display_links( $p_headline );
		$t_body = string_display_links( $p_body );
		$t_date_posted = date( config_get( 'normal_date_format' ), $p_date_posted );

		if ( VS_PRIVATE == $p_view_state ) {
			$t_news_css = 'news-heading-private';
		} else {
			$t_news_css = 'news-heading-public';
		}

		$output = '<div align="center">';
		$output .= '<table class="width75" cellspacing="0">';
		$output .= '<tr>';
		$output .= "<td class=\"$t_news_css\">";
		$output .= "<span class=\"bold\">$t_headline</span> - ";
		$output .= "<span class=\"italic-small\">$t_date_posted</span> - ";
		echo $output;

		# @@@ eventually we should replace print's with methods to construct the
		#     strings.
		print_user( $p_poster_id );
		$output = '';

		$output .= ' <span class="small">';
		if ( 1 == $p_announcement ) {
			$output .= '[' . lang_get( 'announcement' ) . ']';
		}
		if ( VS_PRIVATE == $p_view_state ) {
			$output .= '[' . lang_get( 'private' ) . ']';
		}

		$output .= '</span>';
		$output .= '</td>';
		$output .= '</tr>';
		$output .= '<tr>';
		$output .= "<td class=\"news-body\">$t_body</td>";
		$output .= '</tr>';
		$output .= '</table>';
		$output .= '</div>';

		echo $output;
	}

	# --------------------
	# print a news item given a row in the news table.
        function print_news_entry_from_row( $p_news_row ) {
		extract( $p_news_row, EXTR_PREFIX_ALL, 'v' );
		print_news_entry( $v_headline, $v_body, $v_poster_id, $v_view_state, $v_announcement, $v_date_posted );
	}

	# --------------------
	# print a news item
	function print_news_string_by_news_id( $p_news_id ) {
		$row = news_get_row( $p_news_id );

		# only show VS_PRIVATE posts to configured threshold and above
		if ( ( VS_PRIVATE == $row['view_state'] ) &&
			 !access_has_project_level( config_get( 'private_news_threshold' ) ) ) {
			continue;
		}

		print_news_entry_from_row( $row );
	}
?>
