DELIMITER $$

DROP TABLE IF EXISTS `users_messages`
$$

CREATE TABLE IF NOT EXISTS `users_messages`(
	`id_users` BIGINT UNSIGNED NOT null COMMENT 'ID пользователя'
	, `id_messages` BIGINT UNSIGNED NOT null COMMENT 'ID сообщения'
	, `created_at` TIMESTAMP NOT null DEFAULT CURRENT_TIMESTAMP COMMENT 'когда'

	, PRIMARY KEY(`id_users`, `id_messages`)
	, INDEX(`id_messages`)
	, CONSTRAINT FOREIGN KEY(`id_users`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
	, CONSTRAINT FOREIGN KEY(`id_messages`) REFERENCES `messages`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) COMMENT 'контроль отправки сообщений пользователям'
$$

DELIMITER ;