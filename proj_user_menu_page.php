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

	$f_sort	= gpc_get_string( 'sort', 'username' );
	$f_dir	= gpc_get_string( 'dir', 'DESC' );

	# These pages are invalid for the 'All Project' selection
	if ( '0000000' == $g_project_cookie_val ) {
		print_header_redirect( 'login_select_proj_page.php' );
	}

	check_varset( $f_sort, 'username' );
	check_varset( $f_dir, 'DESC' );

	# Clean up the form variables
	$c_sort = addslashes($f_sort);

	if ($f_dir == 'ASC') {
		$c_dir = 'ASC';
	} else {
		$c_dir = 'DESC';
	}
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php
	$t_project_view_state = project_get_field( $g_project_cookie_val, 'view_state' );
	if ( PUBLIC == $t_project_view_state ) {
		$t_msg = $s_public_project_msg;
	} else {
		$t_msg = $s_private_project_msg;
	}
?>
<br />
<div align="center">
<table class="width75" cellspacing="1">
<tr>
	<td class="center">
		<?php echo $t_msg ?>
	</td>
</tr>
</table>
</div>

<br />
<div align="center">
<form method="post" action="proj_user_add.php">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title" colspan="5">
		<?php echo $s_add_user_title ?>
	</td>
</tr>
<tr class="row-1" valign="top">
	<td class="category">
		<?php echo $s_username ?>
	</td>
	<td>
		<select name="user_id[]" multiple size="10">
			<?php print_project_user_list_option_list() ?>
		</select>
	</td>
	<td class="category">
		<?php echo $s_access_level ?>
	</td>
	<td>
		<select name="access_level">
			<?php # No administrator choice ?>
			<?php print_project_access_levels_option_list( REPORTER ) ?>
		</select>
	</td>
	<td>
		<input type="submit" value="<?php echo $s_add_user_button ?>" />
	</td>
</tr>
</table>
</form>
</div>

<br />
<div align="center">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title" colspan="4">
		<?php echo $s_manage_accounts_title ?>
	</td>
</tr>
<tr class="row-category">
	<td>
		<?php print_manage_user_sort_link( 'proj_user_menu_page.php', $s_username, 'username', $c_dir, $c_sort ) ?>
		<?php print_sort_icon( $c_dir, $c_sort, 'username' ) ?>
	</td>
	<td>
		<?php print_manage_user_sort_link( 'proj_user_menu_page.php', $s_email, 'email', $c_dir, $c_sort ) ?>
		<?php print_sort_icon( $c_dir, $c_sort, 'email' ) ?>
	</td>
	<td>
		<?php print_manage_user_sort_link( 'proj_user_menu_page.php', $s_access_level, 'access_level', $c_dir, $c_sort ) ?>
		<?php print_sort_icon( $c_dir, $c_sort, 'access_level' ) ?>
	</td>
	<td>&nbsp;

	</td>
</tr>
<?php
	$t_adm = ADMINISTRATOR;
	$t_pub = PUBLIC;

	$query = "SELECT DISTINCT u.id, u.username, u.email
			FROM 	$g_mantis_user_table u,
					$g_mantis_project_user_list_table l,
					$g_mantis_project_table p
			WHERE	u.access_level>=$t_adm OR
					(p.view_state=$t_pub AND
					p.id=$g_project_cookie_val) OR
					(u.id=l.user_id AND
					l.project_id=$g_project_cookie_val)
			ORDER BY u.username";

    $result = db_query($query);
	$user_count = db_num_rows( $result );
	for ($i=0;$i<$user_count;$i++) {
		# prefix user data with u_
		$row = db_fetch_array($result);
		extract( $row, EXTR_PREFIX_ALL, 'u' );

		$u_access_level = user_get_access_level( $u_id, helper_get_current_project() );

?>
<tr <?php echo helper_alternate_class( $i ) ?>>
	<td>
		<?php echo $u_username ?>
	</td>
	<td>
		<?php print_email_link( $u_email, $u_email ) ?>
	</td>
	<td>
		<?php echo get_enum_element( 'access_levels', $u_access_level ) ?>
	</td>
	<td class="center">
		<?php
			if ( project_includes_user( helper_get_current_project(), $u_id )  ) {
				print_bracket_link( 'proj_user_delete.php?user_id='.$u_id, $s_remove_link );
			}
		?>
	</td>
</tr>
<?php
	}  # end for
?>
</table>
</div>

<?php print_page_bot1( __FILE__ ) ?>
