# phpMyAdmin SQL Dump
# version 2.5.7
# http://www.phpmyadmin.net
#
# Host: localhost
# Generation Time: Nov 06, 2004 at 09:52 PM
# Server version: 4.0.20
# PHP Version: 4.3.8
# 
# Database : `mantis`
# 

# --------------------------------------------------------

#
# Table structure for table `mantis_bug_file_table`
#

CREATE TABLE mantis_bug_file_table (
  id int(7) unsigned NOT NULL auto_increment,
  bug_id int(7) unsigned NOT NULL default '0',
  title varchar(250) NOT NULL default '',
  description varchar(250) NOT NULL default '',
  diskfile varchar(250) NOT NULL default '',
  filename varchar(250) NOT NULL default '',
  folder varchar(250) NOT NULL default '',
  filesize int(11) NOT NULL default '0',
  file_type varchar(250) NOT NULL default '',
  date_added datetime NOT NULL default '1970-01-01 00:00:01',
  content longblob NOT NULL,
  PRIMARY KEY  (id),
  KEY bug_id (bug_id)
) TYPE=MyISAM;

#
# Dumping data for table `mantis_bug_file_table`
#


# --------------------------------------------------------

#
# Table structure for table `mantis_bug_history_table`
#

CREATE TABLE mantis_bug_history_table (
  id int(7) unsigned NOT NULL auto_increment,
  user_id int(7) unsigned NOT NULL default '0',
  bug_id int(7) unsigned NOT NULL default '0',
  date_modified datetime NOT NULL default '1970-01-01 00:00:01',
  field_name varchar(32) NOT NULL default '',
  old_value varchar(128) NOT NULL default '',
  new_value varchar(128) NOT NULL default '',
  type int(2) NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY bug_id (bug_id),
  KEY user_id (user_id)
) TYPE=MyISAM;

#
# Dumping data for table `mantis_bug_history_table`
#


# --------------------------------------------------------

#
# Table structure for table `mantis_bug_monitor_table`
#

CREATE TABLE mantis_bug_monitor_table (
  user_id int(7) unsigned NOT NULL default '0',
  bug_id int(7) unsigned NOT NULL default '0',
  PRIMARY KEY  (user_id,bug_id)
) TYPE=MyISAM;

#
# Dumping data for table `mantis_bug_monitor_table`
#


# --------------------------------------------------------

#
# Table structure for table `mantis_bug_relationship_table`
#

CREATE TABLE mantis_bug_relationship_table (
  id int(7) unsigned NOT NULL auto_increment,
  source_bug_id int(7) unsigned NOT NULL default '0',
  destination_bug_id int(7) unsigned NOT NULL default '0',
  relationship_type int(2) NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY source_bug_id (source_bug_id),
  KEY destination_bug_id (destination_bug_id)
) TYPE=MyISAM;

#
# Dumping data for table `mantis_bug_relationship_table`
#


# --------------------------------------------------------

#
# Table structure for table `mantis_bug_table`
#

CREATE TABLE mantis_bug_table (
  id int(7) unsigned NOT NULL auto_increment,
  project_id int(7) unsigned NOT NULL default '0',
  reporter_id int(7) unsigned NOT NULL default '0',
  handler_id int(7) unsigned NOT NULL default '0',
  duplicate_id int(7) unsigned NOT NULL default '0',
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
  bug_text_id int(7) unsigned NOT NULL default '0',
  os varchar(32) NOT NULL default '',
  os_build varchar(32) NOT NULL default '',
  platform varchar(32) NOT NULL default '',
  version varchar(64) NOT NULL default '',
  fixed_in_version varchar(64) NOT NULL default '',
  build varchar(32) NOT NULL default '',
  profile_id int(7) unsigned NOT NULL default '0',
  view_state int(2) NOT NULL default '10',
  summary varchar(128) NOT NULL default '',
  sponsorship_total int(7) NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY sponsorship_total (sponsorship_total),
  KEY fixed_in_version (fixed_in_version)
) TYPE=MyISAM;

#
# Dumping data for table `mantis_bug_table`
#


# --------------------------------------------------------

#
# Table structure for table `mantis_bug_text_table`
#

