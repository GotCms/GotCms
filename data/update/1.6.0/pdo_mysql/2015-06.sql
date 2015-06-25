SET foreign_key_checks=0;

ALTER TABLE `view`
DROP COLUMN content;

ALTER TABLE `layout`
DROP COLUMN content;

ALTER TABLE `script`
DROP COLUMN content;

DELETE FROM core_config_data WHERE identifier = 'stream_wrapper_is_active';

SET foreign_key_checks=1;
