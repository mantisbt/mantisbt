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
<? if ( ( $g_allow_file_upload==1 )&&
		( $v_status < RESOLVED ) ) { ?>
<p>
<div align="center">
<form method="post" enctype="multipart/form-data" action="<? echo $g_bug_file_add ?>">
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<? echo $s_upload_file ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="15%">
		<? echo $s_select_file ?>
	</td>
	<td width="85%">
		<input type="hidden" name="f_id" value="<? echo $f_id ?>">
		<input type="hidden" name="max_file_size" value="5000000">
		<input name="f_file" type="file" size="60">
		<input type="submit" value="<? echo $s_upload_file_button ?>">
	</td>
</tr>
</table>
</form>
</div>
<? } ?>
<? ### Upload File Form END ?>