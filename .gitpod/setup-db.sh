echo "Creating database..."
mysql -u root -e "create database bugtracker"
php ./admin/upgrade_unattended.php
mysql -u root bugtracker -e "INSERT INTO mantis_api_token_table (user_id, name, hash, date_created, date_used) VALUES (1, 'test api token', 'test-token', 10, 0, 0)"
mysql -u root bugtracker -e "INSERT INTO mantis_project_table (name, enabled, view_state, access_min, category_id, inherit_global, description) VALUES ('Test', 1, 10, 10, 1, 1, '')"
