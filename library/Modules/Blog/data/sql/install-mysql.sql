DROP TABLE IF EXISTS `blog_comment`;
CREATE TABLE `blog_comment` (
    `id` INT NOT null AUTO_INCREMENT,
    `created_at` DATETIME NOT null,
    `username` VARCHAR(255) NOT null,
    `email` VARCHAR(255) NOT null,
    `show_email` SMALLINT NOT null DEFAULT 0,
    `is_active` SMALLINT NOT null DEFAULT 0,
    `message` TEXT,
    `document_id` INT,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
ALTER TABLE `blog_comment` ADD CONSTRAINT `fk_blog_comment_document` FOREIGN KEY (`document_id`) REFERENCES `document`(`id`) ON UPDATE CASCADE ON DELETE SET null;
