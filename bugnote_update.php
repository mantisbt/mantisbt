<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Update bugnote data then redirect to the appropriate viewing page
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	bugnote_ensure_exists( $f_bugnote_id );
	$t_bug_id = bugnote_get_field( $f_bugnote_id, 'bug_id' );
	project_access_check( $t_bug_id );
	check_access( REPORTER );
	bug_ensure_exists( $t_bug_id );

	$f_bugnote_text		= $f_bugnote_text."\n\n";
	$f_bugnote_text		= $f_bugnote_text.$s_edited_on.date( config_get( 'normal_date_format' ) );

#@@@ jf - need to add string_prepare_textarea() call or something once that is resolved
	$result = bugnote_update_text( $f_bugnote_id, $f_bugnote_text );

	# Determine which view page to redirect back to.
	$t_redirect_url = get_bug_link_plain( $t_bug_id );
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
