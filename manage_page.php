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
	check_access( ADMINISTRATOR );

	$f_sort	= gpc_get_string( 'f_sort', 'username' );
	$f_dir	= gpc_get_string( 'f_dir', 'DESC' );
	$f_hide = gpc_get_bool( 'f_hide', false );
	$f_save = gpc_get_bool( 'f_save', false );

	# set cookie values for hide, sort by, and dir
	if ( isset( $f_save ) ) {
		$t_manage_string = $f_hide.':'.$f_sort.':'.$f_dir;
		setcookie( $g_manage_cookie, $t_manage_string, time()+$g_cookie_time_length, $g_cookie_path );
	} else if ( !empty( $g_manage_cookie_val ) ) {
		$t_manage_arr = explode( ':', $g_manage_cookie_val );
		$f_hide = $t_manage_arr[0];

		if ( isset( $t_manage_arr[1] ) ) {
			$f_sort = $t_manage_arr[1];
		} else {
			$f_sort = 'username';
		}

		if ( isset( $t_manage_arr[2] ) ) {
			$f_dir  = $t_manage_arr[2];
		} else {
			$f_dir = 'DESC';
		}
	}

	# Clean up the form variables
	$c_sort = addslashes($f_sort);

	if ($f_dir == 'ASC') {
		$c_dir = 'ASC';
	} else {
		$c_dir = 'DESC';
	}

	if ($f_hide == 0) { # a 0 will turn it off
		$c_hide = 0;
	} else {            # anything else (including 'on') will turn it on
		$c_hide = 1;
	}
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php print_manage_menu( 'manage_page.php' ) ?>

<?php # New Accounts Form BEGIN ?>
<?php
	$days_old = 7;
	$query = "SELECT *
		FROM $g_mantis_user_table
		WHERE TO_DAYS(NOW()) - TO_DAYS(date_created) <= '$days_old'
		ORDER BY date_created DESC";
	$result = db_query( $query );
	$new_user_count = db_num_rows( $result );
?>
<p>
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title">
		<?php echo $s_new_accounts_title ?> (<?php echo $s_1_week_title ?>) [<?php echo $new_user_count ?>]
	</td>
</tr>
<tr class="row-2">
	<td>
<?php
for ($i=0;$i<$new_user_count;$i++) {
	$row = db_fetch_array( $result );
	$t_username = $row['username'];

	echo $t_username.' : ';
}
?>
	</td>
</tr>
</table>
<?php # New Accounts Form END ?>

<?php # Never Logged In Form BEGIN ?>
<?php
	$query = "SELECT *
		FROM $g_mantis_user_table
		WHERE login_count=0
		ORDER BY date_created";
	$result = db_query( $query );
	$user_count = db_num_rows( $result );
?>
<p>
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title">
		<?php echo $s_never_logged_in_title ?> [<?php echo $user_count ?>] <?php print_bracket_link( 'manage_prune.php', $s_prune_accounts ) ?>
	</td>
</tr>
<tr class="row-2">
	<td>
<?php
	for ($i=0;$i<$user_count;$i++) {
		$row = db_fetch_array( $result );
		$t_username = $row['username'];

		echo $t_username.' : ';
	}
?>
	</td>
</tr>
</table>
<?php # Never Logged In Form END ?>

<?php # Manage Form BEGIN ?>
<?php
	# Get the user data in $c_sort order
	if ( 0 == $c_hide ) {
		$query = "SELECT *,  UNIX_TIMESTAMP(date_created) as date_created,
				UNIX_TIMESTAMP(last_visit) as last_visit
				FROM $g_mantis_user_table
				ORDER BY '$c_sort' $c_dir";
	} else {
		$query = "SELECT *,  UNIX_TIMESTAMP(date_created) as date_created,
				UNIX_TIMESTAMP(last_visit) as last_visit
				FROM $g_mantis_user_table
				WHERE (TO_DAYS(NOW()) - TO_DAYS(last_visit) < '$days_old')
				ORDER BY '$c_sort' $c_dir";
	}

    $result = db_query($query);
	$user_count = db_num_rows( $result );
