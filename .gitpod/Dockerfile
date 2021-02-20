FROM gitpod/workspace-mysql

USER root

# Install packages

RUN apt-get update

RUN apt-get upgrade -y

RUN apt-get install -y software-properties-common

RUN add-apt-repository ppa:ondrej/php 

RUN apt-get update -y

RUN apt-get install -y php8.0

RUN apt-get install -y php8.0-common

RUN apt-get install -y php8.0-gd

RUN apt-get install -y php8.0-curl

RUN apt-get install -y php8.0-soap

RUN apt-get install -y php8.0-mbstring

RUN apt-get install -y php8.0-mysql

RUN apt-get install -y php8.0-cli

RUN apt-get install -y php8.0-dev

RUN apt-get install -y php8.0-xml

# RUN apt-get install -y libapache2-mod-php8.0

RUN rm -rf /var/lib/apt/lists/*

# Setup

# RUN sudo systemctl restart apache2

# RUN mysql -u root -e "SET PASSWORD FOR root@'localhost' = PASSWORD('root');"

# RUN sudo systemctl restart mysql

# Create a database

# Create config_inc.php

# Upgrade the database php admin/upgrade_unattended.php

