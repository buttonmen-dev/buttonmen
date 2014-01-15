#!/bin/sh

cd util/grunt
npm install
grunt jenkins --no-color
