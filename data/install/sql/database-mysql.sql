-- ----------------------------
-- MySQL Database
--
-- Project    : GotCms
-- Description:
-- ----------------------------

SET foreign_key_checks = 0;

DROP TABLE IF EXISTS `core_config_data`;
DROP TABLE IF EXISTS `core_session`;
DROP TABLE IF EXISTS `core_translate_locale`;
DROP TABLE IF EXISTS `core_translate`;
DROP TABLE IF EXISTS `datatype`;
DROP TABLE IF EXISTS `document`;
DROP TABLE IF EXISTS `document_type`;
DROP TABLE IF EXISTS `document_type_view`;
DROP TABLE IF EXISTS `document_type_dependency`;
DROP TABLE IF EXISTS `icon`;
DROP TABLE IF EXISTS `log_url_info`;
DROP TABLE IF EXISTS `log_url`;
DROP TABLE IF EXISTS `log_visitor`;
DROP TABLE IF EXISTS `layout`;
DROP TABLE IF EXISTS `property`;
DROP TABLE IF EXISTS `property_value`;
DROP TABLE IF EXISTS `script`;
DROP TABLE IF EXISTS `tab`;
DROP TABLE IF EXISTS `view`;
DROP TABLE IF EXISTS `user`;
DROP TABLE IF EXISTS `user_acl`;
DROP TABLE IF EXISTS `user_acl_resource`;
DROP TABLE IF EXISTS `user_acl_role`;
DROP TABLE IF EXISTS `user_acl_permission`;
SET foreign_key_checks = 1;

-- Start Table's declaration
CREATE TABLE `datatype` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `prevalue_value` TEXT,
    `model` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE `document_type_view` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `view_id` INT NOT NULL,
    `document_type_id` INT NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE `document_type_dependency` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `parent_id` INT NOT NULL,
    `children_id` INT NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE `document_type` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `icon_id` integer,
    `DEFAULT_view_id` integer,
    `user_id` INT NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE `document` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `url_key` VARCHAR(255) NOT NULL,
    `status` INT NOT NULL DEFAULT 0,
    `sort_order` INT NOT NULL DEFAULT 0,
    `show_in_nav` boolean DEFAULT false,
    `user_id` INT NOT NULL,
    `document_type_id` integer,
    `view_id` integer,
    `layout_id` integer,
    `parent_id` integer DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE `icon` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `url` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE `layout` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `identifier` VARCHAR(255) NOT NULL,
    `content` TEXT,
    `description` VARCHAR(255),
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE `property` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255),
    `identifier` VARCHAR(255) NOT NULL,
    `description` VARCHAR(255),
    `required` boolean NOT NULL DEFAULT false,
    `sort_order` integer DEFAULT 0,
    `tab_id` INT NOT NULL,
    `datatype_id` INT NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE `property_value` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `document_id` INT NOT NULL,
    `property_id` INT NOT NULL,
    `value` text,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE `tab` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `description` VARCHAR(255),
    `sort_order` integer DEFAULT 0,
    `document_type_id` INT NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE `core_translate` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `source` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`source`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE `core_translate_locale` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `destination` VARCHAR(255) NOT NULL,
    `locale` VARCHAR(255) NOT NULL,
    `core_translate_id` INT NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE `user_acl_role` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255),
    `description` VARCHAR(255),
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`)
);

CREATE TABLE `user_acl` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_acl_permission_id` INT NOT NULL,
    `user_acl_role_id` INT NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE `user_acl_resource` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `resource` VARCHAR(255),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE `user` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    `lastname` VARCHAR(255) NOT NULL,
    `firstname` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `login` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `user_acl_role_id` INT NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE `view` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `identifier` VARCHAR(255),
    `content` TEXT,
    `description` VARCHAR(255),
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE `script` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `identifier` VARCHAR(255),
    `content` TEXT,
    `description` VARCHAR(255),
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE `user_acl_permission` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `permission` VARCHAR(255) NOT NULL,
    `user_acl_resource_id` INT NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE `core_config_data` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `identifier` VARCHAR(255) NOT NULL,
    `value` VARCHAR(255),
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`identifier`)
);

