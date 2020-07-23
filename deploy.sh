#! /bin/bash
# ----- START EDITING HERE -----
PLUGINSLUG="wc-serial-numbers"
SVNUSER="manikmist09"
# ----- STOP EDITING HERE -----

# Exit if any command fails.
set -e

# Enable nicer messaging for build status.
BLUE_BOLD='\033[1;34m'
GREEN_BOLD='\033[1;32m'
RED_BOLD='\033[1;31m'
YELLOW_BOLD='\033[1;33m'
COLOR_RESET='\033[0m'
error() {
	echo -e "${RED_BOLD}$1${COLOR_RESET}"
}
status() {
	echo -e "${BLUE_BOLD}$1${COLOR_RESET}"
}
success() {
	echo -e "${GREEN_BOLD}$1${COLOR_RESET}"
}
warning() {
	echo -e "${YELLOW_BOLD}$1${COLOR_RESET}"
}

# ASK INFO
status "--------------------------------------------"
status "      Github to WordPress.org RELEASER      "
status "--------------------------------------------"
read -p "TAG AND RELEASE VERSION: " VERSION
status "--------------------------------------------"
status ""
status "Before continuing, confirm that you have done the following :)"
status ""
read -p " - Set version in main file header to "${VERSION}"?"
read -p " - Set version in main file PHP to "${VERSION}"?"
read -p " - Set version in the package.json to "${VERSION}"?"
read -p " - Included update file update-"${VERSION}".php?"
read -p " - Added update file in array for "${VERSION}".php?"
read -p " - Added a changelog for "${VERSION}"?"
read -p " - Updated the POT file?"
read -p " - Committed all changes up to GITHUB?"
status ""
read -p "PRESS [ENTER] TO BEGIN RELEASING "${VERSION}

CURRENTDIR=$(pwd)
SVNPATH="/tmp/$PLUGINSLUG"
SVNURL="https://plugins.svn.wordpress.org/$PLUGINSLUG"
PLUGINDIR="$CURRENTDIR"
MAINFILE="$PLUGINSLUG.php"
ASSETSDIR=".wordpress-org"

# Check if SVN assets directory exists.
if [ ! -d "$PLUGINDIR/$ASSETSDIR" ]; then
	status "SVN assets directory $PLUGINDIR/$ASSETSDIR not found."
	warning "This is not fatal but you may not have intended results."
fi

# Check for git tag (may need to allow for leading "v"?)
# if git show-ref --tags --quiet --verify -- "refs/tags/$VERSION"
if git show-ref --tags --quiet --verify -- "refs/tags/v$VERSION"; then
	status "Git tag $VERSION does exist. Let's continue..."
else
	error "$VERSION does not exist as a git tag. Aborting."
	exit 1
fi

echo
success "💃 Time to release plugin 🕺"
echo
status "Creating local copy of SVN repo trunk..."
rm -rf $SVNPATH
svn checkout $SVNURL $SVNPATH --depth immediates
svn update --quiet $SVNPATH/assets --set-depth infinity
svn update --quiet $SVNPATH/trunk --set-depth infinity
svn update --quiet $SVNPATH/tags/$VERSION --set-depth infinity

status ""
status -p "PRESS [ENTER] TO DEPLOY VERSION "${VERSION}

# UPDATE SVN
status "Updating SVN"
svn update $SVNPATH || {
	echo "Unable to update SVN."
	exit 1
}

