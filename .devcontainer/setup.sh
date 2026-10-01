#!/bin/sh

set -eux

if [ -z ${CODESPACE_NAME+x} ]; then
	SITE_HOST="http://localhost:8080"
else
	SITE_HOST="https://${CODESPACE_NAME}-8080.${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN}"
fi

cd /app/public

if ls wp-content/yacf/*.yml > /dev/null 2>&1; then
	php wp-content/mu-plugins/yacf/build.php
fi

echo "Setting up WordPress at $SITE_HOST"
wp core install --url="$SITE_HOST" --title="WordPress" --admin_user="admin" --admin_email="admin@example.com" --admin_password="password" --skip-email
wp rewrite structure '/%postname%/'

if [ -f wp-content/seed/run.php ]; then
	php wp-content/seed/run.php
fi
