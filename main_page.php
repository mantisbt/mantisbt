<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# This is the first page a user sees when they login to the bugtracker
	# News is displayed which can notify users of any important changes
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check();

	$f_offset = gpc_get_int( 'offset', 0 );

?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<div class="quick-summary-left">
		<?php echo lang_get( 'open_and_assigned_to_me' ) ?>:
		<?php echo '<a href="view_all_set.php?type=1&amp;reporter_id=any&amp;show_status=any&amp;show_severity=any&amp;show_category=any&amp;handler_id=' .  auth_get_current_user_id() . '&amp;hide_closed=on">' . current_user_get_assigned_open_bug_count() . '</a>' ?>
</div>
<div class="quick-summary-right">
		<?php echo lang_get( 'open_and_reported_to_me' ) ?>:
		<?php PRINT '<a href="view_all_set.php?type=1&amp;reporter_id=' . auth_get_current_user_id() . '&amp;show_status=any&amp;show_severity=any&amp;show_category=any&amp;handler_id=any&amp;hide_closed=on">' . current_user_get_reported_open_bug_count() . '</a>' ?>
</div>
<br />

<?php
	$c_offset = db_prepare_int( $f_offset );

	$t_project_id = helper_get_current_project();

	# get news count (project plus sitewide posts)
    $total_news_count = news_get_count( $t_project_id );

	$t_news_table			= config_get( 'mantis_news_table' );
	$t_news_view_limit		= config_get( 'news_view_limit' );
	$t_news_view_limit_days	= config_get( 'news_view_limit_days' );

	switch ( config_get( 'news_limit_method' ) ) {
		case 0 :
			# Select the news posts
			$query = "SELECT *, UNIX_TIMESTAMP(date_posted) as date_posted
					FROM $t_news_table
					WHERE project_id='$t_project_id' OR project_id=0
					ORDER BY announcement DESC, id DESC
					LIMIT $c_offset, $t_news_view_limit";
			break;
		case 1 :
			# Select the news posts
			$query = "SELECT *, UNIX_TIMESTAMP(date_posted) as date_posted
					FROM $t_news_table
					WHERE ( project_id='$t_project_id' OR project_id=0 ) AND
						(TO_DAYS(NOW()) - TO_DAYS(date_posted) < '$t_news_view_limit_days')
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
		$v_date_posted 	= date( config_get( 'normal_date_format' ), $v_date_posted );

		# only show PRIVATE posts to configured threshold and above
		if ( ( PRIVATE == $v_view_state ) &&
			 !access_level_check_greater_or_equal( config_get( 'private_news_threshold' ) ) ) {
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
<br />
<div align="center">
<table class="width75" cellspacing="0">
<tr>
	<td class="<?php echo $t_news_css ?>">
		<span class="bold"><?php echo $v_headline ?></span> -
		<span class="italic-small"><?php echo $v_date_posted ?></span> -
		<a class="small" href="mailto:<?php echo $t_poster_email ?>"><?php echo $t_poster_name ?></a>
		<span class='small'>
		<?php
			if ( 1 == $v_announcement ) {
				PRINT '[' . lang_get( 'announcement' ) . ']';
			}
		?>
		<?php
			if ( PRIVATE == $v_view_state ) {
				PRINT '[' . lang_get( 'private' ) . ']';
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
<br />
<div align="center">
<?php
	print_bracket_link( 'news_list_page.php', lang_get( 'archives' ) );
	$f_offset_next = $f_offset + $t_news_view_limit;
	$f_offset_prev = $f_offset - $t_news_view_limit;

	if ( $f_offset_prev >= 0) {
		print_bracket_link( 'main_page.php?offset=' . $f_offset_prev, lang_get( 'newer_news_link ' ) );
	}
	if ( $news_count == $t_news_view_limit ) {
		print_bracket_link( 'main_page.php?offset=' . $f_offset_next, lang_get( 'older_news_link ' ) );
	}
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
