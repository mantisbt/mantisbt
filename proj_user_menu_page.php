<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( MANAGER );

	# these pages are invalid for the "All Project" selection
	if ( "0000000" == $g_project_cookie_val ) {
		print_header_redirect( $g_login_select_proj_page );
	}

	if ( !isset( $f_sort ) ) {
		$f_sort = "username";
	}

	# basically we toggle between ASC and DESC if the user clicks the
	# same sort order
	if ( isset( $f_dir ) ) {
		if ( "ASC" == $f_dir ) {
			$f_dir = "DESC";
		} else {
			$f_dir = "ASC";
		}
	} else {
		$f_dir = "ASC";
	}
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php
	$t_project_view_state = get_project_field( $g_project_cookie_val, "view_state" );
	if ( PUBLIC == $t_project_view_state ) {
?>
<p>
<div align="center">
<?php echo $s_public_project_msg ?>
</div>
<?php
	} else {
?>
<p>
<div align="center">
<table class="width75" cellspacing="1">
<form method="post" action="<?php echo $g_proj_user_add ?>">
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
		<select name="f_user_id[]" multiple size="5">
			<?php print_project_user_list_option_list() ?>
		</select>
	</td>
	<td class="category">
		<?php echo $s_access_level ?>
	</td>
	<td>
		<select name="f_access_level">
			<?php # No administrator choice ?>
			<?php print_project_user_option_list( REPORTER ) ?>
		</select>
	</td>
	<td>
		<input type="submit" value="<?php echo $s_add_user_button ?>">
	</td>
</tr>
</form>
</table>
</div>

<p>
<div align="center">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title" colspan="4">
		<?php echo $s_manage_accounts_title ?>
	</td>
</tr>
<tr class="row-category">
	<td>
		<?php print_manage_user_sort_link( $g_proj_user_menu_page, $s_username, "username", $f_dir ) ?>
		<?php print_sort_icon( $f_dir, $f_sort, "username" ) ?>
	</td>
	<td>
		<?php print_manage_user_sort_link( $g_proj_user_menu_page, $s_email, "email", $f_dir ) ?>
		<?php print_sort_icon( $f_dir, $f_sort, "email" ) ?>
	</td>
	<td>
		<?php print_manage_user_sort_link( $g_proj_user_menu_page, $s_access_level, "access_level", $f_dir ) ?>
		<?php print_sort_icon( $f_dir, $f_sort, "access_level" ) ?>
	</td>
	<td>
		&nbsp;
	</td>
</tr>
<?php
	$t_dev = DEVELOPER;
	$t_man = MANAGER;
	$t_adm = ADMINISTRATOR;

	# Get the user data in $f_sort order
	$query = "SELECT DISTINCT t1.id, t1.username, t1.email, t1.access_level, t2.user_id, t2.access_level
			FROM $g_mantis_user_table as t1
			LEFT JOIN $g_mantis_project_user_list_table as t2
			ON t1.id=t2.user_id
			WHERE (t1.access_level>=$t_adm) OR
				  (t2.project_id=$g_project_cookie_val)
			ORDER BY '$f_sort' $f_dir";

    $result = db_query($query);
	$user_count = db_num_rows( $result );
	for ($i=0;$i<$user_count;$i++) {
		if ( isset( $u_user_id ) ) {
			unset( $u_user_id );
		}
		# prefix user data with u_
		$row = db_fetch_array($result);
		extract( $row, EXTR_PREFIX_ALL, "u" );

		if ( !isset( $u_user_id ) ) {
			$t_user_id = $u_id;
		} else {
			$t_user_id = $u_user_id;
		}

		# alternate row colors
		$t_bgcolor = alternate_colors( $i );
?>
<tr>
	<td bgcolor="<?php echo $t_bgcolor ?>">
		<?php echo $u_username ?>
	</td>
	<td bgcolor="<?php echo $t_bgcolor ?>">
		<?php print_email_link( $u_email, $u_email ) ?>
	</td>
	<td bgcolor="<?php echo $t_bgcolor ?>">
		<?php echo get_enum_element( $s_access_levels_enum_string, $u_access_level ) ?>
	</td>
	<td class="center" bgcolor="<?php echo $t_bgcolor ?>">
		<?php
			if ( isset( $u_user_id ) ) {
				print_bracket_link( $g_proj_user_delete."?f_user_id=".$t_user_id, $s_remove_link );
			}
		?>
	</td>
</tr>
<?php
	}  # end for
?>
</table>
</div>

<?php } # end public/private else ?>

<?php print_page_bot1( __FILE__ ) ?>