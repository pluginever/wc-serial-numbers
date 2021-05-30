#!/usr/bin/env bash

while [ $# -gt 0 ]; do
  case "$1" in
    --version*|-v*)
      if [[ "$1" != *=* ]]; then shift; fi # Value is next arg if no `=`
      VERSION="${1#*=}"
      ;;
     --slug*|-s*)
      if [[ "$1" != *=* ]]; then shift; fi # Value is next arg if no `=`
      SLUG="${1#*=}"
      ;;
    --help|-h)
      printf "Meaningful help message" # Flag argument
      exit 0
      ;;
    *)
      >&2 printf "Error: Invalid argument\n"
      exit 1
      ;;
  esac
  shift
done

if [ -z "${VERSION}" ]
then
VERSION='develop'
echo "ℹ︎ Version not specified using 'develop' branch"
fi

if [ -z "${SLUG}" ]
then
SLUG=${PWD##*/}
echo "ℹ︎ Slug not specified using '$SLUG'".
fi
ROOT_DIR=$(pwd)
SVN_URL="http://plugins.svn.wordpress.org/${SLUG}/"
SVN_DIR="$(dirname "$(pwd)")/svn-$SLUG"

#remove old directory
if [ -d "$SVN_DIR" ]; then rm -Rf $SVN_DIR; fi


#build steps;
echo "ℹ︎ Building plugin".
npm install & npm run build

#end builds;

# Checkout just trunk and assets for efficiency
echo "➤ Checking out .org repository..."
svn checkout --depth immediates "$SVN_URL" "$SVN_DIR"
# shellcheck disable=SC2164
cd "$SVN_DIR"
svn update --set-depth infinity trunk

# shellcheck disable=SC2164
cd "$ROOT_DIR"

echo "➤ Copying files..."
if [[ -e ".distignore" ]]; then
	echo "ℹ︎ Using .distignore"
	# Copy from current branch to /trunk, excluding dotorg assets
	# The --delete flag will delete anything in destination that no longer exists in source
	rsync -rc --exclude-from="$ROOT_DIR/.distignore" "$ROOT_DIR/" "$SVN_DIR/trunk/" --delete --delete-excluded
fi

cd "$SVN_DIR"
echo "➤ SVN status..."
svn status

echo "➤ Files will be added..."
svn add . --force

echo "➤ Files modified..."
svn status -u | grep M

echo "➤ Files will be removed..."
svn status | grep '^\!' | sed 's/! *//' | xargs -I% svn rm %

echo "➤ Please verify if the build file is correct..."


read -p 'Input Y to remove generated files: ' ACTION
if [ "$ACTION" = 'Y' ]; then rm -Rf $SVN_DIR; fi
