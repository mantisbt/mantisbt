<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * User Page
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses icon_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'database_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'icon_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );
require_api( 'utility_api.php' );

auth_reauthenticate();

access_ensure_global_level( config_get( 'manage_user_threshold' ) );

$t_cookie_name = config_get( 'manage_users_cookie' );
$t_lock_image = '<i class="fa fa-lock fa-lg" alt="' . lang_get( 'protected' ) . '" />';

$f_save          = gpc_get_bool( 'save' );
$f_filter        = utf8_strtoupper( gpc_get_string( 'filter', config_get( 'default_manage_user_prefix' ) ) );
$f_page_number   = gpc_get_int( 'page_number', 1 );

if( !$f_save && !is_blank( gpc_get_cookie( $t_cookie_name, '' ) ) ) {
	$t_manage_arr = explode( ':', gpc_get_cookie( $t_cookie_name ) );

	# Hide Inactive
	$f_hide_inactive = (bool)$t_manage_arr[0];

	# Sort field
	if ( isset( $t_manage_arr[1] ) ) {
		$f_sort = $t_manage_arr[1];
	} else {
		$f_sort = 'username';
	}

	# Sort order
	if ( isset( $t_manage_arr[2] ) ) {
		$f_dir = $t_manage_arr[2];
	} else {
		$f_dir = 'DESC';
	}

	# Show Disabled
	if ( isset( $t_manage_arr[3] ) ) {
		$f_show_disabled = $t_manage_arr[3];
	}
} else {
	$f_sort          = gpc_get_string( 'sort', 'username' );
	$f_dir           = gpc_get_string( 'dir', 'ASC' );
	$f_hide_inactive = gpc_get_bool( 'hideinactive' );
	$f_show_disabled = gpc_get_bool( 'showdisabled' );
}

# Clean up the form variables
if( !db_field_exists( $f_sort, db_get_table( 'user' ) ) ) {
	$c_sort = 'username';
} else {
	$c_sort = addslashes( $f_sort );
}

$c_dir = ( $f_dir == 'ASC' ) ? 'ASC' : 'DESC';

# OFF = show inactive users, anything else = hide them
$c_hide_inactive = ( $f_hide_inactive == OFF ) ? OFF : ON;
$t_hide_inactive_filter = '&amp;hideinactive=' . $c_hide_inactive;

# OFF = hide disabled users, anything else = show them
$c_show_disabled = ( $f_show_disabled == OFF ) ? OFF : ON;
$t_show_disabled_filter = '&amp;showdisabled=' . $c_show_disabled;

# set cookie values for hide inactive, sort by, dir and show disabled
if( $f_save ) {
	$t_manage_string = $c_hide_inactive.':'.$c_sort.':'.$c_dir.':'.$c_show_disabled;
	gpc_set_cookie( $t_cookie_name, $t_manage_string, true );
}

layout_page_header( lang_get( 'manage_users_link' ) );

layout_page_begin( 'manage_overview_page.php' );

print_manage_menu( 'manage_user_page.php' );

# New Accounts Form BEGIN

$t_days_old = 7 * SECONDS_PER_DAY;
$t_query = 'SELECT COUNT(*) AS new_user_count FROM {user}
	WHERE ' . db_helper_compare_time( db_param(), '<=', 'date_created', $t_days_old );
$t_result = db_query( $t_query, array( db_now() ) );
$t_row = db_fetch_array( $t_result );
$t_new_user_count = $t_row['new_user_count'];

# Never Logged In Form BEGIN

$t_query = 'SELECT COUNT(*) AS unused_user_count FROM {user}
	WHERE ( login_count = 0 ) AND ( date_created = last_visit )';
$t_result = db_query( $t_query );
$t_row = db_fetch_array( $t_result );
$t_unused_user_count = $t_row['unused_user_count'];

# Manage Form BEGIN

$t_prefix_array = array();

$t_prefix_array['ALL'] = lang_get( 'show_all_users' );

for( $i = 'A'; $i != 'AA'; $i++ ) {
	$t_prefix_array[$i] = $i;
}

for( $i = 0; $i <= 9; $i++ ) {
	$t_prefix_array[(string)$i] = (string)$i;
}
$t_prefix_array['UNUSED'] = lang_get( 'users_unused' );
$t_prefix_array['NEW'] = lang_get( 'users_new' );

