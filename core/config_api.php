<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: config_api.php,v 1.33 2005-07-17 17:05:28 thraxisp Exp $
	# --------------------------------------------------------

	# cache for config variables
	$g_cache_config = array();
	$g_cache_config_access = array();
	$g_cache_filled = false;
	
	# cache environment to speed up lookups
	$g_cache_db_table_exists = false;

	### Configuration API ###

	# ------------------
	# Retrieves the value of a config option
	#  This function will return one of (in order of preference):
	#    1. value from cache
	#    2. value from database
	#     looks for specified config_id + current user + current project.
	#     if not found, config_id + current user + all_project
	#     if not found, config_id + default user + current project
	#     if not found, config_id + default user + all_project.
	#    3.use GLOBAL[config_id]
	function config_get( $p_option, $p_default = null, $p_user = null, $p_project = null ) {
		global $g_cache_config, $g_cache_config_access, $g_cache_db_table_exists, $g_cache_filled;

		# @@ debug @@ echo "lu o=$p_option ";

		# bypass table lookup for certain options
		$t_match_pattern = '/' . implode( '|', config_get_global( 'global_settings' ) ) . '/';
		$t_bypass_lookup = ( 0 < preg_match( $t_match_pattern, $p_option ) );
		# @@ debug @@ if ($t_bypass_lookup) { echo "bp=$p_option match=$t_match_pattern <br />"; }

		if ( ! $t_bypass_lookup ) {
			$t_config_table = config_get_global( 'mantis_config_table' );
			# @@ debug @@ if ( ! db_is_connected() ) { echo "no db "; }
			# @@ debug @@ echo "lu table=" . ( db_table_exists( $t_config_table ) ? "yes " : "no " );
			if ( ! $g_cache_db_table_exists ) {
				$g_cache_db_table_exists = ( TRUE === db_is_connected() ) &&
					db_table_exists( $t_config_table );
			}

			if ( $g_cache_db_table_exists ) {
				# @@ debug @@ echo " lu db $p_option ";
				# @@ debug @@ error_print_stack_trace();

				# prepare the user's list
				$t_users = array();
				if ( null === $p_user ) {
					$t_users[] = auth_is_user_authenticated() ? auth_get_current_user_id() : ALL_USERS;
				} else {
					$t_users[] = $p_user;
				}
				if ( ! in_array( ALL_USERS, $t_users ) ) {
					$t_users[] = ALL_USERS;
				}

				# prepare the projects list
				$t_projects = array();
				if ( ( null === $p_project )  ) {
					$t_projects[] = auth_is_user_authenticated() ? helper_get_current_project() : ALL_PROJECTS;
				} else {
					$t_projects[] = $p_project;
				}
				if ( ! in_array( ALL_PROJECTS, $t_projects ) ) {
					$t_projects[] = ALL_PROJECTS;
				}

				if ( ! $g_cache_filled ) {
					
					$query = "SELECT config_id, user_id, project_id, type, value, access_reqd FROM $t_config_table";
					$result = db_query( $query );
					while ( false <> ( $row = db_fetch_array( $result ) ) ) {
						$t_config = $row['config_id'];
						$t_user = $row['user_id'];
						$t_project = $row['project_id'];
						$g_cache_config[$t_config][$t_user][$t_project] = $row['type'] . ';' . $row['value'];
						$g_cache_config_access[$t_config][$t_user][$t_project] = $row['access_reqd'];
					}
					$g_cache_filled = true;
				}

				if( isset( $g_cache_config[$p_option] ) ) {
				    $t_found = false;
				    reset( $t_users );
				    while ( ( list( , $t_user ) = each( $t_users ) ) && ! $t_found ) {
					   reset( $t_projects );
					   while ( ( list( , $t_project ) = each( $t_projects ) ) && ! $t_found ) {
					       if ( isset( $g_cache_config[$p_option][$t_user][$t_project] ) ) {
    							$t_value = $g_cache_config[$p_option][$t_user][$t_project];
    							$t_found = true;
    							# @@ debug @@ echo "clu found u=$t_user, p=$t_project, v=$t_value ";
    						}
    					}
    				}
				
    				if ( $t_found ) {
    					list( $t_type, $t_raw_value ) = explode( ';', $t_value, 2 );

    					switch ( $t_type ) {
    						case CONFIG_TYPE_INT:
    							$t_value = (int) $t_raw_value;
    							break;
    						case CONFIG_TYPE_COMPLEX:
    							$t_value = unserialize( $t_raw_value );
    							break;
    						case CONFIG_TYPE_STRING:
    						default:
    							$t_value = config_eval( $t_raw_value );
    					}
    					return $t_value;
    				}
    			}
			}
		}
		return config_get_global( $p_option, $p_default );
	}

	# ----------------------
	# force config variable from a global to avoid recursion
	function config_get_global( $p_option, $p_default = null ) {

		if ( isset( $GLOBALS['g_' . $p_option] ) ) {
			$t_value = config_eval( $GLOBALS['g_' . $p_option] );
			if ( $t_value !== $GLOBALS['g_' . $p_option] ) {
    			$GLOBALS['g_' . $p_option] = $t_value;
    		}
			return $t_value;
		} else {
			# unless we were allowing for the option not to exist by passing
			#  a default, trigger a WARNING
			if ( null === $p_default ) {
				error_parameters( $p_option );
				trigger_error( ERROR_CONFIG_OPT_NOT_FOUND, WARNING );
			}
			return $p_default;
		}
	}

	# ------------------
	# Retrieves the access level needed to change a config value
	function config_get_access( $p_option, $p_user = null, $p_project = null ) {
		global $g_cache_config, $g_cache_config_access, $g_cache_filled;

		# @@ debug @@ echo "lu o=$p_option ";

		if ( ! $g_cache_filled ) {
			$t = config_get( $p_option, -1, $p_user, $p_project );
		}
		
		# prepare the user's list
		$t_users = array( ALL_USERS );
		if ( ( null === $p_user ) && ( auth_is_user_authenticated() ) ) {
			$t_users[] = auth_get_current_user_id();
		} else if ( ! in_array( $p_user, $t_users ) ) {
			$t_users[] = $p_user;
		}
		$t_users[] = ALL_USERS;

		# prepare the projects list
		$t_projects = array( ALL_PROJECTS );
		if ( ( null === $p_project ) && ( auth_is_user_authenticated() ) ) {
			$t_selected_project = helper_get_current_project();
			if ( ALL_PROJECTS <> $t_selected_project ) {
				$t_projects[] = $t_selected_project;
			}
		} else if ( ! in_array( $p_project, $t_projects ) ) {
			$t_projects[] = $p_project;
		}
				
		$t_found = false;
		if ( isset( $g_cache_config[$p_option] ) ) {
    		reset( $t_users );
    		while ( ( list( , $t_user ) = each( $t_users ) ) && ! $t_found ) {
    			reset( $t_projects );
    			while ( ( list( , $t_project ) = each( $t_projects ) ) && ! $t_found ) {
    				if ( isset( $g_cache_config[$p_option][$t_user][$t_project] ) ) {
    					$t_access = $g_cache_config_access[$p_option][$t_user][$t_project];
    					$t_found = true;
    				}
    			}
    		}
		}

		return $t_found ? $t_access : ADMINISTRATOR;
	}

	# ------------------
	# Returns true if the specified config option exists (ie. a
	#  value or default can be found), false otherwise
	function config_is_set( $p_option, $p_user = null, $p_project = null ) {
		global $g_cache_config, $g_cache_filled;
		
		if ( ! $g_cache_filled ) {
			$t = config_get( $p_option, -1, $p_user, $p_project );
		}
		
		# prepare the user's list
		$t_users = array( ALL_USERS );
		if ( ( null === $p_user ) && ( auth_is_user_authenticated() ) ) {
			$t_users[] = auth_get_current_user_id();
		} else if ( ! in_array( $p_user, $t_users ) ) {
			$t_users[] = $p_user;
		}
		$t_users[] = ALL_USERS;

		# prepare the projects list
		$t_projects = array( ALL_PROJECTS );
		if ( ( null === $p_project ) && ( auth_is_user_authenticated() ) ) {
			$t_selected_project = helper_get_current_project();
			if ( ALL_PROJECTS <> $t_selected_project ) {
				$t_projects[] = $t_selected_project;
			}
		} else if ( ! in_array( $p_project, $t_projects ) ) {
			$t_projects[] = $p_project;
		}
				
		$t_found = false;
		reset( $t_users );
		while ( ( list( , $t_user ) = each( $t_users ) ) && ! $t_found ) {
			reset( $t_projects );
			while ( ( list( , $t_project ) = each( $t_projects ) ) && ! $t_found ) {
				if ( isset( $g_cache_config[$p_option][$t_user][$t_project] ) ) {
					$t_found = true;
				}
			}
		}
				
		if ( $t_found ) {
			return true;
		}

		return isset( $GLOBALS['g_' . $p_option] ) ;
	}

	# ------------------
	# Sets the value of the given config option to the given value
	#  If the config option does not exist, an ERROR is triggered
	function config_set( $p_option, $p_value, $p_user = NO_USER, $p_project = ALL_PROJECTS, $p_access = ADMINISTRATOR ) {
		if ( is_array( $p_value ) || is_object( $p_value ) ) {
			$t_type = CONFIG_TYPE_COMPLEX;
			$c_value = db_prepare_string( serialize( $p_value ) );
		} else if ( is_int( $p_value ) || is_numeric( $p_value ) ) {
			$t_type = CONFIG_TYPE_INT;
			$c_value = db_prepare_int( $p_value );
		} else {
			$t_type = CONFIG_TYPE_STRING;
			$c_value = db_prepare_string( $p_value );
		}
		$c_option = db_prepare_string( $p_option );
		$c_user = db_prepare_int( $p_user );
		$c_project = db_prepare_int( $p_project );
		$c_access = db_prepare_int( $p_access );

		$t_config_table = config_get_global( 'mantis_config_table' );
		$query = "SELECT * from $t_config_table
			WHERE config_id = '$c_option' AND
				project_id = $c_project AND
				user_id = $c_user";
		$result = db_query( $query );

		if ( 0 < db_num_rows( $result ) ) {
			$t_set_query = "UPDATE $t_config_table
				SET value='$c_value', type=$t_type, access_reqd=$c_access
				WHERE config_id = '$c_option' AND
					project_id = $c_project AND
					user_id = $c_user";
		} else {
			$t_set_query = "INSERT INTO $t_config_table
				( value, type, access_reqd, config_id, project_id, user_id )
				VALUES 
				('$c_value', $t_type, $c_access, '$c_option', $c_project, $c_user )";
		}

		$result = db_query( $t_set_query );
		
		config_set_cache( $p_option, $p_value, $p_user, $p_project, $p_access );

		return true;
	}
	
	# ------------------
	# Sets the value of the given config option to the given value
	#  If the config option does not exist, an ERROR is triggered
	function config_set_cache( $p_option, $p_value, $p_user = NO_USER, $p_project = ALL_PROJECTS, $p_access = ADMINISTRATOR ) {
		global $g_cache_config, $g_cache_config_access;
		$g_cache_config[$p_option][$p_user][$p_project] = $p_value;
		$g_cache_config_access[$p_option][$p_user][$p_project] = $p_access;

		return true;
	}
	
	# ------------------
	# delete the config entry
	function config_delete( $p_option, $p_user = ALL_USERS, $p_project = ALL_PROJECTS ) {
	global $g_cache_config, $g_cache_config_access;
		# bypass table lookup for certain options
		$t_match_pattern = '/' . implode( '|', config_get_global( 'global_settings' ) ) . '/';
		$t_bypass_lookup = ( 0 < preg_match( $t_match_pattern, $p_option ) );
		# @@ debug @@ if ($t_bypass_lookup) { echo "bp=$p_option match=$t_match_pattern <br />"; }
		# @@ debug @@ if ( ! db_is_connected() ) { echo "no db"; }

		if ( ( ! $t_bypass_lookup ) && ( TRUE === db_is_connected() )
				&& ( db_table_exists( config_get_global( 'mantis_config_table' ) ) ) ) {
			$t_config_table = config_get_global( 'mantis_config_table' );
			# @@ debug @@ echo "lu table=" . ( db_table_exists( $t_config_table ) ? "yes" : "no" );
			# @@ debug @@ error_print_stack_trace();

			$c_option = db_prepare_string( $p_option );
			$c_user = db_prepare_int( $p_user );
			$c_project = db_prepare_int( $p_project );
			$query = "DELETE FROM $t_config_table
				WHERE config_id = '$c_option' AND
					project_id=$c_project AND
					user_id=$c_user";

			$result = @db_query( $query);
		}
		config_flush_cache( $p_option, $p_user, $p_project );
	}		
		
	# ------------------
	# delete the config entry
	function config_delete_project( $p_project = ALL_PROJECTS ) {
		global $g_cache_config, $g_cache_config_access;
		$t_config_table = config_get_global( 'mantis_config_table' );
		$c_project = db_prepare_int( $p_project );
		$query = "DELETE FROM $t_config_table
				WHERE project_id=$c_project";

		$result = @db_query( $query);
		
		# flush cache here in case some of the deleted configs are in use.
		config_flush_cache(); 
	}		
		
	# ------------------
	# delete the config entry from the cache
	# @@@ to be used sparingly
	function config_flush_cache( $p_option='', $p_user = ALL_USERS, $p_project = ALL_PROJECTS ) {
		global $g_cache_config, $g_cache_config_access;
	
		if ( '' !== $p_option ) {
			unset( $GLOBALS['g_cache_config'][$p_option][$p_user][$p_project] );
			unset( $GLOBALS['g_cache_config_access'][$p_option][$p_user][$p_project] );
		} else {
			unset( $GLOBALS['g_cache_config'] );
			unset( $GLOBALS['g_cache_config_access'] );
			$g_cache_filled = false;
		}
			
	}
		

	# ------------------
	# Checks if an obsolete configuration variable is still in use.  If so, an error
	# will be generated and the script will exit.  This is called from admin_check.php.
	function config_obsolete( $p_var, $p_replace ) {
		# @@@ we could trigger a WARNING here, once we have errors that can
		#     have extra data plugged into them (we need to give the old and
		#     new config option names in the warning text)

		if ( config_is_set( $p_var ) ) {
			PRINT '<p><b>Warning:</b> The configuration option <tt>$g_' . $p_var . '</tt> is now obsolete';
			if ( is_array( $p_replace ) ) {
				PRINT ', please see the following options: <ul>';
				foreach ( $p_replace as $t_option ) {
					PRINT '<li>$g_' . $t_option . '</li>';
				}
				PRINT '</ul>';
			} else if ( !is_blank( $p_replace ) ) {
				PRINT ', please use <tt>$g_' . $p_replace . '</tt> instead.';
			}
			PRINT '</p>';
		}
	}

	# ------------------
	# check for recursion in defining config variables
	# If there is a %text% in the returned value, re-evaluate the "text" part and replace
	#  the string
	function config_eval( $p_value ) {
		$t_value = $p_value;
		if ( is_string( $t_value ) && !is_numeric( $t_value ) ) {
			if ( 0 < preg_match_all( '/%(.*)%/U', $t_value, $t_matches ) ) {
			    $t_count = count( $t_matches[0] );
				for ($i=0; $i<$t_count; $i++) {
					# $t_matches[0][$i] is the matched string including the delimiters
					# $t_matches[1][$i] is the target parameter string
					$t_repl = config_get( $t_matches[1][$i] );
					$t_value = str_replace( $t_matches[0][$i], $t_repl, $t_value );
				}
			}
		}
		return $t_value;
	}
?>
