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
<?php
	$f_id = gpc_get_int( 'f_id' );

	$row = news_get_row( $f_id );

	extract( $row, EXTR_PREFIX_ALL, 'v' );

	$v_headline 	= string_display( $v_headline );
	$v_body 		= string_display( $v_body );
	$v_date_posted 	= date( config_get( 'normal_date_format' ), $v_date_posted );

	## grab the username and email of the poster
	$t_poster_name	= user_get_name( $v_poster_id );
	$t_poster_email	= user_get_email( $v_poster_id );
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

<p>
<div align="center">
	<?php print_bracket_link( 'news_list_page.php', $s_back_link ) ?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