echo '<div class="col-md-12 col-xs-12">';
echo '<div class = "space-10"></div>';
echo '<div class="center" >';
echo '  <div class="btn-toolbar inline" >';
echo '    <div class="btn-group" >';
foreach ( $t_prefix_array as $t_prefix => $t_caption ) {
	if( $t_prefix === 'UNUSED' ) {
		$t_title = ' title="[' . $t_unused_user_count . '] (' . lang_get( 'never_logged_in_title' ) . ')"';
	} else if( $t_prefix === 'NEW' ) {
		$t_title = ' title="[' . $t_new_user_count . '] (' . lang_get( '1_week_title' ) . ')"';
	} else {
		$t_title = '';
	}
	$t_active = $t_prefix === $f_filter ? 'active' : '';
		print_manage_user_sort_link( 'manage_user_page.php',
			$t_caption,
			$c_sort,
			$c_dir, null, $c_hide_inactive, $t_prefix, $c_show_disabled,
			'btn btn-xs btn-white btn-primary ' . $t_active );
}
echo '</div>';
echo '</div>';
echo '</div>';
echo '<div class="space-10"></div >';

$t_where_params = array();
if( $f_filter === 'ALL' ) {
	$t_where = '(1 = 1)';
} else if( $f_filter === 'UNUSED' ) {
	$t_where = '(login_count = 0) AND ( date_created = last_visit )';
} else if( $f_filter === 'NEW' ) {
	$t_where = db_helper_compare_time( db_param(), '<=', 'date_created', $t_days_old );
	$t_where_params[] = db_now();
} else {
	$t_where_params[] = $f_filter . '%';
	$t_where = db_helper_like( 'UPPER(username)' );
}

$p_per_page = 50;

$t_offset = ( ( $f_page_number - 1 ) * $p_per_page );

$t_total_user_count = 0;

# Get the user data in $c_sort order
$t_result = '';

if( ON != $c_show_disabled ) {
	$t_where .= ' AND enabled = ' . db_param();
	$t_where_params[] = true;
}

if( OFF != $c_hide_inactive ) {
	$t_where .= ' AND ' . db_helper_compare_time( db_param(), '<', 'last_visit', $t_days_old );
	$t_where_params[] = db_now();
}

$t_query = 'SELECT count(*) as user_count FROM {user} WHERE ' . $t_where;
$t_result = db_query( $t_query, $t_where_params );
$t_row = db_fetch_array( $t_result );
$t_total_user_count = $t_row['user_count'];

$t_page_count = ceil( $t_total_user_count / $p_per_page );
if( $t_page_count < 1 ) {
	$t_page_count = 1;
}

# Make sure $p_page_number isn't past the last page.
if( $f_page_number > $t_page_count ) {
	$f_page_number = $t_page_count;
}

# Make sure $p_page_number isn't before the first page
if( $f_page_number < 1 ) {
	$f_page_number = 1;
}


$t_query = 'SELECT * FROM {user} WHERE ' . $t_where . ' ORDER BY ' . $c_sort . ' ' . $c_dir;
$t_result = db_query( $t_query, $t_where_params, $p_per_page, $t_offset );

$t_users = array();
while( $t_row = db_fetch_array( $t_result ) ) {
	$t_users[] = $t_row;
}

$t_user_count = count( $t_users );
?>
<div class="widget-box widget-color-blue2">
<div class="widget-header widget-header-small">
<h4 class="widget-title lighter">
	<i class="ace-icon fa fa-users"></i>
	<?php echo lang_get('manage_accounts_title') ?>
	<span class="badge"><?php echo $t_total_user_count ?></span>
</h4>
</div>

