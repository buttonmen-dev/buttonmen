# insert an ipaddr column in player_auth
ALTER TABLE player_verification ADD ipaddr VARCHAR(40) AFTER verification_key;

