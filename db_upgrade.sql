#
#	Run this to upgrade your bugtracker
#	It might complain if the change was already made but no big deal.
#	Double check any errors to make sure that it wasn't another problem

#
#	mysql -u<username> -p<password> <databasename> < db_upgrade.sql
#

# =================
# 0.9.x to 0.10.x
# =================

# You will need to add a 'protected' field in the mantis_user_table.
# This command will do the trick:

ALTER TABLE mantis_user_table ADD protected VARCHAR (3) not null;

# =================
# 0.10.2 to 0.10.3+
# =================

# inserted feedback as a status

ALTER TABLE mantis_bug_table CHANGE status status ENUM ('new','need info',
	'acknowledged','confirmed','assigned','resolved') DEFAULT 'new' not null;

# New user profile tables

CREATE TABLE mantis_user_profile_table (
   id int(10) unsigned zerofill DEFAULT '0000000000' NOT NULL auto_increment,
   user_id int(10) unsigned zerofill DEFAULT '0000000000' NOT NULL,
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

CREATE TABLE mantis_user_defaults_table (
   id int(10) unsigned zerofill DEFAULT '0000000000' NOT NULL auto_increment,
   user_id int(10) unsigned zerofill DEFAULT '0000000000' NOT NULL,
   hide_resolved char(3) NOT NULL,
   limit_view int(11) DEFAULT '0' NOT NULL,
   show_last char(3) NOT NULL,
   advanced_report char(3) NOT NULL,
   advanced_view char(3) NOT NULL,
   PRIMARY KEY (id)
);