?>
<p>
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="5">
		<?php echo $s_manage_accounts_title ?> [<?php echo $user_count ?>]
	</td>
	<td class="center" colspan="2">
		<form method="post" action="manage_page.php">
		<input type="hidden" name="f_sort" value="<?php echo $c_sort ?>">
		<input type="hidden" name="f_dir" value="<?php echo $c_dir ?>">
		<input type="hidden" name="f_save" value="1">
		<input type="checkbox" name="f_hide" value="1" <?php check_checked( $c_hide, 1 ); ?>> <?php echo $s_hide_inactive ?>
		<input type="submit" value="<?php echo $s_filter_button ?>">
		</form>
	</td>
</tr>
<tr class="row-category">
	<td>
		<?php print_manage_user_sort_link(  'manage_page.php', $s_username, 'username', $c_dir, $c_sort, $c_hide ) ?>
		<?php print_sort_icon( $c_dir, $c_sort, 'username' ) ?>
	</td>
	<td>
		<?php print_manage_user_sort_link(  'manage_page.php', $s_email, 'email', $c_dir, $c_sort, $c_hide ) ?>
		<?php print_sort_icon( $c_dir, $c_sort, 'email' ) ?>
	</td>
	<td>
		<?php print_manage_user_sort_link(  'manage_page.php', $s_access_level, 'access_level', $c_dir, $c_sort, $c_hide ) ?>
		<?php print_sort_icon( $c_dir, $c_sort, 'access_level' ) ?>
	</td>
	<td>
		<?php print_manage_user_sort_link(  'manage_page.php', $s_enabled, 'enabled', $c_dir, $c_sort, $c_hide ) ?>
		<?php print_sort_icon( $c_dir, $c_sort, 'enabled' ) ?>
	</td>
	<td>
		<?php print_manage_user_sort_link(  'manage_page.php', $s_p, 'protected', $c_dir, $c_sort, $c_hide ) ?>
		<?php print_sort_icon( $c_dir, $c_sort, 'protected' ) ?>
	</td>
	<td>
		<?php print_manage_user_sort_link(  'manage_page.php', $s_date_created, 'date_created', $c_dir, $c_sort, $c_hide ) ?>
		<?php print_sort_icon( $c_dir, $c_sort, 'date_created' ) ?>
	</td>
	<td>
		<?php print_manage_user_sort_link(  'manage_page.php', $s_last_visit, 'last_visit', $c_dir, $c_sort, $c_hide ) ?>
		<?php print_sort_icon( $c_dir, $c_sort, 'last_visit' ) ?>
	</td>
</tr>
<?php
	for ($i=0;$i<$user_count;$i++) {
		# prefix user data with u_
		$row = db_fetch_array($result);
		extract( $row, EXTR_PREFIX_ALL, 'u' );

		$u_date_created  = date( $g_normal_date_format, $u_date_created );
		$u_last_visit    = date( $g_normal_date_format, $u_last_visit );

		# alternate row colors
		$t_bgcolor = alternate_colors( $i );
?>
<tr>
	<td bgcolor="<?php echo $t_bgcolor ?>">
		<a href="manage_user_page.php?f_id=<?php echo $u_id ?>"><?php echo $u_username ?></a>
	</td>
	<td bgcolor="<?php echo $t_bgcolor ?>">
		<?php print_email_link( $u_email, $u_email ) ?>
	</td>
	<td align="center" bgcolor="<?php echo $t_bgcolor ?>">
		<?php echo get_enum_element( 'access_levels', $u_access_level ) ?>
	</td>
	<td align="center" bgcolor="<?php echo $t_bgcolor ?>">
		<?php echo trans_bool( $u_enabled ) ?>
	</td>
	<td align="center" bgcolor="<?php echo $t_bgcolor ?>">
		<?php echo trans_bool( $u_protected ) ?>
	</td>
	<td align="center" bgcolor="<?php echo $t_bgcolor ?>">
		<?php echo $u_date_created ?>
	</td>
	<td align="center" bgcolor="<?php echo $t_bgcolor ?>">
		<?php echo $u_last_visit ?>
	</td>
</tr>
<?php
	}  # end for
?>
</table>
<?php # Manage Form END ?>

<?php print_page_bot1( __FILE__ ) ?>
