<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

	# --------------------------------------------------------
	# $Id$
	# --------------------------------------------------------

	$t_manage_page = defined( 'MANAGE_COLUMNS' );
	$t_account_page = defined( 'ACCOUNT_COLUMNS' );

	# Protect against direct access to this script.
	if ( !$t_manage_page && !$t_account_page ) {
		require_once( 'core.php' );
		$t_core_path = config_get( 'core_path' );
		require_once( $t_core_path . 'authentication_api.php' );

		access_denied();
	}

	$t_project_id = helper_get_current_project();

	$t_columns = columns_get_all();
	$t_all = implode( ', ', $t_columns );

	$t_columns = helper_get_columns_to_view( COLUMNS_TARGET_CSV_PAGE );
	$t_csv = implode( ', ', $t_columns );

	$t_columns = helper_get_columns_to_view( COLUMNS_TARGET_VIEW_PAGE );
	$t_view_issues = implode( ', ', $t_columns );

	$t_columns = helper_get_columns_to_view( COLUMNS_TARGET_PRINT_PAGE );
	$t_print_issues = implode( ', ', $t_columns );

	$t_columns = helper_get_columns_to_view( COLUMNS_TARGET_EXCEL_PAGE );
	$t_excel = implode( ', ', $t_columns );

	echo '<br />';
?>

<div align="center">
<form name="report_bug_form" method="post" <?php if ( file_allow_bug_upload() ) { echo 'enctype="multipart/form-data"'; } ?> action="manage_config_columns_set.php">
<table class="width50" cellspacing="1">


<!-- Title -->
<tr>
	<td class="form-title">
		<input type="hidden" name="project_id" value="<?php echo $t_project_id ?>" />
		<input type="hidden" name="form_page" value="<?php echo $t_account_page ? 'account' : 'manage'; ?>" />
		<?php echo lang_get( 'manage_columns_config' ) ?>
	</td>
	<td class="right">
		<?php 
			if ( $t_account_page ) {
				print_account_menu( 'account_manage_columns_page.php' );
			}
		?>
	</td>
</tr>

<!-- view issues columns -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'all_columns_title' )?>
	</td>
	<td>
		<textarea <?php echo helper_get_tab_index() ?> name="all_columns" readonly="readonly" cols="80" rows="5"><?php echo $t_all ?></textarea>
	</td>
</tr>

<!-- view issues columns -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'view_issues_columns_title' ), '<span class="required">*</span>' ?>
	</td>
	<td>
		<textarea <?php echo helper_get_tab_index() ?> name="view_issues_columns" cols="80" rows="5"><?php echo $t_view_issues ?></textarea>
	</td>
</tr>

<!-- print issues columns -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'print_issues_columns_title' ), '<span class="required">*</span>' ?>
	</td>
	<td>
		<textarea <?php echo helper_get_tab_index() ?> name="print_issues_columns" cols="80" rows="5"><?php echo $t_print_issues ?></textarea>
	</td>
</tr>

<!-- csv columns -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'csv_columns_title' ), '<span class="required">*</span>' ?>
	</td>
	<td>
		<textarea <?php echo helper_get_tab_index() ?> name="csv_columns" cols="80" rows="5"><?php echo $t_csv ?></textarea>
	</td>
</tr>

<!-- csv columns -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'excel_columns_title' ), '<span class="required">*</span>' ?>
	</td>
	<td>
		<textarea <?php echo helper_get_tab_index() ?> name="excel_columns" cols="80" rows="5"><?php echo $t_excel ?></textarea>
	</td>
</tr>

<!-- Submit Button -->
<tr>
	<td class="left">
		<span class="required"> * <?php echo lang_get( 'required' ) ?></span>
	</td>
	<td class="center">
		<?php
			if ( $t_account_page ) {
				if ( $t_project_id == ALL_PROJECTS ) { ?>
		<input <?php echo helper_get_tab_index() ?> type="submit" class="button" name="update_columns_as_my_default" value="<?php echo lang_get( 'update_columns_as_my_default' ) ?>" />
		<?php } else { ?>
		<input <?php echo helper_get_tab_index() ?> type="submit" class="button" name="update_columns_for_current_project" value="<?php echo lang_get( 'update_columns_for_current_project' ) ?>" />
		<?php } } ?>
<?php if ( $t_manage_page && current_user_is_administrator() ) { ?>
		<input <?php echo helper_get_tab_index() ?> type="submit" class="button" name="update_columns_as_global_default" value="<?php echo lang_get( 'update_columns_as_global_default' ) ?>" />
<?php } ?>
	</td>
</tr>

</table>
</form>
</div>

<br />

<?php
	if ( $t_account_page ) {
?>
<!-- RESET CONFIGURATION -->
<div class="border-center">
<!-- Reset Button -->
	<form method="post" action="manage_config_columns_reset.php">
		<input type="submit" class="button" value="<?php echo lang_get( 'reset_columns_configuration' ) ?>" />
	</form>
</div>
<?php
	}
?>