#!/bin/bash -e
# -----------------------------------------------------------------------------
# MantisBT CI script - Execute install.php
# -----------------------------------------------------------------------------

# Define parameters for MantisBT installer
declare -A query=(
	[install]=2
	[db_type]=$DB_TYPE
	[hostname]=$DB_HOST
	[database_name]=$DB_NAME
	[db_username]=$DB_USER
	[db_password]=$DB_PASSWORD
	[admin_username]=$DB_USER
	[admin_password]=$DB_PASSWORD
	[timezone]=UTC
	[path]=''
)

# Build http query string
unset query_string
for param in "${!query[@]}"
do
	value=${query[$param]}
	query_string="${query_string}&${param}=${value}"
done

# Trigger installation
curl --data "${query_string:1}" http://$HOSTNAME:$PORT/admin/install.php
