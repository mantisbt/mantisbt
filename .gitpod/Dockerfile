FROM gitpod/workspace-mysql

USER root

# Install packages

RUN rm /etc/php/7.4/cli/php.ini && \
    rm /etc/php/7.4/apache2/php.ini && \
    add-apt-repository ppa:ondrej/php && \
    apt-get update -y && \
    apt-get install -y \
        software-properties-common \
        php7.4 \
        php7.4-cli \
        php7.4-common \
        php7.4-curl \
        php7.4-dev \
        php7.4-gd \
        php7.4-mbstring \
        php7.4-mysql \
        php7.4-soap \
        php7.4-xml && \
    rm -rf /var/lib/apt/lists/*
