CREATE TABLE player_reset_verification (
    player_id        SMALLINT UNSIGNED PRIMARY KEY,
    verification_key VARCHAR(253) UNIQUE NOT NULL,
    ipaddr           VARCHAR(40),
    generation_time  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

