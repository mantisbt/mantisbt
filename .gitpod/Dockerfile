FROM gitpod/workspace-mysql

# Install packages

RUN sudo apt-get update

RUN sudo apt-get upgrade -y

RUN sudo apt-get install -y software-properties-common

RUN sudo add-apt-repository ppa:ondrej/php 

RUN sudo apt-get update -y

RUN sudo apt-get install -y php8.0

RUN sudo apt-get install -y libapache2-mod-php8.0

RUN sudo apt-get install -y php8.0-common

RUN sudo apt-get install -y php8.0-gd

RUN sudo apt-get install -y php8.0-curl

RUN sudo apt-get install -y php8.0-soap

RUN sudo apt-get install -y php8.0-mbstring

RUN sudo apt-get install -y php8.0-mysql

RUN sudo apt-get install -y php8.0-cli

RUN sudo apt-get install -y php8.0-dev

RUN sudo rm -rf /var/lib/apt/lists/*

# Setup

RUN sudo systemctl restart apache2

RUN sudo mysql -u root -e "SET PASSWORD FOR root@'localhost' = PASSWORD('root');"

RUN sudo systemctl restart mysql

# Create a database

# Create config_inc.php

# Upgrade the database php admin/upgrade_unattended.php

