# Table data for configuration-related tables

## Since config contains key/value pairs, add a comment for each
## key indicating what types of values are expected
DELETE FROM config;
INSERT INTO config (conf_key, conf_value) VALUES

# site_type should be one of: 'production', 'development'
#
# test-only or unstable features may be disabled on production,
# and the UI may vary slightly to tell users which site they are on
('site_type', 'development');

