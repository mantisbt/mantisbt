# phpMyAdmin MySQL-Dump
# http://phpwizard.net/phpMyAdmin/
#
# Host: localhost Database : bugtracker

# --------------------------------------------------------
#
# Table structure for table 'mantis_bug_file_table'
#

CREATE TABLE mantis_bug_file_table (
   id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL auto_increment,
   bug_id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
   title varchar(250) NOT NULL,
   description varchar(250) NOT NULL,
   diskfile varchar(250) NOT NULL,
   filename varchar(250) NOT NULL,
   folder varchar(250) NOT NULL,
   filesize int(11) DEFAULT '0' NOT NULL,
   date_added datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
   content blob NOT NULL,
   PRIMARY KEY (id)
);


# --------------------------------------------------------
#
# Table structure for table 'mantis_bug_table'
#

CREATE TABLE mantis_bug_table (
   id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL auto_increment,
   project_id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
   reporter_id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
   handler_id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
   duplicate_id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
   priority int(2) DEFAULT '30' NOT NULL,
   severity int(2) DEFAULT '50' NOT NULL,
   reproducibility int(2) DEFAULT '10' NOT NULL,
   status int(2) DEFAULT '10' NOT NULL,
   resolution int(2) DEFAULT '10' NOT NULL,
   projection int(2) DEFAULT '10' NOT NULL,
   category varchar(32) NOT NULL,
   date_submitted datetime,
   last_updated timestamp(14),
   eta int(2) DEFAULT '10' NOT NULL,
   bug_text_id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
   os varchar(32) NOT NULL,
   os_build varchar(16) NOT NULL,
   platform varchar(32) NOT NULL,
   version varchar(32) NOT NULL,
   build tinyint(4) DEFAULT '0' NOT NULL,
   votes tinyint(4) DEFAULT '0' NOT NULL,
   profile_id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
   summary varchar(128) NOT NULL,
   PRIMARY KEY (id)
);


# --------------------------------------------------------
#
# Table structure for table 'mantis_bug_text_table'
#

CREATE TABLE mantis_bug_text_table (
   id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL auto_increment,
   description text NOT NULL,
   steps_to_reproduce text NOT NULL,
   additional_information text NOT NULL,
   PRIMARY KEY (id)
);


# --------------------------------------------------------
#
# Table structure for table 'mantis_bugnote_table'
#

CREATE TABLE mantis_bugnote_table (
   id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL auto_increment,
   bug_id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
   reporter_id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
   bugnote_text_id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
   date_submitted datetime,
   last_modified timestamp(14),
   PRIMARY KEY (id)
);


# --------------------------------------------------------
#
# Table structure for table 'mantis_bugnote_text_table'
#

CREATE TABLE mantis_bugnote_text_table (
   id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL auto_increment,
   note text NOT NULL,
   PRIMARY KEY (id)
);


# --------------------------------------------------------
#
# Table structure for table 'mantis_news_table'
#

CREATE TABLE mantis_news_table (
   id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL auto_increment,
   project_id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
   poster_id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
   date_posted datetime,
   last_modified timestamp(14),
   headline varchar(64) NOT NULL,
   body text NOT NULL,
   PRIMARY KEY (id),
   KEY id (id)
);


# --------------------------------------------------------
#
# Table structure for table 'mantis_project_category_table'
#

CREATE TABLE mantis_project_category_table (
   project_id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
   category varchar(32) NOT NULL
);

INSERT INTO mantis_project_category_table (project_id, category) VALUES ( '0000001', 'bugtracker');
INSERT INTO mantis_project_category_table (project_id, category) VALUES ( '0000001', 'other');
INSERT INTO mantis_project_category_table (project_id, category) VALUES ( '0000001', 'security');

# --------------------------------------------------------
#
# Table structure for table 'mantis_project_file_table'
#

CREATE TABLE mantis_project_file_table (
   id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL auto_increment,
   project_id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
   title varchar(250) NOT NULL,
   description varchar(250) NOT NULL,
   diskfile varchar(250) NOT NULL,
   filename varchar(250) NOT NULL,
   folder varchar(250) NOT NULL,
   filesize int(11) DEFAULT '0' NOT NULL,
   date_added datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
   content blob NOT NULL,
   PRIMARY KEY (id)
);


# --------------------------------------------------------
#
# Table structure for table 'mantis_project_table'
#

