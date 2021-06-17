<?php

/**
 * BMInterfacePlayer: interface between GUI and BMGame for player-related requests
 *
 * @author james
 */

/**
 * This class deals with communication between the UI, the game code, and the database
 * pertaining to player-related information
 */

class BMInterfacePlayer extends BMInterface {
    /**
     * Get player info
     *
     * @param int $playerId
     * @return array|NULL
     */
    public function get_player_info($playerId) {
        try {
            $query =
                'SELECT p.*, b.name AS favorite_button, bs.name AS favorite_buttonset, ' .
                    'UNIX_TIMESTAMP(p.last_access_time) AS last_access_time, ' .
                    'UNIX_TIMESTAMP(p.last_action_time) AS last_action_time, ' .
                    'UNIX_TIMESTAMP(p.creation_time) AS creation_time ' .
                'FROM player_view p ' .
                    'LEFT JOIN button b ON b.id = p.favorite_button_id ' .
                    'LEFT JOIN buttonset bs ON bs.id = p.favorite_buttonset_id ' .
                'WHERE p.id = :id';
            $columnReturnTypes = array(
                'id' => 'int',
                'name_ingame' => 'str',
                'name_irl' => 'str_or_null',
                'email' => 'str',
                'is_email_public' => 'bool',
                'status' => 'str',
                'dob_month' => 'int',
                'dob_day' => 'int',
                'pronouns' => 'str_or_null',
                'image_size' => 'int_or_null',
                'autoaccept' => 'bool',
                'autopass' => 'bool',
                'fire_overshooting' => 'bool',
                'uses_gravatar' => 'bool',
                'monitor_redirects_to_game' => 'bool',
                'monitor_redirects_to_forum' => 'bool',
                'automatically_monitor' => 'bool',
                'die_background' => 'str',
                'comment' => 'str_or_null',
                'vacation_message' => 'str_or_null',
                'player_color' => 'str_or_null',
                'opponent_color' => 'str_or_null',
                'neutral_color_a' => 'str_or_null',
                'neutral_color_b' => 'str_or_null',
                'homepage' => 'str_or_null',
                'favorite_button' => 'str_or_null',
                'favorite_buttonset' => 'str_or_null',
                'last_action_time' => 'int_or_null',
                'last_access_time' => 'int_or_null',
                'creation_time' => 'int_or_null',
                'fanatic_button_id' => 'int_or_null',
                'n_games_won' => 'int',
                'n_games_lost' => 'int',
            );
            $parameters = array(':id' => $playerId);
            $rows = self::$db->select_rows($query, $parameters, $columnReturnTypes);
            if (0 == count($rows)) {
                return NULL;
            }
            $playerInfoArray = $rows[0];
        } catch (Exception $e) {
            $this->set_message('Player info get failed: ' . $e->getMessage());
            error_log($this->message);
            return NULL;
        }


        // Some values have defaults
        if ($playerInfoArray['name_irl'] == NULL) {
            $playerInfoArray['name_irl'] = $playerInfoArray['name_ingame'];
        }
        if ($playerInfoArray['player_color'] == NULL) {
            $playerInfoArray['player_color'] = self::DEFAULT_PLAYER_COLOR;
        }
        if ($playerInfoArray['opponent_color'] == NULL) {
            $playerInfoArray['opponent_color'] = self::DEFAULT_OPPONENT_COLOR;
        }
        if ($playerInfoArray['neutral_color_a'] == NULL) {
            $playerInfoArray['neutral_color_a'] = self::DEFAULT_NEUTRAL_COLOR_A;
        }
        if ($playerInfoArray['neutral_color_b'] == NULL) {
            $playerInfoArray['neutral_color_b'] = self::DEFAULT_NEUTRAL_COLOR_B;
        }

        return array('user_prefs' => $playerInfoArray);
    }

