<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	check_access( config_get( 'manage_project_threshold' ) );

	# attribute definition

	$t_status_flag = 0 ; #for colors
	# complete this switch to add new attributes
	switch ($f_parameter) {
		case 'priorities' :
			$t_title = lang_get( 'priority' ) ;
			break;
		case 'severities' :
			$t_title = lang_get( 'severity' ) ;
			break;
		case 'reproducibilities' :
			$t_title = lang_get( 'reproducibility' ) ;
			break;
		case 'states':
			$t_title = lang_get( 'status' ) ;
			$t_status_flag = 1 ;
			break;
		case 'resolutions' :
			$t_title = lang_get( 'resolution' ) ;
			break;
		case 'projections' :
			$t_title = lang_get( 'projection' ) ;
			break;
		case 'etas' :
			$t_title = lang_get( 'eta' ) ;
			break;
	} // end switch

	if ( is_blank( $f_attribute ) ) {
		print_mantis_error( ERROR_EMPTY_FIELD );
	}


	# Grab the data to test if the table is empty
    $query = "SELECT $f_parameter
    		FROM $g_mantis_project_customization_table
			WHERE project_id='$f_project_id'";
    $result = db_query($query);

    ## OOPS, No entry in the database yet.  Lets make one
    if ( 0 == db_num_rows( $result ) ) {

		# add attribute field and project id
		$query = "INSERT
				INTO $g_mantis_project_customization_table
				(project_id)
				VALUES
				('$f_project_id')";

		$result = db_query($query);
    }

	str_replace(',',' ',$f_attribute); // avoid ',' in the text field, which create multiple entries
	$t_attributes_array = explode( '|', $f_attribute );
	$t_count = count( $t_attributes_array );
	$result = true;
	$duplicate = false;
	check_varset( $f_color, $g_background_color  ) ;

	foreach ( $t_attributes_array as $t_attribute ) {
		$t_attribute = trim( $t_attribute );
		if ( $t_attribute == '') {
			continue;
		}

		$t_duplicate = attribute_is_duplicate(  $f_parameter, $f_project_id, $t_attribute );
		if ( !$t_duplicate ) {
			$t_result = attribute_add( $f_parameter, $f_project_id, $t_attribute);
			if ($t_status_flag) {
				$t_result2 = attribute_add( 'colors', $f_project_id, $f_color);
			}
			$result = $result && $t_result;
		} else {
			$duplicate = true;
			$result = false;
		}
	}
	//$t_redirect_url = 'manage_proj_edit_page.php?f_project_id='.$f_project_id;
	$t_redirect_url = 'manage_proj_menu_page.php';
?>
<?php print_page_top1() ?>
<?php
	if ( $result ) {
		print_meta_redirect( $t_redirect_url );
	}
?>
<?php print_page_top2() ?>

<br />
<div align="center">
<?php
	if ( $result ) {				# SUCCESS
		echo lang_get( 'operation_successful' ).'<br />';
	} else if ( $duplicate ) {		# DUPLICATE
		echo $MANTIS_ERROR[ERROR_DUPLICATE_CATEGORY].'<br />';
	} else {						# FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
