<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<br />
<ul>
<?php
	# Select the news posts
	$rows = news_get_rows( helper_get_current_project() );
    # Loop through results
	for ( $i=0 ; $i < sizeof( $rows ) ; $i++ ) {
		extract( $rows[$i], EXTR_PREFIX_ALL, 'v' );

		$v_headline 	= string_display( $v_headline );
		$v_date_posted 	= date( config_get( 'complete_date_format' ), $v_date_posted );

		# grab the username and email of the poster
		$t_poster_name	= user_get_name( $v_poster_id );
		$t_poster_email	= user_get_email( $v_poster_id );

		$t_notes = array();
		$t_note_string = '';
		if ( 1 == $v_announcement ) {
			array_push( $t_notes, $s_announcement );
		}
		if ( PRIVATE == $v_view_state ) {
			array_push( $t_notes, $s_private );
		}
		if ( sizeof( $t_notes ) > 0 ) {
			$t_note_string = '['.implode( ' ', $t_notes ).']';
		}

		PRINT "<li><span class=\"italic-small\">$v_date_posted</span> - <span class=\"bold\"><a href=\"news_view_page.php?news_id=$v_id\">$v_headline</a></span> <span class=\"small\">$t_note_string</span> <a class=\"small\" href=\"mailto:$t_poster_email\">$t_poster_name</a></li>";
	}  # end for loop
?>
</ul>

<?php print_page_bot1( __FILE__ ) ?>
