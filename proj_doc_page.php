<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	check_access( config_get( 'view_proj_doc_threshold' ) );

	# Select project files
	$query = "SELECT *, UNIX_TIMESTAMP(date_added) as date_added
			FROM $g_mantis_project_file_table
			WHERE project_id='$g_project_cookie_val'";
	$result = db_query( $query );
	$num_files = db_num_rows( $result );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<br />
<div align="center">
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title">
		<?php echo $s_project_documentation_title ?>
	</td>
	<td class="right">
		<?php print_doc_menu( 'proj_doc_page.php' ) ?>
	</td>
</tr>
<?php
	for ($i=0;$i<$num_files;$i++) {
		$row = db_fetch_array( $result );
		extract( $row, EXTR_PREFIX_ALL, 'v' );
		$v_filesize 	= number_format( $v_filesize );
		$v_title 		= string_display( $v_title );
		$v_description 	= string_display( $v_description );
		$v_date_added = date( $g_complete_date_format, $v_date_added );

?>
<tr valign="top" <?php echo helper_alternate_class( $i ) ?>>
	<td>
<?php
		PRINT '<a href="file_download.php?file_id='.$v_id.'&amp;type=doc">'.$v_title.'</a> ('.$v_filesize.' bytes)';

		if ( access_level_check_greater_or_equal( MANAGER ) ) {
			print_bracket_link( 'proj_doc_edit_page.php?file_id='.$v_id, 'edit' );
		}
?>
		<br />
		<span class="small">(<?php echo $v_date_added ?>)</span>
	</td>
	<td>
		<?php echo $v_description ?>
	</td>
</tr>
<?php 		} # end for loop ?>
</table>
</div>

<?php print_page_bot1( __FILE__ ) ?>
