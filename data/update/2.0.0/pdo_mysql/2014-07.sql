SET foreign_key_checks=0;

ALTER TABLE `document` ADD `locale` BOOLEAN DEFAULT NULL AFTER `can_be_cached`;

SET foreign_key_checks=1;