CREATE TABLE mantis_project_table (
   id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL auto_increment,
   name varchar(128) NOT NULL,
   status int(2) DEFAULT '10' NOT NULL,
   enabled int(1) DEFAULT '0' NOT NULL,
   view_state int(2) DEFAULT '10' NOT NULL,
   access_min int(2) DEFAULT '10' NOT NULL,
   file_path varchar(250) NOT NULL,
   description text NOT NULL,
   PRIMARY KEY (id),
   KEY id (id),
   UNIQUE name (name)
);

INSERT INTO mantis_project_table (id, name, status, enabled, view_state, description) VALUES ( '0000001', 'mantis', 'development', '1', '10', 'Mantis.  Report problems with the actual bug tracker here. (Do not remove this account.  You can set it to be disabled if you do not wish to see it)');

# --------------------------------------------------------
#
# Table structure for table 'mantis_project_user_list_table'
#

CREATE TABLE mantis_project_user_list_table (
   project_id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
   user_id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
   access_level int(2) DEFAULT '10' NOT NULL
);

INSERT INTO mantis_project_user_list_table (project_id, user_id, access_level) VALUES ('0000000','0000000','00');

# --------------------------------------------------------
#
# Table structure for table 'mantis_project_version_table'
#

CREATE TABLE mantis_project_version_table (
   project_id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
   version varchar(32) NOT NULL
);

INSERT INTO mantis_project_version_table (project_id, version) VALUES ( '0000001', '0.14.0');

# --------------------------------------------------------
#
# Table structure for table 'mantis_user_pref_table'
#

CREATE TABLE mantis_user_pref_table (
   id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL auto_increment,
   user_id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
   default_profile int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
   default_project int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
   advanced_report int(1) DEFAULT '0' NOT NULL,
   advanced_view int(1) DEFAULT '0' NOT NULL,
   advanced_update int(1) DEFAULT '0' NOT NULL,
   refresh_delay int(4) DEFAULT '0' NOT NULL,
   redirect_delay int(1) DEFAULT '0' NOT NULL,
   email_on_new int(1) DEFAULT '0' NOT NULL,
   email_on_assigned int(1) DEFAULT '0' NOT NULL,
   email_on_feedback int(1) DEFAULT '0' NOT NULL,
   email_on_resolved int(1) DEFAULT '0' NOT NULL,
   email_on_closed int(1) DEFAULT '0' NOT NULL,
   email_on_reopened int(1) DEFAULT '0' NOT NULL,
   email_on_bugnote int(1) DEFAULT '0' NOT NULL,
   email_on_status int(1) DEFAULT '0' NOT NULL,
   email_on_priority int(1) DEFAULT '0' NOT NULL,
   language varchar(16) DEFAULT 'english' NOT NULL,
   PRIMARY KEY (id)
);

INSERT INTO mantis_user_pref_table (id, user_id, advanced_report, advanced_view) VALUES ( '0000001', '0000001', '', '');

# --------------------------------------------------------
#
# Table structure for table 'mantis_user_profile_table'
#

CREATE TABLE mantis_user_profile_table (
   id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL auto_increment,
   user_id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
   platform varchar(32) NOT NULL,
   os varchar(32) NOT NULL,
   os_build varchar(16) NOT NULL,
   description text NOT NULL,
   PRIMARY KEY (id)
);


# --------------------------------------------------------
#
# Table structure for table 'mantis_user_table'
#

CREATE TABLE mantis_user_table (
   id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL auto_increment,
   username varchar(32) NOT NULL,
   email varchar(64) NOT NULL,
   password varchar(32) NOT NULL,
   date_created datetime,
   last_visit timestamp(14),
   enabled int(1) DEFAULT '1' NOT NULL,
   protected int(1) DEFAULT '0' NOT NULL,
   access_level int(2) DEFAULT '10' NOT NULL,
   login_count int(11) DEFAULT '0' NOT NULL,
   cookie_string varchar(64) NOT NULL,
   PRIMARY KEY (id),
   UNIQUE username (username)
);

INSERT INTO mantis_user_table (id, username, email, password, date_created, last_visit, enabled, protected, access_level, login_count, cookie_string) VALUES( '0000001', 'administrator', 'admin', 'MNYm8SfoJlvIY', NOW(), NOW(), '1', '1', '90', '0', 'MN91uSF/JIhos8bcda8acc2ead8d60749ad019e56b54fadkPGTyoBgNBQf91563adc2f7337f566cc3d2c792bc3c-10728595');
