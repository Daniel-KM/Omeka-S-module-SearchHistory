CREATE TABLE `search_request` (
    `id` INT AUTO_INCREMENT NOT NULL,
    `user_id` INT DEFAULT NULL,
    `site_id` INT DEFAULT NULL,
    `comment` VARCHAR(190) DEFAULT NULL,
    `engine` VARCHAR(190) DEFAULT NULL,
    `query` LONGTEXT DEFAULT NULL,
    `created` DATETIME NOT NULL,
    `modified` DATETIME DEFAULT NULL,
    INDEX IDX_B6466005A76ED395 (`user_id`),
    INDEX IDX_B6466005F6BD1646 (`site_id`),
    PRIMARY KEY(`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
ALTER TABLE `search_request` ADD CONSTRAINT FK_B6466005A76ED395 FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL;
ALTER TABLE `search_request` ADD CONSTRAINT FK_B6466005F6BD1646 FOREIGN KEY (`site_id`) REFERENCES `site` (`id`) ON DELETE SET NULL;
