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

    public function get_player_info($playerId) {
        try {
            $query =
                'SELECT p.*, b.name AS favorite_button, bs.name AS favorite_buttonset, ' .
                    'UNIX_TIMESTAMP(p.last_access_time) AS last_access_timestamp, ' .
                    'UNIX_TIMESTAMP(p.last_action_time) AS last_action_timestamp, ' .
                    'UNIX_TIMESTAMP(p.creation_time) AS creation_timestamp ' .
                'FROM player_view p ' .
                    'LEFT JOIN button b ON b.id = p.favorite_button_id ' .
                    'LEFT JOIN buttonset bs ON bs.id = p.favorite_buttonset_id ' .
                'WHERE p.id = :id';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':id' => $playerId));
            $result = $statement->fetchAll();

            if (0 == count($result)) {
                return NULL;
            }
        } catch (Exception $e) {
            if (isset($statement)) {
                $errorData = $statement->errorInfo();
                $this->message = 'Player info get failed: ' . $errorData[2];
            } else {
                $this->message = 'Player info get failed: ' . $e->getMessage();
            }
            error_log($this->message);
            return NULL;
        }

        $infoArray = $result[0];

        $last_action_time = (int)$infoArray['last_action_timestamp'];
        if ($last_action_time == 0) {
            $last_action_time = NULL;
        }

        $last_access_time = (int)$infoArray['last_access_timestamp'];
        if ($last_access_time == 0) {
            $last_access_time = NULL;
        }

        $image_size = NULL;
        if ($infoArray['image_size'] != NULL) {
            $image_size = (int)$infoArray['image_size'];
        }

        // set the values we want to actually return
        $playerInfoArray = array(
            'id' => (int)$infoArray['id'],
            'name_ingame' => $infoArray['name_ingame'],
            'name_irl' => $infoArray['name_irl'] ?: $infoArray['name_ingame'],
            'email' => $infoArray['email'],
            'is_email_public' => (bool)$infoArray['is_email_public'],
            'status' => $infoArray['status'],
            'dob_month' => (int)$infoArray['dob_month'],
            'dob_day' => (int)$infoArray['dob_day'],
            'gender' => $infoArray['gender'],
            'image_size' => $image_size,
            'autopass' => (bool)$infoArray['autopass'],
            'uses_gravatar' => (bool)$infoArray['uses_gravatar'],
            'monitor_redirects_to_game' => (bool)$infoArray['monitor_redirects_to_game'],
            'monitor_redirects_to_forum' => (bool)$infoArray['monitor_redirects_to_forum'],
            'automatically_monitor' => (bool)$infoArray['automatically_monitor'],
            'comment' => $infoArray['comment'],
            'player_color' => $infoArray['player_color'] ?: self::DEFAULT_PLAYER_COLOR,
            'opponent_color' => $infoArray['opponent_color'] ?: self::DEFAULT_OPPONENT_COLOR,
            'neutral_color_a' => $infoArray['neutral_color_a'] ?: self::DEFAULT_NEUTRAL_COLOR_A,
            'neutral_color_b' => $infoArray['neutral_color_b'] ?: self::DEFAULT_NEUTRAL_COLOR_B,
            'homepage' => $infoArray['homepage'],
            'favorite_button' => $infoArray['favorite_button'],
            'favorite_buttonset' => $infoArray['favorite_buttonset'],
            'last_action_time' => $last_action_time,
            'last_access_time' => $last_access_time,
            'creation_time' => (int)$infoArray['creation_timestamp'],
            'fanatic_button_id' => (int)$infoArray['fanatic_button_id'],
            'n_games_won' => (int)$infoArray['n_games_won'],
            'n_games_lost' => (int)$infoArray['n_games_lost'],
        );

        return array('user_prefs' => $playerInfoArray);
    }

//set_player_info()
//validate_player_dob()
//validate_player_password_and_email()
//get_profile_info()
//update_last_action_time()
//update_last_access_time()

}