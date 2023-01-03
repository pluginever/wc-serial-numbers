#!/bin/bash

TEST_DIR=/tmp/acceptance-tests
PLUGIN_DIR=$(pwd)
SITE_URL=$(wp option get siteurl)
DOMAIN=$(echo $SITE_URL | awk -F[/:] '{print $4}')
ROOT_FOLDER=$(dirname $(wp config path --extra --allow-root))

# Verify that the tests directory exists otherwise script was run from the wrong directory
if [ ! -d "$PLUGIN_DIR/tests" ]; then
	echo "ERROR: The tests directory does not exist. Please run this script from the plugin root directory."
	exit 1
fi

# Check if db user and password are provided with -u flag and password with -p flag.
while getopts u:p: flag
do
	# shellcheck disable=SC2220
	case "${flag}" in
		u) DB_USER=${OPTARG};;
		p) DB_PASSWORD=${OPTARG};;
	esac
done

# If user name or password is not provided, set root as default.
if [ -z "$DB_USER" ] || [ -z "$DB_PASSWORD" ]
then
	DB_USER=root
	DB_PASSWORD=root
fi

# If directory already exists, delete it
if [ -d "$TEST_DIR" ]; then
	rm -rf $TEST_DIR
fi
echo "➤ Prepare acceptance tests..."
echo "➤ TEST_DIR: $TEST_DIR"
echo "➤ SITE_URL: $SITE_URL"
echo "➤ ROOT_FOLDER: $ROOT_FOLDER"

# dump database file is not exists
if [ ! -f "$PLUGIN_DIR/tests/_data/dump.sql" ]; then
	echo "➤ Creating acceptance tests directory..."
    mkdir -p "$TEST_DIR"
    echo "✓ Acceptance tests directory created!"
    # Install WordPress
    echo "➤ Installing WordPress..."
    mysql -u $DB_USER -p$DB_PASSWORD -e "DROP DATABASE IF EXISTS acceptance_tests" >> /dev/null || exit 1
    wp core download --path="$TEST_DIR" --version=latest >> /dev/null || exit 1
    wp config create --dbname=acceptance_tests --dbuser="$DB_USER" --dbpass="$DB_PASSWORD" --path="$TEST_DIR" >> /dev/null || exit 1
    wp db create --path="$TEST_DIR" >> /dev/null || exit 1
    wp core install --url=acceptance-tests.test --title="Acceptance Tests" --admin_user=admin --admin_password=password --admin_email='manik@pluginever.com' --skip-email --path="$TEST_DIR"  >> /dev/null || exit 1
    wp rewrite structure '/%postname%/' --hard --path="$TEST_DIR" >> /dev/null || exit 1
    wp core update-db --path="$TEST_DIR" >> /dev/null || exit 1
    wp plugin uninstall --all --deactivate --path="$TEST_DIR" >> /dev/null || exit 1
    echo "✓ WordPress installed!"

    # Install WooCommerce
    echo "➤ Installing WooCommerce..."
    wp plugin install woocommerce --activate --path="$TEST_DIR" >> /dev/null || exit 1
    wp plugin install wordpress-importer --activate --path="$TEST_DIR" >> /dev/null || exit 1
    wp wc tool run install_pages --user=admin --path="$TEST_DIR" >> /dev/null || exit 1
    wp import "$TEST_DIR/wp-content/plugins/woocommerce/sample-data/sample_products.xml" --authors=skip --path="$TEST_DIR" >> /dev/null || exit 1
    wp plugin deactivate wordpress-importer --path="$TEST_DIR" >> /dev/null || exit 1
    wp plugin deactivate woocommerce --path="$TEST_DIR" >> /dev/null || exit 1
    echo "✓ WooCommerce installed!"

    # Dump the database and save it to the tests directory
    echo "➤ Dumping database..."
    wp db export "$PLUGIN_DIR/tests/_data/dump.sql" --path="$TEST_DIR" >> /dev/null || exit 1
    echo "✓ Database dumped!"


	# Clean up
	echo "➤ Cleaning up..."
	rm -rf "$TEST_DIR"
	echo "✓ Cleaned up!"
