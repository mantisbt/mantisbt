<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( VIEWER );

	### Select project files
	$query = "SELECT *
			FROM mantis_project_file_table
			WHERE project_id='$g_project_cookie_val'";
	$result = db_query( $query );
	$num_files = db_num_rows( $result );
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<? include( $g_meta_include_file ) ?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>

<? print_menu( $g_menu_include_file ) ?>

<? print_doc_menu( $g_proj_doc_page ) ?>

<p>
<div align="center">
<table width="100%" bgcolor="<? echo $g_primary_border_color ?>" <? echo $g_primary_table_tags ?>>
<tr>
	<td bgcolor="<? echo $g_white_color ?>">
	<table width="100%" bgcolor="<? echo $g_white_color ?>">
	<tr>
		<td colspan="6" bgcolor="<? echo $g_table_title_color ?>">
			<b><? echo $s_project_documentation_title ?></b>
		</td>
	</tr>
	<?
		for ($i=0;$i<$num_files;$i++) {
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, "v" );
			$v_diskfile 	= str_replace( $DOCUMENT_ROOT, "", $v_diskfile );
			$v_filesize 	= round( $v_filesize / 1024 );
			$v_title 		= string_display( $v_title );
			$v_description 	= string_display( $v_description );

			### alternate row colors
			$status_color = alternate_colors( $i, $g_primary_color_dark, $g_primary_color_light );
	?>
	<tr valign="top" bgcolor="<? echo $status_color ?>">
		<td bgcolor="<? echo $g_category_title_color ?>">
			<b><a href="<? echo $v_diskfile ?>"><? echo $v_title ?></a></b>
		<?
			if ( access_level_check_greater_or_equal( MANAGER ) ) {
				print_bracket_link( $g_proj_doc_edit_page."?f_id=".$v_id, "edit" );
			}
		?>
			<br><span class="small">(<? echo $v_filesize ?> KB)</a>
		</td>
		<td>
			<? echo $v_description ?>
		</td>
	</tr>
	<? } ### end for loop ?>
	</table>
	</td>
</tr>
</table>
</div>


<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>