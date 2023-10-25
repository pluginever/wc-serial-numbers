#!/bin/sh
SLUG=$(node -p "require('./package.json').name")
VERSION=$(node -p "require('./package.json').version")
WORKING_DIR=$(pwd)
ZIP_DIR=/tmp/${SLUG}

# If slug is not set, exit
if [ -z "$SLUG" ]; then
	echo "Slug not set. Exiting."
	exit 1
fi
echo "➤ Preparing zip for $VERSION of $SLUG..."

echo "➤ Building plugin..."
composer install
composer update --no-dev --no-scripts
npm install && npm run build
echo "✓ Plugin built!"

# if directory already exists, delete it
if [ -d "$ZIP_DIR" ]; then
	rm -rf $ZIP_DIR
fi

echo "➤ Creating ZIP directory..."
mkdir -p "$ZIP_DIR"
echo "✓ ZIP directory created!"

echo "➤ Copying files..."
# If .distignore file exists, use it to exclude files from the SVN repo, otherwise use the default.
if [[ -r "$WORKING_DIR/.distignore" ]]; then
	echo "ℹ︎ Using .distignore"
	rsync -rc --exclude-from="$WORKING_DIR/.distignore" "$WORKING_DIR/" "$ZIP_DIR/" --delete --delete-excluded
	echo "✓ Files copied!"
fi

# Remove empty directories from trunk
find "$ZIP_DIR/" -type d -empty -delete

ZIP_NAME="$SLUG-v$VERSION.zip"
echo "➤ Creating ZIP file..."
# cd to the one level above the ZIP directory and zip it up
cd "$ZIP_DIR/.." || exit 1
zip -q -r "$ZIP_NAME" "${SLUG}/"
mv "$ZIP_NAME" "$WORKING_DIR/"
echo "✓ ZIP file created!"

echo "➤ Cleaning up..."
rm -rf "$ZIP_DIR"
echo "✓ Done!"
