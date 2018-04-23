#!/bin/bash
# Build a patch for yacs from the git diff between two states of the repo
# Of course git must be installed and this yacs folder initialized as a git repo.
# 
# One argument must be provided that is a commit sha1 or branch name or git tag
# form with the patch will be created (difference between commit and HEAD, 
# NOT INCLUDING the commit you provide as argument).
#
# The argument SHOULD BE a commit that is already up-to-date on your target server.
# Also, better not to have uncommited change while generating the patch. It may
# result in unexpected updates if a file has a commited and uncommited change cause
# the patch takes the version of the file as it is.
#
# the resulting archive will be placed in /temporary folder with the following pattern :
# patch-<nameofbranche>-<arg-short-sha1>-<head-short-sha1>.zip
#
# Then you could use the patch to update a remote server starting with scripts/stage.php
#
# @author Alexis Raimbault
# @Reference
##

# we must work form yacs'root
cd ..

# check we have a git repo
if [ -e ".git" ]
then echo -e "\e[32m===> Check git repo : OK !\e[39m"
else echo "This is not a yacs install initialized as git repository." ; exit 1;
fi

# test git command
type git > /dev/null && echo -e "\e[32m===> Check git available : OK !\e[39m" || { echo "Git was not found."; exit 1; }

#test if arguments exist
if [ -z "$1" ]
then echo "Please provide a commit/branch/flag git reference as parameter from which you want to generate a patch" ;exit 1
fi

#test if argument is a valid git commit/branch/flag
if [ "$(git rev-parse --quiet --verify $1)" ]
then echo -e "\e[32m===> Check $1 validity : OK !\e[39m"
else echo "$1 is not a valid git commit/branch/flag reference." ; exit 1
fi

# get current branch name
branch=$(git rev-parse --abbrev-ref HEAD)
# get arg short sha1
sha_arg=$(git log --pretty=format:'%h' -n 1 $1)
# get head short sha1
sha_head=$(git log --pretty=format:'%h' -n 1 HEAD)
# build archive name
arch="patch-"$branch"-"$sha_arg"-"$sha_head".zip"

# delete former patch if already exists
if [ -f "temporary/$arch" ]
then rm "temporary/$arch";
fi

# generate archive tree, using a git diff as parameter for git archive
# a filter is provided to not take deleted file into account
# HEAD^ is specified not to take uncommited changes
updatedornew=$(git diff --name-only --diff-filter=ACMRT $1 HEAD^)
echo $updatedornew | tr " " "\n"
# make the archive
git archive -o temporary/$arch HEAD $updatedornew

# check if archive is created
if [ -f "temporary/$arch" ]
then echo -e "\e[32m===> temporary/$arch created !\e[39m"
else echo "Sorry, patch generation failed"; exit 1;
fi 

# check for deleted file since reference argument

deleted="$(git diff --name-only --diff-filter=D $1 )"

if [ -n "$deleted" ] 
then
echo "===> file(s) deletion detected"
echo $deleted | tr " " "\n"
# add a deleted list of deleted file into the archive zip
echo $deleted | tr " " "\n" >> "deleted.list"
zip -ur "temporary/$arch" "deleted.list" > /dev/null
    if [ $? -eq 0 ]
	then
	rm "deleted.list"
	echo -e "\e[32m===> deleting intructions added to patch\e[39m"
	else
	echo "a error occured with zip operation"; exit 1
    fi
fi

## Prepare footprints of the patch
echo '<?php' > "footprints.php"
echo 'global $footprints;' >> "footprints.php"
echo 'if(!isset($footprints)) $footprints=array();' >> "footprints.php"
# transform list of updated file into array
IFS=' ' read -r -a staging <<< $updatedornew
total_size=0
# a line per file
for file in "${staging[@]}"; do
	size=$(wc -c < $file)
	let "total_size+=size"
	md5=$(md5sum $file | awk '{ print $1 }')
	echo '$footprints['"'$file'"']=array('"$size,'$md5'"');' >> "footprints.php"
done
#generation information
echo 'global $generation;' >> "footprints.php"
echo 'if(!isset($generation)) $generation=array();' >> "footprints.php"
echo '$generation'"['date']='$(date -Iseconds)';" >> "footprints.php"
echo '$generation'"['server']='$(hostname) by $(git config --global user.name)';" >> "footprints.php"
rev=$(git describe --tags)
echo '$generation'"['version']='$rev';" >> "footprints.php" 
echo '$generation'"['scripts']=$(wc -w <<< $updatedornew);" >> "footprints.php"
echo '$generation'"['size']=$total_size;" >> "footprints.php" 
echo '$generation'"['deleted']=$(wc -w <<< $deleted);" >> "footprints.php"

#add footprints to archive
zip -ur "temporary/$arch" "footprints.php" > /dev/null
rm "footprints.php"
echo -e "\e[93m===> Patched server revision will be $rev\e[39m"
