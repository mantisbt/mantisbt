<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# This is the first page a user sees when they login to the bugtracker
	# News is displayed which can notify users of any important changes
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<table class="hide">
<tr>
	<td class="quick-summary-left">
		<?php echo $s_open_and_assigned_to_me ?>:
		<?php PRINT '<a href="view_all_set.php?f_type=1&amp;f_user_id=any&amp;f_show_status=any&amp;f_show_severity=any&amp;f_show_category=any&amp;f_assign_id=' .  get_current_user_field( 'id') . '&amp;f_hide_closed=on">' . get_assigned_open_bug_count($g_project_cookie_val,$g_string_cookie_val) . '</a>' ?>
	</td>
	<td class="quick-summary-right">
		<?php echo $s_open_and_reported_to_me ?>:
		<?php PRINT '<a href="view_all_set.php?f_type=1&amp;f_user_id=' . get_current_user_field( 'id') . '&amp;f_show_status=any&amp;f_show_severity=any&amp;f_show_category=any&amp;f_assign_id=any&amp;f_hide_closed=on">' . get_reported_open_bug_count($g_project_cookie_val,$g_string_cookie_val) . '</a>' ?>
		<?php echo  ?>
	</td>
</tr>
</table>

<?php
	# Check to see if variable is set
	check_varset( $f_offset, 0 );
	$c_offset = (integer)$f_offset;

	# get news count (project plus sitewide posts)
    $total_news_count = news_get_count( $g_project_cookie_val );

	switch ( $g_news_limit_method ) {
		case 0 :
			# Select the news posts
			$query = "SELECT *, UNIX_TIMESTAMP(date_posted) as date_posted
					FROM $g_mantis_news_table
					WHERE project_id='$g_project_cookie_val' OR project_id='0000000'
					ORDER BY announcement DESC, id DESC
					LIMIT $c_offset, $g_news_view_limit";
			break;
		case 1 :
			# Select the news posts
			$query = "SELECT *, UNIX_TIMESTAMP(date_posted) as date_posted
					FROM $g_mantis_news_table
					WHERE ( project_id='$g_project_cookie_val' OR project_id='0000000' ) AND
						(TO_DAYS(NOW()) - TO_DAYS(date_posted) < '$g_news_view_limit_days')
					ORDER BY announcement DESC, id DESC";
			break;
	} # end switch
	$result = db_query( $query );
    $news_count = db_num_rows( $result );

    # Loop through results
	for ($i=0;$i<$news_count;$i++) {
		$row = db_fetch_array($result);
		extract( $row, EXTR_PREFIX_ALL, 'v' );

		$v_headline 	= string_display( $v_headline );
		$v_body 		= string_display( $v_body );
		$v_date_posted 	= date( $g_normal_date_format, $v_date_posted );

		# only show PIRVATE posts to configured threshold and above
		if (( PRIVATE == $v_view_state ) &&
			!access_level_check_greater_or_equal( $g_private_news_threshold )) {
			continue;
		}

		## grab the username and email of the poster
		$t_poster_name	= user_get_name( $v_poster_id );
		$t_poster_email	= user_get_email( $v_poster_id );

		if ( PRIVATE == $v_view_state ) {
			$t_news_css = 'news-heading-private';
		} else {
			$t_news_css = 'news-heading-public';
		}
?>
<p>
<div align="center">
<table class="width75" cellspacing="0">
<tr>
	<td class="<?php echo $t_news_css ?>">
		<span class="news-headline"><?php echo $v_headline ?></span> -
		<span class="news-date"><?php echo $v_date_posted ?></span> -
		<a class="news-email" href="mailto:<?php echo $t_poster_email ?>"><?php echo $t_poster_name ?></a>
		<span class='small'>
		<?php
			if ( 1 == $v_announcement ) {
				PRINT '['.$s_announcement.']';
			}
		?>
		<?php
			if ( PRIVATE == $v_view_state ) {
				PRINT '['.$s_private.']';
			}
		?>
		</span>
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
	print_bracket_link( 'news_list_page.php', $s_archives );
	$f_offset_next = $f_offset + $g_news_view_limit;
	$f_offset_prev = $f_offset - $g_news_view_limit;

	if ( $f_offset_prev >= 0) {
		print_bracket_link( 'main_page.php?f_offset='.$f_offset_prev, $s_newer_news_link );
	}
	if ( $news_count == $g_news_view_limit ) {
		print_bracket_link( 'main_page.php?f_offset='.$f_offset_next, $s_older_news_link );
	}
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
