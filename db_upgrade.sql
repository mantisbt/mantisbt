#
#	Run these queries to upgrade your bugtracker
#

#   Here's the basic command to login to your database via telnet/ssh.
#	mysql -u<username> -p<password> <databasename>
#

# =================
# 0.9.x to 0.10.x
# =================

# You will need to add a 'protected' field in the mantis_user_table.
# This command will do the trick:

ALTER TABLE mantis_user_table ADD protected VARCHAR (3) not null;

# =================
# 0.10.2 to 0.11.0
# =================

# inserted feedback as a status

ALTER TABLE mantis_bug_table CHANGE status status ENUM ('new','feedback',
	'acknowledged','confirmed','assigned','resolved') DEFAULT 'new' not null;

# New user profile tables

CREATE TABLE mantis_user_profile_table (
   id int(7) unsigned zerofill DEFAULT '0000000000' NOT NULL auto_increment,
   user_id int(7) unsigned zerofill DEFAULT '0000000000' NOT NULL,
   platform varchar(32) NOT NULL,
   os varchar(32) NOT NULL,
   os_build varchar(16) NOT NULL,
   description text NOT NULL,
   default_profile char(3) NOT NULL,
   PRIMARY KEY (id)
);

# Versions has been changed to be an enum(edit by hand or through the manage section

ALTER TABLE mantis_bug_table CHANGE version version ENUM ('none') not null;

# New table for default user preferences

CREATE TABLE mantis_user_pref_table (
   id int(7) unsigned zerofill DEFAULT '0000000000' NOT NULL auto_increment,
   user_id int(7) unsigned zerofill DEFAULT '0000000000' NOT NULL,
   advanced_report char(3) NOT NULL,
   advanced_view char(3) NOT NULL,
   PRIMARY KEY (id)
);

# =================
# 0.11.x to 0.12.0
# =================

# Add a new field to store profile descriptions

ALTER TABLE mantis_bug_table ADD profile_id INT (7) UNSIGNED ZEROFILL not null AFTER votes

# =================
# 0.12.x to 0.14.0
# =================

#
# Table structure for table 'mantis_projects_table'
#
# Supports multiple projects

CREATE TABLE mantis_project_table (
   id int(7) unsigned zerofill DEFAULT '0000001' NOT NULL auto_increment,
   name varchar(128) NOT NULL,
   status enum('development','release','stable','obsolete') DEFAULT 'development' NOT NULL,
   enabled char(3) NOT NULL,
   view_state set('public','private') DEFAULT 'public' NOT NULL,
   description text NOT NULL,
   PRIMARY KEY (id),
   KEY id (id)
);

INSERT INTO mantis_project_table VALUES ( '0000001', 'mantis', 'development', 'on', 'public', 'Mantis.  Report problems with the actual bug tracker here. (Do not remove this account.  You can set it to be disabled if you do not wish to see it)');

#
# Table structure for table 'mantis_project_category_table'
#

CREATE TABLE mantis_project_category_table (
   project_id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
   category varchar(32) NOT NULL
);

#
# Table structure for table 'mantis_project_version_table'
#

CREATE TABLE mantis_project_version_table (
   project_id int(7) unsigned zerofill DEFAULT '0000000' NOT NULL,
   version varchar(32) NOT NULL
);

# Add new project_id fields for our tables

ALTER TABLE mantis_bug_table ADD project_id INT (7) UNSIGNED ZEROFILL not null AFTER id;
ALTER TABLE mantis_news_table ADD project_id INT (7) UNSIGNED ZEROFILL not null AFTER id;
ALTER TABLE mantis_user_pref_table CHANGE project_id default_project_id INT (7) UNSIGNED ZEROFILL DEFAULT '0000000000' not null;

# make changes to bug_table

ALTER TABLE mantis_bug_table CHANGE category category VARCHAR (32) not null;
ALTER TABLE mantis_bug_table CHANGE version version VARCHAR (32) DEFAULT 'none' not null

# make changes to user_table

ALTER TABLE mantis_user_table ADD login_count INT not null DEFAULT '0' AFTER access_level;
ALTER TABLE mantis_user_table CHANGE access_level access_level ENUM ('viewer','reporter','updater','developer','manager','administrator') DEFAULT 'viewer' not null 