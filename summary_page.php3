<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<? include( $g_meta_include_file ) ?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>
<?
	db_mysql_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
?>

<p>
<? print_menu( $g_menu_include_file ) ?>

<?
	### t_active_bug_count
    $query = "SELECT COUNT(id)
    		FROM $g_mantis_bug_table
    		WHERE status<>'resolved'";
    $result = db_mysql_query( $query );
	$t_active_bug_count = mysql_result( $result, 0 );

    ### t_fixed_bug_count
    $query = "SELECT COUNT(id)
    		FROM $g_mantis_bug_table
    		WHERE status='resolved'";
    $result = db_mysql_query( $query );
	$t_fixed_bug_count = mysql_result( $result, 0 );

	### t_not_fixable_bug_count
    $query = "SELECT COUNT(id)
    		FROM $g_mantis_bug_table
    		WHERE resolution='not fixable'";
    $result = db_mysql_query( $query );
	$t_not_fixable_bug_count = mysql_result( $result, 0 );

	### t_crashing_bug_count
    $query = "SELECT COUNT(id)
    		FROM $g_mantis_bug_table
    		WHERE severity='crash'";
    $result = db_mysql_query( $query );
	$t_crashing_bug_count = mysql_result( $result, 0 );

	### t_major_bug_count
	$t_crashing_bug_count = mysql_result( $result, 0 );
    $query = "SELECT COUNT(id)
    		FROM $g_mantis_bug_table
    		WHERE severity='major'";
    $result = db_mysql_query( $query );
	$t_major_bug_count = mysql_result( $result, 0 );

	### t_minor_bug_count
	$t_crashing_bug_count = mysql_result( $result, 0 );
    $query = "SELECT COUNT(id)
    		FROM $g_mantis_bug_table
    		WHERE severity='minor'";
    $result = db_mysql_query( $query );
	$t_minor_bug_count = mysql_result( $result, 0 );
?>

<p>
<table width=100% bgcolor=<? echo $g_primary_border_color ?>>
<tr>
	<td bgcolor=<? echo $g_white_color ?>>
	<table width=100% cols=4>
	<tr>
		<td bgcolor=<? echo $g_white_color ?> colspan=4>
			<b>Summary</b>
		</td>
	</tr>
	<tr align=center bgcolor=<? echo $g_primary_color_dark ?>>
		<td width=30%>
			<b>active bugs</b>
		</td>
		<td width=20%>
			<? echo $t_active_bug_count ?>
		</td>
		<td width=30%>
			<b>fixed bugs</b>
		</td>
		<td width=20%>
			<? echo $t_fixed_bug_count ?>
		</td>
	</tr>
	<tr align=center bgcolor=<? echo $g_primary_color_light ?>>
		<td>
			<b>non fixable bugs</b>
		</td>
		<td>
			<? echo $t_not_fixable_bug_count ?>
		</td>
		<td>
			<b>crashing bugs</b>
		</td>
		<td>
			<? echo $t_crashing_bug_count ?>
		</td>
	</tr>
	<tr align=center bgcolor=<? echo $g_primary_color_dark ?>>
		<td>
				<b>major bugs</b>
		</td>
		<td>
			<? echo $t_major_bug_count ?>
		</td>
		<td>
				<b>minor bugs</b>
		</td>
		<td>
			<? echo $t_minor_bug_count ?>
		</td>
	</tr>
	</table>
	</td>
</tr>
</table>

<? print_footer() ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>