#!/usr/bin/env python

##### head-to-head.py
#
# Show head-to-head records between a button and one or more opponents.

### Imports

# Import stuff from the future.

from __future__ import division

# Import regular stuff.

import ConfigParser
import argparse
import os
import sys

# Import Button Men utilities.

sys.path.append('lib')
import bmutils

### Functions

## parse_arguments

def parse_arguments():

  parser = argparse.ArgumentParser(description="Show head-to-head records between a button and one or more opponents.")

  parser.add_argument('button', help="first button")

  parser.add_argument('opponent',
                      nargs = '+',
                      help = "opponent buttons")

  parser.add_argument('--n-way',
                      action='store_true',
                      help = "compare opponents to each other as well")

  parser.add_argument('--site',
                      default = 'www',
                      help = "site to check ('www' by default)")

  return parser.parse_args()
          
## print_record
#
# Print the record between two buttons.

def print_record(bmconn, button1, button2):

  apicmd = { 'type': 'searchGameHistory',
             'sortColumn': 'lastMove',
             'sortDirection': 'DESC',
             'numberOfResults': 20,
             'page': 1,
             'buttonNameA': button1,
             'buttonNameB': button2,
             'status': 'COMPLETE' }

  result = bmconn._make_request(apicmd)

  # If we don't get an ok result, print some helpful info, and give up.

  if result.status != 'ok':
    print "WARNING: Couldn't get data about {0} vs {1}".format(button1,button2)
    print "Result status: {0}".format(result.status)
    print "Result message: {0}\n".format(result.message)
    return

  # If we're still here, set some variables, figure out the win
  # percentage, and print the answer.

  wins = result.data['summary']['gamesWonA']
  losses = result.data['summary']['gamesWonB']
  games = result.data['summary']['gamesCompleted']

  try:
    winpct = (wins / games) * 100
  except ZeroDivisionError:
    winpct = 0

  print "{0} vs {1}: {2} - {3} ({4:.2f}%)".format(button1, button2, wins, losses, winpct)

### Main body

args = parse_arguments()

# Connect to the site.

try:
  bmconn = bmutils.BMClientParser(os.path.expanduser("~/.bmrc"), args.site)
except ConfigParser.NoSectionError as e:
  print "ERROR: ~/.bmrc doesn't seem to have a '{0}' section".format(args.site)
  print "(Exception: {0}: {1})".format(e.__module__, e.message)
  sys.exit(1)

if not bmconn.verify_login():
  print "ERROR: Could not log in to {0}".format(args.site)
  sys.exit(1)

# Get head-to-head info about each of the opponents.

for opponent in args.opponent:
  print_record (bmconn, args.button, opponent)

# If we're doing an n-way comparison, compare the other pairs too.

if args.n_way:
  opponents = args.opponent
  for button in args.opponent:
    opponents = opponents[1:]
    for opponent in opponents:
      print_record (bmconn, button, opponent)
