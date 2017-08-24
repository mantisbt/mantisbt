<?php

# TODO check which imports are unnessesary
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'database_api.php' );
require_api( 'email_api.php' );
require_api( 'error_api.php' );
require_api( 'helper_api.php' );
require_api( 'history_api.php' );
require_api( 'project_api.php' );
require_api( 'string_api.php' );
require_api( 'utility_api.php' );

$g_custom_field_types[CUSTOM_FIELD_TYPE_STRING] = 'standard';
$g_custom_field_types[CUSTOM_FIELD_TYPE_TEXTAREA] = 'standard';
$g_custom_field_types[CUSTOM_FIELD_TYPE_NUMERIC] = 'standard';
$g_custom_field_types[CUSTOM_FIELD_TYPE_FLOAT] = 'standard';
$g_custom_field_types[CUSTOM_FIELD_TYPE_ENUM] = 'standard';
$g_custom_field_types[CUSTOM_FIELD_TYPE_EMAIL] = 'standard';
$g_custom_field_types[CUSTOM_FIELD_TYPE_CHECKBOX] = 'standard';
$g_custom_field_types[CUSTOM_FIELD_TYPE_LIST] = 'standard';
$g_custom_field_types[CUSTOM_FIELD_TYPE_MULTILIST] = 'standard';
$g_custom_field_types[CUSTOM_FIELD_TYPE_DATE] = 'standard';

/**
 * Always returns a language no matter which errors may occur.
 * If the
 * language can not be successfully read, then the default language
 * "english" will be returned.
 * @return string Language
 */
function cp_get_language() {
	$t_user_id = auth_get_current_user_id();
	if( $t_user_id == null ) {
		return "english";
	}
	
	$t_pref = user_pref_get( $t_user_id );
	if( $t_pref == null ) {
		return "english";
	}
	
	$t_lang = $t_pref->language;
	if( $t_lang == null ) {
		return "english";
	}
	return $t_lang;
}

/**
 * Returns true, if the passed string is a localisation key.
 * See the
 * pattern.
 * @param string $p_possible_key String to check
 * @return bool Is localisation key
 */
function cp_is_custom_field_localisation_key( $p_possible_key ) {
	return (preg_match( '#^Ref\:\\d*\:\\d*$#', $p_possible_key ) === 1);
}

/**
 * Returns the localized custom field value behind the localisation key.
 * If no translation available for the current language then the key is
 * returned. If the key is no localisation key the key is returned.
 * @param string $p_key
 * @return string Localized text
 */
function cp_get_localized_custom_field_value( $p_key ) {
	$t_is_key = cp_is_custom_field_localisation_key( $p_key );
	if( !$t_is_key ) {
		return $p_key;
	}
	
	$t_key_parts = explode( ':', $p_key );
	$t_field_id = intval( $t_key_parts[1] );
	$t_field_idx = intval( $t_key_parts[2] );
	
	$t_values = array( cp_get_language(), $t_field_id );
	
	db_param_push();
	
	$t_query = '
    SELECT IF (b.possible_values IS NULL, a.possible_values, b.possible_values) possible_values
    FROM {custom_field} a
    LEFT JOIN {custom_field_localized} b
    ON a.id = b.id AND b.text_lang = ' . db_param() . '
    WHERE a.id = ' . db_param() . '
    ';
	$t_result = db_query( $t_query, $t_values );
	$t_row = db_fetch_array( $t_result );
	
	$t_translated = explode( '|', $t_row['possible_values'] );
	if( isset( $t_translated[$t_field_idx] ) ) {
		return $t_translated[$t_field_idx];
	}
	# TODO Find a good default, because printing out the ref is not sexy!
	return 'No value found: ' . $p_key;
}

/**
 * Create a localisation of the custom field for the desired language.
 * @param unknown $p_field_id Custom field id
 * @param unknown $p_language Desired language
 * @param unknown $p_name Field name
 * @param unknown $p_possible_values Value string
 * @param unknown $p_default_value Defualt value
 */
function cp_create_custom_field_localisation( $p_field_id, $p_language, $p_name, $p_possible_values, $p_default_value ) {
	if( !custom_field_exists( $p_field_id ) ) {
		return false;
	}
	
	$query = "
        SELECT *
        FROM {custom_field_localized}
        WHERE id = " . db_param() . "
        AND text_lang = " . db_param() . "
    ";
	$result = db_query( $query, array( $p_field_id, $p_language ) );
	if( db_num_rows( $result ) == 0 ) {
		# If the key does not exist create a ney entry.
		
		$insert_query = "
            INSERT INTO {custom_field_localized}
            VALUES (
                " . db_param() . ", " . db_param() . ", " . db_param() . ", " . db_param() . ", " . db_param() . "
            )
        ";
		$t_params = array( $p_field_id, $p_language, $p_name, $p_possible_values, $p_default_value );
		$insert_result = db_query( $insert_query, $t_params );
	} else {
		# If the key already exists, update name, possible_values and
		# default_value.
		
		$update_query = "
            UPDATE {custom_field_localized} SET
                name            = " . db_param() . ",
                possible_values = " . db_param() . ",
                default_value   = " . db_param() . "
            WHERE id       = " . db_param() . "
            AND text_lang = " . db_param() . "
        ";
		$t_params = array( $p_name, $p_possible_values, $p_default_value, $p_field_id, $p_language );
		$update_result = db_query( $update_query, $t_params );
	}
	return true;
}

