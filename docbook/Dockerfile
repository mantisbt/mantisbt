# Dockerfile for mantisbt documentation builds with Publican
#
# Copyright 2023  MantisBT Team - mantisbt-dev@lists.sourceforge.net
#
# Usage
# =====
# 1. Generate the Docker image
#    docker build --tag publican .
#
# 2. Create a script to run the Publican container 
#    docker run -t -i --rm -v $PWD:/mnt publican "$@"
#    - file name: 'publican'
#    - save it somewhere on the system PATH, e.g. in ~/bin
#
# 3. Build documentation as usual
#    https://mantisbt.org/wiki/doku.php/mantisbt:docbook

FROM ubuntu

# Install publican
RUN apt update \
    && apt install -y \
        publican \
    && rm -rf /var/lib/apt/lists/*

WORKDIR "/mnt"

ENTRYPOINT ["/usr/bin/publican"]
