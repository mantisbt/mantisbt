# --------------------------------------------------------

#
# Table structure for table `mantis_bug_file_table`
#

CREATE TABLE mantis_bug_file_table (
  id int(7) unsigned zerofill NOT NULL auto_increment,
  bug_id int(7) unsigned zerofill NOT NULL default '0000000',
  title varchar(250) NOT NULL default '',
  description varchar(250) NOT NULL default '',
  diskfile varchar(250) NOT NULL default '',
  filename varchar(250) NOT NULL default '',
  folder varchar(250) NOT NULL default '',
  filesize int(11) NOT NULL default '0',
  file_type varchar(250) NOT NULL default '',
  date_added datetime NOT NULL default '1970-01-01 00:00:01',
  content longblob NOT NULL,
  PRIMARY KEY  (id)
);
# --------------------------------------------------------

#
# Table structure for table `mantis_bug_history_table`
#

CREATE TABLE mantis_bug_history_table (
  user_id int(7) unsigned zerofill NOT NULL default '0000000',
  bug_id int(7) unsigned zerofill NOT NULL default '0000000',
  date_modified datetime NOT NULL default '1970-01-01 00:00:01',
  field_name varchar(32) NOT NULL default '',
  old_value varchar(128) NOT NULL default '',
  new_value varchar(128) NOT NULL default '',
  KEY bug_id (bug_id),
  KEY user_id (user_id)
);
# --------------------------------------------------------

#
# Table structure for table `mantis_bug_monitor_table`
#

CREATE TABLE mantis_bug_monitor_table (
  user_id int(7) unsigned zerofill NOT NULL default '0000000',
  bug_id int(7) unsigned NOT NULL default '0'
);
# --------------------------------------------------------

#
# Table structure for table `mantis_bug_relationship_table`
#

CREATE TABLE mantis_bug_relationship_table (
  source_bug_id int(7) unsigned zerofill NOT NULL default '0000000',
  destination_bug_id int(7) unsigned zerofill NOT NULL default '0000000',
  relationship_type int(2) NOT NULL default '0'
);
# --------------------------------------------------------

#
# Table structure for table `mantis_bug_table`
#

CREATE TABLE mantis_bug_table (
  id int(7) unsigned zerofill NOT NULL auto_increment,
  project_id int(7) unsigned zerofill NOT NULL default '0000000',
  reporter_id int(7) unsigned zerofill NOT NULL default '0000000',
  handler_id int(7) unsigned zerofill NOT NULL default '0000000',
  duplicate_id int(7) unsigned zerofill NOT NULL default '0000000',
  priority int(2) NOT NULL default '30',
  severity int(2) NOT NULL default '50',
  reproducibility int(2) NOT NULL default '10',
  status int(2) NOT NULL default '10',
  resolution int(2) NOT NULL default '10',
  projection int(2) NOT NULL default '10',
  category varchar(64) NOT NULL default '',
  date_submitted datetime NOT NULL default '1970-01-01 00:00:01',
  last_updated datetime NOT NULL default '1970-01-01 00:00:01',
  eta int(2) NOT NULL default '10',
  bug_text_id int(7) unsigned zerofill NOT NULL default '0000000',
  os varchar(32) NOT NULL default '',
  os_build varchar(32) NOT NULL default '',
  platform varchar(32) NOT NULL default '',
  version varchar(64) NOT NULL default '',
  build varchar(32) NOT NULL default '',
  votes int(4) NOT NULL default '0',
  profile_id int(7) unsigned zerofill NOT NULL default '0000000',
  view_state int(2) NOT NULL default '10',
  summary varchar(128) NOT NULL default '',
  PRIMARY KEY  (id)
);
# --------------------------------------------------------

#
# Table structure for table `mantis_bug_text_table`
#

CREATE TABLE mantis_bug_text_table (
  id int(7) unsigned zerofill NOT NULL auto_increment,
  description text NOT NULL,
  steps_to_reproduce text NOT NULL,
  additional_information text NOT NULL,
  PRIMARY KEY  (id)
);
# --------------------------------------------------------

