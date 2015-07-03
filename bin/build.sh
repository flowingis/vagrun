#!/usr/bin/env bash

COMMIT_MSG=$(git log -1 --pretty=%B)

if [[ $COMMIT_MSG != *"[release]"* ]]
then
    echo "skipping, not a release commit"
    exit 0
fi

VAGRUN_REMOTE_VERSION=$(curl https://raw.githubusercontent.com/ideatosrl/vagrun/gh-pages/version)
VAGRUN_LOCAL_VERSION=$(cat .git/refs/heads/master)

if [ "$VAGRUN_REMOTE_VERSION" = "$VAGRUN_LOCAL_VERSION" ]
then
    echo "release already deployed"
    exit 0
fi

echo "./bin/box build"
./bin/box build

if [ $? -ne 0 ]
then
    echo "box build fails"
    exit 0
fi

echo "cat .git/refs/heads/master > version"
cat .git/refs/heads/master > version

echo "cp ./vagrun.phar /tmp/vagrun"
cp ./vagrun.phar /tmp/vagrun

echo "cp ./version /tmp/vagrun"
cp ./version /tmp/vagrun

echo "cd /tmp/vagrun"
cd /tmp/vagrun

if git status --porcelain | grep .; then
    echo "git add vagrun.phar"
    git add vagrun.phar
    echo "git add version"
    git add version
    echo "git commit -m '[Release] Released a new version of vagrun.phar'"
    git commit -m "[Release] Released a new version of vagrun.phar"
    echo "git push origin gh-pages"
    git push origin gh-pages
else
    echo "nothing to commit"
fi
