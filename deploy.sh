#! /bin/bash
# See https://github.com/GaryJones/wordpress-plugin-svn-deploy for instructions and credits.
#
# Steps to deploying:
#
#  1. Check main plugin file exists.
#  2. Check readme.txt version matches main plugin file version.
#  3. Ask if input is correct, and give chance to abort.
#  4. Check if Git tag exists for version number (must match exactly).
#  5. Checkout SVN repo.
#  6. Set to SVN ignore some GitHub-related files.
#  7. Export HEAD of master from git to the trunk of SVN.
#  8. Initialise and update and git submodules.
#  9. Move /trunk/assets up to /assets.
# 10. Move into /trunk, and SVN commit.
# 11. Move into /assets, and SVN commit.
# 12. Copy /trunk into /tags/{version}, and SVN commit.
# 13. Delete temporary local SVN checkout.

echo
echo "WordPress Plugin SVN Deploy v3.0.0"
echo
echo "Let's collect some information first. There are six questions."
echo
echo "Default values are in brackets - just hit enter to accept them."
echo

# Set up some default values. Feel free to change these in your own script
CURRENTDIR=$(pwd)
PLUGINSLUG="external-login"
SVNPATH="/tmp/$PLUGINSLUG"
SVNURL="https://plugins.svn.wordpress.org/$PLUGINSLUG"
SVNUSER="tbenyon"
PLUGINDIRNAME="plugin-files"
PLUGINDIR="$CURRENTDIR/$PLUGINDIRNAME"
MAINFILE="$PLUGINSLUG.php"

# Check directory exists.
if [ ! -d "$PLUGINDIR" ]; then
  echo "Directory $PLUGINDIR not found. Aborting."
  exit 1;
fi

# Check main plugin file exists.
if [ ! -f "$PLUGINDIR/$MAINFILE" ]; then
  echo "Plugin file $PLUGINDIR/$MAINFILE not found. Aborting."
  exit 1;
fi

echo "Checking version in main plugin file matches version in readme.txt file..."
echo

# Check version in readme.txt is the same as plugin file after translating both to Unix line breaks to work around grep's failure to identify Mac line breaks
PLUGINVERSION=$(grep -i "Version:" $PLUGINDIR/$MAINFILE | awk -F' ' '{print $NF}' | tr -d '\r')
echo "$MAINFILE version: $PLUGINVERSION"
READMEVERSION=$(grep -i "Stable tag:" $PLUGINDIR/readme.txt | awk -F' ' '{print $NF}' | tr -d '\r')
echo "readme.txt version: $READMEVERSION"

if [ "$READMEVERSION" = "trunk" ]; then
	echo "Version in readme.txt & $MAINFILE don't match, but Stable tag is trunk. Let's continue..."
elif [ "$PLUGINVERSION" != "$READMEVERSION" ]; then
	echo "Version in readme.txt & $MAINFILE don't match. Exiting...."
	exit 1;
elif [ "$PLUGINVERSION" = "$READMEVERSION" ]; then
	echo "Versions match in readme.txt and $MAINFILE. Let's continue..."
fi

echo

echo "Slug: external-login"
echo "Plugin directory: $PLUGINDIR"
echo "Main file: $MAINFILE"
echo "Temp checkout path: $SVNPATH"
echo "Remote SVN repo: $SVNURL"
echo "SVN username: $SVNUSER"
echo

printf "OK to proceed (Y|n)? "
read -e input
PROCEED="${input:-y}"
echo

# Allow user cancellation
if [ $(echo "$PROCEED" |tr [:upper:] [:lower:]) != "y" ]; then echo "Aborting..."; exit 1; fi

# Let's begin...
echo ".........................................."
echo
echo "Preparing to deploy WordPress plugin"
echo
echo ".........................................."
echo

echo

echo "Changing to $PLUGINDIR"
cd $PLUGINDIR

# Check for git tag (may need to allow for leading "v"?)
# if git show-ref --tags --quiet --verify -- "refs/tags/$PLUGINVERSION"
if git show-ref --tags --quiet --verify -- "refs/tags/$PLUGINVERSION"
	then
		echo "Git tag $PLUGINVERSION does exist. Let's continue..."
	else
		echo "$PLUGINVERSION does not exist as a git tag. Aborting.";
		exit 1;
fi

echo

echo "Creating local copy of SVN repo trunk..."
svn checkout $SVNURL $SVNPATH --depth immediates
svn update --quiet $SVNPATH/trunk --set-depth infinity

echo "Ignoring GitHub specific files"
svn propset svn:ignore "README.md
Thumbs.db
.github/*
.git
.gitattributes
.gitignore" "$SVNPATH/trunk/"

echo
echo "Replace SNV Trunk with new deployment files"
rm -r $SVNPATH/trunk/*
cp -r * $SVNPATH/trunk/

echo

# Support for the /assets folder on the .org repo.
echo "Moving assets."
# Make the directory if it doesn't already exist
mkdir -p $SVNPATH/assets/
mv $SVNPATH/trunk/assets/* $SVNPATH/assets/
svn add --force $SVNPATH/assets/
svn delete --force $SVNPATH/trunk/assets

echo

echo "Changing directory to SVN and committing to trunk."
cd $SVNPATH/trunk/
# Delete all files that should not now be added.
svn status | grep -v "^.[ \t]*\..*" | grep "^\!" | awk '{print $2"@"}' | xargs svn del
# Add all new files that are not set to be ignored
svn status | grep -v "^.[ \t]*\..*" | grep "^?" | awk '{print $2"@"}' | xargs svn add
svn commit --username=$SVNUSER -m "Preparing for $PLUGINVERSION release"

echo -e "\nUpdating WordPress plugin repo assets and committing."
cd $SVNPATH/assets/
# Delete all new files that are not set to be ignored
svn status | grep -v "^.[ \t]*\..*" | grep "^\!" | awk '{print $2"@"}' | xargs svn del
# Add all new files that are not set to be ignored
svn status | grep -v "^.[ \t]*\..*" | grep "^?" | awk '{print $2"@"}' | xargs svn add
svn update --quiet --accept working $SVNPATH/assets/*
svn commit --username=$SVNUSER -m "Updating assets"

echo

echo "Creating new SVN tag and committing it."
cd $SVNPATH
svn copy --quiet trunk/ tags/$PLUGINVERSION/
# Remove assets and trunk directories from tag directory
svn delete --force --quiet $SVNPATH/tags/$PLUGINVERSION/assets
svn delete --force --quiet $SVNPATH/tags/$PLUGINVERSION/trunk
svn update --quiet --accept working $SVNPATH/tags/$PLUGINVERSION
cd $SVNPATH/tags/$PLUGINVERSION
svn commit --username=$SVNUSER -m "Tagging version $PLUGINVERSION"

echo

echo "Removing temporary directory $SVNPATH."
#cd $SVNPATH
#cd ..
#rm -fr $SVNPATH/

echo "$SVNPATH"
echo "*** FIN ***"
