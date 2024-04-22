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
 * Edit Core Formatting Configuration
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

auth_reauthenticate( );
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

layout_page_header( lang_get( 'plugin_format_title' ) );

layout_page_begin( 'manage_overview_page.php' );

print_manage_menu( 'manage_plugin_page.php' );

?>

<div class="col-md-12 col-xs-12">
<div class="space-10"></div>
<div class="form-container" >

<form id="formatting-config-form" action="<?php echo plugin_page( 'config_edit' )?>" method="post">
<?php echo form_security_field( 'plugin_format_config_edit' ) ?>

<div class="widget-box widget-color-blue2">
<div class="widget-header widget-header-small">
	<h4 class="widget-title lighter">
		<?php print_icon( 'fa-text-width', 'ace-icon' ); ?>
		<?php echo lang_get( 'plugin_format_title' ) . ': ' . lang_get( 'plugin_format_config' )?>
	</h4>
</div>
<div class="widget-body">
<div class="widget-main no-padding">
<div class="table-responsive">
<table class="table table-bordered table-condensed table-striped">
<tr>
	<th class="category width-40">
		<?php echo lang_get( 'plugin_format_process_text' )?>
		<br /><span class="small"><?php echo lang_get( 'plugin_format_process_text_warning_notice' )?></span>
	</th>
	<td class="center" width="20%">
		<label><input type="radio" class="ace" name="process_text" value="1" <?php echo( ON == plugin_config_get( 'process_text' ) ) ? 'checked="checked" ' : ''?>/>
			<span class="lbl padding-6"><?php echo lang_get( 'plugin_format_enabled' )?></span></label>
	</td>
	<td class="center" width="20%">
		<label><input type="radio" class="ace" name="process_text" value="0" <?php echo( OFF == plugin_config_get( 'process_text' ) ) ? 'checked="checked" ' : ''?>/>
			<span class="lbl padding-6"><?php echo lang_get( 'plugin_format_disabled' )?></span></label>
	</td>
</tr>

<tr>
	<th class="category width-40">
		<?php echo lang_get( 'plugin_format_process_urls' )?>
	</th>
	<td class="center">
		<label><input type="radio" class="ace" name="process_urls" value="1" <?php echo( ON == plugin_config_get( 'process_urls' ) ) ? 'checked="checked" ' : ''?>/>
			<span class="lbl padding-6"><?php echo lang_get( 'plugin_format_enabled' )?></span></label>
	</td>
	<td class="center">
		<label><input type="radio" class="ace" name="process_urls" value="0" <?php echo( OFF == plugin_config_get( 'process_urls' ) ) ? 'checked="checked" ' : ''?>/>
			<span class="lbl padding-6"><?php echo lang_get( 'plugin_format_disabled' )?></span></label>
	</td>
</tr>

<tr>
	<th class="category width-40">
		<?php echo lang_get( 'plugin_format_process_buglinks' )?>
		<br />
		<span class="small"><?php
			printf( lang_get( 'plugin_format_process_buglinks_info' ),
				config_get( 'bug_link_tag' ), config_get( 'bugnote_link_tag' ) );
		?></span>
	</th>
	<td class="center">
		<label><input type="radio" class="ace" name="process_buglinks" value="1" <?php echo( ON == plugin_config_get( 'process_buglinks' ) ) ? 'checked="checked" ' : ''?>/>
			<span class="lbl padding-6"><?php echo lang_get( 'plugin_format_enabled' )?></span></label>
	</td>
	<td class="center">
		<label><input type="radio" class="ace" name="process_buglinks" value="0" <?php echo( OFF == plugin_config_get( 'process_buglinks' ) ) ? 'checked="checked" ' : ''?>/>
			<span class="lbl padding-6"><?php echo lang_get( 'plugin_format_disabled' )?></span></label>
	</td>
</tr>

