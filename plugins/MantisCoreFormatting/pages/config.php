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

auth_reauthenticate();
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

layout_page_header( lang_get( 'plugin_format_title' ) );

layout_page_begin( 'manage_overview_page.php' );

print_manage_menu( 'manage_plugin_page.php' );

# Syntax highlighting. Available plugins
$t_syntax_highlighting_available_plugins = [];
foreach( [
	'copy-to-clipboard',
	'show-language',
	'line-numbers',
	'show-invisibles',
	'normalize-whitespace',
	'match-braces',
	'diff-highlight',
	'inline-color',
	'previewers',
] as $t_plugin_id ) {
	$t_syntax_highlighting_available_plugins[] = [
		'id' => $t_plugin_id,
		'name' => lang_get( sprintf(
			'plugin_format_syntax_highlighting_plugin_%s_name',
			str_replace( '-', '_', $t_plugin_id )
		) ),
		'description' => lang_get( sprintf(
			'plugin_format_syntax_highlighting_plugin_%s_desc',
			str_replace( '-', '_', $t_plugin_id )
		) )
	];
}

# Syntax highlighting. Currently selected plugins
$t_syntax_highlighting_current_plugins = plugin_config_get( 'syntax_highlighting_plugins' );
if ( !is_array( $t_syntax_highlighting_current_plugins ) ) {
	$t_syntax_highlighting_current_plugins = [];
}

# Syntax highlighting. Available theme files
$t_syntax_highlighting_available_themes = [];
foreach( glob( __DIR__ . '/../files/syntax-highlighting/themes/*' ) as $t_file ) {
	$t_syntax_highlighting_available_themes[] = basename( $t_file );
}

# Syntax highlighting. Currently selected theme
$t_syntax_highlighting_current_theme = plugin_config_get( 'syntax_highlighting_theme' );
?>

<div class="col-md-12 col-xs-12">
<div class="space-10"></div>
<div class="form-container">

<form id="formatting-config-form" action="<?php echo plugin_page( 'config_edit' )?>" method="post">
<?php echo form_security_field( 'plugin_format_config_edit' ) ?>

<div class="widget-box widget-color-blue2">
	<div class="widget-header widget-header-small">
		<h4 class="widget-title lighter">
			<?php print_icon( 'fa-text-width', 'ace-icon' ); ?>
			<?php echo sprintf( '%s: %s',
				lang_get( 'plugin_format_title' ),
				lang_get( 'plugin_format_config' )
			) ?>
		</h4>
	</div>
<div class="widget-body">
<div class="widget-main no-padding">
<div class="table-responsive">
<table class="table table-bordered table-condensed table-striped">
<tr>
	<th class="category width-40">
		<?php echo lang_get( 'plugin_format_process_text' ) ?>
		<br>
		<span class="small">
			<?php echo lang_get( 'plugin_format_process_text_warning_notice' ) ?>
		</span>
	</th>
	<td class="center width-20">
		<label>
			<input
				type="radio"
				name="process_text"
				value="1"
				class="ace"
				<?php check_checked( plugin_config_get( 'process_text' ), ON ) ?>
			>
			<span class="lbl padding-6">
				<?php echo lang_get( 'plugin_format_enabled' ) ?>
			</span>
		</label>
	</td>
	<td class="center width-20">
		<label>
			<input
				type="radio"
				name="process_text"
				value="0"
				class="ace"
				<?php check_checked( plugin_config_get( 'process_text' ), OFF ) ?>
			>
			<span class="lbl padding-6">
				<?php echo lang_get( 'plugin_format_disabled' ) ?>
			</span>
		</label>
	</td>
</tr>

<tr>
	<th class="category">
		<?php echo lang_get( 'plugin_format_process_urls' ) ?>
	</th>
	<td class="center">
		<label>
			<input
				type="radio"
				name="process_urls"
				value="1"
				class="ace"
				<?php check_checked( plugin_config_get( 'process_urls' ), ON ) ?>
			>
			<span class="lbl padding-6">
				<?php echo lang_get( 'plugin_format_enabled' ) ?>
			</span>
		</label>
	</td>
	<td class="center">
		<label>
			<input
				type="radio"
				name="process_urls"
				value="0"
				class="ace"
				<?php check_checked( plugin_config_get( 'process_urls' ), OFF ) ?>
			>
			<span class="lbl padding-6">
				<?php echo lang_get( 'plugin_format_disabled' ) ?>
			</span>
		</label>
	</td>
</tr>

