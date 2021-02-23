FROM gitpod/workspace-mysql

USER root

# Install packages

RUN add-apt-repository ppa:ondrej/php 

RUN apt-get update -y

RUN apt-get upgrade -y

RUN apt-get install -y software-properties-common

RUN apt-get install -y php7.4

RUN apt-get install -y php7.4-common

RUN apt-get install -y php7.4-gd

RUN apt-get install -y php7.4-curl

RUN apt-get install -y php7.4-soap

RUN apt-get install -y php7.4-mbstring

RUN apt-get install -y php7.4-mysql

RUN apt-get install -y php7.4-cli

RUN apt-get install -y php7.4-dev

RUN apt-get install -y php7.4-xml

RUN rm -rf /var/lib/apt/lists/*