fi


# Copy .dist.env to .env.testing and adjust all the variables
if [ ! -f "$PLUGIN_DIR/.env.testing" ]; then
	echo "➤ Copying .dist.env to .env.testing..."
	rm -rf .env.testing
	cp .dist.env .env.testing
	sed -i '' "s#WP_ROOT_FOLDER=.*#WP_ROOT_FOLDER=$ROOT_FOLDER#g" .env.testing
	sed -i '' "s#TEST_SITE_WP_URL=.*#TEST_SITE_WP_URL=$SITE_URL#g" .env.testing
	sed -i '' "s#TEST_SITE_WP_DOMAIN=.*#TEST_SITE_WP_DOMAIN=$DOMAIN#g" .env.testing
	sed -i '' "s#TEST_SITE_DB_NAME=.*#TEST_SITE_DB_NAME=test_acceptance#g" .env.testing
	sed -i '' "s#TEST_SITE_DB_USER=.*#TEST_SITE_DB_USER=$DB_USER#g" .env.testing
	sed -i '' "s#TEST_SITE_DB_PASSWORD=.*#TEST_SITE_DB_PASSWORD=$DB_PASSWORD#g" .env.testing
	sed -i '' "s#TEST_DB_USER=.*#TEST_DB_USER=$DB_USER#g" .env.testing
	sed -i '' "s#TEST_DB_PASSWORD=.*#TEST_DB_PASSWORD=$DB_PASSWORD#g" .env.testing
	echo "✓ .dist.env copied to .env.testing!"
fi

# Check if codeception.yml exists otherwise create it.
if [ ! -f "$PLUGIN_DIR/codeception.yml" ]; then
	echo "➤ Creating codeception.yml..."
	# Create codeception.yml
	cat > "$PLUGIN_DIR/codeception.yml" <<EOL
params:
    - .env.testing
EOL
	echo "✓ codeception.yml created!"
fi


if ! grep -q "HTTP_X_TEST_REQUEST" "$ROOT_FOLDER/wp-config.php"; then
	echo "➤ Replacing wp-config.php..."
	DB_NAME=$(wp config get DB_NAME)
	sed -i '' "s|define( 'DB_NAME', '.*' );|if( isset( \$_SERVER['HTTP_X_TEST_REQUEST'] ) \&\& \$_SERVER['HTTP_X_TEST_REQUEST'] ){ define( 'DB_NAME', 'test_acceptance'  );}else{define( 'DB_NAME', '$DB_NAME' );}|g" "$ROOT_FOLDER/wp-config.php"
	echo "✓ wp-config.php replaced!"
fi

# Check if tests database exists and create if not using mysql command
echo "➤ Checking if tests database exists..."
if ! mysql -u "$DB_USER" -p"$DB_PASSWORD" -e "use test_acceptance" 2>/dev/null; then
	echo "➤ Creating tests database..."
	mysql -u "$DB_USER" -p"$DB_PASSWORD" -e "CREATE DATABASE test_acceptance" 2>/dev/null || exit 1
	echo "✓ Tests database created!"
else
	echo "✓ Tests database exists!"
fi

# Check if tests database exists and create if not using mysql command
echo "➤ Checking if tests database exists..."
if ! mysql -u "$DB_USER" -p"$DB_PASSWORD" -e "use test_acceptance" 2>/dev/null; then
	echo "➤ Creating tests database..."
	mysql -u "$DB_USER" -p"$DB_PASSWORD" -e "CREATE DATABASE test_acceptance" 2>/dev/null || exit 1
	echo "✓ Tests database created!"
else
	echo "✓ Tests database exists!"
fi

# Generate codeception.yml file
echo "➤ Generating codeception.yml file..."
./vendor/bin/codecept build
echo "✓ codeception.yml file generated!"
