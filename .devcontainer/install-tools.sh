#!/bin/sh

set -eux

echo "Installing wp-cli..."
sudo curl -fsSLo /usr/local/bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
sudo chmod +x /usr/local/bin/wp

sudo cp .devcontainer/welcome-message.txt /usr/local/etc/vscode-dev-containers/first-run-notice.txt
