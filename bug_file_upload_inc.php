<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: bug_file_upload_inc.php,v 1.25 2002-10-23 04:54:44 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php
	# This include file prints out the bug file upload form
	# It POSTs to bug_file_add.php
?>
<?php
	if ( OFF == config_get( 'allow_file_upload' ) ||
		 ! ini_get( 'file_uploads' ) ) { 
		trigger_error( ERROR_UPLOAD_FAILURE, ERROR );
	}

	$t_max_file_size = (int)config_get( 'max_file_size' );
?>
<br />
<div align="center">
<form method="post" enctype="multipart/form-data" action="bug_file_add.php">
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<?php echo lang_get( 'upload_file' ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="15%">
		<?php echo lang_get( 'select_file' ) ?><br />
		<?php echo '(' . lang_get( 'max_file_size' ) . ': ' . number_format( $t_max_file_size/1000 ) . 'k)'?>
	</td>
	<td width="85%">
		<input type="hidden" name="f_bug_id" value="<?php echo $f_bug_id ?>" />
		<input type="hidden" name="max_file_size" value="<?php echo $t_max_file_size ?>" />
		<input name="f_file" type="file" size="60" />
		<input type="submit" value="<?php echo lang_get( 'upload_file_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>