<tr>
	<th class="category width-40">
		<?php echo lang_get( 'plugin_format_process_markdown' )?>
		<br />
		<span class="small">
			<?php echo lang_get( 'plugin_format_process_markdown_info' )?>
		</span>
	</th>
	<td class="center">
		<label><input type="radio" class="ace" name="process_markdown" value="1" <?php echo( ON == plugin_config_get( 'process_markdown' ) ) ? 'checked="checked" ' : ''?>/>
			<span class="lbl padding-6"><?php echo lang_get( 'plugin_format_enabled' )?></span></label>
	</td>
	<td class="center">
		<label><input type="radio" class="ace" name="process_markdown" value="0" <?php echo( OFF == plugin_config_get( 'process_markdown' ) ) ? 'checked="checked" ' : ''?>/>
			<span class="lbl padding-6"><?php echo lang_get( 'plugin_format_disabled' )?></span></label>
	</td>
</tr>

<tr>
	<th class="category width-40">
		<?php echo lang_get( 'plugin_format_syntax_highlighting' )?>
		<br />
		<span class="small">
			<?php echo lang_get( 'plugin_format_syntax_highlighting_info' )?>
		</span>
	</th>
	<td class="center">
		<label><input type="radio" class="ace" name="syntax_highlighting" value="1" <?php echo( ON == plugin_config_get( 'syntax_highlighting' ) ) ? 'checked="checked" ' : ''?>/>
			<span class="lbl padding-6"><?php echo lang_get( 'plugin_format_enabled' )?></span></label>
	</td>
	<td class="center">
		<label><input type="radio" class="ace" name="syntax_highlighting" value="0" <?php echo( OFF == plugin_config_get( 'syntax_highlighting' ) ) ? 'checked="checked" ' : ''?>/>
			<span class="lbl padding-6"><?php echo lang_get( 'plugin_format_disabled' )?></span></label>
	</td>
</tr>

<tr>
	<th class="category width-40">
		<?php echo lang_get( 'plugin_format_syntax_highlighting_theme' )?>
	</th>
	<td>
		<label>
			<select name="syntax_highlighting_theme" class="input-sm">
			<?php
				$t_current_theme = plugin_config_get( 'syntax_highlighting_theme' );
				foreach( glob( __DIR__ . '/../files/syntax-highlighting/themes/*' ) as $file ): ?>
					<option value="<?php echo basename( $file ) ?>"
						<?php echo basename( $file ) === $t_current_theme ? ' selected' : ''?>>
						<?php echo str_replace( ['prism-', '.min.css'], '', basename( $file ) ) ?>
					</option>
				<?php endforeach; ?>
			</select>
		</label>
	</td>
</tr>
<tr>
	<th class="category width-40">
		<?php echo lang_get( 'plugin_format_syntax_highlighting_plugins' ) ?>
	</th>
	<td colspan="2">
		<?php
			$t_current_plugins = plugin_config_get( 'syntax_highlighting_plugins' );
			if ( !is_array( $t_current_plugins ) ) {
				$t_current_plugins = [];
			}
		?>
		<?php foreach( [
			'copy-to-clipboard',
			'show-language',
			'line-numbers',
			'show-invisibles',
			'normalize-whitespace',
			'match-braces',
			'diff-highlight',
			'inline-color',
			'previewers',
		] as $t_plugin_id ): ?>
			<label style="cursor:pointer; margin-bottom: .8rem">
				<input
					name="syntax_highlighting_plugins[]"
					type="checkbox"
					class="ace input-sm"
					value="<?php echo $t_plugin_id ?>"
					<?php echo in_array( $t_plugin_id, $t_current_plugins, true ) ? ' checked' : '' ?>
				/>
				<span class="lbl"></span>
				<span>
					<?php echo lang_get( sprintf(
						'plugin_format_syntax_highlighting_plugin_%s_name',
						str_replace('-', '_', $t_plugin_id)
					) ) ?>
                </span>
				<small style="margin-left: 2.3rem; display: block; max-width: 350px">
					<?php echo lang_get( sprintf(
						'plugin_format_syntax_highlighting_plugin_%s_desc',
						str_replace('-', '_', $t_plugin_id)
					) ) ?>
				</small>
			</label><br>
		<?php endforeach; ?>
	</td>
</tr>

</table>
</div>
</div>
<div class="widget-toolbox padding-8 clearfix">
	<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'change_configuration' )?>" />
</div>
</div>
</div>
</form>
</div>
</div>

<?php
layout_page_end();
