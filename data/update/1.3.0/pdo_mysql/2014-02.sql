SET foreign_key_checks=0;

ALTER TABLE ̀user` ADD `active` BOOLEAN NOT NULL DEFAULT true;

SET foreign_key_checks=1;
