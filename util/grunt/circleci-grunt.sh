#!/bin/sh

cd util/grunt
npm install -g grunt-cli
npm install
grunt circleci --no-color
