<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: news_inc.php,v 1.1 2004-02-08 08:00:06 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	#---------------
	# Print one news entry given the row retrieved from the news table.
	function print_news_entry( $p_headline, $p_body, $p_poster_id, $p_view_state, $p_announcement, $p_date_posted ) {
		$t_headline = string_display_links( $p_headline );
		$t_body = string_display_links( $p_body );
		$t_date_posted = date( config_get( 'normal_date_format' ), $p_date_posted );

		if ( VS_PRIVATE == $p_view_state ) {
			$t_news_css = 'news-heading-private';
		} else {
			$t_news_css = 'news-heading-public';
		}

		echo '<div align="center">';
		echo '<table class="width75" cellspacing="0">';
		echo '<tr>';
		echo "<td class=\"$t_news_css\">";
		echo "<span class=\"bold\">$t_headline</span> - ";
		echo "<span class=\"italic-small\">$t_date_posted</span> - ";
		print_user( $p_poster_id );
		echo ' <span class="small">';
		if ( 1 == $p_announcement ) {
			echo '[' . lang_get( 'announcement' ) . ']';
		}
		if ( VS_PRIVATE == $p_view_state ) {
			echo '[' . lang_get( 'private' ) . ']';
		}
		echo '</span>';
		echo '</td>';
		echo '</tr>';
		echo '<tr>';
		echo "<td class=\"news-body\">$t_body</td>";
		echo '</tr>';
		echo '</table>';
		echo '</div>';
	}
?>