#!/usr/bin/env bash

echo "./bin/box build"
./bin/box build
echo "cp ./vagrun.phar /tmp/vagrun"
cp ./vagrun.phar /tmp/vagrun
echo "/tmp/vagrun"
cd /tmp/vagrun

if git status --porcelain | grep .; then
    echo "git add vagrun.phar"
    git add vagrun.phar
    echo "git commit -m '[Release] Released a new version of vagrun.phar'"
    git commit -m "[Release] Released a new version of vagrun.phar"
    echo "git push origin gh-pages"
    git push origin gh-pages
else
    echo "nothing to commit"
fi
