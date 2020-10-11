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
 * Manage News
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses config_api.php
 * @uses current_user_api.php
 * @uses form_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses news_api.php
 * @uses print_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'config_api.php' );
require_api( 'current_user_api.php' );
require_api( 'form_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'news_api.php' );
require_api( 'print_api.php' );

news_ensure_enabled();

access_ensure_project_level( config_get( 'manage_news_threshold' ) );

layout_page_header( lang_get( 'edit_news_link' ) );

layout_page_begin( 'main_page.php' );
?>

<div class="col-md-12 col-xs-12">
	<div id="news-add-div" class="form-container">
	<form id="news-add-form" method="post" action="news_add.php">
		<div class="widget-box widget-color-blue2">
			<div class="widget-header widget-header-small">
				<h4 class="widget-title lighter">
					<?php print_icon( 'fa-edit', 'ace-icon' ); ?>
					<?php echo lang_get( 'add_news_title' ) ?>
				</h4>
			</div>
		<div class="widget-body">
		<div class="widget-main no-padding">
		<div class="table-responsive">
		<table class="table table-bordered table-condensed table-striped">
		<fieldset>
			<?php echo form_security_field( 'news_add' ); ?>
			<tr>
				<td class="category">
					<span class="required">*</span> <?php echo lang_get( 'headline' ) ?>
				</td>
				<td>
					<input type="text" id="news-headline" name="headline" class="input-sm" size="64" maxlength="64" required />
				</td>
			</tr>
			<tr>
				<td class="category">
					<span class="required">*</span> <?php echo lang_get( 'body' ) ?>
				</td>
				<td>
					<textarea class="form-control" id="news-body" name="body" cols="60" rows="8" required></textarea>
				</td>
			</tr>
			<tr>
				<td class="category">
					<?php echo lang_get( 'announcement' ) ?> <span class="help-text"><?php echo lang_get( 'stays_on_top' ) ?></span>
				</td>
				<td>
					<label>
						<input type="checkbox" class="ace" id="news-announcement" name="announcement" />
						<span class="lbl"></span>
					</label>
				</td>
			</tr>
			<tr>
				<td class="category">
					<?php echo lang_get( 'view_status' ) ?>
				</td>
				<td>
					<select id="news-view-status" name="view_state" class="input-sm">
						<?php print_enum_string_option_list( 'view_state' ) ?>
					</select>
				</td>
			</tr>
		</fieldset>
		</table>
		</div>
		</div>
			<div class="widget-toolbox padding-8 clearfix">
				<span class="required pull-right"> * <?php echo lang_get( 'required' ) ?></span>
				<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'post_news_button' ) ?>" />
			</div>
		</div>
		</div>
	</form>
	</div>
</div>

<?php
# Add News Form END
# Edit/Delete News Form BEGIN
if( news_get_count( helper_get_current_project(), current_user_is_administrator() ) > 0 ) { ?>
<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>
	<div id="news-edit-div" class="form-container">
	<form id="news-edit-form" method="post" action="news_edit_page.php">
		<div class="widget-box widget-color-blue2">
			<div class="widget-header widget-header-small">
				<h4 class="widget-title lighter">
					<?php print_icon( 'fa-edit', 'ace-icon' ); ?>
					<?php echo lang_get( 'edit_or_delete_news_title' ) ?>
				</h4>
			</div>
		<div class="widget-body">
		<div class="widget-main no-padding">
		<div class="table-responsive">
		<table class="table table-bordered table-condensed table-striped">
		<fieldset>
			<?php echo form_security_field( 'news_delete' ); ?>
			<tr>
				<td class="category">
					<?php echo lang_get( 'edit_post' ) ?>
				</td>
				<td>
					<label>
						<input type="radio" class="ace" id="news-edit-action" name="action" value="edit" checked="checked">
						<span class="lbl"></span>
					</label>
				</td>
			</tr>
			<tr>
				<td class="category">
					<?php echo lang_get( 'delete_post' ) ?>
				</td>
				<td>
					<label>
						<input type="radio" class="ace" id="news-delete-action" name="action" value="delete">
						<span class="lbl"></span>
					</label>
				</td>
			</tr>
			<tr>
				<td class="category">
					<?php echo lang_get( 'select_post' ) ?>
				</td>
				<td>
					<select id="news-edit-id" name="news_id" class="input-sm">
						<?php print_news_item_option_list() ?>
					</select>
				</td>
			</tr>
		</fieldset>
		</table>
		</div>
		</div>
			<div class="widget-toolbox padding-8 clearfix">
				<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'submit' ) ?>" />
			</div>
		</div>
		</div>
	</form>
	</div>
</div>
<?php
} # Edit/Delete News Form END

layout_page_end();
