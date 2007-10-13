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
	# $Id: news_list_page.php,v 1.31.22.1 2007-10-13 22:34:05 giallu Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'news_api.php' );
	require_once( $t_core_path.'string_api.php' );
?>
<?php
	access_ensure_project_level( VIEWER );
?>
<?php html_page_top1() ?>
<?php html_page_top2() ?>

<br />
<ul>
<?php
	# Select the news posts
	$rows = news_get_rows( helper_get_current_project() );
    # Loop through results
	for ( $i=0 ; $i < sizeof( $rows ) ; $i++ ) {
		extract( $rows[$i], EXTR_PREFIX_ALL, 'v' );
		if ( VS_PRIVATE == $v_view_state &&
			 ! access_has_project_level( config_get( 'private_news_threshold' ), $v_project_id ) ) 		{
			continue;
		}

		$v_headline 	= string_display( $v_headline );
		$v_date_posted 	= date( config_get( 'complete_date_format' ), $v_date_posted );

		$t_notes = array();
		$t_note_string = '';
		if ( 1 == $v_announcement ) {
			array_push( $t_notes, lang_get( 'announcement' ) );
		}
		if ( VS_PRIVATE == $v_view_state ) {
			array_push( $t_notes, lang_get( 'private' ) );
		}
		if ( sizeof( $t_notes ) > 0 ) {
			$t_note_string = '['.implode( ' ', $t_notes ).']';
		}

		echo "<li><span class=\"italic-small\">$v_date_posted</span> - <span class=\"bold\"><a href=\"news_view_page.php?news_id=$v_id\">$v_headline</a></span> <span class=\"small\"> ";
		print_user( $v_poster_id );
		echo ' ' . $t_note_string;
		echo "</span></li>";
	}  # end for loop
?>
</ul>

<?php html_page_bottom1( __FILE__ ) ?>
