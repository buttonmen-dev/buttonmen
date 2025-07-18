// namespace for this "module"
var ButtonSelection = {
  'activity': {},
};

ButtonSelection.getButtonSelectionData = function(callback) {
  Env.callAsyncInParallel(
    [
      { 'func': Api.getButtonData, 'args': [ null ] },
      Api.getPlayerData,
    ], callback);
};

//////////////////////////////////////////////////////////////////////////
//// These functions generate and return pieces of HTML

ButtonSelection.getSelectRow = function(rowname, selectname, valuedict,
                                greydict, selectedval, isComboBox,
                                blankOption) {
  var selectRow = $('<tr>');
  selectRow.append($('<th>', {'text': rowname + ':', }));

  var selectTd = ButtonSelection.getSelectTd(rowname, selectname, valuedict,
                                     greydict, selectedval, isComboBox,
                                     null, blankOption);

  selectRow.append(selectTd);
  return selectRow;
};

ButtonSelection.getSelectTd = function(nametext, selectname, valuedict,
                               greydict, selectedval, isComboBox,
                               onChangeEvent, blankOption) {
  var select = $('<select>', {
    'id': selectname,
    'name': selectname,
    'onchange': onChangeEvent,
  });

  if (isComboBox) {
    select.addClass('chosen-select');
  }

  var optionlist = ButtonSelection.getSelectOptionList(
    nametext, valuedict, greydict, selectedval, blankOption);
  var optioncount = optionlist.length;
  for (var i = 0; i < optioncount; i++) {
    select.append(optionlist[i]);
  }

  var selectTd = $('<td>');
  selectTd.append(select);

  return selectTd;
};

ButtonSelection.getSelectOptionList = function(
    nametext, valuedict, greydict, selectedval, blankOption) {

  var optionlist = [];

  if (blankOption !== undefined) {
    // If blanks are allowed, then display a special entry for that
    optionlist.push($('<option>', {
      'value': '',
      'text': blankOption,
    }));
  } else {
    // If there's no default or the selected value doesn't exist
    // in the dropdown, put an invalid default value first
    if ((selectedval === null) || !(selectedval in valuedict)) {
      optionlist.push($('<option>', {
        'value': '',
        'class': 'yellowed',
        'text': 'Choose ' + nametext.toLowerCase(),
      }));
    }
  }

  $.each(valuedict, function(key, value) {
    var selectopts = {
      'value': key,
      'label': value,
      'text': value,
    };
    if (selectedval == key) {
      selectopts.selected = 'selected';
    }
    if ((greydict !== null) && (greydict[key])) {
      selectopts['class'] = 'greyed';
    }
    optionlist.push($('<option>', selectopts));
  });
  return optionlist;
};

ButtonSelection.getCustomRecipeTd = function(player) {
  var inputFieldName = player + '_custom_recipe';
  var currentValue =
    (player == 'player' ?
    ButtonSelection.activity.playerCustomRecipe :
    ButtonSelection.activity.opponentCustomRecipe);

  var customRecipeInput = $('<input>', {
    'id': inputFieldName,
    'name': inputFieldName,
    'type': 'text',
    'val': currentValue,
    'style': 'display: none;',
  });

  var customRecipeTd = $('<td>');
  customRecipeTd.append($('<span>', {
    'text': 'Custom Recipe: ',
    'style': 'display: none;',
  }));
  customRecipeTd.append(customRecipeInput);

  return customRecipeTd;
};

ButtonSelection.getButtonSelectTd = function(player, isComboBox) {
  if (player == 'player') {
    return ButtonSelection.getSelectTd(
      'Your button',
      'player_button',
      ButtonSelection.activity.buttonList.player,
      ButtonSelection.activity.buttonGreyed,
      ButtonSelection.activity.playerButton,
      isComboBox,
      'ButtonSelection.reactToButtonChange("' + player + '", $(this).val())');
  } else if (ButtonSelection.activity.buttonLimits.opponent.button_sets.ANY &&
      ButtonSelection.activity.buttonLimits.opponent.tourn_legal.ANY &&
      ButtonSelection.activity.buttonLimits.opponent.die_skills.ANY) {
    return ButtonSelection.getSelectTd(
      'Opponent\'s button',
      'opponent_button',
      ButtonSelection.activity.buttonList.opponent,
      ButtonSelection.activity.buttonGreyed,
      ButtonSelection.activity.opponentButton,
      isComboBox,
     'ButtonSelection.reactToButtonChange("' + player + '", $(this).val())',
     'Any button');
  } else {
    return ButtonSelection.getSelectTd(
      'Opponent\'s button',
      'opponent_button',
      ButtonSelection.activity.buttonList.opponent,
      ButtonSelection.activity.buttonGreyed,
      ButtonSelection.activity.opponentButton,
      isComboBox);
  }
};

