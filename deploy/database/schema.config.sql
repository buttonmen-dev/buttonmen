# Table schemas for configuration-related tables

CREATE TABLE config (
    conf_key         VARCHAR(50) UNIQUE NOT NULL,
    conf_value       VARCHAR(50)
);
