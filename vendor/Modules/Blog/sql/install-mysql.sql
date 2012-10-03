DROP TABLE IF EXISTS `blog_comment`;
CREATE TABLE `blog_comment` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `show_email` SMALLINT NOT NULL DEFAULT 0,
    `is_active` SMALLINT NOT NULL DEFAULT 0,
    `message` TEXT,
    `document_id` INT,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
ALTER TABLE `blog_comment` ADD CONSTRAINT `fk_blog_comment_document` FOREIGN KEY (`document_id`) REFERENCES `document`(`id`) ON UPDATE CASCADE ON DELETE SET NULL;