    /**
     * Set player info
     *
     * @param int $playerId
     * @param array $infoArray
     * @param array $addlInfo
     * @return mixed
     */
    public function set_player_info($playerId, array $infoArray, array $addlInfo) {
        $isValidData =
            ($this->validate_player_dob($infoArray) &&
            $this->validate_player_password_and_email($addlInfo, $playerId) &&
            $this->validate_and_set_homepage($addlInfo['homepage'], $infoArray));
        if (!$isValidData) {
            return NULL;
        }

        $infoArray['favorite_button_id'] = NULL;
        if (isset($addlInfo['favorite_button'])) {
            $infoArray['favorite_button_id'] =
                $this->get_button_id_from_name($addlInfo['favorite_button']);
            if (!is_int($infoArray['favorite_button_id'])) {
                return FALSE;
            }
        }

        $infoArray['favorite_buttonset_id'] = NULL;
        if (isset($addlInfo['favorite_buttonset'])) {
            $infoArray['favorite_buttonset_id'] =
                $this->get_buttonset_id_from_name($addlInfo['favorite_buttonset']);
            if (!is_int($infoArray['favorite_buttonset_id'])) {
                return FALSE;
            }
        }

        if (isset($addlInfo['new_password'])) {
            $infoArray['password_hashed'] = $this->password_hashed_universal($addlInfo['new_password']);
        }

        if (isset($addlInfo['new_email'])) {
            $infoArray['email'] = $addlInfo['new_email'];
        }

        foreach ($infoArray as $infoType => $info) {
            try {
                $query = 'UPDATE player '.
                         "SET $infoType = :info ".
                         'WHERE id = :player_id;';
                $parameters = array(':info' => $info,
                                    ':player_id' => $playerId);
                self::$db->update($query, $parameters);
            } catch (Exception $e) {
                $this->set_message('Player info update failed: '.$e->getMessage());
            }
        }
        $this->set_message("Player info updated successfully.");
        return array('playerId' => $playerId);
    }

    /**
     * Calculate the password hash, independent of PHP version
     *
     * @param string $password
     * @return string
     */
    protected function password_hashed_universal($password) {
        if (version_compare(phpversion(), "5.5.0", "<")) {
            return crypt($password);
        } else {
            return password_hash($password, PASSWORD_DEFAULT);
        }
    }