#
# Table structure for table `mantis_bugnote_table`
#

CREATE TABLE mantis_bugnote_table (
  id int(7) unsigned zerofill NOT NULL auto_increment,
  bug_id int(7) unsigned zerofill NOT NULL default '0000000',
  reporter_id int(7) unsigned zerofill NOT NULL default '0000000',
  bugnote_text_id int(7) unsigned zerofill NOT NULL default '0000000',
  view_state int(2) NOT NULL default '10',
  date_submitted datetime NOT NULL default '1970-01-01 00:00:01',
  last_modified datetime NOT NULL default '1970-01-01 00:00:01',
  PRIMARY KEY  (id)
);
# --------------------------------------------------------

#
# Table structure for table `mantis_bugnote_text_table`
#

CREATE TABLE mantis_bugnote_text_table (
  id int(7) unsigned zerofill NOT NULL auto_increment,
  note text NOT NULL,
  PRIMARY KEY  (id)
);
# --------------------------------------------------------

#
# Table structure for table `mantis_news_table`
#

CREATE TABLE mantis_news_table (
  id int(7) unsigned zerofill NOT NULL auto_increment,
  project_id int(7) unsigned zerofill NOT NULL default '0000000',
  poster_id int(7) unsigned zerofill NOT NULL default '0000000',
  date_posted datetime NOT NULL default '1970-01-01 00:00:01',
  last_modified datetime NOT NULL default '1970-01-01 00:00:01',
  headline varchar(64) NOT NULL default '',
  body text NOT NULL,
  PRIMARY KEY  (id),
  KEY id (id)
);
# --------------------------------------------------------

#
# Table structure for table `mantis_project_category_table`
#

CREATE TABLE mantis_project_category_table (
  project_id int(7) unsigned zerofill NOT NULL default '0000000',
  category varchar(64) NOT NULL default ''
);

INSERT INTO mantis_project_category_table (project_id, category) VALUES ( '0000001', 'bugtracker');
INSERT INTO mantis_project_category_table (project_id, category) VALUES ( '0000001', 'other');
INSERT INTO mantis_project_category_table (project_id, category) VALUES ( '0000001', 'security');

# --------------------------------------------------------

#
# Table structure for table `mantis_project_file_table`
#

CREATE TABLE mantis_project_file_table (
  id int(7) unsigned zerofill NOT NULL auto_increment,
  project_id int(7) unsigned zerofill NOT NULL default '0000000',
  title varchar(250) NOT NULL default '',
  description varchar(250) NOT NULL default '',
  diskfile varchar(250) NOT NULL default '',
  filename varchar(250) NOT NULL default '',
  folder varchar(250) NOT NULL default '',
  filesize int(11) NOT NULL default '0',
  file_type varchar(250) NOT NULL default '',
  date_added datetime NOT NULL default '1970-01-01 00:00:01',
  content longblob NOT NULL,
  PRIMARY KEY  (id)
);
# --------------------------------------------------------

#
# Table structure for table `mantis_project_table`
#

CREATE TABLE mantis_project_table (
  id int(7) unsigned zerofill NOT NULL auto_increment,
  name varchar(128) NOT NULL default '',
  status int(2) NOT NULL default '10',
  enabled int(1) NOT NULL default '1',
  view_state int(2) NOT NULL default '10',
  access_min int(2) NOT NULL default '10',
  file_path varchar(250) NOT NULL default '',
  description text NOT NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY name (name),
  KEY id (id)
);

INSERT INTO mantis_project_table (id, name, status, enabled, view_state, description) VALUES ( '0000001', 'mantis', '10', '1', '10', 'Mantis.  Report problems with the actual bug tracker here. (Do not remove this account.  You can set it to be disabled if you do not wish to see it)');

# --------------------------------------------------------

#
# Table structure for table `mantis_project_user_list_table`
#

CREATE TABLE mantis_project_user_list_table (
  project_id int(7) unsigned zerofill NOT NULL default '0000000',
  user_id int(7) unsigned zerofill NOT NULL default '0000000',
  access_level int(2) NOT NULL default '10'
);
# --------------------------------------------------------

