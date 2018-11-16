#!/bin/sh

cd util/grunt
./npm_install_grunt
npm install
./node_modules/grunt-cli/bin/grunt circleci --no-color
