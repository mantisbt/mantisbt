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
	 * This page allows users to add a new profile which is POSTed to
	 * account_prof_add.php
	 *
	 * Users can also manage their profiles
	 *
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	if ( isset( $g_global_profiles ) ) {
		$g_global_profiles = true;
	} else {
		$g_global_profiles = false;
	}

	require_once( 'current_user_api.php' );

	auth_ensure_user_authenticated();

	current_user_ensure_unprotected();

	if ( $g_global_profiles ) {
		access_ensure_global_level( config_get( 'manage_global_profile_threshold' ) );
	} else {
		access_ensure_global_level( config_get( 'add_profile_threshold' ) );
	}

	html_page_top( lang_get( 'manage_profiles_link' ) );

	if ( $g_global_profiles ) {
		print_manage_menu( 'manage_prof_menu_page.php' );
	}

	if ( $g_global_profiles ) {
		$t_user_id = ALL_USERS;
	} else {
		$t_user_id = auth_get_current_user_id();
	}

	# Add Profile Form BEGIN
?>
<form method="post" action="account_prof_update.php">
<?php  echo form_security_field( 'profile_update' )?>
<input type="hidden" name="action" value="add" />

	
		<input type="hidden" name="user_id" value="<?php echo $t_user_id ?>" />
		<div class="page-header">
			<h1><?php echo lang_get( 'add_profile_title' ) ?></h1>
		</div>
	
	<div class="row-fluid">
        <div class="span3">
          <div class="well sidebar-nav" style="padding:19px 0px;">	
	
	<?php
		if ( !$g_global_profiles ) {
			print_account_menu( 'account_prof_menu_page.php' );
		}
	?>
        </div>
        		<input type="submit" class="btn btn-primary btn-large span12" value="<?php echo lang_get( 'add_profile_button' ) ?>" />

        </div>
        <div class="span9">
	


        <label>
		<span class="required">*</span><?php echo lang_get( 'platform' ) ?>
        </label>
	
		<input type="text" name="platform" size="32" maxlength="32" />
	


	<label>
		<span class="required">*</span><?php echo lang_get( 'operating_system' ) ?>
	</label>
	
		<input type="text" name="os" size="32" maxlength="32" />
	


	<label>
		<span class="required">*</span><?php echo lang_get( 'os_version' ) ?>
	</label>
	
		<input type="text" name="os_build" size="16" maxlength="16" />
	


	<label>
		<?php echo lang_get( 'additional_description' ) ?>
	</label>
	
		<textarea name="description" cols="60" rows="8"></textarea>
	
</form>
<?php 
	# Add Profile Form END
	# Edit or Delete Profile Form BEGIN

	$t_profiles = profile_get_all_for_user( $t_user_id );
	if( $t_profiles ) {
?>
<form method="post" action="account_prof_update.php">
<?php  echo form_security_field( 'profile_update' )?>

		<h2><?php echo lang_get( 'edit_or_delete_profiles_title' ) ?></h2>
	


		<input type="radio" name="action" value="edit" checked="checked" /> <?php echo lang_get( 'edit_profile' ) ?>
<?php
	if ( !$g_global_profiles ) {
?>
		<input type="radio" name="action" value="make_default" /> <?php echo lang_get( 'make_default' ) ?>
<?php
	}
?>
		<input type="radio" name="action" value="delete" /> <?php echo lang_get( 'delete_profile' ) ?>
	


	
		<label><?php echo lang_get( 'select_profile' ) ?></label>
	
	
		<select name="profile_id">
			<?php print_profile_option_list( $t_user_id, '', $t_profiles ) ?>
		</select>
	


		<input type="submit" class="btn" value="<?php echo lang_get( 'submit_button' ) ?>" />
	
</form>
<?php 
} # Edit or Delete Profile Form END

html_page_bottom();
