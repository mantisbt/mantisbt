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

html_page_top( lang_get( 'edit_news_link' ) );
?>

<div id="news-add-div" class="form-container">
	<form id="news-add-form" method="post" action="news_add.php">
		<fieldset class="required">
			<legend><span><?php echo lang_get( 'add_news_title' ) ?></span></legend>
			<?php echo form_security_field( 'news_add' ); ?>
			<div class="field-container">
				<label for="news-headline" class="required"><span><?php echo lang_get( 'headline' ) ?></span></label>
				<span class="input"><input type="text" id="news-headline" name="headline" size="64" maxlength="64" /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="news-body" class="required"><span><?php echo lang_get( 'body' ) ?></span></label>
				<span class="textarea"><textarea id="news-body" name="body" cols="60" rows="8"></textarea></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="news-announcement"><span><?php echo lang_get( 'announcement' ) ?></span> <span class="help-text"><?php echo lang_get( 'stays_on_top' ) ?></span></label>
				<span class="checkbox"><input type="checkbox" id="news-announcement" name="announcement" /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="news-view-status"><span><?php echo lang_get( 'view_status' ) ?></span></label>
				<span class="select">
					<select id="news-view-status" name="view_state">
						<?php print_enum_string_option_list( 'view_state' ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>
			<span class="submit-button"><input type="submit" class="button" value="<?php echo lang_get( 'post_news_button' ) ?>" /></span>
		</fieldset>
	</form>
</div>
<?php
# Add News Form END
# Edit/Delete News Form BEGIN
if( news_get_count( helper_get_current_project(), current_user_is_administrator() ) > 0 ) { ?>
<div id="news-edit-div" class="form-container">
	<form id="news-edit-form" method="post" action="news_edit_page.php">
		<fieldset>
			<legend><span><?php echo lang_get( 'edit_or_delete_news_title' ) ?></span></legend>
			<?php echo form_security_field( 'news_delete' ); ?>
			<fieldset class="field-container">
					<span class="radio"><input type="radio" id="news-edit-action" name="action" value="edit" checked="checked" /></span>
					<label for="news-edit-action"><span><?php echo lang_get( 'edit_post' ) ?></span></label>
					<span class="radio"><input type="radio" id="news-delete-action" name="action" value="delete" /></span>
					<label for="news-delete-action"><span><?php echo lang_get( 'delete_post' ) ?></span></label>
			</fieldset>
			<div class="field-container">
				<label for="news-edit-id"><span><?php echo lang_get( 'select_post' ) ?></span></label>
				<span class="select">
					<select id="news-edit-id" name="news_id">
						<?php print_news_item_option_list() ?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>
			<span class="submit-button"><input type="submit" class="button" value="<?php echo lang_get( 'submit_button' ) ?>" /></span>
		</fieldset>
	</form>
</div><?php
} # Edit/Delete News Form END

html_page_bottom();