<tr>
	<th class="category">
		<?php echo lang_get( 'plugin_format_process_buglinks' ) ?>
		<br>
		<span class="small"><?php
			printf( lang_get( 'plugin_format_process_buglinks_info' ),
				config_get( 'bug_link_tag' ),
				config_get( 'bugnote_link_tag' ) );
		?>
		</span>
	</th>
	<td class="center">
		<label>
			<input
				type="radio"
				name="process_buglinks"
				value="1"
				class="ace"
				<?php check_checked( plugin_config_get( 'process_buglinks' ), ON ) ?>
			>
			<span class="lbl padding-6">
				<?php echo lang_get( 'plugin_format_enabled' ) ?>
			</span>
		</label>
	</td>
	<td class="center">
		<label>
			<input
				type="radio"
				name="process_buglinks"
				value="0"
				class="ace"
				<?php check_checked( plugin_config_get( 'process_buglinks' ), OFF ) ?>
			>
			<span class="lbl padding-6">
				<?php echo lang_get( 'plugin_format_disabled' ) ?>
			</span>
		</label>
	</td>
</tr>

<tr>
	<th class="category">
		<?php echo lang_get( 'plugin_format_process_markdown' ) ?>
		<br>
		<span class="small">
			<?php echo lang_get( 'plugin_format_process_markdown_info' ) ?>
		</span>
	</th>
	<td class="center">
		<label>
			<input
				type="radio"
				name="process_markdown"
				value="1"
				class="ace"
				<?php check_checked( plugin_config_get( 'process_markdown' ), ON ) ?>
			>
			<span class="lbl padding-6">
				<?php echo lang_get( 'plugin_format_enabled' ) ?>
			</span>
		</label>
	</td>
	<td class="center">
		<label>
			<input
				type="radio"
				name="process_markdown"
				value="0"
				class="ace"
				<?php check_checked( plugin_config_get( 'process_markdown' ), OFF ) ?>
			>
			<span class="lbl padding-6">
				<?php echo lang_get( 'plugin_format_disabled' ) ?>
			</span>
		</label>
	</td>
</tr>

<tr>
	<th class="category">
		<?php echo lang_get( 'plugin_format_syntax_highlighting' ) ?>
		<br>
		<span class="small">
			<?php echo lang_get( 'plugin_format_syntax_highlighting_info' ) ?>
		</span>
	</th>
	<td class="center">
		<label>
			<input
				type="radio"
				name="syntax_highlighting"
				value="1"
				class="ace"
				<?php check_checked( plugin_config_get( 'syntax_highlighting' ), ON ) ?>
			>
			<span class="lbl padding-6">
				<?php echo lang_get( 'plugin_format_enabled' ) ?>
			</span>
		</label>
	</td>
	<td class="center">
		<label>
			<input
				type="radio"
				name="syntax_highlighting"
				value="0"
				class="ace"
				<?php check_checked( plugin_config_get( 'syntax_highlighting' ), OFF ) ?>
			>
			<span class="lbl padding-6">
				<?php echo lang_get( 'plugin_format_disabled' )?>
			</span>
		</label>
	</td>
</tr>

<tr>
	<th class="category">
		<?php echo lang_get( 'plugin_format_syntax_highlighting_theme' )?>
	</th>
	<td>
		<label>
			<select name="syntax_highlighting_theme" class="input-sm">
			<?php foreach( $t_syntax_highlighting_available_themes as $t_theme ): ?>
				<option
					value="<?php echo $t_theme ?>"
					<?php check_selected( $t_syntax_highlighting_current_theme, $t_theme ) ?>
				>
					<?php echo str_replace( ['prism-', '.min.css'], '', $t_theme ) ?>
				</option>
			<?php endforeach; ?>
			</select>
		</label>
	</td>
</tr>
<tr>
	<th class="category">
		<?php echo lang_get( 'plugin_format_syntax_highlighting_plugins' ) ?>
	</th>
	<td colspan="2">
	<?php foreach( $t_syntax_highlighting_available_plugins as $t_plugin ): ?>
		<label class="lbl block">
			<input
				type="checkbox"
				name="syntax_highlighting_plugins[]"
				value="<?php echo $t_plugin['id'] ?>"
				class="ace input-sm"
				<?php check_checked( $t_syntax_highlighting_current_plugins, $t_plugin['id'] ) ?>
			>
			<span class="lbl">
				<?php echo $t_plugin['name'] ?><br>
				<small class="block margin-left-20">
					<?php echo $t_plugin['description'] ?>
				</small>
			</span>
		</label>
	<?php endforeach; ?>
	</td>
</tr>

</table>
</div>
</div>
<div class="widget-toolbox padding-8 clearfix">
	<input
		type="submit" class="btn btn-primary btn-white btn-round"
		value="<?php echo lang_get( 'change_configuration' ) ?>"
	>
</div>
</div>
</div>
</form>
</div>
</div>

<?php
layout_page_end();
