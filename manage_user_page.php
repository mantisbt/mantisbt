<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

	# --------------------------------------------------------
	# $Id: manage_user_page.php,v 1.64.2.1 2007-10-13 22:33:55 giallu Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path . 'icon_api.php' );

	auth_reauthenticate();

	access_ensure_global_level( config_get( 'manage_user_threshold' ) );

	$f_sort	= gpc_get_string( 'sort', 'username' );
	$f_dir	= gpc_get_string( 'dir', 'ASC' );
	$f_hide = gpc_get_bool( 'hide' );
	$f_save = gpc_get_bool( 'save' );
	$f_prefix = strtoupper( gpc_get_string( 'prefix', config_get( 'default_manage_user_prefix' ) ) );

	$t_user_table = config_get( 'mantis_user_table' );
	$t_cookie_name = config_get( 'manage_cookie' );
	$t_lock_image = '<img src="' . config_get( 'icon_path' ) . 'protected.gif" width="8" height="15" border="0" alt="' . lang_get( 'protected' ) . '" />';

	# Clean up the form variables
	if ( ! in_array( $f_sort, db_field_names( $t_user_table ) ) ) {
        $c_sort = 'username';
    } else {	 
        $c_sort = addslashes($f_sort);
    }

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
	# set cookie values for hide, sort by, and dir
	if ( $f_save ) {
		$t_manage_string = $c_hide.':'.$c_sort.':'.$c_dir;
		gpc_set_cookie( $t_cookie_name, $t_manage_string, true );
	} else if ( !is_blank( gpc_get_cookie( $t_cookie_name, '' ) ) ) {
		$t_manage_arr = explode( ':', gpc_get_cookie( $t_cookie_name ) );
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

?>
<?php html_page_top1( lang_get( 'manage_users_link' ) ) ?>
<?php html_page_top2() ?>

<?php print_manage_menu( 'manage_user_page.php' ) ?>

<?php # New Accounts Form BEGIN ?>
<?php
	$days_old = 7;
	$query = "SELECT *
		FROM $t_user_table
		WHERE ".db_helper_compare_days(db_now(),"date_created","<= '$days_old'")."
		ORDER BY date_created DESC";
	$result = db_query( $query );
	$new_user_count = db_num_rows( $result );

	if ( $new_user_count > 0 ) {
?>
<br />
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title">
		<?php echo lang_get( 'new_accounts_title' ) ?> (<?php echo lang_get( '1_week_title' ) ?>) [<?php echo $new_user_count ?>]
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td>
<?php
for ($i=0;$i<$new_user_count;$i++) {
	$row = db_fetch_array( $result );

	if ( $i > 0 ) {
		echo ' : ';
	}

	echo '<a href="manage_user_edit_page.php?user_id=', $row['id'], '">', string_display( $row['username'] ), '</a>';
}
?>
	</td>
</tr>
</table>
<?php } # New Accounts Form END ?>

<?php # Never Logged In Form BEGIN ?>
<?php
	$query = "SELECT *
		FROM $t_user_table
		WHERE ( login_count = 0 ) AND ( date_created = last_visit )
		ORDER BY date_created DESC";
	$result = db_query( $query );
	$user_count = db_num_rows( $result );

	if ( $user_count > 0 ) {
?>
<br />
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title">
		<?php echo lang_get( 'never_logged_in_title' ) ?> [<?php echo $user_count ?>] <?php print_button( 'manage_user_prune.php', lang_get( 'prune_accounts' ) ); ?>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td>
<?php
	for ($i=0;$i<$user_count;$i++) {
		$row = db_fetch_array( $result );

		if ( $i > 0 ) {
			echo ' : ';
		}

		echo '<a href="manage_user_edit_page.php?user_id=', $row['id'], '">', string_display( $row['username'] ), '</a>';
	}
?>
	</td>
</tr>
</table>
<?php } # Never Logged In Form END ?>

<?php # Manage Form BEGIN ?>
<?php
	$t_prefix_array = array( 'ALL' );

	for ( $i = 'A'; $i != 'AA'; $i++ ) {
		$t_prefix_array[] = $i;
	}

	for ( $i = 0; $i <= 9; $i++ ) {
		$t_prefix_array[] = "$i";
	}

	$t_index_links = '<br /><center><table class="width75"><tr>';
	foreach ( $t_prefix_array as $t_prefix ) {
		if ( $t_prefix === 'ALL' ) {
			$t_caption = lang_get( 'show_all_users' );
		} else {
			$t_caption = $t_prefix;
		}

		if ( $t_prefix == $f_prefix ) {
			$t_link = "<strong>$t_caption</strong>";
		} else {
			$t_link = '<a href="manage_user_page.php?prefix=' . $t_prefix .'">' . $t_caption . '</a>';
		}
		$t_index_links .= '<td>' . $t_link . '</td>';
	}
	$t_index_links .= '</tr></table></center>';

	echo $t_index_links;

	if ( $f_prefix === 'ALL' ) {
		$t_where = '(1 = 1)';
	} else {
		$c_prefix = db_prepare_string($f_prefix);
		$t_where = "(username like '$c_prefix%')";
	}

	# Get the user data in $c_sort order
	if ( 0 == $c_hide ) {
		$query = "SELECT *
				FROM $t_user_table
				WHERE $t_where
				ORDER BY $c_sort $c_dir";
	} else {
		$query = "SELECT *
				FROM $t_user_table
				WHERE (" . db_helper_compare_days(db_now(),"last_visit","< '$days_old'") . ") AND $t_where
				ORDER BY $c_sort $c_dir";
	}

    $result = db_query($query);
	$user_count = db_num_rows( $result );
?>
<br />
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="5">
		<?php echo lang_get( 'manage_accounts_title' ) ?> [<?php echo $user_count ?>]
		<?php print_button( 'manage_user_create_page.php', lang_get( 'create_new_account_link' ) ) ?>
	</td>
	<td class="center" colspan="2">
		<form method="post" action="manage_user_page.php">
		<input type="hidden" name="sort" value="<?php echo $c_sort ?>" />
		<input type="hidden" name="dir" value="<?php echo $c_dir ?>" />
		<input type="hidden" name="save" value="1" />
		<input type="checkbox" name="hide" value="1" <?php check_checked( $c_hide, 1 ); ?> /> <?php echo lang_get( 'hide_inactive' ) ?>
		<input type="submit" class="button" value="<?php echo lang_get( 'filter_button' ) ?>" />
		</form>
	</td>
</tr>
<tr class="row-category">
	<td>
		<?php print_manage_user_sort_link(  'manage_user_page.php', lang_get( 'username' ), 'username', $c_dir, $c_sort, $c_hide ) ?>
		<?php print_sort_icon( $c_dir, $c_sort, 'username' ) ?>
	</td>
	<td>
		<?php print_manage_user_sort_link(  'manage_user_page.php', lang_get( 'realname' ), 'realname', $c_dir, $c_sort, $c_hide ) ?>
		<?php print_sort_icon( $c_dir, $c_sort, 'realname' ) ?>
	</td>
	<td>
		<?php print_manage_user_sort_link(  'manage_user_page.php', lang_get( 'email' ), 'email', $c_dir, $c_sort, $c_hide ) ?>
		<?php print_sort_icon( $c_dir, $c_sort, 'email' ) ?>
	</td>
	<td>
		<?php print_manage_user_sort_link(  'manage_user_page.php', lang_get( 'access_level' ), 'access_level', $c_dir, $c_sort, $c_hide ) ?>
		<?php print_sort_icon( $c_dir, $c_sort, 'access_level' ) ?>
	</td>
	<td>
		<?php print_manage_user_sort_link(  'manage_user_page.php', lang_get( 'enabled' ), 'enabled', $c_dir, $c_sort, $c_hide ) ?>
		<?php print_sort_icon( $c_dir, $c_sort, 'enabled' ) ?>
	</td>
	<td>
		<?php print_manage_user_sort_link(  'manage_user_page.php', $t_lock_image, 'protected', $c_dir, $c_sort, $c_hide ) ?>
		<?php print_sort_icon( $c_dir, $c_sort, 'protected' ) ?>
	</td>
	<td>
		<?php print_manage_user_sort_link(  'manage_user_page.php', lang_get( 'date_created' ), 'date_created', $c_dir, $c_sort, $c_hide ) ?>
		<?php print_sort_icon( $c_dir, $c_sort, 'date_created' ) ?>
	</td>
	<td>
		<?php print_manage_user_sort_link(  'manage_user_page.php', lang_get( 'last_visit' ), 'last_visit', $c_dir, $c_sort, $c_hide ) ?>
		<?php print_sort_icon( $c_dir, $c_sort, 'last_visit' ) ?>
	</td>
</tr>
<?php
	for ($i=0;$i<$user_count;$i++) {
		# prefix user data with u_
		$row = db_fetch_array($result);
		extract( $row, EXTR_PREFIX_ALL, 'u' );

		$u_date_created  = date( config_get( 'normal_date_format' ), db_unixtimestamp( $u_date_created ) );
		$u_last_visit    = date( config_get( 'normal_date_format' ), db_unixtimestamp( $u_last_visit ) );
?>
<tr <?php echo helper_alternate_class( $i ) ?>>
	<td>
		<a href="manage_user_edit_page.php?user_id=<?php echo $u_id ?>"><?php echo string_display( $u_username ) ?></a>
	</td>
	<td><?php echo string_display( $u_realname ) ?></td>
	<td><?php print_email_link( $u_email, $u_email ) ?></td>
	<td><?php echo get_enum_element( 'access_levels', $u_access_level ) ?></td>
	<td><?php echo trans_bool( $u_enabled ) ?></td>
	<td class="center">
          <?php
		if ( $u_protected ) {
			echo " $t_lock_image";
		} else {
			echo '&nbsp;';
		}
          ?>
        </td>
	<td><?php echo $u_date_created ?></td>
	<td><?php echo $u_last_visit ?></td>
</tr>
<?php
	}  # end for
?>
</table>
<?php # Manage Form END ?>

<?php html_page_bottom1( __FILE__ ) ?>
