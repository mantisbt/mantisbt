<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Removes all the cookies and then redirect to $g_logout_redirect_page
?>
<?php include( "core_API.php" ); ?>
<?php
	# delete cookies then redirect to $g_logout_redirect_page
	setcookie( $g_string_cookie );
	setcookie( $g_project_cookie );
	setcookie( $g_view_all_cookie );
	setcookie( $g_manage_cookie );

	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
?>
<?php print_page_top1() ?>
<?php print_meta_redirect( $g_logout_redirect_page, $g_wait_time ) ?>
<?php print_page_top2a() ?>

<p>
<div align="center">
<table class="width50" cellspacing="1">
<tr>
	<td class="form-title">
		<?php echo $s_logged_out_title ?>
	</td>
</tr>
<tr class="row-1">
	<td class="right">
		<?php echo $s_redirecting ?> <a href="<?php echo $g_logout_redirect_page ?>"><?php echo $s_here ?></a>
	</td>
</tr>
</table>
</div>

<?php print_bottom_page( $g_bottom_include_page ) ?>
<?php print_body_bottom() ?>
<?php print_html_bottom() ?>