ButtonSelection.reactToButtonChange = function(player, button) {
  var customRecipeControls =
    $('#' + player + '_custom_recipe').parent().children();

  if (button == 'CustomBM') {
    customRecipeControls.show();
  } else {
    customRecipeControls.hide();
  }
};

ButtonSelection.updateButtonSelectTd = function(player) {
  var selectid;
  var optionlist;
  if (player == 'player') {
    selectid = 'player_button';
    optionlist = ButtonSelection.getSelectOptionList(
      'Your button',
      ButtonSelection.activity.buttonList.player,
      ButtonSelection.activity.buttonGreyed,
      ButtonSelection.activity.playerButton
    );
  } else if (ButtonSelection.activity.buttonLimits.opponent.button_sets.ANY &&
      ButtonSelection.activity.buttonLimits.opponent.tourn_legal.ANY &&
      ButtonSelection.activity.buttonLimits.opponent.die_skills.ANY) {
    selectid = 'opponent_button';
    optionlist = ButtonSelection.getSelectOptionList(
      'Opponent\'s button',
      ButtonSelection.activity.buttonList.opponent,
      ButtonSelection.activity.buttonGreyed,
      ButtonSelection.activity.opponentButton,
      'Any button'
    );
  } else {
    selectid = 'opponent_button';
    optionlist = ButtonSelection.getSelectOptionList(
      'Opponent\'s button',
      ButtonSelection.activity.buttonList.opponent,
      ButtonSelection.activity.buttonGreyed,
      ButtonSelection.activity.opponentButton
    );
  }

  var optioncount = optionlist.length;
  var select = $('#' + selectid);
  select.empty();
  for (var i = 0; i < optioncount; i++) {
    select.append(optionlist[i]);
  }

  select.trigger('chosen:updated');
};

ButtonSelection.updateButtonList = function(player, limitid) {
  if (limitid) {
    var buttonText = $('#' + player + '_button_chosen > a > span').text();
    var delimiterIdx = buttonText.indexOf(':');
    if (delimiterIdx >= 0) {
      ButtonSelection.activity[player + 'Button'] =
        buttonText.substr(0, delimiterIdx);
    }

    var optsTag = 
      '#' + ButtonSelection.getLimitSelectid(player, limitid) + ' option';
    $.each($(optsTag), function() {
      ButtonSelection.activity.buttonLimits[player][limitid][$(this).val()] = 
        false;
    });
    $.each($(optsTag + ':selected'), function() {
      ButtonSelection.activity.buttonLimits[player][limitid][$(this).val()] = 
        true;
    });
  }

  if (ButtonSelection.activity.buttonLimits[player].button_sets.ANY &&
      ButtonSelection.activity.buttonLimits[player].tourn_legal.ANY &&
      ButtonSelection.activity.buttonLimits[player].die_skills.ANY) {
    if (Config.siteType == 'development' || Config.siteType == 'staging') {
      ButtonSelection.activity.buttonList[player] = {
        '__random': 'Random button',
        'CustomBM': 'Custom recipe',
      };
    } else {
      ButtonSelection.activity.buttonList[player] = {
        '__random': 'Random button',
      };
    }
  } else if ((Config.siteType == 'development' ||
              Config.siteType == 'staging') &&
             ButtonSelection.activity.buttonLimits[player].button_sets[
               'limit_' + player + '_button_sets_custombm'
             ]) {
    ButtonSelection.activity.buttonList[player] = {
      'CustomBM': 'Custom recipe',
    };
  } else {
    ButtonSelection.activity.buttonList[player] = {};
  }

  var choiceid;
  var hasSkill;
  $.each(Api.button.list, function(button, buttoninfo) {

    // If the user has specified any limits based on button set,
    // skip buttons which are not in one of the sets the user has
    // specified
    if (!ButtonSelection.activity.buttonLimits[player].button_sets.ANY) {
      choiceid = ButtonSelection.getChoiceId(
        player, 'button_sets', buttoninfo.buttonSet);
      if (!ButtonSelection.activity.buttonLimits[player].button_sets[choiceid])
      {
        return true;
      }
    }

    // If the user has specified any limits based on TL status,
    // skip buttons which do not have the status the user has specified
    if (!ButtonSelection.activity.buttonLimits[player].tourn_legal.ANY) {
      if (buttoninfo.isTournamentLegal) {
        choiceid = ButtonSelection.getChoiceId(player, 'tourn_legal', 'yes');
      } else {
        choiceid = ButtonSelection.getChoiceId(player, 'tourn_legal', 'no');
      }
      if (!ButtonSelection.activity.buttonLimits[player].tourn_legal[choiceid])
      {
        return true;
      }
    }

    // If the user has specified any limits based on die skills,
    // skip buttons which do not have at least one requested skills
    if (!ButtonSelection.activity.buttonLimits[player].die_skills.ANY) {
      hasSkill = false;
      $.each(buttoninfo.dieSkills, function(i, dieSkill) {
        choiceid = ButtonSelection.getChoiceId(player, 'die_skills', dieSkill);
        if (ButtonSelection.activity.buttonLimits[player].die_skills[choiceid])
        {
          hasSkill = true;
        }
      });
      if (!hasSkill) {
        return true;
      }
    }

    // remove CustomBM explicitly, since this is handled specially
    if ('CustomBM' == button) {
      return true;
    }

    ButtonSelection.activity.buttonList[player][button] =
      ButtonSelection.activity.buttonRecipe[button];
  });

  // if we're updating an existing button select dropdown, change it now
  if (limitid) {
    ButtonSelection.updateButtonSelectTd(player);
  }
};

