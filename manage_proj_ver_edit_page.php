<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: manage_proj_ver_edit_page.php,v 1.28 2004-07-27 14:38:34 vboctor Exp $
	# --------------------------------------------------------
?>
<?php require_once( 'core.php' ) ?>
<?php
	$f_version_id = gpc_get_int( 'version_id' );

	$t_version = version_get( $f_version_id );

	access_ensure_project_level( config_get( 'manage_project_threshold' ), $t_version->project_id );
?>
<?php html_page_top1() ?>
<?php html_page_top2() ?>

<?php print_manage_menu( 'manage_proj_ver_edit_page.php' ) ?>

<br />
<div align="center">
<form method="post" action="manage_proj_ver_update.php">
<input type="hidden" name="version_id" value="<?php echo string_attribute( $t_version->id ) ?>" />
<table class="width50" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<?php echo lang_get( 'edit_project_version_title' ) ?>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'version' ) ?>
	</td>
	<td>
		<input type="text" name="new_version" size="32" maxlength="64" value="<?php echo string_attribute( $t_version->version ) ?>" />
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'date_order' ) ?>
	</td>
	<td>
		<input type="text" name="date_order" size="32" value="<?php echo string_attribute( $t_version->date_order ) ?>" />
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'description' ) ?>
	</td>
	<td>
		<textarea name="description" cols="60" rows="5" wrap="virtual"><?php echo string_attribute( $t_version->description ) ?></textarea>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'released' ) ?>
	</td>
	<td>
		<input type="checkbox" name="released" <?php check_checked( $t_version->released, VERSION_RELEASED ); ?> />
	</td>
</tr>
<tr>
	<td>
		&nbsp;
	</td>
	<td>
		<input type="submit" class="button" value="<?php echo lang_get( 'update_version_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<br />

<div class="border-center">
	<form method="post" action="manage_proj_ver_delete.php">
	<input type="hidden" name="version_id" value="<?php echo string_attribute( $t_version->id ) ?>" />
	<input type="submit" class="button" value="<?php echo lang_get( 'delete_version_button' ) ?>" />
	</form>
</div>

<?php html_page_bottom1( __FILE__ ) ?>