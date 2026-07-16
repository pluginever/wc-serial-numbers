#!/bin/bash

set -e

DB_NAME=${1:-wordpress_test}
DB_USER=${2:-root}
DB_PASS=${3:-''}
DB_HOST=${4:-localhost}
WP_VERSION=${5:-latest}

WP_TESTS_DIR=${WP_TESTS_DIR-/tmp/wordpress-tests-lib}
WP_CORE_DIR=${WP_CORE_DIR-/tmp/wordpress}
WC_DIR="$WP_CORE_DIR/wp-content/plugins/woocommerce"

# prerequisites
for cmd in wp mysql curl unzip; do
	if ! command -v "$cmd" >/dev/null 2>&1; then
		echo "$cmd is required but not installed."
		exit 1
	fi
done

echo "Setting up WordPress test environment..."
# Download WordPress.
if [ ! -f "$WP_CORE_DIR/wp-load.php" ]; then
	echo "Downloading WordPress..."
	if [ "$WP_VERSION" = "latest" ]; then
		wp core download --path="$WP_CORE_DIR" --force --allow-root || exit 1
	else
		wp core download --path="$WP_CORE_DIR" --version="$WP_VERSION" --force --allow-root || exit 1
	fi
fi

# Download test suite matching WordPress version.
if [ ! -d "$WP_TESTS_DIR/includes" ]; then
	echo "Downloading test suite..."
	BRANCH="trunk"
	[ "$WP_VERSION" != "latest" ] && BRANCH="$WP_VERSION"
	rm -rf /tmp/wordpress-develop
	git clone --depth=1 --branch "$BRANCH" https://github.com/WordPress/wordpress-develop.git /tmp/wordpress-develop 2>/dev/null || exit 1
	mkdir -p "$WP_TESTS_DIR"
	cp -r /tmp/wordpress-develop/tests/phpunit/{includes,data} "$WP_TESTS_DIR/" || exit 1
	rm -rf /tmp/wordpress-develop
fi

# Download WooCommerce (the plugin under test requires it to boot).
if [ ! -f "$WC_DIR/woocommerce.php" ]; then
	echo "Downloading WooCommerce..."
	mkdir -p "$WP_CORE_DIR/wp-content/plugins"
	curl -sL "https://downloads.wordpress.org/plugin/woocommerce.zip" -o /tmp/woocommerce.zip || exit 1
	unzip -q -o /tmp/woocommerce.zip -d "$WP_CORE_DIR/wp-content/plugins/" || exit 1
	rm -f /tmp/woocommerce.zip
fi

# Create config file.
cat > "$WP_TESTS_DIR/wp-tests-config.php" << EOF
<?php
define( 'DB_NAME', '$DB_NAME' );
define( 'DB_USER', '$DB_USER' );
define( 'DB_PASSWORD', '$DB_PASS' );
define( 'DB_HOST', '$DB_HOST' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );
define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );
define( 'WP_PHP_BINARY', 'php' );
define( 'WPLANG', '' );
\$table_prefix = 'wptests_';
define( 'WP_DEBUG', true );
define( 'ABSPATH', '$WP_CORE_DIR/' );
EOF

# Create/reset database.
mysql --user="$DB_USER" --password="$DB_PASS" --host="$DB_HOST" -e "DROP DATABASE IF EXISTS $DB_NAME; CREATE DATABASE $DB_NAME;" 2>/dev/null || exit 1

echo "Setup complete."
