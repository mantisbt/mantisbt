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
	# $Id: manage_user_update.php,v 1.41.2.1 2007-10-13 22:33:58 giallu Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'email_api.php' );

	form_security_validate('manage_user_update');

	auth_reauthenticate();
	access_ensure_global_level( config_get( 'manage_user_threshold' ) );

	$f_protected	= gpc_get_bool( 'protected' );
	$f_enabled		= gpc_get_bool( 'enabled' );
	$f_email		= gpc_get_string( 'email', '' );
	$f_username		= gpc_get_string( 'username', '' );
	$f_realname		= gpc_get_string( 'realname', '' );
	$f_access_level	= gpc_get_int( 'access_level' );
	$f_user_id		= gpc_get_int( 'user_id' );

	$f_email	= trim( $f_email );
	$f_username	= trim( $f_username );

	$t_old_username = user_get_field( $f_user_id, 'username' );

	# check that the username is unique
	if ( 0 != strcasecmp( $t_old_username, $f_username )
        && false == user_is_name_unique( $f_username ) ) {
		trigger_error( ERROR_USER_NAME_NOT_UNIQUE, ERROR );
	}

	user_ensure_name_valid( $f_username );
	user_ensure_realname_valid( $f_realname );
	user_ensure_realname_unique( $f_username, $f_realname );

	$f_email = email_append_domain( $f_email );
	email_ensure_valid( $f_email );
	email_ensure_not_disposable( $f_email );

	$c_email		= db_prepare_string( $f_email );
	$c_username		= db_prepare_string( $f_username );
	$c_realname		= db_prepare_string( $f_realname );
	$c_protected	= db_prepare_bool( $f_protected );
	$c_enabled		= db_prepare_bool( $f_enabled );
	$c_user_id			= db_prepare_int( $f_user_id );
	$c_access_level	= db_prepare_int( $f_access_level );

	$t_user_table = config_get( 'mantis_user_table' );

	$t_old_protected = user_get_field( $f_user_id, 'protected' );
	
	# check that we are not downgrading the last administrator
	$t_old_access = user_get_field( $f_user_id, 'access_level' );
	if ( ( ADMINISTRATOR == $t_old_access ) && ( $t_old_access <> $f_access_level ) && ( 1 >= user_count_level( ADMINISTRATOR ) ) ) {
		trigger_error( ERROR_USER_CHANGE_LAST_ADMIN, ERROR );
	}	   

	# Project specific access rights override global levels, hence, for users who are changed
	# to be administrators, we have to remove project specific rights.
        if ( ( $c_access_level >= ADMINISTRATOR ) && ( !user_is_administrator( $c_user_id ) ) ) {
		user_delete_project_specific_access_levels( $c_user_id );
	}

	# if the user is already protected and the admin is not removing the
	#  protected flag then don't update the access level and enabled flag.
	#  If the user was unprotected or the protected flag is being turned off
	#  then proceed with a full update.
	if ( $f_protected && $t_old_protected ) {
	    $query = "UPDATE $t_user_table
	    		SET username='$c_username', email='$c_email',
	    			protected='$c_protected', realname='$c_realname'
	    		WHERE id='$c_user_id'";
	} else {
	    $query = "UPDATE $t_user_table
	    		SET username='$c_username', email='$c_email',
	    			access_level='$c_access_level', enabled='$c_enabled',
	    			protected='$c_protected', realname='$c_realname'
	    		WHERE id='$c_user_id'";
	}

	$result = db_query( $query );
	$t_redirect_url = 'manage_user_edit_page.php?user_id=' . $c_user_id;

	form_security_purge('manage_user_update');

?>
<?php html_page_top1() ?>
<?php
	if ( $result ) {
		html_meta_redirect( $t_redirect_url );
	}
?>
<?php html_page_top2() ?>

<br />
<div align="center">
<?php
	if ( $f_protected && $t_old_protected ) {				# PROTECTED
		echo lang_get( 'manage_user_protected_msg' ) . '<br />';
	} else if ( $result ) {					# SUCCESS
		echo lang_get( 'operation_successful' ) . '<br />';
	} else {								# FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
