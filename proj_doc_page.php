<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( VIEWER );

	# Select project files
	$query = "SELECT *
			FROM $g_mantis_project_file_table
			WHERE project_id='$g_project_cookie_val'";
	$result = db_query( $query );
	$num_files = db_num_rows( $result );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<p>
<div align="center">
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title">
		<?php echo $s_project_documentation_title ?>
	</td>
	<td class="right">
		<?php print_doc_menu( $g_proj_doc_page ) ?>
	</td>
</tr>
<?php
	for ($i=0;$i<$num_files;$i++) {
		$row = db_fetch_array( $result );
		extract( $row, EXTR_PREFIX_ALL, "v" );
		$v_diskfile 	= str_replace( $DOCUMENT_ROOT, "", $v_diskfile );
		$v_diskfile		= dirname( $v_diskfile )."/".rawurlencode( $v_filename );
		$v_filesize 	= round( $v_filesize / 1024 );
		$v_title 		= string_display( $v_title );
		$v_description 	= string_display( $v_description );

		# alternate row colors
		$status_color = alternate_colors( $i );
?>
<tr valign="top" bgcolor="<?php echo $status_color ?>">
	<td>
<?php
		switch ( $g_file_upload_method ) {
			case DISK:	PRINT "<a href=\"$v_diskfile\">$v_title</a> ($v_filesize KB)";
						break;
			case DATABASE:
						PRINT "<a href=\"$g_file_download?f_id=$v_id&f_type=doc\">$v_title</a> ($v_filesize KB)";
						break;
		}
		if ( access_level_check_greater_or_equal( MANAGER ) ) {
			print_bracket_link( $g_proj_doc_edit_page."?f_id=".$v_id, "edit" );
		}
?>
		<br>
		<span class="small">(<?php echo $v_filesize ?> KB)</span>
	</td>
	<td>
		<?php echo $v_description ?>
	</td>
</tr>
<?php 		} # end for loop ?>
</table>
</div>

<?php print_page_bot1( __FILE__ ) ?>