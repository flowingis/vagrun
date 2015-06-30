#!/usr/bin/env bash

./bin/box build

if git status --porcelain | grep .; then
    echo "git add vagrun.phar"
    git add vagrun.phar
    echo "git commit -m '[Release] Released a new version of vagrun.phar'"
    git commit -m "[Release] Released a new version of vagrun.phar"
    echo "git checkout gh-pages"
    git checkout gh-pages
    echo "git checkout master vagrun.phar"
    git checkout master vagrun.phar
    echo "git commit -m '[Release] Released a new version of vagrun.phar'"
    git commit -m "[Release] Released a new version of vagrun.phar"
    echo "git push deploy gh-pages"
    git push deploy gh-pages
else
    echo "nothing to commit"
fi
