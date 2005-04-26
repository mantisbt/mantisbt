<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: bugnote_add_inc.php,v 1.25 2005-04-26 18:22:57 thraxisp Exp $
	# --------------------------------------------------------
?>
<?php if ( ( !bug_is_readonly( $f_bug_id ) ) &&
		( access_has_bug_level( config_get( 'add_bugnote_threshold' ), $f_bug_id ) ) ) { ?>
<?php # Bugnote Add Form BEGIN ?>
<a name="addbugnote"></a> <br />

<?php if ( ON == config_get( 'use_javascript' ) ) { ?>
<div id="bugnote_add_closed" style="display: none;">
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<a href="" onclick="ToggleDiv( 'bugnote_add', g_div_bugnote_add ); return false;"
		><img border="0" src="images/plus.png" alt="+" /></a>
		<?php echo lang_get( 'add_bugnote_title' ) ?>
	</td>
</tr>
</table>
</div>
<?php } ?>

<div id="bugnote_add_open">
<form method="post" action="bugnote_add.php">
<input type="hidden" name="bug_id" value="<?php echo $f_bug_id ?>" />
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
<?php if ( ON == config_get( 'use_javascript' ) ) { ?>
		<a href="" onclick="ToggleDiv( 'bugnote_add', g_div_bugnote_add ); return false;"
		><img border="0" src="images/minus.png" alt="-" /></a>
		<?php echo lang_get( 'add_bugnote_title' ) ?>
<?php } ?>
	</td>
</tr>
<tr class="row-2">
	<td class="category" width="25%">
		<?php echo lang_get( 'bugnote' ) ?>
	</td>
	<td width="75%">
		<textarea name="bugnote_text" cols="80" rows="10" wrap="virtual"></textarea>
	</td>
</tr>
<?php if ( access_has_bug_level( config_get( 'private_bugnote_threshold' ), $f_bug_id ) ) { ?>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'view_status' ) ?>
	</td>
	<td>
<?php
		$t_default_bugnote_view_status = config_get( 'default_bugnote_view_status' );
		if ( access_has_bug_level( config_get( 'set_view_status_threshold' ), $f_bug_id ) ) {
?>
			<input type="checkbox" name="private" <?php check_checked( $t_default_bugnote_view_status, VS_PRIVATE ); ?> />
<?php
			echo lang_get( 'private' );
		} else {
			echo get_enum_element( 'project_view_state', $t_default_bugnote_view_status );
		}
?>
	</td>
</tr>
<?php } ?>
<tr>
	<td class="center" colspan="2">
		<input type="submit" class="button" value="<?php echo lang_get( 'add_bugnote_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<?php if ( ON == config_get( 'use_javascript' ) ) { ?>
<script type="text/JavaScript">
<!--
	SetDiv( "bugnote_add", g_div_bugnote_add );
// -->
</script>
<?php } ?>

<?php # Bugnote Add Form END ?>
<?php } ?>
