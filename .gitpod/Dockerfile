FROM gitpod/workspace-full

# Install packages

RUN sudo apt-get update

RUN sudo apt-get install -y mysql-server

RUN sudo apt-get install -y php8.0

RUN sudo apt-get install -y libapache2-mod-php8.0

RUN sudo apt-get install -y php8.0-common

RUN sudo apt-get install -y php8.0-curl

RUN sudo apt-get install -y php8.0-json

RUN sudo apt-get install -y php8.0-mbstring

RUN sudo apt-get install -y php8.0-xml

RUN sudo apt-get install -y php8.0-mysql

RUN sudo rm -rf /var/lib/apt/lists/*

# Setup

RUN sudo mysql.server start


RUN sudo mysql -u root -e "SET PASSWORD FOR root@'localhost' = PASSWORD('root');"

# Create a database

# Create config_inc.php

# Upgrade the database php admin/upgrade_unattended.php

