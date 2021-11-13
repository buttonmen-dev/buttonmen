# Replay scripts for buttonmen

Several of the scripts in this directory are designed for use in replay testing of new code. Here's
a quick overview:

* `replay_loop` - runs a test loop on a VM containing code under test
* `test_log_games` - play and record new games using RandomAI
* `prep_replay_games` - translate recorded games for PHP responderTest use
* `update_replay_games` - translate recorded games for PHP responderTest use with optional
  modifications to account for known API changes See header comments of each individual file for
  details about that file's use.