CREATE TABLE mantis_bug_text_table (
  id int(7) unsigned NOT NULL auto_increment,
  description text NOT NULL,
  steps_to_reproduce text NOT NULL,
  additional_information text NOT NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

#
# Dumping data for table `mantis_bug_text_table`
#


# --------------------------------------------------------

#
# Table structure for table `mantis_bugnote_table`
#

CREATE TABLE mantis_bugnote_table (
  id int(7) unsigned NOT NULL auto_increment,
  bug_id int(7) unsigned NOT NULL default '0',
  reporter_id int(7) unsigned NOT NULL default '0',
  bugnote_text_id int(7) unsigned NOT NULL default '0',
  view_state int(2) NOT NULL default '10',
  date_submitted datetime NOT NULL default '1970-01-01 00:00:01',
  last_modified datetime NOT NULL default '1970-01-01 00:00:01',
  note_type int(7) default '0',
  note_attr varchar(250) default '',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

#
# Dumping data for table `mantis_bugnote_table`
#


# --------------------------------------------------------

#
# Table structure for table `mantis_bugnote_text_table`
#

CREATE TABLE mantis_bugnote_text_table (
  id int(7) unsigned NOT NULL auto_increment,
  note text NOT NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

#
# Dumping data for table `mantis_bugnote_text_table`
#


# --------------------------------------------------------

#
# Table structure for table `mantis_custom_field_project_table`
#

CREATE TABLE mantis_custom_field_project_table (
  field_id int(3) NOT NULL default '0',
  project_id int(7) unsigned NOT NULL default '0',
  sequence int(2) NOT NULL default '0',
  PRIMARY KEY  (field_id,project_id)
) TYPE=MyISAM;

#
# Dumping data for table `mantis_custom_field_project_table`
#


# --------------------------------------------------------

#
# Table structure for table `mantis_custom_field_string_table`
#

CREATE TABLE mantis_custom_field_string_table (
  field_id int(3) NOT NULL default '0',
  bug_id int(7) NOT NULL default '0',
  value varchar(255) NOT NULL default '',
  PRIMARY KEY  (field_id,bug_id)
) TYPE=MyISAM;

#
# Dumping data for table `mantis_custom_field_string_table`
#


# --------------------------------------------------------

#
# Table structure for table `mantis_custom_field_table`
#

CREATE TABLE mantis_custom_field_table (
  id int(3) NOT NULL auto_increment,
  name varchar(64) NOT NULL default '',
  type int(2) NOT NULL default '0',
  possible_values varchar(255) NOT NULL default '',
  default_value varchar(255) NOT NULL default '',
  valid_regexp varchar(255) NOT NULL default '',
  access_level_r int(2) NOT NULL default '0',
  access_level_rw int(2) NOT NULL default '0',
  length_min int(3) NOT NULL default '0',
  length_max int(3) NOT NULL default '0',
  advanced int(1) NOT NULL default '0',
  require_report tinyint(1) NOT NULL default '0',
  require_update tinyint(1) NOT NULL default '0',
  display_report tinyint(1) NOT NULL default '1',
  display_update tinyint(1) NOT NULL default '1',
  require_resolved tinyint(1) NOT NULL default '0',
  display_resolved tinyint(1) NOT NULL default '0',
  display_closed tinyint(1) NOT NULL default '0',
  require_closed tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY name (name)
) TYPE=MyISAM;

#
# Dumping data for table `mantis_custom_field_table`
#


# --------------------------------------------------------

#
# Table structure for table `mantis_filters_table`
#

CREATE TABLE mantis_filters_table (
  id int(7) unsigned NOT NULL auto_increment,
  user_id int(7) NOT NULL default '0',
  project_id int(7) NOT NULL default '0',
  is_public tinyint(1) default NULL,
  name varchar(64) NOT NULL default '',
  filter_string text NOT NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

#
# Dumping data for table `mantis_filters_table`
#


# --------------------------------------------------------

#
# Table structure for table `mantis_news_table`
#

CREATE TABLE mantis_news_table (
  id int(7) unsigned NOT NULL auto_increment,
  project_id int(7) unsigned NOT NULL default '0',
  poster_id int(7) unsigned NOT NULL default '0',
  date_posted datetime NOT NULL default '1970-01-01 00:00:01',
  last_modified datetime NOT NULL default '1970-01-01 00:00:01',
  view_state int(2) NOT NULL default '10',
  announcement int(1) NOT NULL default '0',
  headline varchar(64) NOT NULL default '',
  body text NOT NULL,
  PRIMARY KEY  (id),
  KEY id (id)
) TYPE=MyISAM;

#
# Dumping data for table `mantis_news_table`
#


# --------------------------------------------------------

#
# Table structure for table `mantis_project_category_table`
#

CREATE TABLE mantis_project_category_table (
  project_id int(7) unsigned NOT NULL default '0',
  category varchar(64) NOT NULL default '',
  user_id int(7) unsigned NOT NULL default '0',
  PRIMARY KEY  (project_id,category)
) TYPE=MyISAM;

#
# Dumping data for table `mantis_project_category_table`
#


# --------------------------------------------------------

#
# Table structure for table `mantis_project_file_table`
#

CREATE TABLE mantis_project_file_table (
  id int(7) unsigned NOT NULL auto_increment,
  project_id int(7) unsigned NOT NULL default '0',
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
) TYPE=MyISAM;

#
# Dumping data for table `mantis_project_file_table`
#


# --------------------------------------------------------

#
# Table structure for table `mantis_project_table`
#

CREATE TABLE mantis_project_table (
  id int(7) unsigned NOT NULL auto_increment,
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
) TYPE=MyISAM;

#
# Dumping data for table `mantis_project_table`
#


# --------------------------------------------------------

#
# Table structure for table `mantis_project_user_list_table`
#

CREATE TABLE mantis_project_user_list_table (
  project_id int(7) unsigned NOT NULL default '0',
  user_id int(7) unsigned NOT NULL default '0',
  access_level int(2) NOT NULL default '10',
  PRIMARY KEY  (project_id,user_id)
) TYPE=MyISAM;

#
# Dumping data for table `mantis_project_user_list_table`
#


# --------------------------------------------------------

#
# Table structure for table `mantis_project_version_table`
#

CREATE TABLE mantis_project_version_table (
  id int(7) NOT NULL auto_increment,
  project_id int(7) unsigned NOT NULL default '0',
  version varchar(64) NOT NULL default '',
  date_order datetime NOT NULL default '1970-01-01 00:00:01',
  description text NOT NULL,
  released tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (id),
  UNIQUE KEY project_version (project_id,version)
) TYPE=MyISAM;

#
# Dumping data for table `mantis_project_version_table`
#


# --------------------------------------------------------

#
# Table structure for table `mantis_sponsorship_table`
#

CREATE TABLE mantis_sponsorship_table (
  id int(7) NOT NULL auto_increment,
  bug_id int(7) NOT NULL default '0',
  user_id int(7) NOT NULL default '0',
  amount int(7) NOT NULL default '0',
  logo varchar(128) NOT NULL default '',
  url varchar(128) NOT NULL default '',
  paid int(1) NOT NULL default '0',
  date_submitted datetime NOT NULL default '1970-01-01 00:00:01',
  last_updated datetime NOT NULL default '1970-01-01 00:00:01',
  PRIMARY KEY  (id),
  KEY bug_id (bug_id),
  KEY user_id (user_id)
) TYPE=MyISAM COMMENT='A table for sponsorships';

#
# Dumping data for table `mantis_sponsorship_table`
#


# --------------------------------------------------------

#
# Table structure for table `mantis_upgrade_table`
#

CREATE TABLE mantis_upgrade_table (
  upgrade_id char(20) NOT NULL default '',
  description char(255) NOT NULL default '',
  PRIMARY KEY  (upgrade_id)
) TYPE=MyISAM;

#
# Dumping data for table `mantis_upgrade_table`
#

INSERT INTO mantis_upgrade_table VALUES ('0.13-1', 'Add mantis_project_table');
INSERT INTO mantis_upgrade_table VALUES ('0.13-2', 'Insert default project into mantis_project_table');
INSERT INTO mantis_upgrade_table VALUES ('0.13-3', 'Add mantis_project_category_table');
INSERT INTO mantis_upgrade_table VALUES ('0.13-4', 'Add mantis_project_version_table');
INSERT INTO mantis_upgrade_table VALUES ('0.13-5', 'Add project_id column to mantis_bug_table');
INSERT INTO mantis_upgrade_table VALUES ('0.13-6', 'Change category column in mantis_bug_table to varchar');
INSERT INTO mantis_upgrade_table VALUES ('0.13-7', 'Change version column in mantis_bug_table to varchar');
INSERT INTO mantis_upgrade_table VALUES ('0.13-8', 'Set project_id to "0000001" for all bugs');
INSERT INTO mantis_upgrade_table VALUES ('0.13-9', 'Add project_id column news table');
INSERT INTO mantis_upgrade_table VALUES ('0.13-10', 'Set project_id to "0000001" for all news postings');
INSERT INTO mantis_upgrade_table VALUES ('0.13-11', 'Added login count to user table');
INSERT INTO mantis_upgrade_table VALUES ('0.13-12', 'Add manager to access_levels');
INSERT INTO mantis_upgrade_table VALUES ('0.13-13', 'Make username unique');
INSERT INTO mantis_upgrade_table VALUES ('0.14a-0', '');
INSERT INTO mantis_upgrade_table VALUES ('0.14a-1', '');
INSERT INTO mantis_upgrade_table VALUES ('0.14a-2', '');
INSERT INTO mantis_upgrade_table VALUES ('0.14a-3', '');
INSERT INTO mantis_upgrade_table VALUES ('0.14a-4', '');
INSERT INTO mantis_upgrade_table VALUES ('0.14a-5', '');
INSERT INTO mantis_upgrade_table VALUES ('0.14a-6', '');
INSERT INTO mantis_upgrade_table VALUES ('0.14a-7', '');
INSERT INTO mantis_upgrade_table VALUES ('0.14a-8', '');
INSERT INTO mantis_upgrade_table VALUES ('0.14a-9', '');
INSERT INTO mantis_upgrade_table VALUES ('0.14-0', 'Change some of the TIMESTAMP fields to DATETIME');
INSERT INTO mantis_upgrade_table VALUES ('0.14-1', 'Change some of the TIMESTAMP fields to DATETIME');
INSERT INTO mantis_upgrade_table VALUES ('0.14-2', 'Change some of the TIMESTAMP fields to DATETIME');
INSERT INTO mantis_upgrade_table VALUES ('0.14-3', 'Change some of the TIMESTAMP fields to DATETIME');
INSERT INTO mantis_upgrade_table VALUES ('0.14-4', 'INT(1) Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-5', 'INT(1) Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-6', 'INT(1) Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-7', 'INT(1) Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-8', 'INT(1) Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-9', 'INT(1) Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-10', 'INT(1) Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-11', 'INT(1) Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-12', 'INT(1) Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-13', 'INT(1) Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-14', 'INT(1) Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-15', 'INT(1) Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-16', 'Change CHAR(3) to INT(1)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-17', 'Change CHAR(3) to INT(1)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-18', 'Change CHAR(3) to INT(1)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-19', 'Change CHAR(3) to INT(1)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-20', 'Change CHAR(3) to INT(1)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-21', 'Change CHAR(3) to INT(1)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-22', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-23', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-24', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-25', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-26', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-27', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-28', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-29', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-30', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-31', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-32', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-33', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-34', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-35', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-36', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-37', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-38', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-39', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-40', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-41', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-42', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-43', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-44', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-45', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-46', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-47', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-48', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-49', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-50', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-51', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-52', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-53', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-54', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-55', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-56', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-57', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-58', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-59', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-60', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-61', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-62', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-63', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-64', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-65', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-66', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-67', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-68', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-69', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-70', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-71', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-72', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-73', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-74', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-75', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-76', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-77', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-78', 'ENUM Updates (Before ALTERation)');
INSERT INTO mantis_upgrade_table VALUES ('0.14-79', 'Change ENUM to INT');
INSERT INTO mantis_upgrade_table VALUES ('0.14-80', 'Change ENUM to INT');
INSERT INTO mantis_upgrade_table VALUES ('0.14-81', 'Change ENUM to INT');
INSERT INTO mantis_upgrade_table VALUES ('0.14-82', 'Change ENUM to INT');
INSERT INTO mantis_upgrade_table VALUES ('0.14-83', 'Change ENUM to INT');
INSERT INTO mantis_upgrade_table VALUES ('0.14-84', 'Change ENUM to INT');
INSERT INTO mantis_upgrade_table VALUES ('0.14-85', 'Change ENUM to INT');
INSERT INTO mantis_upgrade_table VALUES ('0.14-86', 'Change ENUM to INT');
INSERT INTO mantis_upgrade_table VALUES ('0.14-87', 'Update dates to be legal');
INSERT INTO mantis_upgrade_table VALUES ('0.14-88', 'Update dates to be legal');
INSERT INTO mantis_upgrade_table VALUES ('0.14-89', 'Update dates to be legal');
INSERT INTO mantis_upgrade_table VALUES ('0.14-90', 'Shorten cookie string to 64 characters');
INSERT INTO mantis_upgrade_table VALUES ('0.14-91', 'Add file_path to projects');
INSERT INTO mantis_upgrade_table VALUES ('0.14-92', 'Add access_min to projects');
INSERT INTO mantis_upgrade_table VALUES ('0.14-93', 'Add new user prefs');
INSERT INTO mantis_upgrade_table VALUES ('0.14-94', 'Add new user prefs');
INSERT INTO mantis_upgrade_table VALUES ('0.14-95', 'Add new user prefs');
INSERT INTO mantis_upgrade_table VALUES ('0.14-96', 'Add new user prefs');
INSERT INTO mantis_upgrade_table VALUES ('0.14-97', 'Add new user prefs');
INSERT INTO mantis_upgrade_table VALUES ('0.14-98', 'Add new user prefs');
INSERT INTO mantis_upgrade_table VALUES ('0.14-99', 'Add new user prefs');
INSERT INTO mantis_upgrade_table VALUES ('0.14-100', 'Add new user prefs');
INSERT INTO mantis_upgrade_table VALUES ('0.14-101', 'Add new user prefs');
INSERT INTO mantis_upgrade_table VALUES ('0.14-102', 'Add new user prefs');
INSERT INTO mantis_upgrade_table VALUES ('0.14-103', 'Add new user prefs');
INSERT INTO mantis_upgrade_table VALUES ('0.14-104', 'Add new user prefs');
INSERT INTO mantis_upgrade_table VALUES ('0.14-105', 'Add new user prefs');
INSERT INTO mantis_upgrade_table VALUES ('0.14-106', 'Add new user prefs');
INSERT INTO mantis_upgrade_table VALUES ('0.14-107', 'Add new user prefs');
INSERT INTO mantis_upgrade_table VALUES ('0.14-108', 'Add new user prefs');
INSERT INTO mantis_upgrade_table VALUES ('0.14-109', 'Make new project level user access table');
INSERT INTO mantis_upgrade_table VALUES ('0.14-110', 'Make new project file table');
INSERT INTO mantis_upgrade_table VALUES ('0.14-111', 'Make new bug file table');
INSERT INTO mantis_upgrade_table VALUES ('0.14-112', 'more varchar to enum conversions');
INSERT INTO mantis_upgrade_table VALUES ('0.14-113', 'more varchar to enum conversions');
INSERT INTO mantis_upgrade_table VALUES ('0.14-114', 'Need this entry for the project listing to work');
INSERT INTO mantis_upgrade_table VALUES ('0.14-115', 'Add ordering field for versions');
INSERT INTO mantis_upgrade_table VALUES ('0.14-116', 'Make the cookie string unique');
INSERT INTO mantis_upgrade_table VALUES ('0.15-1', 'Add file type column to bug file table');
INSERT INTO mantis_upgrade_table VALUES ('0.15-2', 'Add file type column to project file table');
INSERT INTO mantis_upgrade_table VALUES ('0.15-3', '');
INSERT INTO mantis_upgrade_table VALUES ('0.15-4', '');
INSERT INTO mantis_upgrade_table VALUES ('0.15-5', '');
INSERT INTO mantis_upgrade_table VALUES ('0.15-6', '');
INSERT INTO mantis_upgrade_table VALUES ('0.15-7', '');
INSERT INTO mantis_upgrade_table VALUES ('0.15-8', 'Create bug history table');
INSERT INTO mantis_upgrade_table VALUES ('0.15-9', 'Add order field to project version table');
INSERT INTO mantis_upgrade_table VALUES ('0.16-1', '');
INSERT INTO mantis_upgrade_table VALUES ('0.16-2', '');
INSERT INTO mantis_upgrade_table VALUES ('0.16-3', '');
INSERT INTO mantis_upgrade_table VALUES ('0.16-4', '');
INSERT INTO mantis_upgrade_table VALUES ('0.16-5', '');
INSERT INTO mantis_upgrade_table VALUES ('0.16-6', '');
INSERT INTO mantis_upgrade_table VALUES ('0.16-7', 'Add view_state to bug table');
INSERT INTO mantis_upgrade_table VALUES ('0.16-8', 'Add view_state to bugnote table');
INSERT INTO mantis_upgrade_table VALUES ('0.16-9', '');
INSERT INTO mantis_upgrade_table VALUES ('0.16-10', '');
INSERT INTO mantis_upgrade_table VALUES ('0.16-11', '');
INSERT INTO mantis_upgrade_table VALUES ('0.16-12', '');
INSERT INTO mantis_upgrade_table VALUES ('0.16-13', 'Add project_id to user pref table');
INSERT INTO mantis_upgrade_table VALUES ('0.16-14', 'Create bug relationship table');
INSERT INTO mantis_upgrade_table VALUES ('0.16-15', 'Create bug monitor table');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-1', 'Printing Preference Table');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-2', 'Bug history');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-3', 'Auto-assigning of bugs for a default user per category');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-4', 'Private news support');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-5', 'Allow news items to stay at the top');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-6', 'relationship support');
INSERT INTO mantis_upgrade_table VALUES ('0.17-custom-field-1', 'Add mantis_custom_field_table');
INSERT INTO mantis_upgrade_table VALUES ('0.17-custom-field-2', 'Add mantis_custom_field_string_table');
INSERT INTO mantis_upgrade_table VALUES ('0.17-custom-field-3', 'Add mantis_custom_field_project_table');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-7', 'Drop mantis_project_customization_table');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-8', 'Drop votes column of mantis_bug_table');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-9', 'Add primary key on mantis_project_version_table');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-10', 'Add primary key on mantis_project_user_list_table');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-11', 'Add primary key on mantis_project_category_table');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-12', 'Add primary key on mantis_bug_monitor_table');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-13', 'Remove zerofill on mantis_bug_file_table.id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-14', 'Remove zerofill on mantis_bug_file_table.bug_id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-15', 'Remove zerofill on mantis_bug_history_table.user_id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-16', 'Remove zerofill on mantis_bug_history_table.bug_id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-17', 'Remove zerofill on mantis_bug_monitor_table.user_id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-18', 'Remove zerofill on mantis_bug_relationship_table.id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-19', 'Remove zerofill on mantis_bug_relationship_table.source_bug_id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-20', 'Remove zerofill on mantis_bug_relationship_table.destination_bug_id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-21', 'Remove zerofill on mantis_bug_table.id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-22', 'Remove zerofill on mantis_bug_table.project_id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-23', 'Remove zerofill on mantis_bug_table.reporter_id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-24', 'Remove zerofill on mantis_bug_table.handler_id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-25', 'Remove zerofill on mantis_bug_table.duplicate_id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-26', 'Remove zerofill on mantis_bug_table.bug_text_id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-27', 'Remove zerofill on mantis_bug_table.profile_id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-28', 'Remove zerofill on mantis_bug_text_table.id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-29', 'Remove zerofill on mantis_bugnote_table.id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-30', 'Remove zerofill on mantis_bugnote_table.bug_id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-31', 'Remove zerofill on mantis_bugnote_table.reporter_id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-32', 'Remove zerofill on mantis_bugnote_table.bugnote_text_id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-33', 'Remove zerofill on mantis_bugnote_text_table.id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-34', 'Remove zerofill on mantis_news_table.id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-35', 'Remove zerofill on mantis_news_table.project_id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-36', 'Remove zerofill on mantis_news_table.poster_id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-37', 'Remove zerofill on mantis_project_category_table.project_id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-38', 'Remove zerofill on mantis_project_file_table.id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-39', 'Remove zerofill on mantis_project_file_table.project_id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-40', 'Remove zerofill on mantis_project_table.id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-41', 'Remove zerofill on mantis_project_user_list_table.project_id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-42', 'Remove zerofill on mantis_project_user_list_table.user_id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-43', 'Remove zerofill on mantis_project_version_table.project_id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-44', 'Remove zerofill on mantis_user_pref_table.id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-45', 'Remove zerofill on mantis_user_pref_table.user_id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-46', 'Remove zerofill on mantis_user_pref_table.project_id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-47', 'Remove zerofill on mantis_user_pref_table.default_profile');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-48', 'Remove zerofill on mantis_user_pref_table.default_project');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-49', 'Remove zerofill on mantis_user_print_pref_table.user_id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-50', 'Remove zerofill on mantis_user_profile_table.id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-51', 'Remove zerofill on mantis_user_profile_table.user_id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-jf-52', 'Remove zerofill on mantis_user_table.id');
INSERT INTO mantis_upgrade_table VALUES ('0.17-compat-1', 'Set default for mantis_bug_file_table.date_added (incorrect for 0.15 installs)');
INSERT INTO mantis_upgrade_table VALUES ('0.17-compat-2', 'Correct values for mantis_bug_file_table.date_added (incorrect for 0.15 installs)');
INSERT INTO mantis_upgrade_table VALUES ('0.17-compat-3', 'Set default for mantis_project_file_table.date_added (incorrect for 0.15 installs)');
INSERT INTO mantis_upgrade_table VALUES ('0.17-compat-4', 'Correct values for mantis_project_file_table.date_added (incorrect for 0.15 installs)');
INSERT INTO mantis_upgrade_table VALUES ('0.17-compat-5', 'Set default for mantis_bug_table.build (incorrect for 0.16 installs)');
INSERT INTO mantis_upgrade_table VALUES ('0.17-compat-6', 'Correct values for mantis_bug_table.build (incorrect for 0.16 installs)');
INSERT INTO mantis_upgrade_table VALUES ('0.17-compat-7', 'Set default for mantis_user_table.date_created (incorrect for < 0.17 installs)');
INSERT INTO mantis_upgrade_table VALUES ('0.17-compat-8', 'Correct values for mantis_user_table.date_created (incorrect for < 0.17 installs)');
INSERT INTO mantis_upgrade_table VALUES ('0.17-compat-9', 'Set default for mantis_project_table.enabled to 1 (incorrect for < 0.17 installs)');
INSERT INTO mantis_upgrade_table VALUES ('0.17-compat-10', 'Set default for mantis_news_table.date_posted (incorrect for < 0.17 installs)');
INSERT INTO mantis_upgrade_table VALUES ('0.17-compat-11', 'Correct values for mantis_news_table.date_posted (incorrect for < 0.17 installs)');
INSERT INTO mantis_upgrade_table VALUES ('0.17-compat-12', 'Set default for mantis_bug_table.date_submitted (incorrect for < 0.17 installs)');
INSERT INTO mantis_upgrade_table VALUES ('0.17-compat-13', 'Correct values for mantis_bug_table.date_submitted (incorrect for < 0.17 installs)');
INSERT INTO mantis_upgrade_table VALUES ('0.17-compat-14', 'Set default for mantis_bugnote_table.date_submitted (incorrect for < 0.17 installs)');
INSERT INTO mantis_upgrade_table VALUES ('0.17-compat-15', 'Correct values for mantis_bugnote_table.date_submitted (incorrect for < 0.17 installs)');
INSERT INTO mantis_upgrade_table VALUES ('0.17-compat-16', 'Add unique index to cookie_string if it is not already there (incorrect for > 0.14)');
INSERT INTO mantis_upgrade_table VALUES ('0.17-compat-17', 'Remove mantis_project_version_table.ver_order (incorrect for < 0.15)');
INSERT INTO mantis_upgrade_table VALUES ('0.17-compat-18', 'Remove users from project 0');
INSERT INTO mantis_upgrade_table VALUES ('escaping-fix-1', 'Fix double escaped data in mantis_bug_file_table');
INSERT INTO mantis_upgrade_table VALUES ('escaping-fix-2', 'Fix double escaped data in mantis_bug_table');
INSERT INTO mantis_upgrade_table VALUES ('escaping-fix-3', 'Fix double escaped data in mantis_bug_text_table');
INSERT INTO mantis_upgrade_table VALUES ('escaping-fix-4', 'Fix double escaped data in mantis_bugnote_text_table');
INSERT INTO mantis_upgrade_table VALUES ('escaping-fix-5', 'Fix double escaped data in mantis_news_table');
INSERT INTO mantis_upgrade_table VALUES ('escaping-fix-6', 'Fix double escaped data in mantis_project_file_table');
INSERT INTO mantis_upgrade_table VALUES ('escaping-fix-7', 'Fix double escaped data in mantis_project_table');
INSERT INTO mantis_upgrade_table VALUES ('escaping-fix-8', 'Fix double escaped data in mantis_user_profile_table');
INSERT INTO mantis_upgrade_table VALUES ('0.17-vb-19', 'Add id field to bug history table');
INSERT INTO mantis_upgrade_table VALUES ('escaping-fix-9', 'Fix double escaped data in mantis_bug_history_table');
INSERT INTO mantis_upgrade_table VALUES ('escaping-fix-10', 'Remove history entries where type=0 and the old value = new value.  These existed because of escaping errors');
INSERT INTO mantis_upgrade_table VALUES ('0.18-vb-1', 'Add index on bug_id field in mantis_bug_file_table.');
INSERT INTO mantis_upgrade_table VALUES ('filtersdb-1', 'Add mantis_filters_table');
INSERT INTO mantis_upgrade_table VALUES ('emailsevs-1', 'Add the necessary columns for email severity filtering');
INSERT INTO mantis_upgrade_table VALUES ('sponsorship-1', 'Add sponsorships table');
INSERT INTO mantis_upgrade_table VALUES ('sponsorship-2', 'Add sponsorship_total to bug table');
INSERT INTO mantis_upgrade_table VALUES ('sponsorship-3', 'Add an index on sponsorship_total in bug table');
INSERT INTO mantis_upgrade_table VALUES ('fixed_in_version-1', 'Add fixed_in_version field to bug table.');
INSERT INTO mantis_upgrade_table VALUES ('fixed_in_version-2', 'Add index on fixed_in_version field in bug table.');
INSERT INTO mantis_upgrade_table VALUES ('user_realname', 'Add real name to user information.');
INSERT INTO mantis_upgrade_table VALUES ('custom_fields-1', 'Allow custom fields to be set/required for resolve/close/report/update');
INSERT INTO mantis_upgrade_table VALUES ('custom_fields-2', 'Allow custom fields to be set/required for resolve/close/report/update');
INSERT INTO mantis_upgrade_table VALUES ('custom_fields-3', 'Allow custom fields to be set/required for resolve/close/report/update');
INSERT INTO mantis_upgrade_table VALUES ('custom_fields-4', 'Allow custom fields to be set/required for resolve/close/report/update');
INSERT INTO mantis_upgrade_table VALUES ('custom_fields-5', 'Allow custom fields to be set/required for resolve/close/report/update');
INSERT INTO mantis_upgrade_table VALUES ('custom_fields-6', 'Allow custom fields to be set/required for resolve/close/report/update');
INSERT INTO mantis_upgrade_table VALUES ('version_remove_pk', 'Remove project_id+version primary key');
INSERT INTO mantis_upgrade_table VALUES ('version_add_version_', 'Add id to version table and use it as primary key');
INSERT INTO mantis_upgrade_table VALUES ('version_add_project_', 'Add a unique index for project_id + version combination.');
INSERT INTO mantis_upgrade_table VALUES ('version_add_descript', 'Add description field to versions.');
INSERT INTO mantis_upgrade_table VALUES ('version_add_released', 'Add released flag to determine whether the version was released or still a future release.');
INSERT INTO mantis_upgrade_table VALUES ('relationship-1', 'Add index on source_bug_id field in mantis_bug_relationship_table');
INSERT INTO mantis_upgrade_table VALUES ('relationship-2', 'Add index on destination_bug_id field in mantis_bug_relationship_table');
INSERT INTO mantis_upgrade_table VALUES ('relationship-3', 'Translate duplicate id information in a new duplicate relationship');
INSERT INTO mantis_upgrade_table VALUES ('relationship-4', 'Fix swapped value in duplicate relationship');
INSERT INTO mantis_upgrade_table VALUES ('cat_user_id_unsigned', 'Change the user_id in mantis_project_category_table to unsigned int.');
INSERT INTO mantis_upgrade_table VALUES ('custom_fields-7', 'Allow custom fields to be hidden/displayed for report/update');
INSERT INTO mantis_upgrade_table VALUES ('custom_fields-8', 'Allow custom fields to be hidden/displayed for report/update');
INSERT INTO mantis_upgrade_table VALUES ('custom_fields-9', 'Rename Column');
INSERT INTO mantis_upgrade_table VALUES ('custom_fields-10', 'Rename Column');
INSERT INTO mantis_upgrade_table VALUES ('custom_fields-11', 'Rename Column');
INSERT INTO mantis_upgrade_table VALUES ('custom_fields-12', 'Rename Column');
INSERT INTO mantis_upgrade_table VALUES ('custom_fields-13', 'Rename Column');
INSERT INTO mantis_upgrade_table VALUES ('custom_fields-14', 'Rename Column');
INSERT INTO mantis_upgrade_table VALUES ('custom_fields-15', 'Rename Column');
INSERT INTO mantis_upgrade_table VALUES ('custom_fields-16', 'Rename Column');
INSERT INTO mantis_upgrade_table VALUES ('custom_fields-17', 'Rename Column');
INSERT INTO mantis_upgrade_table VALUES ('custom_fields-18', 'Rename Column');
INSERT INTO mantis_upgrade_table VALUES ('custom_fields-19', 'Rename Column');
INSERT INTO mantis_upgrade_table VALUES ('custom_fields-20', 'Rename Column');
INSERT INTO mantis_upgrade_table VALUES ('lost-password', 'Add the necessary columns for managing lost passwords');
INSERT INTO mantis_upgrade_table VALUES ('delete-admin-over', 'Delete any project level access overrides for admin users');
INSERT INTO mantis_upgrade_table VALUES ('0.18-bugnote-limit', 'Add email_bugnote_limit to user preference table');
INSERT INTO mantis_upgrade_table VALUES ('0.18-bugnote-order', 'Add bugnote_order to user preference table');
INSERT INTO mantis_upgrade_table VALUES ('cb_ml_upgrade', 'Upgrade custom field types (checkbox, list, multilist) to support advanced filtering');
INSERT INTO mantis_upgrade_table VALUES ('bugnote-type', 'Add note type column to bugnote');
INSERT INTO mantis_upgrade_table VALUES ('bugnote-attr', 'Add note_attr column to bugnote');

