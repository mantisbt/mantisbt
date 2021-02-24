echo "Creating database..."
mysql -u root -e "create database bugtracker"
php ./admin/upgrade_unattended.php

# Create API key for REST API testing
mysql -u root bugtracker -e "INSERT INTO mantis_api_token_table (user_id, name, hash, date_created, date_used) VALUES (1, 'test api token', 'test-token', 0, 0)"

# Create Project
mysql -u root bugtracker -e "INSERT INTO mantis_project_table (name, enabled, view_state, access_min, category_id, inherit_global, description) VALUES ('Test', 1, 10, 10, 1, 1, '')"

# Create 3 versions as needed by test cases
mysql -u root bugtracker -e "INSERT INTO mantis_project_version_table (project_id, version, description, released) VALUES (1, 'v1', 'version 1', 1)"
mysql -u root bugtracker -e "INSERT INTO mantis_project_version_table (project_id, version, description, released) VALUES (1, 'v2', 'version 2', 1)"
mysql -u root bugtracker -e "INSERT INTO mantis_project_version_table (project_id, version, description, released) VALUES (1, 'v3', 'version 3', 0)"