<div class="widget-body">
<div class="widget-toolbox padding-8 clearfix">
	<div id="manage-user-div" class="form-container">
		<div class="pull-left">
			<?php print_form_button( 'manage_user_create_page.php', lang_get( 'create_new_account_link' ), null, null, 'btn btn-primary btn-white btn-round' ) ?>
		</div>
		<?php if( $f_filter === 'UNUSED' ) { ?>
		<div class="pull-left">
			<?php print_form_button('manage_user_prune.php', lang_get('prune_accounts'), null, null, 'btn btn-primary btn-white btn-round') ?>
		</div>
		<?php } ?>
	<div class="pull-right">
	<form id="manage-user-filter" method="post" action="manage_user_page.php" class="form-inline">
		<fieldset>
			<?php # CSRF protection not required here - form does not result in modifications ?>
			<input type="hidden" name="sort" value="<?php echo $c_sort ?>" />
			<input type="hidden" name="dir" value="<?php echo $c_dir ?>" />
			<input type="hidden" name="save" value="1" />
			<input type="hidden" name="filter" value="<?php echo $f_filter ?>" />
			<label class="inline">
			<input type="checkbox" class="ace" name="hideinactive" value="<?php echo ON ?>" <?php check_checked( (int)$c_hide_inactive, ON ); ?> />
			<span class="lbl"> <?php echo lang_get( 'hide_inactive' ) ?></span>
			</label>
			<label class="inline">
			<input type="checkbox" class="ace" name="showdisabled" value="<?php echo ON ?>" <?php check_checked( (int)$c_show_disabled, ON ); ?> />
			<span class="lbl"> <?php echo lang_get( 'show_disabled' ) ?></span>
			</label>
			<input type="submit" class="btn btn-primary btn-sm btn-white btn-round" value="<?php echo lang_get( 'filter_button' ) ?>" />
		</fieldset>
	</form>
		</div>
	</div>
</div>

<div class="widget-main no-padding">
	<div class="table-responsive">
		<table class="table table-striped table-bordered table-condensed table-hover">
		<thead>
			<tr>
<?php
	# Print column headers with sort links
	$t_columns = array(
		'username', 'realname', 'email', 'access_level',
		'enabled', 'protected', 'date_created', 'last_visit'
	);

	foreach( $t_columns as $t_col ) {
		echo "\t<th>";
		print_manage_user_sort_link( 'manage_user_page.php',
			lang_get( $t_col ),
			$t_col,
			$c_dir, $c_sort, $c_hide_inactive, $f_filter, $c_show_disabled );
		print_sort_icon( $c_dir, $c_sort, $t_col );
		echo "</th>\n";
	}
?>
			</tr>
		</thead>

		<tbody>
<?php
	$t_date_format = config_get( 'normal_date_format' );
	$t_access_level = array();
	for( $i=0; $i<$t_user_count; $i++ ) {
		# prefix user data with u_
		$t_user = $t_users[$i];
		extract( $t_user, EXTR_PREFIX_ALL, 'u' );

		$u_date_created  = date( $t_date_format, $u_date_created );
		$u_last_visit    = date( $t_date_format, $u_last_visit );

		if( !isset( $t_access_level[$u_access_level] ) ) {
			$t_access_level[$u_access_level] = get_enum_element( 'access_levels', $u_access_level );
		} ?>
			<tr>
				<td><?php
					if( access_has_global_level( $u_access_level ) ) { ?>
						<a href="manage_user_edit_page.php?user_id=<?php echo $u_id ?>"><?php echo string_display_line( $u_username ) ?></a><?php
					} else {
						echo string_display_line( $u_username );
					} ?>
				</td>
				<td><?php echo string_display_line( $u_realname ) ?></td>
				<td><?php print_email_link( $u_email, $u_email ) ?></td>
				<td><?php echo $t_access_level[$u_access_level] ?></td>
				<td class="center"><?php echo trans_bool( $u_enabled ) ?></td>
				<td class="center"><?php
					if( $u_protected ) {
						echo ' ' . $t_lock_image;
					} else {
						echo '&#160;';
					} ?>
				</td>
				<td><?php echo $u_date_created ?></td>
				<td><?php echo $u_last_visit ?></td>
			</tr>
<?php
	}  # end for
?>
		</tbody>
	</table>
</div>
</div>

<div class="widget-toolbox padding-8 clearfix">
	<div id="manage-user-edit-div" class="form-inline pull-left">
		<form id="manage-user-edit-form" method="get" action="manage_user_edit_page.php" class="form-inline"
			<?php # CSRF protection not required here - form does not result in modifications ?>>
			<label class="inline" for="username"><?php echo lang_get( 'search' ) ?></label>
			<input id="username" type="text" name="username" class="input-sm" value="" />
			<input type="submit" class="btn btn-primary btn-sm btn-white btn-round" value="<?php echo lang_get( 'manage_user' ) ?>" />
		</form>
	</div>
	<div class="btn-toolbar pull-right">
		<?php
		# @todo hack - pass in the hide inactive filter via cheating the actual filter value
		print_page_links( 'manage_user_page.php', 1, $t_page_count, (int)$f_page_number, $f_filter . $t_hide_inactive_filter . $t_show_disabled_filter . "&amp;sort=$c_sort&amp;dir=$c_dir");
		?>
	</div>
</div>
</div>
</div>
</div>
<?php
layout_page_end();
