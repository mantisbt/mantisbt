<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Revision: 1.9 $
	# $Author: vboctor $
	# $Date: 2002-06-25 14:23:57 $
	#
	# $Id: account_delete.php,v 1.9 2002-06-25 14:23:57 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	# Delete account, remove cookies, and redirect user to logout redirect page
	# If the account is protected this fails.
?>
<?php include( 'core_API.php' ) ?>
<?php login_cookie_check() ?>
<?php
	# check if users can't delete their own accounts
	if ( OFF == $g_allow_account_delete ) {
		print_header_redirect( 'account_page.php' );
	}

	# get protected state
	$t_protected = get_current_user_field( 'protected' );

	# protected account check
	if ( ON == $t_protected ) {
		print_mantis_error( ERROR_PROTECTED_ACCOUNT );
	}

	# If an account is protected then no one can change the information
	# This is useful for shared accounts or for demo purposes
	$result = 0;
	if ( OFF == $t_protected ) {

		# get user id
		$t_user_id = get_current_user_field( 'id' );

		# Remove account
		$query ="DELETE ".
				"FROM $g_mantis_user_table ".
				"WHERE id='$t_user_id'";
		$result = db_query( $query );

		# Remove associated profiles
		$query ="DELETE ".
				"FROM $g_mantis_user_profile_table ".
				"WHERE user_id='$t_user_id'";
		$result = db_query( $query );

		# Remove associated preferences
		$query ="DELETE ".
				"FROM $g_mantis_user_pref_table ".
				"WHERE user_id='$t_user_id'";
		$result = db_query( $query );

		$query ="DELETE ".
				"FROM $g_mantis_project_user_list_table ".
				"WHERE user_id='$f_id'";
		$result = db_query( $query );

		# delete cookies
		setcookie( $g_string_cookie );
		setcookie( $g_project_cookie );
		setcookie( $g_view_all_cookie );

		drop_user_info_cache();
	} # end if protected
?>
<?php print_page_top1() ?>
<?php
	if ( $result ) {
		print_meta_redirect( 'login_page.php' );
	}
?>
<?php print_page_top2() ?>

<p>
<div align="center">
<?php
	if ( ON == $t_protected ) {				# PROTECTED
		PRINT $s_account_protected_msg.'<p>';
		print_bracket_link( 'account_page.php', $s_go_back );
	} else if ( $result ) {					# SUCCESS
		PRINT $s_operation_successful.'<p>';
		print_bracket_link( 'login_page.php', $s_proceed );
	} else {								# FAILURE
		print_sql_error( $query );
	}
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
