ALTER TABLE player
    ADD COLUMN is_email_public BOOLEAN DEFAULT 0 NOT NULL AFTER email,
    ADD COLUMN dob_month INT DEFAULT 0 NOT NULL AFTER status,
    ADD COLUMN dob_day INT DEFAULT 0 NOT NULL AFTER dob_month,
    ADD COLUMN gender VARCHAR(100) DEFAULT '' NOT NULL AFTER dob_day;

UPDATE player
SET dob_month = MONTH(dob), dob_day = DAY(dob)
WHERE dob IS NOT NULL;

ALTER TABLE player
    DROP COLUMN dob;
