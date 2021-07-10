echo "Creating database..."
mysql -u root -e "create database bugtracker"
php ./admin/upgrade_unattended.php

# Create API key for REST API testing
mysql -u root bugtracker -e "INSERT INTO mantis_api_token_table (user_id, name, hash, date_created, date_used) VALUES (1, 'test api token', 'test-token', 0, 0)"

# Create Project
mysql -u root bugtracker -e "INSERT INTO mantis_project_table (name, enabled, view_state, access_min, category_id, inherit_global, description) VALUES ('Test Project', 1, 10, 10, 1, 1, 'Test Project')"

# Create 3 versions as needed by test cases
mysql -u root bugtracker -e "INSERT INTO mantis_project_version_table (project_id, version, description, released, obsolete) VALUES (1, '1.0.0', 'Obsolete Version', 1, 1)"
mysql -u root bugtracker -e "INSERT INTO mantis_project_version_table (project_id, version, description, released, obsolete) VALUES (1, '1.1.0', 'Released Version', 1, 0)"
mysql -u root bugtracker -e "INSERT INTO mantis_project_version_table (project_id, version, description, released, obsolete) VALUES (1, '2.0.0', 'Future Version', 0, 0)"

# Create 2 tags as needed by test cases
mysql -u root bugtracker -e "INSERT INTO mantis_tag_table (user_id, name, description, date_created, date_updated) VALUES (0, 'modern-ui', '', 1, 1)"
mysql -u root bugtracker -e "INSERT INTO mantis_tag_table (user_id, name, description, date_created, date_updated) VALUES (0, 'patch', '', 1, 1)"
