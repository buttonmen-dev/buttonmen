# Buttonmen API Client

> Python 2 support for `replaytest` scripts
>
> Python 3 support for `user` scripts

## Patterns
The current patterns is for the file `bmapi` to contain the actual api call
and `bmutils` to contain a little wrapper that helps to make reading out the response a little easier. 
Most scripts will interface the api THROUGH the bmutils.

## Look into the `future`
It has been requested by a number of script users to add Python 3 support.
The ever important replaytest regression testing is still tooled for Python 2.
So, this module makes use of the internal modules `__future__` and the third-party module `future`. 
These modules help make the code compatible with Python 2 & 3.
See https://python-future.org/compatible_idioms.html

> TODO: restructure directory as a "Installable Single Package" https://realpython.com/python-application-layouts/#installable-single-package
