<?php

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'current_user_api.php' );
require_api( 'database_api.php' );
require_api( 'html_api.php' );
require_api( 'layout_api.php' );

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'database_api.php' );
require_api( 'file_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );
require_api( 'export_api.php' );


layout_page_header( 'EXPORT PAGE' );
layout_page_begin( 'export_issues_page.php.php' );

?>
<div class="col-md-12 col-xs-12">
	<form id="export_issues_form" method="post"	action="export_issues.php">
		<?php echo form_security_field( 'export_issues' ) ?>
		<div class="widget-box widget-color-blue2">
			<div class="widget-header widget-header-small">
				<h4 class="widget-title lighter">
					<i class="ace-icon fa fa-edit"></i>
					<?php echo 'EXPORT_OPTIONS' ?>
				</h4>
			</div>

			<div class="widget-body dz-clickable">
				<div class="widget-main no-padding">
					<div class="table-responsive">

<table class="table table-bordered table-condensed">
	<tr>
		<th class="category" width="30%">
			<label for="provider"><?php echo 'FORMAT_TYPE' ?></label>
		</th>
		<td>
			<select <?php echo helper_get_tab_index() ?> id="provider" name="provider" class="input-sm">
			<?php export_print_format_option_list() ?>
			</select>
		</td>
	</tr>
	<tr>
		<th class="category">
			<label for="filename"><?php echo 'FILENAME' ?></label>
		</th>
		<td>
			<input <?php echo helper_get_tab_index() ?> type="text" id="filename" name="filename" value="<?php echo export_get_default_filename() ?>" required />
		</td>
	</tr>
</table>

					</div>
				</div>
				<div class="widget-toolbox padding-8 clearfix">
					<input <?php echo helper_get_tab_index() ?> type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo 'EXPORT' ?>" />
				</div>
			</div>
		</div>
	</form>
</div>

<?php
layout_page_end();
