FROM gitpod/workspace-full

RUN apt-get update && \
    apt-get install -y mysql-server php8.0 libapache2-mod-php8.0 php8.0-common php8.0-curl php8.0-curl php8.0-json php8.0-mbstring php8.0-xml php8.0-mysql -y && \
    mysql.server start && \
    mysql -u root -e "SET PASSWORD FOR root@'localhost' = PASSWORD('root');" \
    sudo rm -rf /var/lib/apt/lists/*

# Create a database

# Create config_inc.php

# Upgrade the database php admin/upgrade_unattended.php

