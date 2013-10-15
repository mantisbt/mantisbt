<?php
# MantisBT - A PHP based bugtracking system

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
 * Each entry below defines the schema. The upgrade array consists of two elements
 * The first is the function to generate SQL statements e.g., CreateTableSQL, DropTableSQL, 
 * ChangeTableSQL, RenameTableSQL, RenameColumnSQL, DropTableSQL, ChangeTableSQL, RenameTableSQL, 
 * RenameColumnSQL, AlterColumnSQL, DropColumnSQL
 *
 * A local function "InsertData" has been provided to add data to the db
 * The second parameter is an array of the parameters to be passed to the function.
 * 
 * An update identifier is inferred from the ordering of this table. ONLY ADD NEW CHANGES TO THE
 * END OF THE TABLE!!!
 */

if ( !function_exists( 'db_null_date' ) ) {
	/**
	 * Legacy null date function for installer backwards compatibility
	 */
	function db_null_date() {
		return 0;
	}
}


function installer_db_now() {
        global $g_db;

       return $g_db->BindTimeStamp( time() );
}

/* 00 */ $upgrade[] = array('CreateTableSQL',array(db_get_table( 'config' ),"
			  config_id C(64) NOTNULL PRIMARY,
			  project_id I DEFAULT '0' PRIMARY,
			  user_id I DEFAULT '0' PRIMARY,
			  access_reqd I DEFAULT '0',
			  type I DEFAULT '90',
			  value XL NOTNULL",
array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
/* 01 */ $upgrade[] = array('CreateIndexSQL',array('idx_config',db_get_table( 'config' ),'config_id'));
/* 02 */ $upgrade[] = array('CreateTableSQL',array(db_get_table('bug_file'),"
  id			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  bug_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  title 		C(250) NOTNULL DEFAULT \" '' \",
  description 		C(250) NOTNULL DEFAULT \" '' \",
  diskfile 		C(250) NOTNULL DEFAULT \" '' \",
  filename 		C(250) NOTNULL DEFAULT \" '' \",
  folder 		C(250) NOTNULL DEFAULT \" '' \",
  filesize 		 I NOTNULL DEFAULT '0',
  file_type 		C(250) NOTNULL DEFAULT \" '' \",
  date_added 		T NOTNULL DEFAULT '" . db_null_date() . "',
  content 		B NOTNULL
  ",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
/* 03 */ $upgrade[] = array('CreateIndexSQL',array('idx_bug_file_bug_id',db_get_table('bug_file'),'bug_id'));
/* 04 */ $upgrade[] = array('CreateTableSQL',array(db_get_table('bug_history'),"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  user_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  bug_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  date_modified 	T NOTNULL DEFAULT '" . db_null_date() . "',
  field_name 		C(32) NOTNULL DEFAULT \" '' \",
  old_value 		C(128) NOTNULL DEFAULT \" '' \",
  new_value 		C(128) NOTNULL DEFAULT \" '' \",
  type 			I2 NOTNULL DEFAULT '0'
  ",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
/* 05 */ $upgrade[] = array('CreateIndexSQL',array('idx_bug_history_bug_id',db_get_table('bug_history'),'bug_id'));
/* 06 */ $upgrade[] = array('CreateIndexSQL',array('idx_history_user_id',db_get_table('bug_history'),'user_id'));
/* 07 */ $upgrade[] = array('CreateTableSQL',array(db_get_table('bug_monitor'),"
  user_id 		 I  UNSIGNED NOTNULL PRIMARY DEFAULT '0',
  bug_id 		 I  UNSIGNED NOTNULL PRIMARY DEFAULT '0'
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
/* 08 */ $upgrade[] = array('CreateTableSQL',array(db_get_table('bug_relationship'),"
  id 			 I  UNSIGNED NOTNULL AUTOINCREMENT PRIMARY,
  source_bug_id		 I  UNSIGNED NOTNULL DEFAULT '0',
  destination_bug_id 	 I  UNSIGNED NOTNULL DEFAULT '0',
  relationship_type 	I2 NOTNULL DEFAULT '0'
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
/* 09 */ $upgrade[] = array('CreateIndexSQL',array('idx_relationship_source',db_get_table('bug_relationship'),'source_bug_id'));
/* 10 */ $upgrade[] = array('CreateIndexSQL',array('idx_relationship_destination',db_get_table('bug_relationship'),'destination_bug_id'));
/* 11 */ $upgrade[] = array('CreateTableSQL',array(db_get_table('bug'),"
  id 			 I  UNSIGNED PRIMARY NOTNULL AUTOINCREMENT,
  project_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  reporter_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  handler_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  duplicate_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  priority 		I2 NOTNULL DEFAULT '30',
  severity 		I2 NOTNULL DEFAULT '50',
  reproducibility 	I2 NOTNULL DEFAULT '10',
  status 		I2 NOTNULL DEFAULT '10',
  resolution 		I2 NOTNULL DEFAULT '10',
  projection 		I2 NOTNULL DEFAULT '10',
  category 		C(64) NOTNULL DEFAULT \" '' \",
  date_submitted 	T NOTNULL DEFAULT '" . db_null_date() . "',
  last_updated 		T NOTNULL DEFAULT '" . db_null_date() . "',
  eta 			I2 NOTNULL DEFAULT '10',
  bug_text_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  os 			C(32) NOTNULL DEFAULT \" '' \",
  os_build 		C(32) NOTNULL DEFAULT \" '' \",
  platform 		C(32) NOTNULL DEFAULT \" '' \",
  version 		C(64) NOTNULL DEFAULT \" '' \",
  fixed_in_version 	C(64) NOTNULL DEFAULT \" '' \",
  build 		C(32) NOTNULL DEFAULT \" '' \",
  profile_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  view_state 		I2 NOTNULL DEFAULT '10',
  summary 		C(128) NOTNULL DEFAULT \" '' \",
  sponsorship_total 	 I  NOTNULL DEFAULT '0',
  sticky		L  NOTNULL DEFAULT  \"'0'\"
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
/* 12 */ $upgrade[] = array('CreateIndexSQL',array('idx_bug_sponsorship_total',db_get_table('bug'),'sponsorship_total'));
/* 13 */ $upgrade[] = array('CreateIndexSQL',array('idx_bug_fixed_in_version',db_get_table('bug'),'fixed_in_version'));
/* 14 */ $upgrade[] = array('CreateIndexSQL',array('idx_bug_status',db_get_table('bug'),'status'));
/* 15 */ $upgrade[] = array('CreateIndexSQL',array('idx_project',db_get_table('bug'),'project_id'));
/* 16 */ $upgrade[] = array('CreateTableSQL',array(db_get_table('bug_text'),"
  id 			 I  PRIMARY UNSIGNED NOTNULL AUTOINCREMENT,
  description 		XL NOTNULL,
  steps_to_reproduce 	XL NOTNULL,
  additional_information XL NOTNULL
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
/* 17 */ $upgrade[] = array('CreateTableSQL',array(db_get_table('bugnote'),"
  id 			 I  UNSIGNED PRIMARY NOTNULL AUTOINCREMENT,
  bug_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  reporter_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  bugnote_text_id 	 I  UNSIGNED NOTNULL DEFAULT '0',
  view_state 		I2 NOTNULL DEFAULT '10',
  date_submitted 	T NOTNULL DEFAULT '" . db_null_date() . "',
  last_modified 	T NOTNULL DEFAULT '" . db_null_date() . "',
  note_type 		 I  DEFAULT '0',
  note_attr 		C(250) DEFAULT \" '' \"
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
/* 18 */ $upgrade[] = array('CreateIndexSQL',array('idx_bug',db_get_table('bugnote'),'bug_id'));
/* 19 */ $upgrade[] = array('CreateIndexSQL',array('idx_last_mod',db_get_table('bugnote'),'last_modified'));
/* 20 */
/* 21 */ $upgrade[] = array('CreateTableSQL',array(db_get_table('bugnote_text'),"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  note 			XL NOTNULL
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
/* 22 */ $upgrade[] = array('CreateTableSQL',array(db_get_table('custom_field_project'),"
  field_id 		 I  NOTNULL PRIMARY DEFAULT '0',
  project_id 		 I  UNSIGNED PRIMARY NOTNULL DEFAULT '0',
  sequence 		I2 NOTNULL DEFAULT '0'
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
/* 23 */ $upgrade[] = array('CreateTableSQL',array(db_get_table('custom_field_string'),"
  field_id 		 I  NOTNULL PRIMARY DEFAULT '0',
  bug_id 		 I  NOTNULL PRIMARY DEFAULT '0',
  value 		C(255) NOTNULL DEFAULT \" '' \"
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
/* 24 */ $upgrade[] = array('CreateIndexSQL',array('idx_custom_field_bug',db_get_table('custom_field_string'),'bug_id'));
/* 25 */ $upgrade[] = array('CreateTableSQL',array(db_get_table('custom_field'),"
  id 			 I  NOTNULL PRIMARY AUTOINCREMENT,
  name 			C(64) NOTNULL DEFAULT \" '' \",
  type 			I2 NOTNULL DEFAULT '0',
  possible_values 	C(255) NOTNULL DEFAULT \" '' \",
  default_value 	C(255) NOTNULL DEFAULT \" '' \",
  valid_regexp 		C(255) NOTNULL DEFAULT \" '' \",
  access_level_r 	I2 NOTNULL DEFAULT '0',
  access_level_rw 	I2 NOTNULL DEFAULT '0',
  length_min 		 I  NOTNULL DEFAULT '0',
  length_max 		 I  NOTNULL DEFAULT '0',
  advanced 		L NOTNULL DEFAULT \" '0' \",
  require_report 	L NOTNULL DEFAULT \" '0' \",
  require_update 	L NOTNULL DEFAULT \" '0' \",
  display_report 	L NOTNULL DEFAULT \" '0' \",
  display_update 	L NOTNULL DEFAULT \" '1' \",
  require_resolved 	L NOTNULL DEFAULT \" '0' \",
  display_resolved 	L NOTNULL DEFAULT \" '0' \",
  display_closed 	L NOTNULL DEFAULT \" '0' \",
  require_closed 	L NOTNULL DEFAULT \" '0' \"
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
/* 26 */ $upgrade[] = array('CreateIndexSQL',array('idx_custom_field_name',db_get_table('custom_field'),'name'));
/* 27 */ $upgrade[] = array('CreateTableSQL',array(db_get_table('filters'),"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  user_id 		 I  NOTNULL DEFAULT '0',
  project_id 		 I  NOTNULL DEFAULT '0',
  is_public 		L DEFAULT NULL,
  name 			C(64) NOTNULL DEFAULT \" '' \",
  filter_string 	XL NOTNULL
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
/* 28 */ $upgrade[] = array('CreateTableSQL',array(db_get_table('news'),"
  id 			 I  UNSIGNED PRIMARY NOTNULL AUTOINCREMENT,
  project_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  poster_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  date_posted 		T NOTNULL DEFAULT '" . db_null_date() . "',
  last_modified 	T NOTNULL DEFAULT '" . db_null_date() . "',
  view_state 		I2 NOTNULL DEFAULT '10',
  announcement 		L NOTNULL DEFAULT \" '0' \",
  headline 		C(64) NOTNULL DEFAULT \" '' \",
  body 			XL NOTNULL
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
/* 29 */ $upgrade[] = array('CreateTableSQL',array(db_get_table('project_category'),"
  project_id 		 I  UNSIGNED NOTNULL PRIMARY DEFAULT '0',
  category 		C(64) NOTNULL PRIMARY DEFAULT \" '' \",
  user_id 		 I  UNSIGNED NOTNULL DEFAULT '0'
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
/* 30 */ $upgrade[] = array('CreateTableSQL',array(db_get_table('project_file'),"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  project_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  title 		C(250) NOTNULL DEFAULT \" '' \",
  description 		C(250) NOTNULL DEFAULT \" '' \",
  diskfile 		C(250) NOTNULL DEFAULT \" '' \",
  filename 		C(250) NOTNULL DEFAULT \" '' \",
  folder 		C(250) NOTNULL DEFAULT \" '' \",
  filesize 		 I NOTNULL DEFAULT '0',
  file_type 		C(250) NOTNULL DEFAULT \" '' \",
  date_added 		T NOTNULL DEFAULT '" . db_null_date() . "',
  content 		B NOTNULL
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));

/* 31 */ $upgrade[] = array('CreateTableSQL',array(db_get_table('project_hierarchy'),"
			  child_id I UNSIGNED NOTNULL,
			  parent_id I UNSIGNED NOTNULL",
array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
/* 32 */ $upgrade[] = array('CreateTableSQL',array(db_get_table('project'),"
  id 			 I  UNSIGNED PRIMARY NOTNULL AUTOINCREMENT,
  name 			C(128) NOTNULL DEFAULT \" '' \",
  status 		I2 NOTNULL DEFAULT '10',
  enabled 		L NOTNULL DEFAULT \" '1' \",
  view_state 		I2 NOTNULL DEFAULT '10',
  access_min 		I2 NOTNULL DEFAULT '10',
  file_path 		C(250) NOTNULL DEFAULT \" '' \",
  description 		XL NOTNULL
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
/* 33 */ $upgrade[] = array('CreateIndexSQL',array('idx_project_id',db_get_table('project'),'id'));
/* 34 */ $upgrade[] = array('CreateIndexSQL',array('idx_project_name',db_get_table('project'),'name',array('UNIQUE')));
/* 35 */ $upgrade[] = array('CreateIndexSQL',array('idx_project_view',db_get_table('project'),'view_state'));
/* 36 */ $upgrade[] = array('CreateTableSQL',array(db_get_table('project_user_list'),"
  project_id 		 I  UNSIGNED PRIMARY NOTNULL DEFAULT '0',
  user_id 		 I  UNSIGNED PRIMARY NOTNULL DEFAULT '0',
  access_level 		I2 NOTNULL DEFAULT '10'
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
/* 37 */ $upgrade[] = array( 'CreateIndexSQL',array('idx_project_user',db_get_table('project_user_list'),'user_id'));
/* 38 */ $upgrade[] = array('CreateTableSQL',array(db_get_table('project_version'),"
  id 			 I  NOTNULL PRIMARY AUTOINCREMENT,
  project_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  version 		C(64) NOTNULL DEFAULT \" '' \",
  date_order 		T NOTNULL DEFAULT '" . db_null_date() . "',
  description 		XL NOTNULL,
  released 		L NOTNULL DEFAULT \" '1' \"
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
/* 39 */ $upgrade[] = array('CreateIndexSQL',array('idx_project_version',db_get_table('project_version'),'project_id,version',array('UNIQUE')));
/* 40 */ $upgrade[] = array('CreateTableSQL',array(db_get_table('sponsorship'),"
  id 			 I  NOTNULL PRIMARY AUTOINCREMENT,
  bug_id 		 I  NOTNULL DEFAULT '0',
  user_id 		 I  NOTNULL DEFAULT '0',
  amount 		 I  NOTNULL DEFAULT '0',
  logo 			C(128) NOTNULL DEFAULT \" '' \",
  url 			C(128) NOTNULL DEFAULT \" '' \",
  paid 			L NOTNULL DEFAULT \" '0' \",
  date_submitted 	T NOTNULL DEFAULT '" . db_null_date() . "',
  last_updated 		T NOTNULL DEFAULT '" . db_null_date() . "'
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
/* 41 */ $upgrade[] = array('CreateIndexSQL',array('idx_sponsorship_bug_id',db_get_table('sponsorship'),'bug_id'));
/* 42 */ $upgrade[] = array('CreateIndexSQL',array('idx_sponsorship_user_id',db_get_table('sponsorship'),'user_id'));
/* 43 */ $upgrade[] = array('CreateTableSQL',array(db_get_table('tokens'),"
			  id I NOTNULL PRIMARY AUTOINCREMENT,
			  owner I NOTNULL,
			  type I NOTNULL,
			  timestamp T NOTNULL,
			  expiry T,
			  value XL NOTNULL",
array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
/* 44 */ $upgrade[] = array('CreateTableSQL',array(db_get_table('user_pref'),"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  user_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  project_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  default_profile 	 I  UNSIGNED NOTNULL DEFAULT '0',
  default_project 	 I  UNSIGNED NOTNULL DEFAULT '0',
  advanced_report 	L NOTNULL DEFAULT \" '0' \",
  advanced_view 	L NOTNULL DEFAULT \" '0' \",
  advanced_update 	L NOTNULL DEFAULT \" '0' \",
  refresh_delay 	 I  NOTNULL DEFAULT '0',
  redirect_delay 	L NOTNULL DEFAULT \" '0' \",
  bugnote_order 	C(4) NOTNULL DEFAULT 'ASC',
  email_on_new 		L NOTNULL DEFAULT \" '0' \",
  email_on_assigned 	L NOTNULL DEFAULT \" '0' \",
  email_on_feedback 	L NOTNULL DEFAULT \" '0' \",
  email_on_resolved	L NOTNULL DEFAULT \" '0' \",
  email_on_closed 	L NOTNULL DEFAULT \" '0' \",
  email_on_reopened 	L NOTNULL DEFAULT \" '0' \",
  email_on_bugnote 	L NOTNULL DEFAULT \" '0' \",
  email_on_status 	L NOTNULL DEFAULT \" '0' \",
  email_on_priority 	L NOTNULL DEFAULT \" '0' \",
  email_on_priority_min_severity 	I2 NOTNULL DEFAULT '10',
  email_on_status_min_severity 	I2 NOTNULL DEFAULT '10',
  email_on_bugnote_min_severity 	I2 NOTNULL DEFAULT '10',
  email_on_reopened_min_severity 	I2 NOTNULL DEFAULT '10',
  email_on_closed_min_severity 	I2 NOTNULL DEFAULT '10',
  email_on_resolved_min_severity 	I2 NOTNULL DEFAULT '10',
  email_on_feedback_min_severity	I2 NOTNULL DEFAULT '10',
  email_on_assigned_min_severity 	I2 NOTNULL DEFAULT '10',
  email_on_new_min_severity 	I2 NOTNULL DEFAULT '10',
  email_bugnote_limit 	I2 NOTNULL DEFAULT '0',
  language 		C(32) NOTNULL DEFAULT 'english'
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
/* 45 */ $upgrade[] = array('CreateTableSQL',array(db_get_table('user_print_pref'),"
  user_id 		 I  UNSIGNED NOTNULL PRIMARY DEFAULT '0',
  print_pref 		C(27) NOTNULL DEFAULT \" '' \"
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
/* 46 */ $upgrade[] = array('CreateTableSQL',array(db_get_table('user_profile'),"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  user_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  platform 		C(32) NOTNULL DEFAULT \" '' \",
  os 			C(32) NOTNULL DEFAULT \" '' \",
  os_build 		C(32) NOTNULL DEFAULT \" '' \",
  description 		XL NOTNULL
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
/* 47 */ $upgrade[] = array('CreateTableSQL',array(db_get_table('user'),"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  username 		C(32) NOTNULL DEFAULT \" '' \",
  realname 		C(64) NOTNULL DEFAULT \" '' \",
  email 		C(64) NOTNULL DEFAULT \" '' \",
  password 		C(32) NOTNULL DEFAULT \" '' \",
  date_created 		T NOTNULL DEFAULT '" . db_null_date() . "',
  last_visit 		T NOTNULL DEFAULT '" . db_null_date() . "',
  enabled		L NOTNULL DEFAULT \" '1' \",
  protected 		L NOTNULL DEFAULT \" '0' \",
  access_level 		I2 NOTNULL DEFAULT '10',
  login_count 		 I  NOTNULL DEFAULT '0',
  lost_password_request_count 	I2 NOTNULL DEFAULT '0',
  failed_login_count 	I2 NOTNULL DEFAULT '0',
  cookie_string 	C(64) NOTNULL DEFAULT \" '' \"
",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
/* 48 */ $upgrade[] = array('CreateIndexSQL',array('idx_user_cookie_string',db_get_table('user'),'cookie_string',array('UNIQUE')));
/* 49 */ $upgrade[] = array('CreateIndexSQL',array('idx_user_username',db_get_table('user'),'username',array('UNIQUE')));
/* 50 */ $upgrade[] = array('CreateIndexSQL',array('idx_enable',db_get_table('user'),'enabled'));
/* 51 */ $upgrade[] = array('CreateIndexSQL',array('idx_access',db_get_table('user'),'access_level'));
/* 52 */ $upgrade[] = array('InsertData', array( db_get_table('user'),
    "(username, realname, email, password, date_created, last_visit, enabled, protected, access_level, login_count, lost_password_request_count, failed_login_count, cookie_string) VALUES
        ('administrator', '', 'root@localhost', '63a9f0ea7bb98050796b649e85481845', '" . installer_db_now() . "', '" . installer_db_now() . "', '1', '0', 90, 3, 0, 0, '" .
             md5( mt_rand( 0, mt_getrandmax() ) + mt_rand( 0, mt_getrandmax() ) ) . md5( time() ) . "')" ) );
/* 53 */ $upgrade[] = array('AlterColumnSQL', array( db_get_table( 'bug_history' ), "old_value C(255) NOTNULL" ) );
/* 54 */ $upgrade[] = array('AlterColumnSQL', array( db_get_table( 'bug_history' ), "new_value C(255) NOTNULL" ) );
/* 55 */ $upgrade[] = array('CreateTableSQL',array(db_get_table('email'),"
  email_id 		I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  email		 	C(64) NOTNULL DEFAULT \" '' \",
  subject		C(250) NOTNULL DEFAULT \" '' \",
  submitted 	T NOTNULL DEFAULT '" . db_null_date() . "',
  metadata 		XL NOTNULL,
  body 			XL NOTNULL
  ",array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
/* 56 */ $upgrade[] = array('CreateIndexSQL',array('idx_email_id',db_get_table('email'),'email_id'));
/* 57 */ $upgrade[] = array('AddColumnSQL',array(db_get_table('bug'), "target_version C(64) NOTNULL DEFAULT \" '' \""));
/* 58 */ $upgrade[] = array('AddColumnSQL',array(db_get_table('bugnote'), "time_tracking I UNSIGNED NOTNULL DEFAULT \" 0 \""));
/* 59 */ $upgrade[] = array('CreateIndexSQL',array('idx_diskfile',db_get_table('bug_file'),'diskfile'));
/* 60 */ $upgrade[] = array('AlterColumnSQL', array( db_get_table( 'user_print_pref' ), "print_pref C(64) NOTNULL" ) );
/* 61 */ $upgrade[] = array('AlterColumnSQL', array( db_get_table( 'bug_history' ), "field_name C(64) NOTNULL" ) );

# Release marker: 1.1.0a4

/* 62 */ $upgrade[] = array('CreateTableSQL', array( db_get_table( 'tag' ), "
	id				I		UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
	user_id			I		UNSIGNED NOTNULL DEFAULT '0',
	name			C(100)	NOTNULL PRIMARY DEFAULT \" '' \",
	description		XL		NOTNULL,
	date_created	T		NOTNULL DEFAULT '" . db_null_date() . "',
	date_updated	T		NOTNULL DEFAULT '" . db_null_date() . "'
	", array( 'mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS' ) ) );
/* 63 */ $upgrade[] = array('CreateTableSQL', array( db_get_table( 'bug_tag' ), "
	bug_id			I	UNSIGNED NOTNULL PRIMARY DEFAULT '0',
	tag_id			I	UNSIGNED NOTNULL PRIMARY DEFAULT '0',
	user_id			I	UNSIGNED NOTNULL DEFAULT '0',
	date_attached	T	NOTNULL DEFAULT '" . db_null_date() . "'
	", array( 'mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS' ) ) );

/* 64 */ $upgrade[] = array('CreateIndexSQL', array( 'idx_typeowner', db_get_table( 'tokens' ), 'type, owner' ) );

# Release marker: 1.2.0-SVN

/* 65 */ $upgrade[] = array('CreateTableSQL', array( db_get_table( 'plugin' ), "
	basename		C(40)	NOTNULL PRIMARY,
	enabled			L		NOTNULL DEFAULT \" '0' \"
	", array( 'mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS' ) ) );

/* 66 */ $upgrade[] = array('AlterColumnSQL', array( db_get_table( 'user_pref' ), "redirect_delay 	I NOTNULL DEFAULT 0" ) );

/* apparently mysql now has a STRICT mode, where setting a DEFAULT value on a blob/text is now an error, instead of being silently ignored */
if ( isset( $f_db_type ) && ( $f_db_type == 'mysql' || $f_db_type == 'mysqli' ) ) {
	/* 00 */ $upgrade[] = array('AlterColumnSQL', array( db_get_table( 'custom_field' ), "possible_values X NOTNULL" ) );
} else {
	/* 00 */ $upgrade[] = array('AlterColumnSQL', array( db_get_table( 'custom_field' ), "possible_values X NOTNULL DEFAULT \" '' \"" ) );
}

/* 67 */ $upgrade[] = array( 'CreateTableSQL', array( db_get_table( 'category' ), "
	id				I		UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
	project_id		I		UNSIGNED NOTNULL DEFAULT '0',
	user_id			I		UNSIGNED NOTNULL DEFAULT '0',
	name			C(128)	NOTNULL DEFAULT \" '' \",
	status			I		UNSIGNED NOTNULL DEFAULT '0'
	", array( 'mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS' ) ) );
/* 68 */ $upgrade[] = array( 'CreateIndexSQL', array( 'idx_category_project_name', db_get_table( 'category' ), 'project_id, name', array( 'UNIQUE' ) ) );
/* 69 */ $upgrade[] = array( 'InsertData', array( db_get_table( 'category' ), "
	( project_id, user_id, name, status ) VALUES
	( '0', '0', 'General', '0' ) " ) );
/* 70 */ $upgrade[] = array( 'AddColumnSQL', array( db_get_table( 'bug' ), "category_id I UNSIGNED NOTNULL DEFAULT '1'" ) );
/* 71 */ $upgrade[] = array( 'UpdateFunction', "category_migrate" );
/* 72 */ $upgrade[] = array( 'DropColumnSQL', array( db_get_table( 'bug' ), "category" ) );
/* 73 */ $upgrade[] = array( 'DropTableSQL', array( db_get_table( 'project_category' ) ) );
/* 74 */ $upgrade[] = array( 'AddColumnSQL', array( db_get_table( 'project' ), "category_id I UNSIGNED NOTNULL DEFAULT '1'" ) );
// remove unnecessary indexes
/* 75 */ $upgrade[] = array('CreateIndexSQL',array('idx_project_id',db_get_table('project'),'id', array('DROP')), array( 'db_index_exists', array( db_get_table('project'), 'idx_project_id')));
/* 76 */ $upgrade[] = array('CreateIndexSQL',array('idx_config',db_get_table( 'config' ),'config_id', array('DROP')), array( 'db_index_exists', array( db_get_table('config'), 'idx_config')));

/* 77 */ $upgrade[] = array( 'InsertData', array( db_get_table( 'plugin' ), "
	( basename, enabled ) VALUES
	( 'MantisCoreFormatting', '1' )" ) );

/* 78 */ $upgrade[] = array( 'AddColumnSQL', array( db_get_table( 'project' ), "inherit_global I UNSIGNED NOTNULL DEFAULT '0'" ) );
/* 79 */ $upgrade[] = array( 'AddColumnSQL', array( db_get_table( 'project_hierarchy' ), "inherit_parent I UNSIGNED NOTNULL DEFAULT '0'" ) );
/* 80 */ $upgrade[] = array( 'AddColumnSQL', array( db_get_table( 'plugin' ), "
	protected		L		NOTNULL DEFAULT \" '0' \",
	priority		I		UNSIGNED NOTNULL DEFAULT '3'
	" ) );
/* 81 */ $upgrade[] = array( 'AddColumnSQL', array( db_get_table( 'project_version' ), "
	obsolete		L		NOTNULL DEFAULT \" '0' \"" ) );
/* 82 */ $upgrade[] = array( 'AddColumnSQL', array( db_get_table( 'bug' ), "
    due_date        T       NOTNULL DEFAULT '" . db_null_date() . "' " ) );

/* 83 */ $upgrade[] = array( 'AddColumnSQL', array( db_get_table( 'custom_field' ), "
  filter_by 		L 		NOTNULL DEFAULT \" '1' \"" ) );
/* 84 */ $upgrade[] = array( 'CreateTableSQL', array( db_get_table( 'bug_revision' ), "
	id			I		UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
	bug_id		I		UNSIGNED NOTNULL,
	bugnote_id	I		UNSIGNED NOTNULL DEFAULT '0',
	user_id		I		UNSIGNED NOTNULL,
	timestamp	T		NOTNULL DEFAULT '" . db_null_date() . "',
	type		I		UNSIGNED NOTNULL,
	value		XL		NOTNULL
	", array( 'mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS' ) ) );
/* 85 */ $upgrade[] = array( 'CreateIndexSQL', array( 'idx_bug_rev_id_time', db_get_table( 'bug_revision' ), 'bug_id, timestamp' ) );
/* 86 */ $upgrade[] = array( 'CreateIndexSQL', array( 'idx_bug_rev_type', db_get_table( 'bug_revision' ), 'type' ) );

#date conversion

/* 87 */ $upgrade[] = array( 'AddColumnSQL', array( db_get_table( 'bug' ), "
	date_submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 88 */ $upgrade[] = array( 'AddColumnSQL', array( db_get_table( 'bug' ), "
	due_date_int        			I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 89 */ $upgrade[] = array( 'AddColumnSQL', array( db_get_table( 'bug' ), "
	last_updated_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 90 */ $upgrade[] = array( 'UpdateFunction', "date_migrate", array( db_get_table( 'bug' ), 'id', array( 'date_submitted', 'due_date', 'last_updated' ), array( 'date_submitted_int', 'due_date_int', 'last_updated_int' ) ) );

/* 91 */ $upgrade[] = array( 'DropColumnSQL', array( db_get_table( 'bug' ), "date_submitted" ) );
/* 92 */ $upgrade[] = array( 'RenameColumnSQL', array( db_get_table( 'bug' ), "date_submitted_int", "date_submitted", "date_submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 93 */ $upgrade[] = array( 'DropColumnSQL', array( db_get_table( 'bug' ), "due_date" ) );
/* 94 */ $upgrade[] = array( 'RenameColumnSQL', array( db_get_table( 'bug' ), "due_date_int", "due_date", "due_date_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 95 */ $upgrade[] = array( 'DropColumnSQL', array( db_get_table( 'bug' ), "last_updated" ) );
/* 96 */ $upgrade[] = array( 'RenameColumnSQL', array( db_get_table( 'bug' ), "last_updated_int", "last_updated", "last_updated_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

/* 97 */ $upgrade[] = array('CreateIndexSQL',array('idx_last_mod',db_get_table( 'bugnote' ),'last_modified', array('DROP')), array( 'db_index_exists', array( db_get_table('bugnote'), 'idx_last_mod')));

/* 98 */ $upgrade[] = array( 'AddColumnSQL', array( db_get_table( 'bugnote' ), "
	last_modified_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 99 */ $upgrade[] = array( 'AddColumnSQL', array( db_get_table( 'bugnote' ), "
	date_submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 100 */ $upgrade[] = array( 'UpdateFunction', "date_migrate", array( db_get_table( 'bugnote' ), 'id', array( 'last_modified', 'date_submitted' ), array( 'last_modified_int', 'date_submitted_int' ) ) );

/* 101 */ $upgrade[] = array( 'DropColumnSQL', array( db_get_table( 'bugnote' ), "last_modified" ) );
/* 102 */ $upgrade[] = array( 'RenameColumnSQL', array( db_get_table( 'bugnote' ), "last_modified_int", "last_modified", "last_modified_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 103 */ $upgrade[] = array('CreateIndexSQL',array('idx_last_mod',db_get_table('bugnote'),'last_modified'));
/* 104 */ $upgrade[] = array( 'DropColumnSQL', array( db_get_table( 'bugnote' ), "date_submitted" ) );
/* 105 */ $upgrade[] = array( 'RenameColumnSQL', array( db_get_table( 'bugnote' ), "date_submitted_int", "date_submitted", "date_submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );


/* 106 */ $upgrade[] = array( 'AddColumnSQL', array( db_get_table( 'bug_file' ), "
	date_added_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 107 */ $upgrade[] = array( 'UpdateFunction', "date_migrate", array( db_get_table( 'bug_file' ), 'id', 'date_added', 'date_added_int' ) );
/* 108 */ $upgrade[] = array( 'DropColumnSQL', array( db_get_table( 'bug_file' ), "date_added" ) );
/* 109 */ $upgrade[] = array( 'RenameColumnSQL', array( db_get_table( 'bug_file' ), "date_added_int", "date_added", "date_added_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

/* 110 */ $upgrade[] = array( 'AddColumnSQL', array( db_get_table( 'project_file' ), "
	date_added_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 111 */ $upgrade[] = array( 'UpdateFunction', "date_migrate", array( db_get_table( 'project_file' ), 'id', 'date_added', 'date_added_int' ) );
/* 112 */ $upgrade[] = array( 'DropColumnSQL', array( db_get_table( 'project_file' ), "date_added" ) );
/* 113 */ $upgrade[] = array( 'RenameColumnSQL', array( db_get_table( 'project_file' ), "date_added_int", "date_added", "date_added_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

/* 114 */ $upgrade[] = array( 'AddColumnSQL', array( db_get_table( 'bug_history' ), "
	date_modified_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 115 */ $upgrade[] = array( 'UpdateFunction', "date_migrate", array( db_get_table( 'bug_history' ), 'id', 'date_modified', 'date_modified_int' ) );
/* 116 */ $upgrade[] = array( 'DropColumnSQL', array( db_get_table( 'bug_history' ), "date_modified" ) );
/* 117 */ $upgrade[] = array( 'RenameColumnSQL', array( db_get_table( 'bug_history' ), "date_modified_int", "date_modified", "date_modified_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

/* 118 */ $upgrade[] = array( 'AddColumnSQL', array( db_get_table( 'user' ), "
	last_visit_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 119 */ $upgrade[] = array( 'AddColumnSQL', array( db_get_table( 'user' ), "
	date_created_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

/* 120 */ $upgrade[] = array( 'UpdateFunction', "date_migrate", array( db_get_table( 'user' ), 'id', array( 'last_visit', 'date_created' ), array( 'last_visit_int', 'date_created_int' ) ) );

/* 121 */ $upgrade[] = array( 'DropColumnSQL', array( db_get_table( 'user' ), "date_created" ) );
/* 122 */ $upgrade[] = array( 'RenameColumnSQL', array( db_get_table( 'user' ), "date_created_int", "date_created", "date_created_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 123 */ $upgrade[] = array( 'DropColumnSQL', array( db_get_table( 'user' ), "last_visit" ) );
/* 124 */ $upgrade[] = array( 'RenameColumnSQL', array( db_get_table( 'user' ), "last_visit_int", "last_visit", "last_visit_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

/* 125 */ $upgrade[] = array( 'AddColumnSQL', array( db_get_table( 'email' ), "
	submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 126 */ $upgrade[] = array( 'UpdateFunction', "date_migrate", array( db_get_table( 'email' ), 'email_id', 'submitted', 'submitted_int' ) );
/* 127 */ $upgrade[] = array( 'DropColumnSQL', array( db_get_table( 'email' ), "submitted" ) );
/* 128 */ $upgrade[] = array( 'RenameColumnSQL', array( db_get_table( 'email' ), "submitted_int", "submitted", "submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

/* 129 */ $upgrade[] = array( 'AddColumnSQL', array( db_get_table( 'tag' ), "
	date_created_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 130 */ $upgrade[] = array( 'AddColumnSQL', array( db_get_table( 'tag' ), "
	date_updated_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

/* 131 */ $upgrade[] = array( 'UpdateFunction', "date_migrate", array( db_get_table( 'tag' ), 'id', array( 'date_created', 'date_updated' ), array( 'date_created_int', 'date_updated_int' ) ) );

/* 132 */ $upgrade[] = array( 'DropColumnSQL', array( db_get_table( 'tag' ), "date_created" ) );
/* 133 */ $upgrade[] = array( 'RenameColumnSQL', array( db_get_table( 'tag' ), "date_created_int", "date_created", "date_created_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 134 */ $upgrade[] = array( 'DropColumnSQL', array( db_get_table( 'tag' ), "date_updated" ) );
/* 135 */ $upgrade[] = array( 'RenameColumnSQL', array( db_get_table( 'tag' ), "date_updated_int", "date_updated", "date_updated_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

/* 136 */ $upgrade[] = array( 'AddColumnSQL', array( db_get_table( 'bug_tag' ), "
	date_attached_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 137 */ $upgrade[] = array( 'UpdateFunction', "date_migrate", array( db_get_table( 'bug_tag' ), 'bug_id', 'date_attached', 'date_attached_int' ) );
/* 138 */ $upgrade[] = array( 'DropColumnSQL', array( db_get_table( 'bug_tag' ), "date_attached" ) );
/* 139 */ $upgrade[] = array( 'RenameColumnSQL', array( db_get_table( 'bug_tag' ), "date_attached_int", "date_attached", "date_attached_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

/* 140 */ $upgrade[] = array( 'AddColumnSQL', array( db_get_table( 'tokens' ), "
	timestamp_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 141 */ $upgrade[] = array( 'AddColumnSQL', array( db_get_table( 'tokens' ), "
	expiry_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

/* 142 */ $upgrade[] = array( 'UpdateFunction', "date_migrate", array( db_get_table( 'tokens' ), 'id', array( 'timestamp', 'expiry' ), array( 'timestamp_int', 'expiry_int' ) ) );

/* 143 */ $upgrade[] = array( 'DropColumnSQL', array( db_get_table( 'tokens' ), "timestamp" ) );
/* 144 */ $upgrade[] = array( 'RenameColumnSQL', array( db_get_table( 'tokens' ), "timestamp_int", "timestamp", "timestamp_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 145 */ $upgrade[] = array( 'DropColumnSQL', array( db_get_table( 'tokens' ), "expiry" ) );
/* 146 */ $upgrade[] = array( 'RenameColumnSQL', array( db_get_table( 'tokens' ), "expiry_int", "expiry", "expiry_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

/* 147 */ $upgrade[] = array( 'AddColumnSQL', array( db_get_table( 'news' ), "
	last_modified_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 148 */ $upgrade[] = array( 'AddColumnSQL', array( db_get_table( 'news' ), "
	date_posted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 149 */ $upgrade[] = array( 'UpdateFunction', "date_migrate", array( db_get_table( 'news' ), 'id', array( 'date_posted', 'last_modified' ), array( 'date_posted_int', 'last_modified_int' ) ) );
/* 150 */ $upgrade[] = array( 'DropColumnSQL', array( db_get_table( 'news' ), "last_modified" ) );
/* 151 */ $upgrade[] = array( 'RenameColumnSQL', array( db_get_table( 'news' ), "last_modified_int", "last_modified", "last_modified_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 152 */ $upgrade[] = array( 'DropColumnSQL', array( db_get_table( 'news' ), "date_posted" ) );
/* 153 */ $upgrade[] = array( 'RenameColumnSQL', array( db_get_table( 'news' ), "date_posted_int", "date_posted", "date_posted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

/* 154 */ $upgrade[] = array('CreateIndexSQL',array('idx_bug_rev_id_time',db_get_table( 'bug_revision' ),'bug_id, timestamp', array('DROP')), array( 'db_index_exists', array( db_get_table('bug_revision'), 'idx_bug_rev_id_time')));
/* 155 */ $upgrade[] = array( 'AddColumnSQL', array( db_get_table( 'bug_revision' ), "
	timestamp_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 156 */ $upgrade[] = array( 'UpdateFunction', "date_migrate", array( db_get_table( 'bug_revision' ), 'id', 'timestamp', 'timestamp_int' ) );
/* 157 */ $upgrade[] = array( 'DropColumnSQL', array( db_get_table( 'bug_revision' ), "timestamp" ) );
/* 158 */ $upgrade[] = array( 'RenameColumnSQL', array( db_get_table( 'bug_revision' ), "timestamp_int", "timestamp", "timestamp_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 159 */ $upgrade[] = array( 'CreateIndexSQL', array( 'idx_bug_rev_id_time', db_get_table( 'bug_revision' ), 'bug_id, timestamp' ) );

/* 160 */ $upgrade[] = array( 'AddColumnSQL', array( db_get_table( 'user_pref' ), "
	 timezone C(32) NOTNULL DEFAULT '' " ) );

/* 161 */ $upgrade[] = array( 'AddColumnSQL', array( db_get_table( 'project_version' ), "
	date_order_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 162 */ $upgrade[] = array( 'UpdateFunction', "date_migrate", array( db_get_table( 'project_version' ), 'id', 'date_order', 'date_order_int' ) );
/* 163 */ $upgrade[] = array( 'DropColumnSQL', array( db_get_table( 'project_version' ), "date_order" ) );
/* 164 */ $upgrade[] = array( 'RenameColumnSQL', array( db_get_table( 'project_version' ), "date_order_int", "date_order", "date_order_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

/* 165 */ $upgrade[] = array( 'AddColumnSQL', array( db_get_table( 'sponsorship' ), "
	date_submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );
/* 166 */ $upgrade[] = array( 'AddColumnSQL', array( db_get_table( 'sponsorship' ), "
	last_updated_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

/* 167 */ $upgrade[] = array( 'UpdateFunction', "date_migrate", array( db_get_table( 'sponsorship' ), 'id', array( 'date_submitted', 'last_updated' ), array( 'date_submitted_int', 'last_updated_int' ) ) );

/* 168 */ $upgrade[] = array( 'DropColumnSQL', array( db_get_table( 'sponsorship' ), "last_updated" ) );
/* 169 */ $upgrade[] = array( 'RenameColumnSQL', array( db_get_table( 'sponsorship' ), "last_updated_int", "last_updated", "last_updated_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

/* 170 */ $upgrade[] = array( 'DropColumnSQL', array( db_get_table( 'sponsorship' ), "date_submitted" ) );
/* 171 */ $upgrade[] = array( 'RenameColumnSQL', array( db_get_table( 'sponsorship' ), "date_submitted_int", "date_submitted", "date_submitted_int		I  UNSIGNED     NOTNULL DEFAULT '1' " ) );

/* 172 */ $upgrade[] = array( 'AddColumnSQL', array( db_get_table( 'project_file' ), "
	user_id			I		UNSIGNED NOTNULL DEFAULT '0' " ) );
/* 173 */ $upgrade[] = array( 'AddColumnSQL', array( db_get_table( 'bug_file' ), "
	user_id		I  			UNSIGNED NOTNULL DEFAULT '0' " ) );
/* 174 */ $upgrade[] = array( 'DropColumnSQL', array( db_get_table( 'custom_field'), "advanced" ) );
/* 175 */ $upgrade[] = array( 'DropColumnSQL', array( db_get_table( 'user_pref'), "advanced_report" ) );
/* 176 */ $upgrade[] = array( 'DropColumnSQL', array( db_get_table( 'user_pref'), "advanced_view" ) );
/* 177 */ $upgrade[] = array( 'DropColumnSQL', array( db_get_table( 'user_pref'), "advanced_update" ) );
/* 178 */ $upgrade[] = array( 'CreateIndexSQL', array( 'idx_project_hierarchy_child_id', db_get_table( 'project_hierarchy' ), 'child_id' ) );
/* 179 */ $upgrade[] = array( 'CreateIndexSQL', array( 'idx_project_hierarchy_parent_id', db_get_table( 'project_hierarchy' ), 'parent_id' ) );

/* 180 */ $upgrade[] = array( 'CreateIndexSQL', array( 'idx_tag_name', db_get_table( 'tag' ), 'name' ) );
/* 181 */ $upgrade[] = array( 'CreateIndexSQL', array( 'idx_bug_tag_tag_id', db_get_table( 'bug_tag' ), 'tag_id' ) );
/* 182 */ $upgrade[] = array( 'CreateIndexSQL', array( 'idx_email_id', db_get_table( 'email' ), 'email_id', array( 'DROP' ) ), array( 'db_index_exists', array( db_get_table( 'email' ), 'idx_email_id') ) );
/* 183 */ $upgrade[] = array( 'UpdateFunction', 'correct_multiselect_custom_fields_db_format' );
/* 184 */ $upgrade[] = array( 'UpdateFunction', "stored_filter_migrate" );
/* 185 */ $upgrade[] = array( 'AddColumnSQL', array( db_get_table( 'custom_field_string' ), "
	text		XL  			NULL DEFAULT NULL " ) );
/* 186 */ $upgrade[] = array( 'UpdateFunction', 'update_history_long_custom_fields' );
/* 187 */ $upgrade[] = array( 'CreateIndexSQL', array( 'idx_bug_id', db_get_table( 'bug_monitor' ), 'bug_id' ) );
/* 188 */ $upgrade[] = array( 'AlterColumnSQL', array( db_get_table( 'project' ), "inherit_global L NOTNULL DEFAULT '0'" ) );
/* 189 */ $upgrade[] = array( 'AlterColumnSQL', array( db_get_table( 'project_hierarchy' ), "inherit_parent L NOTNULL DEFAULT '0'" ) );