# --------------------------------------------------------

#
# Table structure for table `mantis_user_pref_table`
#

CREATE TABLE mantis_user_pref_table (
  id int(7) unsigned NOT NULL auto_increment,
  user_id int(7) unsigned NOT NULL default '0',
  project_id int(7) unsigned NOT NULL default '0',
  default_profile int(7) unsigned NOT NULL default '0',
  default_project int(7) unsigned NOT NULL default '0',
  advanced_report int(1) NOT NULL default '0',
  advanced_view int(1) NOT NULL default '0',
  advanced_update int(1) NOT NULL default '0',
  refresh_delay int(4) NOT NULL default '0',
  redirect_delay int(1) NOT NULL default '0',
  bugnote_order varchar(4) NOT NULL default 'ASC',
  email_on_new int(1) NOT NULL default '0',
  email_on_assigned int(1) NOT NULL default '0',
  email_on_feedback int(1) NOT NULL default '0',
  email_on_resolved int(1) NOT NULL default '0',
  email_on_closed int(1) NOT NULL default '0',
  email_on_reopened int(1) NOT NULL default '0',
  email_on_bugnote int(1) NOT NULL default '0',
  email_on_status int(1) NOT NULL default '0',
  email_on_priority int(1) NOT NULL default '0',
  email_on_priority_minimum_severity int(2) NOT NULL default '10',
  email_on_status_minimum_severity int(2) NOT NULL default '10',
  email_on_bugnote_minimum_severity int(2) NOT NULL default '10',
  email_on_reopened_minimum_severity int(2) NOT NULL default '10',
  email_on_closed_minimum_severity int(2) NOT NULL default '10',
  email_on_resolved_minimum_severity int(2) NOT NULL default '10',
  email_on_feedback_minimum_severity int(2) NOT NULL default '10',
  email_on_assigned_minimum_severity int(2) NOT NULL default '10',
  email_on_new_minimum_severity int(2) NOT NULL default '10',
  email_bugnote_limit int(2) NOT NULL default '0',
  language varchar(32) NOT NULL default 'english',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

#
# Dumping data for table `mantis_user_pref_table`
#


# --------------------------------------------------------

#
# Table structure for table `mantis_user_print_pref_table`
#

CREATE TABLE mantis_user_print_pref_table (
  user_id int(7) unsigned NOT NULL default '0',
  print_pref varchar(27) NOT NULL default '',
  PRIMARY KEY  (user_id)
) TYPE=MyISAM;

#
# Dumping data for table `mantis_user_print_pref_table`
#


# --------------------------------------------------------

#
# Table structure for table `mantis_user_profile_table`
#

CREATE TABLE mantis_user_profile_table (
  id int(7) unsigned NOT NULL auto_increment,
  user_id int(7) unsigned NOT NULL default '0',
  platform varchar(32) NOT NULL default '',
  os varchar(32) NOT NULL default '',
  os_build varchar(32) NOT NULL default '',
  description text NOT NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

#
# Dumping data for table `mantis_user_profile_table`
#


# --------------------------------------------------------

#
# Table structure for table `mantis_user_table`
#

CREATE TABLE mantis_user_table (
  id int(7) unsigned NOT NULL auto_increment,
  username varchar(32) NOT NULL default '',
  realname varchar(64) NOT NULL default '',
  email varchar(64) NOT NULL default '',
  password varchar(32) NOT NULL default '',
  date_created datetime NOT NULL default '1970-01-01 00:00:01',
  last_visit datetime NOT NULL default '1970-01-01 00:00:01',
  enabled int(1) NOT NULL default '1',
  protected int(1) NOT NULL default '0',
  access_level int(2) NOT NULL default '10',
  login_count int(11) NOT NULL default '0',
  lost_password_in_progress_count int(2) NOT NULL default '0',
  failed_login_count int(2) NOT NULL default '0',
  cookie_string varchar(64) NOT NULL default '',
  PRIMARY KEY  (id),
  UNIQUE KEY cookie_string (cookie_string),
  UNIQUE KEY username (username)
) TYPE=MyISAM;

#
# Dumping data for table `mantis_user_table`
#

INSERT INTO mantis_user_table VALUES (1, 'administrator', '', 'admin', '63a9f0ea7bb98050796b649e85481845', '2003-02-16 02:03:48', '2004-07-08 23:59:22', 1, 1, 90, 3, 0, 0, CONCAT(MD5(RAND()),MD5(NOW())));
