FROM mcr.microsoft.com/devcontainers/php:0-8.2

# Install xdg-utils, curl and gnupg2
RUN apt-get update && apt-get install -y xdg-utils curl gnupg2

# Add the NodeSource distribution
RUN curl -sL https://deb.nodesource.com/setup_18.x | bash -

# Update packages list, install xdg-utils, node.js, npm and clean up cache in the same command
RUN apt-get update && \
    apt-get install -y nodejs && \
    rm -rf /var/lib/apt/lists/*

# Install the npm packages globally
RUN npm install -g npm \
    && npm install -g \
    @wp-now/wp-now \
    node-gyp  --no-audit --no-fund

# Copy bash profile, if you copy it directly
# you'll lose your colors :-(
COPY ./.bashrc /tmp/.bashrc
RUN cat /tmp/.bashrc >> /home/vscode/.bashrc && \
    rm /tmp/.bashrc

# Copy startup
COPY ./startup.sh /
RUN chmod +x /startup.sh

