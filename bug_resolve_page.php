<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_resolve_page.php,v 1.21 2002-10-27 22:53:40 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	$f_bug_id = gpc_get_int( 'f_bug_id' );

	project_access_check( $f_bug_id );
	check_access( config_get( 'handle_bug_threshold' ) );
	bug_ensure_exists( $f_bug_id );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php # Resolve Form BEGIN ?>
<br />
<div align="center">
<form method="post" action="bug_resolve.php">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<input type="hidden" name="f_bug_id" value="<?php echo $f_bug_id ?>" />
		<?php echo lang_get( 'resolve_bug_title' ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'resolution' ) ?>
	</td>
	<td>
		<select name="f_resolution">
			<?php print_enum_string_option_list( "resolution", FIXED ) ?>
		</select>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'duplicate_id' ) ?>
	</td>
	<td>
		<input type="text" name="f_duplicate_id" maxlength="7" />
	</td>
</tr>

<?php if ( ON == config_get( 'allow_close_immediately' ) ) { ?>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'close_immediately' ) ?>
	</td>
	<td>
		<input type="checkbox" name="f_close_now" />
	</td>
</tr>
<?php } ?>

<tr class="row-1">
	<td class="category" colspan="2">
		<?php echo lang_get( 'add_bugnote_title' ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="center" colspan="2">
		<textarea name="f_bugnote_text" cols="80" rows="10" wrap="virtual"></textarea>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<?php echo lang_get( 'resolve_bug_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>
<?php # Resolve Form END ?>

<?php include( config_get( 'bug_view_inc' ) ) ?>

<?php print_page_bot1( __FILE__ ) ?>
