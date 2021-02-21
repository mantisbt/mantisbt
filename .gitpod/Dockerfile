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

RUN rm -rf /var/lib/apt/lists/*
