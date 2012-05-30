<?php
# MantisBT - a php based bugtracking system

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
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */

	$t_manage_page = defined( 'MANAGE_COLUMNS' );
	$t_account_page = defined( 'ACCOUNT_COLUMNS' );

	# Protect against direct access to this script.
	if ( !$t_manage_page && !$t_account_page ) {
		/**
		 * MantisBT Core API's
		 */
		require_once( 'core.php' );

		require_once( 'authentication_api.php' );

		access_denied();
	}

	$t_project_id = helper_get_current_project();

	# Calculate the user id to set the configuration for.
	if ( $t_manage_page ) {
		$t_user_id = NO_USER;
	} else {
		$t_user_id = auth_get_current_user_id();
	}

	$t_columns = columns_get_all( $t_project_id );
	$t_all = implode( ', ', $t_columns );

	$t_columns = helper_get_columns_to_view( COLUMNS_TARGET_CSV_PAGE, /* $p_viewable_only */ false, $t_user_id );
	$t_csv = implode( ', ', $t_columns );

	$t_columns = helper_get_columns_to_view( COLUMNS_TARGET_VIEW_PAGE, /* $p_viewable_only */ false, $t_user_id );
	$t_view_issues = implode( ', ', $t_columns );

	$t_columns = helper_get_columns_to_view( COLUMNS_TARGET_PRINT_PAGE, /* $p_viewable_only */ false, $t_user_id );
	$t_print_issues = implode( ', ', $t_columns );

	$t_columns = helper_get_columns_to_view( COLUMNS_TARGET_EXCEL_PAGE, /* $p_viewable_only */ false, $t_user_id );
	$t_excel = implode( ', ', $t_columns );

?>



<form name="report_bug_form" method="post" <?php if ( file_allow_bug_upload() ) { echo 'enctype="multipart/form-data"'; } ?> action="manage_config_columns_set.php">
<?php echo form_security_field( 'manage_config_columns_set' ) ?>

<!-- Title -->

		<input type="hidden" name="project_id" value="<?php echo $t_project_id ?>" />
		<input type="hidden" name="form_page" value="<?php echo $t_account_page ? 'account' : 'manage'; ?>" />

<div class="page-header">
		<h1><?php echo lang_get( 'manage_columns_config' )?>
	
</h1>
	</div>
	
	<div class="row-fluid">
        <div class="span3">
          <div class="well sidebar-nav" style="padding:19px 0px;">	


		
	
		<?php
			if ( $t_account_page ) {
				print_account_menu( 'account_manage_columns_page.php' );
			}
		?>
          </div>
          <?php
	if ( $t_account_page ) {
?>
<!-- RESET CONFIGURATION -->
<!-- Reset Button -->
	<form method="post" action="manage_config_columns_reset.php">
		<?php echo form_security_field( 'manage_config_columns_reset' ) ?>
		<input type="submit" class="btn btn-large btn-danger span12" value="<?php echo lang_get( 'reset_columns_configuration' )?>" />
	</form>
<?php
	}?>
	
	<br />
	
	<?php
			if ( $t_account_page ) {
				if ( $t_project_id == ALL_PROJECTS ) { ?>
		<input <?php echo helper_get_tab_index() ?> type="submit" class="btn btn-primary btn-large span12" name="update_columns_as_my_default" value="<?php echo lang_get( 'update_columns_as_my_default' )?>" />
		<?php } else { ?>
		<input <?php echo helper_get_tab_index() ?> type="submit" class="btn btn-primary btn-large span12" name="update_columns_for_current_project" value="<?php echo lang_get( 'update_columns_for_current_project' )?>" />
		<?php } } ?>
<?php if ( $t_manage_page && current_user_is_administrator() ) { ?>
		<input <?php echo helper_get_tab_index() ?> type="submit" class="btn btn-large btn-primary span12" name="update_columns_as_global_default" value="<?php echo lang_get( 'update_columns_as_global_default' )?>" />
<?php } ?>


        </div>
          <div class="span9">

<!-- view issues columns -->

	
		<label><?php echo lang_get( 'all_columns_title' )?></label>
	
		<textarea <?php echo helper_get_tab_index() ?> name="all_columns" readonly="readonly" class="span12"><?php echo $t_all ?></textarea>


<!-- view issues columns -->

	
		<?php echo lang_get( 'view_issues_columns_title' ), '<span class="required">*</span>' ?>
	
		<textarea <?php echo helper_get_tab_index() ?> name="view_issues_columns" class="span12"><?php echo $t_view_issues ?></textarea>


<!-- print issues columns -->

	
		<?php echo lang_get( 'print_issues_columns_title' ), '<span class="required">*</span>' ?>
	
		<textarea <?php echo helper_get_tab_index() ?> name="print_issues_columns" class="span12"><?php echo $t_print_issues ?></textarea>


<!-- csv columns -->

	
		<?php echo lang_get( 'csv_columns_title' ), '<span class="required">*</span>' ?>
	
		<textarea <?php echo helper_get_tab_index() ?> name="csv_columns" class="span12"><?php echo $t_csv ?></textarea>


<!-- csv columns -->

	
		<?php echo lang_get( 'excel_columns_title' ), '<span class="required">*</span>' ?>
	
		<textarea <?php echo helper_get_tab_index() ?> name="excel_columns" class="span12"><?php echo $t_excel ?></textarea>


<!-- Submit Button -->

	
		<span class="required"> * <?php echo lang_get( 'required' )?></label>
	
</span>
	
		
</form>

<form method="post" action="manage_columns_copy.php">
<?php echo form_security_field( 'manage_columns_copy' ) ?>

<!-- Copy Columns -->

			<input type="hidden" name="project_id" value="<?php echo $t_project_id ?>" />
			<input type="hidden" name="manage_page" value="<?php echo $t_manage_page ?>" />

			<select name="other_project_id">
				<?php print_project_option_list( /* project_id */ null, /* include_all_projects */ true, /* filter_project_id */ $t_project_id ); ?>
			</select>
<?php
		# Skip "Copy From" if the current project is ALL PROJECTS, the current page is management page, and the user is not administrator
		if ( !$t_manage_page || ( $t_project_id != ALL_PROJECTS ) || current_user_is_administrator() ) {
?>
			<input type="submit" name="copy_from" class="btn" value="<?php echo lang_get( 'copy_columns_from' )?>" />
<?php
		}
?>
			<input type="submit" name="copy_to" class="btn" value="<?php echo lang_get( 'copy_columns_to' )?>" />

</form>



