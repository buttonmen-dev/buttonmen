# Here's an example of stuff to copy and paste into an interactive Python
# interpreter to get a connection loaded.
# Or you can load it with 'python -i interactive_mode.py'.

# Set some variables.

bmrc = "~/.bmrc"
site = "www"
bmutilspath = "./lib"

# Import everything, make a connection, and try to log in.

import json
import os
import sys
sys.path.append(os.path.expanduser(bmutilspath).rstrip("/"))
import bmutils
bmconnection = bmutils.BMClientParser(os.path.expanduser(bmrc), site)
if not bmconnection.verify_login():
  print("Could not login")
    
# At this point you can do whatever you want. Here's how to load a game,
# and print its info in nice JSON.

gamenumber = 3038

game = bmconnection.wrap_load_game_data(gamenumber)
print(json.dumps(game, sys.stdout, indent=1, sort_keys=True))
