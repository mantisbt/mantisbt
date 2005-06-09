<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: login_select_proj_page.php,v 1.37 2005-06-09 13:05:38 vboctor Exp $
	# --------------------------------------------------------

	# Allows the user to select a project that is visible to him

	require_once( 'core.php' );

	auth_ensure_user_authenticated();

	$f_ref = gpc_get_string( 'ref', '' );

	html_page_top1( lang_get( 'select_project_button' ) );
	html_page_top2();
?>

<!-- Project Select Form BEGIN -->
<br />
<div align="center">
<form method="post" action="set_project.php">
<table class="width50" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<input type="hidden" name="ref" value="<?php echo htmlspecialchars( $f_ref ) ?>" />
		<?php echo lang_get( 'select_project_button' ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="40%">
		<?php echo lang_get( 'choose_project' ) ?>
	</td>
	<td width="60%">
		<select name="project_id">
		<?php print_project_option_list( ALL_PROJECTS, false, null, true ) ?>
		</select>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'make_default' ) ?>
	</td>
	<td>
		<input type="checkbox" name="make_default" />
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" class="button" value="<?php echo lang_get( 'select_project_button') ?>" />
	</td>
</tr>
</table>
</form>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
