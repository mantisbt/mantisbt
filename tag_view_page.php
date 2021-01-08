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
 * Tag View Page
 *
 * @package MantisBT
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses compress_api.php
 * @uses config_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses tag_api.php
 * @uses user_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'compress_api.php' );
require_api( 'config_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );
require_api( 'tag_api.php' );
require_api( 'user_api.php' );

access_ensure_global_level( config_get( 'tag_view_threshold' ) );
compress_enable();

$f_tag_id = gpc_get_int( 'tag_id' );
tag_ensure_exists( $f_tag_id );
$t_tag_row = tag_get( $f_tag_id );

$t_name = string_display_line( $t_tag_row['name'] );
$t_description = string_display( $t_tag_row['description'] );
$t_can_edit = access_has_global_level( config_get( 'tag_edit_threshold' ) );
$t_can_edit_own = $t_can_edit || auth_get_current_user_id() == tag_get_field( $f_tag_id, 'user_id' )
	&& access_has_global_level( config_get( 'tag_edit_own_threshold' ) );


layout_page_header( sprintf( lang_get( 'tag_details' ), $t_name ) );

layout_page_begin();
?>

<div class="col-md-12 col-xs-12">
<div class="space-10"></div>
<div class="widget-box widget-color-blue2">
<div class="widget-header widget-header-small">
<h4 class="widget-title lighter">
	<?php print_icon( 'fa-tag', 'ace-icon' ); ?>
	<?php echo sprintf( lang_get( 'tag_details' ), $t_name ) ?>
</h4>
</div>

<div class="widget-body">
<div class="widget-main no-padding">
	<div class="widget-toolbox padding-8 clearfix">
		<?php print_link_button( 'search.php?tag_string='.urlencode($t_tag_row['name']),
			sprintf( lang_get( 'tag_filter_default' ), tag_stats_attached( $f_tag_id ) ),
			'btn-sm pull-right'); ?>
	</div>
	<div class="table-responsive">
		<table class="table table-bordered table-condensed table-striped">

	<tr>
		<td class="category">
			<?php echo lang_get( 'tag_id' ) ?>
		</td>
		<td><?php echo $t_tag_row['id'] ?></td>
	</tr>
	<tr>
		<td class="category">
			<?php echo lang_get( 'tag_name' ) ?>
		</td>
		<td><?php echo $t_name ?></td>
	</tr>
	<tr>
		<td class="category">
			<?php echo lang_get( 'tag_creator' ) ?>
		</td>
		<td><?php echo string_display_line( user_get_name($t_tag_row['user_id']) ) ?></td>
	</tr>
	<tr>
		<td class="category">
			<?php echo lang_get( 'tag_created' ) ?>
		</td>
		<td><?php echo date( config_get( 'normal_date_format' ), $t_tag_row['date_created'] ) ?></td>
	</tr>
	<tr>
		<td class="category">
			<?php echo lang_get( 'tag_updated' ) ?>
		</td>
		<td><?php echo date( config_get( 'normal_date_format' ), $t_tag_row['date_updated'] ) ?></td>
	</tr>
	<tr>
		<td class="category">
			<?php echo lang_get( 'tag_description' ) ?>
		</td>
		<td><?php echo $t_description ?></td>
	</tr>

<?php
	# Related tags

	$t_tags_related = tag_stats_related( $f_tag_id );
	if( count( $t_tags_related ) ) {
?>
	<tr>
		<td class="category">
			<?php echo lang_get( 'tag_related' ) ?>
		</td>
			<td>
<?php
		foreach( $t_tags_related as $t_tag ) {
			$t_name = string_display_line( $t_tag['name'] );
			$t_description = string_display_line( $t_tag['description'] );
			$t_count = $t_tag['count'];
			$t_link = string_html_specialchars( 'search.php?tag_string='.urlencode( '+' . $t_tag_row['name'] . config_get( 'tag_separator' ) . '+' . $t_name ) );
			$t_label = sprintf( lang_get( 'tag_related_issues' ), $t_tag['count'] ); ?>
			<div class="col-md-3 col-xs-6 no-padding"><a href="tag_view_page.php?tag_id=<?php echo $t_tag['id']; ?>" title="<?php echo $t_description; ?>"><?php echo $t_name; ?></a></div>
			<div class="col-md-9 col-xs-6 no-padding"><a href="<?php echo $t_link; ?>" class="btn btn-xs btn-primary btn-white btn-round"><?php echo $t_label; ?></a></div>
			<div class="clearfix"></div>
			<div class="space-4"></div>
<?php
		}
?>
			</td>
	</tr>
<?php
	} ?>
</table >
</div >

<?php
	if( $t_can_edit_own || $t_can_edit ) {
?>
	<div class="widget-toolbox padding-8 clearfix">
<?php
		if( $t_can_edit_own ) {
?>
		<form class="form-inline pull-left" action="tag_update_page.php" method="post">
			<fieldset>
				<?php # CSRF protection not required here - form does not result in modifications ?>
				<input type="hidden" name="tag_id" value="<?php echo $f_tag_id ?>" />
				<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'tag_update_button' ) ?>" />
			</fieldset>
		</form><?php
		}

		if( $t_can_edit ) { ?>
		<form class="form-inline pull-left" action="tag_delete.php" method="post">
			<fieldset>
				<?php echo form_security_field( 'tag_delete' ) ?>
				<input type="hidden" name="tag_id" value="<?php echo $f_tag_id ?>" />
				<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'tag_delete_button' ) ?>" />
			</fieldset>
		</form><?php
		} ?>
	</div><?php
	} ?>
	</div>
	</div>
	</div>
</div>

<?php
layout_page_end();
