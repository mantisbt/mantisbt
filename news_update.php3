<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( MANAGER );

	# Update news
    $result = news_update_query( $f_id, $f_headline, $f_body, $f_project_id );

    $f_headline 	= string_display( $f_headline );
    $f_body 		= string_display( $f_body );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<p>
<div align="center">
<?
	if ( $result ) {				# SUCCESS
		PRINT "$s_operation_successful<p>";
?>
<table class="width75" cellspacing="0">
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
<?
	} else {						# FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $g_news_edit_page."?f_id=".$f_id."&f_action=edit", $s_edit_link );
	print_bracket_link( $g_news_menu_page, $s_proceed );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>