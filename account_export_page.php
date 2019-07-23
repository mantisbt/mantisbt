<?php

use Mantis\Export\TableWriterFactory;

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'current_user_api.php' );
require_api( 'database_api.php' );
require_api( 'html_api.php' );

auth_ensure_user_authenticated();
auth_reauthenticate();

current_user_ensure_unprotected();

$t_providers = TableWriterFactory::getProviders();
$t_list = array();
foreach( $t_providers as $t_id => $t_provider ) {
	$t_row = array();
	$t_row['id'] = $t_provider->unique_id;
	$t_row['description'] = $t_provider->short_name . ' (.' . $t_provider->file_extension . ')';
	$t_row['provider_name'] = $t_provider->provider_name;
	$t_row['config_page'] = $t_provider->config_page_for_user;
	$t_list[] = $t_row;
}

function print_export_row( $p_row ) {
	echo'<tr>';
	echo '<td>', $p_row['description'], '</td>';
	echo '<td>', $p_row['provider_name'], '</td>';
	if( $p_row['config_page'] ) {
		$t_config_link = '<a href="' . $p_row['config_page'] . '">' . 'CONFIG' . '</a>';
	} else {
		$t_config_link = '';
	}
	echo '<td>', $t_config_link, '</td>';
	echo'</tr>';
}

layout_page_header( 'ACCOUNT_EXPORT' );
layout_page_begin();
print_account_menu( 'account_export_page.php' );

?>
<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>

	<div class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<i class="ace-icon fa fa-columns "></i>
				<?php echo 'AVAILABLE_EXPORT_METHODS' ?>
			</h4>
		</div>
		<div class="widget-body">
			<div class="widget-main no-padding">
				<div class="table-responsive sortable listjs-table">
					<table class="table table-bordered table-hover table-condensed table-striped">
						<thead>
							<tr>
								<th><?php echo 'DESCRIPTION' ?></th>
								<th><?php echo 'PROVIDER' ?></th>
								<th><?php echo 'CONFIG' ?></th>
							<tr>
						</thead>
						<tbody>
							<?php
							foreach( $t_list as $t_row ) {
								print_export_row( $t_row );
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<?php

layout_page_end();