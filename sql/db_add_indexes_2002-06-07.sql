#
# This SQL adds every index I found a need for in the code
# as of June 7, 2002.
#
# This may address the slowness described in bug #1975
# http://mantisbt.sourceforge.net/mantis/view_bug_advanced_page.php?f_id=0001975
#
# This file should only exist long enough for db_upgrade.sql to
# incorporate these changes in the next published release if
# they are deemed appropriate by the other developers.
# -----------------------------------------------------------
# $Revision: 1.2 $
# $Author: jctrosset $
# $Date: 2002-07-17 15:55:52 $
#
# $Id: db_add_indexes_2002-06-07.sql,v 1.2 2002-07-17 15:55:52 jctrosset Exp $

# mantis_bug_history_table.date_modified
ALTER TABLE `mantis_bug_history_table` ADD INDEX(`date_modified`);

# MANTIS_BUG_TABLE ( 3 changes )
# mantis_bug_table.category
ALTER TABLE `mantis_bug_table` ADD INDEX(`category`);
# mantis_bug_table.date_submitted
ALTER TABLE `mantis_bug_table` ADD INDEX(`date_submitted`);
# mantis_bug_table.reporter_id
ALTER TABLE `mantis_bug_table` ADD INDEX(`reporter_id`);


# mantis_bugnote_table.date_submitted
ALTER TABLE `mantis_bugnote_table` ADD INDEX(`date_submitted`);

# mantis_news_table.date_posted
ALTER TABLE `mantis_news_table` ADD INDEX(`date_posted`);

# MANTIS_PROJECT_CATEGORY_TABLE ( 2 changes )
# mantis_project_category_table.category
ALTER TABLE `mantis_project_category_table` ADD INDEX(`category`);
# mantis_project_category_table.project_id
ALTER TABLE `mantis_project_category_table` ADD INDEX(`project_id`);

# mantis_project_user_list_table.user_id
ALTER TABLE `mantis_project_user_list_table` ADD INDEX(`user_id`);

# MANTIS_PROJECT_VERSION_TABLE ( 2 changes )
# mantis_project_version_table.date_order
ALTER TABLE `mantis_project_version_table` ADD INDEX(`date_order`);
# mantis_project_version_table.project_id
ALTER TABLE `mantis_project_version_table` ADD INDEX(`project_id`);

# mantis_user_table.access_level
ALTER TABLE `mantis_user_table` ADD INDEX(`access_level`);

# mantis_user_print_pref_table (new, for printing prefs)
DROP TABLE IF EXISTS mantis_user_print_pref_table;
CREATE TABLE mantis_user_print_pref_table (
user_id int(7) unsigned zerofill NOT NULL default '0000000',
print_pref varchar(27) NOT NULL default '',
PRIMARY KEY  (user_id)
);