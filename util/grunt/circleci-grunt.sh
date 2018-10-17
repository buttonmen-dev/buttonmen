#!/bin/sh

cd util/grunt
./npm_install_grunt
npm install
grunt circleci --no-color
