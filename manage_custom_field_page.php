<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	check_access( MANAGER );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php print_manage_menu( 'manage_custom_field_page.php' ) ?>

<?php if ( access_level_check_greater_or_equal ( ADMINISTRATOR ) ) { # Create Form BEGIN ?>
<br />
<div align="center">
<form method="post" action="manage_custom_field_create.php">
<table class="width75" cellspacing="1">
	<input type="text" name="f_name" size="32" maxlength="64" />
	<input type="submit" value="<?php echo lang_get( 'add_custom_field_button' ) ?>" />
</table>
</form>
</div>
<?php } # Create Form END ?>

<?php # Custom Field Menu Form BEGIN ?>
<br />
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="5">
		<?php echo lang_get( 'custom_fields_setup' ) ?>
	</td>
</tr>
<tr class="row-category">

</tr>
<?php
	$t_custom_fields = custom_field_get_ids();

	foreach( $t_custom_fields as $t_field_id )
	{
		$t_desc = custom_field_get_definition( $t_field_id );
?>
<tr <?php echo helper_alternate_class() ?>>
	<td>
		<a href="manage_custom_field_edit_page.php?f_field_id=<?php echo $t_field_id ?>"><?php echo $t_desc['name'] ?></a>
	</td>
	<td>
		More fields, like type, etc...
	</td>
</tr>
<?php
	}
?>
</table>
<?php # Custom Field Menu Form END ?>


<?php print_page_bot1( __FILE__ ) ?>