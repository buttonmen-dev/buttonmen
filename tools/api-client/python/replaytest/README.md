# Replay scripts

The scripts in this directory are designed for use in replay testing of new code.  Here's a quick overview:
* `replay_loop` - runs a test loop on a VM containing code under test
* `test_log_games` - play and record new games using RandomAI
* `prep_replay_games` - translate recorded games for PHP responderTest use
* `update_replay_games` - translate recorded games for PHP responderTest use with optional modifications to account for known API changes
* `replay_single_game` - unpack and replay a single game from a previously-recorded archive
See header comments of each individual file for details about that file's use.

These scripts assume they are being executed on a vagrant-built buttonmen server, with various configuration files, target paths, etc, installed.  No effort has been made to ensure that the scripts will work well for any other use case or in any other environment.