    /**
     * Validate the date of birth
     *
     * @param array $infoArray
     * @return bool
     */
    protected function validate_player_dob(array $infoArray) {
        if (($infoArray['dob_month'] != 0 && $infoArray['dob_day'] == 0) ||
            ($infoArray['dob_month'] == 0 && $infoArray['dob_day'] != 0)) {
            $this->set_message('DOB is incomplete.');
            return FALSE;
        }

        if ($infoArray['dob_month'] != 0 && $infoArray['dob_day'] != 0 &&
            !checkdate($infoArray['dob_month'], $infoArray['dob_day'], 4)) {
            $this->set_message('DOB is not a valid date.');
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Validate password and email
     *
     * @param array $addlInfo
     * @param int $playerId
     * @return bool
     */
    protected function validate_player_password_and_email(array $addlInfo, $playerId) {
        if ((isset($addlInfo['new_password']) || isset($addlInfo['new_email'])) &&
            !isset($addlInfo['current_password'])) {
            $this->set_message('Current password is required to change password or email.');
            return FALSE;
        }

        if (isset($addlInfo['current_password'])) {
            try {
                $passwordQuery = 'SELECT password_hashed FROM player WHERE id = :playerId';
                $parameters = array(':playerId' => $playerId);
                $password_hashed = self::$db->select_single_value($passwordQuery, $parameters, 'str');
            } catch (Exception $e) {
                error_log(
                    'Caught exception in BMInterface::validate_player_password_and_email: ' .
                    $e->getMessage()
                );
                return NULL;
            }

            // support versions of PHP older than 5.5.0
            if (version_compare(phpversion(), "5.5.0", "<")) {
                $isHashCorrect =
                    ($password_hashed == crypt($addlInfo['current_password'], $password_hashed));
            } else {
                $isHashCorrect = password_verify($addlInfo['current_password'], $password_hashed);
            }

            if (!$isHashCorrect) {
                $this->set_message('Current password is incorrect.');
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * Get profile info
     *
     * @param string $profilePlayerName
     * @return array|NULL
     */
    public function get_profile_info($profilePlayerName) {
        $profilePlayerId = $this->get_player_id_from_name($profilePlayerName);
        if (!is_int($profilePlayerId)) {
            return NULL;
        }

        $playerInfoResults = $this->get_player_info($profilePlayerId);
        $playerInfo = $playerInfoResults['user_prefs'];

        try {
            $query =
                'SELECT ' .
                    'COUNT(*) AS number_of_games, ' .
                    'v.n_rounds_won >= g.n_target_wins AS win_or_loss ' .
                'FROM game AS g ' .
                    'INNER JOIN game_status AS s ON s.id = g.status_id ' .
                    'INNER JOIN game_player_view AS v ' .
                        'ON v.game_id = g.id AND v.player_id = :player_id ' .
                'WHERE s.name = "COMPLETE" ' .
                'GROUP BY v.n_rounds_won >= g.n_target_wins;';
            $parameters = array(':player_id' => $profilePlayerId);
            $columnReturnTypes = array(
                'number_of_games' => 'int',
                'win_or_loss' => 'int',
            );
            $rows = self::$db->select_rows($query, $parameters, $columnReturnTypes);
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::get_profile_info: ' .
                $e->getMessage()
            );
            return NULL;
        }

        $nWins = 0;
        $nLosses = 0;

        foreach ($rows as $row) {
            if ($row['win_or_loss'] == 1) {
                $nWins = $row['number_of_games'];
            }
            if ($row['win_or_loss'] == 0) {
                $nLosses = $row['number_of_games'];
            }
        }

        // Just select the fields we want to expose publically
        $profileInfoArray = array(
            'id' => $playerInfo['id'],
            'name_ingame' => $playerInfo['name_ingame'],
            'name_irl' => $playerInfo['name_irl'],
            'email' => ($playerInfo['is_email_public'] ? $playerInfo['email'] : NULL),
            'email_hash' => md5(strtolower(trim($playerInfo['email']))),
            'dob_month' => $playerInfo['dob_month'],
            'dob_day' => $playerInfo['dob_day'],
            'pronouns' => $playerInfo['pronouns'],
            'image_size' => $playerInfo['image_size'],
            'uses_gravatar' => $playerInfo['uses_gravatar'],
            'comment' => $playerInfo['comment'],
            'vacation_message' => $playerInfo['vacation_message'],
            'homepage' => $playerInfo['homepage'],
            'favorite_button' => $playerInfo['favorite_button'],
            'favorite_buttonset' => $playerInfo['favorite_buttonset'],
            'last_access_time' => $playerInfo['last_access_time'],
            'creation_time' => $playerInfo['creation_time'],
            'fanatic_button_id' => $playerInfo['fanatic_button_id'],
            'n_games_won' => $nWins,
            'n_games_lost' => $nLosses,
        );

        return array('profile_info' => $profileInfoArray);
    }

    /**
     * Update last action time
     *
     * @param int $playerId
     * @param int $gameId
     */
    public function update_last_action_time($playerId, $gameId = NULL) {
        try {
            $query = 'UPDATE player SET last_action_time = now() WHERE id = :id';
            $parameters = array(':id' => $playerId);
            self::$db->update($query, $parameters);

            if (is_null($gameId)) {
                return;
            }

            $query = 'UPDATE game_player_map SET last_action_time = now() '.
                     'WHERE player_id = :player_id '.
                     'AND game_id = :game_id';
            $parameters = array(':player_id' => $playerId, ':game_id' => $gameId);
            self::$db->update($query, $parameters);
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::update_last_action_time: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }

    /**
     * Update last access time
     *
     * @param int $playerId
     */
    public function update_last_access_time($playerId) {
        try {
            $query = 'UPDATE player SET last_access_time = now() WHERE id = :id';
            $parameters = array(':id' => $playerId);
            self::$db->update($query, $parameters);
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::update_last_access_time: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }
}
