<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( 'core_API.php' ) ?>
<?php login_cookie_check() ?>
<?php
	check_access( MANAGER );

	# Add news
    check_varset( $f_announcement, '' );
	$result 	= news_add_query( $f_project_id, $f_poster_id, $f_view_state, $f_announcement, $f_headline, $f_body );
    $f_headline = string_display( $f_headline );
    $f_body 	= string_display( $f_body );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<p>
<div align="center">
<?php
	if ( $result ) {			# SUCCESS
		PRINT "$s_operation_successful<p>";
?>
<table class="width75" cellspacing="1">
<tr>
	<td class="news-heading">
		<span class="news-headline"><?php echo $f_headline ?></span>
	</td>
</tr>
<tr>
	<td class="news-body">
		<?php echo $f_body ?>
	</td>
</tr>
</table>
<p>
<?php
	} else {					# FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( 'news_menu_page.php', $s_proceed );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
