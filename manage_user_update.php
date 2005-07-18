<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: manage_user_update.php,v 1.37 2005-07-18 13:45:22 thraxisp Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'email_api.php' );
?>
<?php
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
	user_ensure_realname_unique( $f_username, $f_realname );

	$f_email = email_append_domain( $f_email );
	email_ensure_valid( $f_email );

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
	$t_redirect_url = 'manage_user_page.php';
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
