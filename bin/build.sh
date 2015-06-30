#!/usr/bin/env bash

./bin/box build

if git status --porcelain | grep .; then
    git add vagrun.phar
    git commit -m "[Release] Released a new version of vagrun.phar"
    git checkout gh-pages
    git checkout master vagrun.phar
    git commit -m "[Release] Released a new version of vagrun.phar"
    git push deploy master
    git push deploy gh-pages
fi

