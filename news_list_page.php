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

<p>
<ul>
<?php
	# Select the news posts
	$query = "SELECT *, UNIX_TIMESTAMP(date_posted) as date_posted
			FROM $g_mantis_news_table
			WHERE project_id='$g_project_cookie_val' OR project_id='0000000'
			ORDER BY id DESC";
	$result = db_query( $query );
    $news_count = db_num_rows( $result );

    # Loop through results
	for ($i=0;$i<$news_count;$i++) {
		$row = db_fetch_array($result);
		extract( $row, EXTR_PREFIX_ALL, 'v' );

		$v_headline 	= string_display( $v_headline );
		$v_date_posted 	= date( $g_complete_date_format, $v_date_posted );

		# grab the username and email of the poster
    	$row2 = get_user_info_by_id_arr( $v_poster_id );
		if ( $row2 ) {
			$t_poster_name	= $row2['username'];
			$t_poster_email	= $row2['email'];
		}

		$t_note = '';
		if ( 1 == $v_announcement ) {
			$t_note = $s_announcement;
		}
		if ( PRIVATE == $v_view_state ) {
			$t_note .= ' '.$s_private;
		}
		if ( !empty( $t_note ) ) {
			$t_note = '['.$t_note.']';
		}
		PRINT "<li><span class=\"news-date\">$v_date_posted</span> - <span class=\"news-headline\"><a href=\"news_view_page.php?f_id=$v_id\">$v_headline</a></span> <span class=\"small\">$t_note</span> <a class=\"news-email\" href=\"mailto:$t_poster_email\">$t_poster_name</a></li>";
	}  # end for loop
?>
</ul>

<?php print_page_bot1( __FILE__ ) ?>