ButtonSelection.getButtonLimitRow = function(
  desctext, limitid, choices, multi, player
) {
  // Default to multi-selects
  if (multi === undefined) { multi = true; }
  
  // Default to applying limits to your own button
  if (player === undefined) { player = 'player'; }

  var limitRow = $('<tr>');
  limitRow.append(ButtonSelection.getButtonLimitTd(
    player, desctext, limitid, choices, multi));
  return limitRow;
};

ButtonSelection.getButtonLimitTd = function(
  player, desctext, limitid, choices, multi
) {
  var limitTd = $('<td>');
  var limitSubtable = $('<table>');
  var limitSubrow = $('<tr>');
  limitSubrow.append($('<td>', {'text': desctext + ' ', }));
  var selectId = ButtonSelection.getLimitSelectid(player, limitid);
  var limitSelect = $('<select>', {
    'id': selectId,
    'name': selectId,
    'multiple': multi,
    'onchange': 'ButtonSelection.updateButtonList("' + player + '", "' +
                limitid + '")',
  });

  // dicts in javascript don't fully work - make a separate array
  // of keys so we can sort it
  var choicekeys = [];
  $.each(choices, function(choice) {
    choicekeys.push(choice);
  });
  choicekeys.sort();

  var anyOptionOpts = {
    'value': 'ANY',
    'label': 'ANY',
    'text': 'ANY',
  };
  if (ButtonSelection.activity.buttonLimits[player][limitid].ANY) {
    anyOptionOpts.selected = 'selected';
  }
  limitSelect.append($('<option>', anyOptionOpts));

  var inputid;
  $.each(choicekeys, function(i, choice) {
    inputid = ButtonSelection.getChoiceId(player, limitid, choice);
    var selectopts = {
      'value': inputid,
      'label': choice,
      'text': choice,
    };
    if (ButtonSelection.activity.buttonLimits[player][limitid][inputid]) {
      selectopts.selected = 'selected';
    }
    limitSelect.append($('<option>', selectopts));
  });
  limitSubrow.append(limitSelect);
  limitSubtable.append(limitSubrow);
  limitTd.append(limitSubtable);

  return limitTd;
};

ButtonSelection.getLimitSelectid = function(player, limitid) {
  return 'limit_' + player + '_' + limitid;
};

ButtonSelection.getChoiceId = function(player, limitid, choice) {
  return 'limit_' + player + '_' + limitid + '_' +
    choice.toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_]/g, '');
};

