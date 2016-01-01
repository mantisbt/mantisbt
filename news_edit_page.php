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
 * News Edit Page
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
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses news_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses string_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'config_api.php' );
require_api( 'current_user_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'news_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'string_api.php' );

news_ensure_enabled();

$f_news_id = gpc_get_int( 'news_id' );
$f_action = gpc_get_string( 'action', '' );

# If deleting item redirect to delete script
if( 'delete' == $f_action ) {
	form_security_validate( 'news_delete' );

	$t_row = news_get_row( $f_news_id );

	# This check is to allow deleting of news items that were left orphan due to bug #3723
	if( project_exists( $t_row['project_id'] ) ) {
		access_ensure_project_level( config_get( 'manage_news_threshold' ), $t_row['project_id'] );
	}

	helper_ensure_confirmed( lang_get( 'delete_news_sure_msg' ), lang_get( 'delete_news_item_button' ) );

	news_delete( $f_news_id );

	form_security_purge( 'news_delete' );

	print_header_redirect( 'news_menu_page.php', true );
}

# Retrieve news item data and prefix with v_
$t_row = news_get_row( $f_news_id );
if( $t_row ) {
	extract( $t_row, EXTR_PREFIX_ALL, 'v' );
}

access_ensure_project_level( config_get( 'manage_news_threshold' ), $v_project_id );

$v_headline = string_attribute( $v_headline );
$v_body 	= string_textarea( $v_body );

html_page_top( lang_get( 'edit_news_title' ) );

# Edit News Form BEGIN
?>

<div id="news-update-div" class="form-container">
	<form id="news-update-form" method="post" action="news_update.php">
		<fieldset class="required">
			<legend><span><?php echo lang_get( 'headline' ) ?></span></legend>
			<div class="section-link"><?php print_bracket_link( 'news_menu_page.php', lang_get( 'go_back' ) ) ?></div>
			<?php echo form_security_field( 'news_update' ); ?>
			<input type="hidden" name="news_id" value="<?php echo $v_id ?>" />
			<div class="field-container">
				<label for="news-update-headline" class="required"><span><?php echo lang_get( 'headline' ) ?></span></label>
				<span class="input"><input type="text" id="news-update-headline" name="headline" size="64" maxlength="64" value="<?php echo $v_headline ?>" /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="news-update-body" class="required"><span><?php echo lang_get( 'body' ) ?></span></label>
				<span class="textarea"><textarea id="news-update-body" name="body" cols="60" rows="10"><?php echo $v_body ?></textarea></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for=""><span><?php echo lang_get( 'post_to' ) ?></span></label>
				<span class="select">
					<select name="project_id"><?php
						$t_sitewide = false;
						if( current_user_is_administrator() ) {
							$t_sitewide = true;
						}
						print_project_option_list( $v_project_id, $t_sitewide ); ?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="news-update-announcement"><span><?php echo lang_get( 'announcement' ) ?></span> <span class="help-text"><?php echo lang_get( 'stays_on_top' ) ?></span></label>
				<span class="checkbox"><input type="checkbox" id="news-update-announcement" name="announcement" <?php check_checked( (int)$v_announcement, 1 ); ?> /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for=""><span><?php echo lang_get( 'view_status' ) ?></span></label>
				<span class="select">
					<select name="view_state">
						<?php print_enum_string_option_list( 'view_state', $v_view_state ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>
			<span class="submit-button"><input type="submit" class="button" value="<?php echo lang_get( 'update_news_button' ) ?>" /></span>
		</fieldset>
	</form>
</div><?php
# Edit News Form END

html_page_bottom();
