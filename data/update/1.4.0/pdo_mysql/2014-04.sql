SET foreign_key_checks=0;

ALTER TABLE `document` ADD `can_be_cached` BOOLEAN NOT NULL DEFAULT TRUE AFTER `show_in_nav`;

SET foreign_key_checks=1;