/**
 * Delete a localisation.
 * @param unknown $p_field_id Custom field id
 * @param unknown $p_language Language
 */
function cp_drop_custom_field_localisation( $p_field_id, $p_language ) {
	if( !custom_field_exists( $p_field_id ) ) {
		return false;
	}
	
	db_param_push();
	
	$t_query = '
        DELETE FROM {custom_field_localized}
        WHERE       id = ' . db_param() . '
    ';
	
	if( $p_language != null ) {
		$t_query .= 'AND text_lang = ' . db_param();
		db_query( $t_query, array( $p_field_id, $p_language ) );
		return true;
	}
	db_query( $t_query, array( $p_field_id ) );
}

/**
 * Creates a translation for the category or updates it, if it already exists.
 * @param unknown $p_category_id Id of category
 * @param unknown $p_language Desired language
 * @param unknown $p_name Name of category
 */
function cp_create_category_localisation( $p_category_id, $p_language, $p_name ) {
	$query = "
        SELECT *
        FROM {category_localized}
        WHERE id = " . db_param() . "
        AND text_lang = " . db_param() . "
    ";
	$result = db_query( $query, array( $p_category_id, $p_language ) );
	
	if( db_num_rows( $result ) == 0 ) {
		$insert_query = "
            INSERT INTO {category_localized}
            VALUES (
                " . db_param() . ", " . db_param() . ", " . db_param() . "
            )
        ";
		$t_params = array( $p_category_id, $p_language, $p_name );
		$insert_result = db_query( $insert_query, $t_params );
	} else {
		$update_query = "
            UPDATE {category_localized} SET
                name            = " . db_param() . ",
            WHERE id       = " . db_param() . "
            AND text_lang = " . db_param() . "
        ";
		$t_params = array( $p_name, $p_category_id, $p_language );
		$update_result = db_query( $update_query, $t_params );
	}
}

/**
 * Removes the translation of the category.
 * @param int $p_category_id Id of category
 * @param string $p_language Desired language
 */
function cp_drop_category_localisation( $p_category_id, $p_language ) {
	db_param_push();
	$t_query = '
        DELETE FROM {category_localized}
        WHERE id =' . db_param() . '
        AND text_lang = ' . db_param() . '
    ';
	db_query( $t_query, array( $p_category_id, $p_language ) );
}

/**
 * Removes all translations of categories related to the project.
 * @param unknown $p_project_id Id of project
 */
function cp_drop_category_localisation_by_project( $p_project_id ) {
	db_param_push();
	$t_query = 'SELECT id FROM {category} WHERE project_id = ' . db_param();
	$t_result = db_query( $t_query, array( $p_project_id ) );
	
	while ( $t_row = db_fetch_array( $t_result ) ) {
		$t_curr_cat_id = $t_row['id'];
		
		db_param_push();
		$t_query = 'DELETE FROM {category_localized} WHERE id =' . db_param();
		db_query( $t_query, array( $t_curr_cat_id ) );
	}
}

/**
 * Create a translation for the project or update it if it exists.
 * @param int $p_project_id Project id
 * @param string $p_language Desired language
 * @param string $p_name Name of project
 * @param string $p_description Description of project
 * @return void
 */
function cp_create_project_localisation( $p_project_id, $p_language, $p_name, $p_description ) {
	$query = "
        SELECT *
        FROM {project_localized}
        WHERE id = " . db_param() . "
        AND text_lang = " . db_param() . "
    ";
	$result = db_query( $query, array( $p_project_id, $p_language ) );
	
	if( db_num_rows( $result ) == 0 ) {
		$insert_query = "
            INSERT INTO {project_localized}
            VALUES (
                " . db_param() . ", " . db_param() . ", " . db_param() . ", " . db_param() . "
            )
        ";
		$t_params = array( $p_project_id, $p_language, $p_name, $p_description );
		$insert_result = db_query( $insert_query, $t_params );
	} else {
		$update_query = "
            UPDATE {category_localized} SET
                name         = " . db_param() . ",
                description  = " . db_param() . ",
            WHERE id       = " . db_param() . "
            AND text_lang = " . db_param() . "
        ";
		$t_params = array( $p_name, $p_description, $p_project_id, $p_language );
		$update_result = db_query( $update_query, $t_params );
	}
}

/**
 * Remove the localisations for the passed language of the project.
 * @param unknown $p_project_id Project id
 */
function cp_drop_project_localisation( $p_project_id, $_language ) {
	db_param_push();
	$t_query = '
        DELETE  FROM {project_localized}
        WHERE   id = ' . db_param() . '
        AND     text_lang = ' . db_param() . '
    ';
	db_query( $t_query, array( $p_project_id, cp_get_language() ) );
}

/**
 * Remove all localisations of the project.
 * @param unknown $p_project_id Project id
 */
function cp_drop_project_localisation_by_project( $p_project_id ) {
	db_param_push();
	$t_query = 'DELETE FROM {project_localized} WHERE id = ' . db_param();
	db_query( $t_query, array( $p_project_id ) );
}