status "Replacing trunk"
rm -Rf $SVNPATH/trunk/*

status "Moving to git "
cd $PLUGINDIR
status "Exporting the HEAD of master from git to the trunk of SVN"
git checkout-index -a -f --prefix=$SVNPATH/trunk/

# If submodule exist, recursively check out their indexes
if [ -f ".gitmodules" ]; then
	status "Exporting the HEAD of each submodule from git to the trunk of SVN"
	git submodule init
	git submodule update
	git config -f .gitmodules --get-regexp '^submodule\..*\.path$' |
		while read path_key path; do
			#url_key=$(status $path_key | sed 's/\.path/.url/')
			#url=$(git config -f .gitmodules --get "$url_key")
			#git submodule add $url $path
			status "This is the submodule path: $path"
			status "The following line is the command to checkout the submodule."
			status "git submodule foreach --recursive 'git checkout-index -a -f --prefix=$SVNPATH/trunk/$path/'"
			git submodule foreach --recursive 'git checkout-index -a -f --prefix=$SVNPATH/trunk/$path/'
		done
fi
#install composer
status "Installing PHP dependencies";
cd $SVNPATH/trunk
composer install --no-dev
composer du -o


# Support for the /assets folder on the .org repo, locally this will be /.wordpress-org
status "Moving assets."
# Make the directory if it doesn't already exist
mkdir -p $SVNPATH/assets/
mv $SVNPATH/trunk/.wordpress-org/* $SVNPATH/assets/
svn add --force $SVNPATH/assets/

# REMOVE UNWANTED FILES & FOLDERS
cd $SVNPATH
status "Removing unwanted files"
rm -Rf trunk/assets/css/*.scss
rm -Rf trunk/assets/css/metabox/*.scss
rm -Rf trunk/assets/*.scss
rm -Rf trunk/bin
rm -Rf trunk/**/.gitkeep
rm -Rf trunk/.git
rm -Rf trunk/.babelrc
rm -Rf trunk/yarn.lock
rm -Rf trunk/.github
rm -Rf trunk/.wordpress-org
rm -Rf trunk/.svnignore
rm -Rf trunk/apigen
rm -Rf trunk/tests
rm -f trunk/.coveralls.yml
rm -f trunk/.editorconfig
rm -f trunk/.gitattributes
rm -f trunk/.gitignore
rm -f trunk/.gitmodules
rm -f trunk/.jscrsrc
rm -f trunk/.jshintrc
rm -f trunk/.scrutinizer.yml
rm -f trunk/.stylelintrc
rm -f trunk/.travis.yml
rm -f trunk/apigen.neon
rm -f trunk/CHANGELOG.txt
rm -f trunk/CODE_OF_CONDUCT.md
rm -f trunk/composer.json
rm -f trunk/composer.lock
rm -f trunk/CONTRIBUTING.md
rm -f trunk/docker-compose.yml
rm -f trunk/Gruntfile.js
rm -f trunk/package.json
rm -f trunk/phpcs.xml
rm -f trunk/phpunit.xml
rm -f trunk/phpunit.xml.dist
rm -f trunk/README.md
rm -f trunk/deploy.sh
rm -f trunk/package-lock.json
rm -f trunk/phpcs.xml.dist
rm -f trunk/tmp
rm -f trunk/.phpcs.xml.dist

# DO THE ADD ALL NOT KNOWN FILES UNIX COMMAND
svn add --force * --auto-props --parents --depth infinity -q

svn status | grep -v "^.[ \t]*\..*" | grep "^\!" | awk '{print $2"@"}' | xargs svn del
# Add all new files that are not set to be ignored
svn status | grep -v "^.[ \t]*\..*" | grep "^?" | awk '{print $2"@"}' | xargs svn add

# DO SVN COMMIT
status "Showing SVN status"
svn status

# PROMPT USER
echo ""
printf "CONFIRM TO COMMIT RELEASE (Y|N)? "
echo ""
read -e input
PROCEED="${input:-y}"
echo

# Allow user cancellation
if [ $(echo "$PROCEED" | tr [:upper:] [:lower:]) == "y" ]; then
	status "Pushing..."
	svn commit --username=$SVNUSER -m "Preparing for $VERSION release"
else
	warning "Aboring..."
fi

# PROMPT USER
echo ""
printf "CONFIRM TO UPDATE ASSETS (Y|N)? "
echo ""
read -e input
PROCEED="${input:-y}"
echo ""

# Allow user cancellation
if [ $(echo "$PROCEED" | tr [:upper:] [:lower:]) == "y" ]; then
	status "Updating WordPress plugin repo assets and committing."
	cd $SVNPATH/assets/
	# Delete all new files that are not set to be ignored
	svn status | grep -v "^.[ \t]*\..*" | grep "^\!" | awk '{print $2"@"}' | xargs svn del
	# Add all new files that are not set to be ignored
	svn status | grep -v "^.[ \t]*\..*" | grep "^?" | awk '{print $2"@"}' | xargs svn add
	svn update --quiet --accept working $SVNPATH/assets/*
	svn resolve --accept working $SVNPATH/assets/*
	svn commit --username=$SVNUSER -m "Updating assets"
else
	warning "Aboring assets push ..."
fi

# PROMPT USER
echo ""
printf "CONFIRM TO TAG (Y|N)? "
echo ""
read -e input
PROCEED="${input:-y}"
echo ""

# Allow user cancellation
if [ $(echo "$PROCEED" | tr [:upper:] [:lower:]) == "y" ]; then
	status "Creating new SVN tag and committing it."
	svn cp -m"tagging v$VERSION" https://plugins.svn.wordpress.org/$PLUGINSLUG/trunk https://plugins.svn.wordpress.org/$PLUGINSLUG/tags/$VERSION
else
	warning "Aboring tag..."
fi

status "Removing temporary directory $SVNPATH."

cd $SVNPATH
rm -fr $SVNPATH/

success "*** FIN ***"
