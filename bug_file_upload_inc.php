<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_file_upload_inc.php,v 1.37 2005-04-22 22:06:07 prichards Exp $
	# --------------------------------------------------------
?>
<?php
	# This include file prints out the bug file upload form
	# It POSTs to bug_file_add.php

	$t_core_path = config_get( 'core_path' );
	require_once( $t_core_path.'file_api.php' );

	# check if we can allow the upload... bail out if we can't
	if ( ! file_allow_bug_upload( $f_bug_id ) ) {
		return false;
	}

	$t_max_file_size = (int)min( ini_get_number( 'upload_max_filesize' ), ini_get_number( 'post_max_size' ), config_get( 'max_file_size' ) );
?>
<br />

<?php if ( ON == config_get( 'use_javascript' ) ) { ?>
<div id="upload_form_closed" style="display: none;">
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<a href="" onclick="ToggleDiv( 'upload_form', g_div_upload_form ); return false;"
		><img border="0" src="images/plus.png" alt="+" /></a>
		<?php echo lang_get( 'upload_file' ) ?>
	</td>
</tr>
</table>
</div>
<?php } ?>

<div id="upload_form_open">
<form method="post" enctype="multipart/form-data" action="bug_file_add.php">
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
<?php if ( ON == config_get( 'use_javascript' ) ) { ?>
		<a href="" onclick="ToggleDiv( 'upload_form', g_div_upload_form ); return false;"
		><img border="0" src="images/minus.png" alt="-" /></a>
<?php } ?>
		<?php echo lang_get( 'upload_file' ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="15%">
		<?php echo lang_get( 'select_file' ) ?><br />
		<?php echo '<span class="small">(' . lang_get( 'max_file_size' ) . ': ' . number_format( $t_max_file_size/1000 ) . 'k)</span>'?>
	</td>
	<td width="85%">
		<input type="hidden" name="bug_id" value="<?php echo $f_bug_id ?>" />
		<input type="hidden" name="max_file_size" value="<?php echo $t_max_file_size ?>" />
		<input name="file" type="file" size="40" />
		<input type="submit" class="button" value="<?php echo lang_get( 'upload_file_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<?php if ( ON == config_get( 'use_javascript' ) ) { ?>
<script type="text/javascript">
<!--
	SetDiv( "upload_form", g_div_upload_form );
-->
</script>
<?php } ?>
