# Table schemas for configuration-related tables

DROP TABLE IF EXISTS config;
CREATE TABLE config (
    conf_key         VARCHAR(50) UNIQUE NOT NULL,
    conf_value       VARCHAR(50)
);
