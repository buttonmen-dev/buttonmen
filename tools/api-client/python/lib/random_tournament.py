import pickle
import random
import time

import random_ai

# Mostly valid options, a few invalid ones
def get_tournament_creation_params():
  if random.random() < 0.99: tournament_type = 'Single Elimination'
  else:                      tournament_type = 'foobar'

  n_player = random.choice([4, 8, 16, 32, 64, random.choice(range(0, 34))])
  max_wins = random.choice([1, 2, 3, 4, 5, random.choice([0, 3.5, 6])])
  if random.random() < 0.5:
    description = "a test tournament"
  else:
    description = None
  return tournament_type, n_player, max_wins, description

def is_tournament_creation_valid(tournament_type, n_player, max_wins, description):
  if tournament_type != 'Single Elimination': return False
  if not n_player in [4, 8, 16, 32]: return False
  if not max_wins in [1, 2, 3, 4, 5]: return False
  return True

class PlayerInvalidTournamentTransitionError(ValueError):
  pass

class LoggingBMClient():
  """
  This client instantiates a BM tournament, and randomly plays
  through all games in it, using random_ai.LoggingBMClient() to
  take game actions.

  Its goal is to find bugs in the tournament creation/update
  methods and interface.
  """

  def __init__(self, client_list, random_seed=None):
    self.client_list = client_list
    assert len(self.client_list) > 0
    self.creator_client = self.client_list[0]
    self.button_selection_client = random_ai.ButtonSelectionClient(self.creator_client)
    self.random_seed = random_seed if random_seed else int(time.time())
    self.games = {}
    self.rounds_loaded = 0
    self.loaded_data = None
    random.seed(self.random_seed)
    # Any state that's needed because of things the API isn't going to tell us
    self.state = {
      'players_following_tournament': [ self.creator_client.username, ],
      'players_dismissed_tournament': [],
      'player_flowchart_state': {},
    }
    # Any state that's needed so we can tailor future random decisions based on past ones
    self.decision_state = {
    }
    self.log = []

  def start_tournament_log(self, n):
    self.int_id = '%05d' % n
    self.log = []
    self.log.append({
      'type': 'start',
      'id': self.int_id,
    })

  def finish_tournament_log(self):
    self.log.append({
      'type': 'finish',
    })
    f = open('output/tournament%s.pck' % self.int_id, 'w')
    pickle.dump(self.log, f)
    f.close()

  def bug(self, message):
    self.log.append({
      'type': 'bug',
      'message': message,
    })
    self.finish_tournament_log()
    raise ValueError, message


  def _follow_tournament_update_state(self, username, call_status):
    # If the call didn't succeed, the internal state shouldn't change
    if call_status != 'ok': return
    if username not in self.state['players_following_tournament']:
      self.state['players_following_tournament'].append(username)

  def _unfollow_tournament_update_state(self, username, call_status):
    # If the call didn't succeed, the internal state shouldn't change
    if call_status != 'ok': return
    if username in self.state['players_following_tournament']:
      self.state['players_following_tournament'].remove(username)

  def _dismiss_tournament_update_state(self, username, call_status):
    # If the call didn't succeed, the internal state shouldn't change
    if call_status != 'ok': return
    self.state['players_dismissed_tournament'].append(username)

  def _update_tournament_update_state(self, action, username, call_status):
    # If the call didn't succeed, the internal state shouldn't change
    if call_status != 'ok': return
    # If a player leaves a tournament who was previously following it, they should stop following it
    if action == 'leave' and self._is_player_name_following_tournament(username):
      self.state['players_following_tournament'].remove(username)

  def _is_player_name_in_tournament(self, username):
    if not 'playerDataArray' in self.loaded_data: return False
    return username in [x['playerName'] for x in self.loaded_data['playerDataArray']]

  def _is_player_name_following_tournament(self, username):
    return username in self.state['players_following_tournament']

  def _has_player_name_dismissed_tournament(self, username):
    return username in self.state['players_dismissed_tournament']


  def _is_tournament_join_action_valid(self, player_client, api_method, action, buttons):
    # This function shouldn't be called outside of JOIN_TOURNAMENT
    state = self.get_tournament_state()
    if state != 'JOIN_TOURNAMENT':
      self.bug("Unexpectedly called _is_tournament_join_action_valid(%s, %s, %s, %s) while in non-join state %s" % (
        player_client, api_method, action, buttons, state))

    # The caller should only use action if api_method is updateTournament
    assert (api_method == 'updateTournament' or action is None)

    # Only the creator can cancel a tournament
    if action == 'cancel':
      return player_client.username == self.creator_client.username

    # Only players not yet in the tournament can join, and a button is required
    if action == 'join':
      if not buttons: return False
      if len(buttons) != 1: return False
      return not self._is_player_name_in_tournament(player_client.username)

    # Only players already in the tournament can leave
    if action == 'leave':
      return self._is_player_name_in_tournament(player_client.username)

    # Only players already in the tournament can changeButton, and a button is required
    if action == 'changeButton':
      if not buttons: return False
      if len(buttons) != 1: return False
      return self._is_player_name_in_tournament(player_client.username)

    # Players cannot dismiss an in-progress tournament
    if api_method == 'dismissTournament': return False

    # Anyone can follow a tournament, because the server treats following idempotently
    if api_method == 'followTournament':
      return True

    # Only players who are following a tournament and are not already in it, can unfollow it 
    if api_method == 'unfollowTournament':
      if self._is_player_name_in_tournament(player_client.username): return False
      if self._is_player_name_following_tournament(player_client.username): return True
      return False

    # No other action names are valid
    return False

  def _is_tournament_play_action_valid(self, player_client, api_method, action, buttons):
    # This function shouldn't be called outside of PLAY_GAMES
    state = self.get_tournament_state()
    if state != 'PLAY_GAMES':
      self.bug("Unexpectedly called _is_tournament_play_action_valid(%s, %s, %s, %s) while in non-play-games state %s" % (
        player_client, api_method, action, buttons, state))

    # The caller should only use action if api_method is updateTournament
    assert (api_method == 'updateTournament' or action is None)

    # Updating a tournament shouldn't be allowed; the tournament has started
    if api_method == 'updateTournament': return False

    # Players cannot dismiss an in-progress tournament
    if api_method == 'dismissTournament': return False

    # Any can follow a tournament
    if api_method == 'followTournament':
      return True

    # Only players who are following a tournament and not in it, can unfollow it 
    if api_method == 'unfollowTournament':
      if self._is_player_name_in_tournament(player_client.username): return False
      if self._is_player_name_following_tournament(player_client.username): return True
      return False

    # No other action names are valid
    return False

  def _is_tournament_complete_action_valid(self, player_client, api_method, action, buttons):
    # This function shouldn't be called outside of END_TOURNAMENT
    state = self.get_tournament_state()
    if state != 'END_TOURNAMENT':
      self.bug("Unexpectedly called _is_tournament_complete_action_valid(%s, %s, %s, %s) while in non-complete state %s" % (
        player_client, api_method, action, buttons, state))

    # The caller should only use action if api_method is updateTournament
    assert (api_method == 'updateTournament' or action is None)

    # Updating a tournament shouldn't be allowed; the tournament has started
    if api_method == 'updateTournament': return False

    # Only players who are in a tournament can dismiss it.  Dismissing is idempotent.
    if api_method == 'dismissTournament':
      if not self._is_player_name_in_tournament(player_client.username): return False
      return True

    # No one can follow a completed tournament
    if api_method == 'followTournament':
      return False

    # Only players who are following a tournament, and are not in it, can unfollow it 
    if api_method == 'unfollowTournament':
      if self._is_player_name_in_tournament(player_client.username): return False
      return self._is_player_name_following_tournament(player_client.username)

    # No other action names are valid
    return False

  def _choose_tournament_join_action(self):
    # Choose action, which may or may not be valid, for tournament players to take during the join stage

    # Pick an action
    rnum = random.random()
    api_method = 'updateTournament'
    action = None
    if   rnum < 0.001: action = 'foobar'
    elif rnum < 0.002: action = 'cancel'
    elif rnum < 0.01:  action = 'leave'
    elif rnum < 0.05:  action = 'changeButton'
    elif rnum < 0.70:  action = 'join'
    elif rnum < 0.80:  api_method = 'dismissTournament'
    elif rnum < 0.90:  api_method = 'followTournament'
    else:              api_method = 'unfollowTournament'

    # Pick a player to take the action
    player_client = random.choice(self.client_list)

    # Pick one or two buttons
    rnum = random.random()
    if rnum < 0.75:
      buttons = [ self.button_selection_client.select_button({}) ]
    elif rnum < 0.85:
      buttons = [ self.button_selection_client.select_button({}), self.button_selection_client.select_button({}) ]
    else:
      buttons = None

    is_valid = self._is_tournament_join_action_valid(player_client, api_method, action, buttons)
    return player_client, api_method, action, buttons, is_valid

  def _choose_tournament_play_action(self):
    # Choose action, which may or may not be valid, for tournament players to take during the play stage

    # Pick an action
    # It should almost always be 'play', because most other things aren't valid,
    # and many rounds need to be played to complete a tournament.
    rnum = random.random()
    api_method = 'updateTournament'
    action = None
    if   rnum < 0.001: action = 'foobar'
    elif rnum < 0.002: action = 'cancel'
    elif rnum < 0.003: action = 'leave'
    elif rnum < 0.004: action = 'changeButton'
    elif rnum < 0.005: action = 'join'
    elif rnum < 0.01:  api_method = 'dismissTournament'
    elif rnum < 0.02:  api_method = 'followTournament'
    elif rnum < 0.03:  api_method = 'unfollowTournament'
    # In the play case, just return, because the caller will determine who should play based on actual game membership
    else:
      return None, None, 'play', None, True

    # Pick a player to take the action
    player_client = random.choice(self.client_list)

    # Pick one or two buttons
    rnum = random.random()
    if rnum < 0.75:
      buttons = [ self.button_selection_client.select_button({}) ]
    elif rnum < 0.85:
      buttons = [ self.button_selection_client.select_button({}), self.button_selection_client.select_button({}) ]
    else:
      buttons = None

    is_valid = self._is_tournament_play_action_valid(player_client, api_method, action, buttons)
    return player_client, api_method, action, buttons, is_valid

  def _choose_tournament_complete_action(self):
    # Choose action, which may or may not be valid, for tournament players to take on a completed tournament

    # Pick an action
    rnum = random.random()
    api_method = 'updateTournament'
    action = None
    if   rnum < 0.01: action = 'foobar'
    elif rnum < 0.02: action = 'cancel'
    elif rnum < 0.05: action = 'leave'
    elif rnum < 0.10: action = 'changeButton'
    elif rnum < 0.15: action = 'join'
    elif rnum < 0.70:  api_method = 'dismissTournament'
    elif rnum < 0.85:  api_method = 'followTournament'
    else:              api_method = 'unfollowTournament'

    # Pick a player to take the action
    player_client = random.choice(self.client_list)

    # Pick one or two buttons
    rnum = random.random()
    if rnum < 0.75:
      buttons = [ self.button_selection_client.select_button({}) ]
    elif rnum < 0.85:
      buttons = [ self.button_selection_client.select_button({}), self.button_selection_client.select_button({}) ]
    else:
      buttons = None

    is_valid = self._is_tournament_complete_action_valid(player_client, api_method, action, buttons)
    return player_client, api_method, action, buttons, is_valid

  def _initialize_game_randomai(self, game_id):
    # N.B. These clients aren't correct, but we need them to load the game.  We'll replace them once we have game data
    game_obj = random_ai.LoggingBMClient(self.client_list[0], self.client_list[1])
    game_obj.initialize_precreated_game(game_id, game_id, self.client_list)
    return game_obj

  def record_load_tournament_data(self, loader_client=None):
    # Note: random_ai logs game data as one of 'initialGameData' or 'updatedGameData' at this point in its analogous function.
    # That's only necessary if we want to support regression testing, so skipping it for now.
    if not loader_client:
      loader_client = self.creator_client
    loader_client.login()
    retval = loader_client.load_tournament_data(self.tournament_id)
    self.log.append({
      'type': 'loadTournamentData',
      'retval': retval,
    })

    call_spec = "load_tournament_data(%s)" % self.tournament_id
    if not retval:
      self.bug("%s unexpectedly returned NULL" % (call_spec))
    if not retval.status == 'ok':
      self.bug("%s unexpectedly failed: %s" % (call_spec, retval and retval.message or "NULL"))
    self.loaded_data = retval.data

  def get_tournament_state(self):
    if not self.loaded_data: return None
    return self.loaded_data.get('tournamentState', None)

  def call_tournament_api_method(self, player_client, api_method, action, buttons, is_valid):
    player_client.login()
    if api_method == 'updateTournament':
      retval = player_client.update_tournament(self.tournament_id, action, button_names=buttons)
      if retval: self._update_tournament_update_state(action, player_client.username, retval.status)
      call_spec = "update_tournament(%s, %s, %s) called by %s" % (self.tournament_id, action, buttons, player_client.username)
    elif api_method == 'dismissTournament':
      retval = player_client.dismiss_tournament(self.tournament_id)
      if retval: self._dismiss_tournament_update_state(player_client.username, retval.status)
      call_spec = "dismiss_tournament(%s) called by %s" % (self.tournament_id, player_client.username)
    elif api_method == 'followTournament':
      retval = player_client.follow_tournament(self.tournament_id)
      if retval: self._follow_tournament_update_state(player_client.username, retval.status)
      call_spec = "follow_tournament(%s) called by %s" % (self.tournament_id, player_client.username)
    elif api_method == 'unfollowTournament':
      retval = player_client.unfollow_tournament(self.tournament_id)
      if retval: self._unfollow_tournament_update_state(player_client.username, retval.status)
      call_spec = "unfollow_tournament(%s) called by %s" % (self.tournament_id, player_client.username)
    self.log.append({
      'type': api_method,
      'retval': retval,
      'player': player_client.username,
      'action': action,
      'button_names': buttons,
    })
    if not retval:
      self.bug("%s unexpectedly gave invalid response: %s" % (call_spec, "NULL"))
    if retval.status == 'ok':
      if not is_valid: 
        self.bug("%s succeeded where it should have failed: %s" % (call_spec, retval.message))
    else:
      if is_valid: 
        self.bug("%s failed where it should have succeeded: %s" % (call_spec, retval.message))
    self.record_load_tournament_data()

  def record_create_tournament(self):
    self.creator_client.login()
    tournament_type, n_player, max_wins, description = get_tournament_creation_params()
    retval = self.creator_client.create_tournament(tournament_type, n_player, max_wins, description)
    self.log.append({
      'type': 'createTournament',
      'retval': retval,
      'creator': self.creator_client.username,
      'tournament_type': tournament_type,
      'max_wins': max_wins,
      'description': description,
    })

    is_valid = is_tournament_creation_valid(tournament_type, n_player, max_wins, description)
    call_spec = "create_tournament(%s, %s, %s, %s)" % (tournament_type, n_player, max_wins, description)
    if not retval:
      self.bug("%s unexpectedly gave invalid response: %s" % (call_spec, "NULL"))
    if not retval.status == 'ok':
      if is_valid:
        self.bug("%s unexpectedly failed: %s" % (call_spec, retval and retval.message or "NULL"))
      self.tournament_id = None
    else:
      if not is_valid:
        self.bug("%s unexpectedly succeeded: %s" % (call_spec, retval and retval.message or "NULL"))
      self.tournament_id = retval.data['tournamentId']
      self.rounds_loaded = 0
      self.games = {}
      self.record_load_tournament_data()
      self.state = {
        'players_following_tournament': [ self.creator_client.username, ],
        'players_dismissed_tournament': [],
        'player_flowchart_state': {},
      }
    return self.tournament_id

  def handle_tournament_state_join(self):
    # Handle a tournament in the 'JOIN_TOURNAMENT' state by having a player attempt to join, leave, or cancel the tournament
    player_client, api_method, action, buttons, is_valid = self._choose_tournament_join_action()
    print("JOIN: username=%s, api_method=%s, action=%s, buttons=%s, is_valid=%s" % (player_client.username if player_client else 'None', api_method, action, buttons, is_valid))
    self.call_tournament_api_method(player_client, api_method, action, buttons, is_valid)

  def handle_tournament_state_play(self):
    # Choose an action
    player_client, api_method, action, buttons, is_valid = self._choose_tournament_play_action()

    # All cases except play involve a tournament-level API method
    if api_method is not None:
      print("PLAY: username=%s, api_method=%s, action=%s, buttons=%s, is_valid=%s" % (player_client.username if player_client else 'None', api_method, action, buttons, is_valid))
      self.call_tournament_api_method(player_client, api_method, action, buttons, is_valid)
      return

    # The only action without a tournament-level API method is 'play'
    assert action == 'play'
    # Make sure all games in the most recent round are loaded into state
    if self.rounds_loaded < len(self.loaded_data['gameDataArrayArray']):
      print("...populating game objects for round %d" % len(self.loaded_data['gameDataArrayArray']))
      for game_info in sorted(self.loaded_data['gameDataArrayArray'][-1]):
        game_id = game_info['gameId']
        self.games[game_id] = self._initialize_game_randomai(game_id)
      self.rounds_loaded = len(self.loaded_data['gameDataArrayArray'])      
    # Select an arbitrary game to advance
    if not self.games:
      print("self.games=%s" % self.games)
      print("self.rounds_loaded=%s" % self.rounds_loaded)
      print("self.loaded_data=%s" % self.loaded_data)
      raise ValueError("BUG: no games available to advance")
    game_to_advance = random.choice(self.games.keys())
    # Progress the game, and remove it from the list if it's done
    try:
      print("PLAY: game_to_advance=%s, action=%s, is_valid=%s" % (game_to_advance, action, is_valid))
      retval = self.games[game_to_advance].progress_game_towards_completion()
      self.log.append({
        'type': 'play',
        'game': game_to_advance,
        'retval': retval,
      })
      if retval:
        self.games.pop(game_to_advance)
        self.record_load_tournament_data()
    except Exception as e:
      self.bug("Progressing game %s to completion failed: %s" % (game_to_advance, str(e)))

  def handle_tournament_state_complete(self):
    # Handle a tournament in the 'END_TOURNAMENT' state by having a player attempt to take a tournament action
    player_client, api_method, action, buttons, is_valid = self._choose_tournament_complete_action()
    print("DONE: username=%s, api_method=%s, action=%s, buttons=%s, is_valid=%s" % (player_client.username if player_client else 'None', api_method, action, buttons, is_valid))
    self.call_tournament_api_method(player_client, api_method, action, buttons, is_valid)


  def log_test_tournament(self, n):
    self.loaded_data = None
    self.start_tournament_log(n)
    self.record_create_tournament()
    post_tournament_actions = 0
    while self.get_tournament_state() == 'JOIN_TOURNAMENT':
      self.handle_tournament_state_join()
    while self.get_tournament_state() == 'PLAY_GAMES':
      self.handle_tournament_state_play()
    while self.get_tournament_state() == 'END_TOURNAMENT' and post_tournament_actions < 10:
      self.handle_tournament_state_complete()
      post_tournament_actions += 1
    self.finish_tournament_log()
    return self.tournament_id
