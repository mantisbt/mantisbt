<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	### Users may change their user information from this page.
	### The data is POSTed to account_update.php3
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	### extracts the user information for the currently logged in user
	### and prefixes it with u_
    $query = "SELECT *
    		FROM $g_mantis_user_table
			WHERE cookie_string='$g_string_cookie_val'";
    $result = db_query( $query );
	$row = db_fetch_array( $result );
	extract( $row, EXTR_PREFIX_ALL, "u" );
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<? include( $g_meta_include_file ) ?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>
<? print_top_page( $g_top_include_page ) ?>

<? print_menu( $g_menu_include_file ) ?>

<? print_account_menu( $g_account_page ) ?>

<? ### Edit Account Form BEGIN ?>
<p>

<div align="center">

<table width="50%" bgcolor="<? echo $g_primary_border_color ?>" <? echo $g_primary_table_tags ?>>
<tr>
	<td bgcolor="<? echo $g_white_color ?>">
	<table cols="2" width="100%">
	<form method="post" action="<? echo $g_account_update ?>">
	<tr>
		<td colspan="2" bgcolor="<? echo $g_table_title_color ?>">
			<b><? echo $s_edit_account_title ?></b>
		</td>
	</tr>
	

<? 
    ## If we ARE NOT using LDAP to login
    if ( $g_login_method != LDAP) { 

print <<<EOF

	<tr bgcolor="$g_primary_color_dark">
		<td width="40%">
			$s_username:
		</td>
		<td width="60%">
			<input type="text" size="16" maxlength="32" name="f_username" value="$u_username">
		</td>
	</tr>
	<tr bgcolor="$g_primary_color_dark">
		<td>
			$s_password:
		</td>
		<td>
			<input type="password" size="32" maxlength="32" name="f_password">
		</td>
	</tr>
	<tr bgcolor="$g_primary_color_light">
		<td>
			$s_confirm_password:
		</td>
		<td>
			<input type="password" size="32" maxlength="32" name="f_password_confirm">
		</td>
	</tr>

EOF;
    } 
    ## If we ARE using LDAP to login
    if ( $g_login_method == LDAP) { 
print <<<EOF

	<tr bgcolor="$g_primary_color_light" >
		<td colspan=2 bgcolor="$g_primary_color_light">
			The password settings are controlled by your LDAP entry,<BR>
			hence cannot be edited here.
		</td>
	</tr>
	<tr bgcolor="$g_primary_color_light" >
		<td width="40%" bgcolor="$g_primary_color_light">
		    $s_username:
		</td>
		<td width="60%" bgcolor="$g_primary_color_light">
		    $u_username
		</td>
	</tr>

EOF;

    }

    
    if ( $g_use_ldap_email == 1 ) {
    $email_address=get_user_info( "$u_username","email" );
print <<<EOF

	<tr bgcolor="$g_primary_color_light" >

		<td width="40%" bgcolor="$g_primary_color_light">
		    $s_email:
		</td>
		<td width="60%" bgcolor="$g_primary_color_light">
		    $email_address
		</td>
	</tr>

EOF;
    }

?>

<?

     if ( ! $g_use_ldap_email) { 

print <<<EOF
    <tr bgcolor="$g_primary_color_light">
	<td>
	    $s_email:
	</td>
	<td>
	    <input type="text" size="32" maxlength="64" name="f_email" value="$u_email">
	</td>
    </tr>
EOF;

     }
?>
    
	<tr bgcolor="<? echo $g_primary_color_dark ?>">
		<td>
			<? echo $s_access_level ?>:
		</td>
		<td>
			<? echo get_enum_element( $s_access_levels_enum_string, $u_access_level ) ?>
		</td>
	</tr>
	<tr>
	
<? 
    ## If we ARE NOT using LDAP
    if ( $g_login_method != LDAP) { 
    
print <<<EOF

		<td align="left">
			<input type="submit" value="$s_update_user_button">
		</td>

EOF;

    }
?>
		</form>
		<form method="post" action="<? echo $g_account_delete_page ?>">
		<td align="right">
			<input type="submit" value="<? echo $s_delete_account_button ?>">
		</td>
		</form>
	</tr>
	</table>
	</td>
</tr>
</table>
</div>

<? ### Edit Account Form END ?>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>
