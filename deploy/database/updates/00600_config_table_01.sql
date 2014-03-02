# Add and populate the config table

DROP TABLE IF EXISTS config;
CREATE TABLE config (
    conf_key         VARCHAR(50) UNIQUE NOT NULL,
    conf_value       VARCHAR(50)
);

DELETE FROM config;
INSERT INTO config (conf_key, conf_value) VALUES
('site_type', 'development');

