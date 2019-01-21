#!/bin/bash

# args
MSG=${1-'deploy from git'}
MAINFILE="wc-serial-numbers.php" # for version checking

# paths
SRC_DIR=$(git rev-parse --show-toplevel)
DIR_NAME=wc-serail
SVN_DIR=~/svn/wp-plugins/wc-serial-numbers
TRUNK="$SVN_DIR/trunk"
SVNURL="http://plugins.svn.wordpress.org/$DIR_NAME/"
BUILD_DIR="$SRC_DIR/build"
# make sure we're deploying from the right dir
if [ ! -d "$SRC_DIR/.git" ]; then
    echo "$SRC_DIR doesn't seem to be a git repository"
    exit
fi

# check version in readme.txt is the same as plugin file
#READMEVERSION=`grep "Stable tag" $SRC_DIR/readme.txt | awk '{ print $NF}'`
READMEVERSION=`grep "^Stable tag:" $SRC_DIR/readme.txt | awk -F' ' '{print $NF}'`
#PLUGINVERSION=`grep "* Version" $SRC_DIR/$MAINFILE | awk '{ print $NF}'`
PLUGINVERSION=`grep "Version:" $SRC_DIR/$MAINFILE | awk -F' ' '{print $NF}'`
PLUGINNAME=`grep "Plugin Name" $SRC_DIR/$MAINFILE  |  awk -F':' '{print $2}' | tr -d '' `

SVNPLUGINNAME= `grep "Plugin Name" $TRUNK/$MAINFILE  |  awk -F':' '{print $2}' | tr -d '' `
SVNPLUGINVERSION=`grep "Version:" $TRUNK/$MAINFILE | awk -F' ' '{print $NF}'`
echo ".........................................."
echo "Preparing to deploy $PLUGINNAME"
echo
echo "New version: $PLUGINVERSION"
echo
echo "Previous version: $SVNPLUGINVERSION"
echo
echo ".........................................."
echo
if [ "$PLUGINVERSION" == "$SVNPLUGINVERSION" ]
 then echo "Version in development & svn same. Exiting....";
 exit 1;
fi

echo -e "Did you tagged version(v$SVNPLUGINVERSION):  (y/n)\c"
read TAGGED
if [ "$TAGGED" != "y" ]
 then svn cp -m"tagging v$PLUGINVERSION" https://plugins.svn.wordpress.org/$DIR_NAME/trunk https://plugins.svn.wordpress.org/$DIR_NAME/tags/$SVNPLUGINVERSION;
fi

#remove build dir
echo "Removing Build directory"
rm -r "$SRC_DIR/build" > /dev/null 2>&1
echo "Running release process"
grunt release  > /dev/null 2>&1
echo "Creating build"
grunt build > /dev/null 2>&1

echo ".........................................."

# make sure the destination dir exists
svn mkdir $TRUNK 2> /dev/null
svn add $TRUNK 2> /dev/null

rsync -r --exclude='*.git*' --exclude="node_modules" --exclude="build" --exclude="*.scss*" $BUILD_DIR/* $TRUNK
cd $TRUNK

echo -e"Updating SVN repo \c"
svn up
# check .svnignore
for file in $(cat "$TRUNK/.svnignore" 2>/dev/null)
do

    echo "Removing from svn ignore $file"
    rm -rf $file
done


# svn addremove
svn stat | grep '^\?' | awk '{print $2}' | xargs svn add > /dev/null 2>&1
svn stat | grep '^\!' | awk '{print $2}' | xargs svn rm  > /dev/null 2>&1

svn stat

echo -e "Deploying now"

svn ci -m "Deploy $PLUGINNAME v$PLUGINVERSION"
