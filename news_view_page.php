<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( 'core_API.php' ) ?>
<?php login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>
<?php
	# Select the news posts
	$c_id = (integer)$f_id;
	$query = "SELECT *, UNIX_TIMESTAMP(date_posted) as date_posted
			FROM $g_mantis_news_table
			WHERE (project_id='$g_project_cookie_val' OR project_id='0000000') AND
					id='$c_id'";
	$result = db_query( $query );
    $news_count = db_num_rows( $result );

    # Loop through results
	for ($i=0;$i<$news_count;$i++) {
		$row = db_fetch_array($result);
		extract( $row, EXTR_PREFIX_ALL, 'v' );

		$v_headline 	= string_display( $v_headline );
		$v_body 		= string_display( $v_body );
		$v_date_posted 	= date( $g_normal_date_format, $v_date_posted );

		## grab the username and email of the poster
    	$row2 = get_user_info_by_id_arr( $v_poster_id );
		if ( $row2 ) {
			$t_poster_name	= $row2['username'];
			$t_poster_email	= $row2['email'];
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

<p>
<div align="center">
	<?php print_bracket_link( 'news_list_page.php', $s_back_link ) ?>
</div>

<?php print_page_bot1( __FILE__ ) ?>