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
	# $Id: schema.php,v 1.23.2.1 2007-10-13 22:34:56 giallu Exp $
	# --------------------------------------------------------
	
	# Each entry below defines the schema. The upgrade array consists of
	#  two elements
	# The first is the function to generate SQL statements (see adodb schema doc for more details)
	#  e.g., CreateTableSQL, DropTableSQL, ChangeTableSQL, RenameTableSQL, RenameColumnSQL,
	#  DropTableSQL, ChangeTableSQL, RenameTableSQL, RenameColumnSQL, AlterColumnSQL, DropColumnSQL
	#  A local function "InsertData" has been provided to add data to the db
	# The second parameter is an array of the parameters to be passed to the function.
	
	# An update identifier is inferred from the ordering of this table. ONLY ADD NEW CHANGES TO THE 
	#  END OF THE TABLE!!!
$upgrade[] = Array('CreateTableSQL',Array(config_get('mantis_config_table'),"
			  config_id C(64) NOTNULL PRIMARY,
			  project_id I DEFAULT '0' PRIMARY,
			  user_id I DEFAULT '0' PRIMARY,
			  access_reqd I DEFAULT '0',
			  type I DEFAULT '90',
			  value XL NOTNULL",
Array('mysql' => 'TYPE=MyISAM', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_config',config_get('mantis_config_table'),'config_id'));
$upgrade[] = Array('CreateTableSQL',Array(config_get('mantis_bug_file_table'),"
  id			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  bug_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  title 		C(250) NOTNULL DEFAULT \" '' \",
  description 		C(250) NOTNULL DEFAULT \" '' \",
  diskfile 		C(250) NOTNULL DEFAULT \" '' \",
  filename 		C(250) NOTNULL DEFAULT \" '' \",
  folder 		C(250) NOTNULL DEFAULT \" '' \",
  filesize 		 I NOTNULL DEFAULT '0',
  file_type 		C(250) NOTNULL DEFAULT \" '' \",
  date_added 		T NOTNULL DEFAULT '1970-01-01 00:00:01',
  content 		B NOTNULL
  ",Array('mysql' => 'TYPE=MyISAM', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_bug_file_bug_id',config_get('mantis_bug_file_table'),'bug_id'));
$upgrade[] = Array('CreateTableSQL',Array(config_get('mantis_bug_history_table'),"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  user_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  bug_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  date_modified 	T NOTNULL DEFAULT '1970-01-01 00:00:01',
  field_name 		C(32) NOTNULL DEFAULT \" '' \",
  old_value 		C(128) NOTNULL DEFAULT \" '' \",
  new_value 		C(128) NOTNULL DEFAULT \" '' \",
  type 			I2 NOTNULL DEFAULT '0'
  ",Array('mysql' => 'TYPE=MyISAM', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_bug_history_bug_id',config_get('mantis_bug_history_table'),'bug_id'));
$upgrade[] = Array('CreateIndexSQL',Array('idx_history_user_id',config_get('mantis_bug_history_table'),'user_id'));
$upgrade[] = Array('CreateTableSQL',Array(config_get('mantis_bug_monitor_table'),"
  user_id 		 I  UNSIGNED NOTNULL PRIMARY DEFAULT '0',
  bug_id 		 I  UNSIGNED NOTNULL PRIMARY DEFAULT '0'
",Array('mysql' => 'TYPE=MyISAM', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateTableSQL',Array(config_get('mantis_bug_relationship_table'),"
  id 			 I  UNSIGNED NOTNULL AUTOINCREMENT PRIMARY,
  source_bug_id		 I  UNSIGNED NOTNULL DEFAULT '0',
  destination_bug_id 	 I  UNSIGNED NOTNULL DEFAULT '0',
  relationship_type 	I2 NOTNULL DEFAULT '0'
",Array('mysql' => 'TYPE=MyISAM', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_relationship_source',config_get('mantis_bug_relationship_table'),'source_bug_id'));
$upgrade[] = Array('CreateIndexSQL',Array('idx_relationship_destination',config_get('mantis_bug_relationship_table'),'destination_bug_id'));
$upgrade[] = Array('CreateTableSQL',Array(config_get('mantis_bug_table'),"
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
  date_submitted 	T NOTNULL DEFAULT '1970-01-01 00:00:01',
  last_updated 		T NOTNULL DEFAULT '1970-01-01 00:00:01',
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
  sticky		L  NOTNULL DEFAULT '0'
",Array('mysql' => 'TYPE=MyISAM', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_bug_sponsorship_total',config_get('mantis_bug_table'),'sponsorship_total'));
$upgrade[] = Array('CreateIndexSQL',Array('idx_bug_fixed_in_version',config_get('mantis_bug_table'),'fixed_in_version'));
$upgrade[] = Array('CreateIndexSQL',Array('idx_bug_status',config_get('mantis_bug_table'),'status'));
$upgrade[] = Array('CreateIndexSQL',Array('idx_project',config_get('mantis_bug_table'),'project_id'));
$upgrade[] = Array('CreateTableSQL',Array(config_get('mantis_bug_text_table'),"
  id 			 I  PRIMARY UNSIGNED NOTNULL AUTOINCREMENT,
  description 		XL NOTNULL,
  steps_to_reproduce 	XL NOTNULL,
  additional_information XL NOTNULL
",Array('mysql' => 'TYPE=MyISAM', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateTableSQL',Array(config_get('mantis_bugnote_table'),"
  id 			 I  UNSIGNED PRIMARY NOTNULL AUTOINCREMENT,
  bug_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  reporter_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  bugnote_text_id 	 I  UNSIGNED NOTNULL DEFAULT '0',
  view_state 		I2 NOTNULL DEFAULT '10',
  date_submitted 	T NOTNULL DEFAULT '1970-01-01 00:00:01',
  last_modified 	T NOTNULL DEFAULT '1970-01-01 00:00:01',
  note_type 		 I  DEFAULT '0',
  note_attr 		C(250) DEFAULT \" '' \"
",Array('mysql' => 'TYPE=MyISAM', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_bug',config_get('mantis_bugnote_table'),'bug_id'));
$upgrade[] = Array('CreateIndexSQL',Array('idx_last_mod',config_get('mantis_bugnote_table'),'last_modified'));
$upgrade[] = Array('CreateTableSQL',Array(config_get('mantis_bugnote_text_table'),"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  note 			XL NOTNULL
",Array('mysql' => 'TYPE=MyISAM', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateTableSQL',Array(config_get('mantis_custom_field_project_table'),"
  field_id 		 I  NOTNULL PRIMARY DEFAULT '0',
  project_id 		 I  UNSIGNED PRIMARY NOTNULL DEFAULT '0',
  sequence 		I2 NOTNULL DEFAULT '0'
",Array('mysql' => 'TYPE=MyISAM', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateTableSQL',Array(config_get('mantis_custom_field_string_table'),"
  field_id 		 I  NOTNULL PRIMARY DEFAULT '0',
  bug_id 		 I  NOTNULL PRIMARY DEFAULT '0',
  value 		C(255) NOTNULL DEFAULT \" '' \"
",Array('mysql' => 'TYPE=MyISAM', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_custom_field_bug',config_get('mantis_custom_field_string_table'),'bug_id'));
$upgrade[] = Array('CreateTableSQL',Array(config_get('mantis_custom_field_table'),"
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
  advanced 		L NOTNULL DEFAULT '0',
  require_report 	L NOTNULL DEFAULT '0',
  require_update 	L NOTNULL DEFAULT '0',
  display_report 	L NOTNULL DEFAULT '1',
  display_update 	L NOTNULL DEFAULT '1',
  require_resolved 	L NOTNULL DEFAULT '0',
  display_resolved 	L NOTNULL DEFAULT '0',
  display_closed 	L NOTNULL DEFAULT '0',
  require_closed 	L NOTNULL DEFAULT '0'
",Array('mysql' => 'TYPE=MyISAM', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_custom_field_name',config_get('mantis_custom_field_table'),'name'));
$upgrade[] = Array('CreateTableSQL',Array(config_get('mantis_filters_table'),"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  user_id 		 I  NOTNULL DEFAULT '0',
  project_id 		 I  NOTNULL DEFAULT '0',
  is_public 		L DEFAULT NULL,
  name 			C(64) NOTNULL DEFAULT \" '' \",
  filter_string 	XL NOTNULL
",Array('mysql' => 'TYPE=MyISAM', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateTableSQL',Array(config_get('mantis_news_table'),"
  id 			 I  UNSIGNED PRIMARY NOTNULL AUTOINCREMENT,
  project_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  poster_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  date_posted 		T NOTNULL DEFAULT '1970-01-01 00:00:01',
  last_modified 	T NOTNULL DEFAULT '1970-01-01 00:00:01',
  view_state 		I2 NOTNULL DEFAULT '10',
  announcement 		L NOTNULL DEFAULT '0',
  headline 		C(64) NOTNULL DEFAULT \" '' \",
  body 			XL NOTNULL
",Array('mysql' => 'TYPE=MyISAM', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateTableSQL',Array(config_get('mantis_project_category_table'),"
  project_id 		 I  UNSIGNED NOTNULL PRIMARY DEFAULT '0',
  category 		C(64) NOTNULL PRIMARY DEFAULT \" '' \",
  user_id 		 I  UNSIGNED NOTNULL DEFAULT '0'
",Array('mysql' => 'TYPE=MyISAM', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateTableSQL',Array(config_get('mantis_project_file_table'),"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  project_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  title 		C(250) NOTNULL DEFAULT \" '' \",
  description 		C(250) NOTNULL DEFAULT \" '' \",
  diskfile 		C(250) NOTNULL DEFAULT \" '' \",
  filename 		C(250) NOTNULL DEFAULT \" '' \",
  folder 		C(250) NOTNULL DEFAULT \" '' \",
  filesize 		 I NOTNULL DEFAULT '0',
  file_type 		C(250) NOTNULL DEFAULT \" '' \",
  date_added 		T NOTNULL DEFAULT '1970-01-01 00:00:01',
  content 		B NOTNULL
",Array('mysql' => 'TYPE=MyISAM', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateTableSQL',Array(config_get('mantis_project_hierarchy_table'),"
			  child_id I UNSIGNED NOTNULL,
			  parent_id I UNSIGNED NOTNULL",
Array('mysql' => 'TYPE=MyISAM', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateTableSQL',Array(config_get('mantis_project_table'),"
  id 			 I  UNSIGNED PRIMARY NOTNULL AUTOINCREMENT,
  name 			C(128) NOTNULL DEFAULT \" '' \",
  status 		I2 NOTNULL DEFAULT '10',
  enabled 		L NOTNULL DEFAULT '1',
  view_state 		I2 NOTNULL DEFAULT '10',
  access_min 		I2 NOTNULL DEFAULT '10',
  file_path 		C(250) NOTNULL DEFAULT \" '' \",
  description 		XL NOTNULL
",Array('mysql' => 'TYPE=MyISAM', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_project_id',config_get('mantis_project_table'),'id'));
$upgrade[] = Array('CreateIndexSQL',Array('idx_project_name',config_get('mantis_project_table'),'name',Array('UNIQUE')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_project_view',config_get('mantis_project_table'),'view_state'));
$upgrade[] = Array('CreateTableSQL',Array(config_get('mantis_project_user_list_table'),"
  project_id 		 I  UNSIGNED PRIMARY NOTNULL DEFAULT '0',
  user_id 		 I  UNSIGNED PRIMARY NOTNULL DEFAULT '0',
  access_level 		I2 NOTNULL DEFAULT '10'
",Array('mysql' => 'TYPE=MyISAM', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array( 'CreateIndexSQL',Array('idx_project_user',config_get('mantis_project_user_list_table'),'user_id'));
$upgrade[] = Array('CreateTableSQL',Array(config_get('mantis_project_version_table'),"
  id 			 I  NOTNULL PRIMARY AUTOINCREMENT,
  project_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  version 		C(64) NOTNULL DEFAULT \" '' \",
  date_order 		T NOTNULL DEFAULT '1970-01-01 00:00:01',
  description 		XL NOTNULL,
  released 		L NOTNULL DEFAULT '1'
",Array('mysql' => 'TYPE=MyISAM', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_project_version',config_get('mantis_project_version_table'),'project_id,version',Array('UNIQUE')));
$upgrade[] = Array('CreateTableSQL',Array(config_get('mantis_sponsorship_table'),"
  id 			 I  NOTNULL PRIMARY AUTOINCREMENT,
  bug_id 		 I  NOTNULL DEFAULT '0',
  user_id 		 I  NOTNULL DEFAULT '0',
  amount 		 I  NOTNULL DEFAULT '0',
  logo 			C(128) NOTNULL DEFAULT \" '' \",
  url 			C(128) NOTNULL DEFAULT \" '' \",
  paid 			L NOTNULL DEFAULT '0',
  date_submitted 	T NOTNULL DEFAULT '1970-01-01 00:00:01',
  last_updated 		T NOTNULL DEFAULT '1970-01-01 00:00:01'
",Array('mysql' => 'TYPE=MyISAM', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_sponsorship_bug_id',config_get('mantis_sponsorship_table'),'bug_id'));
$upgrade[] = Array('CreateIndexSQL',Array('idx_sponsorship_user_id',config_get('mantis_sponsorship_table'),'user_id'));
$upgrade[] = Array('CreateTableSQL',Array(config_get('mantis_tokens_table'),"
			  id I NOTNULL PRIMARY AUTOINCREMENT,
			  owner I NOTNULL,
			  type I NOTNULL,
			  timestamp T NOTNULL,
			  expiry T,
			  value XL NOTNULL",
Array('mysql' => 'TYPE=MyISAM', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateTableSQL',Array(config_get('mantis_user_pref_table'),"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  user_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  project_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  default_profile 	 I  UNSIGNED NOTNULL DEFAULT '0',
  default_project 	 I  UNSIGNED NOTNULL DEFAULT '0',
  advanced_report 	L NOTNULL DEFAULT '0',
  advanced_view 	L NOTNULL DEFAULT '0',
  advanced_update 	L NOTNULL DEFAULT '0',
  refresh_delay 	 I  NOTNULL DEFAULT '0',
  redirect_delay 	L NOTNULL DEFAULT '0',
  bugnote_order 	C(4) NOTNULL DEFAULT 'ASC',
  email_on_new 		L NOTNULL DEFAULT '0',
  email_on_assigned 	L NOTNULL DEFAULT '0',
  email_on_feedback 	L NOTNULL DEFAULT '0',
  email_on_resolved	L NOTNULL DEFAULT '0',
  email_on_closed 	L NOTNULL DEFAULT '0',
  email_on_reopened 	L NOTNULL DEFAULT '0',
  email_on_bugnote 	L NOTNULL DEFAULT '0',
  email_on_status 	L NOTNULL DEFAULT '0',
  email_on_priority 	L NOTNULL DEFAULT '0',
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
",Array('mysql' => 'TYPE=MyISAM', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateTableSQL',Array(config_get('mantis_user_print_pref_table'),"
  user_id 		 I  UNSIGNED NOTNULL PRIMARY DEFAULT '0',
  print_pref 		C(27) NOTNULL DEFAULT \" '' \"
",Array('mysql' => 'TYPE=MyISAM', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateTableSQL',Array(config_get('mantis_user_profile_table'),"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  user_id 		 I  UNSIGNED NOTNULL DEFAULT '0',
  platform 		C(32) NOTNULL DEFAULT \" '' \",
  os 			C(32) NOTNULL DEFAULT \" '' \",
  os_build 		C(32) NOTNULL DEFAULT \" '' \",
  description 		XL NOTNULL
",Array('mysql' => 'TYPE=MyISAM', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateTableSQL',Array(config_get('mantis_user_table'),"
  id 			 I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  username 		C(32) NOTNULL DEFAULT \" '' \",
  realname 		C(64) NOTNULL DEFAULT \" '' \",
  email 		C(64) NOTNULL DEFAULT \" '' \",
  password 		C(32) NOTNULL DEFAULT \" '' \",
  date_created 		T NOTNULL DEFAULT '1970-01-01 00:00:01',
  last_visit 		T NOTNULL DEFAULT '1970-01-01 00:00:01',
  enabled		L NOTNULL DEFAULT '1',
  protected 		L NOTNULL DEFAULT '0',
  access_level 		I2 NOTNULL DEFAULT '10',
  login_count 		 I  NOTNULL DEFAULT '0',
  lost_password_request_count 	I2 NOTNULL DEFAULT '0',
  failed_login_count 	I2 NOTNULL DEFAULT '0',
  cookie_string 	C(64) NOTNULL DEFAULT \" '' \"
",Array('mysql' => 'TYPE=MyISAM', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_user_cookie_string',config_get('mantis_user_table'),'cookie_string',Array('UNIQUE')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_user_username',config_get('mantis_user_table'),'username',Array('UNIQUE')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_enable',config_get('mantis_user_table'),'enabled'));
$upgrade[] = Array('CreateIndexSQL',Array('idx_access',config_get('mantis_user_table'),'access_level'));
$upgrade[] = Array('InsertData', Array( config_get('mantis_user_table'), 
    "(username, realname, email, password, date_created, last_visit, enabled, protected, access_level, login_count, lost_password_request_count, failed_login_count, cookie_string) VALUES 
        ('administrator', '', 'root@localhost', '63a9f0ea7bb98050796b649e85481845', " . db_now() . ", " . db_now() . ", 1, 0, 90, 3, 0, 0, '" . 
             md5( mt_rand( 0, mt_getrandmax() ) + mt_rand( 0, mt_getrandmax() ) ) . md5( time() ) . "')" ) );
$upgrade[] = Array('AlterColumnSQL', Array( config_get( 'mantis_bug_history_table' ), "old_value C(255) NOTNULL" ) );
$upgrade[] = Array('AlterColumnSQL', Array( config_get( 'mantis_bug_history_table' ), "new_value C(255) NOTNULL" ) );

$upgrade[] = Array('CreateTableSQL',Array(config_get('mantis_email_table'),"
  email_id 		I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
  email		 	C(64) NOTNULL DEFAULT \" '' \",
  subject		C(250) NOTNULL DEFAULT \" '' \",
  submitted 	T NOTNULL DEFAULT '1970-01-01 00:00:01',
  metadata 		XL NOTNULL,
  body 			XL NOTNULL
  ",Array('mysql' => 'TYPE=MyISAM', 'pgsql' => 'WITHOUT OIDS')));
$upgrade[] = Array('CreateIndexSQL',Array('idx_email_id',config_get('mantis_email_table'),'email_id'));
$upgrade[] = Array('AddColumnSQL',Array(config_get('mantis_bug_table'), "target_version C(64) NOTNULL DEFAULT \" '' \""));
$upgrade[] = Array('AddColumnSQL',Array(config_get('mantis_bugnote_table'), "time_tracking I UNSIGNED NOTNULL DEFAULT \" 0 \""));
$upgrade[] = Array('CreateIndexSQL',Array('idx_diskfile',config_get('mantis_bug_file_table'),'diskfile'));
$upgrade[] = Array('AlterColumnSQL', Array( config_get( 'mantis_user_print_pref_table' ), "print_pref C(64) NOTNULL" ) );
$upgrade[] = Array('AlterColumnSQL', Array( config_get( 'mantis_bug_history_table' ), "field_name C(64) NOTNULL" ) );

# Release marker: 1.1.0a4

$upgrade[] = Array('CreateTableSQL', Array( config_get( 'mantis_tag_table' ), "
	id				I		UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
	user_id			I		UNSIGNED NOTNULL DEFAULT '0',
	name			C(100)	NOTNULL PRIMARY DEFAULT \" '' \",
	description		XL		NOTNULL,
	date_created	T		NOTNULL DEFAULT '1970-01-01 00:00:01',
	date_updated	T		NOTNULL DEFAULT '1970-01-01 00:00:01'
	", Array( 'mysql' => 'TYPE=MyISAM', 'pgsql' => 'WITHOUT OIDS' ) ) );
$upgrade[] = Array('CreateTableSQL', Array( config_get( 'mantis_bug_tag_table' ), "
	bug_id			I	UNSIGNED NOTNULL PRIMARY DEFAULT '0',
	tag_id			I	UNSIGNED NOTNULL PRIMARY DEFAULT '0',
	user_id			I	UNSIGNED NOTNULL DEFAULT '0',
	date_attached	T	NOTNULL DEFAULT '1970-01-01 00:00:01'
	", Array( 'mysql' => 'TYPE=MyISAM', 'pgsql' => 'WITHOUT OIDS' ) ) );

$upgrade[] = Array('CreateIndexSQL', Array( 'idx_typeowner', config_get( 'mantis_tokens_table' ), 'type, owner' ) );
?>
