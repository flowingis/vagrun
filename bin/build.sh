#!/usr/bin/env bash

./bin/box build
cp ./vagrun.phar /tmp/vagrun
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
