<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details
?>
<?
	### This include file prints out the bug file upload form
	### It POSTs to bug_file_add.php3
?>
<? ### Upload File Form BEGIN ?>
<? if ( $g_allow_file_upload==1 ) { ?>
<p>
<div align="center">
<form method="post" enctype="multipart/form-data" action="<? echo $g_bug_file_add ?>">
<table width="100%" bgcolor="<? echo $g_primary_border_color ?>" <? echo $g_primary_table_tags ?>>
<tr>
	<td bgcolor="<? echo $g_white_color ?>">
	<table width="100%" bgcolor="<? echo $g_white_color ?>">
	<tr>
		<td colspan="2" bgcolor="<? echo $g_table_title_color ?>">
			<b><? echo $s_upload_file ?></b>
		</td>
	</tr>
	<tr>
		<td align="center" width="15%" bgcolor="<? echo $g_category_title_color ?>">
			<b><? echo $s_select_file ?></b>
		</td>
		<td width="85%" bgcolor="<? echo $g_primary_color_light ?>">
			<input type="hidden" name="f_id" value="<? echo $f_id ?>">
			<input type="hidden" name="max_file_size" value="5000000">
			<input name="f_file" type="file" size="70">
		</td>
	</tr>
	<tr>
		<td align="center" colspan="2">
			<input type=submit value="<? echo $s_upload_file_button ?>">
		</td>
	</tr>
	</table>
	</td>
</tr>
</table>
</form>
</div>
<? } ?>
<? ### Upload File Form END ?>