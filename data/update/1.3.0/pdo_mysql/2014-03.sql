SET foreign_key_checks=0;

ALTER TABLE `core_translate` DROP INDEX `name`
ALTER TABLE `core_translate_locale` CHANGE `destination` `source` TEXT;
ALTER TABLE `core_translate` CHANGE `source` `source` TEXT;

SET foreign_key_checks=1;
