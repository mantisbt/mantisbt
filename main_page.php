<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# This is the first page a user sees when they login to the bugtracker
	# News is displayed which can notify users of any important changes
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?php db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name ) ?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<table class="hide">
<tr>
	<td class="quick-summary-left">
		<?php echo $s_open_and_assigned_to_me ?>:
		<?php echo get_assigned_open_bug_count($g_project_cookie_val,$g_string_cookie_val) ?>
	</td>
	<td class="quick-summary-right">
		<?php echo $s_open_and_reported_to_me ?>:
		<?php echo get_reported_open_bug_count($g_project_cookie_val,$g_string_cookie_val) ?>
	</td>
</tr>
</table>

<?php
	# Check to see if variable is set
	if ( !isset( $f_offset ) ) {
		$f_offset = 0;
	}

	# get news count (project plus sitewide posts)
    $total_news_count = news_count_query( $g_project_cookie_val );

	switch ( $g_news_limit_method ) {
		case 0 :
			# Select the news posts
			$query = "SELECT *, UNIX_TIMESTAMP(date_posted) as date_posted
					FROM $g_mantis_news_table
					WHERE project_id='$g_project_cookie_val' OR project_id='0000000'
					ORDER BY id DESC
					LIMIT $f_offset, $g_news_view_limit";
			break;
		case 1 :
			# Select the news posts
			$query = "SELECT *, UNIX_TIMESTAMP(date_posted) as date_posted
					FROM $g_mantis_news_table
					WHERE ( project_id='$g_project_cookie_val' OR project_id='0000000' ) AND
						(TO_DAYS(NOW()) - TO_DAYS(date_posted) < '$g_news_view_limit_days')
					ORDER BY id DESC";
			break;
	} # end switch
	$result = db_query( $query );
    $news_count = db_num_rows( $result );

    # Loop through results
	for ($i=0;$i<$news_count;$i++) {
		$row = db_fetch_array($result);
		extract( $row, EXTR_PREFIX_ALL, "v" );

		$v_headline 	= string_display( $v_headline );
		$v_body 		= string_display( $v_body );
		$v_date_posted 	= date( $g_normal_date_format, $v_date_posted );

		## grab the username and email of the poster
    	$row2 = get_user_info_by_id_arr( $v_poster_id );
		$t_poster_name	= "";
		$t_poster_email	= "";
		if ( $row2 ) {
			$t_poster_name	= $row2["username"];
			$t_poster_email	= $row2["email"];
		}
?>
<p>
<div align="center">
<table class="width75" cellspacing="0">
<tr>
	<td class="news-heading">
		<span class="news-headline"><?php echo $v_headline ?></span> -
		<span class="news-date"><?php echo $v_date_posted ?></span> -
		<a class="news-email" href="mailto:<?php echo $t_poster_email ?>"><?php echo $t_poster_name ?></a>
	</td>
</tr>
<tr>
	<td class="news-body">
		<?php echo $v_body ?>
	</td>
</tr>
</table>
</div>
<?php
	}  # end for loop
?>

<?php # Print NEXT and PREV links if necessary ?>
<p>
<div align="center">
<?php
	print_bracket_link( $g_news_list_page, $s_archives );
	$f_offset_next = $f_offset + $g_news_view_limit;
	$f_offset_prev = $f_offset - $g_news_view_limit;

	if ( $f_offset_prev >= 0) {
		print_bracket_link( $g_main_page."?f_offset=".$f_offset_prev, $s_newer_news_link );
	}
	if ( $news_count == $g_news_view_limit ) {
		print_bracket_link( $g_main_page."?f_offset=".$f_offset_next, $s_older_news_link );
	}
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>