#
# Table structure for table `mantis_project_version_table`
#

CREATE TABLE mantis_project_version_table (
  project_id int(7) unsigned zerofill NOT NULL default '0000000',
  version varchar(64) NOT NULL default '',
  date_order datetime NOT NULL default '1970-01-01 00:00:01'
);

INSERT INTO mantis_project_version_table (project_id, version, date_order) VALUES ( '0000001', '0.14.0', NOW());

# --------------------------------------------------------

#
# Table structure for table `mantis_user_pref_table`
#

CREATE TABLE mantis_user_pref_table (
  id int(7) unsigned zerofill NOT NULL auto_increment,
  user_id int(7) unsigned zerofill NOT NULL default '0000000',
  project_id int(7) unsigned zerofill NOT NULL default '0000000',
  default_profile int(7) unsigned zerofill NOT NULL default '0000000',
  default_project int(7) unsigned zerofill NOT NULL default '0000000',
  advanced_report int(1) NOT NULL default '0',
  advanced_view int(1) NOT NULL default '0',
  advanced_update int(1) NOT NULL default '0',
  refresh_delay int(4) NOT NULL default '0',
  redirect_delay int(1) NOT NULL default '0',
  email_on_new int(1) NOT NULL default '0',
  email_on_assigned int(1) NOT NULL default '0',
  email_on_feedback int(1) NOT NULL default '0',
  email_on_resolved int(1) NOT NULL default '0',
  email_on_closed int(1) NOT NULL default '0',
  email_on_reopened int(1) NOT NULL default '0',
  email_on_bugnote int(1) NOT NULL default '0',
  email_on_status int(1) NOT NULL default '0',
  email_on_priority int(1) NOT NULL default '0',
  language varchar(32) NOT NULL default 'english',
  PRIMARY KEY  (id)
);

INSERT INTO mantis_user_pref_table (id, user_id, advanced_report, advanced_view) VALUES ( '0000001', '0000001', '', '');

# --------------------------------------------------------

#
# Table structure for table `mantis_user_profile_table`
#

CREATE TABLE mantis_user_profile_table (
  id int(7) unsigned zerofill NOT NULL auto_increment,
  user_id int(7) unsigned zerofill NOT NULL default '0000000',
  platform varchar(32) NOT NULL default '',
  os varchar(32) NOT NULL default '',
  os_build varchar(32) NOT NULL default '',
  description text NOT NULL,
  PRIMARY KEY  (id)
);
# --------------------------------------------------------

#
# Table structure for table `mantis_user_table`
#

CREATE TABLE mantis_user_table (
  id int(7) unsigned zerofill NOT NULL auto_increment,
  username varchar(32) NOT NULL default '',
  email varchar(64) NOT NULL default '',
  password varchar(32) NOT NULL default '',
  date_created datetime NOT NULL default '1970-01-01 00:00:01',
  last_visit datetime NOT NULL default '1970-01-01 00:00:01',
  enabled int(1) NOT NULL default '1',
  protected int(1) NOT NULL default '0',
  access_level int(2) NOT NULL default '10',
  login_count int(11) NOT NULL default '0',
  cookie_string varchar(64) NOT NULL default '',
  PRIMARY KEY  (id),
  UNIQUE KEY username (username)
);

# replace the 4th argument after VALUES with your chosen method of encryption
# default is MD5

#   PLAIN = root
#   CRYPT = MNYm8SfoJlvIY
#   MD5   = 63a9f0ea7bb98050796b649e85481845

INSERT INTO mantis_user_table
	(id, username, email, password, date_created, last_visit, enabled, protected, access_level, login_count, cookie_string)
VALUES
	( '0000001', 'administrator', 'admin', '63a9f0ea7bb98050796b649e85481845', NOW(), NOW(), '1', '1', '90', '0', 'MN91uSF/JIhos8bcda8acc2ead8d60749ad019e56b54fadkPGTyoBgNBQf91563adc2f7337f566cc3d2c792bc3c-10728595');
