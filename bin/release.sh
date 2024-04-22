#!/bin/sh
# Run this script to release a new version of the plugin to the WordPress.org plugin repository.
# Update the version number in the package.json file, plugin header, and readme.txt file.
# Then run this script to deploy the new version to the WordPress.org plugin repository.

SLUG=$(node -p "require('./package.json').name")
VERSION=$(node -p "require('./package.json').version")
SVN_DIR=/tmp/$SLUG
WORKING_DIR=$(pwd)

# If slug is not set, exit
if [ -z "$SLUG" ]; then
	echo "Slug not set. Exiting."
	exit 1
fi

echo "Preparing release $VERSION for $SLUG..."

# Check if svn user name is provided with -u flag and password with -p flag
while getopts u:p: flag; do
	case "${flag}" in
	u) SVN_USER=${OPTARG} ;;
	p) SVN_PASSWORD=${OPTARG} ;;
	esac
done

# If user name or password is not provided, exit.
if [ -z "$SVN_USER" ] || [ -z "$SVN_PASSWORD" ]; then
	echo "Please provide svn user name and password with -u and -p flags."
	exit 1
fi

# Replace the version in readme.txt
sed -i '' "s/Stable tag: .*/Stable tag: $VERSION/" readme.txt
# Replace the version in plugin file
sed -i '' "s/Version: .*/Version: $VERSION/" $SLUG.php

echo "➤ Building plugin..."
composer install
composer update --no-dev --no-scripts
npm install && npm run build
echo "✓ Plugin built!"

# if directory already exists, delete it
if [ -d "$SVN_DIR" ]; then
	rm -rf $SVN_DIR
fi
echo "➤ Creating SVN directory..."
mkdir -p "$SVN_DIR"
echo "✓ SVN directory created!"

# Checkout the SVN repo
echo "➤ Checking out SVN repo..."
svn checkout --depth immediates "https://plugins.svn.wordpress.org/$SLUG/" "$SVN_DIR" >>/dev/null || exit 1
svn update --set-depth infinity "$SVN_DIR/trunk" >>/dev/null || exit 1
svn update --set-depth infinity "$SVN_DIR/assets" >>/dev/null || exit 1

echo "➤ Copying files..."
# If .distignore file exists, use it to exclude files from the SVN repo, otherwise use the default.
if [[ -r "$WORKING_DIR/.distignore" ]]; then
	echo "ℹ︎ Using .distignore"
	rsync -rc --exclude-from="$WORKING_DIR/.distignore" "$WORKING_DIR/" "$SVN_DIR/trunk/" --delete --delete-excluded
fi

echo "✓ Files copied!"
# Remove empty directories from trunk
find "$SVN_DIR/trunk/" -type d -empty -delete

# Copy assets
# If .wordpress-org is a directory and contains files, copy them to the SVN repo.
if [[ -d "$WORKING_DIR/.wordpress-org" ]]; then
	echo "➤ Copying assets..."
	rsync -rc "$WORKING_DIR/.wordpress-org/" "$SVN_DIR/assets/" --delete --delete-excluded
	# Fix screenshots getting force downloaded when clicking them
	# https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/
	if test -d "$SVN_DIR/assets" && test -n "$(find "$SVN_DIR/assets" -maxdepth 1 -name "*.png" -print -quit)"; then
		svn propset svn:mime-type "image/png" "$SVN_DIR/assets/"*.png || true
	fi
	if test -d "$SVN_DIR/assets" && test -n "$(find "$SVN_DIR/assets" -maxdepth 1 -name "*.jpg" -print -quit)"; then
		svn propset svn:mime-type "image/jpeg" "$SVN_DIR/assets/"*.jpg || true
	fi
	if test -d "$SVN_DIR/assets" && test -n "$(find "$SVN_DIR/assets" -maxdepth 1 -name "*.gif" -print -quit)"; then
		svn propset svn:mime-type "image/gif" "$SVN_DIR/assets/"*.gif || true
	fi
	if test -d "$SVN_DIR/assets" && test -n "$(find "$SVN_DIR/assets" -maxdepth 1 -name "*.svg" -print -quit)"; then
		svn propset svn:mime-type "image/svg+xml" "$SVN_DIR/assets/"*.svg || true
	fi
	echo "✓ Assets copied!"
fi

# If the version is exist and a valid version number, then crate the svn tag.
if [[ -n "$VERSION" ]] && [[ "$VERSION" =~ ^[0-9]+\.[0-9]+\.[0-9]+ ]]; then
	# Versioning
	echo "➤ Versioning..."
	echo "ℹ︎ SVN tag is $VERSION"

	if svn ls "https://plugins.svn.wordpress.org/$SLUG/tags/$VERSION" >>/dev/null 2>&1; then
		echo "ℹ︎ Tag already exists. Pulling files ..."
		svn update --set-depth infinity "$SVN_DIR/tags/$VERSION"
		rsync -rc "$SVN_DIR/trunk/" "$SVN_DIR/tags/$VERSION/" --delete --delete-excluded
		echo "✓ Tag files synced !"
	else
		echo "ℹ︎ Tag does not exist. Creating tag ..."
		svn copy "$SVN_DIR/trunk" "$SVN_DIR/tags/$VERSION" >>/dev/null
		echo "✓ SVN tag created!"
	fi
else
	echo "ℹ︎ Could not find a valid version number, skipping versioning..."
fi

# Update contents.
echo "➤ Updating files ..."
# SVN add new files and remove deleted files from the SVN repo.
cd "$SVN_DIR" || exit
svn add . --force >/dev/null
# SVN delete all deleted files
# Also suppress stdout here
svn status | grep '^\!' | sed 's/! *//' | xargs -I% svn rm %@ >/dev/null
svn update # Fix directory is out of date error
svn status
cd - || exit
echo "✓ Files updated!"

# Check if there are changes to commit.
if [[ -n "$(svn status "$SVN_DIR")" ]]; then
	# Ask for confirmation to commit.
	echo "ℹ︎ There are changes to commit."
	read -r -p "Do you want to commit? [y/N] " response
	if [[ "$response" =~ ^([yY][eE][sS]|[yY])$ ]]; then
		echo "➤ Committing files..."
		svn commit "$SVN_DIR" -m "Release $VERSION" --username "$SVN_USER" --password "$SVN_PASSWORD" --no-auth-cache --non-interactive
		echo "✓ Files committed!"
	else
		echo "ℹ︎ Commit aborted."
	fi
else
	echo "ℹ︎ No changes to commit."
fi