// This initializes button limits only if they're unset; otherwise
// it leaves them alone
ButtonSelection.initializeButtonLimits = function() {
  if (!('buttonLimits' in ButtonSelection.activity)) {
    ButtonSelection.activity.buttonLimits = {};
  }
  var choiceid;
  var players = ['player', 'opponent'];
  var player;
  for (var i = 0; i < 2; i++) {
    player = players[i];
    if (!(player in ButtonSelection.activity.buttonLimits)) {
      ButtonSelection.activity.buttonLimits[player] = {};
    }

    if (!('button_sets' in ButtonSelection.activity.buttonLimits[player])) {
      ButtonSelection.activity.buttonLimits[player].button_sets = {
        'ANY': true,
      };
    }
    $.each(ButtonSelection.activity.buttonSets, function(buttonset) {
      choiceid = ButtonSelection.getChoiceId(player, 'button_sets', buttonset);
      if (!(choiceid in 
            ButtonSelection.activity.buttonLimits[player].button_sets)) {
        ButtonSelection.activity.buttonLimits[player].button_sets[choiceid] =
          false;
      }
    });

    if (!('tourn_legal' in ButtonSelection.activity.buttonLimits[player])) {
      ButtonSelection.activity.buttonLimits[player].tourn_legal = {
        'ANY': true,
      };
    }
    $.each(ButtonSelection.activity.tournLegal, function(yesno) {
      choiceid = ButtonSelection.getChoiceId(player, 'tourn_legal', yesno);
      if (!(choiceid in
            ButtonSelection.activity.buttonLimits[player].tourn_legal)) {
        
        ButtonSelection.activity.buttonLimits[player].tourn_legal[choiceid] =
          false;
      }
    });

    if (!('die_skills' in ButtonSelection.activity.buttonLimits[player])) {
      ButtonSelection.activity.buttonLimits[player].die_skills = {
        'ANY': true,
      };
    }
    $.each(ButtonSelection.activity.dieSkills, function(dieSkill) {
      choiceid = ButtonSelection.getChoiceId(player, 'die_skills', dieSkill);
      if (!(choiceid in 
            ButtonSelection.activity.buttonLimits[player].die_skills)) {
        ButtonSelection.activity.buttonLimits[player].die_skills[choiceid] =
          false;
      }
    });
  }
};

ButtonSelection.loadButtonsIntoDicts = function() {
  // Load buttons and recipes into dicts for use in selects
  ButtonSelection.activity.buttonRecipe = {};
  ButtonSelection.activity.buttonGreyed = {};
  ButtonSelection.activity.buttonSets = {};
  ButtonSelection.activity.dieSkills = {};
  ButtonSelection.activity.tournLegal = {
    'yes': true,
    'no': true,
  };
  ButtonSelection.activity.anyUnimplementedButtons = false;

  $.each(Api.button.list, function(button, buttoninfo) {
    ButtonSelection.activity.buttonSets[buttoninfo.buttonSet] = true;
    $.each(buttoninfo.dieSkills, function(i, dieSkill) {
      ButtonSelection.activity.dieSkills[dieSkill] = true;
    });
    if (buttoninfo.hasUnimplementedSkill) {
      ButtonSelection.activity.buttonRecipe[button] =
        '-- ' + button + ': ' + buttoninfo.recipe;
      ButtonSelection.activity.buttonGreyed[button] = true;
      ButtonSelection.activity.anyUnimplementedButtons = true;
    } else {
      ButtonSelection.activity.buttonRecipe[button] =
        button + ': ' + buttoninfo.recipe;
      ButtonSelection.activity.buttonGreyed[button] = false;
    }
  });  
  
  ButtonSelection.initializeButtonLimits();
  
  if (!('playerButton' in ButtonSelection.activity)) {
    ButtonSelection.activity.playerButton = null;
  }
  
  if (!('opponentButton' in ButtonSelection.activity)) {
    ButtonSelection.activity.opponentButton = null;
  }
  
  // Set the initial list of selectable buttons for each player
  ButtonSelection.activity.buttonList = {};
};

// this expects a type of 'player' or 'opponent'
ButtonSelection.getSingleButtonOptionsTable = function(player) {
  ButtonSelection.updateButtonList(player, null);
    
  var singleButtonOptionsTable = 
    $('<table>', {'id': player + '_button_table', });
    
  // button limit rows
  singleButtonOptionsTable.append(ButtonSelection.getButtonLimitRow(
    'Button set:',
    'button_sets',
    ButtonSelection.activity.buttonSets,
    true,
    player
  ));
  singleButtonOptionsTable.append(ButtonSelection.getButtonLimitRow(
    'Tournament legal:',
    'tourn_legal',
    ButtonSelection.activity.tournLegal,
    false,
    player
  ));
  singleButtonOptionsTable.append(ButtonSelection.getButtonLimitRow(
    'Die skill:',
    'die_skills',
    ButtonSelection.activity.dieSkills,
    true,
    player
  ));
  
  // button selection row
  var selectRow = $('<tr>');
  selectRow.append(ButtonSelection.getButtonSelectTd(player, true));

  singleButtonOptionsTable.append(selectRow);

  // custom button recipe row
  var customRow = $('<tr>');
  customRow.append(ButtonSelection.getCustomRecipeTd(player));
  singleButtonOptionsTable.append(customRow);

  return singleButtonOptionsTable;
};
