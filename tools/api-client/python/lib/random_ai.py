import json
import itertools
import pickle
import random
import time
import os
import sys
import string

#####
# CustomBM testing
#
# The code in this section generates valid-ish random recipes for CustomBM.
# * RandomAI doesn't know in advance whether the server will accept a given recipe.
# * If the game is rejected, it should be rejected with a clean error message.
# * If the game is accepted, it should play through without internal
#   errors or stalls like any other game.

VALID_SKILLS = '!#%&+?^`BbcDdFfGgHhIJkMmnopqrstvwz'
VALID_ISH_SKILLS = string.punctuation + string.ascii_letters

VALID_SHORT_SIDES_LIST = range(0, 20) + [30, 'P', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z']
VALID_SIDES_LIST = range(0, 100) + ['P', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z']
VALID_ISH_SIDES_LIST = range(-3, 102) + [x for x in string.ascii_uppercase]

def get_fuzzy_skills():
  if random.random() < 0.9:
    skill_list = [x for x in VALID_SKILLS]
  else:
    skill_list = [x for x in VALID_ISH_SKILLS]
  random.shuffle(skill_list)
  skills_to_use = []
  while random.random() < 0.5:
    skills_to_use.append(skill_list.pop())
    if random.random() < 0.01:
      skills_to_use.append(skills_to_use[-1])
  random.shuffle(skills_to_use)
  return "".join(skills_to_use)

def get_side_max(die_sides):
  SWING_MAX = {
    'P': 30, 'R': 16, 'S': 16, 'T': 12,
    'U': 30, 'V': 12, 'W': 12,
    'X': 20, 'Y': 20, 'Z': 30,
  }
  if die_sides in SWING_MAX: return SWING_MAX[die_sides]
  if isinstance(die_sides, int): return die_sides
  if die_sides in string.ascii_uppercase: return 0
  assert isinstance(die_sides, int), "Expected %s to be an int" % die_sides

def get_fuzzy_sides():
  side_max = 0
  valid_ish = []
  if random.random() < 0.7:
    for x in VALID_SHORT_SIDES_LIST: valid_ish.append(x)
  elif random.random() < 0.7:
    for x in VALID_SIDES_LIST: valid_ish.append(x)
  else:
    for x in VALID_ISH_SIDES_LIST: valid_ish.append(x)
  random.shuffle(valid_ish)
  sides = str(valid_ish[0])
  side_max += get_side_max(valid_ish[0])
  if random.random() < 0.01:
    sides += '.1'
  if random.random() < 0.1:
    new_sides, new_side_max = get_fuzzy_sides()
    sides += ',' + new_sides
    side_max += new_side_max
  elif random.random() < 0.1:
    new_sides, new_side_max = get_fuzzy_sides()
    sides += '/' + new_sides
    side_max = max(side_max, new_side_max)
  return sides, side_max

def get_fuzzy_suffixes():
  valid_ish = [ '!', '&', '?']
  random.shuffle(valid_ish)
  suffixes_to_use = []
  while random.random() < 0.2 and len(suffixes_to_use) > 0:
    suffixes_to_use.append(valid_ish.pop())
    if random.random() < 0.01:
      suffixes_to_use.append(suffixes_to_use[-1])
  random.shuffle(suffixes_to_use)
  return "".join(suffixes_to_use)

def get_fuzzy_die():
  sides, side_max = get_fuzzy_sides()
  return ("%s(%s)%s" % (get_fuzzy_skills(), sides, get_fuzzy_suffixes()), side_max)

# Custom recipes known to trigger bugs not yet addressed by codebase under test
def recipe_contains_unfixed_bug(dice):
  return False

def get_fuzzy_recipe():
  while True:
    dice = []
    side_max = 0
    while not dice or random.random() < 0.75:
      die, die_side_max = get_fuzzy_die()
      dice.append(die)
      side_max += die_side_max
    if dice and random.random() < 0.5:
      possible_repeats = [
        150 / len(" ".join(dice)),
        11 / len(dice),
        200 / (side_max if side_max > 0 else 1),
      ]
      num_repeats = min(possible_repeats)
      if num_repeats > 0:
        dice *= num_repeats
    if recipe_contains_unfixed_bug(dice): continue
    recipe = " ".join(dice)
    return recipe

#####

NUMERIC_KEYS = [
  'activePlayerIdx',
  'currentPlayerIdx',
  'playerWithInitiativeIdx',
  'roundNumber',
  'gameActionLogCount',
  'gameChatLogCount',

  # keys within playerDataArray
  'canStillWin',
  'hasDismissedGame',
  'roundScore',
  'sideScore',
  'waitingOnAction',
  'isOnVacation',
  'isChatPrivate',

  # keys within activeDieArray
  'sides',
  'value',
]

STRING_KEYS = [
  'description',
  'gameState',

  # keys within activeDieArray
  'recipe',
]

UNUSED_DURING_AUTOPLAY_KEYS = [
  'creatorDataArray',
  'gameChatEditable',
  'gameChatLog',
  'gameId',
  'maxWins',
  'previousGameId',
  'tournamentId',
  'tournamentRoundNumber',

  # keys within playerDataArray
  'playerColor',
  'playerId',
  'playerName',
  'dieBackgroundType',
]

# TODO: we shouldn't need to hardcode this, there should always be
# another source of the information - place a TODO anywhere we use this
SWING_RANGES = {
  'P': (1, 30),
  'R': (2, 16),
  'S': (6, 20),
  'T': (2, 12),
  'U': (8, 30),
  'V': (6, 12),
  'W': (4, 12),
  'X': (4, 20),
  'Y': (1, 20),
  'Z': (4, 30),
}

# Because of vagaries of how responderTest writes per-turn API data
# files, it can't handle running loadGameData more than 99 times.
# So if the game contains more than 99 instances of loadGameData,
# just skip the later ones
LOAD_GAMES_DATA_MAX_PER_GAME = 99

def random_array_element(array, return_index=False):
  index = int(random.random() * len(array))
  if return_index:
    return index
  else:
    return array[index]

def min_die_value(die):
  if 'Twin' in die['properties']:
    return sum([
      0 if s['sides'] is 0 else 1 \
      for s in die['subdieArray']])
  return 0 if die['sides'] is 0 else 1

class PHPBMClientOutputWriter():
  """
  This module takes an internal representation of the API call and
  return values of a BM game, and outputs them in PHP format usable by
  test/src/api/responderTest.php for replay tests
  """

  def __init__(self, dirname):
    self.dirname = dirname
    self.log = []
    self.num_load_game_data = 0
    self.player_username = 'responder003'
    self.opponent_username = 'responder004'

  def find_games(self):
    games = []
    for fname in sorted(os.listdir(self.dirname)):
      fpath = '%s/%s' % (self.dirname, fname)
      if os.path.isfile(fpath) and fname.endswith('.pck'):
        games.append(fpath)
    return games

  def translate_game(self, inpath):
    f = open(inpath, 'r')
    self.log = pickle.load(f)
    self.num_load_game_data = 0
    f.close()
    self.f = sys.stdout
    for entry in self.log:
      self._write_log_entry(entry)
    if self.f != sys.stdout:
      self.f.close()

  def _write_log_entry(self, entry):
    write_function = '_write_entry_type_%s' % entry['type']
    if not hasattr(self, write_function):
      raise ValueError("Don't know how to write entry of type %s" % entry['type'])
    getattr(self, write_function)(entry)

  def _write_entry_type_bug(self, entry):
    self.f.write("""
        // BUG encountered while logging game: %s
""" % entry['message'])

  def _write_entry_type_start(self, entry):
    n = int(entry['id'])
    self.f.write("""
    /**
     * @depends responder00Test::test_request_savePlayerInfo
     * @group fulltest_deps
     */
    public function test_interface_game_%s() {

        // %s is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to %s as soon as you've done so
        $this->game_number = %d;
        $_SESSION = $this->mock_test_user_login('%s');

""" % (entry['id'], self.player_username, self.player_username, n, self.player_username))

  def _write_entry_type_finish(self, entry):
    self.f.write("""    }
""")

  def _write_entry_type_login(self, entry):
    self.f.write("\n        $_SESSION = $this->mock_test_user_login('%s');" % entry['user'])

  def _write_entry_type_createGame(self, entry):
    # Random skills may be selected during game creation, so use a dict-style randstr
    # that allows for skill-selection random values
    randstr = self._php_dict_style_rand_str(entry['retval'])
    php_custom_recipe_array = []
    for idx in range(len(entry['custom_recipe_array'])):
      custom_recipe = entry['custom_recipe_array'][idx]
      if not custom_recipe: continue
      php_custom_recipe_array.append("%s => '%s'" % (idx, custom_recipe))
    php_custom_recipe_str = 'array(%s)' % ', '.join(php_custom_recipe_array)
    button1_str = entry['button1'].replace("'", "\\'")
    button2_str = entry['button2'].replace("'", "\\'")
    if entry['retval'].status == 'ok':
      self.f.write("""
          $gameId = $this->verify_api_createGame(
              %s,
              '%s', '%s', '%s', '%s', %d,
              '', NULL, 'gameId', %s
          );
""" % (
        randstr, entry['player1'], entry['player2'],
        button1_str, button2_str,
        entry['max_wins'], php_custom_recipe_str))
    else:
      self.f.write("""
        // Expect game creation to fail
        $args = array(
            'type' => 'createGame',
            'playerInfoArray' => array(array('%s', '%s'),
                                       array('%s', '%s')),
            'maxWins' => '%s',
            'customRecipeArray' => %s,
        );
        $this->verify_api_failure($args, '%s');
""" % (
        entry['player1'], button1_str,
        entry['player2'], button2_str,
        entry['max_wins'], php_custom_recipe_str,
        entry['retval'].message))

  def _write_entry_type_reactToAuxiliary(self, entry):
    randstr = self._php_rand_str(entry['retval'])
    if entry['choice'][0] == 'decline':
      php_choice_str = "'decline'"
    elif entry['choice'][0] == 'add':
      php_choice_str = "'add', %s" % entry['choice'][1]

    self.f.write("""
        $this->verify_api_reactToAuxiliary(
            %s,
            '%s',
            $gameId, %s);
""" % (randstr, self._php_get_message(entry['retval']), php_choice_str))

  def _write_entry_type_reactToReserve(self, entry):
    randstr = self._php_rand_str(entry['retval'])
    if entry['choice'][0] == 'decline':
      php_choice_str = "'decline'"
    elif entry['choice'][0] == 'add':
      php_choice_str = "'add', %s" % entry['choice'][1]
    self.f.write("""
        $this->verify_api_reactToReserve(
            %s,
            '%s',
            $gameId, %s);
""" % (randstr, self._php_get_message(entry['retval']), php_choice_str))

  def _write_entry_type_initialGameData(self, entry):
    data = self.apply_known_changes_to_game_data(entry['data'])
    self.f.write("""
        $expData = $this->generate_init_expected_data_array($gameId, '%s', '%s', %s, '%s');
""" % (self.player_username, self.opponent_username, data['maxWins'], data['gameState']))
    self._write_php_json_diff(data, {})
    self.olddata = data

  def _write_entry_type_updatedGameData(self, entry):
    data = self.apply_known_changes_to_game_data(entry['newdata'])
    self.f.write("\n")
    self._write_php_json_diff(data, self.olddata)
    self.olddata = data

  def _write_entry_type_loadGameData(self, entry):
    self.num_load_game_data += 1
    if self.num_load_game_data > LOAD_GAMES_DATA_MAX_PER_GAME:
      self.f.write("""
        // Skipping $this->verify_api_loadGameData() because it has been run %d times this game.
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10, $check=FALSE);
""" % (self.num_load_game_data))
    else:
      self.f.write("""
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
""")

  def _write_entry_type_adjustFire(self, entry):
    randstr = self._php_rand_str(entry['retval'])
    php_idx_str = 'array(%s)' % (", ".join([str(i) for i in entry['idx_array']]) or "")
    php_value_str = 'array(%s)' % (
      ", ".join(["'%s'" % v for v in entry['value_array']]) or "")
    self.f.write("""
        $this->verify_api_adjustFire(
            %s,
            '%s',
            $retval, $gameId, %s, '%s', %s, %s);
""" % (
      randstr, self._php_get_message(entry['retval']),
      entry['roundNumber'], entry['choice'], php_idx_str, php_value_str))

  def _write_entry_type_reactToInitiative(self, entry):
    randstr = self._php_rand_str(entry['retval'])
    php_idx_str = 'array(%s)' % (", ".join([str(i) for i in entry['idx_array']]) or "")
    php_value_str = 'array(%s)' % (
      ", ".join(["'%s'" % v for v in entry['value_array']]) or "")
    self.f.write("""
        $this->verify_api_reactToInitiative(
            %s,
            '%s', %s,
            $retval, $gameId, %s, '%s', %s, %s);
""" % (
      randstr, self._php_get_message(entry['retval']),
      self._php_json_decode_blob(entry['retval'].data),
      entry['roundNumber'], entry['choice'], php_idx_str, php_value_str))

  def _write_entry_type_submitDieValues(self, entry):
    randstr = self._php_rand_str(entry['retval'])
    php_swing_array = []
    if entry['swing_array']:
      for swing_type, swing_choice in sorted(entry['swing_array'].items()):
        php_swing_array.append("'%s' => %d" % (swing_type, swing_choice))
      php_swing_str = 'array(%s)' % ', '.join(php_swing_array)
    else:
      php_swing_str = 'NULL'

    php_option_array = []
    if entry['option_array']:
      for str_option_idx, option_choice in sorted(entry['option_array'].items()):
        php_option_array.append("%s => %s" % (str_option_idx, option_choice))
      php_option_str = 'array(%s)' % ', '.join(php_option_array)
    else:
      php_option_str = 'NULL'

    self.f.write("""
        $this->verify_api_submitDieValues(
            %s,
            $gameId, %s, %s, %s);
""" % (
      randstr, entry['roundNumber'], php_swing_str,
      php_option_str))

  def _write_entry_type_submitTurn(self, entry):
    randstr = self._php_rand_str(entry['retval'])
    [attack, php_attack_str] = self._generate_php_attack_arrays(
      entry['attacker_indices'], entry['defender_indices'],
      entry['all_attackers'], entry['all_defenders'],
      entry['attacker'], entry['defender'])
    if entry['retval'].status == 'ok':
      php_turbo_vals = 'array(%s)' % ', '.join(['%s => %s' % (k, v) for k, v in sorted(entry['turbo_vals'].items())])
      self.f.write("""
          $this->verify_api_submitTurn(
              %s,
              '%s',
              $retval, %s,
              $gameId, %s, '%s', %d, %d, '', %s);
""" % (
        randstr, self._php_get_message(entry['retval']), php_attack_str,
        entry['roundNumber'], entry['attackType'], entry['attacker'], entry['defender'],
        php_turbo_vals))
    elif entry['retval'].status == 'failed':
      self.f.write("""
          $this->verify_api_submitTurn_failure(
              %s,
              '%s',
              $retval, %s,
              $gameId, %s, '%s', %d, %d, '');
""" % (
        randstr, self._php_get_message(entry['retval']), php_attack_str,
        entry['roundNumber'], entry['attackType'], entry['attacker'], entry['defender']))
    else:
      raise ValueError("_write_entry_type_submitTurn() doesn't implement behavior for retval.status=%s" % entry['retval'].status)

  def _write_entry_type_datachange_numeric(self, entry):
    self.f.write("        $expData%s = %s;\n" % (entry['suffix'], json.dumps(entry['newval'])))

  ######
  def apply_known_changes_to_game_data(self, data):
    # Child classes can override this to enable regression-testing in the
    # presence of known API changes
    return data

  def _php_rand_str(self, retval):
    randstr = ""
    if retval.rand_vals:
      randstr = ", ".join([str(val) for val in retval.rand_vals])
    return "array(%s)" % randstr

  def _php_dict_style_rand_str(self, retval):
    randstr = ""
    if retval.rand_vals:
      randstr = ", ".join([str(val) for val in retval.rand_vals])
    skill_randstr = ""
    if retval.skill_rand_vals:
      skill_randstr = ", ".join([str(val) for val in retval.skill_rand_vals])
    return "array('bm_rand' => array(%s), 'bm_skill_rand' => array(%s))" % (
      randstr, skill_randstr)

  def _php_json_decode_blob(self, obj):
    jsonstr = json.dumps(obj)
    jsonstr = jsonstr.replace("'", "\\'")
    php_str = "json_decode('%s', TRUE)" % jsonstr
    return php_str

  def _write_php_diff_numeric_key(self, suffix, newval, oldval):
    if newval != oldval:
      self.f.write("        $expData%s = %s;\n" % (suffix, json.dumps(newval)))

  def _write_php_diff_string_key(self, suffix, newval, oldval):
    if newval != oldval:
      jsonstr = json.dumps(newval).replace("'", "\\'")
      self.f.write("        $expData%s = %s;\n" % (suffix, jsonstr))

  def _write_php_diff_action_log(self, keyname, newval, oldval):
    # If we're starting from scratch, populate the object
    if not oldval or not newval:
      self.f.write("        $expData['gameActionLog'] = array();\n");
      # This probably can't happen, but just in case
      if not newval: return
    nextkey = len(newval) - 1
    # If newval is large enough to imply that we are at the end of
    # the game, empty it and start over
    if len(newval) > 10:
      self.f.write("        $expData['gameActionLog'] = array();\n");
    # Otherwise, there are a couple of possible cases:
    # * in the simplest case, we are simply popping entries off
    #   oldval as newer entries come into play, so all mismatches
    #   will occur at the ends of the arrays
    # * in a more complicated case, some entries from oldval have
    #   changed - as soon as we detect we're in this latter case,
    #   empty the array and start over
    elif len(oldval) > 0:
      matches_found = False
      oldkey = len(oldval) - 1
      while oldkey >= 0:
        if newval[nextkey]['player'] == oldval[oldkey]['player'] \
           and newval[nextkey]['message'] == oldval[oldkey]['message']:
          matches_found = True
          nextkey -= 1
          oldkey -= 1
        elif matches_found:
          self.f.write("        $expData['gameActionLog'] = array();\n");
          nextkey = len(newval) - 1
          break
        else:
          self.f.write("        array_pop($expData['gameActionLog']);\n")
          oldkey -= 1

    while nextkey >= 0:
      self.f.write("        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '%s', 'message' => '%s'));\n" % (
        newval[nextkey]['player'], newval[nextkey]['message'].replace("'", "\\'")))
      nextkey -= 1

  def _write_php_diff_flat_array_key(self, suffix, newval, oldval):
    if newval != oldval:
      self.f.write("        $expData%s = array(%s);\n" % (
        suffix, ', '.join(['"%s"' % arrayelt for arrayelt in newval])))

  def _write_php_diff_subdie_array(self, suffix, newval, oldval):
    # example list element: [{u'sides': 17, u'value': 14}, {u'sides': 12, u'value': 9}]
    # example dict element: {u'1': {u'sides': 12}}
    if newval != oldval:
      if isinstance(newval, list):
        newvals = []
        for arrayelt in newval:
          eltvals = []
          if arrayelt:
            for [key, value] in arrayelt.items():
              eltvals.append('"%s" => "%s"' % (key, value))
          newvals.append("array(%s)" % ", ".join(eltvals))
        self.f.write("        $expData%s = array(%s);\n" % (
          suffix, ', '.join(newvals)))
      elif isinstance(newval, dict):
        newvals = {}
        for nk, arrayelt in sorted(newval.items()):
          eltvals = []
          if arrayelt:
            for [key, value] in arrayelt.items():
              eltvals.append('"%s" => "%s"' % (key, value))
          newvals[nk] = "array(%s)" % ", ".join(eltvals)
        self.f.write("        $expData%s = array(%s);\n" % (
          suffix, ', '.join("%s => %s" % (k, v) for k, v in sorted(newvals.items()))))

  def _write_php_diff_flat_dict_key(self, suffix, newval, oldval, quotevals=False):
    if newval != oldval:
      if newval:
        if not type(newval) == dict:
          raise ValueError, "Not a dict: %s => %s" % (suffix, newval)
        newvals = []
        for [key, value] in sorted(newval.items()):
          if quotevals:
            if '"' in value: valstr = "'%s'" % value
            else:            valstr = '"%s"' % value
          elif type(value) == list:
            valstr = 'array(%s)' % ", ".join([str(v) for v in value])
          else:
            valstr = value
          newvals.append('"%s" => %s' % (key, valstr))
        self.f.write("        $expData%s = array(%s);\n" % (
          suffix, ', '.join(newvals)))
      else:
        self.f.write("        $expData%s = array();\n" % (suffix))

  def _write_php_diff_prev_opt_value_array(self, suffix, newval, oldval):
    if type(newval) == dict:
      self._write_php_diff_flat_dict_key(suffix, newval, oldval)
    elif type(newval) == list:
      self._write_php_diff_flat_array_key(suffix, newval, oldval)
    else:
      raise ValueError, "%s => %s: unexpectedly neither an array nor a dict" % (suffix, newval)

  def _write_php_diff_opt_request_array(self, suffix, newval, oldval):
    # optRequestArray gets encoded in a somewhat inconsistent way
    # --- it's sometimes an dict with keys, but since the keys are
    # numeric, if they're the first N ids (starting with 0), it
    # gets encoded as a flat array of arrays
    if newval != oldval:
      if type(newval) == dict:
        self.f.write("        $expData%s = array(%s);\n" % (
          suffix, ', '.join(['"%s" => array(%s)' % (k, ', '.join([str(subv) for subv in v])) for [k, v] in sorted(newval.items())])))
      else:
        self.f.write("        $expData%s = array(%s);\n" % (
          suffix, ', '.join(['array(%s)' % (', '.join([str(subv) for subv in v])) for v in newval])))

  def _write_php_diff_game_skills_info(self, suffix, newval, oldval):
    if newval != oldval:
      if type(newval) == dict:
        newarr = ["'%s'" % skill for skill in sorted(newval.keys())]
      else:
        newarr = newval
      self.f.write("        $expData%s =  $this->get_skill_info(array(%s));\n" % (
        suffix, ', '.join(newarr)))

  def _write_php_diff_player_data_die_array(self, pnum, pkey, newdice, olddice):
    # TODO: try to figure out whether a die has been spliced out of the array due to capture
    for dnum in range(len(newdice)):
      newdie = newdice[dnum]
      if dnum > len(olddice) - 1:
        olddie = {}
        for dkey in newdie.keys():
          olddie[dkey] = 'UNDEFINED_MISMATCH'
      else:
        olddie = olddice[dnum]
      for dkey in sorted(olddie.keys()):
        if not dkey in newdie:
          suffix = "['playerDataArray'][%d]['%s'][%d]['%s']" % (pnum, pkey, dnum, dkey)
          self.f.write("        unset($expData%s);\n" % (suffix))
      for dkey in sorted(newdie.keys()):
        suffix = "['playerDataArray'][%d]['%s'][%d]['%s']" % (pnum, pkey, dnum, dkey)
        if dkey in STRING_KEYS:
          self._write_php_diff_string_key(suffix, newdie[dkey], olddie[dkey])
        elif dkey in NUMERIC_KEYS:
          self._write_php_diff_numeric_key(suffix, newdie[dkey], olddie[dkey])
        elif dkey in ['properties', 'skills', ]:
          oldval = {}
          if dkey in olddie: oldval = olddie[dkey]
          self._write_php_diff_flat_array_key(suffix, newdie[dkey], oldval)
        elif dkey in ['subdieArray', ]:
          oldval = {}
          if dkey in olddie: oldval = olddie[dkey]
          self._write_php_diff_subdie_array(suffix, newdie[dkey], oldval)
        elif dkey in ['wildcardPropsArray', ]:
          self._write_php_diff_flat_dict_key(suffix, newdie[dkey], olddie.get(dkey, {}), True)
        else:
          raise ValueError, "%s => %s" % (dkey, newdie[dkey])
    for dnum in range(len(olddice) - len(newdice)):
      self.f.write("        array_pop($expData['playerDataArray'][%d]['%s']);\n" % (pnum, pkey))

  def _write_php_diff_player_data_array(self, key, pnum, newdata, olddata):
    for pkey in sorted(olddata.keys()):
      if not pkey in newdata:
        suffix = "['playerDataArray'][%d]['%s']" % (pnum, pkey)
        self.f.write("        unset($expData%s);\n" % (suffix))
    for pkey in sorted(newdata.keys()):
      suffix = "['playerDataArray'][%d]['%s']" % (pnum, pkey)
      if pkey in ['activeDieArray', 'capturedDieArray', 'outOfPlayDieArray', ]:
        oldval = {}
        if pkey in olddata: oldval = olddata[pkey]
        self._write_php_diff_player_data_die_array(pnum, pkey, newdata[pkey], oldval)
      elif pkey in ['gameScoreArray', 'swingRequestArray', 'prevSwingValueArray', ]:
        self._write_php_diff_flat_dict_key(suffix, newdata[pkey], olddata.get(pkey, {}))
      elif pkey in ['button', ]:
        self._write_php_diff_flat_dict_key(suffix, newdata[pkey], olddata.get(pkey, {}), True)
      elif pkey in ['prevOptValueArray', ]:
        self._write_php_diff_prev_opt_value_array(suffix, newdata[pkey], olddata.get(pkey, {}))
      elif pkey in ['optRequestArray', 'turboSizeArray', ]:
        self._write_php_diff_opt_request_array(suffix, newdata[pkey], olddata.get(pkey, {}))
      elif pkey in NUMERIC_KEYS:
        self._write_php_diff_numeric_key(suffix, newdata[pkey], olddata.get(pkey, None))
      elif pkey in UNUSED_DURING_AUTOPLAY_KEYS:
        if olddata and newdata[pkey] != olddata[pkey]:
          self.bug("Playerdata key %s is expected to be static, but unexpectedly changed between loadGameData invocations: %s => %s" % (
            pkey, olddata[pkey], newdata[pkey]))
	# If olddata isn't defined because we're in game initialization, don't fail.
        # Don't do anything else either, because nothing else should be needed:
        # $this->generate_init_expected_data_array() should initialize these items.
      elif pkey == 'lastActionTime':
        pass
      else:
        raise ValueError, pkey

  def _write_php_json_diff(self, newobj, oldobj):
    for key in sorted(newobj.keys()):
      suffix = "['%s']" % key
      if key in NUMERIC_KEYS:
        self._write_php_diff_numeric_key(suffix, newobj[key], oldobj.get(key, None))
      elif key in STRING_KEYS:
        self._write_php_diff_string_key(suffix, newobj[key], oldobj.get(key, None))
      elif key == 'gameActionLog':
        self._write_php_diff_action_log(key, newobj[key], oldobj.get(key, []))
      elif key in UNUSED_DURING_AUTOPLAY_KEYS:
        if oldobj and newobj[key] != oldobj[key]:
          self.bug("Key %s is expected to be static, but unexpectedly changed between loadGameData invocations: %s => %s" % (
            key, oldobj[key], newobj[key]))
	# If oldobj isn't defined because we're in game initialization, don't fail.
        # Don't do anything else either, because nothing else should be needed:
        # $this->generate_init_expected_data_array() should initialize these items.
      elif key == 'playerDataArray':
        for pnum in range(len(newobj[key])):
          old_player_data = oldobj[key][pnum] if oldobj else {}
          self._write_php_diff_player_data_array(key, pnum, newobj[key][pnum], old_player_data)
      elif key == 'timestamp':
        pass
      elif key == 'validAttackTypeArray':
        self._write_php_diff_flat_array_key(suffix, newobj[key], oldobj.get(key, []))
      elif key == 'gameSkillsInfo':
        self._write_php_diff_game_skills_info(suffix, newobj[key], oldobj.get(key, None))
      else:
        raise ValueError, key

  def _php_get_message(self, retval):
    return retval.message.replace("'", "\\'")

  def _generate_php_attack_arrays(
      self, part_attackers, part_defenders, all_attackers, all_defenders, attacker, defender):
    attack = {}
    php_array_parts = []
    for i in range(len(all_attackers)):
      if i in part_attackers:
        value = 'true'
        php_array_parts.append('array(%d, %d)' % (attacker, i))
      else:
        value = 'false'
      attack['playerIdx_%d_dieIdx_%d' % (attacker, i)] = value
    for i in range(len(all_defenders)):
      if i in part_defenders:
        value = 'true'
        php_array_parts.append('array(%d, %d)' % (defender, i))
      else:
        value = 'false'
      attack['playerIdx_%d_dieIdx_%d' % (defender, i)] = value
    php_array = 'array(%s)' % ', '.join(php_array_parts)
    return [attack, php_array]


class LoggingBMClient():
  """
  This client instantiates a BM game and plays both sides of it,
  taking legal game actions randomly.
  """

  def __init__(self, player_client, opponent_client):
    self.player_client = player_client
    self.opponent_client = opponent_client
    self.random_seed = int(time.time())
    self.loaded_data = None
    self.reject_games = {
      'with_skill': [],
      'without_skill': [],
    }
    random.seed(self.random_seed)
    # Any state that's needed because of things the API isn't going to tell us
    self.state = {
      'opponent_aux_chosen': False,
    }
    # Any state that's needed so we can tailor future random decisions based on past ones
    self.decision_state = {
      'cancelled_fire_attacks': 0,
      'expected_start_turn_status': 'ok',
    }
    self.log = []

  def reset_internal_state(self):
    self.loaded_data = None
    self.state = {
      'opponent_aux_chosen': False,
    }

  def _waiting_on_player(self, n):
    return self.loaded_data['playerDataArray'][n]['waitingOnAction']

  def _random_array_element(self, array, return_index=False):
    return random_array_element(array, return_index)

  def _add_php_pre_action_block(self, client):
    if client == self.opponent_client:
      self.log.append({
        'type': 'login',
        'user': self.opponent_client.username,
      })

  def _add_php_post_action_block(self, client):
    if client == self.opponent_client:
      self.log.append({
        'type': 'login',
        'user': self.player_client.username,
      })

  # We're  using arbitrary player clients at this point, so we don't
  # want to record any game data we load in the log, because that
  # data's perspective is wrong for replay.
  #
  # Instead, load the data now, use it to gather player names, and
  # then throw it away.  The caller needs to load data again and
  # actually record it, after this.
  def _load_game_data_then_import_player_clients(self, client_list):
    retval = self.player_client.load_game_data(self.game_id)
    if not (retval and retval.status == 'ok'):
      self.bug("_load_game_data_then_import_player_clients(%s) unexpectedly failed: %s" % (
        self.game_id,
        retval and retval.message or "NULL"))
    player_name = retval.data['playerDataArray'][0]['playerName']
    opponent_name = retval.data['playerDataArray'][1]['playerName']
    player_client = None
    opponent_client = None
    for client in client_list:
      if client.username == player_name: player_client = client
      if client.username == opponent_name: opponent_client = client
    assert player_client is not None, "Could not find client in provided list matching player: %s" % player_name
    assert opponent_client is not None, "Could not find client in provided list matching opponent: %s" % opponent_name
    self.player_client = player_client
    self.opponent_client = opponent_client

  def _list_all_idx_combos(self, list_len, combo_len):
    return [x for x in itertools.combinations(range(list_len), combo_len)]

  def _invalid_attack_expected(self):
    if self.decision_state['expected_start_turn_status'] == 'failed': return True
    return False

  def _use_invalid_attacker_defender_combo(self):
    if random.random() < 0.01:
      self.decision_state['expected_start_turn_status'] = 'failed'
      return True
    return False

  def _look_for_attacker_defender_combo(self, attacker_dice, defender_dice, n_att, n_def, validate_fn):
    attacker_combos = self._list_all_idx_combos(len(attacker_dice), n_att)
    defender_combos = self._list_all_idx_combos(len(defender_dice), n_def)
    random.shuffle(attacker_combos)
    random.shuffle(defender_combos)
    for i in range(len(attacker_combos)):
      for j in range(len(defender_combos)):
        test_attackers = []
        test_non_attackers = []
        for k in range(len(attacker_dice)):
          if k in attacker_combos[i]: test_attackers.append(attacker_dice[k])
          else:                       test_non_attackers.append(attacker_dice[k])
        test_defenders = [ defender_dice[k] for k in defender_combos[j] ]
        if validate_fn(test_attackers, test_defenders, test_non_attackers) \
           or self._invalid_attack_expected() or self._use_invalid_attacker_defender_combo():
          return [attacker_combos[i], defender_combos[j], ]
    return False

  def _find_attacker_defender_combo(self, attacker_dice, defender_dice, n_att, n_def, validate_fn):
    retval = self._look_for_attacker_defender_combo(attacker_dice, defender_dice, n_att, n_def, validate_fn)
    if retval == False:
      if self._invalid_attack_expected():
        return [ [], [] ]
      self.bug("Could not find valid attack with function=%s, attackers=%s, defenders=%s" % (
        validate_fn, attacker_dice, defender_dice))
    return retval

  def _player_with_initiative_from_values(self, player_vals, opponent_vals):
    player_vals.sort()
    opponent_vals.sort()
    while len(player_vals) > 0 and len(opponent_vals) > 0:
      player_first = player_vals.pop(0)
      opponent_first = opponent_vals.pop(0)
      if player_first != opponent_first:
        if player_first < opponent_first: return 0
        else:                             return 1
    if len(player_vals) > 0: return 0
    if len(opponent_vals) > 0: return 1
    # 20201226: My previous comment here said:
    #   "This is what _can_gain_initiative_using_focus_dice() needs in case of ties"
    # As far as i can tell, that's not true --- as far as i can tell, the player needs
    # to gain initiative unambiguously with focus dice; the engine won't let you turn
    # down your dice and flip a coin.  So i think this has to return opponent_idx
    # in case of a tie, and therefore i'm changing this return from 0 to 1
    return 1

  def _can_gain_initiative_using_focus_dice(self, player_dice, opponent_dice):
    skip_initiative_skills = [ 'Rage', 'Slow', 'Stinger', 'Trip', 'Warrior', ]
    player_initiative_values = []
    opponent_initiative_values = []
    for die in player_dice:
      skip_die = False
      for skill in die['skills']:
        if skill in skip_initiative_skills:
          skip_die = True
      if skip_die: continue
      if 'Focus' in die['skills']:
        player_initiative_values.append(min_die_value(die))
      else:
        player_initiative_values.append(die['value'])
    for die in opponent_dice:
      skip_die = False
      for skill in die['skills']:
        if skill in skip_initiative_skills:
          skip_die = True
      if skip_die: continue
      opponent_initiative_values.append(die['value'])
    return self._player_with_initiative_from_values(player_initiative_values, opponent_initiative_values) == 0

  def _valid_dice_for_skill(self, dice, okay_skills):
    all_skills = []
    for die in dice:
      if 'mandatory' in okay_skills: skill_found = False
      else:                          skill_found = True
      for skill in die['skills']:
        if not skill in all_skills: all_skills.append(skill)
        if skill in okay_skills['no']: return False
        if 'mandatory' in okay_skills and skill in okay_skills['mandatory']:
          skill_found = True
          continue
        if skill not in okay_skills['ok']:
          self.bug("Skill %s found in die %s is not defined in okay_skills array %s" % (
            skill, die, okay_skills))
      if not skill_found: return False
      if 'Dizzy' in die['properties']: return False
    return all_skills

  def _is_valid_attack_of_type_Berserk(self, attackers, defenders, non_attackers):
    attack_skills = {
      'mandatory': [ 'Berserk', ],
      'ok': [
	'Boom', 'Chance', 'Doppelganger', 'Fire', 'Focus', 'Insult',
	'Jolt', 'Konstant', 'Mad', 'Maximum', 'Mighty', 'Mood', 'Morphing',
	'Null', 'Ornery', 'Poison', 'Queer', 'Radioactive', 'Rage', 'Rush',
	'Shadow', 'Slow', 'Speed', 'Stinger', 'TimeAndSpace', 'Turbo', 'Trip',
	'Value', 'Weak',
      ],
      'no': [ 'Stealth', 'Warrior', ],
    }
    defend_skills = {
      'ok': [
	'Berserk', 'Boom', 'Chance', 'Doppelganger', 'Fire', 'Focus',
	'Insult', 'Jolt', 'Konstant', 'Mad', 'Maximum', 'Mighty', 'Mood',
	'Morphing', 'Null', 'Ornery', 'Poison', 'Queer', 'Radioactive',
	'Rage', 'Rush', 'Shadow', 'Slow', 'Speed', 'Stinger',
	'TimeAndSpace', 'Turbo', 'Trip', 'Value', 'Weak',
      ],
      'no': [ 'Stealth', 'Warrior', ],
    }
    attacker_okay_skills = self._valid_dice_for_skill(attackers, attack_skills)
    if attacker_okay_skills == False: return False
    defender_okay_skills = self._valid_dice_for_skill(defenders, defend_skills)
    if defender_okay_skills == False: return False
    defender_sum = 0
    for defender in defenders:
      defender_sum += defender['value']
    attacker = attackers[0]
    return (attacker['value'] == defender_sum)

  def _is_valid_attack_of_type_Boom(self, attackers, defenders, non_attackers):
    attack_skills = {
      'mandatory': [ 'Boom', ],
      'ok': [
	'Berserk', 'Boom', 'Chance', 'Doppelganger', 'Fire', 'Focus',
	'Insult', 'Jolt', 'Konstant', 'Mad', 'Maximum', 'Mighty', 'Mood',
	'Morphing', 'Null', 'Ornery', 'Poison', 'Queer', 'Radioactive',
	'Rage', 'Rush', 'Shadow', 'Slow', 'Speed', 'Stealth', 'Stinger',
	'TimeAndSpace', 'Turbo', 'Trip', 'Value', 'Warrior', 'Weak',
      ],
      'no': [ 'Stealth', 'Warrior', ],
    }
    defend_skills = {
      'ok': [
	'Berserk', 'Boom', 'Chance', 'Doppelganger', 'Fire', 'Focus',
	'Insult', 'Jolt', 'Konstant', 'Mad', 'Maximum', 'Mighty', 'Mood',
	'Morphing', 'Null', 'Ornery', 'Poison', 'Queer', 'Radioactive',
	'Rage', 'Rush', 'Shadow', 'Slow', 'Speed', 'Stealth', 'Stinger',
	'TimeAndSpace', 'Turbo', 'Trip', 'Value', 'Weak',
      ],
      'no': [ 'Warrior', ],
    }
    attacker_okay_skills = self._valid_dice_for_skill(attackers, attack_skills)
    if attacker_okay_skills == False: return False
    defender_okay_skills = self._valid_dice_for_skill(defenders, defend_skills)
    if defender_okay_skills == False: return False
    # There are no limits on a Boom attack except that the attacker must have the Boom skill
    return True

  def _is_valid_attack_of_type_Power(self, attackers, defenders, non_attackers):
    attack_skills = {
      'ok': [
	'Berserk', 'Boom', 'Chance', 'Doppelganger', 'Focus',
	'Insult', 'Jolt', 'Mad', 'Maximum', 'Mighty', 'Mood', 'Morphing',
	'Null', 'Ornery', 'Poison', 'Queer', 'Radioactive', 'Rage', 'Rush',
	'Slow', 'Speed', 'Stinger', 'TimeAndSpace', 'Turbo', 'Trip', 'Value',
	'Weak',
      ],
      'no': [ 'Fire', 'Konstant', 'Shadow', 'Stealth', 'Warrior', ],
    }
    defend_skills = {
      'ok': [
	'Berserk', 'Boom', 'Chance', 'Doppelganger', 'Fire', 'Focus',
	'Insult', 'Jolt', 'Konstant', 'Mad', 'Maximum', 'Mighty', 'Mood',
	'Morphing', 'Null', 'Ornery', 'Poison', 'Queer', 'Radioactive',
	'Rage', 'Rush', 'Shadow', 'Slow', 'Speed', 'Stinger', 'TimeAndSpace',
	'Turbo', 'Trip', 'Value', 'Weak',
      ],
      'no': [ 'Stealth', 'Warrior', ],
    }
    attacker_okay_skills = self._valid_dice_for_skill(attackers, attack_skills)
    if attacker_okay_skills == False: return False
    defender_okay_skills = self._valid_dice_for_skill(defenders, defend_skills)
    if defender_okay_skills == False: return False
    available_fire = 0
    smallest_turndown = None
    for non_attacker in non_attackers:
      if 'Fire' in non_attacker['skills']:
        available_fire += (non_attacker['value'] - min_die_value(non_attacker))
        die_smallest_turndown = self._die_smallest_fire_turndown(non_attacker, non_attacker['value'])
        if smallest_turndown: smallest_turndown = min(smallest_turndown, die_smallest_turndown)
        else:                 smallest_turndown = die_smallest_turndown
    attacker = attackers[0]
    defender = defenders[0]
    attacker_value = attacker['value']
    if 'wildcardPropsArray' in attacker and attacker['value'] == 1:
      attacker_value = 14
    if 'Queer' in attacker_okay_skills and attacker_value % 2 == 1: return False
    if attacker_value >= defender['value']: return True
    if (attacker_value + available_fire) >= defender['value'] \
       and int(attacker['sides']) >= defender['value'] \
       and (int(attacker['sides']) - attacker['value']) >= smallest_turndown: return True
    return False

  def _is_valid_attack_of_type_Shadow(self, attackers, defenders, non_attackers):
    attack_skills = {
      'mandatory': [ 'Queer', 'Shadow', ],
      'ok': [
	'Berserk', 'Boom', 'Chance', 'Doppelganger', 'Fire', 'Focus',
	'Insult', 'Jolt', 'Konstant', 'Mad', 'Maximum', 'Mighty', 'Mood',
	'Morphing', 'Null', 'Ornery', 'Poison', 'Queer', 'Radioactive',
	'Rage', 'Rush', 'Slow', 'Speed', 'Stinger', 'TimeAndSpace', 'Trip',
	'Turbo', 'Value', 'Weak',
      ],
      'no': [ 'Stealth', 'Warrior', ],
    }
    defend_skills = {
      'ok': [
	'Berserk', 'Boom', 'Chance', 'Doppelganger', 'Fire', 'Focus',
	'Insult', 'Jolt', 'Konstant', 'Mad', 'Maximum', 'Mighty', 'Mood',
	'Morphing', 'Null', 'Ornery', 'Poison', 'Queer', 'Radioactive',
	'Rage', 'Rush', 'Shadow', 'Slow', 'Speed', 'Stinger', 'TimeAndSpace',
	'Turbo', 'Trip', 'Value', 'Weak',
      ],
      'no': [ 'Stealth', 'Warrior', ],
    }
    attacker_okay_skills = self._valid_dice_for_skill(attackers, attack_skills)
    if attacker_okay_skills == False: return False
    defender_okay_skills = self._valid_dice_for_skill(defenders, defend_skills)
    if defender_okay_skills == False: return False
    attacker = attackers[0]
    defender = defenders[0]
    if attacker['value'] > defender['value']: return False
    if int(attacker['sides']) < defender['value']: return False
    if 'Shadow' in attacker_okay_skills: return True
    if 'Queer' in attacker_okay_skills:
      if attacker['value'] % 2 == 0: return False
      else:                          return True
    # should never get here
    return False

  def _is_valid_attack_of_type_Trip(self, attackers, defenders, non_attackers):
    attack_skills = {
      'mandatory': [ 'Trip', ],
      'ok': [
	'Berserk', 'Boom', 'Chance', 'Doppelganger', 'Fire', 'Focus',
	'Insult', 'Jolt', 'Konstant', 'Mad', 'Maximum', 'Mighty', 'Mood',
	'Morphing', 'Null', 'Ornery', 'Poison', 'Queer', 'Radioactive',
	'Rage', 'Rush', 'Shadow', 'Slow', 'Speed', 'Stinger',
	'TimeAndSpace', 'Turbo', 'Value', 'Weak',
      ],
      'no': [ 'Stealth', 'Warrior', ],
    }
    defend_skills = {
      'ok': [
	'Berserk', 'Boom', 'Chance', 'Doppelganger', 'Fire', 'Focus',
	'Insult', 'Jolt', 'Konstant', 'Mad', 'Maximum', 'Mighty', 'Mood',
	'Morphing', 'Null', 'Ornery', 'Poison', 'Queer', 'Radioactive',
	'Rage', 'Rush', 'Shadow', 'Slow', 'Speed', 'Stinger',
	'TimeAndSpace', 'Turbo', 'Trip', 'Value', 'Weak',
      ],
      'no': [ 'Stealth', 'Warrior', ],
    }
    attacker_okay_skills = self._valid_dice_for_skill(attackers, attack_skills)
    if attacker_okay_skills == False: return False
    defender_okay_skills = self._valid_dice_for_skill(defenders, defend_skills)
    if defender_okay_skills == False: return False
    attacker = attackers[0]
    defender = defenders[0]
    attacker_max = self._max_trip_value(attacker)
    defender_min = self._min_trip_value(defender)
    return attacker_max >= defender_min

  def _max_trip_value(self, die):
    if 'Konstant' in die['skills']: return die['value']
    post_trip_sides = int(die['sides'])
    if 'Mighty' in die['skills']:
      post_trip_sides = self._next_mighty_value(post_trip_sides)
    if 'Weak' in die['skills']:
      post_trip_sides = self._next_weak_value(post_trip_sides)
    # TODO: make attack-selection in general aware of Turbo, don't
    # just hack in the max possible value
    if 'Turbo' in die['skills'] or 'Mad' in die['skills'] or 'Mood' in die['skills']:
      # TODO: don't hardcode ranges, parse turboVals
      sides_part = die['recipe'].split('(')[1].split(')')[0]
      if '/' in sides_part:
        post_trip_sides = max([int(x) for x in sides_part.split('/')])
      else:
        post_trip_sides = sum([
          SWING_RANGES[s][-1] if s in SWING_RANGES else int(s) \
          for s in sides_part.split(',')
        ])
    return post_trip_sides

  def _min_trip_value(self, die):
    if 'Konstant' in die['skills']: return die['value']
    if not 'Maximum' in die['skills']: return min_die_value(die)
    post_trip_sides = int(die['sides'])
    if 'Mighty' in die['skills']:
      post_trip_sides = self._next_mighty_value(post_trip_sides)
    if 'Weak' in die['skills']:
      post_trip_sides = self._next_weak_value(post_trip_sides)
    if 'Turbo' in die['skills'] or 'Mad' in die['skills'] or 'Mood' in die['skills']:
      sides_part = die['recipe'].split('(')[1].split(')')[0]
      if '/' in sides_part:
        post_trip_sides = min([int(x) for x in sides_part.split('/')])
      else:
        post_trip_sides = sum([
          SWING_RANGES[s][0] if s in SWING_RANGES else int(s) \
          for s in sides_part.split(',')
        ])
    return post_trip_sides

  def _next_mighty_value(self, sides):
    sizes = [1, 2, 4, 6, 8, 10, 12, 16, 20, 30]
    if sides >= sizes[-1]: return sizes[-1]  # or should this return sides?
    return [x for x in sizes if sides < x][0]

  def _next_weak_value(self, sides):
    sizes = [1, 2, 4, 6, 8, 10, 12, 16, 20, 30]
    if sides <= sizes[0]: return sides
    return [x for x in sizes if sides > x][-1]

  def _generate_turbo_val_arrays(self, part_attackers, attack_type):
    # The turbo contract is if an attacking die appears in the
    # turboSizeArray provided in the game data, the player both can
    # and must select a turbo size for that die, and the set of
    # valid choices is exactly what appeared in turboSizeArray.
    #
    # If we need more logic than that to make things work, it's a site bug
    turbovals = {}
    attackerData = self.loaded_data['playerDataArray'][self.attacker]

    # If we're given a list, convert it to a dict
    if type(attackerData['turboSizeArray']) == list:
      turboSizeData = {}
      for i in range(len(attackerData['turboSizeArray'])):
        turboSizeData[i] = attackerData['turboSizeArray'][i]
    elif type(attackerData['turboSizeArray']) == dict:
      turboSizeData = attackerData['turboSizeArray']
    else:
      raise ValueError, "Unexpected type: %s in data: %s" % (attackerData['turboSizeArray'], attackerData)

    for key, values in sorted(turboSizeData.items()):
      if int(key) in part_attackers:
        if attack_type == 'Trip':
	  # FIXME: this is a cheat to prevent having to recompute trip attack validity,
          # but obviously we'd get a better test without a cheat
          turbo_choice = values[-1]
        else:
          turbo_choice = self._random_array_element(values)
        turbovals[key] = turbo_choice
    return turbovals

  def _is_valid_attack_of_type_Skill(self, attackers, defenders, non_attackers):
    self.debug_skill_tries.append("ATT=%s, DEF=%s" % (attackers, defenders))
    attack_skills = {
      'ok': [
	'Boom', 'Chance', 'Doppelganger', 'Fire', 'Focus', 'Insult', 'Jolt',
	'Konstant', 'Mad', 'Maximum', 'Mighty', 'Mood', 'Morphing',
	'Null', 'Ornery', 'Poison', 'Queer', 'Radioactive', 'Rage',
	'Rush', 'Shadow', 'Slow', 'Speed', 'Stealth', 'Stinger', 'TimeAndSpace',
	'Turbo', 'Trip', 'Value', 'Warrior', 'Weak',
      ],
      'no': [ 'Berserk', ],
    }
    defend_skills = {
      'ok': [
	'Boom', 'Berserk', 'Chance', 'Doppelganger', 'Fire', 'Focus', 'Jolt',
	'Konstant', 'Mad', 'Maximum', 'Mighty', 'Mood', 'Morphing', 'Null',
	'Ornery', 'Poison', 'Queer', 'Radioactive', 'Rage', 'Rush', 'Shadow',
	'Slow', 'Speed', 'Stealth', 'TimeAndSpace', 'Stinger',
	'Turbo', 'Trip', 'Value', 'Weak',
      ],
      'no': [ 'Insult', 'Warrior', ],
    }
    attacker_okay_skills = self._valid_dice_for_skill(attackers, attack_skills)
    if attacker_okay_skills == False: return False
    defender_okay_skills = self._valid_dice_for_skill(defenders, defend_skills)
    if defender_okay_skills == False: return False
    if 'Konstant' in attacker_okay_skills and len(attackers) == 1: return False
    if 'Stealth' in attacker_okay_skills and len(attackers) == 1: return False
    if 'Stealth' in defender_okay_skills and len(attackers) == 1: return False
    num_warrior = 0
    for attacker in attackers:
      if 'Warrior' in attacker['skills']:
        num_warrior += 1
    if num_warrior > 1: return False
    if num_warrior > 0 and len(attackers) == 1: return False
    contributions = []
    for non_attacker in non_attackers:
      if 'Fire' in non_attacker['skills']:
        # non-participating fire dice can contribute 0, or anywhere
        # from the minimum amount by which the die can be turned
        # down, to the amount that takes the die to its minimum value
        range_max = non_attacker['value'] - self._die_fire_min(non_attacker)
        range_min = self._die_smallest_fire_turndown(non_attacker, non_attacker['value'])
        contributions.append([0] + range(range_min, range_max + 1))
    attacker_max_sides = 0
    for attacker in attackers:
      attacker_max_sides += int(attacker['sides'])
      die_contributions = [ attacker['value'], ]
      if 'Konstant' in attacker['skills'] and not 'Warrior' in attacker['skills']:
        die_contributions.append(-1 * attacker['value'])
      if 'Stinger' in attacker['skills'] and not 'Warrior' in attacker['skills']:
        range_min = min_die_value(attacker)
        die_contributions.extend(range(range_min, attacker['value'] + 1))
        if 'Konstant' in attacker['skills']:
          die_contributions.extend(range(-1 * attacker['value'] + 1, -1 * range_min + 1))
      contributions.append(die_contributions)
    defender = defenders[0]
    if attacker_max_sides < defender['value']: return False
    for attempt in itertools.product(*contributions):
      if sum(attempt) == defender['value']: return True
    return False

  def _is_valid_attack_of_type_Rush(self, attackers, defenders, non_attackers):
    # Logic here is a little different than some of the other skill checkers.
    # We need to check that the mandatory skill appears in *at least one of*
    # the attacker or the defender, and also that no forbidden skills appear
    # in either the attacker or the defender
    mandatory_skill_found = False
    for die in attackers + defenders:
      if 'Rush' in die['skills']: mandatory_skill_found = True
    if not mandatory_skill_found: return False

    attack_ok_skills = {
      'ok': [
	'Berserk', 'Boom', 'Chance', 'Doppelganger', 'Fire', 'Focus',
	'Insult', 'Jolt', 'Konstant', 'Mad', 'Maximum', 'Mighty', 'Mood',
	'Morphing', 'Null', 'Ornery', 'Poison', 'Queer', 'Radioactive',
	'Rage', 'Rush', 'Shadow', 'Slow', 'Speed', 'Stinger',
	'TimeAndSpace', 'Trip', 'Turbo', 'Value', 'Weak',
      ],
      'no': [ 'Stealth', 'Warrior', ],
    }
    defend_ok_skills = {
      'ok': [
	'Berserk', 'Boom', 'Chance', 'Doppelganger', 'Fire', 'Focus',
	'Insult', 'Jolt', 'Konstant', 'Mad', 'Maximum', 'Mighty', 'Mood',
	'Morphing', 'Null', 'Ornery', 'Poison', 'Queer', 'Radioactive',
	'Rage', 'Rush', 'Shadow', 'Slow', 'Speed', 'Stinger',
	'TimeAndSpace', 'Turbo', 'Trip', 'Value', 'Weak',
      ],
      'no': [ 'Stealth', 'Warrior', ],
    }
    if self._valid_dice_for_skill(attackers, attack_ok_skills) == False: return False
    if self._valid_dice_for_skill(defenders, defend_ok_skills) == False: return False
    defender_sum = 0
    for defender in defenders:
      defender_sum += defender['value']
    attacker = attackers[0]
    return (attacker['value'] == defender_sum)

  def _is_valid_attack_of_type_Speed(self, attackers, defenders, non_attackers):
    attack_skills = {
      'mandatory': [ 'Speed', ],
      'ok': [
	'Berserk', 'Boom', 'Chance', 'Doppelganger', 'Fire', 'Focus',
	'Insult', 'Jolt', 'Konstant', 'Mad', 'Maximum', 'Mighty', 'Mood',
	'Morphing', 'Null', 'Ornery', 'Poison', 'Queer', 'Radioactive',
	'Rage', 'Rush', 'Shadow', 'Slow', 'Stinger', 'TimeAndSpace', 'Trip',
	'Turbo', 'Value', 'Weak',
      ],
      'no': [ 'Stealth', 'Warrior', ],
    }
    defend_skills = {
      'ok': [
	'Berserk', 'Boom', 'Chance', 'Doppelganger', 'Fire', 'Focus',
	'Insult', 'Jolt', 'Konstant', 'Mad', 'Maximum', 'Mighty', 'Mood',
	'Morphing', 'Null', 'Ornery', 'Poison', 'Queer', 'Radioactive',
	'Rage', 'Rush', 'Shadow', 'Slow', 'Speed', 'Stinger', 'TimeAndSpace',
	'Turbo', 'Trip', 'Value', 'Weak',
      ],
      'no': [ 'Stealth', 'Warrior', ],
    }
    attacker_okay_skills = self._valid_dice_for_skill(attackers, attack_skills)
    if attacker_okay_skills == False: return False
    defender_okay_skills = self._valid_dice_for_skill(defenders, defend_skills)
    if defender_okay_skills == False: return False
    defender_sum = 0
    for defender in defenders:
      defender_sum += defender['value']
    attacker = attackers[0]
    return (attacker['value'] == defender_sum)

  def _generate_attack_array(self, part_attackers, part_defenders):
    # N.B. This logic is largely duplicated in _generate_php_attack_arrays in the php writer class
    attack = {}
    for i in range(len(self.loaded_data['playerDataArray'][self.attacker]['activeDieArray'])):
      if i in part_attackers:
        value = 'true'
      else:
        value = 'false'
      attack['playerIdx_%d_dieIdx_%d' % (self.attacker, i)] = value
    for i in range(len(self.loaded_data['playerDataArray'][self.defender]['activeDieArray'])):
      if i in part_defenders:
        value = 'true'
      else:
        value = 'false'
      attack['playerIdx_%d_dieIdx_%d' % (self.defender, i)] = value
    return attack


  def _game_action_start_turn_find_attack_Berserk(self, b, attackerData, defenderData):
    num_defenders = range(1, len(defenderData['activeDieArray']) + 1)
    random.shuffle(num_defenders)
    while len(num_defenders) > 0:
      n_def = num_defenders.pop()
      retval = self._look_for_attacker_defender_combo(
        attackerData['activeDieArray'], defenderData['activeDieArray'], 1, n_def,
        self._is_valid_attack_of_type_Berserk)
      if retval:
        return retval
    self.bug("Could not find valid berserk attack")

  def _game_action_start_turn_find_attack_Boom(self, b, attackerData, defenderData):
    return self._find_attacker_defender_combo(
      attackerData['activeDieArray'], defenderData['activeDieArray'], 1, 1,
      self._is_valid_attack_of_type_Boom)

  def _game_action_start_turn_find_attack_Pass(self, b, attackerData, defenderData):
    return [ [], [], ]

  def _game_action_start_turn_find_attack_Surrender(self, b, attackerData, defenderData):
    return [ [], [], ]

  def _game_action_start_turn_find_attack_Power(self, b, attackerData, defenderData):
    return self._find_attacker_defender_combo(
      attackerData['activeDieArray'], defenderData['activeDieArray'], 1, 1,
      self._is_valid_attack_of_type_Power)

  def _game_action_start_turn_find_attack_Rush(self, b, attackerData, defenderData):
    return self._find_attacker_defender_combo(
      attackerData['activeDieArray'], defenderData['activeDieArray'], 1, 2,
      self._is_valid_attack_of_type_Rush)

  def _game_action_start_turn_find_attack_Shadow(self, b, attackerData, defenderData):
    return self._find_attacker_defender_combo(
      attackerData['activeDieArray'], defenderData['activeDieArray'], 1, 1,
      self._is_valid_attack_of_type_Shadow)

  def _game_action_start_turn_find_attack_Skill(self, b, attackerData, defenderData):
    self.debug_skill_tries = []
    num_attackers = range(1, len(attackerData['activeDieArray']) + 1)
    random.shuffle(num_attackers)
    while len(num_attackers) > 0:
      n_att = num_attackers.pop()
      retval = self._look_for_attacker_defender_combo(
        attackerData['activeDieArray'], defenderData['activeDieArray'], n_att, 1,
        self._is_valid_attack_of_type_Skill)
      if retval:
        return retval
    self.bug("Could not find valid skill attack: tried: %s" % "\n".join(self.debug_skill_tries))

  def _game_action_start_turn_find_attack_Speed(self, b, attackerData, defenderData):
    num_defenders = range(1, len(defenderData['activeDieArray']) + 1)
    random.shuffle(num_defenders)
    while len(num_defenders) > 0:
      n_def = num_defenders.pop()
      retval = self._look_for_attacker_defender_combo(
        attackerData['activeDieArray'], defenderData['activeDieArray'], 1, n_def,
        self._is_valid_attack_of_type_Speed)
      if retval:
        return retval
    self.bug("Could not find valid speed attack")

  def _game_action_start_turn_find_attack_Trip(self, b, attackerData, defenderData):
    return self._find_attacker_defender_combo(
      attackerData['activeDieArray'], defenderData['activeDieArray'], 1, 1,
      self._is_valid_attack_of_type_Trip)

  def _game_action_choose_auxiliary_dice_player(self, b, playerData):
    auxiliary_choices = []
    choices = [ 'decline', 'add', ]
    for die_idx in range(len(playerData['activeDieArray'])):
      die = playerData['activeDieArray'][die_idx]
      if 'Auxiliary' in die['skills']:
        auxiliary_choices.append(die_idx)
    if len(auxiliary_choices) != 1:
      self.bug("In choose_auxiliary_dice for %s with wrong number of auxiliary dice to choose: %s" % (
        b.username, playerData))
    unique_swing_among_dice = playerData['button']['name'] == 'Gordo'
    if unique_swing_among_dice:
      unique_aux_conflict = False
      aux_die = playerData['activeDieArray'][die_idx]
      if aux_die['sides']:
        for other_die_idx in range(len(playerData['activeDieArray'])):
          if die_idx == other_die_idx: continue
          if aux_die['sides'] == playerData['activeDieArray'][other_die_idx]['sides']:
            unique_aux_conflict = True
      else:
        # There's no clean way to figure out a die's swing type from the API data, so just use something fast
        aux_swing_type = aux_die['description'].split(' Swing Die')[0][-1]
        for other_die_idx in range(len(playerData['activeDieArray'])):
          if die_idx == other_die_idx: continue
          # This relies on swing letters not being reused as skill names
          if aux_swing_type in playerData['activeDieArray'][other_die_idx]['recipe']:
            unique_aux_conflict = True
      if unique_aux_conflict:
        choices.remove('add')
    action = self._random_array_element(choices)
    die_idx = None
    choice = [ action, ]
    if action == 'add':
      die_idx = self._random_array_element(auxiliary_choices)
      choice.append(die_idx)
    b.login()
    retval = b.choose_auxiliary_dice(self.game_id, action, die_idx)
    if not (retval and retval.status == 'ok'):
      self.bug("API choose_auxiliary_dice(%s, %s, %s) unexpectedly failed: %s" % (
        self.game_id, action, die_idx,
        retval and retval.message or "NULL"))
    self._add_php_pre_action_block(b)
    self.log.append({
      'type': 'reactToAuxiliary',
      'retval': retval,
      'choice': choice,
    })
    self._add_php_post_action_block(b)
    self.record_load_game_data()

  def _game_action_choose_reserve_dice_player(self, b, playerData):
    reserve_choices = []
    choices = [ 'decline', 'add', ]
    for die_idx in range(len(playerData['activeDieArray'])):
      die = playerData['activeDieArray'][die_idx]
      if 'Reserve' in die['skills']:
        reserve_choices.append(die_idx)
    if len(reserve_choices) == 0:
      self.bug("In choose_reserve_dice for %s with no reserve dice to choose: %s" % (
        b.username, playerData))
    action = self._random_array_element(choices)
    die_idx = None
    choice = [ action, ]
    if action == 'add':
      die_idx = self._random_array_element(reserve_choices)
      choice.append(die_idx)
    # BUG: this shouldn't be necessary
    if die_idx == 0: die_idx = "0"
    b.login()
    retval = b.choose_reserve_dice(self.game_id, action, die_idx)
    if not (retval and retval.status == 'ok'):
      self.bug("API choose_reserve_dice(%s, %s, %s) unexpectedly failed: %s" % (
        self.game_id, action, die_idx,
        retval and retval.message or "NULL"))
    self._add_php_pre_action_block(b)
    self.log.append({
      'type': 'reactToReserve',
      'retval': retval,
      'choice': choice,
    })
    self._add_php_post_action_block(b)
    self.record_load_game_data()

  def _die_fire_min(self, die):
    return min_die_value(die)

  def _die_smallest_fire_turndown(self, die, pre_turndown_value):
    # A wildcard die currently showing 20 must be turned down at least as far as 13,
    # so the minimum turndown is 20 - 13 = 7
    if 'wildcardPropsArray' in die and pre_turndown_value == 20:
      return 7
    return 1

  # Don't cancel fire attacks 50% of the time forever
  #
  # It makes games time out and potentially masks bugs.
  # Instead make it less likely that cancelling will be an option,
  # the more attacks we have cancelled during this game
  def _should_allow_fire_cancellation(self):
    if self.decision_state['cancelled_fire_attacks'] == 0: return True
    return random.random() < (float(1) / (self.decision_state['cancelled_fire_attacks']))

  def _all_known_attack_types(self):
    funcPrefix = '_game_action_start_turn_find_attack_'
    return [ x[len(funcPrefix):] for x in sorted(dir(self)) if x.startswith(funcPrefix) and not x.endswith('Surrender')]

  # Given the list of valid attack types, choose one
  # With low probability, choose an invalid attack type, and expect it to fail.
  def _choose_attack_type(self, validAttackTypes):
    allAttackTypes = self._all_known_attack_types()
    if random.random() < 0.01:
      # Note: if the attack type we pick turns out to be valid, we'll
      # fall out of this case and back to the valid attack case
      # (selecting a different attack type).  This is acceptable behavior.
      possiblyInvalidAttackType = self._random_array_element(allAttackTypes)
      if possiblyInvalidAttackType not in validAttackTypes:
        self.decision_state['expected_start_turn_status'] = 'failed'
        return possiblyInvalidAttackType
    elif random.random() < 0.01:
      chosenAttackType = 'Surrender'
      self.decision_state['expected_start_turn_status'] = 'ok'
    chosenAttackType = str(self._random_array_element(validAttackTypes))
    self.decision_state['expected_start_turn_status'] = 'ok'
    return chosenAttackType

  # Given the attack type we've chosen and the list of valid attack
  # types, determine what attack type string to actually submit to the API.
  #
  # * With moderate probability, choose Default (it's fine for this
  #   probability to be relatively high, since players use Default
  #   a lot, and this shouldn't get in the way of exercising code paths)
  # * It's okay to choose an ambiguous attack type; the caller needs
  #   to parse the return value in that case.
  def _attack_type_to_submit(self, chosenAttackType):
    if random.random() > 0.25:
      return chosenAttackType
    if self.decision_state['expected_start_turn_status'] != 'ok':
      return chosenAttackType
    if chosenAttackType in ['Surrender']:
      return chosenAttackType
    return 'Default'

  def _game_action_adjust_fire_dice_player(self, b, playerData, opponentData):
    is_power_turndown = 'Power' in self.loaded_data['validAttackTypeArray']
    turndown_choices = []
    attacker_sum = 0
    attacker_sides = 0
    turndown_avail = 0
    konstant_values = []
    for die_idx in range(len(playerData['activeDieArray'])):
      die = playerData['activeDieArray'][die_idx]
      if 'IsAttacker' in die['properties']:
        attacker_sum += die['value']
        attacker_sides += die['sides']
        if 'Konstant' in die['skills']:
          konstant_values.append(die['value'])
      elif 'Fire' in die['skills'] and die['value'] > self._die_fire_min(die):
        turndown_choices.append(die_idx)
        turndown_avail += die['value'] - self._die_fire_min(die)
    defender_sum = 0
    for die in opponentData['activeDieArray']:
      if 'IsAttackTarget' in die['properties']:
        defender_sum += die['value']
    choices = []
    if self._should_allow_fire_cancellation():
      choices.append('cancel')
    if len(turndown_choices) > 0: choices.append('turndown')
    if is_power_turndown and attacker_sum >= defender_sum: choices.append('no_turndown')
    choice = self._random_array_element(choices)
    idx_array = []
    value_array = []
    if choice == 'cancel':
      self.decision_state['cancelled_fire_attacks'] += 1
    if choice == 'no_turndown': pass
    if choice == 'turndown':
      if is_power_turndown:
        min_needed = max(1, defender_sum - attacker_sum)
        max_needed = min(attacker_sides - attacker_sum, turndown_avail)
        still_needed = self._random_array_element(range(min_needed, max_needed + 1))
      else:
        while defender_sum < attacker_sum and len(konstant_values) > 0:
          konstant_choice = konstant_values.pop()
          attacker_sum -= konstant_choice * 2
        still_needed = (defender_sum - attacker_sum)
      turndown_to = {}
      while still_needed > 0:
        die_idx = self._random_array_element(turndown_choices)
        turndown_to.setdefault(die_idx, playerData['activeDieArray'][die_idx]['value'])
        if turndown_to[die_idx] > self._die_fire_min(playerData['activeDieArray'][die_idx]):
          turndown_increment = self._die_smallest_fire_turndown(playerData['activeDieArray'][die_idx], turndown_to[die_idx])
          if turndown_increment > still_needed: continue
          turndown_to[die_idx] -= turndown_increment
          still_needed -= turndown_increment
      for [die_idx, die_value] in sorted(turndown_to.items()):
        idx_array.append(die_idx)
        value_array.append(die_value)
    b.login()
    retval = b.adjust_fire_dice(
      self.game_id, choice, idx_array, value_array,
      self.loaded_data['roundNumber'], self.loaded_data['timestamp'])
    if not (retval and retval.status == 'ok'):
      self.bug("API adjust_fire_dice(%s, %s, %s, %s, %s, %s) unexpectedly failed: %s" % (
        self.game_id, choice, idx_array, value_array,
        self.loaded_data['roundNumber'], self.loaded_data['timestamp'],
        retval and retval.message or "NULL"))
    self._add_php_pre_action_block(b)
    self.log.append({
      'type': 'adjustFire',
      'retval': retval,
      'roundNumber': self.loaded_data['roundNumber'],
      'choice': choice,
      'idx_array': idx_array,
      'value_array': value_array,
    })
    self._add_php_post_action_block(b)
    self.record_load_game_data()

  def _game_action_react_to_initiative_player(self, b, playerData, opponentData):
    focus_choices = []
    chance_choices = []
    for die_idx in range(len(playerData['activeDieArray'])):
      die = playerData['activeDieArray'][die_idx]
      if 'Focus' in die['skills']:
        focus_choices.append(die_idx)
      if 'Chance' in die['skills']:
        chance_choices.append(die_idx)
    choices = [ 'decline', ]
    if len(focus_choices) > 0:
      if self._can_gain_initiative_using_focus_dice(playerData['activeDieArray'], opponentData['activeDieArray']):
        choices.append('focus')
    if len(chance_choices) > 0:
      choices.append('chance')
    if len(choices) == 1:
      self.bug("In REACT_TO_INITIATIVE, but decline is the only valid choice")
    choice = self._random_array_element(choices)
    idx_array = []
    value_array = []
    if choice == 'decline':
      pass
    if choice == 'chance':
      idx_array.append(self._random_array_element(chance_choices))
      value_array.append('reroll')
    if choice == 'focus':
      for die_idx in focus_choices:
        idx_array.append(die_idx)
        value_array.append(min_die_value(playerData['activeDieArray'][die_idx]))
    b.login()
    retval = b.react_to_initiative(
      self.game_id, choice, idx_array, value_array,
      self.loaded_data['roundNumber'], self.loaded_data['timestamp'])
    if not (retval and retval.status == 'ok'):
      self.bug("API react_to_initiative(%s, %s, %s, %s, %s, %s) unexpectedly failed: %s" % (
        self.game_id, choice, idx_array, value_array,
        self.loaded_data['roundNumber'], self.loaded_data['timestamp'],
        retval and retval.message or "NULL"))
    self._add_php_pre_action_block(b)
    self.log.append({
      'type': 'reactToInitiative',
      'retval': retval,
      'roundNumber': self.loaded_data['roundNumber'],
      'choice': choice,
      'idx_array': idx_array,
      'value_array': value_array,
    })
    self._add_php_post_action_block(b)
    self.record_load_game_data()

  def _game_action_specify_dice_player(self, b, playerData):
    swing_array = {}
    if playerData['swingRequestArray']:
      unique_swing_among_swing = playerData['button']['name'] in ['Guillermo', 'Oregon']
      unique_swing_among_dice = playerData['button']['name'] == 'Gordo'
      for swing_type in sorted(playerData['swingRequestArray'].keys()):
        [swing_min, swing_max] = playerData['swingRequestArray'][swing_type]
        swing_choice = self._random_array_element(range(swing_min, swing_max + 1))
        # N.B. These loops shouldn't get stuck because Gordo has only 5 dice (so 6 with an aux),
        # and the most constrained swing type, V, has 7 > 6 choices
        if unique_swing_among_swing:
          while swing_choice in swing_array.values():
            swing_choice = self._random_array_element(range(swing_min, swing_max + 1))
        if unique_swing_among_dice:
          while swing_choice in [die['sides'] for die in playerData['activeDieArray']] + swing_array.values():
            swing_choice = self._random_array_element(range(swing_min, swing_max + 1))
        swing_array[swing_type] = swing_choice

    option_array = {}
    if playerData['optRequestArray']:
      if type(playerData['optRequestArray']) == dict:
        for option_idx in sorted(playerData['optRequestArray'].keys()):
          option_choice = str(self._random_array_element(playerData['optRequestArray'][option_idx]))
          option_array[str(option_idx)] = option_choice
      elif type(playerData['optRequestArray']) == list:
        for option_idx in range(len(playerData['optRequestArray'])):
          option_choice = str(self._random_array_element(playerData['optRequestArray'][option_idx]))
          option_array[str(option_idx)] = option_choice
      else:
        self.bug("Could not parse optRequestArray: %s" % playerData['optRequestArray'])

    b.login()
    retval = b.submit_die_values(
      self.game_id, swing_array, option_array,
      self.loaded_data['roundNumber'], self.loaded_data['timestamp'])
    if not (retval and retval.status == 'ok'):
      self.bug("API submit_die_values(%s, %s, %s, %s, %s) unexpectedly failed: %s" % (
	self.game_id, swing_array, option_array,
	self.loaded_data['roundNumber'], self.loaded_data['timestamp'],
        retval and retval.message or "NULL"))
    self._add_php_pre_action_block(b)
    self.log.append({
      'type': 'submitDieValues',
      'retval': retval,
      'roundNumber': self.loaded_data['roundNumber'],
      'swing_array': swing_array,
      'option_array': option_array,
    })
    self._add_php_post_action_block(b)
    self.record_load_game_data()

  # Is the return value from submitTurn expected based on what was submitted
  def _submit_turn_retval_is_expected(self, retval, attackType):
    if not retval: return False
    if retval.status == self.decision_state['expected_start_turn_status']: return True
    if attackType == 'Default' \
       and self.decision_state['expected_start_turn_status'] == 'ok' and retval.status == 'failed' \
       and retval.message.startswith('Default attack is ambiguous'):
      return True
    return False

  def _game_action_start_turn_player(self, b, attackerData, defenderData):
    attackTypes = self.loaded_data['validAttackTypeArray']
    if len(attackTypes) == 0:
      self.bug("No valid attack types found during START_TURN")
    chosenAttackType = self._choose_attack_type(attackTypes)
    chosenAttackFunction = '_game_action_start_turn_find_attack_%s' % chosenAttackType
    if not hasattr(self, chosenAttackFunction):
      self.bug("LoggingBMClient has no function %s to perform %s attack" % (
        chosenAttackFunction, chosenAttackType))
    [attacker_indices, defender_indices] = getattr(self, chosenAttackFunction)(b, attackerData, defenderData)
    attack = self._generate_attack_array(attacker_indices, defender_indices)
    turbo_vals = self._generate_turbo_val_arrays(attacker_indices, chosenAttackType)
    attackTypeToSubmit = self._attack_type_to_submit(chosenAttackType)
    b.login()
    retval = b.submit_turn(
      self.game_id, self.attacker, self.defender, attack,
      attackTypeToSubmit, self.loaded_data['roundNumber'],
      self.loaded_data['timestamp'], turbo_vals)
    if not self._submit_turn_retval_is_expected(retval, attackTypeToSubmit):
      self.bug("API submit_turn(%s, %s, %s, %s, %s, %s, %s, %s) unexpectedly got status %s, but expected %s: %s" % (
	self.game_id, self.attacker, self.defender,
	attack, attackTypeToSubmit, self.loaded_data['roundNumber'],
	self.loaded_data['timestamp'], turbo_vals,
        retval.status, self.decision_state['expected_start_turn_status'],
        retval and retval.message or "NULL"))
    self._add_php_pre_action_block(b)
    self.log.append({
      'type': 'submitTurn',
      'retval': retval,
      'roundNumber': self.loaded_data['roundNumber'],
      'attackType': attackTypeToSubmit,
      'attacker_indices': attacker_indices,
      'defender_indices': defender_indices,
      'all_attackers': self.loaded_data['playerDataArray'][self.attacker]['activeDieArray'],
      'all_defenders': self.loaded_data['playerDataArray'][self.defender]['activeDieArray'],
      'attacker': self.attacker,
      'defender': self.defender,
      'turbo_vals': turbo_vals,
    })
    self._add_php_post_action_block(b)
    self.record_load_game_data()
    if retval.status == 'failed':
      self.check_load_game_data_consistency_after_failed_submit_turn()

  def _game_action_react_to_initiative(self):
    if self._waiting_on_player(0) and self._waiting_on_player(1):
      self.bug("Game found waiting on both players during REACT_TO_INITIATIVE")
    if self._waiting_on_player(0):
      return self._game_action_react_to_initiative_player(self.player_client,
        self.loaded_data['playerDataArray'][0], self.loaded_data['playerDataArray'][1])
    if self._waiting_on_player(1):
      return self._game_action_react_to_initiative_player(self.opponent_client,
        self.loaded_data['playerDataArray'][1], self.loaded_data['playerDataArray'][0])
    self.bug("Game found waiting on neither player during REACT_TO_INITIATIVE")

  def _game_action_adjust_fire_dice(self):
    if self._waiting_on_player(0) and self._waiting_on_player(1):
      self.bug("Game found waiting on both players during ADJUST_FIRE_DICE")
    if self._waiting_on_player(0):
      return self._game_action_adjust_fire_dice_player(self.player_client,
        self.loaded_data['playerDataArray'][0], self.loaded_data['playerDataArray'][1])
    if self._waiting_on_player(1):
      return self._game_action_adjust_fire_dice_player(self.opponent_client,
        self.loaded_data['playerDataArray'][1], self.loaded_data['playerDataArray'][0])
    self.bug("Game found waiting on neither player during ADJUST_FIRE_DICE")

  def _game_action_choose_reserve_dice(self):
    if self._waiting_on_player(0) and self._waiting_on_player(1):
      self.bug("Game found waiting on both players during CHOOSE_RESERVE_DICE")
    if self._waiting_on_player(0):
      return self._game_action_choose_reserve_dice_player(self.player_client,
        self.loaded_data['playerDataArray'][0])
    if self._waiting_on_player(1):
      return self._game_action_choose_reserve_dice_player(self.opponent_client,
        self.loaded_data['playerDataArray'][1])
    self.bug("Game found waiting on neither player during CHOOSE_RESERVE_DICE")

  def _game_action_choose_auxiliary_dice(self):
    waiting_players = []
    if self._waiting_on_player(0):
      waiting_players.append([
        self.player_client, self.loaded_data['playerDataArray'][0]])
    if self._waiting_on_player(1) and not self.state['opponent_aux_chosen']:
      waiting_players.append([
	self.opponent_client, self.loaded_data['playerDataArray'][1]])
    if len(waiting_players) == 0:
      self.bug("Game found waiting on neither player during CHOOSE_AUXILIARY_DICE")
    [chosen_client, chosen_array] = self._random_array_element(waiting_players)
    if chosen_client == self.opponent_client:
      self.state['opponent_aux_chosen'] = True
    return self._game_action_choose_auxiliary_dice_player(chosen_client, chosen_array)

  def _game_action_specify_dice(self):
    waiting_players = []
    if self._waiting_on_player(0):
      waiting_players.append([
        self.player_client, self.loaded_data['playerDataArray'][0]])
    if self._waiting_on_player(1):
      waiting_players.append([
	self.opponent_client, self.loaded_data['playerDataArray'][1]])
    if len(waiting_players) == 0:
      self.bug("Game found waiting on neither player during SPECIFY_DICE")
    [chosen_client, chosen_array] = self._random_array_element(waiting_players)
    return self._game_action_specify_dice_player(chosen_client, chosen_array)

  def _game_action_start_turn(self):
    if self._waiting_on_player(0) and self._waiting_on_player(1):
      self.bug("Game found waiting on both players during START_TURN")
    if self._waiting_on_player(0):
      self.attacker = 0
      self.defender = 1
      return self._game_action_start_turn_player(self.player_client,
        self.loaded_data['playerDataArray'][0],
        self.loaded_data['playerDataArray'][1])
    if self._waiting_on_player(1):
      self.attacker = 1
      self.defender = 0
      return self._game_action_start_turn_player(self.opponent_client,
        self.loaded_data['playerDataArray'][1],
        self.loaded_data['playerDataArray'][0])
    self.bug("Game found waiting on neither player during START_TURN")

  def _create_game_failure_is_okay(self, button1, button2, custom_recipe_array, retval):
    # We only ever expect game creation failures with CustomBM
    if not 'CustomBM' in [button1, button2]: return False

    # retval should always be well-formed
    if not retval or not hasattr(retval, 'status') or not hasattr(retval, 'message'): return False

    # the message should never say anything about an internal error
    if 'internal error' in retval.message.lower(): return False

    # Otherwise assume failure is okay, for now
    return True

  def bug(self, message):
    self.log.append({
      'type': 'bug',
      'message': message,
    })
    self.finish_game_log()
    raise ValueError, message

  def start_game_log(self, n):
    self.int_id = '%05d' % n
    self.log = []
    self.log.append({
      'type': 'start',
      'id': self.int_id,
    })

  def finish_game_log(self):
    self.log.append({
      'type': 'finish',
    })
    f = open('output/game%s.pck' % self.int_id, 'w')
    pickle.dump(self.log, f)
    f.close()

  def record_create_game(self, button1, button2, max_wins=3, use_prev_game=False):
    self.player_client.login()
    if 'CustomBM' in [button1, button2]:
      custom1 = get_fuzzy_recipe() if button1 == 'CustomBM' else ''
      custom2 = get_fuzzy_recipe() if button2 == 'CustomBM' else ''
      custom_recipe_array = [ custom1, custom2, ]
    else:
      custom_recipe_array = []
    retval = self.player_client.create_game(button1, obutton=button2, opponent=self.opponent_client.username, max_wins=max_wins, use_prev_game=use_prev_game, custom_recipe_array=custom_recipe_array)
    if not retval:
      self.bug("create_game(%s, %s, %d, custom_recipe_array=%s) unexpectedly gave invalid response: %s" % (
        button1, button2, max_wins, custom_recipe_array,
        retval and retval.message or "NULL"))
    if not retval.status == 'ok':
      if not self._create_game_failure_is_okay(button1, button2, custom_recipe_array, retval):
        self.bug("create_game(%s, %s, %d, custom_recipe_array=%s) unexpectedly failed: %s" % (
          button1, button2, max_wins, custom_recipe_array,
          retval and retval.message or "NULL"))
      self.log.append({
        'type': 'createGame',
        'retval': retval,
        'player1': self.player_client.username,
        'player2': self.opponent_client.username,
        'button1': button1,
        'button2': button2,
        'max_wins': max_wins,
        'custom_recipe_array': custom_recipe_array,
      })
      self.game_id = None
    else:
      self.game_id = retval.data['gameId']
      self.log.append({
        'type': 'createGame',
        'retval': retval,
        'player1': self.player_client.username,
        'player2': self.opponent_client.username,
        'button1': button1,
        'button2': button2,
        'max_wins': max_wins,
        'custom_recipe_array': custom_recipe_array,
      })
      self.record_load_game_data()
    return self.game_id

  def reject_created_game(self):
    if type(self.loaded_data['gameSkillsInfo']) == dict:
      skills_in_game = self.loaded_data['gameSkillsInfo'].keys()
    else:
      skills_in_game = []
    for mandatory_skill in self.reject_games['without_skill']:
      found_skill_option = False
      if type(mandatory_skill) == list:
        for mandatory_skill_option in mandatory_skill:
          if mandatory_skill_option in skills_in_game: found_skill_option = True
      else:
        if mandatory_skill in skills_in_game: found_skill_option = True
      if not found_skill_option: return True
    for forbidden_skill in self.reject_games['with_skill']:
      if forbidden_skill in skills_in_game: return True
    return False

  def record_load_game_data(self):
    self.player_client.login()
    retval = self.player_client.load_game_data(self.game_id)
    if not retval:
      self.bug("load_game_data(%s) unexpectedly returned False" % (
        self.game_id))
    if not (retval and retval.status == 'ok'):
      self.bug("load_game_data(%s) unexpectedly failed: %s" % (
        self.game_id,
        retval and retval.message or "NULL"))
    updating = True
    if updating and self.loaded_data:
      self.log.append({
        'type': 'updatedGameData',
        'newdata': retval.data,
      })
    else:
      self.log.append({
        'type': 'initialGameData',
        'data': retval.data,
      })
    self.log.append({
      'type': 'loadGameData',
    })
    self.loaded_data = retval.data

  # The backend must not change the game state when it rejects an attack.
  # If an attack has just been rejected, look in the log for the
  # two most recent game data loads, and raise a bug if they don't match.
  def check_load_game_data_consistency_after_failed_submit_turn(self):
    game_data_entries = [
      entry.get('newdata', entry.get('data')) for entry in self.log if entry['type'] in ['updatedGameData', 'initialGameData']
    ]
    game_data_before_failure = game_data_entries[-2]
    game_data_after_failure = game_data_entries[-1]
    if game_data_before_failure != game_data_after_failure:
      self.bug("game data was inconsistent before vs after a submitTurn call which returned a failure")
    

  def next_game_action(self):
    while self.is_game_state_active():
      self.handle_active_game_state()
    return True

  def is_game_state_active(self):
    return self.loaded_data['gameState'] not in [ 'END_GAME', 'CANCELLED', ]

  def handle_active_game_state(self):
    state = self.loaded_data['gameState']
    if state == 'START_TURN': self._game_action_start_turn()
    elif state == 'SPECIFY_DICE': self._game_action_specify_dice()
    elif state == 'REACT_TO_INITIATIVE': self._game_action_react_to_initiative()
    elif state == 'CHOOSE_RESERVE_DICE': self._game_action_choose_reserve_dice()
    elif state == 'CHOOSE_AUXILIARY_DICE': self._game_action_choose_auxiliary_dice()
    elif state == 'ADJUST_FIRE_DICE': self._game_action_adjust_fire_dice()
    else:
      self.bug("LoggingBMClient.handle_active_game_state() has no action for state %s" % state)

  def log_test_game(self, n, button1, button2, use_prev_game=False):
    self.reset_internal_state()
    self.start_game_log(n)
    self.record_create_game(button1, button2, use_prev_game=use_prev_game)
    if self.game_id and not self.reject_created_game():
      self.next_game_action()
    self.finish_game_log()
    return self.game_id

  def initialize_precreated_game(self, n, game_id, client_list):
    self.game_id = game_id
    self.start_game_log(n)
    self._load_game_data_then_import_player_clients(client_list)
    self.record_load_game_data()

  # Return game ID if the game is finished (similar to log_test_game()), False if it's still active
  def progress_game_towards_completion(self):
    if self.is_game_state_active():
      self.handle_active_game_state()
    if self.is_game_state_active():
      return False
    self.finish_game_log()
    return self.game_id

class ButtonSelectionClient():
  """
  This class is used to randomly select buttons to be used in a new game.
  """

  def _process_button_names(self):
    self.button_names = []
    self.unimplemented_buttons = []
    self.implemented_buttons = []
    self.buttons_with_skill_or_type = {}
    for known_type in [ 'Twin', ]:
      self.buttons_with_skill_or_type[known_type] = []

    button_response = self.client.load_button_names()
    for button in button_response.data:
      name = button['buttonName']
      self.button_names.append(name)
      if button['hasUnimplementedSkill']:
        self.unimplemented_buttons.append(name)
      else:
        self.implemented_buttons.append(name)
        for skill in button['dieSkills']:
          self.buttons_with_skill_or_type.setdefault(skill, [])
          self.buttons_with_skill_or_type[skill].append(name)
        if ',' in button['recipe']:
          self.buttons_with_skill_or_type['Twin'].append(name)

  def __init__(self, client):
    self.client = client
    self._process_button_names()

  def select_button(self, criteria):
    KNOWN_KEYS = [ 'name', 'random', 'unimplemented', 'and_skills', 'or_skills', 'skipname', ]
    for key in sorted(criteria.keys()):
      if not key in KNOWN_KEYS:
        raise ValueError("Requested search based on key %s which is unknown" % key)

    options = []
    if 'name' in criteria:
      for name in criteria['name']:
        if not name in self.button_names:
          raise ValueError("Requested button name %s was not found in list" % name)
      options = criteria['name']
    elif 'random' in criteria:
      options = [ '__random', ]
    elif 'unimplemented' in criteria:
      options = self.unimplemented_buttons
    elif 'and_skills' in criteria:
      intersect = None
      for skill in criteria['and_skills']:
        s = set(self.buttons_with_skill_or_type[skill])
        if intersect:
          intersect = intersect.intersection(s)
        else:
          intersect = s
      options = list(intersect)
    elif 'or_skills' in criteria:
      union = None
      for skill in criteria['or_skills']:
        s = set(self.buttons_with_skill_or_type[skill])
        if union:
          union = union.union(s)
        else:
          union = s
      options = list(union)
    else:
      options = self.implemented_buttons

    if 'skipname' in criteria:
      for name in criteria['skipname']:
        if name in options:
          options.remove(name)

    return random_array_element(options)
