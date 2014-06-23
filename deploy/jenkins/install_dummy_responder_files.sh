#!/bin/bash
##### Install files lighttpd uses to serve dummy responder for jenkins testing

# exit if anything goes wrong
set -e

# echo what the script does
set -x

# target directory
HTTPDIR='/usr/local/share/jenkins-lighttpd/www'

# PHP files
PHPFILES='dummy_responder.php DummyApiResponder.php ApiSpec.php'

# JSON file parent directory name
JSONDIR='dummy_data'

for phpfile in $PHPFILES; do
  /bin/cp -f ./src/api/$phpfile $HTTPDIR/$phpfile
done

/bin/mkdir $HTTPDIR/$JSONDIR

/usr/bin/rsync -av --delete ./src/api/$JSONDIR/ $HTTPDIR/$JSONDIR/
