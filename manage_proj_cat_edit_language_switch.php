<?php

/**
 * Loads localized category names for the selected language. If there
 * is no localization, an empty string is returned. If default is selected,
 * the original text is returned.
 */
require_once ('core.php');
require_api( 'category_api.php' );

header( 'Content-Type: application/json' );

function update_custom_field() {
	# Define response array
	$localization = array( 'name' => 'ERROR' );
	
	# Check, if parameters are set
	if( isset( $_GET['id'] ) && isset( $_GET['lang'] ) ) {
		$t_id = $_GET['id'];
		$t_lang = $_GET['lang'];
		
		# Build query
		$t_params = array();
		if( strcmp( $t_lang, 'default' ) !== 0 ) {
			$t_query = "
                SELECT  name
                FROM    {category_localized}
                WHERE   id = " . db_param() . "
                AND     text_lang = " . db_param() . "
            ";
			$t_params = array( $t_id, $t_lang );
		} else {
			$t_query = "
                SELECT  name
                FROM    {category}
                WHERE   id = " . db_param() . "
            ";
			$t_params = array( $t_id );
		}
		
		# Execute query
		$t_result = db_query( $t_query, $t_params );
		$row = db_fetch_array( $t_result );
		
		# Save query results
		$t_name = (!isset( $row['name'] ) ? '' : $row['name']);
		
		$localization['name'] = $t_name;
	}
	return $localization;
}
echo json_encode( update_custom_field() );