CREATE TABLE `core_session` (
    `id` VARCHAR(255) NOT NULL,
    `name` VARCHAR(50) NOT NULL,
    `updated_at` INT NOT NULL,
    `lifetime` INT NOT NULL,
    `data` TEXT,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE `log_visitor` (
    `id` BIGINT(20) unsigned NOT NULL AUTO_INCREMENT,
    `session_id` CHAR(64) NOT NULL DEFAULT '',
    `http_user_agent` VARCHAR(255) DEFAULT NULL,
    `http_accept_CHARset` VARCHAR(255) DEFAULT NULL,
    `http_accept_language` VARCHAR(255) DEFAULT NULL,
    `server_addr` BIGINT(20) DEFAULT NULL,
    `remote_addr` BIGINT(20) DEFAULT NULL,
    PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE `log_url_info` (
    `id` BIGINT(20) unsigned NOT NULL AUTO_INCREMENT,
    `url` VARCHAR(255) NOT NULL DEFAULT '',
    `referer` VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE `log_url` (
    `id` BIGINT(20) unsigned NOT NULL AUTO_INCREMENT,
    `visit_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    `log_url_info_id` BIGINT(20) unsigned,
    `log_visitor_id` BIGINT(20) unsigned,
    PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

-- End Table's declaration

-- Start Relation's declaration
ALTER TABLE `document_type_view` ADD CONSTRAINT `fk_document_type_views_views` FOREIGN KEY (`view_id`) REFERENCES `view`(`id`) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE `document_type_dependency` ADD CONSTRAINT `fk_document_type_dependency_parent_id` FOREIGN KEY (`parent_id`) REFERENCES `document_type`(`id`) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE `document_type_dependency` ADD CONSTRAINT `fk_document_type_dependency_children_id` FOREIGN KEY (`children_id`) REFERENCES `document_type`(`id`) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE `property` ADD CONSTRAINT `fk_property_datatype` FOREIGN KEY (`datatype_id`) REFERENCES `datatype`(`id`) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE `property` ADD CONSTRAINT `fk_property_tab` FOREIGN KEY (`tab_id`) REFERENCES `tab`(`id`) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE `document` ADD CONSTRAINT `fk_document_layout` FOREIGN KEY (`layout_id`) REFERENCES `layout`(`id`) ON UPDATE SET NULL ON DELETE SET NULL;

ALTER TABLE `document` ADD CONSTRAINT `fk_document_document` FOREIGN KEY (`parent_id`) REFERENCES `document`(`id`) ON UPDATE SET NULL ON DELETE SET NULL;

ALTER TABLE `document` ADD CONSTRAINT `fk_documents_view` FOREIGN KEY (`view_id`) REFERENCES `view`(`id`) ON UPDATE SET NULL ON DELETE SET NULL;

ALTER TABLE `document` ADD CONSTRAINT `fk_document_document_type` FOREIGN KEY (`document_type_id`) REFERENCES `document_type`(`id`) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE `document_type` ADD CONSTRAINT `fk_document_type_view` FOREIGN KEY (`DEFAULT_view_id`) REFERENCES `view`(`id`) ON UPDATE SET NULL ON DELETE SET NULL;

ALTER TABLE `document_type` ADD CONSTRAINT `fk_document_type_icon` FOREIGN KEY (`icon_id`) REFERENCES `icon`(`id`) ON UPDATE SET NULL ON DELETE SET NULL;

ALTER TABLE `document` ADD CONSTRAINT `fk_document_user` FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE `user_acl` ADD CONSTRAINT `fk_user_acl_permission_user_acl_role` FOREIGN KEY (`user_acl_role_id`) REFERENCES `user_acl_role`(`id`) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE `user` ADD CONSTRAINT `fk_user_user_acl_role` FOREIGN KEY (`user_acl_role_id`) REFERENCES `user_acl_role`(`id`) ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE `core_translate_locale` ADD CONSTRAINT `fk_core_translate_locale_core_translate` FOREIGN KEY (`core_translate_id`) REFERENCES `core_translate`(`id`) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE `user_acl` ADD CONSTRAINT `fk_user_acl_user_acl_permission` FOREIGN KEY (`user_acl_permission_id`) REFERENCES `user_acl_permission`(`id`) ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE `document_type_view` ADD CONSTRAINT `fk_document_type_view_document_type` FOREIGN KEY (`document_type_id`) REFERENCES `document_type`(`id`) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE `user_acl_permission` ADD CONSTRAINT `fk_user_acl_permission_user_acl_resource` FOREIGN KEY (`user_acl_resource_id`) REFERENCES `user_acl_resource`(`id`) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE `property_value` ADD CONSTRAINT `fk_property_value_document` FOREIGN KEY (`document_id`) REFERENCES `document`(`id`) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE `property_value` ADD CONSTRAINT `fk_property_value_property` FOREIGN KEY (`property_id`) REFERENCES `property`(`id`) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE `log_url` ADD CONSTRAINT `fk_log_url_log_visitor` FOREIGN KEY (`log_visitor_id`) REFERENCES `log_visitor`(`id`) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE `log_url` ADD CONSTRAINT `fk_log_url_log_url_info` FOREIGN KEY (`log_url_info_id`) REFERENCES `log_url_info`(`id`) ON UPDATE CASCADE ON DELETE CASCADE;
-- End Relation's declaration

